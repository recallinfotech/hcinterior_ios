import React, { useState, useEffect } from 'react';
import { ClientProject } from '../types';
import {
  PauseCircle,
  Building,
  RotateCcw,
} from 'lucide-react';
import { fetchClientDetail, ApiClientDetailData } from '../services/clientApi';

interface ProjectWorkflowTrackerProps {
  client: ClientProject;
  authToken?: string;
  onSelectSection?: (key: string) => void;
}

const DEFAULT_PHASE_DETAILS = [
  {
    phaseNum: 1,
    title: 'Phase 1',
    status: 'In Progress',
    items: [
      { name: 'Civil Drawing', done: false },
      { name: 'Design Finalization', done: false },
      { name: 'Material Selection', done: false },
      { name: '3D', done: true },
      { name: 'Validation', done: false },
      { name: 'Wall Electrical', done: false },
      { name: 'Production File', done: false },
    ],
  },
  {
    phaseNum: 2,
    title: 'Phase 2',
    status: 'Pending',
    items: [
      { name: 'Site Start', done: false },
      { name: 'Validation', done: false },
      { name: 'Ready for Dispatch', done: true },
      { name: 'KT Request', done: true },
    ],
  },
  {
    phaseNum: 3,
    title: 'Phase 3',
    status: 'Pending',
    items: [
      { name: 'BOM', done: false },
      { name: 'Dispatch', done: false },
    ],
  },
  {
    phaseNum: 4,
    title: 'Phase 4',
    status: 'Pending',
    items: [
      { name: 'Dispatch', done: false },
      { name: 'Handover', done: false },
    ],
  },
];

export const ProjectWorkflowTracker: React.FC<ProjectWorkflowTrackerProps> = ({ client, authToken, onSelectSection }) => {
  const [activeTabPhase, setActiveTabPhase] = useState<number>(1);
  const [hoveredPhaseNum, setHoveredPhaseNum] = useState<number | null>(null);
  const [isHold, setIsHold] = useState<boolean>(false);

  const [apiDetail, setApiDetail] = useState<ApiClientDetailData | null>(null);
  const [loadingDetail, setLoadingDetail] = useState<boolean>(false);

  useEffect(() => {
    const numericId = client.clientIdNum || parseInt(client.id.replace(/\D/g, ''), 10) || 529;
    if (authToken) {
      setLoadingDetail(true);
      fetchClientDetail(authToken, numericId)
        .then((res) => {
          if (res && res.data) {
            setApiDetail(res.data);
            if (typeof res.data.client_hold !== 'undefined') {
              setIsHold(res.data.client_hold === 1);
            }
          }
        })
        .catch((err) => {
          console.warn('Could not fetch client detail from API:', err);
        })
        .finally(() => {
          setLoadingDetail(false);
        });
    }
  }, [client.id, client.clientIdNum, authToken]);

  const refreshDetail = () => {
    const numericId = client.clientIdNum || parseInt(client.id.replace(/\D/g, ''), 10) || 529;
    if (authToken) {
      setLoadingDetail(true);
      fetchClientDetail(authToken, numericId)
        .then((res) => {
          if (res && res.data) {
            setApiDetail(res.data);
            if (typeof res.data.client_hold !== 'undefined') {
              setIsHold(res.data.client_hold === 1);
            }
          }
        })
        .finally(() => setLoadingDetail(false));
    }
  };

  const phaseDetails = apiDetail?.workflow?.phases
    ? apiDetail.workflow.phases.map((p) => ({
        phaseNum: p.phase_number,
        title: p.title || `Phase ${p.phase_number}`,
        status: p.status || 'Pending',
        items: p.items ? p.items.map((i) => ({ name: i.name, done: i.status?.toLowerCase() === 'done' })) : [],
      }))
    : DEFAULT_PHASE_DETAILS;

  const displayedPhaseNum = hoveredPhaseNum !== null ? hoveredPhaseNum : activeTabPhase;
  const currentPhaseIndex = Math.max(0, Math.min(displayedPhaseNum - 1, phaseDetails.length - 1));
  const activePhaseData = phaseDetails[currentPhaseIndex] || phaseDetails[0];

  const clientSrId = apiDetail?.client_sr_id || client.id;
  const clientName = apiDetail?.name || client.name;
  const progressPercentage = apiDetail?.workflow?.progress_percentage ?? client.overallProgress ?? 23;
  const activePhaseTitle = apiDetail?.workflow?.current_active_phase
    ? `Phase ${apiDetail.workflow.current_active_phase}`
    : client.phase || 'Phase 1';

  return (
    <div className="p-3.5 space-y-3.5 text-slate-800">
      {/* Top Banner Bar */}
      <div className="bg-white p-3 rounded-xl border border-slate-200/90 shadow-2xs flex items-center justify-between">
        <div className="truncate pr-2 flex items-center space-x-2">
          <div>
            <span className="text-xs font-bold text-slate-800 truncate block">
              {clientSrId} || {clientName} - Details
            </span>
            <p className="text-[10px] text-slate-400">Project Management Workflow Suite</p>
          </div>
          {loadingDetail && (
            <span className="inline-block w-3 h-3 border-2 border-orange-500/30 border-t-orange-500 rounded-full animate-spin shrink-0" />
          )}
        </div>

        <div className="flex items-center space-x-1.5 shrink-0">
          <button
            onClick={refreshDetail}
            disabled={loadingDetail}
            title="Reload API Details"
            className="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer transition-colors"
          >
            <RotateCcw className={`w-3.5 h-3.5 ${loadingDetail ? 'animate-spin text-orange-500' : ''}`} />
          </button>

          <button
            onClick={() => setIsHold(!isHold)}
            className={`px-2.5 py-1 rounded text-[10px] font-bold flex items-center space-x-1 cursor-pointer transition-colors ${
              isHold
                ? 'bg-rose-500 text-white'
                : 'bg-orange-500 hover:bg-orange-600 text-white'
            }`}
          >
            <PauseCircle className="w-3 h-3" />
            <span>{isHold ? 'On Hold' : 'Hold'}</span>
          </button>

          <span className="px-2 py-1 bg-slate-100 text-slate-700 rounded text-[10px] font-semibold border border-slate-200">
            Converted Form
          </span>
        </div>
      </div>

      {/* Detail Pages Sub-Tabs Bar */}
      {onSelectSection && (
        <div className="bg-zinc-950 p-2 rounded-xl border border-zinc-800 shadow-md">
          <div className="text-[10px] uppercase font-bold text-zinc-400 px-1 pb-1 tracking-wider flex items-center justify-between">
            <span>Client Detail Tabs ({client.id})</span>
            <span className="text-amber-400 font-semibold lowercase">client data only</span>
          </div>
          <div className="flex items-center space-x-1.5 overflow-x-auto pb-0.5 no-scrollbar">
            {[
              { key: 'clientEscalation', label: 'Escalation' },
              { key: 'bom', label: 'BOM' },
              { key: 'dispatch', label: 'Dispatch' },
              { key: 'looseFurniture', label: 'Loose Furniture' },
              { key: 'qcDesign', label: 'QC Design' },
              { key: 'finalValidation', label: 'Final Production Drawing' },
              { key: 'onSitePurchaseRequest', label: 'On Site Purchase Request' },
              { key: 'executionTimeline', label: 'Execution Timeline' },
              { key: 'handover', label: 'Handover' },
              { key: 'pushNotification', label: 'Push Notifications' },
            ].map((tab) => (
              <button
                key={tab.key}
                onClick={() => onSelectSection(tab.key)}
                className={`px-2.5 py-1 rounded-lg text-xs font-bold whitespace-nowrap transition-all cursor-pointer ${
                  tab.key === 'workflow'
                    ? 'bg-amber-400 text-zinc-950 shadow-xs font-extrabold'
                    : 'bg-zinc-900 hover:bg-zinc-800 text-zinc-300 border border-zinc-800'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Main Workflow Card */}
      <div className="bg-white rounded-2xl p-4 border border-zinc-200 shadow-sm relative">
        <div className="flex items-start justify-between pb-3 border-b border-zinc-100">
          <div>
            <h2 className="text-base font-extrabold text-zinc-900 tracking-tight">Project Workflow Tracker</h2>
            <p className="text-xs text-amber-600 font-bold mt-0.5">
              Current Active Phase : <span className="font-extrabold">{activePhaseTitle}</span>
            </p>
          </div>

          {/* Radial Gauge Badge */}
          <div className="relative w-14 h-14 flex items-center justify-center shrink-0">
            <svg className="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
              <path
                className="text-zinc-100"
                strokeWidth="3.5"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                className="text-amber-400"
                strokeDasharray={`${progressPercentage}, 100`}
                strokeWidth="3.5"
                strokeLinecap="round"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <span className="absolute text-xs font-extrabold text-zinc-950">{progressPercentage}%</span>
          </div>
        </div>

        {/* Phase Pills Stepper */}
        <div className="grid grid-cols-2 gap-3 mt-4 relative">
          {phaseDetails.map((p) => {
            const isActive = activeTabPhase === p.phaseNum;
            const isHovered = hoveredPhaseNum === p.phaseNum;

            return (
              <div
                key={p.phaseNum}
                onMouseEnter={() => setHoveredPhaseNum(p.phaseNum)}
                onMouseLeave={() => setHoveredPhaseNum(null)}
                onClick={() => setActiveTabPhase(p.phaseNum)}
                className={`p-3.5 rounded-2xl border transition-all cursor-pointer relative ${
                  isActive || isHovered
                    ? 'border-amber-400 bg-amber-400/10 shadow-xs ring-2 ring-amber-400/30'
                    : 'border-zinc-200 bg-zinc-50 hover:bg-zinc-100'
                }`}
              >
                <div className="flex items-center justify-between">
                  <span className="w-7 h-7 rounded-full bg-zinc-950 text-amber-400 font-extrabold text-xs flex items-center justify-center">
                    {p.phaseNum}
                  </span>
                  <span
                    className={`text-[10px] font-bold px-2 py-0.5 rounded-md ${
                      p.status === 'In Progress'
                        ? 'bg-amber-400/20 text-amber-800 border border-amber-400/40'
                        : 'bg-zinc-200 text-zinc-600'
                    }`}
                  >
                    {p.status}
                  </span>
                </div>
                <h4 className="text-sm font-bold text-zinc-900 mt-2.5">{p.title}</h4>

                {/* Floating Tooltip Card on Hover */}
                {isHovered && (
                  <div className="absolute top-0 left-0 w-full z-30 bg-zinc-950 text-zinc-100 rounded-2xl p-4 shadow-2xl border-2 border-amber-400 animate-in zoom-in-95 duration-150">
                    <h4 className="text-sm font-bold text-white mb-2">{p.title}</h4>
                    <div className="border-b border-zinc-800 mb-3" />
                    <div className="space-y-2 text-xs">
                      {p.items.map((item, idx) => (
                        <div key={idx} className="flex items-center space-x-2.5">
                          {item.done ? (
                            <span className="text-amber-400 font-extrabold text-sm shrink-0">✓</span>
                          ) : (
                            <span className="text-zinc-500 font-extrabold text-sm shrink-0">✕</span>
                          )}
                          <span className="font-semibold text-zinc-100 leading-snug">{item.name}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
        </div>

        {/* Persistent Dark Process Checklist Box */}
        <div className="mt-4 bg-zinc-950 text-zinc-100 p-4 rounded-2xl border border-zinc-800 shadow-xl relative overflow-hidden">
          <div className="flex items-center justify-between pb-2.5 border-b border-zinc-800">
            <h4 className="text-sm font-bold text-white">
              Phase {activePhaseData.phaseNum}
            </h4>
            <span className="text-[10px] font-semibold text-amber-400 bg-amber-400/10 px-2 py-0.5 rounded border border-amber-400/20">
              {hoveredPhaseNum ? 'Hovering Phase' : 'Active Phase'}
            </span>
          </div>

          <div className="mt-3 space-y-2.5 text-xs">
            {activePhaseData.items.map((item, idx) => (
              <div key={idx} className="flex items-center space-x-2.5">
                {item.done ? (
                  <span className="text-amber-400 font-extrabold text-sm shrink-0">✓</span>
                ) : (
                  <span className="text-zinc-500 font-extrabold text-sm shrink-0">✕</span>
                )}
                <span className="font-semibold text-zinc-100">{item.name}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Contact Details Card */}
      <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3">
        <h3 className="text-xs font-bold text-slate-500 tracking-tight pb-2 border-b border-slate-100">
          Contact Details
        </h3>

        <div className="space-y-2.5 text-xs text-slate-700">
          <div className="grid grid-cols-3 gap-2">
            <span className="text-slate-400 font-medium">Client ID</span>
            <span className="font-semibold text-slate-900 col-span-2">{clientSrId}</span>
          </div>

          <div className="grid grid-cols-3 gap-2">
            <span className="text-slate-400 font-medium">Name</span>
            <span className="font-semibold text-slate-900 col-span-2">{clientName}</span>
          </div>

          {/* Permanent Address Subsection */}
          <div className="border-t border-slate-100 pt-3 mt-3 space-y-2">
            <h4 className="text-xs font-bold text-slate-800">Permanent Address</h4>

            <div className="grid grid-cols-3 gap-2 pt-1">
              <span className="text-slate-400 font-medium">Address</span>
              <span className="font-medium text-slate-800 col-span-2">{apiDetail?.address || client.address || 'test permanent address'}</span>
            </div>

            <div className="grid grid-cols-3 gap-2">
              <span className="text-slate-400 font-medium">City</span>
              <span className="font-medium text-slate-800 col-span-2">{apiDetail?.city || client.city || 'testjddvnjksd'}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Site Address */}
      <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-2.5">
        <h3 className="text-xs font-extrabold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center space-x-1.5">
          <Building className="w-4 h-4 text-amber-500" />
          <span>Site Address</span>
        </h3>

        <div className="space-y-2 text-xs text-slate-700">
          <div className="grid grid-cols-3 gap-1 py-1 border-b border-slate-50">
            <span className="text-slate-400 font-medium col-span-1">Site Address</span>
            <span className="font-medium text-slate-800 col-span-2">{apiDetail?.site_address || 'test site address'}</span>
          </div>

          <div className="grid grid-cols-3 gap-1 py-1 border-b border-slate-50">
            <span className="text-slate-400 font-medium col-span-1">Site City</span>
            <span className="font-medium text-slate-800 col-span-2">{apiDetail?.site_city || 'jhsdcjhwsdjchgb'}</span>
          </div>

          <div className="grid grid-cols-3 gap-1 py-1">
            <span className="text-slate-400 font-medium col-span-1">Zip Code</span>
            <span className="font-mono font-semibold text-slate-900 col-span-2">{apiDetail?.site_zipcode || '1000000'}</span>
          </div>
        </div>
      </div>
    </div>
  );
};
