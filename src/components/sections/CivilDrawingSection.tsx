import React, { useState } from 'react';
import { CivilDrawing } from '../../types';
import { Edit2, Check, AlertCircle, FileText, ChevronDown } from 'lucide-react';

interface CivilDrawingSectionProps {
  drawings?: CivilDrawing[];
  onAddDrawing?: () => void;
}

interface OldDrawingItem {
  id: string;
  fileName: string;
  thumbnailUrl: string;
  designType: string;
  url: string;
  status: 'Approved' | 'Pending' | 'Rejected';
  uploadedBy: string;
  date: string;
}

export const CivilDrawingSection: React.FC<CivilDrawingSectionProps> = ({
  drawings: initialDrawings = [],
}) => {
  // Primary civil drawings list
  const [drawingList, setDrawingList] = useState<CivilDrawing[]>(() => {
    if (initialDrawings && initialDrawings.length > 0) {
      return initialDrawings;
    }
    return [
      {
        id: 'cd-1',
        fileName: 'Test Client : 22 july_Drawing_file',
        fileUrl: '1784715410_6a609892c80b9.pdf',
        designType: 'Civil Design',
        status: 'Approved',
        uploadedBy: 'Nishant Singh',
        date: '2026-07-22 3:46 PM',
      },
    ];
  });

  // Old design records (matching Image 1)
  const [oldDrawings, setOldDrawings] = useState<OldDrawingItem[]>([
    {
      id: 'old-1',
      fileName: 'Test Client : 22 july_Drawing_file',
      thumbnailUrl:
        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=120&auto=format&fit=crop&q=80',
      designType: 'Civil Design',
      url: '',
      status: 'Approved',
      uploadedBy: 'Nishant Singh',
      date: '2026-07-22 12:29 PM',
    },
  ]);

  // Toggle state
  const [showOldDesign, setShowOldDesign] = useState<boolean>(false);
  const [showForm, setShowForm] = useState<boolean>(false);
  const [editingId, setEditingId] = useState<string | null>(null);

  // Form fields matching Image 2
  const [formDesignType, setFormDesignType] = useState<string>('Civil Design');
  const [formFileName, setFormFileName] = useState<string>('Test Client : 22 july_Drawing_file');
  const [formFileNameDisplay, setFormFileNameDisplay] = useState<string>('No file chosen');

  // Remarks state
  const [remarksText, setRemarksText] = useState<string>('');

  // Handle Edit click from table (Image 2) or old design table (Image 1)
  const handleEditClick = (item: CivilDrawing | OldDrawingItem) => {
    setShowForm(true);
    setEditingId(item.id);
    setFormDesignType(item.designType || 'Civil Design');
    setFormFileName(item.fileName || 'Test Client : 22 july_Drawing_file');
    setFormFileNameDisplay('1784715410_6a609892c80b9.pdf');
  };

  // Handle Add Civil Drawing button
  const handleAddNewClick = () => {
    setShowForm(true);
    setEditingId(null);
    setFormDesignType('Civil Design');
    setFormFileName('Test Client : 22 july_Drawing_file');
    setFormFileNameDisplay('No file chosen');
  };

  // Handle Save
  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingId) {
      setDrawingList((prev) =>
        prev.map((item) =>
          item.id === editingId
            ? {
                ...item,
                designType: formDesignType,
                fileName: formFileName,
                fileUrl:
                  formFileNameDisplay !== 'No file chosen'
                    ? formFileNameDisplay
                    : item.fileUrl,
                date: '2026-07-22 3:46 PM',
              }
            : item
        )
      );
    } else {
      const newDrawing: CivilDrawing = {
        id: `cd-${Date.now()}`,
        fileName: formFileName || 'Test Client : 22 july_Drawing_file',
        fileUrl:
          formFileNameDisplay !== 'No file chosen'
            ? formFileNameDisplay
            : '1784715410_6a609892c80b9.pdf',
        designType: formDesignType,
        status: 'Approved',
        uploadedBy: 'Nishant Singh',
        date: '2026-07-22 3:46 PM',
      };
      setDrawingList((prev) => [newDrawing, ...prev]);
    }
    setShowForm(false);
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setFormFileNameDisplay(e.target.files[0].name);
    }
  };

  return (
    <div className="bg-white rounded-xl border border-slate-200 border-t-4 border-t-orange-500 shadow-sm overflow-hidden p-4 sm:p-6 space-y-6">
      {/* Top Section Header matching Image 2 */}
      <div className="flex items-center justify-between border-b border-slate-100 pb-3">
        <h3 className="text-sm font-extrabold text-slate-800 tracking-wide uppercase">
          Civil Drawing
        </h3>
        <button
          onClick={handleAddNewClick}
          className="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded border border-slate-300 transition-colors cursor-pointer shadow-2xs"
        >
          Add Civil Drawing
        </button>
      </div>

      {/* Edit / Add Form (Image 2 Content) */}
      {showForm && (
        <form
          onSubmit={handleSave}
          className="bg-slate-50/60 p-4 sm:p-5 rounded-lg border border-slate-200/80 space-y-4 animate-in fade-in"
        >
          <div className="max-w-2xl space-y-3">
            {/* Design Type */}
            <div className="grid grid-cols-1 sm:grid-cols-3 items-center gap-2">
              <label className="text-xs font-bold text-slate-600 sm:text-right">
                Design Type <span className="text-red-500">*</span>
              </label>
              <div className="sm:col-span-2 relative">
                <select
                  value={formDesignType}
                  onChange={(e) => setFormDesignType(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded px-3 py-1.5 text-xs font-medium text-slate-800 appearance-none focus:outline-none focus:border-sky-500 pr-8 cursor-pointer"
                >
                  <option value="Civil Design">Civil Design</option>
                  <option value="3D Design">3D Design</option>
                  <option value="Architectural Plan">Architectural Plan</option>
                  <option value="Structural Drawing">Structural Drawing</option>
                  <option value="Plumbing & Electrical">Plumbing & Electrical</option>
                </select>
                <ChevronDown className="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
              </div>
            </div>

            {/* File Name */}
            <div className="grid grid-cols-1 sm:grid-cols-3 items-center gap-2">
              <label className="text-xs font-bold text-slate-600 sm:text-right">
                File Name <span className="text-red-500">*</span>
              </label>
              <div className="sm:col-span-2">
                <input
                  type="text"
                  value={formFileName}
                  onChange={(e) => setFormFileName(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded px-3 py-1.5 text-xs font-medium text-slate-800 focus:outline-none focus:border-sky-500"
                  required
                />
              </div>
            </div>

            {/* Upload Files */}
            <div className="grid grid-cols-1 sm:grid-cols-3 items-start gap-2">
              <label className="text-xs font-bold text-slate-600 sm:text-right pt-1.5">
                Upload Files <span className="text-red-500">*</span>
              </label>
              <div className="sm:col-span-2 space-y-1">
                <div className="flex items-center space-x-2 bg-white border border-slate-300 rounded p-1">
                  <label className="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold px-3 py-1 rounded border border-slate-300 cursor-pointer transition-colors shrink-0">
                    Choose Files
                    <input
                      type="file"
                      onChange={handleFileChange}
                      className="hidden"
                      accept=".jpg,.jpeg,.png,.pdf,.dwg"
                    />
                  </label>
                  <span className="text-xs text-slate-500 truncate px-1 font-mono">
                    {formFileNameDisplay}
                  </span>
                </div>
                <p className="text-[10px] text-slate-400 font-medium">
                  Allowed: JPG, PNG, PDF, DWG
                </p>
              </div>
            </div>

            {/* Save Button */}
            <div className="flex justify-end pt-2">
              <button
                type="submit"
                className="bg-[#5b9bd5] hover:bg-[#4a89c4] text-white text-xs font-bold px-6 py-1.5 rounded shadow-2xs transition-colors cursor-pointer"
              >
                Save
              </button>
            </div>
          </div>
        </form>
      )}

      {/* Main Active Drawings Table (Image 2 Table Content) */}
      <div className="border border-slate-200 rounded-lg overflow-hidden bg-white">
        <div className="overflow-x-auto scrollbar-thin">
          <table className="w-full text-left border-collapse min-w-[700px] text-xs">
            <thead>
              <tr className="bg-slate-50/80 text-slate-700 font-extrabold border-b border-slate-200">
                <th className="py-2.5 px-4">File Name</th>
                <th className="py-2.5 px-4">File</th>
                <th className="py-2.5 px-4">Design Type</th>
                <th className="py-2.5 px-4">URL</th>
                <th className="py-2.5 px-4">Status</th>
                <th className="py-2.5 px-4">Uploaded By</th>
                <th className="py-2.5 px-4">Date</th>
                <th className="py-2.5 px-4 text-center">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-800">
              {drawingList.map((drw) => (
                <tr key={drw.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="py-3 px-4 font-semibold text-slate-800">
                    {drw.fileName}
                  </td>
                  <td className="py-3 px-4">
                    <a
                      href="#"
                      onClick={(e) => {
                        e.preventDefault();
                        alert(`Opening file: ${drw.fileUrl}`);
                      }}
                      className="text-sky-600 hover:underline font-mono text-[11px] break-all"
                    >
                      {drw.fileUrl}
                    </a>
                  </td>
                  <td className="py-3 px-4 text-slate-700">{drw.designType}</td>
                  <td className="py-3 px-4 text-slate-400">—</td>
                  <td className="py-3 px-4">
                    <span className="bg-[#28a745] text-white text-[10px] font-bold px-2.5 py-0.5 rounded uppercase tracking-wider inline-block">
                      {drw.status}
                    </span>
                  </td>
                  <td className="py-3 px-4 font-medium text-slate-700">
                    {drw.uploadedBy}
                  </td>
                  <td className="py-3 px-4 font-mono text-slate-600 text-[11px]">
                    {drw.date}
                  </td>
                  <td className="py-3 px-4 text-center">
                    <button
                      onClick={() => handleEditClick(drw)}
                      className="p-1.5 bg-[#3399ff] hover:bg-blue-600 text-white rounded cursor-pointer transition-colors inline-flex items-center justify-center shadow-2xs"
                      title="Edit Drawing"
                    >
                      <Edit2 className="w-3.5 h-3.5" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* See Old Design Button (Cyan `#17a2b8`) */}
      <div className="pt-1">
        <button
          onClick={() => setShowOldDesign(!showOldDesign)}
          className="bg-[#17a2b8] hover:bg-[#138496] text-white text-xs font-bold px-4 py-2 rounded-xs shadow-2xs transition-colors cursor-pointer inline-flex items-center space-x-1.5"
        >
          <span>See Old Design</span>
        </button>
      </div>

      {/* Image 1 Content: Old Design View (Toggled when clicking "See Old Design") */}
      {showOldDesign && (
        <div className="space-y-4 pt-2 border-t border-slate-200/80 animate-in fade-in">
          <div className="border border-slate-200 rounded-lg overflow-hidden bg-white">
            <div className="overflow-x-auto scrollbar-thin">
              <table className="w-full text-left border-collapse min-w-[700px] text-xs">
                <thead>
                  <tr className="bg-slate-50/80 text-slate-700 font-extrabold border-b border-slate-200">
                    <th className="py-2.5 px-4">File Name</th>
                    <th className="py-2.5 px-4">File</th>
                    <th className="py-2.5 px-4">Design Type</th>
                    <th className="py-2.5 px-4">URL</th>
                    <th className="py-2.5 px-4">Status</th>
                    <th className="py-2.5 px-4">Uploaded By</th>
                    <th className="py-2.5 px-4">Date</th>
                    <th className="py-2.5 px-4 text-center">Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 text-slate-800">
                  {oldDrawings.map((oldItem) => (
                    <tr
                      key={oldItem.id}
                      className="hover:bg-slate-50/50 transition-colors"
                    >
                      <td className="py-3 px-4 font-semibold text-slate-800">
                        {oldItem.fileName}
                      </td>
                      <td className="py-3 px-4">
                        <img
                          src={oldItem.thumbnailUrl}
                          alt="Drawing Thumbnail"
                          className="w-12 h-12 object-cover rounded border border-slate-300 shadow-2xs"
                        />
                      </td>
                      <td className="py-3 px-4 text-slate-700">
                        {oldItem.designType}
                      </td>
                      <td className="py-3 px-4 text-slate-400">—</td>
                      <td className="py-3 px-4">
                        <span className="bg-[#28a745] text-white text-[10px] font-bold px-2.5 py-0.5 rounded uppercase tracking-wider inline-block">
                          {oldItem.status}
                        </span>
                      </td>
                      <td className="py-3 px-4 font-medium text-slate-700">
                        {oldItem.uploadedBy}
                      </td>
                      <td className="py-3 px-4 font-mono text-slate-600 text-[11px]">
                        {oldItem.date}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <div className="flex items-center justify-center space-x-1.5">
                          {/* Edit button: opens the Edit Form (Image 2 Content) */}
                          <button
                            onClick={() => handleEditClick(oldItem)}
                            className="p-1.5 bg-[#3399ff] hover:bg-blue-600 text-white rounded cursor-pointer transition-colors inline-flex items-center justify-center shadow-2xs"
                            title="Edit"
                          >
                            <Edit2 className="w-3.5 h-3.5" />
                          </button>
                          {/* Approve/Confirm button */}
                          <button
                            onClick={() =>
                              alert(`Confirmed old design: ${oldItem.fileName}`)
                            }
                            className="p-1.5 bg-[#28a745] hover:bg-emerald-700 text-white rounded cursor-pointer transition-colors inline-flex items-center justify-center shadow-2xs"
                            title="Approve / Confirm"
                          >
                            <Check className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Remarks Section matching Image 1 & Image 2 */}
      <div className="pt-4 space-y-3">
        <div className="bg-sky-50/50 p-2 border-b-2 border-[#17a2b8] flex items-center justify-between">
          <h4 className="text-xs font-extrabold text-slate-800 tracking-wide uppercase">
            Remarks
          </h4>
        </div>

        <div className="space-y-2">
          <textarea
            value={remarksText}
            onChange={(e) => setRemarksText(e.target.value)}
            placeholder="Add any remarks or notes regarding civil drawings here..."
            className="w-full bg-slate-50 border border-slate-200 rounded p-2.5 text-xs text-slate-800 focus:outline-none focus:border-sky-500 min-h-[60px]"
          />
          {remarksText.trim() && (
            <div className="flex justify-end">
              <button
                onClick={() => {
                  alert('Remarks saved');
                }}
                className="bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold px-4 py-1 rounded shadow-2xs cursor-pointer"
              >
                Save Remarks
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
