import React, { useState } from 'react';
import { PaymentRecord } from '../../types';
import { IndianRupee, Download, Plus, Edit2, CheckCircle, FileText } from 'lucide-react';

interface PaymentRecordsSectionProps {
  payments: PaymentRecord[];
  onAddPayment: () => void;
}

export const PaymentRecordsSection: React.FC<PaymentRecordsSectionProps> = ({
  payments,
  onAddPayment,
}) => {
  const totalAmount = 174929.25;
  const totalPaid = payments.reduce((acc, p) => acc + p.amount, 0);
  const balanceAmount = totalAmount - totalPaid;
  const receivedPct = ((totalPaid / totalAmount) * 100).toFixed(2);

  return (
    <div className="space-y-4">
      {/* 4 Financial Metric Cards matching Image 13 EXACTLY */}
      <div className="grid grid-cols-2 gap-2.5">
        {/* Total Amount Card */}
        <div className="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm text-center">
          <div className="bg-sky-500 text-white text-[11px] font-bold py-1.5 uppercase tracking-wider">
            Total Amount
          </div>
          <div className="p-3">
            <span className="text-sm sm:text-base font-extrabold text-slate-900">
              ₹{totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}
            </span>
          </div>
        </div>

        {/* Total Paid Card */}
        <div className="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm text-center">
          <div className="bg-emerald-500 text-white text-[11px] font-bold py-1.5 uppercase tracking-wider">
            Total Paid
          </div>
          <div className="p-3">
            <span className="text-sm sm:text-base font-extrabold text-emerald-600">
              ₹{totalPaid.toLocaleString('en-IN', { minimumFractionDigits: 2 })}
            </span>
          </div>
        </div>

        {/* Balance Amount Card */}
        <div className="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm text-center">
          <div className="bg-rose-500 text-white text-[11px] font-bold py-1.5 uppercase tracking-wider">
            Balance Amount
          </div>
          <div className="p-3">
            <span className="text-sm sm:text-base font-extrabold text-rose-600">
              ₹{balanceAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}
            </span>
          </div>
        </div>

        {/* Received % Card */}
        <div className="bg-white rounded-xl overflow-hidden border border-slate-200 shadow-sm text-center">
          <div className="bg-indigo-500 text-white text-[11px] font-bold py-1.5 uppercase tracking-wider">
            Received %
          </div>
          <div className="p-3">
            <span className="text-sm sm:text-base font-extrabold text-indigo-600">{receivedPct}%</span>
          </div>
        </div>
      </div>

      {/* Download Payment Schedule Red Button matching Image 13 */}
      <div>
        <button
          onClick={() => alert('Downloading Payment Schedule PDF...')}
          className="px-3.5 py-2 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs rounded-xl flex items-center space-x-1.5 shadow-sm transition-colors cursor-pointer"
        >
          <FileText className="w-4 h-4" />
          <span>Download Payment Schedule</span>
        </button>
      </div>

      {/* Payment Record Table Card matching Image 13 */}
      <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3">
        <div className="flex items-center justify-between border-b-2 border-orange-500 pb-2">
          <h2 className="text-base font-bold text-slate-900">Payment Record</h2>
          <button
            onClick={onAddPayment}
            className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded border border-slate-300 transition-colors cursor-pointer"
          >
            Add Payment
          </button>
        </div>

        <div className="space-y-2.5">
          {payments.map((p) => (
            <div key={p.id} className="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs space-y-2">
              <div className="flex items-start justify-between">
                <div>
                  <span className="text-[10px] text-slate-400 font-semibold block uppercase">Amount</span>
                  <span className="font-extrabold text-slate-900 text-sm">
                    ₹{p.amount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                  </span>
                </div>

                <span className="px-2 py-0.5 bg-emerald-500 text-white font-bold text-[10px] rounded">
                  {p.status}
                </span>
              </div>

              <div className="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-slate-200">
                <div>
                  <span className="text-slate-400 block text-[10px]">Payment Mode</span>
                  <span className="font-semibold text-slate-800">{p.paymentMode}</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[10px]">Payment Date</span>
                  <span className="font-medium text-slate-700">{p.paymentDate}</span>
                </div>
                <div className="col-span-2">
                  <span className="text-slate-400 block text-[10px]">Transaction Detail</span>
                  <span className="font-mono text-slate-700">{p.transactionDetail}</span>
                </div>
              </div>

              <div className="flex items-center justify-between pt-2 border-t border-slate-200">
                <span className="text-[10px] text-slate-400">Recorded Date: {p.date}</span>

                <button
                  onClick={() => alert(`Edit payment of ₹${p.amount}`)}
                  className="p-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded cursor-pointer"
                  title="Edit Payment"
                >
                  <Edit2 className="w-3 h-3" />
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
