import React, { useState, useMemo } from 'react';
import { DispatchItem, DispatchQuery, ClientProject } from '../../types';
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
  MessageSquare,
  Truck,
  Image as ImageIcon,
  Plus,
  Send,
  Paperclip,
  Check,
  ChevronDown,
  ChevronUp,
  Filter,
} from 'lucide-react';
import { addDispatchQuery } from '../../services/clientApi';

interface DispatchSectionProps {
  dispatchItems: DispatchItem[];
  clients?: ClientProject[];
  client?: ClientProject;
  showAllClients?: boolean;
  authToken?: string;
  showToast?: (msg: string) => void;
  onRefresh?: () => Promise<void>;
}

export const DispatchSection: React.FC<DispatchSectionProps> = ({
  dispatchItems,
  clients = [],
  client,
  showAllClients = true,
  authToken = '',
  showToast,
  onRefresh,
}) => {
  const [items, setItems] = useState<DispatchItem[]>(dispatchItems);

  // Synchronize items when props update
  React.useEffect(() => {
    setItems(dispatchItems);
  }, [dispatchItems]);

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

  // Filters state
  const [isFilterExpanded, setIsFilterExpanded] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState('All');
  const [isReceivedFilter, setIsReceivedFilter] = useState('All');
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');

  const [activeSearch, setActiveSearch] = useState('');
  const [activeStatus, setActiveStatus] = useState('All');
  const [activeIsReceived, setActiveIsReceived] = useState('All');
  const [activeFromDate, setActiveFromDate] = useState('');
  const [activeToDate, setActiveToDate] = useState('');

  const [isRefreshing, setIsRefreshing] = useState(false);

  // Pagination
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [itemsPerPage] = useState<number>(10);

  // Expanded queries card IDs
  const [expandedQueriesItemIds, setExpandedQueriesItemIds] = useState<Set<string | number>>(
    new Set()
  );

  // Modal 1: Add Query Modal
  const [queryModalItem, setQueryModalItem] = useState<DispatchItem | null>(null);
  const [queryRemark, setQueryRemark] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isSubmittingQuery, setIsSubmittingQuery] = useState(false);

  // Modal 2: Dispatch Received Status Modal
  const [statusModalItem, setStatusModalItem] = useState<DispatchItem | null>(null);
  const [receivedChoice, setReceivedChoice] = useState<'Select' | 'Yes' | 'No'>('Select');

  // Remarks state for section
  const [remarkInput, setRemarkInput] = useState('');
  const [remarksList, setRemarksList] = useState<string[]>([]);

  // Unique client list
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
    setActiveSearch(searchQuery);
    setActiveStatus(statusFilter);
    setActiveIsReceived(isReceivedFilter);
    setActiveFromDate(fromDate);
    setActiveToDate(toDate);
    setCurrentPage(1);
  };

  const handleReset = () => {
    setSearchQuery('');
    setStatusFilter('All');
    setIsReceivedFilter('All');
    setFromDate('');
    setToDate('');
    setActiveSearch('');
    setActiveStatus('All');
    setActiveIsReceived('All');
    setActiveFromDate('');
    setActiveToDate('');
    setCurrentPage(1);
  };

  const handleRefreshClick = async () => {
    if (onRefresh) {
      setIsRefreshing(true);
      try {
        await onRefresh();
        showToast?.('Dispatch list updated from CRM.');
      } catch (err) {
        console.error('Refresh error:', err);
      } finally {
        setIsRefreshing(false);
      }
    }
  };

  const toggleQueries = (itemId: string | number) => {
    setExpandedQueriesItemIds((prev) => {
      const next = new Set(prev);
      if (next.has(itemId)) {
        next.delete(itemId);
      } else {
        next.add(itemId);
      }
      return next;
    });
  };

  // Add dispatch query submit
  const handleQuerySubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!queryModalItem || !queryRemark.trim()) return;

    const dispatchId = queryModalItem.id;
    const clientId = queryModalItem.client_id || queryModalItem.clientId;

    setIsSubmittingQuery(true);
    try {
      const res = await addDispatchQuery(
        authToken,
        dispatchId,
        clientId,
        queryRemark.trim(),
        selectedFile
      );

      if (res && res.status) {
        showToast?.('Dispatch query added successfully!');
        const newQueryData: DispatchQuery = res.data
          ? {
              id: res.data.id || Math.random(),
              dispatch_id: res.data.dispatch_id || dispatchId,
              client_id: res.data.client_id || clientId,
              remarks: res.data.remarks || queryRemark.trim(),
              uploaded_by: res.data.uploaded_by,
              uploaded_by_name: res.data.uploaded_by_name || 'Me',
              file_name: res.data.file_name || (selectedFile ? selectedFile.name : ''),
              file_url: res.data.file_url || '',
              created_date: res.data.created_date || new Date().toISOString().slice(0, 19).replace('T', ' '),
            }
          : {
              id: Math.random(),
              dispatch_id: dispatchId,
              client_id: clientId,
              remarks: queryRemark.trim(),
              uploaded_by_name: 'Me',
              file_name: selectedFile ? selectedFile.name : '',
              created_date: new Date().toISOString().slice(0, 19).replace('T', ' '),
            };

        // Update local item queries list immediately
        setItems((prev) =>
          prev.map((i) => {
            if (i.id === dispatchId) {
              const updatedQueries = [newQueryData, ...(i.queries || [])];
              return {
                ...i,
                queries: updatedQueries,
                total_queries: updatedQueries.length,
              };
            }
            return i;
          })
        );

        // Expand queries for this item so user sees their new query
        setExpandedQueriesItemIds((prev) => new Set(prev).add(dispatchId));

        setQueryRemark('');
        setSelectedFile(null);
        setQueryModalItem(null);

        // Optionally refresh in background
        if (onRefresh) {
          onRefresh();
        }
      } else {
        alert(res?.message || 'Failed to add dispatch query. Please try again.');
      }
    } catch (err: any) {
      console.error('Add dispatch query error:', err);
      alert('Error submitting query: ' + (err?.message || 'Network error'));
    } finally {
      setIsSubmittingQuery(false);
    }
  };

  const handleStatusUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    if (statusModalItem && receivedChoice !== 'Select') {
      const isReceived = receivedChoice === 'Yes';
      setItems((prev) =>
        prev.map((i) =>
          i.id === statusModalItem.id
            ? { ...i, status: isReceived ? 'Received' : 'Pending', is_recived: isReceived ? 1 : 0 }
            : i
        )
      );
      setRemarksList((prev) => [
        `Status updated for ${statusModalItem.itemName}: Item Received = ${receivedChoice}`,
        ...prev,
      ]);
      showToast?.(`Status updated: Item Received = ${receivedChoice}`);
      setStatusModalItem(null);
      setReceivedChoice('Select');
    }
  };

  const handleAddRemark = () => {
    if (remarkInput.trim()) {
      setRemarksList([remarkInput.trim(), ...remarksList]);
      setRemarkInput('');
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
          i.itemName.toLowerCase().includes(q) ||
          (i.upload_url && i.upload_url.toLowerCase().includes(q)) ||
          (i.remark && i.remark.toLowerCase().includes(q))
      );
    }

    if (activeStatus !== 'All') {
      list = list.filter(
        (i) => i.status.toLowerCase() === activeStatus.toLowerCase()
      );
    }

    if (activeIsReceived !== 'All') {
      const targetIsReceived = activeIsReceived === 'Yes';
      list = list.filter((i) => {
        if (targetIsReceived) {
          return i.is_recived === 1 || i.status.toLowerCase() === 'received';
        } else {
          return i.is_recived === 0 || i.status.toLowerCase() !== 'received';
        }
      });
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
  }, [items, dataMode, selectedClientFilterId, uniqueClients, client, activeSearch, activeStatus, activeIsReceived, activeFromDate, activeToDate]);

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

  const openUrl = (url?: string) => {
    if (!url || url === '#' || url === 'test') {
      alert('No valid document or URL provided for this record.');
      return;
    }
    const targetUrl = url.startsWith('http') ? url : `https://${url}`;
    window.open(targetUrl, '_blank', 'noopener,noreferrer');
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 space-y-4 text-slate-800">
      {/* Header & Data Scope Bar */}
      <div className="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-3">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
          <div>
            <div className="flex items-center space-x-2">
              <Truck className="w-5 h-5 text-emerald-600" />
              <h2 className="text-base font-bold text-slate-900">
                Dispatch List
              </h2>
              {onRefresh && (
                <button
                  onClick={handleRefreshClick}
                  disabled={isRefreshing}
                  className="p-1 rounded-md text-slate-500 hover:text-sky-600 hover:bg-slate-200/60 transition-colors cursor-pointer"
                  title="Refresh Dispatch list"
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
              <span className={`text-[10px] px-1.5 py-0.2 rounded-full font-mono ${dataMode === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700'}`}>
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
                  ? 'bg-emerald-600 text-white shadow-xs'
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
          <div className="bg-emerald-50 border border-emerald-200 rounded-xl p-3 space-y-2 text-xs">
            <div className="flex items-center space-x-2.5">
              <div className="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                <User className="w-4 h-4" />
              </div>
              <div className="min-w-0 flex-1">
                <span className="text-[10px] text-emerald-700 font-bold uppercase block tracking-wider">Active Client Selection</span>
                <span className="font-bold text-slate-900 text-xs truncate block">
                  {selectedClientFilterId
                    ? uniqueClients.find((c) => c.id === selectedClientFilterId)?.name || 'Selected Client'
                    : 'Select client'}
                  {selectedClientFilterId ? (
                    <span className="text-emerald-600 font-mono text-xs font-normal ml-1.5">
                      ({selectedClientFilterId})
                    </span>
                  ) : null}
                </span>
              </div>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center gap-1.5 pt-2 border-t border-emerald-200/80">
              <span className="text-slate-700 text-xs font-bold shrink-0">Switch Client:</span>
              <select
                value={selectedClientFilterId}
                onChange={(e) => {
                  setSelectedClientFilterId(e.target.value);
                  setCurrentPage(1);
                }}
                className="w-full bg-white border border-emerald-300 font-semibold text-slate-800 text-xs rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 shadow-2xs truncate cursor-pointer"
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
              <span>Viewing combined dispatch records across <strong>all CRM clients</strong>.</span>
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
            {(activeSearch || activeStatus !== 'All' || activeIsReceived !== 'All' || activeFromDate || activeToDate) && (
              <span className="bg-sky-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-semibold">Active</span>
            )}
          </div>
          <div className="flex items-center space-x-1 text-slate-500 text-[11px]">
            <span>{isFilterExpanded ? 'Hide Filters' : 'Show Filters'}</span>
            <ChevronDown className={`w-4 h-4 transition-transform duration-200 ${isFilterExpanded ? 'rotate-180' : ''}`} />
          </div>
        </button>

        {isFilterExpanded && (
          <div className="grid grid-cols-1 sm:grid-cols-6 gap-2.5 items-end p-3 border-t border-slate-200">
            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Search Clients</label>
              <input
                type="text"
                placeholder="Search ID, Name or File"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Status</label>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
              >
                <option value="All">All Status</option>
                <option value="Approved">Approved</option>
                <option value="Pending">Pending</option>
                <option value="Received">Received</option>
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">Received?</label>
              <select
                value={isReceivedFilter}
                onChange={(e) => setIsReceivedFilter(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2.5 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
              >
                <option value="All">All</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
              <input
                type="date"
                value={fromDate}
                onChange={(e) => setFromDate(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
              />
            </div>

            <div>
              <label className="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
              <input
                type="date"
                value={toDate}
                onChange={(e) => setToDate(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded-md px-2 py-1.5 text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
              />
            </div>

            <div className="flex items-center space-x-1.5">
              <button
                onClick={handleFilter}
                className="flex-1 bg-sky-500 hover:bg-sky-600 text-white font-semibold py-1.5 px-2.5 rounded-md transition-colors cursor-pointer text-center"
              >
                Filter
              </button>
              <button
                onClick={handleReset}
                className="flex-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-semibold py-1.5 px-2.5 rounded-md transition-colors cursor-pointer text-center"
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
            No dispatch records found.
          </div>
        ) : (
          currentPaginatedItems.map((item) => {
            const hasUploadFiles = Array.isArray(item.upload_file) && item.upload_file.length > 0;
            const primaryUploadFile = hasUploadFiles ? item.upload_file![0] : item.imageUrl;
            const statusLower = (item.status || '').toLowerCase();
            const queryList = item.queries || [];
            const queryCount = item.total_queries ?? queryList.length;
            const isQueriesExpanded = expandedQueriesItemIds.has(item.id);

            return (
              <div
                key={item.id}
                className="bg-slate-50 rounded-xl p-4 border border-slate-200 hover:border-sky-300 transition-all space-y-3 text-xs text-slate-800 shadow-2xs"
              >
                {/* Header: Client info, Status, & Queries Badge */}
                <div className="flex items-start justify-between gap-3">
                  <div className="flex items-start space-x-3 min-w-0 flex-1">
                    <div className="w-12 h-12 rounded-lg overflow-hidden border border-slate-300 shrink-0 bg-white shadow-2xs">
                      {primaryUploadFile ? (
                        <img
                          src={primaryUploadFile}
                          alt={item.itemName}
                          className="w-full h-full object-cover"
                          onError={(e) => {
                            (e.target as HTMLElement).style.display = 'none';
                          }}
                        />
                      ) : (
                        <ImageIcon className="w-5 h-5 text-slate-400 m-auto mt-3" />
                      )}
                    </div>
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
                      <h4 className="font-bold text-slate-900 text-xs mt-0.5 truncate">
                        {item.itemName}
                      </h4>
                    </div>
                  </div>

                  <div className="flex flex-col items-end space-y-1 shrink-0">
                    <span
                      className={`px-2.5 py-1 rounded-md text-white text-[10px] font-extrabold uppercase shadow-xs flex items-center space-x-1 ${
                        statusLower === 'approved' || statusLower === 'received'
                          ? 'bg-emerald-600'
                          : 'bg-amber-500'
                      }`}
                    >
                      {statusLower === 'approved' || statusLower === 'received' ? (
                        <CheckCircle2 className="w-3 h-3" />
                      ) : (
                        <Clock className="w-3 h-3" />
                      )}
                      <span>{item.status || 'Pending'}</span>
                    </span>

                    {queryCount > 0 && (
                      <button
                        onClick={() => toggleQueries(item.id)}
                        className="text-[11px] font-bold text-rose-600 hover:text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full flex items-center space-x-1 transition-colors cursor-pointer"
                      >
                        <MessageSquare className="w-3 h-3" />
                        <span>{queryCount} {queryCount === 1 ? 'Query' : 'Queries'}</span>
                        {isQueriesExpanded ? <ChevronUp className="w-3 h-3" /> : <ChevronDown className="w-3 h-3" />}
                      </button>
                    )}
                  </div>
                </div>

                {/* Details Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs pt-2.5 border-t border-slate-200/80">
                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Created Date</span>
                    <span className="font-medium text-slate-700">
                      {item.created_date || item.date || 'N/A'}
                    </span>
                  </div>

                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Approved Date</span>
                    <span className="font-medium text-slate-700">
                      {item.status_approve_date || 'N/A'}
                    </span>
                  </div>

                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Dispatch ID</span>
                    <span className="font-mono font-bold text-slate-800">
                      #{item.id}
                    </span>
                  </div>
                </div>

                {/* Queries Thread (Collapsible Accordion) */}
                {isQueriesExpanded && queryList.length > 0 && (
                  <div className="bg-rose-50/70 border border-rose-200 rounded-xl p-3 space-y-2.5 animate-in fade-in duration-200">
                    <div className="flex items-center justify-between border-b border-rose-200/80 pb-1.5">
                      <span className="text-xs font-bold text-rose-900 flex items-center space-x-1.5">
                        <MessageSquare className="w-3.5 h-3.5 text-rose-600" />
                        <span>Dispatch Queries Thread ({queryList.length})</span>
                      </span>
                      <button
                        onClick={() => {
                          setQueryModalItem(item);
                          setQueryRemark('');
                        }}
                        className="text-[10px] font-bold text-rose-700 hover:text-rose-900 bg-white border border-rose-300 px-2 py-0.5 rounded transition-colors cursor-pointer flex items-center space-x-1"
                      >
                        <Plus className="w-3 h-3" />
                        <span>Add Query</span>
                      </button>
                    </div>

                    <div className="space-y-2 max-h-60 overflow-y-auto pr-1 text-xs">
                      {queryList.map((q) => (
                        <div
                          key={q.id}
                          className="bg-white p-2.5 rounded-lg border border-rose-200 shadow-2xs space-y-1"
                        >
                          <div className="flex items-center justify-between text-[10px]">
                            <span className="font-bold text-slate-800 flex items-center space-x-1">
                              <User className="w-3 h-3 text-rose-500" />
                              <span>{q.uploaded_by_name || 'Staff User'}</span>
                            </span>
                            <span className="text-slate-400 font-mono">
                              {q.created_date || ''}
                            </span>
                          </div>

                          <p className="text-slate-700 text-xs font-medium pl-1 leading-relaxed">
                            {q.remarks}
                          </p>

                          {q.file_url && (
                            <div className="pt-1 flex items-center space-x-1">
                              <button
                                onClick={() => openUrl(q.file_url)}
                                className="text-[10px] font-bold text-sky-600 hover:underline flex items-center space-x-1 bg-sky-50 px-2 py-0.5 rounded border border-sky-200 cursor-pointer"
                              >
                                <Paperclip className="w-3 h-3 text-sky-500" />
                                <span className="truncate max-w-[200px]">{q.file_name || 'Attached File'}</span>
                                <ExternalLink className="w-3 h-3 shrink-0" />
                              </button>
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* Action Bar */}
                <div className="pt-2 border-t border-slate-200/80 flex flex-wrap items-center justify-between gap-2">
                  <div className="flex items-center space-x-2">
                    {hasUploadFiles ? (
                      item.upload_file!.map((fUrl, idx) => (
                        <button
                          key={idx}
                          onClick={() => openUrl(fUrl)}
                          className="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                        >
                          <FileText className="w-3.5 h-3.5" />
                          <span>View Doc #{idx + 1}</span>
                          <ExternalLink className="w-3 h-3 opacity-80" />
                        </button>
                      ))
                    ) : item.imageUrl && item.imageUrl !== '#' ? (
                      <button
                        onClick={() => openUrl(item.imageUrl)}
                        className="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                      >
                        <FileText className="w-3.5 h-3.5" />
                        <span>View Attachment</span>
                        <ExternalLink className="w-3 h-3 opacity-80" />
                      </button>
                    ) : (
                      <span className="text-[11px] text-slate-400 italic">No File Attached</span>
                    )}
                  </div>

                  <div className="flex items-center space-x-1.5">
                    {/* Checkmark Button -> Opens Received Status modal */}
                    <button
                      onClick={() => {
                        setStatusModalItem(item);
                        setReceivedChoice('Select');
                      }}
                      className="px-2.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg text-xs flex items-center space-x-1 cursor-pointer shadow-xs transition-colors"
                      title="Dispatch Received Status"
                    >
                      <Check className="w-3.5 h-3.5 stroke-[3]" />
                      <span className="hidden sm:inline">Received?</span>
                    </button>

                    {/* Speech Bubble Button -> Opens Add Item Query modal */}
                    <button
                      onClick={() => {
                        setQueryModalItem(item);
                        setQueryRemark('');
                        setSelectedFile(null);
                      }}
                      className="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 cursor-pointer shadow-xs transition-colors"
                      title="Add Query to Dispatch"
                    >
                      <MessageSquare className="w-3.5 h-3.5" />
                      <span>Add Query</span>
                    </button>
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
            <span className="font-semibold text-slate-800">{totalItems}</span> records
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

      {/* Remarks Section */}
      <div className="bg-sky-50 rounded-2xl p-4 border border-sky-200 space-y-3">
        <h3 className="text-xs font-bold text-sky-900 border-b border-sky-200 pb-1.5">Remarks</h3>

        <div className="space-y-1.5">
          {remarksList.length > 0 ? (
            remarksList.map((r, i) => (
              <div key={i} className="p-2 bg-white rounded-lg text-xs text-slate-700 border border-sky-100 font-medium leading-relaxed">
                • {r}
              </div>
            ))
          ) : (
            <div className="p-2 bg-white/70 rounded-lg text-xs text-slate-500 italic border border-sky-100/60 font-medium">
              No Remarks
            </div>
          )}
        </div>

        <div className="space-y-2 pt-1">
          <textarea
            rows={2}
            value={remarkInput}
            onChange={(e) => setRemarkInput(e.target.value)}
            placeholder="Enter remark..."
            className="w-full p-2.5 bg-white border border-sky-300 rounded-lg text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500"
          ></textarea>

          <button
            onClick={handleAddRemark}
            className="px-4 py-1.5 bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs rounded transition-colors cursor-pointer shadow-sm"
          >
            Add Remark
          </button>
        </div>
      </div>

      {/* ================= MODAL 1: ADD DISPATCH QUERY (Red Header) ================= */}
      {queryModalItem && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200 animate-in zoom-in-95 duration-150">
            {/* Red Title Banner matching design */}
            <div className="bg-red-500 px-4 py-3 text-white flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <MessageSquare className="w-4 h-4" />
                <h3 className="text-sm font-bold tracking-wide">Add Dispatch Query</h3>
              </div>
              <button
                onClick={() => setQueryModalItem(null)}
                className="text-white/80 hover:text-white cursor-pointer font-bold text-base"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleQuerySubmit} className="p-4 space-y-4 text-xs">
              <div className="bg-slate-50 p-2.5 rounded-lg border border-slate-200 space-y-1">
                <div className="text-[10px] text-slate-400 uppercase font-bold">Dispatch Item Details</div>
                <div className="font-bold text-slate-800">{queryModalItem.itemName}</div>
                <div className="text-[11px] text-slate-500">
                  Client: <span className="font-semibold text-slate-700">{queryModalItem.clientName}</span> ({queryModalItem.clientId}) | Dispatch ID: #{queryModalItem.id}
                </div>
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1.5">
                  Remarks / Query Message <span className="text-red-500">*</span>
                </label>
                <textarea
                  rows={3}
                  value={queryRemark}
                  onChange={(e) => setQueryRemark(e.target.value)}
                  className="w-full bg-white border border-slate-300 rounded-md p-2.5 text-xs text-slate-800 focus:outline-none focus:border-red-500 shadow-2xs resize-none"
                  placeholder="Enter dispatch query remarks..."
                  required
                />
              </div>

              <div>
                <label className="block text-slate-700 font-bold mb-1.5">
                  Upload File <span className="text-slate-400 font-normal">(Optional)</span>
                </label>
                <div className="border border-slate-300 rounded-md p-2 bg-white">
                  <input
                    type="file"
                    onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                    className="block w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                  />
                  {selectedFile && (
                    <div className="mt-1.5 text-[11px] text-emerald-600 font-semibold flex items-center space-x-1">
                      <Paperclip className="w-3 h-3" />
                      <span>Selected: {selectedFile.name}</span>
                    </div>
                  )}
                </div>
              </div>

              <div className="pt-2 flex items-center justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setQueryModalItem(null)}
                  className="px-4 py-2 bg-white border border-slate-300 text-slate-700 font-semibold rounded text-xs hover:bg-slate-50 transition-colors cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={isSubmittingQuery || !queryRemark.trim()}
                  className="px-5 py-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white font-bold rounded text-xs transition-colors shadow-sm cursor-pointer flex items-center space-x-1.5"
                >
                  {isSubmittingQuery ? (
                    <>
                      <RotateCcw className="w-3.5 h-3.5 animate-spin" />
                      <span>Sending...</span>
                    </>
                  ) : (
                    <>
                      <Send className="w-3.5 h-3.5" />
                      <span>Submit Query</span>
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ================= MODAL 2: DISPATCH STATUS ("Item Received ? Yes / No") (Orange Header) ================= */}
      {statusModalItem && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-xl max-w-xs w-full overflow-hidden shadow-2xl border border-slate-200 animate-in zoom-in-95 duration-150">
            {/* Orange Title Banner */}
            <div className="bg-orange-500 px-4 py-2.5 text-white flex items-center justify-between">
              <h3 className="text-sm font-bold tracking-wide">Dispatch Status</h3>
              <button
                onClick={() => setStatusModalItem(null)}
                className="text-white/80 hover:text-white cursor-pointer font-bold text-sm"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleStatusUpdate} className="p-4 space-y-4 text-xs">
              <div>
                <label className="block text-slate-600 font-medium mb-1.5">
                  Item Received ?
                </label>
                <select
                  value={receivedChoice}
                  onChange={(e) => setReceivedChoice(e.target.value as 'Select' | 'Yes' | 'No')}
                  className="w-full bg-white border border-slate-300 rounded-md p-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-orange-500 cursor-pointer"
                >
                  <option value="Select">Select</option>
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                </select>
              </div>

              <div className="pt-2 flex items-center justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setStatusModalItem(null)}
                  className="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 font-semibold rounded text-xs hover:bg-slate-50 transition-colors cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={receivedChoice === 'Select'}
                  className="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white font-bold rounded text-xs transition-colors shadow-sm cursor-pointer"
                >
                  Update
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
