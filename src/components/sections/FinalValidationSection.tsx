import React, { useState, useMemo, useEffect } from 'react';
import { FinalValidationItem, ClientProject } from '../../types';
import {
  User,
  FileText,
  Globe,
  RotateCcw,
  Search,
  ExternalLink,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  CheckCircle2,
  Folder,
  ChevronDown,
  Filter,
} from 'lucide-react';

interface FinalValidationSectionProps {
  items: FinalValidationItem[];
  clients?: ClientProject[];
  client?: ClientProject;
  showAllClients?: boolean;
  authToken?: string;
  showToast?: (msg: string) => void;
  onRefresh?: () => Promise<void>;
}

export const FinalValidationSection: React.FC<FinalValidationSectionProps> = ({
  items,
  clients = [],
  client,
  showAllClients = true,
  authToken,
  showToast,
  onRefresh,
}) => {
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

  const [searchQuery, setSearchQuery] = useState('');
  const [isFilterExpanded, setIsFilterExpanded] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);

  const [designStyleFilter, setDesignStyleFilter] = useState('All');
  const [isFinalFilter, setIsFinalFilter] = useState('All');
  const [statusFilter, setStatusFilter] = useState('All');

  const [activeDesignStyle, setActiveDesignStyle] = useState('All');
  const [activeIsFinal, setActiveIsFinal] = useState('All');
  const [activeStatus, setActiveStatus] = useState('All');

  // Pagination state
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [itemsPerPage, setItemsPerPage] = useState<number>(10);

  // Extract unique clients
  const uniqueClients = useMemo(() => {
    const map = new Map<string, { id: string; name: string }>();
    if (client) {
      map.set(client.id, { id: client.id, name: client.name });
    }
    clients.forEach((c) => {
      map.set(c.id, { id: c.id, name: c.name });
    });
    items.forEach((item) => {
      if (item.clientId && !map.has(item.clientId)) {
        map.set(item.clientId, { id: item.clientId, name: item.clientName });
      }
    });
    return Array.from(map.values());
  }, [items, client, clients]);

  const handleFilter = () => {
    setActiveDesignStyle(designStyleFilter);
    setActiveIsFinal(isFinalFilter);
    setActiveStatus(statusFilter);
    setCurrentPage(1);
  };

  const handleReset = () => {
    setDesignStyleFilter('All');
    setIsFinalFilter('All');
    setStatusFilter('All');
    setActiveDesignStyle('All');
    setActiveIsFinal('All');
    setActiveStatus('All');
    setSearchQuery('');
    setCurrentPage(1);
  };

  const handleRefreshClick = async () => {
    if (!onRefresh) return;
    setIsRefreshing(true);
    try {
      await onRefresh();
      if (showToast) showToast('Final Validation Design list refreshed from CRM');
    } catch (err) {
      console.error(err);
    } finally {
      setIsRefreshing(false);
    }
  };

  const filteredItems = useMemo(() => {
    let list = items;

    if (dataMode === 'clientWise') {
      const activeClientObj = uniqueClients.find((c) => c.id === selectedClientFilterId) || client;
      list = list.filter((i) => {
        if (!activeClientObj) return false;
        const itemSrId = (i.clientId || '').toLowerCase();
        const itemNumId = i.client_id ? String(i.client_id) : itemSrId.replace(/\D/g, '');
        const itemName = (i.clientName || '').toLowerCase();

        const targetSrId = (activeClientObj.id || '').toLowerCase();
        const targetNumId = (activeClientObj as any).clientIdNum ? String((activeClientObj as any).clientIdNum) : targetSrId.replace(/\D/g, '');
        const targetName = (activeClientObj.name || '').toLowerCase();

        if (targetNumId && itemNumId && itemNumId === targetNumId) return true;
        if (targetSrId && itemSrId && (itemSrId === targetSrId || itemSrId.includes(targetSrId) || targetSrId.includes(itemSrId))) return true;
        if (targetName && itemName && (itemName.includes(targetName) || targetName.includes(itemName))) return true;

        return false;
      });
    }

    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase().trim();
      list = list.filter(
        (i) =>
          i.clientName.toLowerCase().includes(q) ||
          i.clientId.toLowerCase().includes(q) ||
          i.fileName.toLowerCase().includes(q) ||
          i.uploadedBy.toLowerCase().includes(q) ||
          (i.remark && i.remark.toLowerCase().includes(q))
      );
    }

    if (activeDesignStyle !== 'All') {
      list = list.filter((i) => (i.designStyle || i.designType) === activeDesignStyle);
    }
    if (activeIsFinal !== 'All') {
      list = list.filter((i) => String(i.isFinal) === activeIsFinal);
    }
    if (activeStatus !== 'All') {
      list = list.filter((i) => i.status.toLowerCase() === activeStatus.toLowerCase());
    }

    return list;
  }, [items, dataMode, selectedClientFilterId, uniqueClients, client, searchQuery, activeDesignStyle, activeIsFinal, activeStatus]);

  // Reset page on search or filter changes
  useEffect(() => {
    setCurrentPage(1);
  }, [dataMode, selectedClientFilterId, searchQuery, activeDesignStyle, activeIsFinal, activeStatus, itemsPerPage]);

  const totalItems = filteredItems.length;
  const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
  const safeCurrentPage = Math.min(Math.max(1, currentPage), totalPages);

  const startIndex = (safeCurrentPage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

  const paginatedItems = useMemo(() => {
    return filteredItems.slice(startIndex, endIndex);
  }, [filteredItems, startIndex, endIndex]);

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-2xs p-3.5 sm:p-4 space-y-3.5 text-slate-800">
      {/* Top Header & Data Scope Mode Selector */}
      <div className="bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-3.5 space-y-3">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
          <div>
            <h2 className="text-base font-bold text-slate-900 flex items-center space-x-2">
              <span>Final Production Drawing</span>
            </h2>
          </div>

          {/* Segmented Control Switcher */}
          <div className="bg-slate-200/70 p-1 rounded-xl flex items-center space-x-1 shrink-0 self-start sm:self-auto">
            <button
              onClick={() => setDataMode('all')}
              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer ${
                dataMode === 'all'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-300/60'
              }`}
            >
              <Globe className="w-3.5 h-3.5 text-sky-400" />
              <span>All Clients</span>
              <span className={`text-[10px] px-1.5 py-0.2 rounded-full font-mono ${dataMode === 'all' ? 'bg-orange-500 text-white' : 'bg-slate-300 text-slate-700'}`}>
                {items.length}
              </span>
            </button>

            <button
              onClick={() => setDataMode('clientWise')}
              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 cursor-pointer ${
                dataMode === 'clientWise'
                  ? 'bg-orange-500 text-white shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-300/60'
              }`}
            >
              <User className="w-3.5 h-3.5 text-white" />
              <span>Filter by Client</span>
              <span className={`text-[10px] px-1.5 py-0.2 rounded-full font-mono ${dataMode === 'clientWise' ? 'bg-slate-900 text-white' : 'bg-slate-300 text-slate-700'}`}>
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
          <div className="bg-slate-100/80 border border-slate-200/80 rounded-lg p-2 px-3 text-[11px] text-slate-600 flex items-center justify-between">
            <span className="flex items-center space-x-1.5">
              <Globe className="w-3.5 h-3.5 text-sky-600" />
              <span>Viewing combined validation files across <strong>all CRM clients</strong>.</span>
            </span>
            <span className="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded font-mono font-bold">
              Global View
            </span>
          </div>
        )}
      </div>

      {/* Search & Refresh Bar */}
      <div className="flex items-center justify-between gap-2">
        <div className="relative flex-1">
          <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400 pointer-events-none" />
          <input
            type="text"
            placeholder="Search by client name, SR ID, file name, or uploader..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full bg-white text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:border-amber-500 shadow-2xs"
          />
        </div>

        {onRefresh && (
          <button
            onClick={handleRefreshClick}
            disabled={isRefreshing}
            className="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg cursor-pointer border border-slate-200 transition-colors disabled:opacity-50 shrink-0"
            title="Refresh Final Validation List"
          >
            <RotateCcw className={`w-4 h-4 ${isRefreshing ? 'animate-spin text-amber-600' : ''}`} />
          </button>
        )}
      </div>

      {/* Filters Bar */}
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
          <div className="grid grid-cols-1 sm:grid-cols-4 gap-2.5 items-end p-3 border-t border-slate-200">
            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Design Style</label>
              <select
                value={designStyleFilter}
                onChange={(e) => setDesignStyleFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500"
              >
                <option value="All">All Design Styles</option>
                <option value="Post Validation Design">Post Validation Design</option>
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Is Final</label>
              <select
                value={isFinalFilter}
                onChange={(e) => setIsFinalFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500"
              >
                <option value="All">All</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Status</label>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500"
              >
                <option value="All">All</option>
                <option value="Approved">Approved</option>
                <option value="Pending">Pending</option>
                <option value="Reject">Reject</option>
              </select>
            </div>

            <div className="flex items-center space-x-2">
              <button
                onClick={handleFilter}
                className="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-bold py-1.5 px-3 rounded-md transition-colors cursor-pointer text-center"
              >
                Filter
              </button>
              <button
                onClick={handleReset}
                className="flex-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold py-1.5 px-3 rounded-md transition-colors cursor-pointer text-center"
              >
                Reset
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Cards List */}
      <div className="space-y-3">
        {paginatedItems.length === 0 ? (
          <div className="bg-white rounded-xl p-8 text-center border border-slate-200 space-y-2">
            <CheckCircle2 className="w-8 h-8 text-emerald-500 mx-auto" />
            <h4 className="text-xs font-bold text-slate-800">No Final Validation Files Found</h4>
            <p className="text-[11px] text-slate-500">
              No validation design records match your selected criteria or search term.
            </p>
          </div>
        ) : (
          paginatedItems.map((item) => {
            const hasPdfFiles = item.upload_file && item.upload_file.length > 0;
            const pdfUrl = hasPdfFiles ? item.upload_file![0] : null;
            const driveUrl = item.upload_url && item.upload_url !== 'testasdfs s fasdf ' ? item.upload_url : null;

            return (
              <div
                key={item.id}
                className="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-2xs space-y-2.5 text-xs text-slate-800 hover:border-slate-300 transition-all"
              >
                {/* Header: Client & Status */}
                <div className="flex items-start justify-between gap-2 border-b border-slate-100 pb-2">
                  <div className="truncate">
                    <span className="text-[10px] text-slate-400 font-semibold uppercase block">Client Name</span>
                    <span className="font-bold text-slate-900 truncate block text-xs">
                      {item.clientName}{' '}
                      <span className="font-mono text-[11px] text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded ml-1">
                        {item.clientId}
                      </span>
                    </span>
                  </div>
                  <div className="flex items-center space-x-1.5 shrink-0">
                    <span className="px-2 py-0.5 rounded text-white text-[10px] font-bold uppercase bg-emerald-600">
                      Is Final: {item.isFinal}
                    </span>
                    <span className="px-2 py-0.5 rounded text-white text-[10px] font-bold uppercase shadow-2xs bg-sky-600">
                      {item.status}
                    </span>
                  </div>
                </div>

                {/* Details Grid */}
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px] bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                  <div>
                    <span className="text-slate-400 block text-[10px]">Design Style</span>
                    <span className="font-bold text-slate-800 truncate block">{item.designStyle || item.designType}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block text-[10px]">Uploaded By</span>
                    <span className="font-medium text-slate-700 truncate block">{item.uploadedBy}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block text-[10px]">Upload Date</span>
                    <span className="font-medium text-slate-600 truncate block">{item.date || 'N/A'}</span>
                  </div>
                  <div className="col-span-2 sm:col-span-3 pt-1 border-t border-slate-200/60">
                    <span className="text-slate-400 block text-[10px]">Remark</span>
                    <span className="font-normal text-slate-700">
                      {item.remark && item.remark.trim() ? item.remark : 'No Remarks'}
                    </span>
                  </div>
                </div>

                {/* Action Buttons: PDF Download & Google Drive Folder */}
                <div className="pt-1 flex flex-wrap items-center justify-between gap-2">
                  <div className="text-[10px] text-slate-400 font-mono">
                    Design ID: #{item.design_id || item.id}
                  </div>

                  <div className="flex items-center space-x-2">
                    {pdfUrl && (
                      <a
                        href={pdfUrl}
                        target="_blank"
                        rel="noreferrer"
                        className="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-2xs transition-colors cursor-pointer"
                      >
                        <FileText className="w-3.5 h-3.5" />
                        <span>View PDF Document</span>
                      </a>
                    )}

                    {driveUrl ? (
                      <a
                        href={driveUrl}
                        target="_blank"
                        rel="noreferrer"
                        className="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-2xs transition-colors cursor-pointer"
                      >
                        <Folder className="w-3.5 h-3.5" />
                        <span>Google Drive Link</span>
                        <ExternalLink className="w-3 h-3" />
                      </a>
                    ) : !pdfUrl && item.url && item.url !== '#' ? (
                      <a
                        href={item.url}
                        target="_blank"
                        rel="noreferrer"
                        className="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-2xs transition-colors cursor-pointer"
                      >
                        <ExternalLink className="w-3.5 h-3.5" />
                        <span>Open Link</span>
                      </a>
                    ) : null}
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
          <div className="flex flex-wrap items-center justify-between sm:justify-start w-full sm:w-auto gap-3">
            <span className="font-medium text-slate-700">
              Showing <strong className="text-slate-900 font-bold">{totalItems === 0 ? 0 : startIndex + 1}</strong> to{' '}
              <strong className="text-slate-900 font-bold">{endIndex}</strong> of{' '}
              <strong className="text-slate-900 font-bold">{totalItems}</strong> files
            </span>

            <div className="flex items-center space-x-1.5 pl-2 border-l border-slate-200">
              <span className="text-slate-500 text-[11px]">Per page:</span>
              <select
                value={itemsPerPage}
                onChange={(e) => setItemsPerPage(Number(e.target.value))}
                className="bg-white border border-slate-300 font-bold text-slate-800 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500 cursor-pointer shadow-2xs"
              >
                <option value={5}>5</option>
                <option value={10}>10</option>
                <option value={20}>20</option>
                <option value={50}>50</option>
              </select>
            </div>
          </div>

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
                            ? 'bg-slate-900 text-white shadow-2xs'
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
    </div>
  );
};
