import React, { useState } from 'react';
import { BOQItem } from '../../types';
import { X, Plus, FileText } from 'lucide-react';

interface AddQuotationModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAdd: (quotation: BOQItem) => void;
  clientId: string;
}

export const AddQuotationModal: React.FC<AddQuotationModalProps> = ({
  isOpen,
  onClose,
  onAdd,
  clientId,
}) => {
  const [phone, setPhone] = useState('3030303030');
  const [gTotal, setGTotal] = useState('188125.80');
  const [siteHandling, setSiteHandling] = useState('0.00');
  const [status, setStatus] = useState<'Draft' | 'Approved'>('Draft');

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const parsedGTotal = parseFloat(gTotal) || 0;
    const parsedHandling = parseFloat(siteHandling) || 0;
    const newQuote: BOQItem = {
      quotationNo: `HCIPPL/Quote/26-27/N/${Math.floor(10000000 + Math.random() * 90000000)}/1`,
      date: new Date().toISOString().split('T')[0],
      phone,
      gTotal: parsedGTotal,
      siteHandling: parsedHandling,
      toBePaid: parsedGTotal + parsedHandling,
      status,
    };
    onAdd(newQuote);
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
        <div className="bg-slate-900 text-white px-4 py-3 flex items-center justify-between font-bold text-sm">
          <div className="flex items-center space-x-2">
            <FileText className="w-4 h-4 text-orange-400" />
            <span>New Quotation BOQ</span>
          </div>
          <button onClick={onClose} className="text-slate-400 hover:text-white p-0.5 cursor-pointer">
            <X className="w-4 h-4" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-4 space-y-3 text-xs text-slate-700">
          <div>
            <label className="font-semibold text-slate-600 block mb-1">Client ID</label>
            <input
              type="text"
              value={clientId}
              disabled
              className="w-full bg-slate-100 border border-slate-200 rounded p-2 font-mono text-slate-500 text-xs"
            />
          </div>

          <div>
            <label className="font-semibold text-slate-600 block mb-1">Phone Number *</label>
            <input
              type="text"
              required
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-orange-500"
            />
          </div>

          <div className="grid grid-cols-2 gap-2">
            <div>
              <label className="font-semibold text-slate-600 block mb-1">G Total (₹) *</label>
              <input
                type="number"
                required
                value={gTotal}
                onChange={(e) => setGTotal(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-orange-500 font-bold"
              />
            </div>
            <div>
              <label className="font-semibold text-slate-600 block mb-1">Site Handling (₹)</label>
              <input
                type="number"
                value={siteHandling}
                onChange={(e) => setSiteHandling(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-orange-500"
              />
            </div>
          </div>

          <div>
            <label className="font-semibold text-slate-600 block mb-1">Quotation Status</label>
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value as any)}
              className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-orange-500 font-medium"
            >
              <option value="Draft">Draft</option>
              <option value="Approved">Approved</option>
            </select>
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
              className="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded text-xs cursor-pointer shadow-sm"
            >
              Save Quotation
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
