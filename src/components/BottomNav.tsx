import React, { useState } from 'react';
import { Capacitor } from '@capacitor/core';
import {
  Users,
  ShieldAlert,
  Clock,
  MoreHorizontal,
  Package,
  Truck,
  CheckCircle2,
  ShieldCheck,
  ShoppingCart,
  User,
  X,
  ChevronRight,
  Layers,
  Sparkles,
  Calendar,
  FileCheck,
} from 'lucide-react';

export type TabType = 'dashboard' | 'attendance' | 'profile' | 'escalation' | 'checklist' | 'dispatch';

interface BottomNavProps {
  activeTab: string;
  onChangeTab: (tab: any) => void;
  onOpenAttendance?: () => void;
  onOpenProfile?: () => void;
  activeSectionKey?: string;
  onSelectSection?: (sectionKey: string) => void;
  todayAttendance?: {
    isClockedIn: boolean;
    clockInTime?: string;
    isPending?: boolean;
  };
  canViewClients?: boolean;
}

export const BottomNav: React.FC<BottomNavProps> = ({
  activeTab,
  onChangeTab,
  onOpenAttendance,
  onOpenProfile,
  activeSectionKey,
  onSelectSection,
  todayAttendance,
  canViewClients = true,
}) => {
  const [isMoreOpen, setIsMoreOpen] = useState(false);

  const moreSections = [
    {
      key: 'escalation',
      title: 'Escalation',
      desc: 'Client issue logs & tickets tracker',
      icon: ShieldAlert,
      color: 'bg-rose-500/10 text-rose-500 border-rose-500/20',
    },
    {
      key: 'dispatch',
      title: 'Dispatch',
      desc: 'Logistics & Dispatch tracking',
      icon: Truck,
      color: 'bg-sky-500/10 text-sky-500 border-sky-500/20',
    },
    {
      key: 'bom',
      title: 'BOM',
      desc: 'Bill of Materials & Tree',
      icon: Package,
      color: 'bg-amber-500/10 text-amber-500 border-amber-500/20',
    },
    {
      key: 'looseFurniture',
      title: 'Loose Furniture',
      desc: 'Custom furniture items & status',
      icon: Sparkles,
      color: 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
    },
    {
      key: 'qcDesign',
      title: 'QC Design',
      desc: 'Quality control checklist files',
      icon: CheckCircle2,
      color: 'bg-orange-500/10 text-orange-500 border-orange-500/20',
    },
    {
      key: 'finalValidation',
      title: 'Final Production Drawing',
      desc: 'Site validation drawings & files',
      icon: ShieldCheck,
      color: 'bg-teal-500/10 text-teal-500 border-teal-500/20',
    },
    {
      key: 'onSitePurchaseWithItem',
      title: 'On Site Purchase Request',
      desc: 'On-site item purchase requests & receiving logs',
      icon: ShoppingCart,
      color: 'bg-rose-500/10 text-rose-500 border-rose-500/20',
    },
    {
      key: 'executionTimeline',
      title: 'Execution Timeline',
      desc: 'Site progress milestones & dates',
      icon: Calendar,
      color: 'bg-purple-500/10 text-purple-500 border-purple-500/20',
    },
    {
      key: 'handover',
      title: 'Handover',
      desc: 'Final client site handover checklist',
      icon: FileCheck,
      color: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
    },
  ];

  const handleMoreItemClick = (key: string) => {
    setIsMoreOpen(false);
    if (key === 'escalation') {
      onChangeTab('escalation');
    } else if (key === 'dispatch') {
      onChangeTab('dispatch');
    } else if (onSelectSection) {
      onSelectSection(key);
    }
  };

  const isMoreActive =
    isMoreOpen ||
    activeTab === 'escalation' ||
    activeTab === 'dispatch' ||
    (activeTab === 'checklist' && !!activeSectionKey && activeSectionKey !== 'menu');

  return (
    <>
      <div
        className="bg-white border-t border-slate-200/90 pt-2 px-3 flex items-center justify-around sticky bottom-0 z-30 shadow-[0_-4px_25px_rgba(0,0,0,0.07)]"
        style={{
          paddingBottom:
            Capacitor.getPlatform() === 'android'
              ? '2.8rem'
              : Capacitor.getPlatform() === 'ios'
              ? 'max(2rem, calc(0.5rem + env(safe-area-inset-bottom, 0px)))'
              : '0.75rem'
        }}
      >
        {/* If user CANNOT view clients: 2 tabs with icons on the side, fixed size, no jumping */}
        {canViewClients === false ? (
          <div className="flex items-center justify-center gap-3 w-full max-w-xs mx-auto">
            {/* 1. Attendance & Leave Tab */}
            <button
              type="button"
              title="Attendance & Leave"
              aria-label="Attendance & Leave"
              onClick={() => {
                setIsMoreOpen(false);
                onChangeTab('attendance');
                if (onOpenAttendance) onOpenAttendance();
              }}
              className={`flex-1 h-11 rounded-full flex items-center justify-center transition-colors select-none outline-none cursor-pointer ${
                activeTab === 'attendance'
                  ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 text-white font-extrabold shadow-md shadow-orange-500/30'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold'
              }`}
            >
              <div className="relative shrink-0">
                <Clock className="w-5 h-5 shrink-0" />
                {todayAttendance?.isClockedIn && activeTab !== 'attendance' && (
                  <span className="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white animate-pulse" />
                )}
              </div>
            </button>

            {/* 2. Profile Tab */}
            <button
              type="button"
              title="Profile"
              aria-label="Profile"
              onClick={() => {
                setIsMoreOpen(false);
                onChangeTab('profile');
                if (onOpenProfile) onOpenProfile();
              }}
              className={`flex-1 h-11 rounded-full flex items-center justify-center transition-colors select-none outline-none cursor-pointer ${
                activeTab === 'profile'
                  ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 text-white font-extrabold shadow-md shadow-orange-500/30'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold'
              }`}
            >
              <User className="w-5 h-5 shrink-0" />
            </button>
          </div>
        ) : (
          /* If user CAN view clients: 4 tabs with icons on the side, fixed size, no jumping */
          <div className="flex items-center justify-between gap-1.5 w-full">
            {/* 1. CRM Tab */}
            <button
              type="button"
              title="CRM"
              aria-label="CRM"
              onClick={() => {
                setIsMoreOpen(false);
                onChangeTab('dashboard');
              }}
              className={`flex-1 h-11 rounded-full flex items-center justify-center transition-colors select-none outline-none cursor-pointer ${
                activeTab === 'dashboard' && !isMoreOpen
                  ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 text-white font-extrabold shadow-md shadow-orange-500/25'
                  : 'text-slate-600 hover:text-amber-600 hover:bg-slate-100 font-bold'
              }`}
            >
              <Users className="w-5 h-5 shrink-0" />
            </button>

            {/* 2. Attendance Tab */}
            <button
              type="button"
              title="Attendance"
              aria-label="Attendance"
              onClick={() => {
                setIsMoreOpen(false);
                onChangeTab('attendance');
                if (onOpenAttendance) onOpenAttendance();
              }}
              className={`flex-1 h-11 rounded-full flex items-center justify-center transition-colors select-none outline-none cursor-pointer relative ${
                activeTab === 'attendance' && !isMoreOpen
                  ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 text-white font-extrabold shadow-md shadow-orange-500/25'
                  : 'text-slate-600 hover:text-amber-600 hover:bg-slate-100 font-bold'
              }`}
            >
              <div className="relative shrink-0">
                <Clock className="w-5 h-5 shrink-0" />
                {todayAttendance?.isClockedIn && activeTab !== 'attendance' && (
                  <span className="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white animate-pulse" />
                )}
              </div>
            </button>

            {/* 3. Profile Tab */}
            <button
              type="button"
              title="Profile"
              aria-label="Profile"
              onClick={() => {
                setIsMoreOpen(false);
                onChangeTab('profile');
                if (onOpenProfile) onOpenProfile();
              }}
              className={`flex-1 h-11 rounded-full flex items-center justify-center transition-colors select-none outline-none cursor-pointer ${
                activeTab === 'profile' && !isMoreOpen
                  ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 text-white font-extrabold shadow-md shadow-orange-500/25'
                  : 'text-slate-600 hover:text-amber-600 hover:bg-slate-100 font-bold'
              }`}
            >
              <User className="w-5 h-5 shrink-0" />
            </button>

            {/* 4. More Tab */}
            <button
              type="button"
              title="More Sections"
              aria-label="More Sections"
              onClick={() => setIsMoreOpen(!isMoreOpen)}
              className={`flex-1 h-11 rounded-full flex items-center justify-center transition-colors select-none outline-none cursor-pointer relative ${
                isMoreActive
                  ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 text-white font-extrabold shadow-md shadow-orange-500/25'
                  : 'text-slate-600 hover:text-amber-600 hover:bg-slate-100 font-bold'
              }`}
            >
              <div className="relative shrink-0">
                <MoreHorizontal className="w-5 h-5 shrink-0" />
                <span className="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-500 animate-pulse" />
              </div>
            </button>
          </div>
        )}
      </div>

      {/* MORE MENU SLIDE-UP MODAL / SHEET */}
      {isMoreOpen && (
        <div
          className="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-end justify-center p-0 sm:p-4 animate-in fade-in duration-200"
          onClick={() => setIsMoreOpen(false)}
        >
          <div
            className="bg-zinc-950 border border-zinc-800 text-zinc-100 rounded-t-3xl sm:rounded-2xl w-full max-w-lg p-4 space-y-3.5 shadow-2xl max-h-[82vh] overflow-y-auto animate-in slide-in-from-bottom duration-300"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header */}
            <div className="flex items-center justify-between border-b border-zinc-800 pb-3">
              <div className="flex items-center space-x-2">
                <div className="p-2 rounded-xl bg-amber-400/20 text-amber-400 border border-amber-500/30">
                  <Layers className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-bold text-sm text-zinc-100">All Sections &amp; Direct Links</h3>
                  <p className="text-[11px] text-zinc-400">
                    Select Escalation, Dispatch or any module to view direct listings
                  </p>
                </div>
              </div>

              <button
                onClick={() => setIsMoreOpen(false)}
                className="p-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-400 hover:text-white transition-colors cursor-pointer border border-zinc-800"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Links Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
              {moreSections.map((sec) => {
                const Icon = sec.icon;
                const isSelected =
                  (sec.key === 'escalation' && activeTab === 'escalation') ||
                  (sec.key === 'dispatch' && activeTab === 'dispatch') ||
                  (activeTab === 'checklist' && activeSectionKey === sec.key);

                return (
                  <button
                    key={sec.key}
                    onClick={() => handleMoreItemClick(sec.key)}
                    className={`flex items-center justify-between p-2.5 rounded-xl border text-left transition-all cursor-pointer ${
                      isSelected
                        ? 'bg-amber-400/15 border-amber-400 text-amber-400 font-bold shadow-xs'
                        : 'bg-zinc-900/80 border-zinc-800 hover:bg-zinc-900 hover:border-zinc-700 text-zinc-200'
                    }`}
                  >
                    <div className="flex items-center space-x-2.5 min-w-0">
                      <div className={`p-2 rounded-lg border shrink-0 ${sec.color}`}>
                        <Icon className="w-4 h-4" />
                      </div>
                      <div className="min-w-0">
                        <div className="text-xs font-bold truncate text-zinc-100">
                          {sec.title}
                        </div>
                        <div className="text-[10px] text-zinc-400 truncate">
                          {sec.desc}
                        </div>
                      </div>
                    </div>
                    <ChevronRight className="w-4 h-4 text-zinc-500 shrink-0 ml-1" />
                  </button>
                );
              })}
            </div>
          </div>
        </div>
      )}
    </>
  );
};
