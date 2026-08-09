import React, { useState } from 'react';
import { PurchaseRequestItem } from '../../types';
import { ShoppingCart, Plus, Edit2, Check, Trash2, X } from 'lucide-react';

interface PurchaseSectionProps {
  purchases: PurchaseRequestItem[];
  sitePurchases: PurchaseRequestItem[];
  onAddPurchase: (isOnSite: boolean) => void;
  mode?: 'purchaseRequest' | 'onSitePurchase';
}

export const PurchaseSection: React.FC<PurchaseSectionProps> = ({
  purchases: initialPurchases,
  sitePurchases: initialSitePurchases,
  onAddPurchase,
  mode,
}) => {
  const [siteItems, setSiteItems] = useState<PurchaseRequestItem[]>(initialSitePurchases);
  const [purchaseItems, setPurchaseItems] = useState<PurchaseRequestItem[]>(initialPurchases);

  // Form toggle & state matching user screenshot
  const [isAddOpen, setIsAddOpen] = useState(false);
  const [isAddingSite, setIsAddingSite] = useState(false);
  const [fileName, setFileName] = useState('');
  const [uploadUrl, setUploadUrl] = useState('');
  const [dragActive, setDragActive] = useState(false);

  const handleOpenAdd = (isOnSite: boolean) => {
    setIsAddingSite(isOnSite);
    setFileName('');
    setUploadUrl('');
    setIsAddOpen(!isAddOpen);
  };

  const handleSavePurchase = (e: React.FormEvent) => {
    e.preventDefault();
    if (!fileName.trim()) return;

    const newItem: PurchaseRequestItem = {
      id: `pr-${Date.now()}`,
      fileName: fileName.trim(),
      bomName: 'Site Material',
      vendorName: 'Supplier',
      fileUrl: uploadUrl.trim() || '#',
      date: new Date().toLocaleString('sv-SE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
      }).replace(' ', ' '),
      status: 'Received',
      isOnSite: isAddingSite,
    };

    if (isAddingSite) {
      setSiteItems([newItem, ...siteItems]);
    } else {
      setPurchaseItems([newItem, ...purchaseItems]);
    }

    setFileName('');
    setUploadUrl('');
    setIsAddOpen(false);
  };

  const toggleStatus = (id: string, isOnSite: boolean) => {
    if (isOnSite) {
      setSiteItems((prev) =>
        prev.map((item) =>
          item.id === id
            ? { ...item, status: item.status === 'Received' ? 'Pending' : 'Received' }
            : item
        )
      );
    } else {
      setPurchaseItems((prev) =>
        prev.map((item) =>
          item.id === id
            ? { ...item, status: item.status === 'Received' ? 'Pending' : 'Received' }
            : item
        )
      );
    }
  };

  const handleDelete = (id: string, isOnSite: boolean) => {
    if (isOnSite) {
      setSiteItems((prev) => prev.filter((item) => item.id !== id));
    } else {
      setPurchaseItems((prev) => prev.filter((item) => item.id !== id));
    }
  };

  const renderAddForm = () => (
    <form onSubmit={handleSavePurchase} className="p-6 bg-white border-b border-slate-200 space-y-4">
      <div className="max-w-3xl mx-auto space-y-4 text-xs">
        {/* File Name * */}
        <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label className="w-28 text-slate-700 font-medium sm:text-right shrink-0">
            File Name <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={fileName}
            onChange={(e) => setFileName(e.target.value)}
            className="flex-1 bg-white border border-slate-300 rounded px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-sky-500"
            required
          />
        </div>

        {/* Upload URL * */}
        <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
          <label className="w-28 text-slate-700 font-medium sm:text-right shrink-0">
            Upload URL <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={uploadUrl}
            onChange={(e) => setUploadUrl(e.target.value)}
            className="flex-1 bg-white border border-slate-300 rounded px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-sky-500"
          />
        </div>

        {/* Attachment Drag and Drop */}
        <div className="flex flex-col sm:flex-row items-start gap-2 sm:gap-4">
          <label className="w-28 text-slate-700 font-medium sm:text-right pt-2 shrink-0">
            Attachment
          </label>
          <div
            onDragOver={(e) => {
              e.preventDefault();
              setDragActive(true);
            }}
            onDragLeave={() => setDragActive(false)}
            onDrop={(e) => {
              e.preventDefault();
              setDragActive(false);
            }}
            className={`flex-1 w-full border-2 border-dashed rounded-lg p-6 text-center transition-colors ${
              dragActive ? 'border-sky-500 bg-sky-50' : 'border-slate-300 bg-slate-50/50'
            }`}
          >
            <p className="text-slate-600 text-sm font-semibold">
              <strong className="font-serif text-slate-800 text-base">Drag files</strong> to upload
            </p>
            <p className="text-slate-500 text-xs mt-1 italic font-serif">(or click)</p>
          </div>
        </div>

        {/* Save Button */}
        <div className="flex justify-end pt-2">
          <button
            type="submit"
            className="px-6 py-1.5 bg-[#5b9bd5] hover:bg-[#4a89c4] text-white font-bold text-xs rounded transition-colors shadow-2xs cursor-pointer"
          >
            Save
          </button>
        </div>
      </div>
    </form>
  );

  const renderOnSiteTable = () => (
    <div className="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
      {/* Header matching screenshot */}
      <div className="px-4 py-2.5 border-b-2 border-orange-500 flex items-center justify-between">
        <h3 className="text-sm font-bold text-slate-800">On Site Purchase Request</h3>
        <button
          onClick={() => handleOpenAdd(true)}
          className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded border border-slate-300 transition-colors cursor-pointer"
        >
          Add Purchase
        </button>
      </div>

      {isAddOpen && isAddingSite && renderAddForm()}

      {/* Table matching user screenshot */}
      <div className="overflow-x-auto scrollbar-thin">
        <table className="w-full text-left border-collapse text-xs min-w-[650px]">
          <thead>
            <tr className="bg-white text-slate-700 font-bold border-b border-slate-200 text-xs">
              <th className="py-2.5 px-4 font-bold">File Name</th>
              <th className="py-2.5 px-4 font-bold">File</th>
              <th className="py-2.5 px-4 font-bold">URL</th>
              <th className="py-2.5 px-4 font-bold">Date</th>
              <th className="py-2.5 px-4 font-bold">Status</th>
              <th className="py-2.5 px-4 font-bold">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-slate-800">
            {siteItems.length > 0 ? (
              siteItems.map((item) => (
                <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="py-3 px-4 font-medium text-slate-800">{item.fileName}</td>
                  <td className="py-3 px-4 text-slate-400">
                    <div className="w-10 h-8 rounded border border-slate-200 bg-slate-50 flex items-center justify-center text-slate-400">
                      <ShoppingCart className="w-4 h-4 text-slate-400" />
                    </div>
                  </td>
                  <td className="py-3 px-4 font-mono text-sky-600 truncate max-w-[150px]">
                    {item.fileUrl}
                  </td>
                  <td className="py-3 px-4 font-mono text-slate-600">{item.date}</td>
                  <td className="py-3 px-4">
                    <div className="flex items-center space-x-1">
                      <span className="text-slate-600 font-medium text-xs">Status :</span>
                      <span className="bg-[#28a745] text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase inline-block">
                        {item.status === 'Received' ? 'Recieved' : item.status}
                      </span>
                    </div>
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center space-x-1.5">
                      <button
                        onClick={() => {
                          const name = prompt('Edit File Name:', item.fileName);
                          if (name) {
                            setSiteItems((prev) =>
                              prev.map((i) => (i.id === item.id ? { ...i, fileName: name } : i))
                            );
                          }
                        }}
                        className="p-1.5 bg-[#428bca] hover:bg-[#3071a9] text-white rounded cursor-pointer transition-colors shadow-2xs"
                        title="Edit"
                      >
                        <Edit2 className="w-3.5 h-3.5" />
                      </button>
                      <button
                        onClick={() => toggleStatus(item.id, true)}
                        className="p-1.5 bg-[#5cb85c] hover:bg-[#4cae4c] text-white rounded cursor-pointer transition-colors shadow-2xs"
                        title="Mark Status"
                      >
                        <Check className="w-3.5 h-3.5 stroke-[3]" />
                      </button>
                      <button
                        onClick={() => handleDelete(item.id, true)}
                        className="p-1.5 text-slate-400 hover:text-red-600 transition-colors cursor-pointer"
                        title="Delete"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={6} className="py-6 text-center text-slate-400 text-xs font-medium">
                  No site purchase requests found.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderPurchaseTable = () => (
    <div className="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
      {/* Header */}
      <div className="px-4 py-2.5 border-b-2 border-orange-500 flex items-center justify-between">
        <h3 className="text-sm font-bold text-slate-800">Purchase Request</h3>
        <button
          onClick={() => handleOpenAdd(false)}
          className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded border border-slate-300 transition-colors cursor-pointer"
        >
          Add Purchase
        </button>
      </div>

      {isAddOpen && !isAddingSite && renderAddForm()}

      {/* Table */}
      <div className="overflow-x-auto scrollbar-thin">
        <table className="w-full text-left border-collapse text-xs min-w-[650px]">
          <thead>
            <tr className="bg-white text-slate-700 font-bold border-b border-slate-200 text-xs">
              <th className="py-2.5 px-4 font-bold">File Name</th>
              <th className="py-2.5 px-4 font-bold">File</th>
              <th className="py-2.5 px-4 font-bold">URL</th>
              <th className="py-2.5 px-4 font-bold">Date</th>
              <th className="py-2.5 px-4 font-bold">Status</th>
              <th className="py-2.5 px-4 font-bold">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-slate-800">
            {purchaseItems.length > 0 ? (
              purchaseItems.map((item) => (
                <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="py-3 px-4 font-medium text-slate-800">{item.fileName}</td>
                  <td className="py-3 px-4 text-slate-400">
                    <div className="w-10 h-8 rounded border border-slate-200 bg-slate-50 flex items-center justify-center text-slate-400">
                      <ShoppingCart className="w-4 h-4 text-slate-400" />
                    </div>
                  </td>
                  <td className="py-3 px-4 font-mono text-sky-600 truncate max-w-[150px]">
                    {item.fileUrl}
                  </td>
                  <td className="py-3 px-4 font-mono text-slate-600">{item.date}</td>
                  <td className="py-3 px-4">
                    <span
                      className={`text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase inline-block ${
                        item.status === 'Order Placed' || item.status === 'Received'
                          ? 'bg-[#28a745]'
                          : 'bg-orange-500'
                      }`}
                    >
                      {item.status}
                    </span>
                  </td>
                  <td className="py-3 px-4">
                    <div className="flex items-center space-x-1.5">
                      <button
                        onClick={() => {
                          const name = prompt('Edit File Name:', item.fileName);
                          if (name) {
                            setPurchaseItems((prev) =>
                              prev.map((i) => (i.id === item.id ? { ...i, fileName: name } : i))
                            );
                          }
                        }}
                        className="p-1.5 bg-[#428bca] hover:bg-[#3071a9] text-white rounded cursor-pointer transition-colors shadow-2xs"
                        title="Edit"
                      >
                        <Edit2 className="w-3.5 h-3.5" />
                      </button>
                      <button
                        onClick={() => toggleStatus(item.id, false)}
                        className="p-1.5 bg-[#5cb85c] hover:bg-[#4cae4c] text-white rounded cursor-pointer transition-colors shadow-2xs"
                        title="Mark Status"
                      >
                        <Check className="w-3.5 h-3.5 stroke-[3]" />
                      </button>
                      <button
                        onClick={() => handleDelete(item.id, false)}
                        className="p-1.5 text-slate-400 hover:text-red-600 transition-colors cursor-pointer"
                        title="Delete"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={6} className="py-6 text-center text-slate-400 text-xs font-medium">
                  No purchase requests found.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );

  return (
    <div className="space-y-6">
      {mode === 'onSitePurchase' && renderOnSiteTable()}
      {mode === 'purchaseRequest' && renderPurchaseTable()}
      {!mode && (
        <>
          {renderPurchaseTable()}
          {renderOnSiteTable()}
        </>
      )}
    </div>
  );
};
