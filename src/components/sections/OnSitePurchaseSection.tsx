import React, { useState, useMemo } from 'react';
import { OnSitePurchaseItem, ClientProject } from '../../types';
import { createOnSitePurchase } from '../../services/clientApi';
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
  Plus,
  Upload,
  X,
  Send,
  AlertCircle,
  ShoppingBag,
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
  const [localItems, setLocalItems] = useState<OnSitePurchaseItem[]>(items);

  React.useEffect(() => {
    setLocalItems(items);
  }, [items]);

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

  // Create Modal / Form state
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [createClientId, setCreateClientId] = useState<string>(() => {
    if (client?.clientIdNum) return String(client.clientIdNum);
    if (client?.id) return client.id.replace(/\D/g, '');
    return '';
  });
  const [fileNameInput, setFileNameInput] = useState('');
  const [brandInput, setBrandInput] = useState('');
  const [messageInput, setMessageInput] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  React.useEffect(() => {
    const numId = client?.clientIdNum
      ? String(client.clientIdNum)
      : client?.id
      ? client.id.replace(/\D/g, '')
      : selectedClientFilterId
      ? selectedClientFilterId.replace(/\D/g, '')
      : '';
    if (numId) {
      setCreateClientId(numId);
    }
  }, [selectedClientFilterId, client]);

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
    const map = new Map<string, { id: string; numericId: string; name: string }>();
    if (client) {
      const numId = client.clientIdNum ? String(client.clientIdNum) : client.id.replace(/\D/g, '');
      map.set(client.id, { id: client.id, numericId: numId, name: client.name });
    }
    clients.forEach((c) => {
      if (c.id && !map.has(c.id)) {
        const numId = c.clientIdNum ? String(c.clientIdNum) : c.id.replace(/\D/g, '');
        map.set(c.id, { id: c.id, numericId: numId, name: c.name });
      }
    });
    items.forEach((item) => {
      const key = item.clientId || item.client_sr_id || String(item.client_id || '');
      if (key && !map.has(key)) {
        const numId = item.client_id ? String(item.client_id) : key.replace(/\D/g, '');
        map.set(key, { id: key, numericId: numId, name: item.clientName || item.client_name || 'Client ' + numId });
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
        showToast?.('On Site Purchase list updated from CRM.');
      } catch (err) {
        console.error('Refresh error:', err);
      } finally {
        setIsRefreshing(false);
      }
    }
  };

  const handleCreateSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError(null);

    const rawTarget = createClientId || selectedClientFilterId || client?.clientIdNum || client?.id;
    if (!rawTarget) {
      setFormError('Please select a client for this On-Site Purchase request.');
      return;
    }

    const matchedClient = uniqueClients.find((c) => c.id === String(rawTarget) || c.numericId === String(rawTarget));
    const numericClientId = matchedClient
      ? matchedClient.numericId
      : (client?.clientIdNum ? String(client.clientIdNum) : String(rawTarget).replace(/\D/g, ''));

    if (!numericClientId) {
      setFormError('Could not determine numeric client_id.');
      return;
    }

    if (!fileNameInput.trim()) {
      setFormError('Please enter a Request Title / Description.');
      return;
    }

    setIsSubmitting(true);
    try {
      const res = await createOnSitePurchase(
        authToken || '',
        numericClientId,
        fileNameInput.trim(),
        selectedFile,
        brandInput.trim(),
        messageInput.trim()
      );

      if (res.success) {
        showToast?.(res.message || 'On site purchase request created successfully!');
        if (res.data) {
          const rawDocUrl = getCleanUrl(res.data.upload_url || res.data.fileUrl || (res.data.upload_file && res.data.upload_file[0]));
          const enrichedData: OnSitePurchaseItem = {
            ...res.data,
            clientName: res.data.clientName && res.data.clientName !== 'N/A' ? res.data.clientName : (matchedClient?.name || 'Client ' + numericClientId),
            clientId: res.data.clientId && res.data.clientId !== 'N/A' ? res.data.clientId : (matchedClient?.id || 'HC' + numericClientId),
            client_id: res.data.client_id || parseInt(numericClientId, 10),
            client_sr_id: res.data.client_sr_id || matchedClient?.id,
            fileUrl: rawDocUrl || res.data.fileUrl || '#',
            upload_url: rawDocUrl || res.data.upload_url || '#',
            upload_file: rawDocUrl ? [rawDocUrl] : (res.data.upload_file || []),
          };
          setLocalItems((prev) => [enrichedData, ...prev]);
        }
        if (onRefresh) {
          await onRefresh();
        }
        setShowCreateModal(false);
        setFileNameInput('');
        setBrandInput('');
        setMessageInput('');
        setSelectedFile(null);
      } else {
        setFormError(res.message || 'Failed to create purchase request.');
      }
    } catch (err: any) {
      console.error('Error creating on site purchase:', err);
      setFormError(err.message || 'Error submitting request. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const filteredItems = useMemo(() => {
    let list = localItems;

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

  const getCleanUrl = (f: any): string => {
    if (!f || f === '#') return '';
    let target = '';
    if (typeof f === 'object' && f) {
      target = f.file_url || f.path || f.url || '';
    } else if (typeof f === 'string') {
      target = f;
    }
    if (!target || target === '#') return '';
    if (!target.startsWith('http://') && !target.startsWith('https://')) {
      target = `https://crm.hcinterior.in/${target.replace(/^\//, '')}`;
    }
    return target;
  };

  const openFileUrl = (url: any) => {
    const clean = getCleanUrl(url);
    if (!clean) {
      alert('No valid file URL available.');
      return;
    }
    window.open(clean, '_blank', 'noopener,noreferrer');
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

          <div className="flex items-center space-x-2 shrink-0 self-start sm:self-auto">
            <button
              onClick={() => {
                const activeNumId = client?.clientIdNum
                  ? String(client.clientIdNum)
                  : client?.id
                  ? client.id.replace(/\D/g, '')
                  : selectedClientFilterId
                  ? selectedClientFilterId.replace(/\D/g, '')
                  : uniqueClients[0]?.numericId || '';
                if (activeNumId) {
                  setCreateClientId(activeNumId);
                }
                setFormError(null);
                setShowCreateModal(true);
              }}
              className="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-xs flex items-center space-x-1.5 transition-colors cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>New Purchase Request</span>
            </button>
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
            const primaryRawFile = (item.upload_file && item.upload_file[0]) || item.fileUrl || item.upload_url || '#';
            const primaryCleanUrl = getCleanUrl(primaryRawFile);
            const isImageFile = primaryCleanUrl && /\.(png|jpe?g|gif|webp|bmp|svg)($|\?)/i.test(primaryCleanUrl);

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
                <div className="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs pt-2.5 border-t border-slate-200/80">
                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">File / Title</span>
                    <span className="font-semibold text-slate-900 truncate block">
                      {item.fileName}
                    </span>
                  </div>

                  <div>
                    <span className="text-slate-400 block text-[10px] font-semibold">Brand</span>
                    <span className="font-semibold text-slate-900 truncate block">
                      {item.brand || 'N/A'}
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

                {/* Attached File Preview (if Image) */}
                {isImageFile && (
                  <div className="p-2 bg-white rounded-xl border border-slate-200 flex items-center space-x-3">
                    <img
                      src={primaryCleanUrl}
                      alt={item.fileName}
                      className="w-16 h-16 object-cover rounded-lg border border-slate-300 shadow-2xs shrink-0 cursor-pointer"
                      onClick={() => openFileUrl(primaryCleanUrl)}
                    />
                    <div className="min-w-0 flex-1">
                      <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Attached Photo Preview</span>
                      <span className="text-xs font-semibold text-slate-800 truncate block">{item.fileName}</span>
                      <button
                        onClick={() => openFileUrl(primaryCleanUrl)}
                        className="mt-1 text-[11px] text-sky-600 hover:text-sky-800 font-bold flex items-center space-x-1 cursor-pointer"
                      >
                        <span>Open Full Image</span>
                        <ExternalLink className="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                )}

                {/* Site Message / Message */}
                {(item.message || item.site_message) && (
                  <div className="bg-sky-50/80 border border-sky-200/80 rounded-lg p-2 px-3 text-[11px] text-sky-900">
                    <span className="font-bold mr-1">Message:</span>
                    <span>{item.site_message || item.message}</span>
                  </div>
                )}

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
                        onClick={() => openFileUrl(primaryRawFile)}
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

      {/* CREATE ON-SITE PURCHASE MODAL */}
      {showCreateModal && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-lg w-full p-4 sm:p-5 space-y-4 border border-slate-200 shadow-2xl animate-in zoom-in-95 duration-200">
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <div className="flex items-center space-x-2.5">
                <div className="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center font-bold">
                  <ShoppingBag className="w-4.5 h-4.5" />
                </div>
                <div>
                  <h3 className="font-bold text-sm text-slate-900">New On-Site Purchase Request</h3>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setShowCreateModal(false)}
                className="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Form Error Alert */}
            {formError && (
              <div className="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center space-x-2">
                <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
                <span>{formError}</span>
              </div>
            )}

            {/* Form Body */}
            <form onSubmit={handleCreateSubmit} className="space-y-3.5 text-xs">
              {/* Client Selection / Display */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 mb-1">
                  Client <span className="text-rose-500">*</span>
                </label>
                <select
                  value={createClientId}
                  onChange={(e) => setCreateClientId(e.target.value)}
                  className="w-full bg-slate-50 border border-slate-300 font-bold text-slate-900 text-xs rounded-xl p-2.5 focus:outline-none focus:border-orange-500 focus:bg-white shadow-2xs cursor-pointer"
                  required
                >
                  <option value="">-- Select Client --</option>
                  {uniqueClients.map((c) => (
                    <option key={c.id} value={c.numericId}>
                      {c.name} ({c.id})
                    </option>
                  ))}
                </select>
              </div>

              {/* Request Title / Description */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 mb-1">
                  Request Title / Description <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={fileNameInput}
                  onChange={(e) => setFileNameInput(e.target.value)}
                  className="w-full bg-white border border-slate-300 font-medium text-slate-900 text-xs rounded-xl p-2.5 focus:outline-none focus:border-orange-500 shadow-2xs"
                  required
                />
              </div>

              {/* Brand Name */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 mb-1">
                  Brand Name
                </label>
                <input
                  type="text"
                  value={brandInput}
                  onChange={(e) => setBrandInput(e.target.value)}
                  className="w-full bg-white border border-slate-300 font-medium text-slate-900 text-xs rounded-xl p-2.5 focus:outline-none focus:border-orange-500 shadow-2xs"
                />
              </div>

              {/* Site Message / Note */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 mb-1">
                  Site Message / Note
                </label>
                <textarea
                  rows={2}
                  value={messageInput}
                  onChange={(e) => setMessageInput(e.target.value)}
                  className="w-full bg-white border border-slate-300 font-medium text-slate-900 text-xs rounded-xl p-2.5 focus:outline-none focus:border-orange-500 shadow-2xs resize-none"
                />
              </div>

              {/* File Attachment */}
              <div>
                <label className="block text-[11px] font-bold text-slate-700 mb-1">
                  Attach Document / Photo
                </label>
                <div className="border-2 border-dashed border-slate-300 hover:border-orange-400 rounded-xl p-4 bg-slate-50 hover:bg-orange-50/40 transition-colors text-center cursor-pointer relative">
                  <input
                    type="file"
                    accept="image/*,application/pdf"
                    onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                    className="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
                  />
                  {selectedFile ? (
                    <div className="flex items-center justify-center space-x-2 text-slate-800 font-bold">
                      <FileText className="w-4.5 h-4.5 text-orange-600 shrink-0" />
                      <span className="truncate max-w-[200px] text-xs">{selectedFile.name}</span>
                      <span className="text-[10px] text-slate-500 font-normal font-mono">
                        ({(selectedFile.size / 1024).toFixed(1)} KB)
                      </span>
                    </div>
                  ) : (
                    <div className="space-y-1">
                      <Upload className="w-5 h-5 text-slate-400 mx-auto" />
                      <p className="text-xs text-slate-600 font-semibold">Click or drag file to attach photo/PDF</p>
                      <p className="text-[10px] text-slate-400 font-mono">Supports PNG, JPG, PDF up to 10MB</p>
                    </div>
                  )}
                </div>
              </div>

              {/* Actions */}
              <div className="pt-2 flex items-center justify-end space-x-2 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-md flex items-center space-x-1.5 transition-colors cursor-pointer disabled:opacity-50"
                >
                  {isSubmitting ? (
                    <>
                      <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      <span>Creating...</span>
                    </>
                  ) : (
                    <>
                      <Send className="w-3.5 h-3.5" />
                      <span>Submit Request</span>
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
