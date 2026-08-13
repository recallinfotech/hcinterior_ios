import React, { useState, useMemo } from 'react';
import { EscalationItem, ClientProject, EscalationComment } from '../../types';
import {
  AlertTriangle,
  ShieldAlert,
  CheckCircle2,
  Clock,
  Plus,
  Filter,
  UserCheck,
  MessageSquare,
  AlertCircle,
  Search,
  Send,
  Loader2,
  RotateCcw,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  User,
  Phone,
  CornerDownRight,
} from 'lucide-react';
import { saveEscalationReply } from '../../services/clientApi';

interface EscalationSectionProps {
  escalations: EscalationItem[];
  clients: ClientProject[];
  client?: ClientProject;
  showAllClients?: boolean;
  authToken?: string;
  showToast?: (msg: string) => void;
  onAddEscalation?: (newItem: EscalationItem) => void;
  onUpdateStatus?: (id: string, newStatus: string) => void;
  onRefreshEscalations?: () => Promise<void>;
}

export const EscalationSection: React.FC<EscalationSectionProps> = ({
  escalations: initialEscalations,
  clients,
  client,
  showAllClients = false,
  authToken,
  showToast,
  onAddEscalation,
  onUpdateStatus,
  onRefreshEscalations,
}) => {
  // Local state for escalations list so comments & status update instantly on reply
  const [localEscalations, setLocalEscalations] = useState<EscalationItem[]>(initialEscalations);

  // Sync if parent escalations change
  React.useEffect(() => {
    setLocalEscalations(initialEscalations);
  }, [initialEscalations]);

  const [dataMode, setDataMode] = useState<'all' | 'clientWise'>(showAllClients ? 'all' : 'clientWise');
  const [selectedClientFilterId, setSelectedClientFilterId] = useState<string>(client?.id || clients[0]?.id || '');

  React.useEffect(() => {
    setDataMode(showAllClients ? 'all' : 'clientWise');
  }, [showAllClients]);

  React.useEffect(() => {
    if (client?.id) {
      setSelectedClientFilterId(client.id);
    }
  }, [client]);

  const [activeTab, setActiveTab] = useState<'All' | 'Pending' | 'Replied'>('All');
  const [searchQuery, setSearchQuery] = useState('');
  const [isRefreshing, setIsRefreshing] = useState(false);

  // Comment replies state
  const [replyInputs, setReplyInputs] = useState<Record<string | number, string>>({});
  const [submittingIds, setSubmittingIds] = useState<Record<string | number, boolean>>({});
  const [expandedComments, setExpandedComments] = useState<Record<string | number, boolean>>({});

  // Filter logic
  const filteredEscalations = useMemo(() => {
    return localEscalations.filter((item) => {
      if (dataMode === 'clientWise') {
        const activeClientObj = clients.find((c) => c.id === selectedClientFilterId) || client;
        if (activeClientObj) {
          const itemSrId = (item.client_sr_id || item.clientId || '').toLowerCase();
          const itemNumId = item.client_id ? String(item.client_id) : itemSrId.replace(/\D/g, '');
          const itemName = (item.client_name || item.clientName || '').toLowerCase();

          const targetSrId = (activeClientObj.id || '').toLowerCase();
          const targetNumId = (activeClientObj as any).clientIdNum ? String((activeClientObj as any).clientIdNum) : targetSrId.replace(/\D/g, '');
          const targetName = (activeClientObj.name || '').toLowerCase();

          let matchesClient = false;
          if (targetNumId && itemNumId && itemNumId === targetNumId) matchesClient = true;
          if (targetSrId && itemSrId && (itemSrId === targetSrId || itemSrId.includes(targetSrId) || targetSrId.includes(itemSrId))) matchesClient = true;
          if (targetName && itemName && (itemName.includes(targetName) || targetName.includes(itemName))) matchesClient = true;

          if (!matchesClient) return false;
        }
      }

      const clientName = item.client_name || item.clientName || '';
      const clientId = item.client_sr_id || item.clientId || '';
      const remark = item.remark || item.description || '';
      const raisedBy = item.raised_by_name || '';
      const assignedToName = item.assigned_to_name || item.assignedTo || '';
      const statusStr = item.status || 'Open';

      const matchesTab =
        activeTab === 'All'
          ? true
          : activeTab === 'Pending'
            ? statusStr.toLowerCase() === 'open' || statusStr.toLowerCase() === 'pending'
            : statusStr.toLowerCase() === 'replied';

      const q = searchQuery.toLowerCase().trim();
      const matchesSearch =
        !q ||
        clientName.toLowerCase().includes(q) ||
        clientId.toLowerCase().includes(q) ||
        remark.toLowerCase().includes(q) ||
        raisedBy.toLowerCase().includes(q) ||
        assignedToName.toLowerCase().includes(q) ||
        item.comments?.some((c) => c.comment.toLowerCase().includes(q) || c.user_name.toLowerCase().includes(q));

      return matchesTab && matchesSearch;
    });
  }, [localEscalations, dataMode, selectedClientFilterId, client, clients, activeTab, searchQuery]);

  // Stat Counters
  const countOpen = localEscalations.filter(
    (e) => (e.status || '').toLowerCase() === 'open' || (e.status || '').toLowerCase() === 'pending'
  ).length;
  const countReplied = localEscalations.filter((e) => (e.status || '').toLowerCase() === 'replied').length;
  const countTotal = localEscalations.length;

  // Pagination state
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [itemsPerPage, setItemsPerPage] = useState<number>(10);

  // Reset page when search or tab changes
  React.useEffect(() => {
    setCurrentPage(1);
  }, [activeTab, searchQuery, itemsPerPage]);

  const totalItems = filteredEscalations.length;
  const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
  const safeCurrentPage = Math.min(Math.max(1, currentPage), totalPages);

  const startIndex = (safeCurrentPage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

  const paginatedEscalations = useMemo(() => {
    return filteredEscalations.slice(startIndex, endIndex);
  }, [filteredEscalations, startIndex, endIndex]);

  // Refresh handler
  const handleRefresh = async () => {
    if (!onRefreshEscalations) return;
    setIsRefreshing(true);
    try {
      await onRefreshEscalations();
      if (showToast) showToast('Escalation list refreshed from CRM API');
    } catch (err) {
      console.error(err);
    } finally {
      setIsRefreshing(false);
    }
  };

  // Submit comment/reply handler
  const handleSendReply = async (escalationId: string | number) => {
    const text = replyInputs[escalationId]?.trim();
    if (!text) return;

    if (!authToken) {
      if (showToast) showToast('Please login to post a reply.');
      return;
    }

    setSubmittingIds((prev) => ({ ...prev, [escalationId]: true }));

    try {
      const res = await saveEscalationReply(authToken, escalationId, text);
      if (res.success && res.newComment) {
        // Update local list with new comment
        setLocalEscalations((prevList) =>
          prevList.map((item) => {
            if (String(item.id) === String(escalationId)) {
              const updatedComments = [...(item.comments || []), res.newComment!];
              return {
                ...item,
                comments: updatedComments,
                total_comments: updatedComments.length,
                status: res.newStatus || 'Replied',
              };
            }
            return item;
          })
        );

        // Clear input text
        setReplyInputs((prev) => ({ ...prev, [escalationId]: '' }));

        // Expand comment section automatically
        setExpandedComments((prev) => ({ ...prev, [escalationId]: true }));

        if (showToast) showToast('Escalation reply saved successfully!');
      } else {
        if (showToast) showToast(res.message || 'Failed to save reply');
      }
    } catch (err: any) {
      console.error('Error posting reply:', err);
      if (showToast) showToast('Error posting reply. Please try again.');
    } finally {
      setSubmittingIds((prev) => ({ ...prev, [escalationId]: false }));
    }
  };

  const toggleExpandComments = (id: string | number) => {
    setExpandedComments((prev) => ({ ...prev, [id]: !prev[id] }));
  };

  return (
    <div className="space-y-3.5 text-slate-800">
      {/* Overview Stat Cards */}
      <div className="grid grid-cols-3 gap-2">
        <div className="bg-rose-50/90 border border-rose-200/80 rounded-xl p-2.5 text-center">
          <span className="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Open / Pending</span>
          <span className="text-lg font-black text-rose-700">{countOpen}</span>
        </div>
        <div className="bg-sky-50/90 border border-sky-200/80 rounded-xl p-2.5 text-center">
          <span className="text-[10px] font-bold text-sky-700 uppercase tracking-wider block">Replied</span>
          <span className="text-lg font-black text-sky-800">{countReplied}</span>
        </div>
        <div className="bg-slate-50/90 border border-slate-200/80 rounded-xl p-2.5 text-center">
          <span className="text-[10px] font-bold text-slate-700 uppercase tracking-wider block">Total Escalations</span>
          <span className="text-lg font-black text-slate-900">{countTotal}</span>
        </div>
      </div>

      {/* Action Bar: Search & Refresh */}
      <div className="flex items-center justify-between gap-2">
        <div className="relative flex-1">
          <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400 pointer-events-none" />
          <input
            type="text"
            placeholder="Search by client, SR ID, remark or comment..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full bg-white text-xs pl-8 pr-3 py-1.5 rounded-lg border border-zinc-200 focus:outline-none focus:border-amber-400 shadow-2xs"
          />
        </div>

        <button
          onClick={handleRefresh}
          disabled={isRefreshing}
          className="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg cursor-pointer border border-slate-200 transition-colors disabled:opacity-50"
          title="Refresh Escalations"
        >
          <RotateCcw className={`w-4 h-4 ${isRefreshing ? 'animate-spin text-amber-600' : ''}`} />
        </button>
      </div>

      {/* Filter Tabs */}
      <div className="flex space-x-1 bg-slate-100 p-1 rounded-xl">
        {(['All', 'Pending', 'Replied'] as const).map((tab) => (
          <button
            key={tab}
            onClick={() => setActiveTab(tab)}
            className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer text-center ${activeTab === tab ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-800'
              }`}
          >
            {tab === 'All'
              ? `All (${countTotal})`
              : tab === 'Pending'
                ? `Pending (${countOpen})`
                : `Replied (${countReplied})`}
          </button>
        ))}
      </div>

      {/* Escalation Cards List */}
      <div className="space-y-3">
        {paginatedEscalations.length === 0 ? (
          <div className="bg-white rounded-xl p-6 border border-slate-200 text-center space-y-2">
            <CheckCircle2 className="w-8 h-8 text-emerald-500 mx-auto" />
            <h4 className="text-xs font-bold text-slate-800">No Escalation Issues Found</h4>
            <p className="text-[11px] text-slate-500">
              No escalations match your search query or selected filter tab.
            </p>
          </div>
        ) : (
          paginatedEscalations.map((item) => {
            const clientName = item.client_name || item.clientName || 'N/A';
            const clientSrId = item.client_sr_id || item.clientId || 'N/A';
            const raisedByName = item.raised_by_name || 'N/A';
            const assignedToName = item.assigned_to_name || item.assignedTo || 'Unassigned';
            const remarkText = item.remark || item.description || 'No Remarks';
            const statusStr = item.status || 'Open';
            const commentsList = item.comments || [];
            const isCommentsExpanded = expandedComments[item.id] ?? false;
            const isSubmitting = submittingIds[item.id] || false;

            return (
              <div
                key={item.id}
                className="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-3.5 space-y-3 relative overflow-hidden transition-all hover:border-slate-300"
              >
                {/* Header info */}
                <div className="flex items-start justify-between border-b border-slate-100 pb-2">
                  <div>
                    <div className="flex items-center space-x-2">
                      <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-mono">
                        Escalation #{item.id}
                      </span>
                      <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 font-mono">
                        {clientSrId}
                      </span>
                    </div>
                    <h4 className="text-xs font-bold text-slate-900 mt-1 flex items-center gap-1.5">
                      <span>{clientName}</span>
                      {item.client_phone && item.client_phone !== '-' && (
                        <span className="text-[10px] font-normal text-slate-500 flex items-center gap-0.5">
                          <Phone className="w-2.5 h-2.5" />
                          {item.client_phone}
                        </span>
                      )}
                    </h4>
                  </div>

                  {/* Status Badge */}
                  <span
                    className={`text-[10px] font-bold px-2.5 py-1 rounded-full border uppercase tracking-wide ${statusStr.toLowerCase() === 'replied'
                        ? 'bg-sky-50 text-sky-700 border-sky-200'
                        : statusStr.toLowerCase() === 'resolved'
                          ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                          : 'bg-rose-50 text-rose-700 border-rose-200'
                      }`}
                  >
                    {statusStr}
                  </span>
                </div>

                {/* Main Remark / Issue Description */}
                <div className="bg-rose-50/50 p-2.5 rounded-lg border border-rose-100 space-y-1">
                  <div className="flex items-center space-x-1.5 text-[11px] font-bold text-rose-800">
                    <AlertTriangle className="w-3.5 h-3.5 text-rose-600 shrink-0" />
                    <span>Issue Remark</span>
                  </div>
                  <p className="text-xs text-slate-700 leading-relaxed pl-5 whitespace-pre-line font-medium">
                    {remarkText}
                  </p>
                </div>

                {/* Assignment & Created Time */}
                <div className="grid grid-cols-2 gap-2 text-[10px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100">
                  <div className="flex items-center space-x-1">
                    <User className="w-3 h-3 text-slate-400 shrink-0" />
                    <span>
                      Raised by: <strong className="text-slate-800">{raisedByName}</strong>
                    </span>
                  </div>
                  <div className="flex items-center space-x-1 justify-end">
                    <UserCheck className="w-3 h-3 text-sky-600 shrink-0" />
                    <span>
                      Assigned to: <strong className="text-slate-800">{assignedToName}</strong>
                    </span>
                  </div>
                  {item.created_at && (
                    <div className="col-span-2 text-[10px] text-slate-400 flex items-center justify-end space-x-1 pt-1 border-t border-slate-200/50">
                      <Clock className="w-3 h-3" />
                      <span>{item.created_at}</span>
                    </div>
                  )}
                </div>

                {/* Comments & Replies Thread */}
                <div className="border-t border-slate-100 pt-2.5 space-y-2">
                  <button
                    onClick={() => toggleExpandComments(item.id)}
                    className="flex items-center justify-between w-full text-xs font-bold text-slate-700 hover:text-slate-900 cursor-pointer"
                  >
                    <div className="flex items-center space-x-1.5">
                      <MessageSquare className="w-3.5 h-3.5 text-sky-600" />
                      <span>
                        Discussion & Replies ({commentsList.length})
                      </span>
                    </div>
                    <span className="text-[10px] font-semibold text-slate-400 hover:underline">
                      {isCommentsExpanded ? 'Hide' : 'Show Comments'}
                    </span>
                  </button>

                  {isCommentsExpanded && (
                    <div className="space-y-2 pt-1 pl-1">
                      {/* Comments List */}
                      {commentsList.length === 0 ? (
                        <p className="text-[11px] text-slate-400 italic pl-2">
                          No replies yet. Use the box below to reply to this escalation.
                        </p>
                      ) : (
                        <div className="space-y-2 max-h-60 overflow-y-auto pr-1">
                          {commentsList.map((c) => (
                            <div
                              key={c.id}
                              className="bg-slate-50 border border-slate-200/80 rounded-lg p-2.5 text-xs space-y-1"
                            >
                              <div className="flex items-center justify-between text-[11px]">
                                <span className="font-bold text-slate-800 flex items-center space-x-1">
                                  <CornerDownRight className="w-3 h-3 text-sky-500 shrink-0" />
                                  <span>{c.user_name}</span>
                                </span>
                                <span className="text-[10px] text-slate-400 font-mono">
                                  {c.created_at}
                                </span>
                              </div>
                              <p className="text-slate-700 pl-4 leading-normal font-normal whitespace-pre-line">
                                {c.comment}
                              </p>
                            </div>
                          ))}
                        </div>
                      )}

                      {/* Add Reply Box */}
                      <div className="flex items-center space-x-2 pt-1">
                        <input
                          type="text"
                          placeholder="Write a reply comment..."
                          value={replyInputs[item.id] || ''}
                          onChange={(e) =>
                            setReplyInputs((prev) => ({
                              ...prev,
                              [item.id]: e.target.value,
                            }))
                          }
                          onKeyDown={(e) => {
                            if (e.key === 'Enter' && !e.shiftKey) {
                              e.preventDefault();
                              handleSendReply(item.id);
                            }
                          }}
                          className="flex-1 bg-white text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:outline-none focus:border-sky-500 shadow-2xs"
                        />
                        <button
                          onClick={() => handleSendReply(item.id)}
                          disabled={isSubmitting || !replyInputs[item.id]?.trim()}
                          className="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-40 text-white font-bold text-xs rounded-lg shadow-2xs flex items-center space-x-1 cursor-pointer transition-colors"
                        >
                          {isSubmitting ? (
                            <Loader2 className="w-3.5 h-3.5 animate-spin" />
                          ) : (
                            <>
                              <Send className="w-3 h-3" />
                              <span className="hidden sm:inline">Reply</span>
                            </>
                          )}
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            );
          })
        )}
      </div>

      {/* Pagination Controls */}
      {filteredEscalations.length > 0 && (
        <div className="bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
          <div className="flex flex-wrap items-center justify-between sm:justify-start w-full sm:w-auto gap-3">
            <span className="font-medium text-slate-700">
              Showing <strong className="text-slate-900 font-bold">{totalItems === 0 ? 0 : startIndex + 1}</strong> to{' '}
              <strong className="text-slate-900 font-bold">{endIndex}</strong> of{' '}
              <strong className="text-slate-900 font-bold">{totalItems}</strong> escalations
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
                        className={`min-w-[32px] h-8 rounded-lg font-bold text-xs transition-all cursor-pointer ${page === safeCurrentPage
                            ? 'bg-rose-600 text-white shadow-2xs'
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
