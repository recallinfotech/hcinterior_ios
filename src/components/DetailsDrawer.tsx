import React from 'react';
import { DetailSectionItem } from '../types';
import { Check, ChevronRight, CheckSquare, Layers } from 'lucide-react';

interface DetailsDrawerProps {
  items: DetailSectionItem[];
  selectedKey: string;
  onSelectItem: (key: string) => void;
  clientName?: string;
}

export const DetailsDrawer: React.FC<DetailsDrawerProps> = ({
  items,
  selectedKey,
  onSelectItem,
  clientName,
}) => {
  return (
    <div className="bg-slate-50 min-h-full p-3.5 space-y-3">
      {/* Header Orange Bar matching Image 2 */}
      <div className="bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-sm px-4 py-2.5 rounded-xl shadow-md flex items-center justify-between">
        <div className="flex items-center space-x-2">
          <Layers className="w-4 h-4 text-orange-100" />
          <span>Details Menu</span>
        </div>
        {clientName && (
          <span className="text-[10px] bg-white/20 text-white font-semibold px-2 py-0.5 rounded-full">
            {clientName}
          </span>
        )}
      </div>

      {/* Vertical List Card matching Image 2 structure */}
      <div className="bg-white rounded-2xl border border-slate-200/90 shadow-sm divide-y divide-slate-100 overflow-hidden">
        {items.map((item) => {
          const isSelected = selectedKey === item.key;
          return (
            <button
              key={item.id}
              onClick={() => onSelectItem(item.key)}
              className={`w-full px-4 py-3 flex items-center justify-between text-left transition-all cursor-pointer ${
                isSelected
                  ? 'bg-orange-50/80 font-bold text-orange-700 border-l-4 border-orange-500'
                  : 'hover:bg-slate-50 text-slate-700 font-medium'
              }`}
            >
              <span className="text-xs">{item.title}</span>

              <div className="flex items-center space-x-2">
                {item.completed ? (
                  <div className="w-5 h-5 rounded bg-emerald-500 text-white flex items-center justify-center text-xs shadow-sm shadow-emerald-500/30">
                    <Check className="w-3.5 h-3.5 stroke-[3]" />
                  </div>
                ) : (
                  <ChevronRight className={`w-4 h-4 ${isSelected ? 'text-orange-500' : 'text-slate-300'}`} />
                )}
              </div>
            </button>
          );
        })}
      </div>
    </div>
  );
};
