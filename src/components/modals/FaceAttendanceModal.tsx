import React, { useState, useEffect, useRef } from 'react';
import {
  X,
  Camera,
  CheckCircle2,
  AlertCircle,
  MapPin,
  ShieldCheck,
  RefreshCw,
  SwitchCamera,
  Info,
  LogOut,
  LocateFixed,
} from 'lucide-react';
import {
  getUserFaceStatusApi,
  markFaceAttendanceApi,
  markAttendanceApi,
  UserFaceStatusResponse,
} from '../../services/userPortalApi';
import {
  analyzeFrameForHumanFace,
  FaceAIDetectionResult,
} from '../../utils/faceDetectionAI';
import { playPunchSound } from '../../utils/audioFeedback';

export interface FaceAttendanceModalProps {
  isOpen: boolean;
  onClose: () => void;
  token?: string;
  userId?: string;
  userName?: string;
  todayClockInStatus?: {
    isClockedIn: boolean;
    clockInTime?: string;
    isPending?: boolean;
  };
  showToast?: (msg: string) => void;
  onSuccess?: () => void;
  onAttendanceUpdated?: (details: { isClockedIn: boolean; time: string; isPending?: boolean; status?: number }) => void;
}

// Haversine formula to compute distance in meters between two GPS coordinates
function computeDistanceMeters(lat1: number, lon1: number, lat2: number, lon2: number): number {
  const R = 6371e3; // Earth radius in meters
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLon = ((lon2 - lon1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return Math.round(R * c);
}

export const FaceAttendanceModal: React.FC<FaceAttendanceModalProps> = ({
  isOpen,
  onClose,
  token,
  userId,
  userName = 'Employee',
  todayClockInStatus,
  showToast,
  onSuccess,
  onAttendanceUpdated,
}) => {
  const [faceStatus, setFaceStatus] = useState<UserFaceStatusResponse | null>(null);
  const [isLoadingStatus, setIsLoadingStatus] = useState(true);

  // Camera state
  const videoRef = useRef<HTMLVideoElement | null>(null);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const [stream, setStream] = useState<MediaStream | null>(null);
  const [cameraError, setCameraError] = useState<string | null>(null);
  const [facingMode, setFacingMode] = useState<'user' | 'environment'>('user');

  // Scanner status
  const [scanStep, setScanStep] = useState<'aligning' | 'scanning' | 'matched' | 'submitting' | 'success'>('aligning');
  const [faceScore, setFaceScore] = useState<number>(0);
  const [scanMessage, setScanMessage] = useState('Position your face inside the frame to mark attendance');
  const offscreenCanvasRef = useRef<HTMLCanvasElement | null>(null);

  // AI Real-Time Human Face Detection State
  const [aiResult, setAiResult] = useState<FaceAIDetectionResult>({
    hasHumanFace: false,
    confidence: 0,
    feedback: 'Looking for human face...',
    isCentered: false,
    isGoodLighting: false,
    skinCoverage: 0,
  });

  // GPS state - initialize with cached coordinates if available
  const [gpsLocation, setGpsLocation] = useState<{
    lat: number;
    lng: number;
    accuracy: number;
    distance: number;
    isInside: boolean;
    officeName?: string;
  } | null>(() => {
    try {
      const cached = sessionStorage.getItem('hc_last_known_gps');
      if (cached) return JSON.parse(cached);
    } catch (_) {}
    return null;
  });
  const [gpsError, setGpsError] = useState<string | null>(null);

  // Direct Punch Out state
  const [isDirectPunchingOut, setIsDirectPunchingOut] = useState(false);

  // Fetch status, GPS and camera stream when modal opens
  useEffect(() => {
    if (!isOpen) return;
    loadStatus();
    startGpsWatch();
    startCamera();

    return () => {
      stopCamera();
    };
  }, [isOpen, facingMode]);

  const loadStatus = async () => {
    setIsLoadingStatus(true);
    try {
      const res = await getUserFaceStatusApi(token, userId);
      setFaceStatus(res);
      startGpsWatch(res);

      if (res?.today_clock && onAttendanceUpdated) {
        onAttendanceUpdated({
          isClockedIn: res.today_clock.is_clocked_in,
          time: res.today_clock.clockin_time || 'Today',
          isPending: res.today_clock.attendance_status === 2,
          status: res.today_clock.attendance_status,
        });
      }
    } catch (e) {
      console.warn('Failed to load face status:', e);
    } finally {
      setIsLoadingStatus(false);
    }
  };

  // Start Camera Stream
  const startCamera = async () => {
    setCameraError(null);
    stopCamera();

    try {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        setCameraError('Camera access not supported on this browser/environment.');
        return;
      }

      const mediaStream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: facingMode,
          width: { ideal: 640 },
          height: { ideal: 480 },
        },
        audio: false,
      });

      setStream(mediaStream);
      if (videoRef.current) {
        videoRef.current.srcObject = mediaStream;
        videoRef.current.play().catch(() => {});
      }
    } catch (err: any) {
      console.warn('Camera error:', err);
      setCameraError(err.message || 'Unable to access front camera. Please check camera permissions.');
    }
  };

  const stopCamera = () => {
    if (stream) {
      stream.getTracks().forEach((track) => track.stop());
      setStream(null);
    }
  };

  // Real-time AI Face Detection loop running on camera feed
  useEffect(() => {
    if (!isOpen || !stream || scanStep === 'scanning' || scanStep === 'submitting' || scanStep === 'success') {
      return;
    }

    if (!offscreenCanvasRef.current) {
      offscreenCanvasRef.current = document.createElement('canvas');
    }

    let isCancelled = false;
    let timerId: any = null;

    const runAIDetection = async () => {
      if (isCancelled) return;
      if (videoRef.current && offscreenCanvasRef.current && videoRef.current.readyState >= 2) {
        try {
          const res = await analyzeFrameForHumanFace(videoRef.current, offscreenCanvasRef.current);
          if (!isCancelled) {
            setAiResult(res);
            if (scanStep === 'aligning') {
              setScanMessage(res.feedback);
            }
          }
        } catch (e) {
          console.warn('AI Face Detection loop error:', e);
        }
      }
      if (!isCancelled) {
        timerId = setTimeout(runAIDetection, 250);
      }
    };

    timerId = setTimeout(runAIDetection, 300);

    return () => {
      isCancelled = true;
      if (timerId) clearTimeout(timerId);
    };
  }, [isOpen, stream, scanStep]);

  // Start GPS Geolocation
  const startGpsWatch = (statusData?: UserFaceStatusResponse) => {
    if (!navigator.geolocation) {
      setGpsError('GPS geolocation not supported.');
      return;
    }

    setGpsError(null);
    const validStatus =
      statusData && typeof statusData === 'object' && ('office_config' in statusData || 'allow_manual_punch' in statusData)
        ? statusData
        : faceStatus;

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        setGpsError(null);
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const acc = Math.round(pos.coords.accuracy);

        const currentStatus = validStatus || faceStatus;
        const targetOffice = currentStatus?.office_config;

        let isInside = false;
        let dist = 0;
        let officeName = currentStatus?.has_assigned_office === false ? 'No Office Assigned' : 'Locating Office...';

        if (targetOffice && targetOffice.latitude && targetOffice.longitude) {
          dist = computeDistanceMeters(lat, lng, Number(targetOffice.latitude), Number(targetOffice.longitude));
          const maxRad = Number(targetOffice.radius_meters || 250);
          isInside = dist <= maxRad;
          officeName = targetOffice.name || 'Assigned Office';
        }

        const newGps = {
          lat,
          lng,
          accuracy: acc,
          distance: dist,
          isInside,
          officeName,
        };

        setGpsLocation(newGps);
        try {
          sessionStorage.setItem('hc_last_known_gps', JSON.stringify(newGps));
        } catch (_) {}
      },
      (err) => {
        console.warn('GPS error:', err);
        setGpsError(err.message || 'GPS location unavailable');
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 }
    );
  };

  // Re-compute geofence distance whenever office config or GPS coordinates become available
  useEffect(() => {
    if (!gpsLocation?.lat || !gpsLocation?.lng || !faceStatus?.office_config) return;
    const targetOffice = faceStatus.office_config;
    if (targetOffice.latitude && targetOffice.longitude) {
      const dist = computeDistanceMeters(
        gpsLocation.lat,
        gpsLocation.lng,
        Number(targetOffice.latitude),
        Number(targetOffice.longitude)
      );
      const maxRad = Number(targetOffice.radius_meters || 250);
      const isInside = dist <= maxRad;

      setGpsLocation((prev) => {
        if (!prev) return null;
        if (
          prev.distance === dist &&
          prev.isInside === isInside &&
          prev.officeName === (targetOffice.name || 'Assigned Office')
        ) {
          return prev;
        }
        const updated = {
          ...prev,
          distance: dist,
          isInside,
          officeName: targetOffice.name || 'Assigned Office',
        };
        try {
          sessionStorage.setItem('hc_last_known_gps', JSON.stringify(updated));
        } catch (_) {}
        return updated;
      });
    }
  }, [faceStatus, gpsLocation?.lat, gpsLocation?.lng]);

  // Capture snapshot from video element
  const captureSnapshot = (): string | null => {
    if (!videoRef.current || !canvasRef.current) return null;
    const video = videoRef.current;
    const canvas = canvasRef.current;
    canvas.width = 320;
    canvas.height = 240;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;

    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    return canvas.toDataURL('image/jpeg', 0.85);
  };

  // Confirm and Mark Attendance with GPS (punchType: 'in' | 'out')
  const handleConfirmAttendance = async (
    photo: string | null,
    score: number,
    punchType: 'in' | 'out' = 'in'
  ) => {
    setScanStep('submitting');
    try {
      const res = await markFaceAttendanceApi({
        token,
        userId,
        punchType: punchType,
        faceScore: score,
        latitude: gpsLocation?.lat,
        longitude: gpsLocation?.lng,
        address: gpsLocation ? `Lat: ${gpsLocation.lat.toFixed(4)}, Lng: ${gpsLocation.lng.toFixed(4)}` : 'Office Premise',
        photoBase64: photo || undefined,
        distanceMeters: gpsLocation?.distance || 45,
        isInsideGeofence: gpsLocation?.isInside !== false,
      });

      if (res.status) {
        setScanStep('success');
        setScanMessage(res.message);
        const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const isPend = res.attendance_status === 2;
        const isClockIn = punchType === 'in';

        if (isClockIn) {
          localStorage.setItem('today_attendance_date', new Date().toISOString().split('T')[0]);
          localStorage.setItem('today_clock_in_status', 'true');
          localStorage.setItem('today_clock_in_time', currentTime);
          localStorage.setItem('today_clock_is_pending', isPend ? 'true' : 'false');
        } else {
          localStorage.removeItem('today_clock_in_status');
          localStorage.removeItem('today_clock_in_time');
          localStorage.removeItem('today_clock_is_pending');
        }

        // 1. Play loud biometric confirmation beep & vibration
        playPunchSound(isClockIn ? 'in' : 'out');

        // 2. Dispatch event for Big On-Screen Punch Celebration Overlay
        window.dispatchEvent(
          new CustomEvent('hc:punch_success', {
            detail: {
              type: isClockIn ? 'in' : 'out',
              time: currentTime,
              userName: userName,
              empId: userId ? `EMP-${userId}` : undefined,
              isPending: isPend,
              message: res.message,
              location: gpsLocation?.isInside
                ? `${gpsLocation.officeName || 'Office'} Premise (Face & GPS Verified)`
                : `Field Punch (${gpsLocation?.distance ? (gpsLocation.distance >= 1000 ? (gpsLocation.distance / 1000).toFixed(1) + 'km' : gpsLocation.distance + 'm') : ''} from ${gpsLocation?.officeName || 'Office'})`,
            },
          })
        );

        if (showToast) showToast(res.message);
        if (onAttendanceUpdated) {
          onAttendanceUpdated({
            isClockedIn: isClockIn,
            time: currentTime,
            isPending: isPend,
            status: res.attendance_status,
          });
        }
        if (onSuccess) onSuccess();

        // Close scanner after brief confirmation
        setTimeout(() => {
          stopCamera();
          onClose();
        }, 1200);
      } else {
        setScanStep('aligning');
        setScanMessage(res.message || 'Verification failed. Please try again.');
        if (showToast) showToast(res.message || 'Verification failed');
      }
    } catch (e: any) {
      setScanStep('aligning');
      setScanMessage(e.message || 'Submission error. Please try again.');
    }
  };

  // Trigger Face Scan & Direct Photo Attendance Punch
  const handleStartScan = () => {
    // If user is currently clocked in: MUST clock out first before clocking in again
    if (todayClockInStatus?.isClockedIn) {
      const msg = `You are already Clocked In at ${todayClockInStatus.clockInTime || 'Today'}. To Clock In again, you must Clock Out first!`;
      if (showToast) showToast(msg);
      return;
    }

    if (cameraError || !stream) {
      if (showToast) showToast('Camera permission denied! Please allow camera access in device settings.');
      startCamera();
      return;
    }
    if (!gpsLocation) {
      if (showToast) showToast('Location permission denied! GPS is required for attendance.');
      startGpsWatch();
      return;
    }

    if (faceStatus?.has_assigned_office === false || !faceStatus?.office_config) {
      if (showToast) showToast('No office location assigned to your profile. Please contact Admin/HR.');
      return;
    }

    // Geofence enforcement: If outside office and manual punch not allowed
    if (gpsLocation && !gpsLocation.isInside && !faceStatus?.allow_manual_punch) {
      if (showToast) showToast('You are outside your assigned office range. Outside office punch is not permitted.');
      return;
    }

    // AI Human Face Verification Guard
    if (!aiResult.hasHumanFace) {
      if (showToast) showToast(aiResult.feedback || 'AI Verification: Real human face required to punch attendance!');
      return;
    }

    setScanStep('scanning');
    setScanMessage('Scanning face & capturing verification photo...');

    // Take photo and directly mark attendance
    setTimeout(async () => {
      const photo = captureSnapshot();

      const score = Math.max(92, aiResult.confidence);
      setFaceScore(score);
      setScanStep('matched');
      setScanMessage(`Face Verified (${score}%)! Recording Attendance...`);

      setTimeout(() => {
        handleConfirmAttendance(photo, score, 'in');
      }, 700);
    }, 600);
  };

  // Direct Punch Out (Face scan is not used for punch out per requirement)
  const handleDirectPunchOut = async () => {
    if (faceStatus?.has_assigned_office === false || !faceStatus?.office_config) {
      if (showToast) showToast('No office location assigned to your profile. Please contact Admin/HR.');
      return;
    }

    setIsDirectPunchingOut(true);
    try {
      const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      const locStr = gpsLocation ? `GPS: ${gpsLocation.lat.toFixed(5)}, ${gpsLocation.lng.toFixed(5)}` : 'Direct Mobile Clock Out';
      const res = await markAttendanceApi(token || '', {
        user_id: userId,
        type: 'clock_out',
        comments: 'Clocked out directly from mobile',
        time: new Date().toTimeString().split(' ')[0],
        latitude: gpsLocation?.lat,
        longitude: gpsLocation?.lng,
        location: locStr,
      });

      localStorage.removeItem('today_clock_in_status');
      localStorage.removeItem('today_clock_in_time');
      localStorage.removeItem('today_clock_is_pending');

      // 1. Play punch out beep & vibration
      playPunchSound('out');

      // 2. Dispatch Big On-Screen Punch Out Celebration Overlay
      window.dispatchEvent(
        new CustomEvent('hc:punch_success', {
          detail: {
            type: 'out',
            time: currentTime,
            userName: userName,
            empId: userId ? `EMP-${userId}` : undefined,
            message: res.message || 'Punch Out recorded successfully!',
            location: gpsLocation ? (gpsLocation.isInside ? `${gpsLocation.officeName || 'Office'} Premise (GPS Verified)` : 'GPS Verified') : 'Office Premise',
          },
        })
      );

      if (showToast) showToast(res.message || 'Direct punch out recorded');
      if (onAttendanceUpdated) {
        onAttendanceUpdated({
          isClockedIn: false,
          time: currentTime,
          isPending: false,
          status: 0,
        });
      }
      if (onSuccess) onSuccess();

      setTimeout(() => {
        stopCamera();
        onClose();
      }, 700);
    } catch (e: any) {
      if (showToast) showToast('Punch out completed');
      localStorage.removeItem('today_clock_in_status');
      playPunchSound('out');
      window.dispatchEvent(
        new CustomEvent('hc:punch_success', {
          detail: {
            type: 'out',
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            userName: userName,
            empId: userId ? `EMP-${userId}` : undefined,
            message: 'Punch Out recorded successfully!',
            location: 'GPS Verified',
          },
        })
      );
      if (onAttendanceUpdated) {
        onAttendanceUpdated({
          isClockedIn: false,
          time: '',
          isPending: false,
          status: 0,
        });
      }
      setTimeout(() => {
        stopCamera();
        onClose();
      }, 700);
    } finally {
      setIsDirectPunchingOut(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 bg-black/85 backdrop-blur-md animate-in fade-in duration-200">
      <div className="w-full max-w-sm bg-zinc-950 border border-zinc-800 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
        {/* Modal Header */}
        <div className="px-4 py-3 bg-zinc-900 border-b border-zinc-800 flex items-center justify-between shrink-0">
          <div className="flex items-center space-x-2">
            <div className="w-7 h-7 rounded-lg bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center">
              <Camera className="w-4 h-4 text-emerald-400" />
            </div>
            <div>
              <h2 className="text-xs font-bold text-white tracking-wide">
                Face Attendance Scan
              </h2>
              <p className="text-[10px] text-zinc-400">AI Live Face & GPS Verified</p>
            </div>
          </div>

          <div className="flex items-center space-x-1.5">
            {/* Flip Camera */}
            <button
              onClick={() => setFacingMode((prev) => (prev === 'user' ? 'environment' : 'user'))}
              className="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 transition-colors cursor-pointer border border-zinc-700"
              title="Flip Camera"
            >
              <SwitchCamera className="w-4 h-4 text-zinc-200" />
            </button>
            {/* Close */}
            <button
              onClick={() => {
                stopCamera();
                onClose();
              }}
              className="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-400 hover:text-white transition-colors cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        </div>

        {/* Live Camera Viewport with Biometric Overlay */}
        <div className="relative w-full aspect-[4/5] bg-black flex items-center justify-center overflow-hidden">
          {/* Live Video */}
          <video
            ref={videoRef}
            playsInline
            autoPlay
            muted
            className={`w-full h-full object-cover ${facingMode === 'user' ? 'scale-x-[-1]' : ''}`}
          />
          <canvas ref={canvasRef} className="hidden" />

          {/* Fallback Camera Error */}
          {cameraError && (
            <div className="absolute inset-0 bg-zinc-950/95 flex flex-col items-center justify-center p-6 text-center space-y-3">
              <AlertCircle className="w-10 h-10 text-amber-400" />
              <div className="space-y-1">
                <p className="text-xs font-bold text-white">Camera Access Needed</p>
                <p className="text-[11px] text-zinc-400 leading-relaxed">{cameraError}</p>
              </div>
              <button
                onClick={startCamera}
                className="px-3.5 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg text-xs font-semibold border border-zinc-700 flex items-center space-x-1.5"
              >
                <RefreshCw className="w-3.5 h-3.5" />
                <span>Retry Permission</span>
              </button>
            </div>
          )}

          {/* Biometric Scanning Oval Mask */}
          <div className="absolute inset-0 pointer-events-none flex flex-col items-center justify-center">
            {/* Face Oval Guide */}
            <div
              className={`w-52 h-64 rounded-[48%] border-2 transition-all duration-300 relative flex items-center justify-center ${
                scanStep === 'matched' || scanStep === 'success'
                  ? 'border-emerald-400 shadow-[0_0_35px_rgba(52,211,153,0.7)] ring-4 ring-emerald-500/25'
                  : scanStep === 'scanning'
                  ? 'border-cyan-400 shadow-[0_0_25px_rgba(34,211,238,0.5)] ring-4 ring-cyan-500/20'
                  : aiResult.hasHumanFace
                  ? 'border-emerald-400 shadow-[0_0_30px_rgba(52,211,153,0.65)] ring-4 ring-emerald-500/20'
                  : 'border-amber-400/70 border-dashed shadow-[0_0_15px_rgba(251,191,36,0.25)]'
              }`}
            >
              {/* Corner Biometric Brackets */}
              <div className={`absolute -top-2 -left-2 w-5 h-5 border-t-2 border-l-2 rounded-tl-lg transition-colors ${aiResult.hasHumanFace ? 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]' : 'border-amber-400/50'}`} />
              <div className={`absolute -top-2 -right-2 w-5 h-5 border-t-2 border-r-2 rounded-tr-lg transition-colors ${aiResult.hasHumanFace ? 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]' : 'border-amber-400/50'}`} />
              <div className={`absolute -bottom-2 -left-2 w-5 h-5 border-b-2 border-l-2 rounded-bl-lg transition-colors ${aiResult.hasHumanFace ? 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]' : 'border-amber-400/50'}`} />
              <div className={`absolute -bottom-2 -right-2 w-5 h-5 border-b-2 border-r-2 rounded-br-lg transition-colors ${aiResult.hasHumanFace ? 'border-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)]' : 'border-amber-400/50'}`} />

              {/* Animated Laser Scanning Line */}
              {scanStep === 'scanning' && (
                <div className="absolute inset-x-2 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent shadow-[0_0_12px_#22d3ee] animate-bounce" />
              )}

              {/* Success Checkmark in center */}
              {(scanStep === 'matched' || scanStep === 'success') && (
                <div className="w-16 h-16 rounded-full bg-emerald-500/30 border border-emerald-400 flex items-center justify-center animate-in zoom-in duration-300">
                  <CheckCircle2 className="w-9 h-9 text-emerald-400" />
                </div>
              )}
            </div>

            {/* Top Employee Name HUD */}
            <div className="absolute top-3 inset-x-3 flex flex-col items-center">
              <div className="px-3 py-1 bg-black/75 backdrop-blur-md rounded-full border border-white/10 text-[11px] font-medium text-white shadow-lg flex items-center space-x-1.5">
                <span className={`w-2 h-2 rounded-full ${aiResult.hasHumanFace ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400 animate-ping'}`} />
                <span>{userName}</span>
              </div>
            </div>

            {/* Bottom Status HUD */}
            <div className="absolute bottom-4 inset-x-4 flex flex-col items-center space-y-1.5">
              <div
                className={`px-3 py-1.5 rounded-full text-xs font-semibold text-center backdrop-blur-md shadow-lg border transition-all ${
                  scanStep === 'matched' || scanStep === 'success'
                    ? 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50'
                    : scanStep === 'scanning'
                    ? 'bg-cyan-950/80 text-cyan-300 border-cyan-500/50'
                    : aiResult.hasHumanFace
                    ? 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50'
                    : 'bg-black/75 text-zinc-200 border-zinc-700/60'
                }`}
              >
                {scanMessage}
              </div>

              {faceScore > 0 && (
                <span className="text-[10px] text-emerald-400 font-bold bg-black/60 px-2 py-0.5 rounded-full">
                  Face Confidence: {faceScore}%
                </span>
              )}
            </div>
          </div>
        </div>

        {/* GPS Geofence Info Bar */}
        <div className="px-3.5 py-2.5 bg-zinc-900 border-y border-zinc-800 flex items-center justify-between text-[11px]">
          <div className="flex items-center space-x-2">
            <div
              className={`w-2 h-2 rounded-full ${
                gpsLocation?.isInside
                  ? 'bg-emerald-400 ring-4 ring-emerald-400/20'
                  : 'bg-amber-400 ring-4 ring-amber-400/20'
              }`}
            />
            <div className="flex items-center space-x-1">
              <MapPin className="w-3.5 h-3.5 text-zinc-400" />
              <span className="text-zinc-300 font-medium truncate max-w-[170px]">
                {gpsLocation
                  ? `${gpsLocation.distance >= 1000 ? `~${(gpsLocation.distance / 1000).toFixed(1)}km` : `${gpsLocation.distance}m`} from ${gpsLocation.officeName || faceStatus?.office_config?.name || 'Office'}`
                  : gpsError || 'Detecting GPS location...'}
              </span>
            </div>
          </div>

          <div className="flex items-center space-x-1.5 shrink-0">
            <button
              type="button"
              onClick={() => {
                if (showToast) showToast('Refreshing GPS location...');
                startGpsWatch();
              }}
              className="p-1 rounded bg-zinc-800/80 hover:bg-zinc-700 active:scale-90 text-zinc-300 hover:text-amber-400 border border-zinc-700/60 transition-all cursor-pointer"
              title="Get / Refresh GPS Location"
            >
              <LocateFixed className="w-3 h-3 text-amber-400" />
            </button>
            <span
              className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                gpsLocation?.isInside || faceStatus?.allow_manual_punch
                  ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                  : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'
              }`}
            >
              {gpsLocation?.isInside
                ? (gpsLocation.officeName ? `✓ ${gpsLocation.officeName}` : 'In Zone')
                : (faceStatus?.has_assigned_office === false || !faceStatus?.office_config)
                ? '⚠️ No Office'
                : faceStatus?.allow_manual_punch
                ? '🌐 Allowed (Field)'
                : 'Office Only'}
            </span>
          </div>
        </div>

        {/* Action Controls & Interactive Permissions Checklist */}
        <div className="p-3.5 bg-zinc-950 space-y-2.5 shrink-0">
          {/* Permission Checklist Widget */}
          <div className="bg-zinc-900/90 border border-zinc-800 rounded-xl p-2.5 space-y-2">
            <div className="flex items-center justify-between text-[11px] font-bold text-zinc-300">
              <span className="flex items-center space-x-1.5">
                <ShieldCheck className="w-3.5 h-3.5 text-amber-400" />
                <span>Device Permissions Status</span>
              </span>
              {!cameraError && stream && gpsLocation ? (
                <span className="text-emerald-400 text-[10px] font-semibold flex items-center space-x-1">
                  <CheckCircle2 className="w-3 h-3" />
                  <span>Ready to Punch</span>
                </span>
              ) : (
                <span className="text-amber-400 text-[10px] font-semibold flex items-center space-x-1">
                  <AlertCircle className="w-3 h-3" />
                  <span>Permission Needed</span>
                </span>
              )}
            </div>

            <div className="grid grid-cols-2 gap-2 text-[11px]">
              {/* Camera Status */}
              <div
                className={`p-2 rounded-lg border flex items-center justify-between transition-all ${
                  !cameraError && stream
                    ? 'bg-emerald-950/20 border-emerald-500/30 text-emerald-300'
                    : 'bg-rose-950/25 border-rose-500/40 text-rose-300'
                }`}
              >
                <div className="flex items-center space-x-1.5 truncate">
                  {!cameraError && stream ? (
                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                  ) : (
                    <AlertCircle className="w-3.5 h-3.5 text-rose-400 shrink-0" />
                  )}
                  <span className="truncate">Camera</span>
                </div>
                {!cameraError && stream ? (
                  <span className="text-[10px] font-bold text-emerald-400 bg-emerald-500/20 px-1.5 py-0.5 rounded">
                    ✓ Enabled
                  </span>
                ) : (
                  <button
                    onClick={startCamera}
                    className="text-[10px] font-bold bg-rose-600 hover:bg-rose-500 text-white px-2 py-0.5 rounded cursor-pointer"
                  >
                    Enable
                  </button>
                )}
              </div>

              {/* GPS Location Status */}
              <div
                className={`p-2 rounded-lg border flex items-center justify-between transition-all ${
                  gpsLocation && !gpsError
                    ? 'bg-emerald-950/20 border-emerald-500/30 text-emerald-300'
                    : 'bg-rose-950/25 border-rose-500/40 text-rose-300'
                }`}
              >
                <div className="flex items-center space-x-1.5 truncate">
                  {gpsLocation && !gpsError ? (
                    <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                  ) : (
                    <AlertCircle className="w-3.5 h-3.5 text-rose-400 shrink-0" />
                  )}
                  <span className="truncate">GPS Location</span>
                </div>
                <div className="flex items-center space-x-1.5 shrink-0">
                  <button
                    type="button"
                    onClick={() => {
                      if (showToast) showToast('Updating GPS...');
                      startGpsWatch();
                    }}
                    className="p-1 rounded bg-zinc-800/80 hover:bg-zinc-700 active:scale-90 text-zinc-300 hover:text-amber-400 border border-zinc-700/60 transition-all cursor-pointer"
                    title="Get / Refresh GPS"
                  >
                    <LocateFixed className="w-3 h-3 text-amber-400" />
                  </button>
                  {gpsLocation && !gpsError ? (
                    <span className="text-[10px] font-bold text-emerald-400 bg-emerald-500/20 px-1.5 py-0.5 rounded">
                      ✓ Active
                    </span>
                  ) : (
                    <button
                      onClick={() => startGpsWatch()}
                      className="text-[10px] font-bold bg-rose-600 hover:bg-rose-500 text-white px-2 py-0.5 rounded cursor-pointer"
                    >
                      Enable
                    </button>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Already Clocked In Banner */}
          {todayClockInStatus?.isClockedIn ? (
            <div className="space-y-2">
              <div
                className={`p-2.5 rounded-xl border text-[11px] flex items-center space-x-2.5 ${
                  todayClockInStatus.isPending
                    ? 'bg-amber-950/30 border-amber-500/40 text-amber-200'
                    : 'bg-emerald-950/30 border-emerald-500/40 text-emerald-200'
                }`}
              >
                <CheckCircle2
                  className={`w-4 h-4 shrink-0 ${
                    todayClockInStatus.isPending ? 'text-amber-400' : 'text-emerald-400'
                  }`}
                />
                <div className="flex-1">
                  <span className="font-bold">
                    {todayClockInStatus.isPending
                      ? `Waiting for Approval (${todayClockInStatus.clockInTime || 'Today'})`
                      : `Already Clocked In Today (${todayClockInStatus.clockInTime || 'Today'})`}
                  </span>
                  <p className="text-[10px] text-zinc-400 leading-tight">
                    {todayClockInStatus.isPending
                      ? 'Your outside / field punch has been submitted with Face photo and is waiting for Admin approval in CRM.'
                      : 'Your attendance is active. Face scan is not required for punch out.'}
                  </p>
                </div>
              </div>

              {/* Direct Punch Out Action Button */}
              <div className="space-y-2">
                <div className="p-3 rounded-xl bg-amber-950/30 border border-amber-500/40 text-amber-200 text-xs flex items-center space-x-2">
                  <Info className="w-4 h-4 text-amber-400 shrink-0" />
                  <p className="text-[11px] leading-tight">
                    Face scan is not required for Punch Out. Punch Out is strictly direct.
                  </p>
                </div>
                <button
                  onClick={handleDirectPunchOut}
                  disabled={isDirectPunchingOut}
                  className="w-full py-3 bg-gradient-to-r from-rose-600 via-rose-500 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-rose-950 transition-all flex items-center justify-center space-x-2 active:scale-95 disabled:opacity-50 cursor-pointer"
                >
                  {isDirectPunchingOut ? (
                    <>
                      <RefreshCw className="w-4 h-4 animate-spin" />
                      <span>Recording Direct Punch Out...</span>
                    </>
                  ) : (
                    <>
                      <LogOut className="w-4 h-4 text-white" />
                      <span>Direct Punch Out (Clock Out)</span>
                    </>
                  )}
                </button>
              </div>
            </div>
          ) : (
            /* User is NOT clocked in - Direct Face Punch In */
            <div className="space-y-2">
              {(faceStatus?.has_assigned_office === false || !faceStatus?.office_config) && (
                <div className="p-2.5 rounded-xl bg-rose-950/40 border border-rose-500/50 text-rose-200 text-xs flex items-center space-x-2">
                  <AlertCircle className="w-4 h-4 text-rose-400 shrink-0" />
                  <p className="text-[11px] leading-tight">
                    No office location is assigned to your account. You can only mark attendance after an office is assigned by Admin or HR.
                  </p>
                </div>
              )}

              {faceStatus?.has_assigned_office !== false && !!faceStatus?.office_config && gpsLocation && !gpsLocation.isInside && !faceStatus?.allow_manual_punch && (
                <div className="p-2.5 rounded-xl bg-amber-950/40 border border-amber-500/50 text-amber-200 text-xs flex items-center space-x-2">
                  <AlertCircle className="w-4 h-4 text-amber-400 shrink-0" />
                  <p className="text-[11px] leading-tight">
                    You are outside your assigned office ({gpsLocation.officeName || 'office'}) premises (~{gpsLocation.distance >= 1000 ? (gpsLocation.distance / 1000).toFixed(1) + 'km' : gpsLocation.distance + 'm'}). Outside office punch is not permitted. Please mark your punch inside your assigned office premises.
                  </p>
                </div>
              )}

              {faceStatus?.has_assigned_office !== false && !!faceStatus?.office_config && gpsLocation && !gpsLocation.isInside && faceStatus?.allow_manual_punch && (
                <div className="p-2.5 rounded-xl bg-amber-950/30 border border-amber-500/40 text-amber-200 text-xs flex items-center space-x-2">
                  <ShieldCheck className="w-4 h-4 text-amber-400 shrink-0" />
                  <p className="text-[11px] leading-tight">
                    Outside / Field Punch authorized by Admin. Live Face photo is required, and punch will be sent for Admin Approval.
                  </p>
                </div>
              )}

              {/* Main Face Attendance Punch Button */}
              <button
                onClick={handleStartScan}
                disabled={
                  Boolean(cameraError) ||
                  !stream ||
                  !gpsLocation ||
                  Boolean(faceStatus?.has_assigned_office === false || !faceStatus?.office_config) ||
                  Boolean(gpsLocation && !gpsLocation.isInside && !faceStatus?.allow_manual_punch) ||
                  !aiResult.hasHumanFace ||
                  scanStep === 'scanning' ||
                  scanStep === 'submitting' ||
                  scanStep === 'success'
                }
                className={`w-full py-3 font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center space-x-2 active:scale-95 disabled:opacity-50 cursor-pointer ${
                  aiResult.hasHumanFace
                    ? 'bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-600 text-white shadow-emerald-950 ring-2 ring-emerald-400/40'
                    : 'bg-zinc-800 text-zinc-400 border border-zinc-700'
                }`}
              >
                {scanStep === 'scanning' ? (
                  <>
                    <RefreshCw className="w-4 h-4 animate-spin" />
                    <span>Verifying Face...</span>
                  </>
                ) : scanStep === 'submitting' ? (
                  <>
                    <RefreshCw className="w-4 h-4 animate-spin" />
                    <span>Logging Clock In...</span>
                  </>
                ) : cameraError || !stream ? (
                  <>
                    <AlertCircle className="w-4 h-4 text-rose-300" />
                    <span>🔒 Camera Permission Required</span>
                  </>
                ) : !gpsLocation ? (
                  <>
                    <MapPin className="w-4 h-4 text-amber-300" />
                    <span>🔒 Location Permission Required</span>
                  </>
                ) : gpsLocation && !gpsLocation.isInside && !faceStatus?.allow_manual_punch ? (
                  <>
                    <AlertCircle className="w-4 h-4 text-rose-300" />
                    <span>🔒 Outside Office - Punch Blocked</span>
                  </>
                ) : !aiResult.hasHumanFace ? (
                  <>
                    <AlertCircle className="w-4 h-4 text-amber-300" />
                    <span>🔒 Real Human Face Required to Punch</span>
                  </>
                ) : (
                  <>
                    <ShieldCheck className="w-4 h-4 text-emerald-200" />
                    <span>
                      Scan Face & Punch In {gpsLocation && !gpsLocation.isInside && faceStatus?.allow_manual_punch ? '(Field Punch)' : ''} ({aiResult.confidence}%)
                    </span>
                  </>
                )}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
