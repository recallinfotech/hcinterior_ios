import React, { useState } from 'react';
import { PaymentRecord } from '../../types';
import { X, IndianRupee } from 'lucide-react';

interface AddPaymentModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAdd: (payment: PaymentRecord) => void;
}

export const AddPaymentModal: React.FC<AddPaymentModalProps> = ({ isOpen, onClose, onAdd }) => {
  const [amount, setAmount] = useState<string>('50000');
  const [paymentMode, setPaymentMode] = useState<'Cash' | 'UPI' | 'Bank Transfer' | 'Cheque'>('Cash');
  const [transactionDetail, setTransactionDetail] = useState<string>('UPI Ref 49810238');

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newPayment: PaymentRecord = {
      id: `pay-${Date.now()}`,
      amount: parseFloat(amount) || 0,
      paymentMode,
      paymentDate: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),
      transactionDetail,
      status: 'Paid',
      date: new Date().toISOString().split('T')[0],
    };
    onAdd(newPayment);
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
        <div className="bg-emerald-600 text-white px-4 py-3 flex items-center justify-between font-bold text-sm">
          <div className="flex items-center space-x-2">
            <IndianRupee className="w-4 h-4" />
            <span>Add Payment Record</span>
          </div>
          <button onClick={onClose} className="text-white hover:text-slate-200 p-0.5 cursor-pointer">
            <X className="w-4 h-4" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-4 space-y-3 text-xs text-slate-700">
          <div>
            <label className="font-semibold text-slate-600 block mb-1">Amount (₹) *</label>
            <input
              type="number"
              required
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded p-2 font-bold text-slate-900 text-sm focus:outline-none focus:border-emerald-500"
            />
          </div>

          <div>
            <label className="font-semibold text-slate-600 block mb-1">Payment Mode</label>
            <select
              value={paymentMode}
              onChange={(e) => setPaymentMode(e.target.value as any)}
              className="w-full bg-white border border-slate-300 rounded p-2 text-xs focus:outline-none focus:border-emerald-500 font-medium"
            >
              <option value="Cash">Cash</option>
              <option value="UPI">UPI / Online</option>
              <option value="Bank Transfer">Bank Transfer (NEFT/RTGS)</option>
              <option value="Cheque">Cheque</option>
            </select>
          </div>

          <div>
            <label className="font-semibold text-slate-600 block mb-1">Transaction Detail / Ref No.</label>
            <input
              type="text"
              value={transactionDetail}
              onChange={(e) => setTransactionDetail(e.target.value)}
              className="w-full bg-white border border-slate-300 rounded p-2 text-xs font-mono focus:outline-none focus:border-emerald-500"
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
              className="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs cursor-pointer shadow-sm"
            >
              Record Payment
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
