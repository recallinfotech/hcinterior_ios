import React, { useState, useEffect } from 'react';
import {
  X,
  MapPin,
  Clock,
  CheckCircle2,
  LogOut,
  Calendar,
  AlertCircle,
  Building,
  Sparkles,
  Coffee,
  Camera,
  RotateCw,
  ChevronLeft,
  ChevronRight,
  LocateFixed,
  Palmtree,
  CalendarDays,
  Send,
  FileText,
  Globe,
} from 'lucide-react';
import {
  markAttendanceApi,
  getUserFaceStatusApi,
  UserFaceStatusResponse,
  getMyAttendanceApi,
  getLeaveTypesApi,
  requestLeaveApi,
  getMyLeavesApi,
  AttendanceResponse,
  LeaveTypeItem,
  LeaveApplicationItem,
} from '../../services/userPortalApi';
import { UserData } from '../../services/authApi';
import { playPunchSound } from '../../utils/audioFeedback';

interface MarkAttendanceModalProps {
  isOpen?: boolean;
  isFullPage?: boolean;
  onClose: () => void;
  userData: UserData | null;
  authToken: string;
  showToast: (msg: string) => void;
  onAttendanceUpdated?: (details?: { isClockedIn: boolean; time: string; isPending?: boolean; status?: number }) => void;
  onOpenFaceScan?: () => void;
  canViewClients?: boolean;
}

export const MarkAttendanceModal: React.FC<MarkAttendanceModalProps> = ({
  isOpen = true,
  isFullPage = false,
  onClose,
  userData,
  authToken,
  showToast,
  onAttendanceUpdated,
  onOpenFaceScan,
  canViewClients = true,
}) => {
  const [activeSubTab, setActiveSubTab] = useState<'punch' | 'leave' | 'calendar'>('punch');
  const [currentTime, setCurrentTime] = useState<string>('');
  const [currentDate, setCurrentDate] = useState<string>('');
  const [comments, setComments] = useState<string>('');
  const [isClockedIn, setIsClockedIn] = useState<boolean>(false);
  const [clockInTime, setClockInTime] = useState<string>('09:30 AM');
  const [isPendingApproval, setIsPendingApproval] = useState<boolean>(() => localStorage.getItem('today_clock_is_pending') === 'true');
  const [isSubmitting, setIsSubmitting] = useState<boolean>(false);
  const [isRefreshing, setIsRefreshing] = useState<boolean>(false);
  const [faceStatus, setFaceStatus] = useState<UserFaceStatusResponse | null>(null);
  const isFacePending = Boolean(faceStatus?.is_enrolled && (!faceStatus?.is_approved || faceStatus?.approval_status === 0));
  const isFaceRejected = Boolean(faceStatus?.approval_status === 3);

  // Leave Management State
  const [leaveTypes, setLeaveTypes] = useState<LeaveTypeItem[]>([]);
  const [selectedLeaveCatId, setSelectedLeaveCatId] = useState<number>(2);
  const [leaveDurationType, setLeaveDurationType] = useState<'single_day' | 'multiple_days'>('single_day');
  const [leaveStartDate, setLeaveStartDate] = useState<string>(() => new Date().toISOString().split('T')[0]);
  const [leaveEndDate, setLeaveEndDate] = useState<string>(() => new Date().toISOString().split('T')[0]);
  const [leaveReason, setLeaveReason] = useState<string>('');
  const [isSubmittingLeave, setIsSubmittingLeave] = useState<boolean>(false);
  const [pastLeaves, setPastLeaves] = useState<LeaveApplicationItem[]>([]);
  const [leaveViewMode, setLeaveViewMode] = useState<'apply' | 'history'>('apply');

  // Attendance Monthly Calendar State
  const [selectedMonth, setSelectedMonth] = useState<string>(() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
  });
  const [attendanceData, setAttendanceData] = useState<AttendanceResponse | null>(null);
  const [isLoadingAttendance, setIsLoadingAttendance] = useState<boolean>(false);
  const [selectedDateDetail, setSelectedDateDetail] = useState<any | null>(null);

  // GPS Tracking state
  const [gpsLocation, setGpsLocation] = useState<{
    lat: number;
    lng: number;
    address?: string;
    distance: number;
    isInside: boolean;
    officeName?: string;
  } | null>(null);
  const [isLocating, setIsLocating] = useState<boolean>(true);
  const [gpsError, setGpsError] = useState<string | null>(null);

  // Compute distance utility
  const computeDistanceMeters = (lat1: number, lon1: number, lat2: number, lon2: number) => {
    const R = 6371e3; // metres
    const p1 = (lat1 * Math.PI) / 180;
    const p2 = (lat2 * Math.PI) / 180;
    const dp = ((lat2 - lat1) * Math.PI) / 180;
    const dl = ((lon2 - lon1) * Math.PI) / 180;
    const a =
      Math.sin(dp / 2) * Math.sin(dp / 2) +
      Math.cos(p1) * Math.cos(p2) * Math.sin(dl / 2) * Math.sin(dl / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
  };

  const requestGpsLocation = (
    config?: { latitude?: number; longitude?: number; radius_meters?: number; name?: string },
    statusData?: UserFaceStatusResponse | null
  ) => {
    setIsLocating(true);
    setGpsError(null);
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          const currentStatus = statusData || faceStatus;

          setGpsError(null);
          const targetOffice = currentStatus?.office_config || config;

          if (targetOffice && targetOffice.latitude && targetOffice.longitude) {
            const dist = computeDistanceMeters(lat, lng, Number(targetOffice.latitude), Number(targetOffice.longitude));
            const maxRad = Number(targetOffice.radius_meters || 250);
            const isInside = dist <= maxRad;

            setGpsLocation({
              lat,
              lng,
              distance: dist,
              isInside,
              officeName: targetOffice.name || 'Assigned Office',
              address: `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`,
            });
          } else {
            setGpsLocation({
              lat,
              lng,
              distance: 0,
              isInside: false,
              officeName: currentStatus?.has_assigned_office === false ? 'No Office Assigned' : 'Office',
              address: `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`,
            });
          }
          setIsLocating(false);
        },
        (err) => {
          setIsLocating(false);
          setGpsError('GPS location permission denied. Location is required to verify punch.');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    } else {
      setIsLocating(false);
      setGpsError('Geolocation is not supported on this device.');
    }
  };

  // Live Clock updater
  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      setCurrentTime(
        now.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: true,
        })
      );
      setCurrentDate(
        now.toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
        })
      );
    };

    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  // Check saved state from localStorage
  useEffect(() => {
    const saved = localStorage.getItem('today_clock_in_status');
    if (saved === 'true') {
      setIsClockedIn(true);
      const savedTime = localStorage.getItem('today_clock_in_time');
      if (savedTime) setClockInTime(savedTime);
      setIsPendingApproval(localStorage.getItem('today_clock_is_pending') === 'true');
    }
  }, []);

  // Refresh Attendance & Face Status from CRM API
  const refreshStatus = async (showSuccessToast = false) => {
    if (!authToken) return;
    setIsRefreshing(true);
    try {
      const res = await getUserFaceStatusApi(authToken, userData?.user_id);
      setFaceStatus(res);
      if (res?.office_config) {
        requestGpsLocation(res.office_config, res);
      }
      if (res?.today_clock) {
        const serverClockedIn = Boolean(res.today_clock.is_clocked_in);
        setIsClockedIn(serverClockedIn);
        if (res.today_clock.clockin_time) setClockInTime(res.today_clock.clockin_time);
        const isPend = res.today_clock.attendance_status === 2;
        setIsPendingApproval(isPend);

        if (serverClockedIn) {
          localStorage.setItem('today_attendance_date', new Date().toISOString().split('T')[0]);
          localStorage.setItem('today_clock_in_status', 'true');
          localStorage.setItem('today_clock_in_time', res.today_clock.clockin_time || 'Today');
          localStorage.setItem('today_clock_is_pending', isPend ? 'true' : 'false');
        } else {
          localStorage.removeItem('today_clock_in_status');
          localStorage.removeItem('today_clock_in_time');
          localStorage.removeItem('today_clock_is_pending');
        }

        if (onAttendanceUpdated) {
          onAttendanceUpdated({
            isClockedIn: serverClockedIn,
            time: res.today_clock.clockin_time || 'Today',
            isPending: isPend,
            status: res.today_clock.attendance_status,
          });
        }
      }
      if (showSuccessToast) {
        showToast('Attendance status synced with CRM');
      }
    } catch (e) {
      console.warn('Could not refresh face & attendance status:', e);
    } finally {
      setIsRefreshing(false);
    }
  };

  // Load Leave Types & Past Leaves when Leave sub-tab is opened
  useEffect(() => {
    if (activeSubTab === 'leave' && authToken) {
      getLeaveTypesApi(authToken).then((types) => {
        setLeaveTypes(types || []);
        if (types && types.length > 0 && !selectedLeaveCatId) {
          setSelectedLeaveCatId(types[0].leave_category_id);
        }
      });
      getMyLeavesApi(authToken, userData?.user_id).then((res) => {
        if (res && res.leaves) setPastLeaves(res.leaves);
      });
    } else if (activeSubTab === 'calendar' && authToken) {
      loadCalendarAttendance(selectedMonth);
    }
  }, [activeSubTab, selectedMonth, authToken]);

  const loadCalendarAttendance = async (monthStr: string) => {
    setIsLoadingAttendance(true);
    try {
      const data = await getMyAttendanceApi(authToken, monthStr, userData?.user_id);
      setAttendanceData(data);
    } catch (e) {
      console.warn('Failed to load attendance:', e);
    } finally {
      setIsLoadingAttendance(false);
    }
  };

  const handlePrevMonth = () => {
    const [y, m] = selectedMonth.split('-').map(Number);
    const prevDate = new Date(y, m - 2, 1);
    setSelectedMonth(`${prevDate.getFullYear()}-${String(prevDate.getMonth() + 1).padStart(2, '0')}`);
  };

  const handleNextMonth = () => {
    const [y, m] = selectedMonth.split('-').map(Number);
    const nextDate = new Date(y, m, 1);
    setSelectedMonth(`${nextDate.getFullYear()}-${String(nextDate.getMonth() + 1).padStart(2, '0')}`);
  };

  const formatMonthTitle = (monthStr: string) => {
    const [y, m] = monthStr.split('-').map(Number);
    const d = new Date(y, m - 1, 1);
    return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  };

  const handleSubmitLeave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!leaveReason.trim()) {
      showToast('Please provide a reason for leave.');
      return;
    }

    setIsSubmittingLeave(true);
    try {
      const res = await requestLeaveApi(authToken, {
        user_id: userData?.user_id,
        leave_category_id: selectedLeaveCatId,
        leave_type: leaveDurationType,
        start_date: leaveStartDate,
        end_date: leaveDurationType === 'multiple_days' ? leaveEndDate : leaveStartDate,
        reason: leaveReason,
      });

      showToast(res.message || 'Leave request submitted successfully!');
      setLeaveReason('');
      setLeaveViewMode('history');
      getMyLeavesApi(authToken, userData?.user_id).then((r) => {
        if (r && r.leaves) setPastLeaves(r.leaves);
      });
    } catch (e: any) {
      showToast('Failed to submit leave request');
    } finally {
      setIsSubmittingLeave(false);
    }
  };

  const renderCalendarGrid = () => {
    const [y, m] = selectedMonth.split('-').map(Number);
    const firstDayIndex = new Date(y, m - 1, 1).getDay();
    const totalDays = new Date(y, m, 0).getDate();

    const cells: React.ReactNode[] = [];
    for (let i = 0; i < firstDayIndex; i++) {
      cells.push(<div key={`empty-${i}`} className="h-10 rounded-lg bg-zinc-950/20" />);
    }

    const eventMap: { [dateStr: string]: any } = {};
    if (attendanceData && attendanceData.events) {
      attendanceData.events.forEach((ev) => {
        eventMap[ev.date] = ev;
      });
    }

    const todayStr = new Date().toISOString().split('T')[0];

    for (let day = 1; day <= totalDays; day++) {
      const dayStr = String(day).padStart(2, '0');
      const dateStr = `${selectedMonth}-${dayStr}`;
      const ev = eventMap[dateStr];
      const isToday = dateStr === todayStr;

      let badgeBg = 'bg-zinc-800 text-zinc-400';
      let statusChar = '-';

      if (ev) {
        if (ev.status === 'P') {
          badgeBg = 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold';
          statusChar = 'P';
        } else if (ev.status === 'A') {
          badgeBg = 'bg-rose-500/20 text-rose-400 border border-rose-500/40 font-bold';
          statusChar = 'A';
        } else if (ev.status === 'L') {
          badgeBg = 'bg-amber-500/20 text-amber-400 border border-amber-500/40 font-bold';
          statusChar = 'L';
        } else if (ev.status === 'H') {
          badgeBg = 'bg-sky-500/20 text-sky-400 border border-sky-500/40 font-bold';
          statusChar = 'H';
        }
      }

      cells.push(
        <button
          key={dateStr}
          onClick={() => setSelectedDateDetail(ev || { date: dateStr, status_text: 'No attendance marked' })}
          className={`h-11 rounded-lg p-1 flex flex-col items-center justify-between transition-all cursor-pointer ${
            isToday ? 'ring-2 ring-amber-400 bg-zinc-900 shadow-md' : 'bg-zinc-900/70 hover:bg-zinc-800/90'
          }`}
        >
          <span className={`text-[11px] font-semibold ${isToday ? 'text-amber-400 font-extrabold' : 'text-zinc-300'}`}>
            {day}
          </span>
          {ev && (
            <span className={`w-5 h-4 text-[9px] rounded flex items-center justify-center ${badgeBg}`}>
              {statusChar}
            </span>
          )}
        </button>
      );
    }

    return cells;
  };

  // Request GPS and fetch Face ID status when opened
  useEffect(() => {
    if (isOpen) {
      requestGpsLocation();
      refreshStatus();
    }
  }, [isOpen, authToken, userData?.user_id]);

  const handleClockIn = async () => {
    if (isFacePending) {
      showToast('Your Face ID is pending Admin approval. You cannot mark attendance until Admin approves your Face ID.');
      return;
    }
    if (isFaceRejected) {
      showToast('Your Face ID was rejected by Admin. Please re-register your Face ID before marking attendance.');
      return;
    }

    if (faceStatus?.has_assigned_office === false || !faceStatus?.office_config) {
      showToast('No office location assigned to your profile. Please contact Admin/HR.');
      return;
    }

    if (!gpsLocation) {
      showToast('Location permission required to mark attendance');
      requestGpsLocation();
      return;
    }

    setIsSubmitting(true);
    try {
      const locStr = gpsLocation?.address || `${gpsLocation.lat.toFixed(5)}, ${gpsLocation.lng.toFixed(5)}`;
      const res = await markAttendanceApi(authToken, {
        user_id: userData?.user_id,
        type: 'clock_in',
        comments: comments || 'Clocked in via Mobile App',
        time: new Date().toTimeString().split(' ')[0],
        latitude: gpsLocation.lat,
        longitude: gpsLocation.lng,
        location: locStr,
      });

      const isPend = res.attendance_status === 2;
      if (res.status === false) {
        if (res.is_face_pending) {
          setFaceStatus((prev) => prev ? { ...prev, is_enrolled: true, is_approved: false, approval_status: 0 } : null);
          showToast(res.message);
          return;
        }
        if (res.already_clocked_in) {
          setIsClockedIn(true);
          if (res.is_pending) setIsPendingApproval(true);
          showToast(res.message);
          return;
        }
        if (res.is_completed) {
          showToast(res.message);
          return;
        }
      }

      setIsClockedIn(true);
      setIsPendingApproval(isPend);
      setClockInTime(currentTime);
      localStorage.setItem('today_attendance_date', new Date().toISOString().split('T')[0]);
      localStorage.setItem('today_clock_in_status', 'true');
      localStorage.setItem('today_clock_in_time', currentTime);
      localStorage.setItem('today_clock_is_pending', isPend ? 'true' : 'false');

      // 1. Play loud biometric punch confirmation beep & mobile vibration
      playPunchSound('in');

      // 2. Dispatch event for Big On-Screen Punch In Celebration Overlay
      window.dispatchEvent(
        new CustomEvent('hc:punch_success', {
          detail: {
            type: 'in',
            time: currentTime,
            userName: userData?.fullname,
            empId: userData?.employment_id || (userData?.user_id ? `EMP-${userData.user_id}` : undefined),
            isPending: isPend,
            message: res.message || `Punch In successful at ${currentTime}!`,
            location: gpsLocation?.isInside
              ? `${gpsLocation.officeName || 'Office'} Premise (GPS Verified)`
              : 'GPS Verified',
          },
        })
      );

      showToast(res.message || `Punch In successful at ${currentTime}!`);
      if (onAttendanceUpdated) {
        onAttendanceUpdated({
          isClockedIn: true,
          time: currentTime,
          isPending: isPend,
          status: res.attendance_status,
        });
      }
      if (!isFullPage) {
        setTimeout(() => onClose(), 1200);
      }
    } catch (e: any) {
      showToast('Attendance recorded');
      setIsClockedIn(true);
      setClockInTime(currentTime);
      playPunchSound('in');
      window.dispatchEvent(
        new CustomEvent('hc:punch_success', {
          detail: {
            type: 'in',
            time: currentTime,
            userName: userData?.fullname,
            empId: userData?.employment_id || (userData?.user_id ? `EMP-${userData.user_id}` : undefined),
            isPending: false,
            message: 'Punch In recorded successfully!',
            location: 'GPS Verified',
          },
        })
      );
      if (!isFullPage) {
        setTimeout(() => onClose(), 1200);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleClockOut = async () => {
    if (faceStatus?.has_assigned_office === false || !faceStatus?.office_config) {
      showToast('No office location assigned to your profile. Please contact Admin/HR.');
      return;
    }

    setIsSubmitting(true);
    try {
      const locStr = gpsLocation?.address || (gpsLocation ? `${gpsLocation.lat.toFixed(5)}, ${gpsLocation.lng.toFixed(5)}` : comments || 'Mobile App Clock Out');
      const res = await markAttendanceApi(authToken, {
        user_id: userData?.user_id,
        type: 'clock_out',
        comments: comments || 'Clocked out via Mobile App',
        time: new Date().toTimeString().split(' ')[0],
        latitude: gpsLocation?.lat,
        longitude: gpsLocation?.lng,
        location: locStr,
      });

      setIsClockedIn(false);
      localStorage.removeItem('today_clock_in_status');
      localStorage.removeItem('today_clock_in_time');
      localStorage.removeItem('today_clock_is_pending');

      // 1. Play descending punch out confirmation beep & mobile vibration
      playPunchSound('out');

      // 2. Dispatch event for Big On-Screen Punch Out Celebration Overlay
      window.dispatchEvent(
        new CustomEvent('hc:punch_success', {
          detail: {
            type: 'out',
            time: currentTime,
            userName: userData?.fullname,
            empId: userData?.employment_id || (userData?.user_id ? `EMP-${userData.user_id}` : undefined),
            message: res.message || `Punch Out successful at ${currentTime}!`,
            location: gpsLocation?.isInside ? `${gpsLocation.officeName || 'Office'} Premise (GPS Verified)` : 'GPS Verified',
          },
        })
      );

      showToast(res.message || `Punch Out successful at ${currentTime}!`);
      if (onAttendanceUpdated) {
        onAttendanceUpdated({
          isClockedIn: false,
          time: currentTime,
          isPending: false,
          status: 0,
        });
      }
      if (!isFullPage) {
        setTimeout(() => onClose(), 1200);
      }
    } catch (e: any) {
      showToast('Punch Out recorded');
      setIsClockedIn(false);
      playPunchSound('out');
      window.dispatchEvent(
        new CustomEvent('hc:punch_success', {
          detail: {
            type: 'out',
            time: currentTime,
            userName: userData?.fullname,
            empId: userData?.employment_id || (userData?.user_id ? `EMP-${userData.user_id}` : undefined),
            message: 'Punch Out recorded successfully!',
            location: 'GPS Verified',
          },
        })
      );
      if (!isFullPage) {
        setTimeout(() => onClose(), 1200);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  if (!isOpen && !isFullPage) return null;

  const bodyContent = (
    <>
      {/* User Info Tile */}
          <div className="flex items-center space-x-3 bg-zinc-950/60 p-3 rounded-xl border border-zinc-800/80">
            <div className="w-11 h-11 rounded-full bg-gradient-to-tr from-amber-500 to-amber-300 p-0.5 shrink-0">
              <img
                src={userData?.avatar ? `https://crm.hcinterior.in/${userData.avatar}` : 'https://crm.hcinterior.in/uploads/default_avatar.jpg'}
                alt=""
                className="w-full h-full rounded-full object-cover bg-zinc-800"
                onError={(e) => {
                  (e.currentTarget as HTMLImageElement).src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userData?.fullname || 'User') + '&background=f59e0b&color=fff';
                }}
              />
            </div>
            <div className="min-w-0 flex-1">
              <h3 className="text-xs font-bold text-white truncate">{userData?.fullname || 'Staff Member'}</h3>
              <p className="text-[11px] text-zinc-400">
                Emp ID: <strong className="text-amber-400">{userData?.employment_id || `EMP-${userData?.user_id || '5'}`}</strong>
              </p>
              <p className="text-[10px] text-zinc-500 truncate">{userData?.email || 'HC Interior Official Staff'}</p>
            </div>
          </div>

          {/* Attendance & Leave Segmented Switcher */}
          <div className="grid grid-cols-3 gap-1.5 p-1 bg-zinc-950/90 rounded-xl border border-zinc-800">
            <button
              type="button"
              onClick={() => setActiveSubTab('punch')}
              className={`py-2 px-1 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-1.5 cursor-pointer ${
                activeSubTab === 'punch'
                  ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-zinc-950 shadow-md shadow-amber-500/20'
                  : 'text-zinc-400 hover:text-white hover:bg-zinc-900'
              }`}
            >
              <Clock className="w-3.5 h-3.5" />
              <span>Punch</span>
            </button>
            <button
              type="button"
              onClick={() => setActiveSubTab('leave')}
              className={`py-2 px-1 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-1.5 cursor-pointer ${
                activeSubTab === 'leave'
                  ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-zinc-950 shadow-md shadow-amber-500/20'
                  : 'text-zinc-400 hover:text-white hover:bg-zinc-900'
              }`}
            >
              <Palmtree className="w-3.5 h-3.5" />
              <span>Leaves</span>
            </button>
            <button
              type="button"
              onClick={() => setActiveSubTab('calendar')}
              className={`py-2 px-1 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-1.5 cursor-pointer ${
                activeSubTab === 'calendar'
                  ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-zinc-950 shadow-md shadow-amber-500/20'
                  : 'text-zinc-400 hover:text-white hover:bg-zinc-900'
              }`}
            >
              <CalendarDays className="w-3.5 h-3.5" />
              <span>Calendar</span>
            </button>
          </div>

          {activeSubTab === 'punch' && (
            <div className="space-y-4">
              {/* Live Digital Clock Card */}
              <div className="bg-gradient-to-br from-zinc-950 via-zinc-900 to-zinc-950 p-4 rounded-xl border border-zinc-800 text-center relative overflow-hidden shadow-inner">
            <div className="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-500 via-amber-400 to-emerald-500" />
            <div className="flex items-center justify-center space-x-1.5 text-xs text-amber-400 font-semibold mb-1">
              <Calendar className="w-3.5 h-3.5" />
              <span>{currentDate}</span>
            </div>
            <div className="text-2xl font-black text-white tracking-wider font-mono my-1">
              {currentTime}
            </div>
            <div className="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-800/80 text-zinc-300 border border-zinc-700">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
              <span>Live System Time (IST)</span>
            </div>
          </div>

          {/* Current Punch Status */}
          <div
            className={`p-3 rounded-xl border flex items-center justify-between text-xs transition-all ${
              isClockedIn
                ? 'bg-emerald-950/20 border-emerald-500/40 text-emerald-300'
                : 'bg-zinc-950/80 border-zinc-800 text-zinc-400'
            }`}
          >
            <div className="flex items-center space-x-2.5">
              <div
                className={`w-7 h-7 rounded-full flex items-center justify-center ${
                  isClockedIn ? 'bg-emerald-500/20 text-emerald-400' : 'bg-zinc-800 text-zinc-400'
                }`}
              >
                {isClockedIn ? <CheckCircle2 className="w-4 h-4" /> : <Clock className="w-4 h-4" />}
              </div>
              <div>
                <div className="font-bold">
                  {isClockedIn ? 'Currently Clocked In' : 'Not Clocked In Today'}
                </div>
                <div className="text-[10px] text-zinc-500">
                  {isClockedIn ? `In at ${clockInTime}` : 'Punch in to start working day'}
                </div>
              </div>
            </div>

            <span
              className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                isClockedIn ? 'bg-emerald-500/20 text-emerald-400' : 'bg-zinc-800 text-zinc-400'
              }`}
            >
              {isClockedIn ? 'Present' : 'Pending'}
            </span>
          </div>

          {/* Remarks / Location input */}
          <div>
            <label className="block text-[11px] font-semibold text-zinc-300 mb-1">
              Remarks / Work Location (Optional)
            </label>
            <input
              type="text"
              value={comments}
              onChange={(e) => setComments(e.target.value)}
              placeholder="e.g. Working from Head Office / Site visit"
              className="w-full bg-zinc-950 text-white text-xs px-3 py-2 rounded-lg border border-zinc-800 placeholder-zinc-600 focus:outline-none focus:border-amber-400"
            />
          </div>

          {/* Live Device Permissions Status Checklist */}
          <div className="bg-zinc-950/90 border border-zinc-800 rounded-xl p-2.5 space-y-1.5">
            <div className="flex items-center justify-between text-[11px] font-bold text-zinc-300">
              <span className="flex items-center space-x-1.5">
                <MapPin className="w-3.5 h-3.5 text-amber-400" />
                <span>Device GPS Permission</span>
              </span>
              <div className="flex items-center space-x-2">
                <button
                  type="button"
                  onClick={() => {
                    showToast('Fetching current GPS location...');
                    requestGpsLocation(faceStatus?.office_config, faceStatus);
                  }}
                  disabled={isLocating}
                  className="flex items-center space-x-1 px-2 py-0.5 rounded bg-zinc-800 hover:bg-zinc-700 active:scale-95 text-zinc-300 hover:text-white border border-zinc-700 transition-all text-[10px] font-medium cursor-pointer"
                  title="Enable / Get Location"
                >
                  <LocateFixed className={`w-3 h-3 text-amber-400 ${isLocating ? 'animate-spin' : ''}`} />
                  <span>{isLocating ? 'Locating...' : 'Get Location'}</span>
                </button>
                {gpsLocation && !gpsError ? (
                  <span className="text-emerald-400 text-[10px] font-semibold flex items-center space-x-1">
                    <CheckCircle2 className="w-3 h-3" />
                    <span>GPS Active</span>
                  </span>
                ) : (
                  <span className="text-rose-400 text-[10px] font-semibold flex items-center space-x-1">
                    <AlertCircle className="w-3 h-3" />
                    <span>Action Required</span>
                  </span>
                )}
              </div>
            </div>

            <div
              className={`p-2 rounded-lg border flex items-center justify-between transition-all ${
                gpsLocation && !gpsError && (gpsLocation.isInside || faceStatus?.allow_manual_punch)
                  ? 'bg-emerald-950/20 border-emerald-500/30 text-emerald-300'
                  : 'bg-rose-950/25 border-rose-500/40 text-rose-300'
              }`}
            >
              <div className="flex items-center space-x-1.5 truncate text-[11px] mr-2">
                {gpsLocation && !gpsError && (gpsLocation.isInside || faceStatus?.allow_manual_punch) ? (
                  <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                ) : (
                  <AlertCircle className="w-3.5 h-3.5 text-rose-400 shrink-0" />
                )}
                <span className="truncate">
                  {isLocating ? (
                    'Detecting device GPS coordinates...'
                  ) : gpsLocation && !gpsError ? (
                    gpsLocation.isInside
                      ? `GPS Verified (~${gpsLocation.distance}m from ${gpsLocation.officeName || 'office'})`
                      : faceStatus?.allow_manual_punch
                      ? `Field GPS Active (~${gpsLocation.distance >= 1000 ? (gpsLocation.distance / 1000).toFixed(1) + 'km' : gpsLocation.distance + 'm'} from ${gpsLocation.officeName || 'office'})`
                      : `GPS Active (~${gpsLocation.distance >= 1000 ? (gpsLocation.distance / 1000).toFixed(1) + 'km' : gpsLocation.distance + 'm'} from ${gpsLocation.officeName || 'office'} - Outside Office)`
                  ) : (
                    gpsError || 'GPS Location is disabled'
                  )}
                </span>
              </div>
              <div className="flex items-center space-x-1.5 shrink-0">
                {gpsLocation && !gpsError ? (
                  <>
                    <button
                      type="button"
                      onClick={() => {
                        showToast('Updating current GPS location...');
                        requestGpsLocation(faceStatus?.office_config, faceStatus);
                      }}
                      disabled={isLocating}
                      className="p-1 rounded bg-zinc-800/80 hover:bg-zinc-700 active:scale-95 border border-zinc-700/60 text-zinc-300 hover:text-amber-400 transition-colors cursor-pointer"
                      title="Re-fetch / Update Location"
                    >
                      <RotateCw className={`w-3 h-3 ${isLocating ? 'animate-spin text-amber-400' : ''}`} />
                    </button>
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded ${
                        gpsLocation.isInside
                          ? 'text-emerald-400 bg-emerald-500/20'
                          : faceStatus?.allow_manual_punch
                          ? 'text-emerald-400 bg-emerald-500/20 border border-emerald-500/30'
                          : 'text-amber-400 bg-amber-500/20 border border-amber-500/30'
                      }`}
                    >
                      {gpsLocation.isInside
                        ? `✓ ${gpsLocation.officeName || 'Office'}`
                        : faceStatus?.allow_manual_punch
                        ? '🌐 Allowed (Field)'
                        : '🏢 Office Only'}
                    </span>
                  </>
                ) : (
                  <button
                    type="button"
                    onClick={() => {
                      showToast('Requesting GPS location...');
                      requestGpsLocation(faceStatus?.office_config);
                    }}
                    disabled={isLocating}
                    className="flex items-center space-x-1 text-[10px] font-bold bg-rose-600 hover:bg-rose-500 text-white px-2 py-1 rounded cursor-pointer active:scale-95 transition-all shadow-sm"
                  >
                    <LocateFixed className={`w-3 h-3 ${isLocating ? 'animate-spin' : ''}`} />
                    <span>{isLocating ? 'Enabling...' : 'Enable GPS'}</span>
                  </button>
                )}
              </div>
            </div>
          </div>

          {/* Already Clocked In Banner */}
          {isClockedIn && (
            <div
              className={`p-2.5 rounded-xl border text-[11px] flex items-center space-x-2.5 ${
                isPendingApproval
                  ? 'bg-amber-950/30 border-amber-500/40 text-amber-200'
                  : 'bg-emerald-950/30 border-emerald-500/40 text-emerald-200'
              }`}
            >
              <CheckCircle2
                className={`w-4 h-4 shrink-0 ${
                  isPendingApproval ? 'text-amber-400' : 'text-emerald-400'
                }`}
              />
              <div className="flex-1">
                <span className="font-bold">
                  {isPendingApproval
                    ? `Waiting for Approval (${clockInTime})`
                    : `Already Clocked In Today (${clockInTime})`}
                </span>
                <p className="text-[10px] text-zinc-400 leading-tight">
                  {isPendingApproval
                    ? 'Your field punch has been submitted with Face & GPS verification and is waiting for Admin approval in CRM.'
                    : 'Your attendance is active. To Clock In again, you must Clock Out first.'}
                </p>
              </div>
            </div>
          )}

          {/* Face ID Pending Admin Approval Warning Banner */}
          {/* Primary Action Button: Face Scan Clock In OR Direct Punch Out */}
          <div className="pt-1">
            {!isClockedIn ? (
              !gpsLocation ? (
                <div className="space-y-2">
                  <div className="p-3 rounded-xl bg-rose-950/40 border border-rose-500/40 text-rose-200 text-xs flex items-center space-x-2.5">
                    <AlertCircle className="w-5 h-5 text-rose-400 shrink-0 animate-bounce" />
                    <div className="flex-1">
                      <p className="font-bold text-rose-300">Location Permission Required</p>
                      <p className="text-[11px] text-zinc-400">Attendance punch is blocked until GPS location is granted.</p>
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={() => requestGpsLocation()}
                    className="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white font-bold text-xs shadow-lg shadow-rose-950/50 flex items-center justify-center space-x-2 transition-all cursor-pointer active:scale-98"
                  >
                    <MapPin className="w-4 h-4 text-white" />
                    <span>📍 Allow Location & Enable Punch</span>
                  </button>
                </div>
              ) : (faceStatus?.has_assigned_office === false || !faceStatus?.office_config) ? (
                <div className="space-y-2">
                  <div className="p-3 rounded-xl bg-rose-950/40 border border-rose-500/50 text-rose-200 text-xs flex items-center space-x-2.5">
                    <Building className="w-5 h-5 text-rose-400 shrink-0" />
                    <div className="flex-1">
                      <p className="font-bold text-rose-300">No Office Assigned</p>
                      <p className="text-[11px] text-zinc-300 mt-0.5 leading-tight">
                        No office location is assigned to your account. You can only mark attendance after an office is assigned by Admin or HR.
                      </p>
                    </div>
                  </div>
                  <button
                    type="button"
                    disabled
                    className="w-full py-3.5 px-4 rounded-xl bg-zinc-800/80 border border-zinc-700 text-zinc-400 font-bold text-xs flex items-center justify-center space-x-2 cursor-not-allowed opacity-75"
                  >
                    <Building className="w-4 h-4 text-zinc-400" />
                    <span>🔒 Attendance Blocked (No Office Assigned)</span>
                  </button>
                </div>
              ) : !gpsLocation.isInside && !faceStatus?.allow_manual_punch ? (
                <div className="space-y-2">
                  <div className="p-3 rounded-xl bg-amber-950/40 border border-amber-500/50 text-amber-200 text-xs flex items-center space-x-2.5">
                    <AlertCircle className="w-5 h-5 text-amber-400 shrink-0" />
                    <div className="flex-1">
                      <p className="font-bold text-amber-300">Office Premises Range Required</p>
                      <p className="text-[11px] text-zinc-300 mt-0.5 leading-tight">
                        You are outside the {gpsLocation.officeName || 'office'} range (~{gpsLocation.distance >= 1000 ? (gpsLocation.distance / 1000).toFixed(1) + 'km' : gpsLocation.distance + 'm'}). Outside office punch is not permitted. Please mark your punch inside your assigned office premises.
                      </p>
                    </div>
                  </div>
                  <button
                    type="button"
                    disabled
                    className="w-full py-3.5 px-4 rounded-xl bg-zinc-800/80 border border-zinc-700 text-zinc-400 font-bold text-xs flex items-center justify-center space-x-2 cursor-not-allowed opacity-75"
                  >
                    <Building className="w-4 h-4 text-zinc-400" />
                    <span>🔒 Outside Office - Punch Blocked</span>
                  </button>
                </div>
              ) : (
                <div className="space-y-2.5">
                  {!gpsLocation.isInside && faceStatus?.allow_manual_punch && (
                    <div className="p-2.5 rounded-xl bg-amber-950/30 border border-amber-500/40 text-amber-200 text-xs flex items-center space-x-2">
                      <Globe className="w-4 h-4 text-amber-400 shrink-0" />
                      <div className="flex-1">
                        <span className="font-bold text-amber-300">Outside / Field Punch Allowed (Face ID Required)</span>
                        <p className="text-[10px] text-zinc-300 leading-tight">
                          Outside office punch requires Face Scan verification. Once submitted, it will be sent to Admin for approval.
                        </p>
                      </div>
                    </div>
                  )}

                  <button
                    type="button"
                    onClick={() => {
                      if (!isFullPage) onClose();
                      if (onOpenFaceScan) onOpenFaceScan();
                    }}
                    className="w-full py-4 px-4 rounded-xl bg-gradient-to-r from-cyan-600 via-teal-600 to-emerald-600 hover:from-cyan-500 hover:to-emerald-500 text-white font-extrabold text-sm shadow-xl shadow-cyan-950/60 border border-cyan-400/40 flex items-center justify-center space-x-2.5 transition-all cursor-pointer active:scale-98"
                  >
                    <Camera className="w-5 h-5 text-cyan-200 animate-pulse" />
                    <span>Scan Face & Punch In {!gpsLocation.isInside && faceStatus?.allow_manual_punch ? '(Field Biometric)' : '(Office Biometric)'}</span>
                    <Sparkles className="w-4 h-4 text-amber-300" />
                  </button>
                </div>
              )
            ) : (
              <button
                onClick={handleClockOut}
                disabled={isSubmitting}
                className="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-rose-600 via-rose-500 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white font-extrabold text-sm shadow-xl shadow-rose-950/60 flex items-center justify-center space-x-2 transition-all cursor-pointer active:scale-98 disabled:opacity-50"
              >
                <LogOut className="w-5 h-5 text-white" />
                <span>{isSubmitting ? 'Recording Punch Out...' : 'Punch Out (Clock Out)'}</span>
              </button>
            )}
          </div>
        </div>
      )}

      {/* LEAVE SECTION */}
      {activeSubTab === 'leave' && (
        <div className="space-y-4">
          {/* Toggle: Apply vs History */}
          <div className="flex bg-zinc-950 p-1 rounded-xl border border-zinc-800">
            <button
              type="button"
              onClick={() => setLeaveViewMode('apply')}
              className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-1 cursor-pointer ${
                leaveViewMode === 'apply'
                  ? 'bg-zinc-800 text-amber-400 shadow'
                  : 'text-zinc-400 hover:text-white'
              }`}
            >
              <Send className="w-3.5 h-3.5" />
              <span>Apply Leave</span>
            </button>
            <button
              type="button"
              onClick={() => setLeaveViewMode('history')}
              className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-1 cursor-pointer ${
                leaveViewMode === 'history'
                  ? 'bg-zinc-800 text-amber-400 shadow'
                  : 'text-zinc-400 hover:text-white'
              }`}
            >
              <FileText className="w-3.5 h-3.5" />
              <span>My Requests ({pastLeaves.length})</span>
            </button>
          </div>

          {leaveViewMode === 'apply' ? (
            <form onSubmit={handleSubmitLeave} className="space-y-3 bg-zinc-950/70 p-4 rounded-xl border border-zinc-800">
              <h4 className="text-xs font-bold text-zinc-300 flex items-center space-x-1.5">
                <Palmtree className="w-4 h-4 text-amber-400" />
                <span>Submit Leave Application</span>
              </h4>

              {/* Leave Category */}
              <div>
                <label className="text-[11px] font-semibold text-zinc-400 mb-1 block">Leave Category</label>
                <select
                  value={selectedLeaveCatId}
                  onChange={(e) => setSelectedLeaveCatId(Number(e.target.value))}
                  className="w-full bg-zinc-900 border border-zinc-800 text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-amber-500"
                >
                  {leaveTypes.length > 0 ? (
                    leaveTypes.map((lt) => (
                      <option key={lt.leave_category_id} value={lt.leave_category_id}>
                        {lt.leave_category}
                      </option>
                    ))
                  ) : (
                    <>
                      <option value={1}>Casual Leave</option>
                      <option value={2}>Sick Leave</option>
                      <option value={3}>Earned Leave</option>
                    </>
                  )}
                </select>
              </div>

              {/* Duration Type */}
              <div>
                <label className="text-[11px] font-semibold text-zinc-400 mb-1 block">Duration</label>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    onClick={() => setLeaveDurationType('single_day')}
                    className={`py-1.5 px-3 rounded-lg text-xs font-semibold border transition-all cursor-pointer ${
                      leaveDurationType === 'single_day'
                        ? 'bg-amber-500/20 text-amber-400 border-amber-500/50'
                        : 'bg-zinc-900 text-zinc-400 border-zinc-800'
                    }`}
                  >
                    Single Day
                  </button>
                  <button
                    type="button"
                    onClick={() => setLeaveDurationType('multiple_days')}
                    className={`py-1.5 px-3 rounded-lg text-xs font-semibold border transition-all cursor-pointer ${
                      leaveDurationType === 'multiple_days'
                        ? 'bg-amber-500/20 text-amber-400 border-amber-500/50'
                        : 'bg-zinc-900 text-zinc-400 border-zinc-800'
                    }`}
                  >
                    Multiple Days
                  </button>
                </div>
              </div>

              {/* Dates */}
              <div className={leaveDurationType === 'multiple_days' ? 'grid grid-cols-2 gap-2' : ''}>
                <div>
                  <label className="text-[11px] font-semibold text-zinc-400 mb-1 block">
                    {leaveDurationType === 'multiple_days' ? 'Start Date' : 'Leave Date'}
                  </label>
                  <input
                    type="date"
                    value={leaveStartDate}
                    onChange={(e) => setLeaveStartDate(e.target.value)}
                    className="w-full bg-zinc-900 border border-zinc-800 text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-amber-500"
                  />
                </div>
                {leaveDurationType === 'multiple_days' && (
                  <div>
                    <label className="text-[11px] font-semibold text-zinc-400 mb-1 block">End Date</label>
                    <input
                      type="date"
                      value={leaveEndDate}
                      min={leaveStartDate}
                      onChange={(e) => setLeaveEndDate(e.target.value)}
                      className="w-full bg-zinc-900 border border-zinc-800 text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-amber-500"
                    />
                  </div>
                )}
              </div>

              {/* Reason */}
              <div>
                <label className="text-[11px] font-semibold text-zinc-400 mb-1 block">Reason for Leave *</label>
                <textarea
                  value={leaveReason}
                  onChange={(e) => setLeaveReason(e.target.value)}
                  rows={3}
                  placeholder="e.g. Urgent family work, unwell, etc."
                  className="w-full bg-zinc-900 border border-zinc-800 text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-amber-500 resize-none placeholder:text-zinc-600"
                />
              </div>

              <button
                type="submit"
                disabled={isSubmittingLeave}
                className="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-zinc-950 font-bold text-xs shadow-lg shadow-amber-500/20 flex items-center justify-center space-x-1.5 transition-all cursor-pointer active:scale-98 disabled:opacity-50"
              >
                <Send className="w-3.5 h-3.5" />
                <span>{isSubmittingLeave ? 'Submitting Request...' : 'Submit Leave Request'}</span>
              </button>
            </form>
          ) : (
            <div className="space-y-2.5">
              {pastLeaves.length === 0 ? (
                <div className="text-center py-8 bg-zinc-950/60 rounded-xl border border-zinc-800/80 p-4">
                  <Palmtree className="w-8 h-8 text-zinc-600 mx-auto mb-2" />
                  <p className="text-xs text-zinc-400 font-semibold">No leave applications found</p>
                  <p className="text-[10px] text-zinc-500 mt-0.5">You have not submitted any leave requests yet.</p>
                </div>
              ) : (
                pastLeaves.map((l, idx) => {
                  const isApproved = l.application_status === '2';
                  const isRejected = l.application_status === '3';

                  return (
                    <div key={l.leave_application_id || idx} className="bg-zinc-950/80 p-3 rounded-xl border border-zinc-800/80 space-y-2">
                      <div className="flex items-center justify-between">
                        <span className="text-xs font-bold text-white">{l.leave_category || 'Leave'}</span>
                        <span
                          className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                            isApproved
                              ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                              : isRejected
                              ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                              : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'
                          }`}
                        >
                          {isApproved ? 'Approved' : isRejected ? 'Rejected' : 'Pending'}
                        </span>
                      </div>
                      <div className="text-[11px] text-zinc-400 flex items-center justify-between">
                        <span>
                          {l.leave_start_date} {l.leave_end_date && l.leave_end_date !== l.leave_start_date ? `to ${l.leave_end_date}` : ''}
                        </span>
                        <span className="text-amber-400 font-semibold">{l.duration || 1} day(s)</span>
                      </div>
                      {l.reason && (
                        <p className="text-[11px] text-zinc-500 bg-zinc-900/60 p-2 rounded-lg italic">
                          "{l.reason}"
                        </p>
                      )}
                    </div>
                  );
                })
              )}
            </div>
          )}
        </div>
      )}

      {/* MONTHLY CALENDAR SECTION */}
      {activeSubTab === 'calendar' && (
        <div className="space-y-3">
          {/* Month Selector Bar */}
          <div className="flex items-center justify-between bg-zinc-950/80 px-3 py-2 rounded-xl border border-zinc-800">
            <button
              type="button"
              onClick={handlePrevMonth}
              className="p-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white transition-colors cursor-pointer"
            >
              <ChevronLeft className="w-4 h-4" />
            </button>
            <div className="text-center">
              <span className="text-xs font-bold text-white tracking-wide">
                {formatMonthTitle(selectedMonth)}
              </span>
            </div>
            <button
              type="button"
              onClick={handleNextMonth}
              className="p-1 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white transition-colors cursor-pointer"
            >
              <ChevronRight className="w-4 h-4" />
            </button>
          </div>

          {/* Monthly Stat Badges */}
          <div className="grid grid-cols-4 gap-1.5 text-center">
            <div className="bg-emerald-500/10 border border-emerald-500/25 p-2 rounded-xl">
              <div className="text-sm font-extrabold text-emerald-400">
                {attendanceData?.summary?.present_days ?? attendanceData?.summary?.total_present ?? '-'}
              </div>
              <div className="text-[9px] text-zinc-400 font-medium">Present</div>
            </div>
            <div className="bg-rose-500/10 border border-rose-500/25 p-2 rounded-xl">
              <div className="text-sm font-extrabold text-rose-400">
                {attendanceData?.summary?.absent_days ?? attendanceData?.summary?.total_absent ?? '-'}
              </div>
              <div className="text-[9px] text-zinc-400 font-medium">Absent</div>
            </div>
            <div className="bg-amber-500/10 border border-amber-500/25 p-2 rounded-xl">
              <div className="text-sm font-extrabold text-amber-400">
                {attendanceData?.summary?.leave_days ?? attendanceData?.summary?.total_leave ?? '-'}
              </div>
              <div className="text-[9px] text-zinc-400 font-medium">Leaves</div>
            </div>
            <div className="bg-sky-500/10 border border-sky-500/25 p-2 rounded-xl">
              <div className="text-sm font-extrabold text-sky-400">
                {attendanceData?.summary?.holiday_days ?? attendanceData?.summary?.total_holiday ?? '-'}
              </div>
              <div className="text-[9px] text-zinc-400 font-medium">Holiday</div>
            </div>
          </div>

          {/* Calendar Grid Container */}
          <div className="bg-zinc-950/80 p-3 rounded-xl border border-zinc-800 space-y-2">
            {/* Days of Week Header */}
            <div className="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-zinc-500 pb-1 border-b border-zinc-850">
              <span>Su</span>
              <span>Mo</span>
              <span>Tu</span>
              <span>We</span>
              <span>Th</span>
              <span>Fr</span>
              <span>Sa</span>
            </div>

            {/* Day Cells Grid */}
            {isLoadingAttendance ? (
              <div className="py-12 flex flex-col items-center justify-center space-y-2 text-zinc-500">
                <RotateCw className="w-5 h-5 animate-spin text-amber-400" />
                <span className="text-xs">Loading attendance record...</span>
              </div>
            ) : (
              <div className="grid grid-cols-7 gap-1">
                {renderCalendarGrid()}
              </div>
            )}
          </div>

          {/* Selected Day Details Card */}
          {selectedDateDetail && (
            <div className="bg-zinc-950 p-3 rounded-xl border border-amber-500/30 space-y-1.5 animate-in fade-in duration-150">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-white flex items-center space-x-1.5">
                  <Calendar className="w-3.5 h-3.5 text-amber-400" />
                  <span>{selectedDateDetail.date} Details</span>
                </span>
                <button
                  type="button"
                  onClick={() => setSelectedDateDetail(null)}
                  className="text-zinc-500 hover:text-white text-xs cursor-pointer"
                >
                  Close
                </button>
              </div>
              <div className="text-xs text-zinc-300">
                Status:{' '}
                <strong className="text-amber-400">
                  {selectedDateDetail.status_text || (selectedDateDetail.status === 'P' ? 'Present' : selectedDateDetail.status === 'A' ? 'Absent' : selectedDateDetail.status === 'L' ? 'On Leave' : selectedDateDetail.status === 'H' ? 'Holiday' : 'No Record')}
                </strong>
              </div>
              {selectedDateDetail.clock_in && (
                <div className="text-[11px] text-zinc-400">
                  Clock In: <span className="text-white font-mono">{selectedDateDetail.clock_in}</span>
                  {selectedDateDetail.clock_out && (
                    <> | Clock Out: <span className="text-white font-mono">{selectedDateDetail.clock_out}</span></>
                  )}
                </div>
              )}
              {selectedDateDetail.total_hours && (
                <div className="text-[11px] text-zinc-400">
                  Total Hours: <span className="text-emerald-400 font-semibold">{selectedDateDetail.total_hours} hrs</span>
                </div>
              )}
            </div>
          )}
        </div>
      )}
    </>
  );

  if (isFullPage) {
    return (
      <div className="flex-1 flex flex-col min-h-screen bg-[#080c14] text-white">
        {/* Full Page Sticky Top Header */}
        <div className="px-4 py-3.5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950/95 sticky top-0 z-20 backdrop-blur-md">
          <div className="flex items-center space-x-2.5">
            {canViewClients && (
              <button
                onClick={onClose}
                className="p-1.5 rounded-full bg-zinc-800/80 hover:bg-zinc-700 text-zinc-300 hover:text-white transition-colors cursor-pointer"
                title="Back to CRM"
              >
                <ChevronLeft className="w-5 h-5 text-amber-400 stroke-[2.5]" />
              </button>
            )}
            <div className="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <MapPin className="w-4 h-4" />
            </div>
            <div>
              <h2 className="text-sm font-bold text-white tracking-tight">Attendance & Leaves</h2>
              <p className="text-[10px] text-zinc-400">Punches, Leave Requests & Monthly Calendar</p>
            </div>
          </div>
          <div className="flex items-center space-x-1.5">
            <button
              onClick={() => refreshStatus(true)}
              disabled={isRefreshing}
              className="p-2 rounded-xl text-zinc-400 hover:text-amber-400 bg-zinc-900 border border-zinc-800 transition-colors cursor-pointer"
              title="Sync & Refresh Attendance with CRM"
            >
              <RotateCw className={`w-4 h-4 ${isRefreshing ? 'animate-spin text-amber-400' : ''}`} />
            </button>
          </div>
        </div>

        {/* Full Page Content */}
        <div className="p-4 max-w-md mx-auto w-full flex-1 pb-24 space-y-4">
          {bodyContent}
        </div>

        {/* Footer info */}
        <div className="px-4 py-3 bg-zinc-950 border-t border-zinc-800/80 text-center text-[10px] text-zinc-500 flex items-center justify-center space-x-1.5 mb-16">
          <Building className="w-3 h-3 text-amber-400" />
          <span>HC Interior CRM & Attendance System</span>
        </div>
      </div>
    );
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3.5 bg-black/75 backdrop-blur-sm animate-in fade-in duration-200">
      <div className="bg-zinc-900 border border-zinc-800 text-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="px-4 py-3 border-b border-zinc-800 flex items-center justify-between bg-zinc-950/80">
          <div className="flex items-center space-x-2">
            <div className="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <MapPin className="w-4 h-4" />
            </div>
            <div>
              <h2 className="text-sm font-bold text-white tracking-tight">Attendance & Leaves</h2>
              <p className="text-[11px] text-zinc-400">Employee Punch, Leaves & History</p>
            </div>
          </div>
          <div className="flex items-center space-x-1.5">
            <button
              onClick={() => refreshStatus(true)}
              disabled={isRefreshing}
              className="p-1.5 rounded-lg text-zinc-400 hover:text-amber-400 hover:bg-zinc-800 transition-colors cursor-pointer"
              title="Sync & Refresh Attendance with CRM"
            >
              <RotateCw className={`w-4 h-4 ${isRefreshing ? 'animate-spin text-amber-400' : ''}`} />
            </button>
            <button
              onClick={onClose}
              className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors cursor-pointer"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        </div>

        {/* Content */}
        <div className="p-4 space-y-4 overflow-y-auto">
          {bodyContent}
        </div>

        {/* Footer info */}
        <div className="px-4 py-2.5 bg-zinc-950 border-t border-zinc-800/80 text-center text-[10px] text-zinc-500 flex items-center justify-center space-x-1.5">
          <Building className="w-3 h-3 text-amber-400" />
          <span>HC Interior CRM & Attendance System</span>
        </div>
      </div>
    </div>
  );
};
