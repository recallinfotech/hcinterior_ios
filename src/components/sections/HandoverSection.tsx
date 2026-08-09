import React, { useState } from 'react';
import { HandoverItem, ClientProject } from '../../types';
import {
  FileCheck,
  Plus,
  Search,
  Upload,
  FileText,
  Eye,
  Download,
  Trash2,
  Calendar,
  User,
  CheckCircle2,
  Clock,
  AlertCircle,
  X,
  File,
  Sparkles,
  ExternalLink,
  ShieldCheck,
  FolderDown,
  Filter
} from 'lucide-react';

interface HandoverSectionProps {
  client?: ClientProject | null;
  showAllClients?: boolean;
  showToast?: (msg: string) => void;
  initialHandovers?: HandoverItem[];
}

export const HandoverSection: React.FC<HandoverSectionProps> = ({
  client,
  showAllClients = false,
  showToast,
  initialHandovers = [],
}) => {
  // Handover records state
  const [handovers, setHandovers] = useState<HandoverItem[]>(() => {
    if (initialHandovers.length > 0) return initialHandovers;
    // Default fallback mock list
    return [
      {
        id: 'ho-101',
        clientId: client?.id || 'HC101806',
        clientName: client?.name || 'Shubhra Chauhan',
        title: 'Site Handover & Key Acceptance Certificate',
        handoverType: 'Final Site Handover',
        fileName: `${client?.id || 'HC101806'}_Final_Handover_Certificate.pdf`,
        fileUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
        fileSize: '2.4 MB',
        fileType: 'PDF Document',
        handoverDate: '2026-08-01',
        handoverBy: 'Abhishek Bhati (Project Manager)',
        handoverTo: client?.name || 'Shubhra Chauhan',
        status: 'Completed',
        remarks: 'Keys handed over along with site defect sign-off sheet and appliance manuals.',
        createdAt: '2026-08-01 11:30 AM',
      },
      {
        id: 'ho-102',
        clientId: client?.id || 'HC101806',
        clientName: client?.name || 'Shubhra Chauhan',
        title: 'Warranty Cards & Appliance Manuals Handover',
        handoverType: 'Warranty & Manuals',
        fileName: `${client?.id || 'HC101806'}_Warranty_Manuals_Bundle.pdf`,
        fileUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
        fileSize: '4.8 MB',
        fileType: 'PDF Document',
        handoverDate: '2026-08-02',
        handoverBy: 'Nishant Singh',
        handoverTo: client?.name || 'Shubhra Chauhan',
        status: 'Approved',
        remarks: 'Modular fittings & hardware warranty cards verified and signed by client.',
        createdAt: '2026-08-02 02:15 PM',
      },
      {
        id: 'ho-103',
        clientId: '520',
        clientName: 'Test Client',
        title: 'Interim Civil & Electrical Handover Sheet',
        handoverType: 'Interim Handover',
        fileName: 'TestClient_Civil_Handover_Draft.pdf',
        fileUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
        fileSize: '1.2 MB',
        fileType: 'PDF Document',
        handoverDate: '2026-08-05',
        handoverBy: 'Site Supervisor',
        handoverTo: 'Test Client',
        status: 'Pending Sign-off',
        remarks: 'Awaiting client final signature on electrical point check.',
        createdAt: '2026-08-05 04:00 PM',
      },
    ];
  });

  // UI States
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [selectedStatusTab, setSelectedStatusTab] = useState<string>('All');
  const [isUploadModalOpen, setIsUploadModalOpen] = useState<boolean>(false);
  const [selectedPreviewItem, setSelectedPreviewItem] = useState<HandoverItem | null>(null);

  // Form State
  const [title, setTitle] = useState<string>('');
  const [handoverType, setHandoverType] = useState<string>('Final Site Handover');
  const [handoverBy, setHandoverBy] = useState<string>('Project Manager');
  const [handoverTo, setHandoverTo] = useState<string>(client?.name || '');
  const [handoverDate, setHandoverDate] = useState<string>(new Date().toISOString().split('T')[0]);
  const [status, setStatus] = useState<HandoverItem['status']>('Completed');
  const [remarks, setRemarks] = useState<string>('');
  const [uploadedFile, setUploadedFile] = useState<File | null>(null);
  const [dragActive, setDragActive] = useState<boolean>(false);

  // Filter Handovers by active client or show all
  const clientFilteredHandovers = handovers.filter((item) => {
    if (showAllClients || !client) return true;
    return item.clientId === client.id || item.clientName?.toLowerCase().includes(client.name.toLowerCase());
  });

  // Search & Tab Filtered Handovers
  const displayedHandovers = clientFilteredHandovers.filter((item) => {
    const matchesSearch =
      item.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
      item.fileName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      item.handoverType.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (item.handoverBy && item.handoverBy.toLowerCase().includes(searchTerm.toLowerCase())) ||
      (item.handoverTo && item.handoverTo.toLowerCase().includes(searchTerm.toLowerCase()));

    const matchesStatus =
      selectedStatusTab === 'All' || item.status.toLowerCase() === selectedStatusTab.toLowerCase();

    return matchesSearch && matchesStatus;
  });

  // Handle File Drag & Select
  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setUploadedFile(e.target.files[0]);
    }
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(true);
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      setUploadedFile(e.dataTransfer.files[0]);
    }
  };

  // Submit Upload Form
  const handleSaveHandover = (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim()) {
      showToast?.('Please enter a handover document title');
      return;
    }

    const fileNameToUse = uploadedFile
      ? uploadedFile.name
      : `${client?.id || 'CLIENT'}_${title.replace(/\s+/g, '_')}_Handover.pdf`;

    const fileSizeToUse = uploadedFile
      ? `${(uploadedFile.size / (1024 * 1024)).toFixed(1)} MB`
      : '2.1 MB';

    const fileTypeToUse = uploadedFile ? uploadedFile.type || 'PDF Document' : 'PDF Document';

    // File URL object if actual file, else dummy pdf
    const fileUrlToUse = uploadedFile
      ? URL.createObjectURL(uploadedFile)
      : 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

    const newHandover: HandoverItem = {
      id: `ho-${Date.now()}`,
      clientId: client?.id || 'HC101806',
      clientName: client?.name || 'Shubhra Chauhan',
      title: title.trim(),
      handoverType,
      fileName: fileNameToUse,
      fileUrl: fileUrlToUse,
      fileSize: fileSizeToUse,
      fileType: fileTypeToUse,
      handoverDate,
      handoverBy: handoverBy.trim() || 'Project Manager',
      handoverTo: handoverTo.trim() || client?.name || 'Client',
      status,
      remarks: remarks.trim(),
      createdAt: new Date().toLocaleString(),
    };

    setHandovers([newHandover, ...handovers]);
    setIsUploadModalOpen(false);

    // Reset Form
    setTitle('');
    setRemarks('');
    setUploadedFile(null);

    showToast?.(`Handover document "${newHandover.title}" uploaded successfully!`);
  };

  // Delete Handover
  const handleDeleteHandover = (id: string) => {
    setHandovers((prev) => prev.filter((item) => item.id !== id));
    if (selectedPreviewItem?.id === id) {
      setSelectedPreviewItem(null);
    }
    showToast?.('Handover document removed');
  };

  // Status Badge Helper
  const getStatusBadge = (statusVal: HandoverItem['status']) => {
    switch (statusVal) {
      case 'Completed':
      case 'Approved':
        return (
          <span className="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <CheckCircle2 className="w-3 h-3 text-emerald-600" />
            <span>{statusVal}</span>
          </span>
        );
      case 'Pending Sign-off':
        return (
          <span className="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
            <Clock className="w-3 h-3 text-amber-600" />
            <span>{statusVal}</span>
          </span>
        );
      case 'Draft':
      default:
        return (
          <span className="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-700 border border-zinc-200">
            <AlertCircle className="w-3 h-3 text-zinc-500" />
            <span>{statusVal}</span>
          </span>
        );
    }
  };

  return (
    <div className="space-y-4">
      {/* SECTION HEADER CARD */}
      <div className="bg-gradient-to-r from-zinc-900 via-zinc-900 to-amber-950 p-4 rounded-2xl border border-zinc-800 text-white shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div className="space-y-1">
          <div className="flex items-center space-x-2">
            <div className="p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400">
              <FileCheck className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-base font-bold text-white tracking-tight flex items-center space-x-2">
                <span>Client Handover Documents</span>
                {client && (
                  <span className="text-xs px-2 py-0.5 bg-zinc-800 text-amber-400 border border-zinc-700 rounded-md font-mono">
                    {client.name} ({client.id})
                  </span>
                )}
              </h2>
              <p className="text-xs text-zinc-400">
                Upload and view site handover certificates, key acceptance sheets, and warranty documents.
              </p>
            </div>
          </div>
        </div>

        <button
          onClick={() => setIsUploadModalOpen(true)}
          className="inline-flex items-center justify-center space-x-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-zinc-950 text-xs font-bold rounded-xl transition-all shadow-sm active:scale-95 cursor-pointer shrink-0"
        >
          <Plus className="w-4 h-4" />
          <span>Upload Handover Document</span>
        </button>
      </div>

      {/* STATS OVERVIEW */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <div className="bg-white p-3 rounded-xl border border-zinc-200 shadow-2xs">
          <span className="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Total Handovers</span>
          <span className="text-lg font-black text-zinc-900">{clientFilteredHandovers.length}</span>
        </div>
        <div className="bg-emerald-50/60 p-3 rounded-xl border border-emerald-100 shadow-2xs">
          <span className="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Completed / Signed</span>
          <span className="text-lg font-black text-emerald-900">
            {clientFilteredHandovers.filter((h) => h.status === 'Completed' || h.status === 'Approved').length}
          </span>
        </div>
        <div className="bg-amber-50/60 p-3 rounded-xl border border-amber-100 shadow-2xs">
          <span className="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Pending Sign-off</span>
          <span className="text-lg font-black text-amber-900">
            {clientFilteredHandovers.filter((h) => h.status === 'Pending Sign-off').length}
          </span>
        </div>
        <div className="bg-zinc-50 p-3 rounded-xl border border-zinc-200 shadow-2xs">
          <span className="text-[10px] font-bold text-zinc-600 uppercase tracking-wider block">Draft Documents</span>
          <span className="text-lg font-black text-zinc-800">
            {clientFilteredHandovers.filter((h) => h.status === 'Draft').length}
          </span>
        </div>
      </div>

      {/* SEARCH AND FILTER BAR */}
      <div className="bg-white p-3 rounded-xl border border-zinc-200 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-3">
        {/* Search */}
        <div className="relative w-full sm:w-72">
          <Search className="w-4 h-4 text-zinc-400 absolute left-3 top-2.5" />
          <input
            type="text"
            placeholder="Search handover file or title..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-3 py-1.5 bg-zinc-50 border border-zinc-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900 placeholder:text-zinc-400"
          />
          {searchTerm && (
            <button
              onClick={() => setSearchTerm('')}
              className="absolute right-2.5 top-2 text-zinc-400 hover:text-zinc-600"
            >
              <X className="w-3.5 h-3.5" />
            </button>
          )}
        </div>

        {/* Status Tabs */}
        <div className="flex items-center space-x-1 bg-zinc-100 p-1 rounded-lg w-full sm:w-auto overflow-x-auto">
          {['All', 'Completed', 'Approved', 'Pending Sign-off', 'Draft'].map((tab) => (
            <button
              key={tab}
              onClick={() => setSelectedStatusTab(tab)}
              className={`px-2.5 py-1 rounded-md text-[11px] font-bold transition-all whitespace-nowrap cursor-pointer ${
                selectedStatusTab === tab
                  ? 'bg-white text-zinc-900 shadow-2xs'
                  : 'text-zinc-500 hover:text-zinc-900'
              }`}
            >
              {tab}
            </button>
          ))}
        </div>
      </div>

      {/* HANDOVER DOCUMENTS LIST */}
      {displayedHandovers.length === 0 ? (
        <div className="bg-white rounded-2xl border border-zinc-200 p-8 text-center space-y-3">
          <div className="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto border border-amber-200">
            <FileCheck className="w-6 h-6" />
          </div>
          <div className="space-y-1">
            <h3 className="text-sm font-bold text-zinc-900">No Handover Documents Found</h3>
            <p className="text-xs text-zinc-500 max-w-sm mx-auto">
              No handover files match your search criteria. Click "Upload Handover Document" above to add client handover sheets.
            </p>
          </div>
          <button
            onClick={() => setIsUploadModalOpen(true)}
            className="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-zinc-950 text-xs font-bold rounded-xl transition-all cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Upload Handover File</span>
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
          {displayedHandovers.map((item) => (
            <div
              key={item.id}
              className="bg-white rounded-2xl border border-zinc-200 p-4 shadow-2xs hover:shadow-md hover:border-amber-400/80 transition-all space-y-3 flex flex-col justify-between"
            >
              {/* Card Header */}
              <div className="space-y-2">
                <div className="flex items-start justify-between gap-2">
                  <div className="flex items-start space-x-3">
                    <div className="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-600 shrink-0">
                      <FileText className="w-5 h-5" />
                    </div>
                    <div>
                      <h3 className="text-xs font-bold text-zinc-900 line-clamp-1">{item.title}</h3>
                      <div className="flex items-center space-x-2 mt-0.5">
                        <span className="text-[10px] font-mono text-zinc-500">{item.handoverType}</span>
                        <span className="text-zinc-300">•</span>
                        <span className="text-[10px] font-mono text-amber-600 font-bold">{item.clientName}</span>
                      </div>
                    </div>
                  </div>
                  {getStatusBadge(item.status)}
                </div>

                {/* File Detail Box */}
                <div className="bg-zinc-50 rounded-xl border border-zinc-200/80 p-2.5 flex items-center justify-between text-xs">
                  <div className="flex items-center space-x-2 truncate">
                    <File className="w-4 h-4 text-zinc-400 shrink-0" />
                    <span className="font-mono text-[11px] font-semibold text-zinc-800 truncate">
                      {item.fileName}
                    </span>
                  </div>
                  <span className="text-[10px] text-zinc-500 font-mono shrink-0 ml-2">
                    {item.fileSize || '2.4 MB'}
                  </span>
                </div>

                {/* Handover Meta */}
                <div className="grid grid-cols-2 gap-2 text-[11px] text-zinc-600 pt-1 border-t border-zinc-100">
                  <div className="flex items-center space-x-1.5">
                    <Calendar className="w-3.5 h-3.5 text-zinc-400 shrink-0" />
                    <span>Date: <strong className="text-zinc-800">{item.handoverDate}</strong></span>
                  </div>
                  <div className="flex items-center space-x-1.5 truncate">
                    <User className="w-3.5 h-3.5 text-zinc-400 shrink-0" />
                    <span className="truncate">By: <strong className="text-zinc-800">{item.handoverBy}</strong></span>
                  </div>
                </div>

                {item.remarks && (
                  <p className="text-[11px] text-zinc-500 italic bg-amber-50/40 p-2 rounded-lg border border-amber-100/60 line-clamp-2">
                    "{item.remarks}"
                  </p>
                )}
              </div>

              {/* Card Actions */}
              <div className="pt-2 border-t border-zinc-100 flex items-center justify-between">
                <span className="text-[10px] text-zinc-400 font-mono">
                  Added {item.createdAt}
                </span>

                <div className="flex items-center space-x-1.5">
                  {/* View / Preview Button */}
                  <button
                    onClick={() => setSelectedPreviewItem(item)}
                    className="inline-flex items-center space-x-1 px-2.5 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg text-[11px] font-bold transition-all shadow-2xs cursor-pointer"
                    title="View uploaded handover file"
                  >
                    <Eye className="w-3.5 h-3.5 text-amber-400" />
                    <span>View File</span>
                  </button>

                  {/* Download Direct Link */}
                  <a
                    href={item.fileUrl}
                    target="_blank"
                    rel="noreferrer"
                    download={item.fileName}
                    className="p-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-lg transition-colors cursor-pointer"
                    title="Download document"
                  >
                    <Download className="w-3.5 h-3.5" />
                  </a>

                  {/* Delete Button */}
                  <button
                    onClick={() => handleDeleteHandover(item.id)}
                    className="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition-colors cursor-pointer"
                    title="Delete handover record"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* UPLOAD HANDOVER DOCUMENT MODAL */}
      {isUploadModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
          <div className="bg-white rounded-2xl border border-zinc-200 shadow-xl max-w-lg w-full p-5 space-y-4 my-8 animate-in fade-in zoom-in-95 duration-150">
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <div className="flex items-center space-x-2">
                <div className="p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-600">
                  <Upload className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-sm font-bold text-zinc-900">Upload Handover File</h3>
                  <p className="text-[11px] text-zinc-500">
                    Client: <strong className="text-zinc-800">{client ? `${client.name} (${client.id})` : 'Select Client'}</strong>
                  </p>
                </div>
              </div>
              <button
                onClick={() => setIsUploadModalOpen(false)}
                className="p-1 text-zinc-400 hover:text-zinc-600 rounded-lg hover:bg-zinc-100 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Modal Form */}
            <form onSubmit={handleSaveHandover} className="space-y-3">
              {/* Document Title */}
              <div>
                <label className="block text-xs font-bold text-zinc-700 mb-1">
                  Document Title <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Key Acceptance & Site Defect Clearance"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900"
                />
              </div>

              {/* Handover Type & Status */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-zinc-700 mb-1">Handover Type</label>
                  <select
                    value={handoverType}
                    onChange={(e) => setHandoverType(e.target.value)}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900"
                  >
                    <option value="Final Site Handover">Final Site Handover</option>
                    <option value="Key Handover">Key Handover</option>
                    <option value="Snag List Signoff">Snag List Signoff</option>
                    <option value="Interim Handover">Interim Handover</option>
                    <option value="Warranty & Manuals">Warranty & Manuals</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-bold text-zinc-700 mb-1">Document Status</label>
                  <select
                    value={status}
                    onChange={(e) => setStatus(e.target.value as HandoverItem['status'])}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900"
                  >
                    <option value="Completed">Completed</option>
                    <option value="Approved">Approved</option>
                    <option value="Pending Sign-off">Pending Sign-off</option>
                    <option value="Draft">Draft</option>
                  </select>
                </div>
              </div>

              {/* Handover Date & Handover By */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-zinc-700 mb-1">Handover Date</label>
                  <input
                    type="date"
                    value={handoverDate}
                    onChange={(e) => setHandoverDate(e.target.value)}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-zinc-700 mb-1">Handover By</label>
                  <input
                    type="text"
                    placeholder="e.g. Abhishek Bhati (PM)"
                    value={handoverBy}
                    onChange={(e) => setHandoverBy(e.target.value)}
                    className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900"
                  />
                </div>
              </div>

              {/* Handover To */}
              <div>
                <label className="block text-xs font-bold text-zinc-700 mb-1">Handover To (Client Signatory)</label>
                <input
                  type="text"
                  placeholder="e.g. Shubhra Chauhan"
                  value={handoverTo}
                  onChange={(e) => setHandoverTo(e.target.value)}
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900"
                />
              </div>

              {/* File Upload Box */}
              <div>
                <label className="block text-xs font-bold text-zinc-700 mb-1">
                  Upload File (PDF / DOC / Image)
                </label>
                <div
                  onDragOver={handleDragOver}
                  onDragLeave={handleDragLeave}
                  onDrop={handleDrop}
                  className={`border-2 border-dashed rounded-2xl p-4 text-center transition-all cursor-pointer ${
                    dragActive
                      ? 'border-amber-500 bg-amber-50'
                      : uploadedFile
                      ? 'border-emerald-400 bg-emerald-50/50'
                      : 'border-zinc-200 bg-zinc-50 hover:bg-zinc-100/80 hover:border-amber-300'
                  }`}
                >
                  <input
                    type="file"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    onChange={handleFileChange}
                    className="hidden"
                    id="handover-file-input"
                  />
                  <label htmlFor="handover-file-input" className="cursor-pointer block space-y-1.5">
                    {uploadedFile ? (
                      <div className="space-y-1">
                        <CheckCircle2 className="w-8 h-8 text-emerald-500 mx-auto" />
                        <p className="text-xs font-bold text-emerald-900 truncate">{uploadedFile.name}</p>
                        <p className="text-[10px] text-emerald-700 font-mono">
                          {(uploadedFile.size / (1024 * 1024)).toFixed(2)} MB • {uploadedFile.type || 'Document'}
                        </p>
                        <span className="text-[10px] text-amber-600 font-bold underline">Click to change file</span>
                      </div>
                    ) : (
                      <div className="space-y-1">
                        <Upload className="w-7 h-7 text-amber-500 mx-auto" />
                        <p className="text-xs font-bold text-zinc-800">
                          Drag and drop file here, or <span className="text-amber-600 underline">Browse</span>
                        </p>
                        <p className="text-[10px] text-zinc-400">Supports PDF, DOCX, JPG, PNG (Max 25MB)</p>
                      </div>
                    )}
                  </label>
                </div>
              </div>

              {/* Remarks */}
              <div>
                <label className="block text-xs font-bold text-zinc-700 mb-1">Remarks / Key Handover Notes</label>
                <textarea
                  rows={2}
                  placeholder="Additional observations, key counts, defect list cleared..."
                  value={remarks}
                  onChange={(e) => setRemarks(e.target.value)}
                  className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 text-zinc-900 resize-none"
                />
              </div>

              {/* Actions */}
              <div className="pt-2 flex items-center justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setIsUploadModalOpen(false)}
                  className="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold rounded-xl transition-all cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-zinc-950 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
                >
                  Save & Upload Handover
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* PREVIEW / VIEW HANDOVER DOCUMENT MODAL */}
      {selectedPreviewItem && (
        <div className="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5 overflow-y-auto">
          <div className="bg-white rounded-2xl border border-zinc-200 shadow-2xl max-w-2xl w-full p-5 space-y-4 my-6 animate-in fade-in zoom-in-95 duration-150">
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-zinc-100 pb-3">
              <div className="flex items-center space-x-2">
                <div className="p-2 bg-amber-50 border border-amber-200 rounded-xl text-amber-600">
                  <FileText className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-sm font-bold text-zinc-900">{selectedPreviewItem.title}</h3>
                  <p className="text-[11px] text-zinc-500">
                    Handover Document • <strong className="text-amber-600">{selectedPreviewItem.clientName}</strong>
                  </p>
                </div>
              </div>
              <button
                onClick={() => setSelectedPreviewItem(null)}
                className="p-1 text-zinc-400 hover:text-zinc-600 rounded-lg hover:bg-zinc-100 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Document Details Grid */}
            <div className="bg-zinc-50 rounded-xl p-3 border border-zinc-200/80 grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
              <div>
                <span className="text-[10px] text-zinc-400 font-medium block">Document Type</span>
                <span className="font-bold text-zinc-800">{selectedPreviewItem.handoverType}</span>
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 font-medium block">Status</span>
                {getStatusBadge(selectedPreviewItem.status)}
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 font-medium block">Handover Date</span>
                <span className="font-bold text-zinc-800">{selectedPreviewItem.handoverDate}</span>
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 font-medium block">Issued By</span>
                <span className="font-semibold text-zinc-800">{selectedPreviewItem.handoverBy}</span>
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 font-medium block">Handover To</span>
                <span className="font-semibold text-zinc-800">{selectedPreviewItem.handoverTo || 'Client'}</span>
              </div>
              <div>
                <span className="text-[10px] text-zinc-400 font-medium block">File Size</span>
                <span className="font-mono text-zinc-700">{selectedPreviewItem.fileSize || '2.4 MB'}</span>
              </div>
            </div>

            {selectedPreviewItem.remarks && (
              <div className="bg-amber-50/60 rounded-xl p-3 border border-amber-100 text-xs">
                <span className="text-[10px] font-bold text-amber-800 block uppercase tracking-wider mb-0.5">Remarks / Notes</span>
                <p className="text-zinc-700 font-medium">{selectedPreviewItem.remarks}</p>
              </div>
            )}

            {/* Document File Viewer Box */}
            <div className="bg-zinc-900 rounded-2xl p-4 text-white space-y-3">
              <div className="flex items-center justify-between border-b border-zinc-800 pb-2">
                <div className="flex items-center space-x-2 truncate">
                  <File className="w-4 h-4 text-amber-400 shrink-0" />
                  <span className="text-xs font-mono font-bold text-zinc-200 truncate">
                    {selectedPreviewItem.fileName}
                  </span>
                </div>
                <span className="text-[10px] px-2 py-0.5 rounded bg-zinc-800 text-amber-400 font-mono">PDF Preview</span>
              </div>

              {/* Viewer iframe / preview fallback */}
              <div className="bg-zinc-950 rounded-xl border border-zinc-800 p-6 text-center space-y-3 min-h-[180px] flex flex-col items-center justify-center">
                <FileCheck className="w-12 h-12 text-emerald-400 mx-auto opacity-90 animate-pulse" />
                <div className="space-y-1">
                  <h4 className="text-xs font-bold text-zinc-200">{selectedPreviewItem.title}</h4>
                  <p className="text-[11px] text-zinc-400 font-mono">{selectedPreviewItem.fileName}</p>
                </div>

                <div className="pt-2 flex items-center justify-center space-x-2">
                  <a
                    href={selectedPreviewItem.fileUrl}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-zinc-950 text-xs font-bold rounded-lg transition-all shadow-xs cursor-pointer"
                  >
                    <ExternalLink className="w-3.5 h-3.5" />
                    <span>Open Full Document</span>
                  </a>

                  <a
                    href={selectedPreviewItem.fileUrl}
                    target="_blank"
                    rel="noreferrer"
                    download={selectedPreviewItem.fileName}
                    className="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-bold rounded-lg transition-all cursor-pointer"
                  >
                    <Download className="w-3.5 h-3.5 text-zinc-300" />
                    <span>Download</span>
                  </a>
                </div>
              </div>
            </div>

            {/* Modal Footer */}
            <div className="pt-2 flex items-center justify-between border-t border-zinc-100">
              <span className="text-[10px] text-zinc-400 font-mono">
                Uploaded: {selectedPreviewItem.createdAt}
              </span>
              <button
                onClick={() => setSelectedPreviewItem(null)}
                className="px-4 py-2 bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-bold rounded-xl transition-all cursor-pointer"
              >
                Close Preview
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
