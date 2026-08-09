import React, { useState } from 'react';
import { ValidationDrawing } from '../../types';
import { FileText, Plus, Check, Eye, X, Upload } from 'lucide-react';

interface ValidationSectionProps {
  postDrawing: ValidationDrawing;
}

export const ValidationSection: React.FC<ValidationSectionProps> = ({ postDrawing }) => {
  const [showAddModularModal, setShowAddModularModal] = useState<boolean>(false);
  const [designStyle, setDesignStyle] = useState<string>('None');
  const [designType, setDesignType] = useState<string>('Modular Design');
  const [fileNameInput, setFileNameInput] = useState<string>(postDrawing.fileName);
  const [uploadUrl, setUploadUrl] = useState<string>('gefuigjiodfjgjskehn');

  const handleSaveModular = (e: React.FormEvent) => {
    e.preventDefault();
    alert('Modular Drawing saved successfully!');
    setShowAddModularModal(false);
  };

  return (
    <div className="space-y-4">
      {/* Top Header & Button matching Image 10 */}
      <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-4">
        <div className="flex items-center justify-between border-b-2 border-orange-500 pb-2">
          <h2 className="text-base font-bold text-slate-900">Validation</h2>
          <button
            onClick={() => setShowAddModularModal(true)}
            className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded border border-slate-300 transition-colors cursor-pointer"
          >
            Add Modular Drawing
          </button>
        </div>

        {/* Form Modal for Add Modular Drawing matching Image 11 */}
        {showAddModularModal && (
          <form
            onSubmit={handleSaveModular}
            className="bg-slate-50 p-4 rounded-xl border border-slate-300 space-y-3 text-xs text-slate-800 animate-in fade-in duration-200"
          >
            <div className="flex items-center justify-between pb-2 border-b border-slate-200 font-bold text-slate-900">
              <h3>Add Modular Drawing Form</h3>
              <button
                type="button"
                onClick={() => setShowAddModularModal(false)}
                className="text-slate-400 hover:text-slate-600"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
              <label className="font-semibold text-slate-600 sm:text-right pr-2">
                Design Style <span className="text-rose-500">*</span>
              </label>
              <select
                value={designStyle}
                onChange={(e) => setDesignStyle(e.target.value)}
                className="sm:col-span-2 bg-white border border-slate-300 rounded p-1.5 focus:outline-none focus:border-sky-500 text-xs"
              >
                <option value="None">None</option>
                <option value="Modern Italian">Modern Italian</option>
                <option value="Minimalist Luxury">Minimalist Luxury</option>
              </select>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
              <label className="font-semibold text-slate-600 sm:text-right pr-2">
                Design Type <span className="text-rose-500">*</span>
              </label>
              <select
                value={designType}
                onChange={(e) => setDesignType(e.target.value)}
                className="sm:col-span-2 bg-white border border-slate-300 rounded p-1.5 focus:outline-none focus:border-sky-500 text-xs"
              >
                <option value="Modular Design">Modular Design</option>
                <option value="Civil Layout">Civil Layout</option>
                <option value="Electrical Layout">Electrical Layout</option>
              </select>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
              <label className="font-semibold text-slate-600 sm:text-right pr-2">
                File Name <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={fileNameInput}
                onChange={(e) => setFileNameInput(e.target.value)}
                className="sm:col-span-2 bg-white border border-slate-300 rounded p-1.5 text-xs focus:outline-none focus:border-sky-500"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
              <label className="font-semibold text-slate-600 sm:text-right pr-2">Upload URL</label>
              <input
                type="text"
                value={uploadUrl}
                onChange={(e) => setUploadUrl(e.target.value)}
                className="sm:col-span-2 bg-white border border-slate-300 rounded p-1.5 text-xs focus:outline-none focus:border-sky-500"
              />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
              <label className="font-semibold text-slate-600 sm:text-right pr-2">
                Upload Files <span className="text-rose-500">*</span>
              </label>
              <div className="sm:col-span-2">
                <input
                  type="file"
                  className="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100"
                />
                <span className="text-[10px] text-slate-400 mt-0.5 block">Allowed: JPG, PNG, PDF, DWG</span>
              </div>
            </div>

            <div className="flex justify-end pt-2">
              <button
                type="submit"
                className="px-5 py-1.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs rounded transition-colors cursor-pointer"
              >
                Save
              </button>
            </div>
          </form>
        )}

        {/* Pre Validation Drawing Section */}
        <div className="border-t border-slate-100 pt-3 space-y-2">
          <h3 className="text-xs font-bold text-slate-800">Pre Validation Drawing</h3>
          <div className="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
            <button
              onClick={() => alert('Viewing old pre validation designs')}
              className="px-3 py-1 bg-sky-500 hover:bg-sky-600 text-white font-bold text-[11px] rounded transition-colors cursor-pointer"
            >
              See Old Design
            </button>
          </div>
        </div>

        {/* Post Validation Drawing Section matching Image 10 */}
        <div className="border-t border-slate-100 pt-3 space-y-2">
          <h3 className="text-xs font-bold text-slate-800">Post Validation Drawing</h3>

          <div className="bg-slate-50 rounded-xl p-3 border border-slate-200 space-y-2 text-xs">
            <div className="flex items-start justify-between">
              <div>
                <span className="text-[10px] text-slate-400 font-semibold block uppercase">File Name</span>
                <span className="font-bold text-slate-900">{postDrawing.fileName}</span>
              </div>

              <div className="text-right">
                <span className="px-2 py-0.5 bg-rose-500 text-white font-bold text-[10px] rounded uppercase shadow-sm">
                  {postDrawing.status}
                </span>
                {postDrawing.rejectionReason && (
                  <p className="text-[10px] text-slate-500 mt-0.5 font-mono">{postDrawing.rejectionReason}</p>
                )}
              </div>
            </div>

            <div className="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-slate-200">
              <div>
                <span className="text-slate-400 block text-[10px]">File</span>
                <a href="#" className="text-sky-600 font-medium underline truncate block">
                  {postDrawing.fileUrl}
                </a>
              </div>
              <div>
                <span className="text-slate-400 block text-[10px]">Design Type</span>
                <span className="font-semibold text-slate-800">{postDrawing.designType}</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[10px]">URL</span>
                <span className="font-mono text-slate-600 truncate block">{postDrawing.url}</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[10px]">Uploaded By</span>
                <span className="font-medium text-slate-800">{postDrawing.uploadedBy}</span>
              </div>
            </div>

            <div className="flex items-center justify-between pt-2 border-t border-slate-200">
              <button
                onClick={() => alert(`Rejection remark file: ${postDrawing.rejectionReason}`)}
                className="px-2 py-1 bg-sky-500 hover:bg-sky-600 text-white text-[10px] font-bold rounded cursor-pointer"
              >
                View Remark File
              </button>

              <button
                onClick={() => alert('Drawing validated and accepted!')}
                className="p-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded transition-colors cursor-pointer"
                title="Approve Drawing"
              >
                <Check className="w-3.5 h-3.5 stroke-[3]" />
              </button>
            </div>
          </div>
        </div>

        {/* Validation File Uploaders matching Image 10 bottom */}
        <div className="border-t border-slate-100 pt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div>
            <label className="font-bold text-slate-700 block mb-1">Validation Checklist File</label>
            <input
              type="file"
              className="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300"
            />
          </div>

          <div>
            <label className="font-bold text-slate-700 block mb-1">Manual Correction File</label>
            <input
              type="file"
              className="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300"
            />
          </div>
        </div>

        <div className="pt-2">
          <button
            onClick={() => alert('Files uploaded successfully!')}
            className="px-4 py-1.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs rounded transition-colors cursor-pointer"
          >
            Upload
          </button>
        </div>
      </div>
    </div>
  );
};
