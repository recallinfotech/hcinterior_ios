import React, { useState } from 'react';
import { Calendar, RotateCcw, ChevronDown } from 'lucide-react';

export interface FilterState {
  role?: string;
  user?: string;
  paymentFilter?: string;
  siteValidationFrom: string;
  siteValidationTo: string;
  dispatchFrom: string;
  dispatchTo: string;
  siteStartFrom: string;
  siteStartTo: string;
  handOverFrom: string;
  handOverTo: string;
  ktMeetingFrom: string;
  ktMeetingTo: string;
}

export const INITIAL_FILTERS: FilterState = {
  role: 'All Roles',
  user: 'All Users',
  paymentFilter: 'All Payments',
  siteValidationFrom: '',
  siteValidationTo: '',
  dispatchFrom: '',
  dispatchTo: '',
  siteStartFrom: '',
  siteStartTo: '',
  handOverFrom: '',
  handOverTo: '',
  ktMeetingFrom: '',
  ktMeetingTo: '',
};

interface ClientFilterPanelProps {
  filters: FilterState;
  onFilterChange: (newFilters: FilterState) => void;
  onResetFilters: () => void;
  isCompact?: boolean;
}

export const ClientFilterPanel: React.FC<ClientFilterPanelProps> = ({
  filters,
  onFilterChange,
  onResetFilters,
}) => {
  const [isExpanded, setIsExpanded] = useState(false);

  const handleChange = (key: keyof FilterState, value: string) => {
    onFilterChange({
      ...filters,
      [key]: value,
    });
  };

  return (
    <div className="bg-zinc-50 border border-zinc-200 rounded-2xl p-3.5 space-y-3 shadow-xs">
      {/* Date Range Filters Container */}
      <div className="bg-white rounded-xl border border-zinc-200 shadow-2xs overflow-hidden">
        <div
          onClick={() => setIsExpanded(!isExpanded)}
          className="flex items-center justify-between p-3 bg-zinc-50 hover:bg-zinc-100 transition-colors cursor-pointer select-none"
        >
          <h4 className="font-bold text-zinc-900 text-xs flex items-center space-x-1.5">
            <Calendar className="w-4 h-4 text-amber-500" />
            <span>Date Range Filters</span>
          </h4>

          <div className="flex items-center space-x-2">
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                onResetFilters();
              }}
              className="text-[10px] font-bold text-zinc-500 hover:text-amber-600 flex items-center space-x-1 transition-colors cursor-pointer"
            >
              <RotateCcw className="w-3 h-3" />
              <span>Reset Filters</span>
            </button>
            <ChevronDown
              className={`w-4 h-4 text-zinc-500 transition-transform duration-200 ${
                isExpanded ? 'rotate-180' : ''
              }`}
            />
          </div>
        </div>

        {/* Date Inputs Grid */}
        {isExpanded && (
          <div className="p-3 pt-0 border-t border-zinc-100 space-y-2 text-[11px]">
          {/* Site Validation */}
          <div className="space-y-1">
            <span className="font-semibold text-zinc-800 text-[11px] block">Site Validation</span>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">From</span>
                <input
                  type="date"
                  value={filters.siteValidationFrom}
                  onChange={(e) => handleChange('siteValidationFrom', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">To</span>
                <input
                  type="date"
                  value={filters.siteValidationTo}
                  onChange={(e) => handleChange('siteValidationTo', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
            </div>
          </div>

          {/* Dispatch Date */}
          <div className="space-y-1">
            <span className="font-semibold text-zinc-800 text-[11px] block">Dispatch Date</span>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">From</span>
                <input
                  type="date"
                  value={filters.dispatchFrom}
                  onChange={(e) => handleChange('dispatchFrom', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">To</span>
                <input
                  type="date"
                  value={filters.dispatchTo}
                  onChange={(e) => handleChange('dispatchTo', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
            </div>
          </div>

          {/* Site Start */}
          <div className="space-y-1">
            <span className="font-semibold text-zinc-800 text-[11px] block">Site Start</span>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">From</span>
                <input
                  type="date"
                  value={filters.siteStartFrom}
                  onChange={(e) => handleChange('siteStartFrom', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">To</span>
                <input
                  type="date"
                  value={filters.siteStartTo}
                  onChange={(e) => handleChange('siteStartTo', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
            </div>
          </div>

          {/* Hand Over Date */}
          <div className="space-y-1">
            <span className="font-semibold text-zinc-800 text-[11px] block">Hand Over Date</span>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">From</span>
                <input
                  type="date"
                  value={filters.handOverFrom}
                  onChange={(e) => handleChange('handOverFrom', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">To</span>
                <input
                  type="date"
                  value={filters.handOverTo}
                  onChange={(e) => handleChange('handOverTo', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
            </div>
          </div>

          {/* KT Meeting */}
          <div className="space-y-1">
            <span className="font-semibold text-zinc-800 text-[11px] block">KT Meeting</span>
            <div className="grid grid-cols-2 gap-2">
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">From</span>
                <input
                  type="date"
                  value={filters.ktMeetingFrom}
                  onChange={(e) => handleChange('ktMeetingFrom', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 block mb-0.5">To</span>
                <input
                  type="date"
                  value={filters.ktMeetingTo}
                  onChange={(e) => handleChange('ktMeetingTo', e.target.value)}
                  className="w-full bg-white border border-zinc-200 rounded-lg p-1.5 text-zinc-800 text-[11px] focus:outline-none focus:border-amber-400"
                />
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  </div>
);
};
