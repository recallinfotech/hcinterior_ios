import React, { useState } from 'react';
import {
  Users,
  ShieldAlert,
  Clock,
  Grid,
  MoreHorizontal,
  FileText,
  Calendar,
  Package,
  Truck,
  CheckCircle2,
  ShieldCheck,
  ShoppingCart,
  CreditCard,
  X,
  ChevronRight,
  Layers,
  Sparkles,
} from 'lucide-react';

export type TabType = 'dashboard' | 'escalation' | 'checklist' | 'dispatch';

interface BottomNavProps {
  activeTab: TabType;
  onChangeTab: (tab: TabType) => void;
  activeSectionKey?: string;
  onSelectSection?: (sectionKey: string) => void;
}

export const BottomNav: React.FC<BottomNavProps> = ({
  activeTab,
  onChangeTab,
  activeSectionKey,
  onSelectSection,
}) => {
  const [isMoreOpen, setIsMoreOpen] = useState(false);

  const mainNavItems = [
    { id: 'dashboard' as TabType, label: 'Client', icon: Users },
    { id: 'escalation' as TabType, label: 'Escalation', icon: ShieldAlert },
    { id: 'dispatch' as TabType, label: 'Dispatch', icon: Truck },
  ];

  const moreSections = [
    {
      key: 'bom',
      title: 'BOM',
      desc: 'Bill of Materials & Tree',
      icon: Package,
      color: 'bg-amber-500/10 text-amber-500 border-amber-500/20',
    },
    {
      key: 'dispatch',
      title: 'Dispatch',
      desc: 'Logistics & Dispatch tracking',
      icon: Truck,
      color: 'bg-sky-500/10 text-sky-500 border-sky-500/20',
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
      key: 'onSitePurchaseRequest',
      title: 'On Site Purchase',
      desc: 'Local material purchase logs',
      icon: ShoppingCart,
      color: 'bg-rose-500/10 text-rose-500 border-rose-500/20',
    },
  ];

  const handleMoreItemClick = (key: string) => {
    setIsMoreOpen(false);
    if (onSelectSection) {
      onSelectSection(key);
    }
  };

  const isMoreActive =
    isMoreOpen ||
    (activeTab === 'checklist' &&
      !!activeSectionKey &&
      activeSectionKey !== 'workflow');

  return (
    <>
      <div className="bg-zinc-950 border-t border-zinc-800 text-zinc-400 py-1.5 px-2 flex items-center justify-around sticky bottom-0 z-30 shadow-2xl">
        {mainNavItems.map((tab) => {
          const Icon = tab.icon;
          const isActive = activeTab === tab.id && !isMoreOpen;
          return (
            <button
              key={tab.id}
              onClick={() => {
                setIsMoreOpen(false);
                onChangeTab(tab.id);
              }}
              className={`flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all cursor-pointer relative ${
                isActive
                  ? 'text-amber-400 font-bold bg-amber-400/10 border border-amber-500/20'
                  : 'hover:text-zinc-200 hover:bg-zinc-900 font-medium'
              }`}
            >
              <div className="relative">
                <Icon
                  className={`w-5 h-5 ${
                    isActive ? 'text-amber-400 scale-110' : 'text-zinc-400'
                  } transition-transform`}
                />
              </div>
              <span className="text-[10px] mt-0.5 tracking-tight font-medium">
                {tab.label}
              </span>
            </button>
          );
        })}

        {/* 5th Button: MORE */}
        <button
          onClick={() => setIsMoreOpen(!isMoreOpen)}
          className={`flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all cursor-pointer relative ${
            isMoreActive
              ? 'text-amber-400 font-bold bg-amber-400/10 border border-amber-500/20'
              : 'hover:text-zinc-200 hover:bg-zinc-900 font-medium'
          }`}
        >
          <div className="relative">
            <MoreHorizontal
              className={`w-5 h-5 ${
                isMoreActive ? 'text-amber-400 scale-110' : 'text-zinc-400'
              } transition-transform`}
            />
            <span className="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-400 animate-pulse" />
          </div>
          <span className="text-[10px] mt-0.5 tracking-tight font-bold">More</span>
        </button>
      </div>

      {/* MORE MENU SLIDE-UP MODAL / SHEET */}
      {isMoreOpen && (
        <div
          className="fixed inset-0 bg-zinc-950/80 backdrop-blur-xs z-50 flex items-end justify-center p-0 sm:p-4 animate-in fade-in duration-200"
          onClick={() => setIsMoreOpen(false)}
        >
          <div
            className="bg-zinc-950 border border-zinc-800 text-zinc-100 rounded-t-3xl sm:rounded-2xl w-full max-w-lg p-4 space-y-3.5 shadow-2xl max-h-[80vh] overflow-y-auto animate-in slide-in-from-bottom duration-300"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header */}
            <div className="flex items-center justify-between border-b border-zinc-800 pb-3">
              <div className="flex items-center space-x-2">
                <div className="p-2 rounded-xl bg-amber-400/20 text-amber-400 border border-amber-500/30">
                  <Layers className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-bold text-sm text-zinc-100">All Sections & Links</h3>
                  <p className="text-[11px] text-zinc-400">
                    Select any module to view client data
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
                const isSelected = activeSectionKey === sec.key;
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

