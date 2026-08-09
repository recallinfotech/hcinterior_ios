import React, { useState } from 'react';
import { Design3DRequest } from '../../types';
import { Check, X, Plus, Trash2 } from 'lucide-react';

interface Design3DSectionProps {
  request: Design3DRequest;
  onUpdateRequestStatus: (status: 'Approved' | 'Pending' | 'Rejected') => void;
}

interface Design3DItem {
  id: string;
  fileName: string;
  fileUrl: string;
  url: string;
  date: string;
  status: string;
}

interface RemarkItem {
  id: string;
  text: string;
  date: string;
}

export const Design3DSection: React.FC<Design3DSectionProps> = ({
  request,
  onUpdateRequestStatus,
}) => {
  const [isModalOpen, setIsModalOpen] = useState<boolean>(false);
  const [modalStatus, setModalStatus] = useState<'Approved' | 'Pending' | 'Rejected'>('Approved');

  // 3D Designs state
  const [designs, setDesigns] = useState<Design3DItem[]>([
    {
      id: 'd-1',
      fileName: '3D Render Living Room v1',
      fileUrl: '3d_render_livingroom_v1.dwg',
      url: 'https://example.com/view/3d-1',
      date: '2026-07-29',
      status: 'Approved',
    },
  ]);

  // Remarks state
  const [remarkInput, setRemarkInput] = useState<string>('');
  const [remarksList, setRemarksList] = useState<RemarkItem[]>([]);

  const handleModalSubmit = () => {
    onUpdateRequestStatus(modalStatus);
    setIsModalOpen(false);
  };

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

  const handleAddDesign = () => {
    const fileName = prompt('Enter 3D File Name:', '3D Master Bedroom Render');
    if (fileName) {
      const newDesign: Design3DItem = {
        id: `d-${Date.now()}`,
        fileName,
        fileUrl: `${fileName.toLowerCase().replace(/\s+/g, '_')}.pdf`,
        url: '#',
        date: new Date().toISOString().split('T')[0],
        status: 'Approved',
      };
      setDesigns((prev) => [...prev, newDesign]);
    }
  };

  return (
    <div className="space-y-6">
      {/* Card 1: Request 3D Design */}
      <div className="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
        {/* Top Header */}
        <div className="px-4 py-2.5 border-b-2 border-orange-500">
          <h3 className="text-sm font-bold text-slate-800">Request 3D Design</h3>
        </div>

        {/* Content Row matching screenshot */}
        <div className="p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs">
          <div className="flex flex-wrap items-center gap-x-8 gap-y-2 font-medium text-slate-700">
            <div>
              <span className="text-slate-500">Request Type : </span>
              <span className="font-bold text-slate-800">{request.requestType || '3d Design'}</span>
            </div>
            <div>
              <span className="text-slate-500">Request Date : </span>
              <span className="font-semibold text-slate-800">{request.requestDate || '29-07-2026'}</span>
            </div>
            <div className="flex items-center space-x-1.5">
              <span className="text-slate-500">Status : </span>
              <span className="bg-[#28a745] text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider inline-block">
                {request.status || 'Approved'}
              </span>
            </div>
          </div>

          <button
            onClick={() => setIsModalOpen(true)}
            className="bg-[#5cb85c] hover:bg-[#4cae4c] text-white text-xs font-bold px-3.5 py-1.5 rounded flex items-center space-x-1.5 cursor-pointer transition-colors shadow-2xs shrink-0"
          >
            <Check className="w-4 h-4 stroke-[3]" />
            <span>Approve Request</span>
          </button>
        </div>
      </div>

      {/* Card 2: Upload 3D Design */}
      <div className="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
        {/* Header with button */}
        <div className="px-4 py-2.5 border-b-2 border-orange-500 flex items-center justify-between">
          <h3 className="text-sm font-bold text-slate-800">Upload 3D Design</h3>
          <button
            onClick={handleAddDesign}
            className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded border border-slate-300 transition-colors cursor-pointer"
          >
            Add 3D Design
          </button>
        </div>

        {/* Table matching screenshot columns */}
        <div className="overflow-x-auto scrollbar-thin">
          <table className="w-full text-left border-collapse text-xs min-w-[650px]">
            <thead>
              <tr className="bg-slate-50/70 text-slate-700 font-bold border-b border-slate-200 text-xs">
                <th className="py-2.5 px-4 font-bold">File Name</th>
                <th className="py-2.5 px-4 font-bold">File</th>
                <th className="py-2.5 px-4 font-bold">URL</th>
                <th className="py-2.5 px-4 font-bold">Date</th>
                <th className="py-2.5 px-4 font-bold">Status</th>
                <th className="py-2.5 px-4 font-bold">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-800">
              {designs.length > 0 ? (
                designs.map((item) => (
                  <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                    <td className="py-2.5 px-4 font-semibold text-slate-800">{item.fileName}</td>
                    <td className="py-2.5 px-4 font-mono text-sky-600 text-[11px]">{item.fileUrl}</td>
                    <td className="py-2.5 px-4 text-slate-400">—</td>
                    <td className="py-2.5 px-4 font-mono text-slate-600">{item.date}</td>
                    <td className="py-2.5 px-4">
                      <span className="bg-[#28a745] text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                        {item.status}
                      </span>
                    </td>
                    <td className="py-2.5 px-4">
                      <button
                        onClick={() => setDesigns((prev) => prev.filter((d) => d.id !== item.id))}
                        className="text-slate-400 hover:text-red-600 transition-colors p-1"
                        title="Delete"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={6} className="py-6 text-center text-slate-400 text-xs font-medium">
                    No 3D design files uploaded yet.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Card 3: Remarks Section matching screenshot */}
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

      {/* Modal for Approve Request */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
            {/* Modal Green Header */}
            <div className="bg-emerald-500 text-white px-4 py-3 flex items-center justify-between font-bold text-sm">
              <h3>Approve Design Request</h3>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-white hover:text-slate-200 p-0.5 cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Modal Body */}
            <div className="p-5 space-y-4 text-xs text-slate-700">
              <div className="relative">
                <select
                  value={modalStatus}
                  onChange={(e) =>
                    setModalStatus(e.target.value as 'Approved' | 'Pending' | 'Rejected')
                  }
                  className="w-full bg-white border border-slate-300 rounded-lg p-2.5 font-semibold text-slate-800 focus:outline-none focus:border-emerald-500 text-xs"
                >
                  <option value="Approved">Approved</option>
                  <option value="Pending">Pending</option>
                  <option value="Rejected">Rejected</option>
                </select>
              </div>

              <p className="text-slate-600 text-center font-medium">
                Are you sure you want to <strong className="text-slate-900">Update</strong> this design request?
              </p>

              {/* Action Buttons */}
              <div className="flex items-center justify-end space-x-2 pt-2">
                <button
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold rounded text-xs transition-colors cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  onClick={handleModalSubmit}
                  className="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded text-xs transition-colors cursor-pointer shadow-sm"
                >
                  Submit
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
