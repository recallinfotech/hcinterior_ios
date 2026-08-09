import React, { useState } from 'react';
import { BOMRecord } from '../../types';
import { X, Layers } from 'lucide-react';

interface AddBOMModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAdd: (bom: BOMRecord, parentId?: string) => void;
  parentId?: string;
}

export const AddBOMModal: React.FC<AddBOMModalProps> = ({
  isOpen,
  onClose,
  onAdd,
  parentId,
}) => {
  const [fileName, setFileName] = useState(parentId ? 'Additional BOM 2' : 'BOM3 - Custom Cabinetry');
  const [category, setCategory] = useState('Category 1');
  const [type, setType] = useState(parentId ? 'BOM 1' : 'BOM 3');

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newBOM: BOMRecord = {
      id: `bom-${Date.now()}`,
      fileName,
      category,
      type,
      fileUrl: '#',
      imageUrl: 'https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?auto=format&fit=crop&w=300&q=80',
      date: new Date().toISOString().split('T')[0] + ' 12:00 PM',
      status: parentId ? 'Pending' : 'Accepted',
      parentId,
    };
    onAdd(newBOM, parentId);
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
        <div className="bg-sky-600 text-white px-4 py-3 flex items-center justify-between font-bold text-sm">
          <div className="flex items-center space-x-2">
            <Layers className="w-4 h-4" />
            <span>{parentId ? 'Add Child BOM Item' : 'Add BOM / Check List'}</span>
          </div>
          <button onClick={onClose} className="text-white hover:text-slate-200 p-0.5 cursor-pointer">
            <X className="w-4 h-4" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-4 space-y-3 text-xs text-slate-700">
          <div>
            <label className="font-semibold text-slate-600 block mb-1">File Name *</label>
            <input
              type="text"
              required
              value={fileName}
              onChange={(e) => setFileName(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-sky-500 font-bold"
            />
          </div>

          <div>
            <label className="font-semibold text-slate-600 block mb-1">Category</label>
            <input
              type="text"
              value={category}
              onChange={(e) => setCategory(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-sky-500"
            />
          </div>

          <div>
            <label className="font-semibold text-slate-600 block mb-1">Type</label>
            <input
              type="text"
              value={type}
              onChange={(e) => setType(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-sky-500"
            />
          </div>

          <div className="flex items-center justify-end space-x-2 pt-2">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-xs cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="px-4 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded text-xs cursor-pointer shadow-sm"
            >
              Save BOM
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
