import React, { useState, useEffect } from 'react';
import {
  Calendar,
  Clock,
  Layers,
  User,
  RotateCcw,
  Save,
  CheckCircle2,
  AlertCircle,
  Search,
  ShieldCheck,
  Check,
  SlidersHorizontal,
} from 'lucide-react';
import { ClientProject, ApiExecutionTask, ApiExecutionTimelineResponse } from '../../types';
import { fetchExecutionTimeline, updateExecutionTimeline } from '../../services/clientApi';

interface ExecutionTimelineSectionProps {
  client?: ClientProject;
  showAllClients?: boolean;
  token?: string;
}

// Fallback initial tasks matching the CRM structure when API returns empty or offline
const FALLBACK_TASKS: ApiExecutionTask[] = [
  { id: 1711, client_id: 531, task_name: 'KT meetings done', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1712, client_id: 531, task_name: 'documentation', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1713, client_id: 531, task_name: 'call to cx', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1714, client_id: 531, task_name: 'cx end documenattion done', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1715, client_id: 531, task_name: 'floor protection order', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1716, client_id: 531, task_name: 'electrical material order', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1717, client_id: 531, task_name: 'plumbing material order', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1718, client_id: 531, task_name: 'Demolition', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1719, client_id: 531, task_name: 'debris removal', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1720, client_id: 531, task_name: 'civil material order', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1721, client_id: 531, task_name: 'brick work + few repairing', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1722, client_id: 531, task_name: 'plaster', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1723, client_id: 531, task_name: 'falceiling gypsum with ceiling electrical', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1724, client_id: 531, task_name: 'Kitchen ceiling (punning)', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1725, client_id: 531, task_name: 'wall punning', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1726, client_id: 531, task_name: 'internal plumbing of kitchen', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1727, client_id: 531, task_name: 'internal plumbing of washrooms 2nos', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1728, client_id: 531, task_name: 'water proofing other areas on plater', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1729, client_id: 531, task_name: 'slab casting', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1730, client_id: 531, task_name: 'PCC of bathroom', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1731, client_id: 531, task_name: 'Tiling bathroom', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1732, client_id: 531, task_name: 'tiling of all rooms and living and kitchen', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1733, client_id: 531, task_name: 'SS gate order', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1734, client_id: 531, task_name: 'Repairing of chiseling and dhar core', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1735, client_id: 531, task_name: 'validation', start_date: null, end_date: null, duration: 0, is_validation: true },
  { id: 1736, client_id: 531, task_name: 'Wall electrical', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1737, client_id: 531, task_name: 'Aluminium measurement', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1738, client_id: 531, task_name: 'ceiling light installation', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1739, client_id: 531, task_name: 'Putty & primer base coat', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1740, client_id: 531, task_name: 'cleaning of all civil and debris', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1741, client_id: 531, task_name: 'dispatch', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1742, client_id: 531, task_name: 'carpenter align same day hardware check with BOM and hardware list', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1743, client_id: 531, task_name: 'mail to factory if wooden 1st slot and final slot have gap more hen 3 days', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1744, client_id: 531, task_name: 'kitchen counter top installation & dedo tile isnatllation', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1745, client_id: 531, task_name: 'handover', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1746, client_id: 531, task_name: 'deepcleaning', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1747, client_id: 531, task_name: 'make snag list', start_date: null, end_date: null, duration: 0, is_validation: false },
  { id: 1748, client_id: 531, task_name: 'closing with final touchup Final touchup', start_date: null, end_date: null, duration: 0, is_validation: false },
];

export const ExecutionTimelineSection: React.FC<ExecutionTimelineSectionProps> = ({
  client,
  showAllClients = false,
  token = '',
}) => {
  const [tasks, setTasks] = useState<ApiExecutionTask[]>(FALLBACK_TASKS);
  const [apiMetadata, setApiMetadata] = useState<ApiExecutionTimelineResponse['client'] | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [isSaving, setIsSaving] = useState<boolean>(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  // Search & Filter
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [filterMode, setFilterMode] = useState<'all' | 'validation' | 'dated'>('all');

  // Track modified tasks
  const [dirtyTaskIds, setDirtyTaskIds] = useState<Set<number>>(new Set());

  const activeClientId = client?.clientIdNum || (client?.id ? parseInt(client.id.replace(/\D/g, ''), 10) : 531);

  // Load Execution Timeline from API
  const loadTimeline = async () => {
    setIsLoading(true);
    setErrorMsg(null);
    setSuccessMsg(null);

    try {
      const res = await fetchExecutionTimeline(token, activeClientId);
      if (res && res.status && Array.isArray(res.data) && res.data.length > 0) {
        setTasks(res.data);
        if (res.client) {
          setApiMetadata(res.client);
        }
        setDirtyTaskIds(new Set());
      } else {
        console.warn('Execution timeline API returned empty/failed, using local structure:', res?.message);
      }
    } catch (err: any) {
      console.error('Failed to load execution timeline:', err);
      setErrorMsg('Could not sync with live CRM timeline API. Displaying local workspace state.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadTimeline();
  }, [activeClientId, token]);

  const handleTaskDateChange = (taskId: number, field: 'start_date' | 'end_date', value: string) => {
    setTasks((prev) =>
      prev.map((t) => {
        if (t.id === taskId) {
          const updated = { ...t, [field]: value || null };
          // Calculate duration if both dates exist
          if (updated.start_date && updated.end_date) {
            const d1 = new Date(updated.start_date);
            const d2 = new Date(updated.end_date);
            const diffTime = d2.getTime() - d1.getTime();
            updated.duration = diffTime >= 0 ? Math.ceil(diffTime / (1000 * 60 * 60 * 24)) : 0;
          }
          return updated;
        }
        return t;
      })
    );

    setDirtyTaskIds((prev) => {
      const next = new Set(prev);
      next.add(taskId);
      return next;
    });
  };

  // Batch Save changes back to API
  const handleSaveTimeline = async () => {
    if (dirtyTaskIds.size === 0) {
      setSuccessMsg('No changes to save.');
      setTimeout(() => setSuccessMsg(null), 3000);
      return;
    }

    setIsSaving(true);
    setErrorMsg(null);
    setSuccessMsg(null);

    // Prepare array of tasks that have start_date or end_date or are dirty
    const tasksToUpdate = tasks
      .filter((t) => dirtyTaskIds.has(t.id) || t.start_date || t.end_date)
      .map((t) => ({
        id: t.id,
        start_date: t.start_date || '',
        end_date: t.end_date || '',
      }));

    try {
      const res = await updateExecutionTimeline(token, activeClientId, tasksToUpdate);
      if (res && res.status) {
        setSuccessMsg(
          res.message || `Timeline updated successfully! (${res.total_updated || tasksToUpdate.length} tasks synced)`
        );
        setDirtyTaskIds(new Set());
        // Re-sync after 1.5s
        setTimeout(() => {
          loadTimeline();
        }, 1500);
      } else {
        setErrorMsg(res?.message || 'Failed to update execution timeline via CRM API.');
      }
    } catch (err: any) {
      console.error('Error saving execution timeline:', err);
      setErrorMsg('Failed to update timeline: ' + (err.message || 'Network issue'));
    } finally {
      setIsSaving(false);
    }
  };

  // Filter tasks
  const filteredTasks = tasks.filter((task) => {
    const matchesSearch = task.task_name.toLowerCase().includes(searchQuery.toLowerCase());
    if (!matchesSearch) return false;

    if (filterMode === 'validation') return task.is_validation;
    if (filterMode === 'dated') return Boolean(task.start_date || task.end_date);

    return true;
  });

  const calculateDays = (start: string | null, end: string | null) => {
    if (!start || !end) return 0;
    const d1 = new Date(start);
    const d2 = new Date(end);
    const diffTime = d2.getTime() - d1.getTime();
    if (isNaN(diffTime) || diffTime < 0) return 0;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  };

  const totalCalculatedDays = tasks.reduce(
    (acc, t) => acc + (t.duration || calculateDays(t.start_date, t.end_date)),
    0
  );

  return (
    <div className="bg-white rounded-2xl border border-zinc-200/90 shadow-sm p-4 space-y-4">
      {/* Client Scope Banner */}
      <div className="bg-zinc-900 text-white p-3.5 rounded-xl shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border border-zinc-800">
        <div className="flex items-center space-x-3">
          <div className="w-9 h-9 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/30 shrink-0">
            <User className="w-5 h-5" />
          </div>
          <div>
            <div className="flex items-center space-x-2">
              <h4 className="text-sm font-bold text-zinc-100">
                {apiMetadata?.client_name || client?.name || 'Client Execution Timeline'}
              </h4>
              <span className="px-2 py-0.5 bg-amber-500/20 text-amber-300 font-mono text-[11px] font-bold rounded border border-amber-500/30">
                SR ID: {apiMetadata?.client_sr_id || client?.id || 'HC101802'}
              </span>
            </div>
            <p className="text-[11px] text-zinc-400 mt-0.5">
              Client ID: <span className="text-amber-400 font-mono font-bold">{activeClientId}</span>
              {apiMetadata?.mobile && ` | Mobile: ${apiMetadata.mobile}`}
              {apiMetadata?.email && ` | Email: ${apiMetadata.email}`}
            </p>
          </div>
        </div>

        <div className="flex items-center space-x-2 w-full sm:w-auto justify-end">
          <button
            onClick={loadTimeline}
            disabled={isLoading}
            className="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-200 rounded-lg text-xs font-semibold flex items-center space-x-1.5 transition-colors cursor-pointer border border-zinc-700 disabled:opacity-50"
            title="Reload timeline from API"
          >
            <RotateCcw className={`w-3.5 h-3.5 ${isLoading ? 'animate-spin text-amber-400' : ''}`} />
            <span>Sync API</span>
          </button>
          <button
            onClick={handleSaveTimeline}
            disabled={isSaving || dirtyTaskIds.size === 0}
            className="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-zinc-950 rounded-lg text-xs font-bold flex items-center space-x-1.5 transition-colors cursor-pointer disabled:opacity-50 shadow-xs"
          >
            <Save className={`w-3.5 h-3.5 ${isSaving ? 'animate-spin' : ''}`} />
            <span>{isSaving ? 'Updating...' : `Update Timeline ${dirtyTaskIds.size > 0 ? `(${dirtyTaskIds.size})` : ''}`}</span>
          </button>
        </div>
      </div>

      {/* Header & Stats Bar */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-3 border-b-2 border-amber-500 gap-2">
        <div className="flex items-center space-x-2">
          <Layers className="w-5 h-5 text-amber-600" />
          <div>
            <h3 className="text-base font-bold text-zinc-900 tracking-tight">Execution Timeline</h3>
            <p className="text-[11px] text-zinc-500 font-medium">Manage and schedule site execution tasks & milestones</p>
          </div>
        </div>

        <div className="flex items-center space-x-2">
          <span className="text-xs font-bold px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200 rounded-lg flex items-center space-x-1">
            <Clock className="w-3.5 h-3.5 text-amber-600" />
            <span>Total Duration: {totalCalculatedDays} Days</span>
          </span>
          <span className="text-xs font-semibold px-2.5 py-1 bg-zinc-100 text-zinc-700 border border-zinc-200 rounded-lg">
            Total Tasks: {tasks.length}
          </span>
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

      {/* Search and Filters */}
      <div className="flex flex-col sm:flex-row items-center justify-between gap-2.5 pt-1">
        <div className="relative w-full sm:w-72">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400" />
          <input
            type="text"
            placeholder="Search execution task..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-3 py-1.5 bg-zinc-50 border border-zinc-200 rounded-xl text-xs text-zinc-800 focus:outline-none focus:border-amber-500 focus:bg-white transition-all"
          />
        </div>

        <div className="flex items-center space-x-1.5 w-full sm:w-auto justify-start sm:justify-end overflow-x-auto pb-1 sm:pb-0">
          <button
            onClick={() => setFilterMode('all')}
            className={`px-3 py-1 rounded-lg text-xs font-semibold cursor-pointer transition-colors ${
              filterMode === 'all'
                ? 'bg-zinc-900 text-white'
                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
            }`}
          >
            All ({tasks.length})
          </button>
          <button
            onClick={() => setFilterMode('validation')}
            className={`px-3 py-1 rounded-lg text-xs font-semibold cursor-pointer transition-colors flex items-center space-x-1 ${
              filterMode === 'validation'
                ? 'bg-amber-500 text-zinc-950 font-bold'
                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
            }`}
          >
            <ShieldCheck className="w-3.5 h-3.5" />
            <span>Validation ({tasks.filter((t) => t.is_validation).length})</span>
          </button>
          <button
            onClick={() => setFilterMode('dated')}
            className={`px-3 py-1 rounded-lg text-xs font-semibold cursor-pointer transition-colors ${
              filterMode === 'dated'
                ? 'bg-zinc-900 text-white'
                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200'
            }`}
          >
            Scheduled ({tasks.filter((t) => t.start_date || t.end_date).length})
          </button>
        </div>
      </div>

      {/* Task Cards List */}
      {filteredTasks.length === 0 ? (
        <div className="text-center py-10 bg-zinc-50 rounded-2xl border border-dashed border-zinc-300 space-y-2">
          <SlidersHorizontal className="w-8 h-8 mx-auto text-zinc-400" />
          <p className="text-xs text-zinc-500 font-semibold">No execution tasks match your search or filter.</p>
        </div>
      ) : (
        <div className="space-y-2.5">
          {filteredTasks.map((task, index) => {
            const isDirty = dirtyTaskIds.has(task.id);
            const days = task.duration || calculateDays(task.start_date, task.end_date);

            return (
              <div
                key={task.id || index}
                className={`rounded-xl p-3 border transition-all ${
                  task.is_validation
                    ? 'bg-amber-50/60 border-amber-300/80 hover:border-amber-400'
                    : isDirty
                    ? 'bg-sky-50/60 border-sky-300 hover:border-sky-400'
                    : 'bg-zinc-50/70 hover:bg-zinc-50 border-zinc-200'
                }`}
              >
                {/* Header: Task Index, Name, Validation Flag & Days Badge */}
                <div className="flex items-center justify-between gap-2 mb-2">
                  <div className="flex items-center space-x-2.5 min-w-0">
                    <span className={`w-6 h-6 rounded-full font-bold text-xs flex items-center justify-center shrink-0 ${
                      task.is_validation ? 'bg-amber-600 text-white' : 'bg-zinc-900 text-white'
                    }`}>
                      {index + 1}
                    </span>
                    <h4 className="text-xs font-bold text-zinc-900 capitalize truncate">
                      {task.task_name}
                    </h4>

                    {task.is_validation && (
                      <span className="px-2 py-0.5 bg-amber-200 text-amber-950 font-black text-[10px] rounded uppercase tracking-wider flex items-center space-x-1 shrink-0">
                        <ShieldCheck className="w-3 h-3 text-amber-800" />
                        <span>Validation Task</span>
                      </span>
                    )}

                    {isDirty && (
                      <span className="px-1.5 py-0.5 bg-sky-200 text-sky-900 font-bold text-[9px] rounded uppercase">
                        Unsaved
                      </span>
                    )}
                  </div>

                  <div className="flex items-center space-x-1.5 shrink-0">
                    <span className={`px-2 py-0.5 rounded-md font-black text-[11px] border ${
                      days > 0
                        ? 'bg-amber-100 text-amber-900 border-amber-200'
                        : 'bg-zinc-200/70 text-zinc-600 border-zinc-300'
                    }`}>
                      {days} {days === 1 ? 'Day' : 'Days'}
                    </span>
                  </div>
                </div>

                {/* Input Date Grid */}
                <div className="grid grid-cols-2 gap-2 text-xs">
                  <div>
                    <label className="block text-[10px] font-bold text-zinc-500 uppercase tracking-wider mb-1 flex items-center space-x-1">
                      <Calendar className="w-3 h-3 text-sky-600" />
                      <span>Start Date</span>
                    </label>
                    <input
                      type="date"
                      value={task.start_date || ''}
                      onChange={(e) => handleTaskDateChange(task.id, 'start_date', e.target.value)}
                      className="w-full bg-white border border-zinc-300 rounded-lg px-2.5 py-1.5 text-xs text-zinc-800 font-medium focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 shadow-2xs"
                    />
                  </div>

                  <div>
                    <label className="block text-[10px] font-bold text-zinc-500 uppercase tracking-wider mb-1 flex items-center space-x-1">
                      <Clock className="w-3 h-3 text-rose-500" />
                      <span>End Date</span>
                    </label>
                    <input
                      type="date"
                      value={task.end_date || ''}
                      onChange={(e) => handleTaskDateChange(task.id, 'end_date', e.target.value)}
                      className="w-full bg-white border border-zinc-300 rounded-lg px-2.5 py-1.5 text-xs text-zinc-800 font-medium focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 shadow-2xs"
                    />
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Footer Save CTA */}
      <div className="pt-2 border-t border-zinc-200 flex items-center justify-between">
        <span className="text-xs text-zinc-500 font-medium">
          {dirtyTaskIds.size > 0 ? `${dirtyTaskIds.size} task(s) modified.` : 'All timeline tasks up to date.'}
        </span>

        <button
          onClick={handleSaveTimeline}
          disabled={isSaving || dirtyTaskIds.size === 0}
          className="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold text-xs rounded-xl flex items-center space-x-1.5 transition-colors shadow-xs disabled:opacity-50 cursor-pointer"
        >
          <Save className="w-4 h-4" />
          <span>Save Execution Timeline</span>
        </button>
      </div>
    </div>
  );
};
