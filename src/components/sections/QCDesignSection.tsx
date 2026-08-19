import React, { useState, useMemo, useEffect } from 'react';
import { QCDesignItem, ClientProject } from '../../types';
import { User, Image as ImageIcon, X, Download, ExternalLink, Users, Globe, Filter, AlertCircle, FileText, RotateCcw, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, ChevronDown } from 'lucide-react';

interface QCDesignSectionProps {
  items: QCDesignItem[];
  client?: ClientProject;
  showAllClients?: boolean;
}

export const QCDesignSection: React.FC<QCDesignSectionProps> = ({
  items,
  client,
  showAllClients = true,
}) => {
  // Data Mode: 'all' or 'clientWise'
  const [dataMode, setDataMode] = useState<'all' | 'clientWise'>(
    showAllClients ? 'all' : 'clientWise'
  );

  const [selectedClientFilterId, setSelectedClientFilterId] = useState<string>(
    client?.id || ''
  );

  useEffect(() => {
    if (client) {
      setSelectedClientFilterId(client.id);
    }
    setDataMode(showAllClients ? 'all' : 'clientWise');
  }, [client, showAllClients]);

  const [designStyleFilter, setDesignStyleFilter] = useState('All');
  const [isFilterExpanded, setIsFilterExpanded] = useState(false);
  const [isFinalFilter, setIsFinalFilter] = useState('All');
  const [statusFilter, setStatusFilter] = useState('All');

  const [activeDesignStyle, setActiveDesignStyle] = useState('All');
  const [activeIsFinal, setActiveIsFinal] = useState('All');
  const [activeStatus, setActiveStatus] = useState('All');

  // Preview Modal State
  const [previewFile, setPreviewFile] = useState<{ name: string; url: string; type: 'image' | 'pdf' | 'other' } | null>(null);
  const [pdfTab, setPdfTab] = useState<'embed' | 'gdoc'>('embed');

  // Extract list of unique clients from items for the dropdown
  const uniqueClients = useMemo(() => {
    const map = new Map<string, { id: string; name: string; numId?: number }>();
    if (client) {
      map.set(client.id, { id: client.id, name: client.name, numId: client.clientIdNum });
    }
    items.forEach((item) => {
      const id = item.client_sr_id || item.clientId || String(item.client_id || '');
      const name = item.client_name || item.clientName || 'Unknown Client';
      if (id && !map.has(id)) {
        map.set(id, { id, name, numId: item.client_id });
      }
    });
    return Array.from(map.values());
  }, [items, client]);

  const handleFilter = () => {
    setActiveDesignStyle(designStyleFilter);
    setActiveIsFinal(isFinalFilter);
    setActiveStatus(statusFilter);
  };

  const handleReset = () => {
    setDesignStyleFilter('All');
    setIsFinalFilter('All');
    setStatusFilter('All');
    setActiveDesignStyle('All');
    setActiveIsFinal('All');
    setActiveStatus('All');
  };

  const filteredItems = useMemo(() => {
    let list = items;

    // Filter based on Data Mode
    if (dataMode === 'clientWise') {
      const activeClientObj = uniqueClients.find((c) => c.id === selectedClientFilterId) || client;
      
      list = list.filter((i) => {
        const itemSrId = i.client_sr_id || i.clientId;
        const itemNumId = i.client_id;
        const itemName = i.client_name || i.clientName;

        if (activeClientObj?.numId && itemNumId) {
          if (itemNumId === activeClientObj.numId) return true;
        }
        if (activeClientObj?.id && itemSrId) {
          if (itemSrId.toLowerCase() === activeClientObj.id.toLowerCase()) return true;
        }
        if (activeClientObj?.name && itemName) {
          if (itemName.toLowerCase().includes(activeClientObj.name.toLowerCase())) return true;
        }
        return false;
      });
    }

    if (activeDesignStyle !== 'All') {
      list = list.filter((i) => (i.design_style || i.designStyle || i.designType) === activeDesignStyle);
    }
    if (activeIsFinal !== 'All') {
      list = list.filter((i) => {
        const isFinalVal = i.is_final !== undefined ? String(i.is_final) : i.isFinal;
        if (activeIsFinal === '1' || activeIsFinal === 'Yes') {
          return isFinalVal === '1' || isFinalVal === 'Yes' || isFinalVal === 'true';
        } else {
          return isFinalVal === '0' || isFinalVal === 'No' || isFinalVal === 'false';
        }
      });
    }
    if (activeStatus !== 'All') {
      list = list.filter((i) => i.status === activeStatus);
    }

    return list;
  }, [items, dataMode, selectedClientFilterId, uniqueClients, client, activeDesignStyle, activeIsFinal, activeStatus]);

  // Pagination State
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [itemsPerPage, setItemsPerPage] = useState<number>(10);

  // Reset page to 1 when filters or data mode change
  useEffect(() => {
    setCurrentPage(1);
  }, [dataMode, selectedClientFilterId, activeDesignStyle, activeIsFinal, activeStatus, itemsPerPage]);

  const totalItems = filteredItems.length;
  const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
  const safeCurrentPage = Math.min(Math.max(1, currentPage), totalPages);

  const startIndex = (safeCurrentPage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

  const paginatedItems = useMemo(() => {
    return filteredItems.slice(startIndex, endIndex);
  }, [filteredItems, startIndex, endIndex]);

  // Helper to get simple file name from full server path
  const getCleanFileName = (path: string) => {
    if (!path) return 'file';
    const parts = path.split('/');
    return parts[parts.length - 1];
  };

  // Helper to resolve real file URL without fallback dummy images
  const resolveFileUrl = (filePath: string) => {
    if (!filePath) return '';
    if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
      return filePath;
    }
    const cleanName = getCleanFileName(filePath);
    return `https://crm.hcinterior.in/uploads/${cleanName}`;
  };

  // Open file in preview modal with iframe or direct download
  const openFilePreview = (filePath: string) => {
    const cleanName = getCleanFileName(filePath);
    const lower = cleanName.toLowerCase();
    const fileUrl = resolveFileUrl(filePath);

    let type: 'image' | 'pdf' | 'other' = 'other';
    if (lower.endsWith('.jpg') || lower.endsWith('.jpeg') || lower.endsWith('.png') || lower.endsWith('.webp') || lower.endsWith('.gif')) {
      type = 'image';
    } else if (lower.endsWith('.pdf')) {
      type = 'pdf';
    }

    setPdfTab('embed');
    setPreviewFile({ name: cleanName, url: fileUrl, type });
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-3.5 sm:p-5 space-y-4">
      {/* Top Data Scope Bar & Mode Selector */}
      <div className="bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-4 space-y-3">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
          <div className="min-w-0 flex-1">
            <h2 className="text-base font-bold text-slate-900 flex items-center space-x-2">
              <span>QC Design Checklist</span>
            </h2>
            <p className="text-[11px] text-slate-500 leading-snug mt-0.5">
              Select data scope to view combined records or specific client design checklists
            </p>
          </div>

          {/* Segmented Control Switcher */}
          <div className="bg-slate-200/70 p-1 rounded-xl grid grid-cols-2 gap-1 w-full md:w-auto shrink-0">
            <button
              onClick={() => setDataMode('all')}
              className={`px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-1.5 cursor-pointer whitespace-nowrap ${
                dataMode === 'all'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-300/60'
              }`}
            >
              <Globe className="w-3.5 h-3.5 text-sky-400 shrink-0" />
              <span>All Clients</span>
              <span className={`text-[10px] px-1.5 py-0.2 rounded-full font-mono shrink-0 ${dataMode === 'all' ? 'bg-orange-500 text-white' : 'bg-slate-300 text-slate-700'}`}>
                {items.length}
              </span>
            </button>

            <button
              onClick={() => setDataMode('clientWise')}
              className={`px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center space-x-1.5 cursor-pointer whitespace-nowrap ${
                dataMode === 'clientWise'
                  ? 'bg-orange-500 text-white shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-300/60'
              }`}
            >
              <User className="w-3.5 h-3.5 text-white shrink-0" />
              <span>Filter by Client</span>
              <span className={`text-[10px] px-1.5 py-0.2 rounded-full font-mono shrink-0 ${dataMode === 'clientWise' ? 'bg-slate-900 text-white' : 'bg-slate-300 text-slate-700'}`}>
                {uniqueClients.length}
              </span>
            </button>
          </div>
        </div>

        {/* Banner according to selected mode */}
        {dataMode === 'clientWise' ? (
          <div className="bg-orange-50 border border-orange-200 rounded-xl p-3 space-y-2.5 text-xs">
            <div className="flex items-center space-x-2.5">
              <div className="w-8 h-8 rounded-lg bg-orange-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                <User className="w-4 h-4" />
              </div>
              <div className="min-w-0 flex-1">
                <span className="text-[10px] text-orange-700 font-bold uppercase block tracking-wider">Active Client Selection</span>
                <span className="font-bold text-slate-900 text-xs truncate block">
                  {selectedClientFilterId
                    ? uniqueClients.find((c) => c.id === selectedClientFilterId)?.name || 'Selected Client'
                    : 'Select client'}
                  {selectedClientFilterId ? (
                    <span className="text-orange-600 font-mono text-xs font-normal ml-1.5">
                      ({selectedClientFilterId})
                    </span>
                  ) : null}
                </span>
              </div>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center gap-1.5 pt-2 border-t border-orange-200/80">
              <span className="text-slate-700 text-xs font-bold shrink-0">Switch Client:</span>
              <select
                value={selectedClientFilterId}
                onChange={(e) => setSelectedClientFilterId(e.target.value)}
                className="w-full bg-white border border-orange-300 font-semibold text-slate-800 text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-500 shadow-2xs truncate cursor-pointer"
              >
                <option value="">Select client</option>
                {uniqueClients.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name} ({c.id})
                  </option>
                ))}
              </select>
            </div>
          </div>
        ) : (
          <div className="bg-slate-100/80 border border-slate-200/80 rounded-lg p-2.5 px-3 text-[11px] text-slate-600 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <span className="flex items-center space-x-1.5 min-w-0">
              <Globe className="w-3.5 h-3.5 text-sky-600 shrink-0" />
              <span className="truncate">Viewing combined QC Design checklist records across <strong>all CRM clients</strong>.</span>
            </span>
            <span className="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded font-mono font-bold shrink-0 self-start sm:self-auto">
              Global View
            </span>
          </div>
        )}
      </div>

      {/* Top Filters Bar */}
      <div className="bg-slate-50 rounded-xl border border-slate-200 overflow-hidden text-xs">
        <button
          type="button"
          onClick={() => setIsFilterExpanded(!isFilterExpanded)}
          className="w-full flex items-center justify-between p-3 bg-slate-100/80 hover:bg-slate-200/80 font-bold text-slate-700 transition-colors cursor-pointer select-none"
        >
          <div className="flex items-center space-x-2">
            <Filter className="w-4 h-4 text-sky-600" />
            <span>Filter Options</span>
            {(activeDesignStyle !== 'All' || activeIsFinal !== 'All' || activeStatus !== 'All') && (
              <span className="bg-sky-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold">Active</span>
            )}
          </div>
          <div className="flex items-center space-x-1 text-slate-500 text-[11px]">
            <span>{isFilterExpanded ? 'Hide Filters' : 'Show Filters'}</span>
            <ChevronDown className={`w-4 h-4 transition-transform duration-200 ${isFilterExpanded ? 'rotate-180' : ''}`} />
          </div>
        </button>

        {isFilterExpanded && (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end p-3.5 border-t border-slate-200">
            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Design Style</label>
              <select
                value={designStyleFilter}
                onChange={(e) => setDesignStyleFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500 text-xs cursor-pointer shadow-2xs"
              >
                <option value="All">All Design Styles</option>
                <option value="Post Validation Design">Post Validation Design</option>
                <option value="Pre Validation Design">Pre Validation Design</option>
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Is Final</label>
              <select
                value={isFinalFilter}
                onChange={(e) => setIsFinalFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500 text-xs cursor-pointer shadow-2xs"
              >
                <option value="All">All</option>
                <option value="1">Final (1 / Yes)</option>
                <option value="0">Not Final (0 / No)</option>
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Status</label>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500 text-xs cursor-pointer shadow-2xs"
              >
                <option value="All">All Statuses</option>
                <option value="Approved">Approved</option>
                <option value="Pending">Pending</option>
                <option value="Reject">Reject</option>
              </select>
            </div>

            <div className="flex items-center space-x-2">
              <button
                onClick={handleFilter}
                className="flex-1 bg-sky-500 hover:bg-sky-600 text-white font-semibold py-1.5 px-3 rounded-lg transition-colors cursor-pointer text-center shadow-xs text-xs"
              >
                Filter
              </button>
              <button
                onClick={handleReset}
                className="flex-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-semibold py-1.5 px-3 rounded-lg transition-colors cursor-pointer text-center shadow-2xs text-xs"
              >
                Reset
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Card List View (Matching BOQ Card Style) */}
      <div className="space-y-3">
        {filteredItems.length === 0 ? (
          <div className="bg-slate-50/90 rounded-2xl p-8 sm:p-12 text-center border border-slate-200 space-y-3.5 shadow-2xs my-2">
            <div className="w-14 h-14 rounded-full bg-slate-200/70 text-slate-500 flex items-center justify-center mx-auto border border-slate-300/60">
              <AlertCircle className="w-7 h-7 text-slate-500" />
            </div>
            <div className="space-y-1.5">
              <h3 className="text-base font-extrabold text-slate-900">No QC Design Data Found</h3>
              <p className="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                {dataMode === 'clientWise' && (selectedClientFilterId || client?.name)
                  ? `No QC design checklist items or drawings found for client "${uniqueClients.find(c => c.id === selectedClientFilterId)?.name || client?.name || 'Selected Client'}". Only live CRM data is rendered.`
                  : 'No QC design checklist records found across clients. Only live data from CRM is displayed.'}
              </p>
            </div>
            <div className="pt-2 flex items-center justify-center space-x-2">
              <button
                onClick={handleReset}
                className="inline-flex items-center space-x-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors cursor-pointer shadow-xs"
              >
                <RotateCcw className="w-3.5 h-3.5 text-orange-400" />
                <span>Reset Filters</span>
              </button>
            </div>
          </div>
        ) : (
          paginatedItems.map((item) => {
            const clientName = item.client_name || item.clientName;
            const clientSrId = item.client_sr_id || item.clientId;
            const designStyle = item.design_style || item.designStyle || 'Post Validation Design';
            const designType = item.design_type || item.designType || 'Modular Design';
            const uploadedBy = item.uploaded_by_name || item.uploadedBy || 'Team';
            const createdAt = item.created_at || item.date || 'N/A';
            const isFinalVal = item.is_final !== undefined ? String(item.is_final) : item.isFinal;
            const isFinalBadge = isFinalVal === '1' || isFinalVal === 'Yes' || isFinalVal === 'true';

            const fileList = item.upload_file && item.upload_file.length > 0 
              ? item.upload_file 
              : item.fileUrl ? [item.fileUrl] : [];

            return (
              <div
                key={item.design_id || item.id}
                className="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-3 text-xs text-slate-800 hover:shadow-xs transition-shadow"
              >
                {/* Card Header: Client Info & Status Badges */}
                <div className="flex items-start justify-between gap-2 border-b border-slate-200/80 pb-2.5">
                  <div>
                    <span className="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Client Details</span>
                    <span className="font-bold text-sky-600 text-sm block">
                      {clientName} <span className="font-mono text-xs text-slate-500 font-normal">({clientSrId})</span>
                    </span>
                  </div>
                  <div className="flex items-center space-x-2 shrink-0">
                    <span
                      className={`px-2.5 py-0.5 rounded text-white text-[10px] font-bold uppercase ${
                        isFinalBadge ? 'bg-emerald-500' : 'bg-slate-400'
                      }`}
                    >
                      Is Final: {isFinalBadge ? '1 (Yes)' : '0 (No)'}
                    </span>
                    <span
                      className={`px-2.5 py-0.5 rounded text-white text-[10px] font-bold uppercase shadow-2xs ${
                        item.status === 'Approved'
                          ? 'bg-emerald-500'
                          : item.status === 'Reject'
                          ? 'bg-red-500'
                          : 'bg-orange-500'
                      }`}
                    >
                      {item.status}
                    </span>
                  </div>
                </div>

                {/* Key Grid Information */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px]">
                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Design Type</span>
                    <span className="font-semibold text-slate-800">{designType}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Design Style</span>
                    <span className="font-medium text-slate-800">{designStyle}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Uploaded By</span>
                    <span className="font-medium text-slate-700">{uploadedBy}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Created Date</span>
                    <span className="font-medium text-slate-600">{createdAt}</span>
                  </div>
                </div>

                {/* Remark Block */}
                <div className="bg-amber-50/80 border border-amber-200/80 rounded-lg p-2.5 text-amber-900 text-[11px] flex items-start space-x-2">
                  <AlertCircle className="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                  <div>
                    <span className="font-bold block text-[10px] text-amber-700 uppercase">QC Remarks / Points</span>
                    <p className="whitespace-pre-line text-[11px] leading-relaxed">
                      {item.remark && item.remark.trim() ? item.remark : 'No Remarks'}
                    </p>
                  </div>
                </div>

                {/* Uploaded Files List */}
                <div className="pt-2 border-t border-slate-200/80 space-y-1.5">
                  <span className="text-[10px] text-slate-400 font-bold uppercase block">
                    Uploaded Drawing / QC Files ({fileList.length})
                  </span>
                  <div className="flex flex-wrap gap-2 items-center">
                    {fileList.map((file, idx) => {
                      const fileName = getCleanFileName(file);
                      const isPdf = fileName.toLowerCase().endsWith('.pdf');
                      const isImg = fileName.toLowerCase().endsWith('.jpg') || fileName.toLowerCase().endsWith('.png') || fileName.toLowerCase().endsWith('.jpeg');

                      return (
                        <button
                          key={idx}
                          onClick={() => openFilePreview(file)}
                          className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 font-mono text-[11px] transition-colors cursor-pointer shadow-2xs"
                        >
                          {isPdf ? (
                            <FileText className="w-3.5 h-3.5 text-red-500 shrink-0" />
                          ) : isImg ? (
                            <ImageIcon className="w-3.5 h-3.5 text-sky-500 shrink-0" />
                          ) : (
                            <FileText className="w-3.5 h-3.5 text-slate-500 shrink-0" />
                          )}
                          <span className="truncate max-w-[200px]">{fileName}</span>
                          <span className="text-[9px] uppercase px-1 py-0.2 rounded bg-slate-200 text-slate-700 font-bold">
                            {isPdf ? 'PDF' : isImg ? 'IMG' : 'FILE'}
                          </span>
                        </button>
                      );
                    })}

                    {/* External Drive Link Button */}
                    {item.upload_url && item.upload_url !== 'no url' && item.upload_url !== '#' && (
                      <a
                        href={item.upload_url}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs transition-colors shadow-2xs ml-auto"
                      >
                        <ExternalLink className="w-3.5 h-3.5" />
                        <span>Open Drive Link</span>
                      </a>
                    )}
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* Pagination Controls */}
      {filteredItems.length > 0 && (
        <div className="bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
          {/* Info & Items Per Page Selector */}
          <div className="flex flex-wrap items-center justify-between sm:justify-start w-full sm:w-auto gap-3">
            <span className="font-medium text-slate-700">
              Showing <strong className="text-slate-900 font-bold">{totalItems === 0 ? 0 : startIndex + 1}</strong> to{' '}
              <strong className="text-slate-900 font-bold">{endIndex}</strong> of{' '}
              <strong className="text-slate-900 font-bold">{totalItems}</strong> entries
            </span>

            <div className="flex items-center space-x-1.5 pl-2 border-l border-slate-200">
              <span className="text-slate-500 text-[11px]">Per page:</span>
              <select
                value={itemsPerPage}
                onChange={(e) => setItemsPerPage(Number(e.target.value))}
                className="bg-white border border-slate-300 font-bold text-slate-800 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer shadow-2xs"
              >
                <option value={5}>5</option>
                <option value={10}>10</option>
                <option value={20}>20</option>
                <option value={50}>50</option>
                <option value={100}>100</option>
              </select>
            </div>
          </div>

          {/* Page Navigation Buttons */}
          <div className="flex items-center space-x-1">
            <button
              onClick={() => setCurrentPage(1)}
              disabled={safeCurrentPage === 1}
              className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-slate-700 cursor-pointer"
              title="First Page"
            >
              <ChevronsLeft className="w-4 h-4" />
            </button>
            <button
              onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
              disabled={safeCurrentPage === 1}
              className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-slate-700 cursor-pointer flex items-center space-x-1 px-2 font-medium"
              title="Previous Page"
            >
              <ChevronLeft className="w-4 h-4" />
              <span className="hidden md:inline">Prev</span>
            </button>

            {/* Page Number Buttons */}
            <div className="flex items-center space-x-1 px-1">
              {Array.from({ length: totalPages }, (_, idx) => idx + 1)
                .filter((page) => {
                  if (totalPages <= 7) return true;
                  if (page === 1 || page === totalPages) return true;
                  return Math.abs(page - safeCurrentPage) <= 1;
                })
                .map((page, idx, arr) => {
                  const prevPage = arr[idx - 1];
                  const showEllipsis = prevPage && page - prevPage > 1;

                  return (
                    <React.Fragment key={page}>
                      {showEllipsis && (
                        <span className="px-1 text-slate-400 font-bold text-xs select-none">...</span>
                      )}
                      <button
                        onClick={() => setCurrentPage(page)}
                        className={`min-w-[32px] h-8 rounded-lg font-bold text-xs transition-all cursor-pointer ${
                          page === safeCurrentPage
                            ? 'bg-orange-500 text-white shadow-xs'
                            : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'
                        }`}
                      >
                        {page}
                      </button>
                    </React.Fragment>
                  );
                })}
            </div>

            <button
              onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
              disabled={safeCurrentPage === totalPages}
              className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-slate-700 cursor-pointer flex items-center space-x-1 px-2 font-medium"
              title="Next Page"
            >
              <span className="hidden md:inline">Next</span>
              <ChevronRight className="w-4 h-4" />
            </button>
            <button
              onClick={() => setCurrentPage(totalPages)}
              disabled={safeCurrentPage === totalPages}
              className="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-slate-700 cursor-pointer"
              title="Last Page"
            >
              <ChevronsRight className="w-4 h-4" />
            </button>
          </div>
        </div>
      )}

      {/* File Preview Modal */}
      {previewFile && (
        <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-5xl w-full max-h-[92vh] flex flex-col overflow-hidden shadow-2xl border border-zinc-200">
            {/* Modal Header */}
            <div className="p-3.5 sm:p-4 bg-zinc-950 text-white flex items-center justify-between gap-3 border-b border-zinc-800">
              <div className="flex items-center space-x-2.5 min-w-0">
                {previewFile.type === 'pdf' ? (
                  <div className="w-8 h-8 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center shrink-0 border border-red-500/30">
                    <FileText className="w-4 h-4" />
                  </div>
                ) : (
                  <div className="w-8 h-8 rounded-lg bg-amber-400/20 text-amber-400 flex items-center justify-center shrink-0 border border-amber-400/30">
                    <ImageIcon className="w-4 h-4" />
                  </div>
                )}
                <div className="min-w-0">
                  <span className="font-bold text-sm truncate block text-zinc-100">{previewFile.name}</span>
                  <span className="text-[10px] text-zinc-400 font-medium block truncate">
                    {previewFile.type === 'pdf' ? 'PDF Drawing Document' : 'Image File Preview'}
                  </span>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex items-center space-x-2 shrink-0">
                <a
                  href={previewFile.url}
                  download={previewFile.name}
                  target="_blank"
                  rel="noreferrer"
                  className="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs transition-colors shadow-xs"
                >
                  <Download className="w-3.5 h-3.5" />
                  <span>Download File</span>
                </a>
                <a
                  href={previewFile.url}
                  target="_blank"
                  rel="noreferrer"
                  className="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold rounded-lg text-xs transition-colors border border-zinc-700"
                >
                  <ExternalLink className="w-3.5 h-3.5" />
                  <span>Open in New Tab</span>
                </a>
                <button
                  onClick={() => setPreviewFile(null)}
                  className="p-1.5 hover:bg-zinc-800 rounded-lg text-zinc-400 hover:text-white transition-colors cursor-pointer"
                  title="Close Modal"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            {/* Toolbar for PDF Viewer */}
            {previewFile.type === 'pdf' && (
              <div className="bg-slate-100 border-b border-slate-200 px-3 py-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div className="flex items-center space-x-1 bg-slate-200/80 p-0.5 rounded-lg border border-slate-300">
                  <button
                    onClick={() => setPdfTab('embed')}
                    className={`px-3 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer ${
                      pdfTab === 'embed'
                        ? 'bg-white text-slate-900 shadow-2xs'
                        : 'text-slate-600 hover:text-slate-900'
                    }`}
                  >
                    Direct PDF Iframe
                  </button>
                  <button
                    onClick={() => setPdfTab('gdoc')}
                    className={`px-3 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer ${
                      pdfTab === 'gdoc'
                        ? 'bg-white text-slate-900 shadow-2xs'
                        : 'text-slate-600 hover:text-slate-900'
                    }`}
                  >
                    Google Docs Viewer
                  </button>
                </div>

                <div className="flex items-center space-x-2">
                  <a
                    href={previewFile.url}
                    download={previewFile.name}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center space-x-1 px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-md text-[11px] transition-colors"
                  >
                    <Download className="w-3 h-3" />
                    <span>Download PDF</span>
                  </a>
                </div>
              </div>
            )}

            {/* Modal Content - Display PDF or Image */}
            <div className="p-3 sm:p-4 overflow-y-auto flex-1 bg-slate-100/90 min-h-[420px] flex items-center justify-center">
              {previewFile.type === 'pdf' ? (
                <div className="w-full h-full flex flex-col space-y-2">
                  <div className="bg-amber-50 border border-amber-200 rounded-lg p-2.5 text-xs text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div className="flex items-center space-x-2 min-w-0">
                      <AlertCircle className="w-4 h-4 text-amber-600 shrink-0" />
                      <span className="truncate text-[11px]">
                        File Source: <strong className="font-mono text-amber-950">{previewFile.url}</strong>
                      </span>
                    </div>
                    <div className="flex items-center space-x-2 shrink-0">
                      <a
                        href={previewFile.url}
                        download={previewFile.name}
                        className="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white font-bold rounded text-[11px] transition-colors"
                      >
                        Download
                      </a>
                      <a
                        href={previewFile.url}
                        target="_blank"
                        rel="noreferrer"
                        className="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded text-[11px] transition-colors"
                      >
                        New Tab
                      </a>
                    </div>
                  </div>

                  {pdfTab === 'embed' ? (
                    <iframe
                      src={previewFile.url}
                      className="w-full h-[58vh] rounded-xl border border-slate-300 bg-white shadow-md"
                      title={previewFile.name}
                    />
                  ) : (
                    <iframe
                      src={`https://docs.google.com/gview?url=${encodeURIComponent(previewFile.url)}&embedded=true`}
                      className="w-full h-[58vh] rounded-xl border border-slate-300 bg-white shadow-md"
                      title={previewFile.name}
                    />
                  )}
                </div>
              ) : previewFile.type === 'image' ? (
                <div className="w-full flex justify-center p-2">
                  <img
                    src={previewFile.url}
                    alt={previewFile.name}
                    className="max-h-[68vh] w-auto rounded-xl shadow-lg object-contain border border-zinc-300"
                  />
                </div>
              ) : (
                <div className="text-center space-y-3 py-8">
                  <FileText className="w-12 h-12 text-slate-400 mx-auto" />
                  <p className="text-xs text-slate-600 font-medium">{previewFile.name}</p>
                  <a
                    href={previewFile.url}
                    download={previewFile.name}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition-colors shadow-xs"
                  >
                    <Download className="w-4 h-4" />
                    <span>Download File</span>
                  </a>
                </div>
              )}
            </div>

            {/* Modal Footer */}
            <div className="p-3 bg-zinc-50 border-t border-zinc-200 flex items-center justify-between text-xs">
              <span className="text-zinc-500 text-[11px] truncate max-w-[60%]">
                {previewFile.url}
              </span>
              <div className="flex items-center space-x-2">
                <a
                  href={previewFile.url}
                  download={previewFile.name}
                  target="_blank"
                  rel="noreferrer"
                  className="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer"
                >
                  Download
                </a>
                <button
                  onClick={() => setPreviewFile(null)}
                  className="px-4 py-1.5 bg-zinc-200 hover:bg-zinc-300 text-zinc-700 font-bold text-xs rounded-lg transition-colors cursor-pointer"
                >
                  Close Preview
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

