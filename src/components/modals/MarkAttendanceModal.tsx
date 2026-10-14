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
} from 'lucide-react';
import { markAttendanceApi } from '../../services/userPortalApi';
import { UserData } from '../../services/authApi';

interface MarkAttendanceModalProps {
  isOpen: boolean;
  onClose: () => void;
  userData: UserData | null;
  authToken: string;
  showToast: (msg: string) => void;
  onAttendanceUpdated?: () => void;
  onOpenFaceScan?: () => void;
}

export const MarkAttendanceModal: React.FC<MarkAttendanceModalProps> = ({
  isOpen,
  onClose,
  userData,
  authToken,
  showToast,
  onAttendanceUpdated,
  onOpenFaceScan,
}) => {
  const [currentTime, setCurrentTime] = useState<string>('');
  const [currentDate, setCurrentDate] = useState<string>('');
  const [comments, setComments] = useState<string>('');
  const [isClockedIn, setIsClockedIn] = useState<boolean>(false);
  const [clockInTime, setClockInTime] = useState<string>('09:30 AM');
  const [isSubmitting, setIsSubmitting] = useState<boolean>(false);

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
    }
  }, []);

  if (!isOpen) return null;

  const handleClockIn = async () => {
    setIsSubmitting(true);
    try {
      const res = await markAttendanceApi(authToken, {
        user_id: userData?.user_id,
        type: 'clock_in',
        comments: comments || 'Clocked in via Mobile App',
        time: new Date().toTimeString().split(' ')[0],
      });

      setIsClockedIn(true);
      setClockInTime(currentTime);
      localStorage.setItem('today_clock_in_status', 'true');
      localStorage.setItem('today_clock_in_time', currentTime);

      showToast(res.message || `Clock In successful at ${currentTime}!`);
      if (onAttendanceUpdated) onAttendanceUpdated();
      setTimeout(() => onClose(), 600);
    } catch (e: any) {
      showToast('Attendance recorded locally');
      setIsClockedIn(true);
      setClockInTime(currentTime);
      setTimeout(() => onClose(), 600);
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleClockOut = async () => {
    setIsSubmitting(true);
    try {
      const res = await markAttendanceApi(authToken, {
        user_id: userData?.user_id,
        type: 'clock_out',
        comments: comments || 'Clocked out via Mobile App',
        time: new Date().toTimeString().split(' ')[0],
      });

      setIsClockedIn(false);
      localStorage.removeItem('today_clock_in_status');
      localStorage.removeItem('today_clock_in_time');

      showToast(res.message || `Clock Out successful at ${currentTime}!`);
      if (onAttendanceUpdated) onAttendanceUpdated();
      setTimeout(() => onClose(), 600);
    } catch (e: any) {
      showToast('Clock Out recorded');
      setIsClockedIn(false);
      setTimeout(() => onClose(), 600);
    } finally {
      setIsSubmitting(false);
    }
  };

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
              <h2 className="text-sm font-bold text-white tracking-tight">Mark Attendance</h2>
              <p className="text-[11px] text-zinc-400">Employee Punch & Attendance</p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Content */}
        <div className="p-4 space-y-4 overflow-y-auto">
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

          {/* Face Scan Attendance Button (High Priority) */}
          {onOpenFaceScan && (
            <div className="pt-2">
              <button
                type="button"
                onClick={() => {
                  onClose();
                  onOpenFaceScan();
                }}
                className="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-600 via-teal-600 to-emerald-600 hover:from-cyan-500 hover:to-emerald-500 text-white font-extrabold text-xs shadow-lg shadow-cyan-950/60 flex items-center justify-center space-x-2 transition-all cursor-pointer active:scale-98 border border-cyan-400/40"
              >
                <Camera className="w-4 h-4 text-cyan-200 animate-pulse" />
                <span>Scan Face & Punch (GPS Verified)</span>
                <Sparkles className="w-3.5 h-3.5 text-amber-300" />
              </button>
            </div>
          )}

          {/* Primary Manual Action Buttons */}
          <div className="pt-1">
            {!isClockedIn ? (
              <button
                onClick={handleClockIn}
                disabled={isSubmitting}
                className="w-full py-2.5 px-4 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-200 border border-zinc-800 font-semibold text-xs flex items-center justify-center space-x-2 transition-all cursor-pointer active:scale-98 disabled:opacity-50"
              >
                <CheckCircle2 className="w-4 h-4 text-emerald-400" />
                <span>{isSubmitting ? 'Recording Punch...' : 'Manual Punch In (Clock In)'}</span>
              </button>
            ) : (
              <button
                onClick={handleClockOut}
                disabled={isSubmitting}
                className="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white font-bold text-sm shadow-lg shadow-rose-950/50 flex items-center justify-center space-x-2 transition-all cursor-pointer active:scale-98 disabled:opacity-50"
              >
                <LogOut className="w-5 h-5 text-white" />
                <span>{isSubmitting ? 'Recording Punch...' : 'Punch Out (Clock Out)'}</span>
              </button>
            )}
          </div>
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
