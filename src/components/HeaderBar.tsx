import React from 'react';
import {
  Search,
  SlidersHorizontal,
  ChevronLeft,
  User,
  Clock,
  RotateCw,
  Moon,
  Building2,
  TrendingUp,
  Sparkles,
  LogOut,
} from 'lucide-react';
import { CompanyLogo } from './CompanyLogo';

interface HeaderBarProps {
  title?: string;
  showBack?: boolean;
  onBack?: () => void;
  searchQuery?: string;
  onSearchChange?: (query: string) => void;
  onOpenFilter?: () => void;
  onSync?: () => void;
  isSyncing?: boolean;
  activeClientName?: string;
  currentUser?: string;
  onLogout?: () => void;
  onNotificationClick?: () => void;
  onMarkAttendance?: () => void;
  onOpenProfile?: () => void;
  clockInStatus?: {
    isClockedIn: boolean;
    clockInTime?: string;
    isPending?: boolean;
  };
  totalClients?: number;
  activeCount?: number;
  pendingCount?: number;
  categoryFilter?: string;
  onSelectCategory?: (category: string) => void;
}

export const HeaderBar: React.FC<HeaderBarProps> = ({
  title = 'CRM',
  showBack = false,
  onBack,
  searchQuery,
  onSearchChange,
  onOpenFilter,
  onSync,
  isSyncing = false,
  currentUser,
  onLogout,
  onMarkAttendance,
  onOpenProfile,
  clockInStatus,
  totalClients = 9,
  activeCount = 1,
  pendingCount = 0,
  categoryFilter = 'All',
  onSelectCategory,
}) => {
  // SUB-PAGE HEADER (when viewing specific client checklist/section)
  if (showBack) {
    return (
      <div className="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-white sticky top-0 z-20 shadow-md">
        <div className="px-4 py-3 flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <button
              onClick={onBack}
              className="p-1.5 rounded-full bg-white/20 hover:bg-white/30 text-white transition-colors cursor-pointer"
              aria-label="Go back"
            >
              <ChevronLeft className="w-5 h-5 text-white stroke-[2.5]" />
            </button>
            <CompanyLogo className="h-8" variant="badge" />
            <h1 className="text-sm font-black text-white tracking-tight">{title}</h1>
          </div>

          <div className="flex items-center space-x-2">
            {onSync && (
              <button
                onClick={onSync}
                disabled={isSyncing}
                className="p-2 rounded-xl bg-white/20 hover:bg-white/30 text-white transition-colors cursor-pointer"
                title="Sync Data"
              >
                <RotateCw className={`w-4 h-4 ${isSyncing ? 'animate-spin' : ''}`} />
              </button>
            )}
            {onOpenProfile && (
              <button
                onClick={onOpenProfile}
                className="w-8 h-8 rounded-full bg-white text-orange-600 flex items-center justify-center font-black text-xs shadow-sm cursor-pointer"
              >
                {currentUser ? currentUser.charAt(0).toUpperCase() : <User className="w-4 h-4" />}
              </button>
            )}
          </div>
        </div>
      </div>
    );
  }

  // MAIN CRM DASHBOARD HEADER (Matching Screenshot 2 exactly)
  return (
    <div className="bg-gradient-to-b from-amber-500 via-orange-500 to-orange-600 text-white rounded-b-[2.2rem] px-4 pt-3 pb-5 shadow-lg shadow-orange-500/25 sticky top-0 z-20 space-y-3.5">
      {/* 1. Top Brand Bar: Logo + CRM Title + Right Action Icons (Moon, Profile) */}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-2.5">
          <CompanyLogo className="h-9 w-9" variant="badge" />
          <h1 className="text-xl font-black text-white tracking-tight">CRM</h1>
        </div>

        <div className="flex items-center space-x-2">
          {/* Attendance Indicator / Quick Button */}
          {onMarkAttendance && (
            <button
              onClick={onMarkAttendance}
              className={`flex items-center space-x-1 px-2.5 py-1.5 rounded-xl text-xs font-bold shadow-xs cursor-pointer transition-all active:scale-95 border ${
                clockInStatus?.isClockedIn
                  ? clockInStatus.isPending
                    ? 'bg-amber-950/40 border-amber-300/40 text-amber-200'
                    : 'bg-emerald-950/40 border-emerald-300/40 text-emerald-200'
                  : 'bg-white/20 border-white/30 text-white'
              }`}
              title="Attendance Status"
            >
              <Clock className="w-3.5 h-3.5" />
              <span className="text-[11px]">
                {clockInStatus?.isClockedIn ? (clockInStatus.isPending ? 'Pending' : 'In') : 'Punch'}
              </span>
            </button>
          )}


          {/* Profile Avatar Button */}
          {onOpenProfile && (
            <button
              onClick={onOpenProfile}
              className="w-8 h-8 rounded-full bg-white text-orange-600 hover:bg-slate-50 flex items-center justify-center font-black text-xs shadow-xs cursor-pointer transition-all active:scale-95"
              title={`Profile: ${currentUser || 'User'}`}
            >
              {currentUser ? currentUser.charAt(0).toUpperCase() : <User className="w-4 h-4 text-orange-600" />}
            </button>
          )}
        </div>
      </div>

      {/* 2. Search Bar + Action Buttons Row */}
      <div className="flex items-center space-x-2">
        <div className="flex-1 relative flex items-center">
          <Search className="w-4 h-4 absolute left-3.5 text-slate-400 pointer-events-none" />
          <input
            type="text"
            placeholder="Search name or phone..."
            value={searchQuery || ''}
            onChange={(e) => onSearchChange?.(e.target.value)}
            className="w-full bg-white text-slate-800 font-semibold placeholder-slate-400 text-xs pl-10 pr-3.5 py-3 rounded-2xl shadow-sm focus:outline-none focus:ring-2 focus:ring-white transition-all"
          />
        </div>

        {/* Sync / Refresh Button */}
        {onSync && (
          <button
            onClick={onSync}
            disabled={isSyncing}
            className="w-10 h-10 bg-white hover:bg-slate-50 text-slate-700 rounded-2xl shadow-sm transition-all cursor-pointer disabled:opacity-50 flex items-center justify-center shrink-0 active:scale-95"
            title="Sync Data from CRM"
          >
            <RotateCw className={`w-4 h-4 text-slate-700 ${isSyncing ? 'animate-spin text-orange-500' : ''}`} />
          </button>
        )}

        {/* Filter Button */}
        {onOpenFilter && (
          <button
            onClick={onOpenFilter}
            className="w-10 h-10 bg-white hover:bg-slate-50 text-slate-700 rounded-2xl shadow-sm transition-all cursor-pointer flex items-center justify-center shrink-0 active:scale-95"
            title="Filter Clients"
          >
            <SlidersHorizontal className="w-4 h-4 text-slate-700" />
          </button>
        )}
      </div>

      {/* 3. Category Filter Pill: Only 'All' as requested */}
      <div className="flex items-center space-x-2 pt-0.5">
        <div className="px-4 py-1.5 rounded-full text-xs font-black bg-white text-slate-900 shadow-sm flex items-center space-x-2">
          <span>All Clients</span>
          <span className="bg-amber-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-extrabold">
            {totalClients}
          </span>
        </div>
      </div>
    </div>
  );
};
