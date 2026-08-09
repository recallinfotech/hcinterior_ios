import React from 'react';
import { Search, Bell, SlidersHorizontal, ChevronLeft, LogOut, User } from 'lucide-react';
import { CompanyLogo } from './CompanyLogo';

interface HeaderBarProps {
  title?: string;
  showBack?: boolean;
  onBack?: () => void;
  searchQuery?: string;
  onSearchChange?: (query: string) => void;
  onOpenFilter?: () => void;
  activeClientName?: string;
  currentUser?: string;
  onLogout?: () => void;
  onNotificationClick?: () => void;
}

export const HeaderBar: React.FC<HeaderBarProps> = ({
  title,
  showBack = false,
  onBack,
  searchQuery,
  onSearchChange,
  onOpenFilter,
  activeClientName,
  currentUser,
  onLogout,
  onNotificationClick,
}) => {
  return (
    <div className="bg-zinc-950 text-white border-b border-zinc-800 sticky top-0 z-20 shadow-md">
      {/* Top Brand & Actions */}
      <div className="px-3.5 py-2.5 flex items-center justify-between">
        <div className="flex items-center space-x-2.5">
          {showBack && (
            <button
              onClick={onBack}
              className="p-1.5 -ml-1 rounded-full bg-zinc-900 hover:bg-zinc-800 text-zinc-200 transition-colors cursor-pointer border border-zinc-800"
              aria-label="Go back"
            >
              <ChevronLeft className="w-5 h-5 text-amber-400" />
            </button>
          )}

          {/* Company Logo Badge */}
          <CompanyLogo className="h-9" variant="badge" />

          {title && (
            <div className="hidden sm:block pl-2 border-l border-zinc-800">
              <h1 className="text-xs font-bold text-zinc-300 tracking-tight">{title}</h1>
            </div>
          )}
        </div>

        <div className="flex items-center space-x-2">
          {onOpenFilter && (
            <button
              onClick={onOpenFilter}
              className="p-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 transition-colors cursor-pointer border border-zinc-800"
              title="Filter"
            >
              <SlidersHorizontal className="w-4 h-4 text-amber-400" />
            </button>
          )}

          <div className="relative">
            <button
              onClick={onNotificationClick}
              className="p-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 transition-colors cursor-pointer border border-zinc-800 relative"
              title="Push Notifications & FCM Center"
            >
              <Bell className="w-4 h-4 text-zinc-300" />
              <span className="absolute top-1 right-1 w-2 h-2 rounded-full bg-amber-400 ring-2 ring-zinc-950 animate-pulse"></span>
            </button>
          </div>

          {onLogout && (
            <button
              onClick={onLogout}
              className="p-2 rounded-lg bg-zinc-900 hover:bg-rose-950/40 text-zinc-300 hover:text-rose-400 transition-colors cursor-pointer border border-zinc-800 flex items-center space-x-1"
              title={`Logged in as ${currentUser || 'User'}. Click to logout.`}
            >
              <LogOut className="w-4 h-4 text-rose-400" />
            </button>
          )}
        </div>
      </div>

      {/* Optional Search Bar */}
      {onSearchChange !== undefined && (
        <div className="px-4 pb-3 pt-1">
          <div className="relative flex items-center">
            <Search className="w-4 h-4 absolute left-3 text-zinc-400 pointer-events-none" />
            <input
              type="text"
              placeholder="Search Client ID, Name, Manager, Phase..."
              value={searchQuery || ''}
              onChange={(e) => onSearchChange(e.target.value)}
              className="w-full bg-zinc-900/90 text-white text-xs pl-9 pr-3 py-2 rounded-lg border border-zinc-800 placeholder-zinc-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-all font-medium"
            />
          </div>
        </div>
      )}
    </div>
  );
};
