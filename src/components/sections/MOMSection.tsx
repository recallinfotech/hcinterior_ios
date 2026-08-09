import React, { useState } from 'react';
import { MOMItem } from '../../types';
import { FileCheck, Edit2, Upload, Image as ImageIcon } from 'lucide-react';

interface MOMSectionProps {
  momList: MOMItem[];
  onUpdateMOM?: (item: MOMItem) => void;
}

export const MOMSection: React.FC<MOMSectionProps> = ({ momList }) => {
  const [items, setItems] = useState<MOMItem[]>(momList);

  return (
    <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-4">
      {/* Header matching Image 4 */}
      <div className="flex items-center justify-between border-b-2 border-orange-500 pb-2.5">
        <h2 className="text-base font-bold text-slate-900">MOM / Checklist</h2>
        <span className="text-[10px] text-slate-400 font-semibold">Already MOM/CheckList Added</span>
      </div>

      {/* MOM List Table Card */}
      <div className="space-y-3">
        {items.map((item) => (
          <div
            key={item.id}
            className="bg-slate-50 rounded-xl p-3 border border-slate-200 flex items-center justify-between gap-3 text-xs"
          >
            <div className="flex items-center space-x-3">
              <div className="w-14 h-16 rounded-lg overflow-hidden border border-slate-300 bg-slate-200 shrink-0">
                {item.thumbnailUrl ? (
                  <img
                    src={item.thumbnailUrl}
                    alt="MOM Checklist"
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-slate-400">
                    <ImageIcon className="w-6 h-6" />
                  </div>
                )}
              </div>

              <div>
                <h4 className="font-bold text-slate-900 text-xs">{item.fileName}</h4>
                <p className="text-[10px] text-slate-500 mt-1">{item.date}</p>
                <span className="inline-block mt-1 text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded">
                  Verified Site MOM
                </span>
              </div>
            </div>

            <button
              onClick={() => alert('Editing MOM/Checklist document')}
              className="p-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg transition-colors cursor-pointer shadow-sm"
              title="Edit MOM"
            >
              <Edit2 className="w-4 h-4" />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
};
