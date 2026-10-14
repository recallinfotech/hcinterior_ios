import React, { useState, useEffect } from 'react';
import {
  X,
  User,
  Calendar,
  Bell,
  FileText,
  Palmtree,
  ChevronRight,
  ChevronLeft,
  ArrowLeft,
  Clock,
  CheckCircle2,
  AlertCircle,
  LogOut,
  Send,
  Building,
  Sparkles,
  ShieldAlert,
  MapPin,
  CalendarDays,
  Briefcase,
  Phone,
  Mail,
  BadgeCheck,
} from 'lucide-react';
import { UserData } from '../../services/authApi';
import {
  getMyAttendanceApi,
  getAnnouncementsApi,
  getHolidaysApi,
  getLeaveTypesApi,
  requestLeaveApi,
  getMyLeavesApi,
  AttendanceResponse,
  AnnouncementItem,
  HolidayItem,
  LeaveTypeItem,
  LeaveApplicationItem,
} from '../../services/userPortalApi';

interface UserProfileModalProps {
  isOpen: boolean;
  onClose: () => void;
  userData: UserData | null;
  authToken: string;
  showToast: (msg: string) => void;
  onLogout: () => void;
  onOpenMarkAttendance: () => void;
}

type ProfileView = 'main' | 'attendance' | 'announcements' | 'requestLeave' | 'holidays';

export const UserProfileModal: React.FC<UserProfileModalProps> = ({
  isOpen,
  onClose,
  userData,
  authToken,
  showToast,
  onLogout,
  onOpenMarkAttendance,
}) => {
  const [currentView, setCurrentView] = useState<ProfileView>('main');

  // Attendance state
  const [selectedMonth, setSelectedMonth] = useState<string>(() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
  });
  const [attendanceData, setAttendanceData] = useState<AttendanceResponse | null>(null);
  const [isLoadingAttendance, setIsLoadingAttendance] = useState<boolean>(false);
  const [selectedDateDetail, setSelectedDateDetail] = useState<any | null>(null);

  // Announcements state
  const [announcements, setAnnouncements] = useState<AnnouncementItem[]>([]);
  const [isLoadingAnnouncements, setIsLoadingAnnouncements] = useState<boolean>(false);

  // Holidays state
  const [holidays, setHolidays] = useState<HolidayItem[]>([]);
  const [isLoadingHolidays, setIsLoadingHolidays] = useState<boolean>(false);

  // Leave Request state
  const [leaveTypes, setLeaveTypes] = useState<LeaveTypeItem[]>([]);
  const [selectedLeaveCatId, setSelectedLeaveCatId] = useState<number>(2); // Default EL
  const [leaveDurationType, setLeaveDurationType] = useState<'single_day' | 'multiple_days'>('single_day');
  const [leaveStartDate, setLeaveStartDate] = useState<string>(() => {
    return new Date().toISOString().split('T')[0];
  });
  const [leaveEndDate, setLeaveEndDate] = useState<string>(() => {
    return new Date().toISOString().split('T')[0];
  });
  const [leaveReason, setLeaveReason] = useState<string>('');
  const [isSubmittingLeave, setIsSubmittingLeave] = useState<boolean>(false);
  const [pastLeaves, setPastLeaves] = useState<LeaveApplicationItem[]>([]);
  const [leaveSubTab, setLeaveSubTab] = useState<'apply' | 'history'>('apply');

  // Reset view on open
  useEffect(() => {
    if (isOpen) {
      setCurrentView('main');
    }
  }, [isOpen]);

  // Load Attendance data when viewing calendar
  useEffect(() => {
    if (currentView === 'attendance' && authToken) {
      loadAttendance(selectedMonth);
    }
  }, [currentView, selectedMonth, authToken]);

  const loadAttendance = async (monthStr: string) => {
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

  // Load Announcements
  useEffect(() => {
    if (currentView === 'announcements' && authToken) {
      setIsLoadingAnnouncements(true);
      getAnnouncementsApi(authToken)
        .then((data) => setAnnouncements(data))
        .catch(console.warn)
        .finally(() => setIsLoadingAnnouncements(false));
    }
  }, [currentView, authToken]);

  // Load Holidays
  useEffect(() => {
    if (currentView === 'holidays' && authToken) {
      setIsLoadingHolidays(true);
      getHolidaysApi(authToken, '2026')
        .then((data) => setHolidays(data))
        .catch(console.warn)
        .finally(() => setIsLoadingHolidays(false));
    }
  }, [currentView, authToken]);

  // Load Leave Types & Past Leaves
  useEffect(() => {
    if (currentView === 'requestLeave' && authToken) {
      getLeaveTypesApi(authToken).then((types) => {
        setLeaveTypes(types);
        if (types.length > 0 && !selectedLeaveCatId) {
          setSelectedLeaveCatId(types[0].leave_category_id);
        }
      });
      getMyLeavesApi(authToken, userData?.user_id).then((res) => {
        if (res && res.leaves) setPastLeaves(res.leaves);
      });
    }
  }, [currentView, authToken]);

  if (!isOpen) return null;

  // Handle Month Change in Attendance Calendar
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

  // Handle Submit Leave Request
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
      setLeaveSubTab('history');
      // Refresh past leaves
      getMyLeavesApi(authToken, userData?.user_id).then((r) => {
        if (r && r.leaves) setPastLeaves(r.leaves);
      });
    } catch (e: any) {
      showToast('Failed to submit leave request');
    } finally {
      setIsSubmittingLeave(false);
    }
  };

  // Month Display Name
  const formatMonthTitle = (monthStr: string) => {
    const [y, m] = monthStr.split('-').map(Number);
    const d = new Date(y, m - 1, 1);
    return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  };

  // Render Calendar Grid Days
  const renderCalendarGrid = () => {
    const [y, m] = selectedMonth.split('-').map(Number);
    const firstDayIndex = new Date(y, m - 1, 1).getDay(); // 0 = Sun
    const totalDays = new Date(y, m, 0).getDate();

    const cells: React.ReactNode[] = [];

    // Empty lead cells
    for (let i = 0; i < firstDayIndex; i++) {
      cells.push(<div key={`empty-${i}`} className="h-10 rounded-lg bg-zinc-950/20" />);
    }

    // Days map
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

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3.5 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200">
      <div className="bg-zinc-900 border border-zinc-800 text-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl flex flex-col max-h-[92vh]">
        {/* Modal Header */}
        <div className="px-4 py-3 border-b border-zinc-800 flex items-center justify-between bg-zinc-950/80 shrink-0">
          <div className="flex items-center space-x-2">
            {currentView !== 'main' ? (
              <button
                onClick={() => {
                  setCurrentView('main');
                  setSelectedDateDetail(null);
                }}
                className="p-1 rounded-lg bg-zinc-800 text-zinc-300 hover:text-white transition-colors mr-1 cursor-pointer"
              >
                <ArrowLeft className="w-4 h-4 text-amber-400" />
              </button>
            ) : (
              <div className="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                <User className="w-4 h-4" />
              </div>
            )}
            <div>
              <h2 className="text-sm font-bold text-white tracking-tight">
                {currentView === 'main' && 'User Profile & Work Center'}
                {currentView === 'attendance' && 'Attendance Calendar'}
                {currentView === 'announcements' && 'Company Announcements'}
                {currentView === 'requestLeave' && 'Request Leave'}
                {currentView === 'holidays' && 'Holiday List (2026)'}
              </h2>
              <p className="text-[10px] text-zinc-400">
                {currentView === 'main' ? userData?.fullname || 'Staff Portal' : 'HC Interior HR System'}
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Modal Body */}
        <div className="flex-1 overflow-y-auto p-4 space-y-4">
          {/* ========================================================= */}
          {/* VIEW: MAIN PROFILE MENU                                   */}
          {/* ========================================================= */}
          {currentView === 'main' && (
            <div className="space-y-4">
              {/* Profile Card */}
              <div className="bg-gradient-to-br from-zinc-950 to-zinc-900 border border-zinc-800 p-4 rounded-2xl relative overflow-hidden shadow-lg">
                <div className="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl pointer-events-none" />
                <div className="flex items-center space-x-3.5">
                  <div className="w-14 h-14 rounded-full bg-gradient-to-tr from-amber-500 to-amber-300 p-0.5 shrink-0 shadow-md">
                    <img
                      src={
                        userData?.avatar
                          ? `https://crm.hcinterior.in/${userData.avatar}`
                          : 'https://crm.hcinterior.in/uploads/default_avatar.jpg'
                      }
                      alt=""
                      className="w-full h-full rounded-full object-cover bg-zinc-800"
                      onError={(e) => {
                        (e.currentTarget as HTMLImageElement).src =
                          'https://ui-avatars.com/api/?name=' +
                          encodeURIComponent(userData?.fullname || 'User') +
                          '&background=f59e0b&color=fff';
                      }}
                    />
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="flex items-center space-x-1.5">
                      <h3 className="text-sm font-bold text-white truncate">{userData?.fullname || 'Staff Member'}</h3>
                      <BadgeCheck className="w-4 h-4 text-emerald-400 shrink-0" />
                    </div>
                    <p className="text-xs text-amber-400 font-semibold mt-0.5">
                      {userData?.employment_id || `EMP-${userData?.user_id || '5'}`}
                    </p>
                    <div className="flex items-center space-x-2 text-[11px] text-zinc-400 mt-1">
                      <Mail className="w-3 h-3 text-zinc-500 shrink-0" />
                      <span className="truncate">{userData?.email || 'user@hcinterior.in'}</span>
                    </div>
                  </div>
                </div>

                {/* Quick Punch Button */}
                <div className="mt-4 pt-3 border-t border-zinc-800/80 flex items-center justify-between">
                  <div className="text-[11px] text-zinc-400 flex items-center space-x-1.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                    <span>Daily Punch Status</span>
                  </div>
                  <button
                    onClick={() => {
                      onClose();
                      onOpenMarkAttendance();
                    }}
                    className="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center space-x-1.5 transition-all shadow-md shadow-emerald-950 cursor-pointer active:scale-95"
                  >
                    <MapPin className="w-3.5 h-3.5" />
                    <span>Mark Attendance</span>
                  </button>
                </div>
              </div>

              {/* 4 CORE SECTION CARDS REQUESTED BY USER */}
              <div className="space-y-2">
                <div className="text-[11px] font-bold uppercase tracking-wider text-zinc-400 px-1">
                  Employee Self-Service (ESS)
                </div>

                {/* 1. View Attendance (in Calendar) */}
                <button
                  onClick={() => setCurrentView('attendance')}
                  className="w-full bg-zinc-950/70 hover:bg-zinc-800/80 border border-zinc-800 hover:border-emerald-500/40 p-3.5 rounded-xl flex items-center justify-between transition-all cursor-pointer group text-left shadow-2xs"
                >
                  <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 group-hover:scale-105 transition-transform">
                      <Calendar className="w-5 h-5" />
                    </div>
                    <div>
                      <h4 className="text-xs font-bold text-white group-hover:text-emerald-400 transition-colors">
                        View Attendance (in Calendar)
                      </h4>
                      <p className="text-[10px] text-zinc-400">Monthly calendar, daily punch logs & work hours</p>
                    </div>
                  </div>
                  <ChevronRight className="w-4 h-4 text-zinc-500 group-hover:text-emerald-400 transition-colors" />
                </button>

                {/* 2. Announcement (Notice) */}
                <button
                  onClick={() => setCurrentView('announcements')}
                  className="w-full bg-zinc-950/70 hover:bg-zinc-800/80 border border-zinc-800 hover:border-amber-500/40 p-3.5 rounded-xl flex items-center justify-between transition-all cursor-pointer group text-left shadow-2xs"
                >
                  <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 group-hover:scale-105 transition-transform">
                      <Bell className="w-5 h-5" />
                    </div>
                    <div>
                      <h4 className="text-xs font-bold text-white group-hover:text-amber-400 transition-colors">
                        Announcement (Notice)
                      </h4>
                      <p className="text-[10px] text-zinc-400">Company announcements, circulars & notices</p>
                    </div>
                  </div>
                  <ChevronRight className="w-4 h-4 text-zinc-500 group-hover:text-amber-400 transition-colors" />
                </button>

                {/* 3. Request Leave */}
                <button
                  onClick={() => setCurrentView('requestLeave')}
                  className="w-full bg-zinc-950/70 hover:bg-zinc-800/80 border border-zinc-800 hover:border-sky-500/40 p-3.5 rounded-xl flex items-center justify-between transition-all cursor-pointer group text-left shadow-2xs"
                >
                  <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-sky-400 group-hover:scale-105 transition-transform">
                      <FileText className="w-5 h-5" />
                    </div>
                    <div>
                      <h4 className="text-xs font-bold text-white group-hover:text-sky-400 transition-colors">
                        Request Leave
                      </h4>
                      <p className="text-[10px] text-zinc-400">Apply for Earn Leave, Sick Leave & check status</p>
                    </div>
                  </div>
                  <ChevronRight className="w-4 h-4 text-zinc-500 group-hover:text-sky-400 transition-colors" />
                </button>

                {/* 4. Holiday List */}
                <button
                  onClick={() => setCurrentView('holidays')}
                  className="w-full bg-zinc-950/70 hover:bg-zinc-800/80 border border-zinc-800 hover:border-indigo-500/40 p-3.5 rounded-xl flex items-center justify-between transition-all cursor-pointer group text-left shadow-2xs"
                >
                  <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400 group-hover:scale-105 transition-transform">
                      <Palmtree className="w-5 h-5" />
                    </div>
                    <div>
                      <h4 className="text-xs font-bold text-white group-hover:text-indigo-400 transition-colors">
                        Holiday List (2026)
                      </h4>
                      <p className="text-[10px] text-zinc-400">Official company holiday calendar & festivals</p>
                    </div>
                  </div>
                  <ChevronRight className="w-4 h-4 text-zinc-500 group-hover:text-indigo-400 transition-colors" />
                </button>
              </div>

              {/* Logout Button */}
              <div className="pt-2">
                <button
                  onClick={() => {
                    onClose();
                    onLogout();
                  }}
                  className="w-full py-2.5 px-4 rounded-xl bg-rose-950/20 hover:bg-rose-950/40 text-rose-400 border border-rose-500/30 font-bold text-xs flex items-center justify-center space-x-2 transition-all cursor-pointer"
                >
                  <LogOut className="w-4 h-4" />
                  <span>Log Out of Session</span>
                </button>
              </div>
            </div>
          )}

          {/* ========================================================= */}
          {/* VIEW 1: ATTENDANCE CALENDAR                               */}
          {/* ========================================================= */}
          {currentView === 'attendance' && (
            <div className="space-y-3.5">
              {/* Month Switcher Header */}
              <div className="flex items-center justify-between bg-zinc-950 p-2.5 rounded-xl border border-zinc-800">
                <button
                  onClick={handlePrevMonth}
                  className="p-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 cursor-pointer"
                  title="Previous Month"
                >
                  <ChevronLeft className="w-4 h-4" />
                </button>
                <div className="text-xs font-bold text-white flex items-center space-x-1.5">
                  <Calendar className="w-3.5 h-3.5 text-emerald-400" />
                  <span>{formatMonthTitle(selectedMonth)}</span>
                </div>
                <button
                  onClick={handleNextMonth}
                  className="p-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 cursor-pointer"
                  title="Next Month"
                >
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>

              {/* KPI Summary Cards */}
              <div className="grid grid-cols-4 gap-2 text-center text-[10px]">
                <div className="bg-emerald-950/30 border border-emerald-500/40 p-2 rounded-xl">
                  <div className="text-base font-black text-emerald-400">{attendanceData?.summary.present || 0}</div>
                  <div className="text-zinc-400 font-semibold">Present</div>
                </div>
                <div className="bg-rose-950/30 border border-rose-500/40 p-2 rounded-xl">
                  <div className="text-base font-black text-rose-400">{attendanceData?.summary.absent || 0}</div>
                  <div className="text-zinc-400 font-semibold">Absent</div>
                </div>
                <div className="bg-amber-950/30 border border-amber-500/40 p-2 rounded-xl">
                  <div className="text-base font-black text-amber-400">{attendanceData?.summary.leave || 0}</div>
                  <div className="text-zinc-400 font-semibold">On Leave</div>
                </div>
                <div className="bg-sky-950/30 border border-sky-500/40 p-2 rounded-xl">
                  <div className="text-base font-black text-sky-400">{attendanceData?.summary.holiday || 0}</div>
                  <div className="text-zinc-400 font-semibold">Holiday/Off</div>
                </div>
              </div>

              {/* Leave Balances Pill Banner */}
              <div className="bg-zinc-950/70 p-2.5 rounded-xl border border-zinc-800 flex items-center justify-between text-[11px]">
                <div>
                  <span className="font-semibold text-zinc-400">Earn Leave (EL):</span>{' '}
                  <strong className="text-emerald-400">{attendanceData?.summary.el_balance || '12.50'} Days</strong>
                </div>
                <div className="h-4 w-px bg-zinc-800" />
                <div>
                  <span className="font-semibold text-zinc-400">Sick Leave (SL):</span>{' '}
                  <strong className="text-indigo-400">{attendanceData?.summary.sl_balance || '10.00'} Days</strong>
                </div>
              </div>

              {/* Calendar Grid Header */}
              <div className="grid grid-cols-7 text-center text-[10px] font-bold text-zinc-500">
                <span>Sun</span>
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span>Sat</span>
              </div>

              {/* Calendar Grid Cells */}
              <div className="grid grid-cols-7 gap-1">
                {isLoadingAttendance ? (
                  <div className="col-span-7 py-8 text-center text-xs text-zinc-500 flex items-center justify-center space-x-2">
                    <span className="inline-block w-4 h-4 border-2 border-emerald-400/30 border-t-emerald-400 rounded-full animate-spin" />
                    <span>Loading calendar...</span>
                  </div>
                ) : (
                  renderCalendarGrid()
                )}
              </div>

              {/* Selected Day Punch Detail Card */}
              {selectedDateDetail && (
                <div className="bg-zinc-950 border border-zinc-800 p-3 rounded-xl animate-in fade-in duration-200 text-xs space-y-1.5">
                  <div className="flex items-center justify-between">
                    <strong className="text-white font-bold">{selectedDateDetail.date}</strong>
                    <span
                      className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                        selectedDateDetail.status === 'P'
                          ? 'bg-emerald-500/20 text-emerald-400'
                          : selectedDateDetail.status === 'A'
                          ? 'bg-rose-500/20 text-rose-400'
                          : selectedDateDetail.status === 'L'
                          ? 'bg-amber-500/20 text-amber-400'
                          : 'bg-sky-500/20 text-sky-400'
                      }`}
                    >
                      {selectedDateDetail.status_text || 'Attendance Record'}
                    </span>
                  </div>
                  {selectedDateDetail.clockin && (
                    <div className="text-zinc-400 text-[11px] flex items-center justify-between">
                      <span>Clock In: <strong className="text-white">{selectedDateDetail.clockin}</strong></span>
                      <span>Clock Out: <strong className="text-white">{selectedDateDetail.clockout || 'Active'}</strong></span>
                      {selectedDateDetail.working_hours && (
                        <span>Hours: <strong className="text-emerald-400">{selectedDateDetail.working_hours}</strong></span>
                      )}
                    </div>
                  )}
                  {selectedDateDetail.comments && (
                    <p className="text-[10px] text-zinc-500 italic">Notes: {selectedDateDetail.comments}</p>
                  )}
                </div>
              )}
            </div>
          )}

          {/* ========================================================= */}
          {/* VIEW 2: ANNOUNCEMENTS (NOTICES)                           */}
          {/* ========================================================= */}
          {currentView === 'announcements' && (
            <div className="space-y-3">
              {isLoadingAnnouncements ? (
                <div className="py-8 text-center text-xs text-zinc-500 flex items-center justify-center space-x-2">
                  <span className="inline-block w-4 h-4 border-2 border-amber-400/30 border-t-amber-400 rounded-full animate-spin" />
                  <span>Loading notices...</span>
                </div>
              ) : announcements.length === 0 ? (
                <div className="p-8 text-center text-xs text-zinc-500">No active announcements.</div>
              ) : (
                announcements.map((item) => (
                  <div
                    key={item.announcements_id}
                    className="bg-zinc-950 p-3.5 rounded-xl border border-zinc-800 space-y-1.5 hover:border-amber-500/40 transition-colors"
                  >
                    <div className="flex items-center justify-between">
                      <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        Notice
                      </span>
                      <span className="text-[10px] text-zinc-400">{item.date_formatted}</span>
                    </div>
                    <h4 className="text-xs font-bold text-white leading-snug">{item.title}</h4>
                    <p className="text-[11px] text-zinc-400 leading-relaxed">{item.description}</p>
                    <div className="pt-1 text-[10px] text-zinc-500 flex items-center space-x-1">
                      <Building className="w-3 h-3 text-zinc-600" />
                      <span>Posted by: {item.posted_by}</span>
                    </div>
                  </div>
                ))
              )}
            </div>
          )}

          {/* ========================================================= */}
          {/* VIEW 3: REQUEST LEAVE                                     */}
          {/* ========================================================= */}
          {currentView === 'requestLeave' && (
            <div className="space-y-3.5">
              {/* Tabs: Apply / Past Requests */}
              <div className="flex items-center bg-zinc-950 p-1 rounded-xl border border-zinc-800 text-xs">
                <button
                  type="button"
                  onClick={() => setLeaveSubTab('apply')}
                  className={`flex-1 py-1.5 rounded-lg font-bold transition-all ${
                    leaveSubTab === 'apply' ? 'bg-sky-500 text-white shadow-md' : 'text-zinc-400 hover:text-white'
                  }`}
                >
                  Apply Leave
                </button>
                <button
                  type="button"
                  onClick={() => setLeaveSubTab('history')}
                  className={`flex-1 py-1.5 rounded-lg font-bold transition-all ${
                    leaveSubTab === 'history' ? 'bg-sky-500 text-white shadow-md' : 'text-zinc-400 hover:text-white'
                  }`}
                >
                  My Requests ({pastLeaves.length})
                </button>
              </div>

              {leaveSubTab === 'apply' ? (
                <form onSubmit={handleSubmitLeave} className="space-y-3">
                  {/* Leave Category Selector */}
                  <div>
                    <label className="block text-[11px] font-semibold text-zinc-300 mb-1">
                      Leave Type <span className="text-rose-400">*</span>
                    </label>
                    <select
                      value={selectedLeaveCatId}
                      onChange={(e) => setSelectedLeaveCatId(Number(e.target.value))}
                      className="w-full bg-zinc-950 text-white text-xs px-3 py-2 rounded-lg border border-zinc-800 focus:outline-none focus:border-sky-400"
                    >
                      {leaveTypes.map((cat) => (
                        <option key={cat.leave_category_id} value={cat.leave_category_id}>
                          {cat.leave_category === 'EL'
                            ? 'Earn Leave (EL)'
                            : cat.leave_category === 'SL'
                            ? 'Sick Leave (SL)'
                            : cat.leave_category === 'CL'
                            ? 'Casual Leave (CL)'
                            : cat.leave_category}
                        </option>
                      ))}
                    </select>
                  </div>

                  {/* Duration Type (Single / Multiple) */}
                  <div>
                    <label className="block text-[11px] font-semibold text-zinc-300 mb-1">Duration</label>
                    <div className="grid grid-cols-2 gap-2 text-xs">
                      <button
                        type="button"
                        onClick={() => setLeaveDurationType('single_day')}
                        className={`py-2 px-3 rounded-lg border text-center font-bold transition-all cursor-pointer ${
                          leaveDurationType === 'single_day'
                            ? 'bg-sky-500/20 text-sky-400 border-sky-500/40'
                            : 'bg-zinc-950 text-zinc-400 border-zinc-800'
                        }`}
                      >
                        Single Day
                      </button>
                      <button
                        type="button"
                        onClick={() => setLeaveDurationType('multiple_days')}
                        className={`py-2 px-3 rounded-lg border text-center font-bold transition-all cursor-pointer ${
                          leaveDurationType === 'multiple_days'
                            ? 'bg-sky-500/20 text-sky-400 border-sky-500/40'
                            : 'bg-zinc-950 text-zinc-400 border-zinc-800'
                        }`}
                      >
                        Multiple Days
                      </button>
                    </div>
                  </div>

                  {/* Date Pickers */}
                  <div className="grid grid-cols-2 gap-2">
                    <div>
                      <label className="block text-[11px] font-semibold text-zinc-300 mb-1">
                        {leaveDurationType === 'multiple_days' ? 'Start Date' : 'Leave Date'}
                      </label>
                      <input
                        type="date"
                        value={leaveStartDate}
                        onChange={(e) => setLeaveStartDate(e.target.value)}
                        className="w-full bg-zinc-950 text-white text-xs px-2.5 py-2 rounded-lg border border-zinc-800 focus:outline-none focus:border-sky-400"
                        required
                      />
                    </div>
                    {leaveDurationType === 'multiple_days' && (
                      <div>
                        <label className="block text-[11px] font-semibold text-zinc-300 mb-1">End Date</label>
                        <input
                          type="date"
                          value={leaveEndDate}
                          min={leaveStartDate}
                          onChange={(e) => setLeaveEndDate(e.target.value)}
                          className="w-full bg-zinc-950 text-white text-xs px-2.5 py-2 rounded-lg border border-zinc-800 focus:outline-none focus:border-sky-400"
                          required
                        />
                      </div>
                    )}
                  </div>

                  {/* Reason Textarea */}
                  <div>
                    <label className="block text-[11px] font-semibold text-zinc-300 mb-1">
                      Reason for Leave <span className="text-rose-400">*</span>
                    </label>
                    <textarea
                      rows={3}
                      value={leaveReason}
                      onChange={(e) => setLeaveReason(e.target.value)}
                      placeholder="Please specify reason (e.g., Medical emergency, Family function)..."
                      className="w-full bg-zinc-950 text-white text-xs p-3 rounded-lg border border-zinc-800 placeholder-zinc-600 focus:outline-none focus:border-sky-400"
                      required
                    />
                  </div>

                  <button
                    type="submit"
                    disabled={isSubmittingLeave}
                    className="w-full py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg shadow-sky-950 flex items-center justify-center space-x-2 transition-all cursor-pointer disabled:opacity-50"
                  >
                    <Send className="w-4 h-4" />
                    <span>{isSubmittingLeave ? 'Submitting Application...' : 'Submit Leave Request'}</span>
                  </button>
                </form>
              ) : (
                <div className="space-y-2.5">
                  {pastLeaves.length === 0 ? (
                    <div className="p-8 text-center text-xs text-zinc-500">No previous leave applications.</div>
                  ) : (
                    pastLeaves.map((app) => (
                      <div
                        key={app.leave_application_id}
                        className="bg-zinc-950 p-3 rounded-xl border border-zinc-800 space-y-1.5"
                      >
                        <div className="flex items-center justify-between">
                          <span className="font-bold text-xs text-white">
                            {app.category_name} ({app.leave_type === 'single_day' ? '1 Day' : 'Multiple Days'})
                          </span>
                          <span
                            className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                              app.application_status === 2
                                ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                                : app.application_status === 3
                                ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40'
                                : 'bg-amber-500/20 text-amber-400 border border-amber-500/40'
                            }`}
                          >
                            {app.status_text}
                          </span>
                        </div>
                        <div className="text-[11px] text-zinc-400">
                          {app.leave_start_date} {app.leave_end_date && app.leave_end_date !== app.leave_start_date ? `to ${app.leave_end_date}` : ''}
                        </div>
                        {app.reason && <p className="text-[10px] text-zinc-500 italic">{app.reason}</p>}
                      </div>
                    ))
                  )}
                </div>
              )}
            </div>
          )}

          {/* ========================================================= */}
          {/* VIEW 4: HOLIDAY LIST (2026)                               */}
          {/* ========================================================= */}
          {currentView === 'holidays' && (
            <div className="space-y-2.5">
              {isLoadingHolidays ? (
                <div className="py-8 text-center text-xs text-zinc-500 flex items-center justify-center space-x-2">
                  <span className="inline-block w-4 h-4 border-2 border-indigo-400/30 border-t-indigo-400 rounded-full animate-spin" />
                  <span>Loading holidays...</span>
                </div>
              ) : holidays.length === 0 ? (
                <div className="p-8 text-center text-xs text-zinc-500">No holidays found for this year.</div>
              ) : (
                holidays.map((h) => (
                  <div
                    key={h.holiday_id}
                    className="bg-zinc-950 p-3 rounded-xl border border-zinc-800 flex items-center justify-between hover:border-indigo-500/40 transition-colors"
                  >
                    <div className="flex items-center space-x-3">
                      <div className="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex flex-col items-center justify-center text-indigo-400">
                        <span className="text-[11px] font-black leading-tight">
                          {h.start_date.split('-')[2]}
                        </span>
                        <span className="text-[9px] uppercase font-bold text-zinc-400">
                          {new Date(h.start_date).toLocaleDateString('en-US', { month: 'short' })}
                        </span>
                      </div>
                      <div>
                        <h4 className="text-xs font-bold text-white">{h.event_name}</h4>
                        <div className="text-[10px] text-zinc-400 flex items-center space-x-1.5">
                          <span>{h.day_name}</span>
                          <span>•</span>
                          <span className="text-indigo-400">{h.description}</span>
                        </div>
                      </div>
                    </div>

                    <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-zinc-800 text-zinc-300">
                      Off
                    </span>
                  </div>
                ))
              )}
            </div>
          )}
        </div>

        {/* Modal Footer */}
        <div className="px-4 py-2 bg-zinc-950 border-t border-zinc-800/80 text-center text-[10px] text-zinc-500 flex items-center justify-between shrink-0">
          <span>HC Interior Mobile App</span>
          <span className="text-amber-400 font-semibold">{new Date().getFullYear()}</span>
        </div>
      </div>
    </div>
  );
};
