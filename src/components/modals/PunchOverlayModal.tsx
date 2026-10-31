import React, { useEffect, useState } from 'react';
import {
  CheckCircle2,
  LogOut,
  Clock,
  Calendar,
  MapPin,
  Sparkles,
  ShieldCheck,
  X,
  User,
} from 'lucide-react';

export interface PunchOverlayData {
  isOpen: boolean;
  type: 'in' | 'out';
  time?: string;
  date?: string;
  userName?: string;
  empId?: string;
  isPending?: boolean;
  message?: string;
  location?: string;
}

interface PunchOverlayModalProps {
  data: PunchOverlayData | null;
  onClose: () => void;
  durationMs?: number;
}

export const PunchOverlayModal: React.FC<PunchOverlayModalProps> = ({
  data,
  onClose,
  durationMs = 3000,
}) => {
  const [progress, setProgress] = useState(100);

  useEffect(() => {
    if (!data?.isOpen) {
      setProgress(100);
      return;
    }

    setProgress(100);
    const startTime = Date.now();
    const interval = setInterval(() => {
      const elapsed = Date.now() - startTime;
      const remainingPct = Math.max(0, 100 - (elapsed / durationMs) * 100);
      setProgress(remainingPct);
    }, 30);

    const timer = setTimeout(() => {
      onClose();
    }, durationMs);

    return () => {
      clearInterval(interval);
      clearTimeout(timer);
    };
  }, [data?.isOpen, durationMs, onClose]);

  if (!data || !data.isOpen) return null;

  const isPunchIn = data.type === 'in';
  const displayTime =
    data.time ||
    new Date().toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true,
    });
  const displayDate =
    data.date ||
    new Date().toLocaleDateString('en-US', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });

  return (
    <div
      className="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md animate-in fade-in duration-200 select-none"
      onClick={onClose}
    >
      <div
        className={`w-full max-w-sm rounded-3xl p-6 text-center relative overflow-hidden shadow-2xl transition-all transform animate-in zoom-in-95 duration-250 border ${
          isPunchIn
            ? data.isPending
              ? 'bg-gradient-to-b from-zinc-950 via-[#261d08] to-zinc-950 border-amber-500/50 shadow-[0_0_60px_rgba(245,158,11,0.35)]'
              : 'bg-gradient-to-b from-zinc-950 via-[#062016] to-zinc-950 border-emerald-500/50 shadow-[0_0_60px_rgba(16,185,129,0.35)]'
            : 'bg-gradient-to-b from-zinc-950 via-[#260c12] to-zinc-950 border-rose-500/50 shadow-[0_0_60px_rgba(244,63,94,0.35)]'
        }`}
        onClick={(e) => e.stopPropagation()}
      >
        {/* Subtle Ambient Glowing Background Rings */}
        <div
          className={`absolute -top-24 -left-24 w-52 h-52 rounded-full blur-3xl pointer-events-none opacity-40 ${
            isPunchIn ? 'bg-emerald-500' : 'bg-rose-500'
          }`}
        />
        <div
          className={`absolute -bottom-24 -right-24 w-52 h-52 rounded-full blur-3xl pointer-events-none opacity-40 ${
            isPunchIn ? 'bg-teal-400' : 'bg-amber-500'
          }`}
        />

        {/* Close Button in corner */}
        <button
          onClick={onClose}
          className="absolute top-3.5 right-3.5 p-1.5 rounded-full bg-zinc-900/80 hover:bg-zinc-800 text-zinc-400 hover:text-white transition-colors cursor-pointer border border-zinc-800"
          title="Dismiss"
        >
          <X className="w-4 h-4" />
        </button>

        {/* Brand / System Tag */}
        <div className="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-zinc-900/90 border border-zinc-800 text-zinc-300 mb-4">
          <Sparkles className={`w-3 h-3 ${isPunchIn ? 'text-emerald-400' : 'text-rose-400'}`} />
          <span>HC Interior Attendance</span>
        </div>

        {/* Big Animated Icon Badge */}
        <div className="relative mx-auto w-24 h-24 mb-4 flex items-center justify-center">
          {/* Pulsing Ripple Ring */}
          <div
            className={`absolute inset-0 rounded-full animate-ping opacity-25 ${
              isPunchIn ? 'bg-emerald-400' : 'bg-rose-400'
            }`}
          />
          <div
            className={`w-24 h-24 rounded-full flex items-center justify-center border-2 shadow-xl ${
              isPunchIn
                ? data.isPending
                  ? 'bg-gradient-to-tr from-amber-600 to-yellow-400 border-amber-300/80 text-white shadow-amber-950'
                  : 'bg-gradient-to-tr from-emerald-600 to-teal-400 border-emerald-300/80 text-white shadow-emerald-950'
                : 'bg-gradient-to-tr from-rose-600 to-amber-500 border-rose-300/80 text-white shadow-rose-950'
            }`}
          >
            {isPunchIn ? (
              data.isPending ? (
                <Clock className="w-13 h-13 stroke-[2.5] text-white animate-in zoom-in-75 duration-300" />
              ) : (
                <CheckCircle2 className="w-13 h-13 stroke-[2.5] text-white animate-in zoom-in-75 duration-300" />
              )
            ) : (
              <LogOut className="w-12 h-12 stroke-[2.5] text-white animate-in zoom-in-75 duration-300" />
            )}
          </div>
        </div>

        {/* Giant Main Title */}
        <div className="space-y-1 mb-4">
          <h1
            className={`text-3xl sm:text-4xl font-black tracking-wider uppercase drop-shadow-sm ${
              isPunchIn ? (data.isPending ? 'text-amber-400' : 'text-emerald-400') : 'text-rose-400'
            }`}
          >
            {isPunchIn ? (data.isPending ? 'WAITING FOR APPROVAL' : 'PUNCH IN') : 'PUNCH OUT'}
          </h1>
          <p className="text-xs sm:text-sm font-bold text-white tracking-wide">
            {isPunchIn
              ? data.isPending
                ? 'Field Punch Marked (Waiting for Admin Approval)'
                : 'Attendance Marked Successfully!'
              : 'Shift Ended Successfully!'}
          </p>
        </div>

        {/* Digital Time & Date Box */}
        <div className="bg-zinc-950/90 border border-zinc-800 rounded-2xl p-3.5 mb-4 shadow-inner space-y-1.5">
          <div className="flex items-center justify-center space-x-2 text-xl sm:text-2xl font-black font-mono tracking-wider text-white">
            <Clock className={`w-5 h-5 ${isPunchIn ? 'text-emerald-400' : 'text-rose-400'}`} />
            <span>{displayTime}</span>
          </div>
          <div className="flex items-center justify-center space-x-1.5 text-[11px] font-medium text-zinc-400">
            <Calendar className="w-3.5 h-3.5 text-zinc-500" />
            <span>{displayDate}</span>
          </div>
        </div>

        {/* Employee & Location Metadata Info */}
        <div className="bg-zinc-900/60 border border-zinc-800/80 rounded-xl p-2.5 mb-4 text-left space-y-1.5 text-xs">
          <div className="flex items-center justify-between text-zinc-300">
            <div className="flex items-center space-x-1.5 truncate">
              <User className="w-3.5 h-3.5 text-amber-400 shrink-0" />
              <span className="font-semibold truncate">{data.userName || 'Employee'}</span>
            </div>
            {data.empId && (
              <span className="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                {data.empId}
              </span>
            )}
          </div>
          <div className="flex items-center justify-between text-[11px] text-zinc-400 pt-1 border-t border-zinc-800/60">
            <span className="flex items-center space-x-1">
              <ShieldCheck className="w-3.5 h-3.5 text-emerald-400" />
              <span>{isPunchIn ? (data.isPending ? 'Status: Waiting for Approval' : 'Status: Present') : 'Status: Clocked Out'}</span>
            </span>
            <span className="flex items-center space-x-1 text-zinc-400">
              <MapPin className="w-3 h-3 text-cyan-400" />
              <span>{data.location || 'GPS Verified'}</span>
            </span>
          </div>
        </div>

        {/* Auto Dismiss Progress Bar */}
        <div className="space-y-1.5">
          <div className="w-full h-1.5 bg-zinc-800 rounded-full overflow-hidden">
            <div
              className={`h-full transition-all duration-75 ease-linear rounded-full ${
                isPunchIn ? 'bg-emerald-400' : 'bg-rose-500'
              }`}
              style={{ width: `${progress}%` }}
            />
          </div>
          <div className="flex items-center justify-between text-[10px] text-zinc-500 px-1">
            <span>Closing automatically...</span>
            <button
              onClick={onClose}
              className="text-zinc-400 hover:text-white font-bold underline cursor-pointer"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
