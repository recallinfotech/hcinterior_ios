import React, { useState, useMemo } from 'react';
import { OnSitePurchaseItem, ClientProject } from '../../types';
import {
  User,
  FileText,
  Globe,
  RotateCcw,
  ExternalLink,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  CheckCircle2,
  Clock,
  Download,
  Building,
  ChevronDown,
  Filter,
} from 'lucide-react';

interface OnSitePurchaseSectionProps {
  items: OnSitePurchaseItem[];
  clients?: ClientProject[];
  client?: ClientProject;
  showAllClients?: boolean;
  authToken?: string;
  showToast?: (msg: string) => void;
  onRefresh?: () => Promise<void>;
}

export const OnSitePurchaseSection: React.FC<OnSitePurchaseSectionProps> = ({
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

  React.useEffect(() => {
    if (client) {
      setSelectedClientFilterId(client.id);
    }
    setDataMode(showAllClients ? 'all' : 'clientWise');
  }, [client, showAllClients]);

  const [isFilterExpanded, setIsFilterExpanded] = useState(false);
  const [clientIdSearch, setClientIdSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('All');
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');

  const [activeSearch, setActiveSearch] = useState('');
  const [activeStatus, setActiveStatus] = useState('All');
  const [activeFromDate, setActiveFromDate] = useState('');
  const [activeToDate, setActiveToDate] = useState('');

  const [isRefreshing, setIsRefreshing] = useState(false);

  // Pagination
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [itemsPerPage, setItemsPerPage] = useState<number>(10);

  // Extract unique list of clients
  const uniqueClients = useMemo(() => {
    const map = new Map<string, { id: string; name: string }>();
    if (client) {
      map.set(client.id, { id: client.id, name: client.name });
    }
    clients.forEach((c) => {
      if (c.id && !map.has(c.id)) {
        map.set(c.id, { id: c.id, name: c.name });
      }
    });
    items.forEach((item) => {
      if (item.clientId && !map.has(item.clientId)) {
        map.set(item.clientId, { id: item.clientId, name: item.clientName || 'Client ' + item.clientId });
      }
    });
    return Array.from(map.values());
  }, [items, clients, client]);

  const handleFilter = () => {
    setActiveSearch(clientIdSearch);
    setActiveStatus(statusFilter);
    setActiveFromDate(fromDate);
    setActiveToDate(toDate);
    setCurrentPage(1);
  };

  const handleReset = () => {
    setClientIdSearch('');
    setStatusFilter('All');
    setFromDate('');
    setToDate('');
    setActiveSearch('');
    setActiveStatus('All');
    setActiveFromDate('');
    setActiveToDate('');
    setCurrentPage(1);
  };

  const handleRefreshClick = async () => {
    if (onRefresh) {
      setIsRefreshing(true);
      try {
        await onRefresh();
        showToast?.('On Site Purchase list updated from CRM API.');
      } catch (err) {
        console.error('Refresh error:', err);
      } finally {
        setIsRefreshing(false);
      }
    }
  };

  const filteredItems = useMemo(() => {
    let list = items;

    if (dataMode === 'clientWise') {
      const activeClientObj = uniqueClients.find((c) => c.id === selectedClientFilterId) || client;
      list = list.filter((i) => {
        if (!activeClientObj) return false;
        const itemSrId = (i.clientId || i.client_sr_id || '').toLowerCase();
        const itemNumId = i.client_id ? String(i.client_id) : itemSrId.replace(/\D/g, '');
        const itemName = (i.clientName || i.client_name || '').toLowerCase();

        const targetSrId = (activeClientObj.id || '').toLowerCase();
        const targetNumId = (activeClientObj as any).clientIdNum ? String((activeClientObj as any).clientIdNum) : targetSrId.replace(/\D/g, '');
        const targetName = (activeClientObj.name || '').toLowerCase();

        if (targetNumId && itemNumId && itemNumId === targetNumId) return true;
        if (targetSrId && itemSrId && (itemSrId === targetSrId || itemSrId.includes(targetSrId) || targetSrId.includes(itemSrId))) return true;
        if (targetName && itemName && (itemName.includes(targetName) || targetName.includes(itemName))) return true;

        return false;
      });
    }

    if (activeSearch.trim()) {
      const q = activeSearch.toLowerCase().trim();
      list = list.filter(
        (i) =>
          i.clientId.toLowerCase().includes(q) ||
          i.clientName.toLowerCase().includes(q) ||
          i.fileName.toLowerCase().includes(q) ||
          (i.remark && i.remark.toLowerCase().includes(q))
      );
    }

    if (activeStatus !== 'All') {
      list = list.filter(
        (i) => i.status.toLowerCase() === activeStatus.toLowerCase()
      );
    }

    if (activeFromDate) {
      list = list.filter((i) => {
        const itemDate = i.date || i.created_date || '';
        if (!itemDate) return true;
        return itemDate >= activeFromDate;
      });
    }

    if (activeToDate) {
      list = list.filter((i) => {
        const itemDate = i.date || i.created_date || '';
        if (!itemDate) return true;
        return itemDate <= activeToDate;
      });
    }

    return list;
  }, [items, dataMode, selectedClientFilterId, uniqueClients, client, activeSearch, activeStatus, activeFromDate, activeToDate]);

  // Pagination calculation
  const totalItems = filteredItems.length;
  const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
  const currentPaginatedItems = useMemo(() => {
    return filteredItems.slice(startIndex, endIndex);
  }, [filteredItems, startIndex, endIndex]);

  const handlePageChange = (page: number) => {
    if (page >= 1 && page <= totalPages) {
      setCurrentPage(page);
    }
  };

  const openFileUrl = (url: string) => {
    if (!url || url === '#') {
      alert('No valid file URL available.');
      return;
    }
    window.open(url, '_blank', 'noopener,noreferrer');
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-4">
      {/* Header & Data Scope Bar */}
      <div className="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-3">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
          <div>
            <div className="flex items-center space-x-2">
              <h2 className="text-base font-bold text-slate-900">
                On Site Purchase Request List
              </h2>
              {onRefresh && (
                <button
                  onClick={handleRefreshClick}
                  disabled={isRefreshing}
                  className="p-1 rounded-md text-slate-500 hover:text-sky-600 hover:bg-slate-200/60 transition-colors cursor-pointer"
                  title="Refresh On Site Purchase list"
                >
                  <RotateCcw className={`w-3.5 h-3.5 ${isRefreshing ? 'animate-spin text-sky-600' : ''}`} />
                </button>
              )}
            </div>
          </div>

          {/* Segmented Mode Selector */}
          <div className="bg-slate-200/70 p-1 rounded-xl flex items-center space-x-1 shrink-0 self-start sm:self-auto">
            <button
              onClick={() => {
                setDataMode('all');
                setCurrentPage(1);
              }}
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
              onClick={() => {
                setDataMode('clientWise');
                setCurrentPage(1);
              }}
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

        {/* Dynamic Mode Banner */}
        {dataMode === 'clientWise' ? (
          <div className="bg-orange-50 border border-orange-200 rounded-xl p-3 space-y-2 text-xs">
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
                onChange={(e) => {
                  setSelectedClientFilterId(e.target.value);
                  setCurrentPage(1);
                }}
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
              <span>Viewing combined purchase requests across <strong>all CRM clients</strong>.</span>
            </span>
            <span className="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded font-mono font-bold">
              Global View
            </span>
          </div>
        )}
      </div>

      {/* Filter controls bar */}
      <div className="bg-slate-50 rounded-xl border border-slate-200 overflow-hidden text-xs">
        <button
          type="button"
          onClick={() => setIsFilterExpanded(!isFilterExpanded)}
          className="w-full flex items-center justify-between p-3 bg-slate-100/80 hover:bg-slate-200/80 font-bold text-slate-700 transition-colors cursor-pointer select-none"
        >
          <div className="flex items-center space-x-2">
            <Filter className="w-4 h-4 text-sky-600" />
            <span>Filter Options</span>
            {(activeSearch || activeStatus !== 'All' || activeFromDate || activeToDate) && (
              <span className="bg-sky-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold">Active</span>
            )}
          </div>
          <div className="flex items-center space-x-1 text-slate-500 text-[11px]">
            <span>{isFilterExpanded ? 'Hide Filters' : 'Show Filters'}</span>
            <ChevronDown className={`w-4 h-4 transition-transform duration-200 ${isFilterExpanded ? 'rotate-180' : ''}`} />
          </div>
        </button>

        {isFilterExpanded && (
          <div className="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end p-3 border-t border-slate-200">
            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Search Clients</label>
              <input
                type="text"
                placeholder="Search ID or Name"
                value={clientIdSearch}
                onChange={(e) => setClientIdSearch(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-orange-500"
              />
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Status</label>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer"
              >
                <option value="All">All Status</option>
                <option value="Recieved">Recieved</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
              <input
                type="date"
                value={fromDate}
                onChange={(e) => setFromDate(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer"
              />
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
              <input
                type="date"
                value={toDate}
                onChange={(e) => setToDate(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer"
              />
            </div>

            <div className="flex items-center space-x-2">
              <button
                onClick={handleFilter}
                className="flex-1 bg-sky-500 hover:bg-sky-600 text-white font-semibold py-1.5 px-3 rounded-md transition-colors cursor-pointer text-center"
              >
                Filter
              </button>
              <button
                onClick={handleReset}
                className="flex-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-semibold py-1.5 px-3 rounded-md transition-colors cursor-pointer text-center"
              >
                Reset
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Card List View */}
      <div className="space-y-3">
        {currentPaginatedItems.length === 0 ? (
          <div className="bg-slate-50 rounded-xl p-8 text-center text-slate-400 italic border border-slate-200">
            No On Site Purchase Requests found.
          </div>
        ) : (
          currentPaginatedItems.map((item) => {
            const hasMultipleFiles = Array.isArray(item.upload_file) && item.upload_file.length > 1;
            const primaryFileUrl =
              (item.upload_file && item.upload_file[0]) || item.fileUrl || item.upload_url || '#';

            return (
              <div
                key={item.id}
                className="bg-slate-50 rounded-xl p-4 border border-slate-200 hover:border-sky-300 transition-all space-y-3 text-xs text-slate-800 shadow-2xs"
              >
                {/* Header: Client info & Status */}
                <div className="flex items-start justify-between gap-3">
                  <div className="min-w-0 flex-1">
                    <span className="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">
                      Client
                    </span>
                    <span className="font-extrabold text-sky-700 text-sm truncate block">
                      {item.clientName}
                      <span className="font-mono text-xs text-slate-500 font-normal ml-1">
                        ({item.clientId})
                      </span>
                    </span>
                  </div>

                  <span
                    className={`px-2.5 py-1 rounded-md text-white text-[10px] font-extrabold uppercase shadow-xs shrink-0 flex items-center space-x-1 ${
                      item.status.toLowerCase() === 'recieved' || item.status.toLowerCase() === 'approved'
                        ? 'bg-emerald-600'
                        : 'bg-amber-500'
                    }`}
                  >
                    {item.status.toLowerCase() === 'recieved' || item.status.toLowerCase() === 'approved' ? (
                      <CheckCircle2 className="w-3 h-3" />
                    ) : (
                      <Clock className="w-3 h-3" />
                    )}
                    <span>{item.status}</span>
                  </span>
                </div>

                {/* Details Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-2.5 border-t border-slate-200/80">
                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">File / Document Name</span>
                    <span className="font-semibold text-slate-900 truncate block">
                      {item.fileName}
                    </span>
                  </div>

                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Created Date</span>
                    <span className="font-medium text-slate-700">
                      {item.date || item.created_date || 'N/A'}
                    </span>
                  </div>

                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Approval / Status Date</span>
                    <span className="font-medium text-slate-700">
                      {item.status_approve_date || 'N/A'}
                    </span>
                  </div>
                </div>

                {/* Remarks */}
                <div className="bg-amber-50/80 border border-amber-200/80 rounded-lg p-2 px-3 text-[11px] text-amber-900">
                  <span className="font-bold mr-1">Remark:</span>
                  <span>{item.remark && item.remark.trim() ? item.remark : 'No Remarks'}</span>
                </div>

                {/* Action Bar */}
                <div className="pt-2 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-2">
                  <div className="text-[10px] text-slate-400 italic">
                    Req ID: <span className="font-mono text-slate-600 font-semibold">{item.id}</span>
                  </div>

                  <div className="flex items-center space-x-2">
                    {hasMultipleFiles ? (
                      item.upload_file?.map((fUrl, idx) => (
                        <button
                          key={idx}
                          onClick={() => openFileUrl(fUrl)}
                          className="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                        >
                          <FileText className="w-3.5 h-3.5" />
                          <span>View Doc #{idx + 1}</span>
                          <ExternalLink className="w-3 h-3 opacity-80" />
                        </button>
                      ))
                    ) : (
                      <button
                        onClick={() => openFileUrl(primaryFileUrl)}
                        className="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                      >
                        <FileText className="w-3.5 h-3.5" />
                        <span>View Request Document</span>
                        <ExternalLink className="w-3 h-3 opacity-80" />
                      </button>
                    )}
                  </div>
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* Pagination Footer */}
      {totalPages > 1 && (
        <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200 text-xs">
          <div className="text-slate-500 text-[11px]">
            Showing <span className="font-semibold text-slate-800">{startIndex + 1}</span> to{' '}
            <span className="font-semibold text-slate-800">{endIndex}</span> of{' '}
            <span className="font-semibold text-slate-800">{totalItems}</span> requests
          </div>

          <div className="flex items-center space-x-1">
            <button
              onClick={() => handlePageChange(1)}
              disabled={currentPage === 1}
              className="p-1.5 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
              title="First Page"
            >
              <ChevronsLeft className="w-4 h-4" />
            </button>
            <button
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage === 1}
              className="p-1.5 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
              title="Previous Page"
            >
              <ChevronLeft className="w-4 h-4" />
            </button>

            <span className="px-3 py-1 font-bold text-slate-800 text-xs">
              Page {currentPage} of {totalPages}
            </span>

            <button
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage === totalPages}
              className="p-1.5 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
              title="Next Page"
            >
              <ChevronRight className="w-4 h-4" />
            </button>
            <button
              onClick={() => handlePageChange(totalPages)}
              disabled={currentPage === totalPages}
              className="p-1.5 rounded-md border border-slate-300 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
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
