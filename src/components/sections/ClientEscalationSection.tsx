import React, { useState, useEffect } from 'react';
import {
  AlertTriangle,
  Plus,
  Send,
  MessageSquare,
  User,
  Clock,
  CheckCircle2,
  AlertCircle,
  RotateCcw,
  ShieldAlert,
  ChevronDown,
  ChevronUp,
  CornerDownRight,
  Phone,
} from 'lucide-react';
import { ClientProject, EscalationItem, EscalationComment } from '../../types';
import { fetchEscalationList, createEscalation, saveEscalationReply } from '../../services/clientApi';

interface ClientEscalationSectionProps {
  client: ClientProject;
  authToken?: string;
  showToast?: (msg: string) => void;
  onEscalationCreated?: (newItem: EscalationItem) => void;
}

export const ClientEscalationSection: React.FC<ClientEscalationSectionProps> = ({
  client,
  authToken = '',
  showToast,
  onEscalationCreated,
}) => {
  const [escalations, setEscalations] = useState<EscalationItem[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  // Form state for Create Escalation
  const [isCreating, setIsCreating] = useState<boolean>(false);
  const [showCreateForm, setShowCreateForm] = useState<boolean>(false);
  const [assignedTo, setAssignedTo] = useState<number>(5);
  const [remark, setRemark] = useState<string>('');

  // Comment replies state
  const [replyInputs, setReplyInputs] = useState<Record<string | number, string>>({});
  const [submittingIds, setSubmittingIds] = useState<Record<string | number, boolean>>({});
  const [expandedComments, setExpandedComments] = useState<Record<string | number, boolean>>({});

  const activeClientId = client?.clientIdNum || (client?.id ? parseInt(client.id.replace(/\D/g, ''), 10) : 513);
  const clientSrId = client?.id || 'HC101784';
  const clientName = client?.name || 'Client';

  // Fetch escalations for this specific client
  const loadClientEscalations = async () => {
    setIsLoading(true);
    setErrorMsg(null);

    try {
      const list = await fetchEscalationList(authToken, activeClientId);
      setEscalations(list || []);
    } catch (err: any) {
      console.error('Failed to load client escalations:', err);
      setErrorMsg('Could not sync live client escalations.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadClientEscalations();
  }, [activeClientId, authToken]);

  // Handle Create Escalation
  const handleCreateEscalationSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!remark.trim()) {
      setErrorMsg('Please enter an issue remark/description.');
      return;
    }

    setIsCreating(true);
    setErrorMsg(null);
    setSuccessMsg(null);

    try {
      const res = await createEscalation(authToken, activeClientId, remark.trim(), Number(assignedTo) || 5);
      if (res.success) {
        setSuccessMsg(res.message || 'Escalation created successfully');
        if (showToast) showToast('Escalation created successfully!');
        setRemark('');

        if (res.data) {
          setEscalations((prev) => [res.data!, ...prev]);
          if (onEscalationCreated) onEscalationCreated(res.data);
        }
        await loadClientEscalations();

        // Collapse create form after successful submission
        setShowCreateForm(false);
      } else {
        setErrorMsg(res.message || 'Failed to create escalation.');
      }
    } catch (err: any) {
      console.error('Error creating escalation:', err);
      setErrorMsg('Failed to create escalation: ' + (err.message || 'Network error'));
    } finally {
      setIsCreating(false);
    }
  };

  // Handle Reply Submission
  const handleSendReply = async (escalationId: string | number) => {
    const text = replyInputs[escalationId]?.trim();
    if (!text) return;

    setSubmittingIds((prev) => ({ ...prev, [escalationId]: true }));

    try {
      const res = await saveEscalationReply(authToken, escalationId, text);
      if (res.success && res.newComment) {
        setEscalations((prevList) =>
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

        setReplyInputs((prev) => ({ ...prev, [escalationId]: '' }));
        setExpandedComments((prev) => ({ ...prev, [escalationId]: true }));
        if (showToast) showToast('Reply saved successfully!');
      } else {
        if (showToast) showToast(res.message || 'Failed to save reply');
      }
    } catch (err: any) {
      console.error('Error posting reply:', err);
      if (showToast) showToast('Error posting reply.');
    } finally {
      setSubmittingIds((prev) => ({ ...prev, [escalationId]: false }));
    }
  };

  const toggleExpandComments = (id: string | number) => {
    setExpandedComments((prev) => ({ ...prev, [id]: !prev[id] }));
  };

  return (
    <div className="bg-white rounded-2xl border border-zinc-200/90 shadow-sm p-4 space-y-4 text-zinc-800">
      {/* Header Banner */}
      <div className="bg-zinc-950 text-white p-3.5 rounded-xl shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border border-zinc-800">
        <div className="flex items-center space-x-3">
          <div className="w-9 h-9 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-xs border border-rose-500/30 shrink-0">
            <ShieldAlert className="w-5 h-5" />
          </div>
          <div>
            <div className="flex items-center space-x-2">
              <h3 className="text-sm font-bold text-zinc-100">{clientName} - Escalation Section</h3>
              <span className="px-2 py-0.5 bg-rose-500/20 text-rose-300 font-mono text-[11px] font-bold rounded border border-rose-500/30">
                {clientSrId}
              </span>
            </div>
            <p className="text-[11px] text-zinc-400 mt-0.5">
              Client ID: <span className="text-amber-400 font-mono font-bold">{activeClientId}</span> | Manage & track client site escalations
            </p>
          </div>
        </div>

        <div className="flex items-center space-x-2 w-full sm:w-auto justify-end">
          <button
            onClick={loadClientEscalations}
            disabled={isLoading}
            className="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition-colors cursor-pointer border border-zinc-700 disabled:opacity-50"
            title="Reload Escalations"
          >
            <RotateCcw className={`w-3.5 h-3.5 ${isLoading ? 'animate-spin text-amber-400' : ''}`} />
            <span>Sync</span>
          </button>

          <button
            onClick={() => setShowCreateForm(!showCreateForm)}
            className="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center space-x-1 cursor-pointer transition-colors"
          >
            <Plus className={`w-4 h-4 transition-transform ${showCreateForm ? 'rotate-45' : ''}`} />
            <span>{showCreateForm ? 'Close Form' : 'Create Escalation'}</span>
          </button>
        </div>
      </div>

      {/* Alerts */}
      {errorMsg && (
        <div className="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
            <span>{errorMsg}</span>
          </div>
          <button onClick={() => setErrorMsg(null)} className="text-rose-500 font-bold text-xs">Dismiss</button>
        </div>
      )}

      {successMsg && (
        <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl text-xs flex items-center justify-between animate-in fade-in duration-200">
          <div className="flex items-center space-x-2">
            <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
            <span className="font-medium">{successMsg}</span>
          </div>
          <button onClick={() => setSuccessMsg(null)} className="text-emerald-700 font-bold text-xs">Dismiss</button>
        </div>
      )}

      {/* CREATE ESCALATION FORM CARD */}
      {showCreateForm && (
        <div className="bg-rose-50/70 border-2 border-rose-200 rounded-2xl p-4 space-y-3 animate-in slide-in-from-top-2 duration-200">
          <div className="flex items-center justify-between border-b border-rose-200 pb-2">
            <h4 className="text-xs font-bold text-rose-950 uppercase tracking-wider flex items-center space-x-1.5">
              <Plus className="w-4 h-4 text-rose-600" />
              <span>Create Escalation</span>
            </h4>
            <span className="text-[10px] text-rose-700 font-semibold font-mono">
              Target API: mobileapi/Client/create_escalation
            </span>
          </div>

          <form onSubmit={handleCreateEscalationSubmit} className="space-y-3 text-xs">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-[11px] font-bold text-zinc-700 mb-1">
                  Client Details
                </label>
                <div className="w-full bg-white border border-zinc-300 rounded-lg p-2 font-semibold text-zinc-900 text-xs shadow-2xs">
                  {clientName} ({clientSrId} - ID: {activeClientId})
                </div>
              </div>

              <div>
                <label className="block text-[11px] font-bold text-zinc-700 mb-1">
                  Assigned Manager (assigned_to)
                </label>
                <select
                  value={assignedTo}
                  onChange={(e) => setAssignedTo(Number(e.target.value))}
                  className="w-full bg-white border border-zinc-300 rounded-lg p-2 font-bold text-zinc-800 focus:outline-none focus:border-rose-500 shadow-2xs"
                >
                  <option value={5}>Nishant Singh (ID: 5)</option>
                  <option value={166}>Planning Team (ID: 166)</option>
                  <option value={10}>Site Supervisor (ID: 10)</option>
                  <option value={12}>QC Manager (ID: 12)</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block text-[11px] font-bold text-zinc-700 mb-1">
                Escalation Issue / Remark <span className="text-rose-600">*</span>
              </label>
              <textarea
                rows={3}
                value={remark}
                onChange={(e) => setRemark(e.target.value)}
                placeholder="Client issue regarding site work..."
                className="w-full bg-white border border-zinc-300 rounded-xl p-2.5 text-xs text-zinc-900 focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 shadow-2xs"
                required
              />
            </div>

            <div className="pt-1 flex items-center justify-end space-x-2">
              <button
                type="button"
                onClick={() => setShowCreateForm(false)}
                className="px-3 py-1.5 bg-zinc-200 hover:bg-zinc-300 text-zinc-800 font-bold text-xs rounded-lg transition-colors cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={isCreating}
                className="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center space-x-1.5 transition-colors cursor-pointer disabled:opacity-50"
              >
                {isCreating ? (
                  <>
                    <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                    <span>Creating...</span>
                  </>
                ) : (
                  <>
                    <Send className="w-3.5 h-3.5" />
                    <span>Create Escalation</span>
                  </>
                )}
              </button>
            </div>
          </form>
        </div>
      )}

      {/* CLIENT ESCALATION LIST */}
      <div className="space-y-3">
        <div className="flex items-center justify-between pb-2 border-b border-zinc-200">
          <h4 className="text-xs font-bold text-zinc-900 uppercase tracking-wider flex items-center space-x-1.5">
            <AlertTriangle className="w-4 h-4 text-rose-600" />
            <span>Client Escalations ({escalations.length})</span>
          </h4>
          <span className="text-[11px] font-semibold text-zinc-500">
            Comments & Replies Enabled
          </span>
        </div>

        {escalations.length === 0 ? (
          <div className="text-center py-10 bg-zinc-50 rounded-2xl border border-dashed border-zinc-300 space-y-2">
            <CheckCircle2 className="w-8 h-8 mx-auto text-emerald-500" />
            <h5 className="text-xs font-bold text-zinc-800">No Escalations Logged</h5>
            <p className="text-[11px] text-zinc-500">
              Click &quot;Create Escalation&quot; above to log a new site issue for {clientName}.
            </p>
          </div>
        ) : (
          escalations.map((item) => {
            const statusStr = item.status || 'Open';
            const commentsList = item.comments || [];
            const isCommentsExpanded = expandedComments[item.id] ?? true; // Default expanded for client view
            const isSubmitting = submittingIds[item.id] || false;

            return (
              <div
                key={item.id}
                className="bg-zinc-50/80 rounded-xl border border-zinc-200 p-3.5 space-y-3 relative overflow-hidden transition-all hover:border-zinc-300"
              >
                {/* Header */}
                <div className="flex items-start justify-between border-b border-zinc-200/80 pb-2">
                  <div>
                    <div className="flex items-center space-x-2">
                      <span className="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-zinc-200 text-zinc-800">
                        Escalation #{item.id}
                      </span>
                      <span className="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-900 border border-rose-200">
                        {item.client_sr_id || clientSrId}
                      </span>
                    </div>
                    <h4 className="text-xs font-bold text-zinc-900 mt-1 flex items-center gap-1.5">
                      <span>{item.client_name || clientName}</span>
                    </h4>
                  </div>

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

                {/* Details Grid */}
                <div className="grid grid-cols-2 gap-2 text-[11px]">
                  <div className="bg-white p-2 rounded-lg border border-zinc-200/80">
                    <span className="text-zinc-400 font-semibold block text-[10px] uppercase">Raised By</span>
                    <span className="font-bold text-zinc-800 flex items-center gap-1 mt-0.5">
                      <User className="w-3 h-3 text-amber-500" />
                      {item.raised_by_name || 'Planning'}
                    </span>
                  </div>

                  <div className="bg-white p-2 rounded-lg border border-zinc-200/80">
                    <span className="text-zinc-400 font-semibold block text-[10px] uppercase">Assigned To</span>
                    <span className="font-bold text-zinc-800 flex items-center gap-1 mt-0.5">
                      <User className="w-3 h-3 text-sky-600" />
                      {item.assigned_to_name || item.assignedTo || 'Nishant Singh'}
                    </span>
                  </div>
                </div>

                {/* Remark Box */}
                <div className="bg-white p-2.5 rounded-lg border border-zinc-200 space-y-1">
                  <span className="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">
                    Issue Remark
                  </span>
                  <p className="text-xs font-medium text-zinc-900 leading-relaxed whitespace-pre-wrap">
                    {item.remark || item.description || 'No description provided.'}
                  </p>
                  {item.created_at && (
                    <span className="text-[10px] text-zinc-400 flex items-center space-x-1 pt-1">
                      <Clock className="w-3 h-3" />
                      <span>{item.created_at}</span>
                    </span>
                  )}
                </div>

                {/* Comments / Replies Section */}
                <div className="pt-1 border-t border-zinc-200 space-y-2">
                  <button
                    onClick={() => toggleExpandComments(item.id)}
                    className="flex items-center justify-between w-full text-xs font-bold text-zinc-700 hover:text-zinc-900 cursor-pointer"
                  >
                    <div className="flex items-center space-x-1.5">
                      <MessageSquare className="w-3.5 h-3.5 text-rose-600" />
                      <span>Comments / Replies ({commentsList.length})</span>
                    </div>
                    {isCommentsExpanded ? (
                      <ChevronUp className="w-4 h-4 text-zinc-400" />
                    ) : (
                      <ChevronDown className="w-4 h-4 text-zinc-400" />
                    )}
                  </button>

                  {isCommentsExpanded && (
                    <div className="space-y-2 pt-1 animate-in fade-in duration-150">
                      {/* Comments Feed */}
                      {commentsList.length > 0 ? (
                        <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
                          {commentsList.map((c, idx) => (
                            <div
                              key={c.id || idx}
                              className="bg-white p-2.5 rounded-xl border border-zinc-200/90 text-xs space-y-1"
                            >
                              <div className="flex items-center justify-between text-[11px]">
                                <span className="font-bold text-zinc-900 flex items-center space-x-1">
                                  <CornerDownRight className="w-3 h-3 text-rose-500" />
                                  <span>{c.user_name || 'Team Member'}</span>
                                </span>
                                <span className="text-[10px] text-zinc-400">{c.created_at}</span>
                              </div>
                              <p className="text-xs text-zinc-800 pl-4 font-normal leading-normal">
                                {c.comment}
                              </p>
                            </div>
                          ))}
                        </div>
                      ) : (
                        <p className="text-[11px] text-zinc-400 italic pl-1">
                          No replies posted yet. Add a reply below.
                        </p>
                      )}

                      {/* Reply Input */}
                      <div className="flex items-center space-x-2 pt-1">
                        <input
                          type="text"
                          placeholder="Type reply or status update..."
                          value={replyInputs[item.id] || ''}
                          onChange={(e) =>
                            setReplyInputs((prev) => ({ ...prev, [item.id]: e.target.value }))
                          }
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') handleSendReply(item.id);
                          }}
                          className="flex-1 bg-white border border-zinc-300 rounded-xl px-3 py-1.5 text-xs text-zinc-900 focus:outline-none focus:border-rose-500 shadow-2xs font-medium"
                        />
                        <button
                          onClick={() => handleSendReply(item.id)}
                          disabled={isSubmitting || !replyInputs[item.id]?.trim()}
                          className="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white font-bold text-xs rounded-xl shadow-xs flex items-center space-x-1 transition-colors cursor-pointer disabled:opacity-40"
                        >
                          {isSubmitting ? (
                            <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                          ) : (
                            <>
                              <Send className="w-3.5 h-3.5" />
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
    </div>
  );
};
