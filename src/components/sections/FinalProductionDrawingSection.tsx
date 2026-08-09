import React, { useState, useEffect } from 'react';
import { Plus, Trash2, User } from 'lucide-react';
import { ClientProject } from '../../types';

interface ModularDrawingItem {
  id: string;
  isFinalDesign: boolean;
  fileName: string;
  fileThumbnail: string;
  designType: string;
  url: string;
  status: 'Approved' | 'Pending' | 'Rejected';
  uploadedBy: string;
  date: string;
  clientId?: string;
}

interface FinalProductionDrawingSectionProps {
  client?: ClientProject;
  showAllClients?: boolean;
}

export const FinalProductionDrawingSection: React.FC<FinalProductionDrawingSectionProps> = ({
  client,
  showAllClients = false,
}) => {
  const [drawings, setDrawings] = useState<ModularDrawingItem[]>([
    {
      id: 'md-1',
      isFinalDesign: true,
      fileName: 'Test Lead Digital_Drawing_file',
      fileThumbnail:
        'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=160&q=80',
      designType: 'Modular Design',
      url: 'fsdafgsd',
      status: 'Approved',
      uploadedBy: 'Nishant Singh',
      date: '2026-06-24 3:33 PM',
      clientId: 'HC101791',
    },
  ]);

  // Adjust drawings list if client is specified
  useEffect(() => {
    if (client && !showAllClients) {
      setDrawings([
        {
          id: `md-${client.id}`,
          isFinalDesign: true,
          fileName: `${client.name}_Final_Production_Drawing`,
          fileThumbnail:
            'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=160&q=80',
          designType: 'Modular Design',
          url: `https://drive.google.com/file/${client.id}_drawings`,
          status: 'Approved',
          uploadedBy: 'Nishant Singh',
          date: '2026-07-25 3:33 PM',
          clientId: client.id,
        },
      ]);
    }
  }, [client, showAllClients]);

  const [isAdding, setIsAdding] = useState(false);
  const [newFileName, setNewFileName] = useState('');
  const [newUrl, setNewUrl] = useState('');

  const handleAddDrawing = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newFileName.trim()) return;

    const newItem: ModularDrawingItem = {
      id: `md-${Date.now()}`,
      isFinalDesign: false,
      fileName: newFileName.trim(),
      fileThumbnail:
        'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=160&q=80',
      designType: 'Modular Design',
      url: newUrl.trim() || 'https://drive.google.com/file',
      status: 'Approved',
      uploadedBy: 'Nishant Singh',
      date: new Date().toLocaleString('sv-SE').replace(' ', ' ').slice(0, 16),
      };

    setDrawings((prev) => [newItem, ...prev]);
    setNewFileName('');
    setNewUrl('');
    setIsAdding(false);
  };

  const handleToggleFinalDesign = (id: string) => {
    setDrawings((prev) =>
      prev.map((item) =>
        item.id === id ? { ...item, isFinalDesign: !item.isFinalDesign } : item
      )
    );
  };

  const handleDelete = (id: string) => {
    setDrawings((prev) => prev.filter((item) => item.id !== id));
  };

  return (
    <div className="space-y-4">
      {/* Client Scope Banner */}
      {client && !showAllClients && (
        <div className="bg-slate-900 text-white p-3 rounded-xl shadow-2xs flex items-center justify-between border border-slate-800">
          <div className="flex items-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs border border-orange-500/30">
              <User className="w-4 h-4" />
            </div>
            <div>
              <h4 className="text-xs font-bold text-slate-100">{client.name}</h4>
              <p className="text-[10px] text-slate-400">
                Client ID: <span className="text-orange-400 font-mono font-bold">{client.id}</span>
              </p>
            </div>
          </div>
          <span className="px-2.5 py-0.5 bg-orange-500/20 border border-orange-500/40 text-orange-300 text-[10px] font-bold rounded-full uppercase tracking-wider">
            Client Data Only
          </span>
        </div>
      )}

      <div className="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
        {/* Header with full orange bottom line */}
        <div className="px-4 py-2.5 border-b-2 border-orange-500 flex items-center justify-between">
          <h3 className="text-sm font-bold text-slate-800">
            Final Production Drawing {client && !showAllClients ? `— ${client.id}` : ''}
          </h3>
          <button
            onClick={() => setIsAdding(!isAdding)}
            className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded border border-slate-300 transition-colors cursor-pointer inline-flex items-center space-x-1"
          >
            <Plus className="w-3.5 h-3.5" />
            <span>Add Modular Drawing</span>
          </button>
        </div>

        {/* Add Form drawer */}
        {isAdding && (
          <form
            onSubmit={handleAddDrawing}
            className="p-4 bg-slate-50 border-b border-slate-200 space-y-3 text-xs"
          >
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-slate-600 font-medium mb-1">File Name</label>
                <input
                  type="text"
                  placeholder="e.g. Test Lead Digital_Drawing_file"
                  value={newFileName}
                  onChange={(e) => setNewFileName(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded px-2.5 py-1.5 focus:outline-none focus:border-sky-500"
                  required
                />
              </div>
              <div>
                <label className="block text-slate-600 font-medium mb-1">URL / Link</label>
                <input
                  type="text"
                  placeholder="e.g. fsdafgsd"
                  value={newUrl}
                  onChange={(e) => setNewUrl(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded px-2.5 py-1.5 focus:outline-none focus:border-sky-500"
                />
              </div>
            </div>
            <div className="flex justify-end space-x-2 pt-1">
              <button
                type="button"
                onClick={() => setIsAdding(false)}
                className="px-3 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-slate-600 rounded"
              >
                Cancel
              </button>
              <button
                type="submit"
                className="px-3 py-1 bg-[#5cb85c] hover:bg-[#4cae4c] text-white font-bold rounded shadow-2xs"
              >
                Save Modular Drawing
              </button>
            </div>
          </form>
        )}

        {/* Table matching user screenshot */}
        <div className="overflow-x-auto scrollbar-thin">
          <table className="w-full text-left border-collapse text-xs min-w-[750px]">
            <thead>
              <tr className="bg-slate-50/70 text-slate-700 font-bold border-b border-slate-200 text-xs">
                <th className="py-2.5 px-4 font-bold">File Name</th>
                <th className="py-2.5 px-4 font-bold">File</th>
                <th className="py-2.5 px-4 font-bold">Design Type</th>
                <th className="py-2.5 px-4 font-bold">URL</th>
                <th className="py-2.5 px-4 font-bold">Status</th>
                <th className="py-2.5 px-4 font-bold">Uploaded By</th>
                <th className="py-2.5 px-4 font-bold">Date</th>
                <th className="py-2.5 px-4 font-bold">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-slate-800">
              {drawings.map((item) => (
                <tr key={item.id} className="hover:bg-slate-50/50 transition-colors">
                  {/* File Name Column with Final Design Green Badge */}
                  <td className="py-3 px-4 font-medium text-slate-800">
                    <div className="flex items-center space-x-2">
                      {item.isFinalDesign && (
                        <span className="bg-[#28a745] text-white text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider shrink-0">
                          Final Design
                        </span>
                      )}
                      <span className="text-slate-800">{item.fileName}</span>
                    </div>
                  </td>

                  {/* File Thumbnail */}
                  <td className="py-3 px-4">
                    <img
                      src={item.fileThumbnail}
                      alt={item.fileName}
                      className="w-16 h-10 object-cover rounded border border-slate-200 shadow-2xs"
                    />
                  </td>

                  {/* Design Type */}
                  <td className="py-3 px-4 text-slate-700 font-medium">{item.designType}</td>

                  {/* URL */}
                  <td className="py-3 px-4 font-mono text-slate-600">{item.url}</td>

                  {/* Status */}
                  <td className="py-3 px-4">
                    <span className="bg-[#28a745] text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase inline-block">
                      {item.status}
                    </span>
                  </td>

                  {/* Uploaded By */}
                  <td className="py-3 px-4 text-slate-700 font-medium">{item.uploadedBy}</td>

                  {/* Date */}
                  <td className="py-3 px-4 font-mono text-slate-600">{item.date}</td>

                  {/* Action */}
                  <td className="py-3 px-4">
                    <div className="flex items-center space-x-2">
                      <button
                        onClick={() => handleToggleFinalDesign(item.id)}
                        className={`text-white text-xs font-bold px-3 py-1 rounded shadow-2xs transition-colors cursor-pointer ${
                          item.isFinalDesign
                            ? 'bg-[#5cb85c] hover:bg-[#4cae4c]'
                            : 'bg-slate-400 hover:bg-slate-500'
                        }`}
                      >
                        Final Design
                      </button>
                      <button
                        onClick={() => handleDelete(item.id)}
                        className="text-slate-400 hover:text-red-600 transition-colors p-1"
                        title="Delete"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}

              {drawings.length === 0 && (
                <tr>
                  <td colSpan={8} className="py-6 text-center text-slate-400 text-xs font-medium">
                    No modular drawings uploaded yet.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
