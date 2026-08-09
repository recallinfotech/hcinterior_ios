import React, { useState } from 'react';
import { KTRecord } from '../../types';
import { Check, MessageSquare, Trash2 } from 'lucide-react';

interface KTSectionProps {
  ktData: KTRecord;
}

interface RemarkItem {
  id: string;
  text: string;
  date: string;
}

export const KTSection: React.FC<KTSectionProps> = ({ ktData }) => {
  const [isDone, setIsDone] = useState(ktData.ktCheck === 'Done');
  const [remarkInput, setRemarkInput] = useState('');
  const [remarksList, setRemarksList] = useState<RemarkItem[]>([]);

  const handleAddRemark = (e: React.FormEvent) => {
    e.preventDefault();
    if (!remarkInput.trim()) return;

    const newRemark: RemarkItem = {
      id: `rem-${Date.now()}`,
      text: remarkInput.trim(),
      date: new Date().toLocaleString(),
    };

    setRemarksList((prev) => [newRemark, ...prev]);
    setRemarkInput('');
  };

  const handleDeleteRemark = (id: string) => {
    setRemarksList((prev) => prev.filter((r) => r.id !== id));
  };

  return (
    <div className="space-y-6">
      {/* Main KT Record Card */}
      <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-4">
        <div className="border-b-2 border-orange-500 pb-2.5">
          <h2 className="text-base font-bold text-slate-900">KT (Knowledge Transfer)</h2>
        </div>

        <div className="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs space-y-3">
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 items-center">
            <div>
              <span className="text-[10px] text-slate-400 font-semibold block uppercase">Request Type</span>
              <span className="font-bold text-slate-800 text-xs">{ktData.requestType}</span>
            </div>

            <div>
              <span className="text-[10px] text-slate-400 font-semibold block uppercase">Request Date</span>
              <span className="font-medium text-slate-700">{ktData.requestDate}</span>
            </div>

            <div>
              <span className="text-[10px] text-slate-400 font-semibold block uppercase">Status</span>
              <span className="inline-block mt-0.5 px-2 py-0.5 rounded bg-emerald-500 text-white font-bold text-[10px]">
                {ktData.status}
              </span>
            </div>

            <div>
              <span className="text-[10px] text-slate-400 font-semibold block uppercase">KT Check</span>
              <span className="inline-block mt-0.5 px-2 py-0.5 rounded bg-emerald-500 text-white font-bold text-[10px]">
                {isDone ? 'Done' : 'Pending'}
              </span>
            </div>
          </div>

          <div className="pt-3 border-t border-slate-200 flex justify-end">
            <button
              onClick={() => setIsDone(!isDone)}
              className={`px-3 py-1.5 rounded-lg font-bold text-xs flex items-center space-x-1.5 shadow-sm transition-colors cursor-pointer ${
                isDone
                  ? 'bg-emerald-500 hover:bg-emerald-600 text-white'
                  : 'bg-slate-200 hover:bg-slate-300 text-slate-700'
              }`}
            >
              <Check className="w-4 h-4 stroke-[3]" />
              <span>{isDone ? 'KT Checked' : 'Mark KT Check'}</span>
            </button>
          </div>
        </div>
      </div>

      {/* Remarks Section matching screenshot */}
      <div className="bg-[#f0f7ff] border border-sky-100 rounded-lg p-4 space-y-3.5 shadow-2xs">
        {/* Remarks Header with full blue line */}
        <div className="border-b-2 border-[#5b9bd5] pb-2">
          <h3 className="text-sm font-bold text-slate-700">Remarks</h3>
        </div>

        {/* Input Form */}
        <form onSubmit={handleAddRemark} className="space-y-3">
          <textarea
            value={remarkInput}
            onChange={(e) => setRemarkInput(e.target.value)}
            placeholder="Enter remark..."
            className="w-full bg-white border border-slate-200 rounded p-3 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-[#5b9bd5] focus:ring-1 focus:ring-[#5b9bd5] min-h-[70px] resize-y"
          />

          <div>
            <button
              type="submit"
              className="bg-[#5b9bd5] hover:bg-[#4a89c4] text-white font-bold text-xs px-4 py-2 rounded shadow-2xs transition-colors cursor-pointer inline-flex items-center space-x-1.5"
            >
              <span>Add Remark</span>
            </button>
          </div>
        </form>

        {/* Display List of Remarks */}
        {remarksList.length > 0 ? (
          <div className="pt-2 border-t border-sky-200/60 space-y-2">
            <span className="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">
              Saved Remarks ({remarksList.length})
            </span>
            <div className="space-y-2">
              {remarksList.map((item) => (
                <div
                  key={item.id}
                  className="bg-white border border-slate-200 rounded p-3 text-xs flex items-start justify-between gap-3 shadow-2xs"
                >
                  <div className="space-y-1">
                    <p className="text-slate-800 font-medium whitespace-pre-wrap">{item.text}</p>
                    <span className="text-[10px] text-slate-400 font-mono block">{item.date}</span>
                  </div>
                  <button
                    onClick={() => handleDeleteRemark(item.id)}
                    className="text-slate-400 hover:text-red-600 transition-colors cursor-pointer p-1 rounded hover:bg-red-50"
                    title="Delete Remark"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              ))}
            </div>
          </div>
        ) : (
          <div className="pt-2 border-t border-sky-200/60 text-xs text-slate-500 italic p-2 bg-white rounded border border-slate-200 font-medium">
            No Remarks
          </div>
        )}
      </div>
    </div>
  );
};
