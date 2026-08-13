import React from 'react';
import { ClientProject } from '../types';
import { FileText, ChevronRight, CheckCircle2, Clock, Send, Users, CalendarCheck, ShieldAlert } from 'lucide-react';

interface ClientListCardProps {
  client: ClientProject;
  onSelectClient: (client: ClientProject) => void;
  onOpenDetails: (client: ClientProject) => void;
  onOpenWorkflow: (client: ClientProject) => void;
  onOpenBoq?: (client: ClientProject) => void;
  onOpenFinalValidation?: (client: ClientProject) => void;
}

export const ClientListCard: React.FC<ClientListCardProps> = ({
  client,
  onSelectClient,
  onOpenDetails,
  onOpenWorkflow,
  onOpenBoq,
  onOpenFinalValidation,
}) => {
  if (!client) return null;

  const ktStatus = client.ktRequest?.status || 'Accepted';
  const ktDate = client.ktRequest?.date || '24 July 2026';
  const valDate = client.validationDate || '25 July 2026';
  const designerName = client.assignedTeam?.designer || '';
  const pmName = client.assignedTeam?.projectManager || '';

  return (
    <div className="bg-white rounded-xl border border-zinc-200 shadow-xs hover:shadow-md hover:border-amber-400/50 transition-all p-3.5 mb-3 text-zinc-800 relative overflow-hidden group">
      {/* Top Header Row */}
      <div className="flex items-start justify-between pb-2 mb-2 border-b border-zinc-100">
        <div>
          <div className="flex items-center space-x-2">
            <button
              onClick={() => onSelectClient(client)}
              className="text-xs font-bold text-zinc-900 hover:text-amber-600 transition-colors flex items-center space-x-1 cursor-pointer"
            >
              <span className="bg-zinc-900 text-amber-400 px-2 py-0.5 rounded font-mono text-[11px] font-bold">{client.id}</span>
            </button>
            <span className="text-[10px] font-extrabold px-2 py-0.5 rounded bg-amber-400/15 text-amber-700 border border-amber-400/30 uppercase">
              {client.phase}
            </span>
          </div>
          <h3
            onClick={() => onSelectClient(client)}
            className="text-sm font-bold text-zinc-900 mt-1 cursor-pointer hover:text-amber-600 transition-colors"
          >
            {client.name}
          </h3>
          <p className="text-[10px] text-zinc-400 font-mono mt-0.5">{client.date}</p>
        </div>

        {/* KT Request Badge */}
        <div className="text-right">
          {ktStatus === 'Accepted' && (
            <div className="bg-emerald-50 border border-emerald-200 text-emerald-700 px-2.5 py-1 rounded-md text-[10px] font-bold inline-flex items-center space-x-1 shadow-2xs">
              <CheckCircle2 className="w-3 h-3 text-emerald-600" />
              <span>KT: Accepted</span>
            </div>
          )}
          {ktStatus === 'Pending' && (
            <div className="bg-amber-50 border border-amber-200 text-amber-800 px-2.5 py-1 rounded-md text-[10px] font-bold inline-flex items-center space-x-1 shadow-2xs">
              <Clock className="w-3 h-3 text-amber-600" />
              <span>KT: Pending</span>
            </div>
          )}
          {ktStatus === 'NA' && (
            <div className="bg-zinc-100 border border-zinc-200 text-zinc-500 px-2 py-0.5 rounded text-[10px] font-semibold inline-block">
              KT: NA
            </div>
          )}
        </div>
      </div>

      {/* Middle KT Request & Validation Date Row */}
      <div className="grid grid-cols-2 gap-2 text-xs py-2 bg-zinc-50 rounded-xl px-3 my-2 border border-zinc-200/80 shadow-2xs">
        <div>
          <span className="text-[10px] text-zinc-400 font-bold block uppercase tracking-wider">
            KT Request
          </span>
          <div className="flex items-center space-x-1 mt-0.5">
            <span className={`text-xs font-bold ${ktStatus === 'Accepted' ? 'text-emerald-700' : 'text-zinc-700'}`}>
              {ktStatus}
            </span>
            <span className="text-[10px] text-zinc-500 font-medium">({ktDate})</span>
          </div>
        </div>

        <div className="border-l border-zinc-200 pl-2.5">
          <span className="text-[10px] text-zinc-400 font-bold block uppercase tracking-wider">
            Validation Date
          </span>
          <span className="text-xs font-bold text-zinc-800 block mt-0.5 flex items-center space-x-1">
            <CalendarCheck className="w-3.5 h-3.5 text-amber-500 inline" />
            <span>{valDate}</span>
          </span>
        </div>
      </div>

      {/* Team Row */}
      <div className="text-[11px] text-zinc-600 space-y-1 my-2">
        <div className="flex items-start justify-between">
          <span className="text-zinc-400 font-medium">Assigned Team:</span>
          <span className="font-medium text-zinc-700 text-right truncate max-w-[180px]">
            {designerName
              ? `Designer: ${designerName}`
              : pmName
              ? `PM: ${pmName}`
              : '-'}
          </span>
        </div>
      </div>

      {/* Action Buttons Row */}
      <div className="pt-2 border-t border-zinc-100 flex items-center justify-between">
        <div className="flex items-center space-x-1.5">
          {onOpenBoq && (
            <button
              onClick={(e) => {
                e.stopPropagation();
                onOpenBoq(client);
              }}
              className="p-1.5 rounded-md bg-amber-400 text-zinc-950 hover:bg-amber-500 font-extrabold text-xs flex items-center space-x-1 cursor-pointer shadow-2xs transition-colors"
              title="View BOQ List & PDFs"
            >
              <FileText className="w-3.5 h-3.5" />
              <span className="text-[10px] uppercase font-black tracking-wider">BOQ</span>
            </button>
          )}

          <button
            onClick={(e) => {
              e.stopPropagation();
              if (onOpenFinalValidation) {
                onOpenFinalValidation(client);
              } else {
                onSelectClient(client);
              }
            }}
            className="p-1.5 rounded-md bg-purple-50 hover:bg-purple-100 text-purple-950 border border-purple-200/80 text-xs font-bold flex items-center space-x-1 cursor-pointer shadow-2xs transition-colors"
            title="View Final Production Drawing"
          >
            <FileText className="w-3.5 h-3.5 text-purple-600" />
            <span className="text-[10px] font-black uppercase tracking-wider">Final Drawing</span>
          </button>
        </div>

        <button
          onClick={() => onSelectClient(client)}
          className="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-zinc-950 rounded-lg text-xs font-extrabold flex items-center space-x-1 cursor-pointer shadow-xs transition-colors"
        >
          <span>View Details</span>
          <ChevronRight className="w-4 h-4" />
        </button>
      </div>

    </div>
  );
};
