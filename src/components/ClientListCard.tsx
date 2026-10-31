import React from 'react';
import { ClientProject } from '../types';
import { ChevronRight, Calendar, Users, FileText } from 'lucide-react';

interface ClientListCardProps {
  client: ClientProject;
  onSelectClient: (client: ClientProject) => void;
  onOpenDetails: (client: ClientProject) => void;
  onOpenWorkflow: (client: ClientProject) => void;
  onOpenBoq?: (client: ClientProject) => void;
  onOpenFinalValidation?: (client: ClientProject) => void;
  onOpenOnSitePurchaseWithItem?: (client: ClientProject) => void;
}

export const ClientListCard: React.FC<ClientListCardProps> = ({
  client,
  onSelectClient,
  onOpenDetails,
  onOpenWorkflow,
  onOpenBoq,
  onOpenFinalValidation,
  onOpenOnSitePurchaseWithItem,
}) => {
  if (!client) return null;

  const phoneNum = client.mobile || '9874563210';
  const displayDate = client.date ? client.date.split(' ')[0] : 'Today';
  const initialLetter = client.name ? client.name.charAt(0).toUpperCase() : 'C';

  return (
    <div className="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-md hover:border-amber-400/40 transition-all p-4 mb-3.5 text-slate-800 relative overflow-hidden group">
      {/* Top Header Row: Squircle Avatar + Title/Phone + Status Pill */}
      <div className="flex items-start justify-between gap-3">
        {/* Left Squircle Avatar */}
        <div
          onClick={() => onSelectClient(client)}
          className="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-500 text-slate-950 font-black text-xl flex items-center justify-center shrink-0 shadow-sm shadow-orange-500/25 cursor-pointer"
        >
          {initialLetter}
        </div>

        {/* Middle Info */}
        <div className="flex-1 min-w-0" onClick={() => onSelectClient(client)}>
          <h3 className="text-sm font-extrabold text-slate-900 truncate hover:text-orange-500 transition-colors cursor-pointer tracking-tight">
            {client.name}
          </h3>
          <div className="flex items-center space-x-1.5 text-xs text-slate-500 mt-0.5">
            <span className="font-semibold text-slate-700">{phoneNum}</span>
            <span className="text-slate-300">•</span>
            <span className="text-slate-400 text-[11px] flex items-center space-x-1">
              <Calendar className="w-3 h-3 text-slate-400 inline" />
              <span>{displayDate}</span>
            </span>
          </div>
        </div>

        {/* Right Status Pill */}
        <div className="shrink-0">
          <span className="inline-flex items-center space-x-1 text-[10px] font-extrabold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200/80 uppercase tracking-wider">
            <span className="w-1.5 h-1.5 rounded-full bg-slate-400" />
            <span>{client.phase || 'FRESH'}</span>
          </span>
        </div>
      </div>

      {/* Middle Banner: Team Assign Info */}
      <div className="bg-[#fff9eb] border border-amber-200/60 rounded-2xl p-2.5 flex items-center space-x-2.5 my-3">
        <div className="w-7 h-7 rounded-xl bg-amber-100/90 text-amber-600 flex items-center justify-center shrink-0">
          <Users className="w-3.5 h-3.5 text-amber-600" />
        </div>
        <div className="min-w-0 flex-1">
          <div className="text-[10px] font-black text-amber-600 tracking-wider uppercase">
            Team Assign
          </div>
          <p className="text-[11px] font-semibold text-slate-700 truncate mt-0.5">
            {client.assignedTeam?.designer ? `Designer: ${client.assignedTeam.designer}` : ''}
            {client.assignedTeam?.projectManager ? ` • PM: ${client.assignedTeam.projectManager}` : ''}
            {!client.assignedTeam?.designer && !client.assignedTeam?.projectManager ? `ID: ${client.id} • Validation: ${client.validationDate || 'Active'}` : ''}
          </p>
        </div>
      </div>

      {/* Bottom Action Footer: BOQ, Final Design & Details */}
      <div className="pt-2.5 border-t border-slate-100/80 flex items-center justify-between">
        <div className="flex items-center space-x-2">
          {/* Quick BOQ button */}
          {onOpenBoq && (
            <button
              onClick={(e) => {
                e.stopPropagation();
                onOpenBoq(client);
              }}
              className="px-2.5 py-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-slate-800 text-xs font-bold flex items-center space-x-1.5 cursor-pointer transition-colors shadow-2xs"
              title="View BOQ List & PDFs"
            >
              <FileText className="w-3.5 h-3.5 text-amber-600" />
              <span>BOQ</span>
            </button>
          )}

          {/* Final Design / Final Production File Button */}
          <button
            onClick={(e) => {
              e.stopPropagation();
              if (onOpenFinalValidation) {
                onOpenFinalValidation(client);
              } else {
                onSelectClient(client);
              }
            }}
            className="px-2.5 py-1.5 rounded-xl border border-purple-300 bg-purple-50 hover:bg-purple-100 text-purple-900 text-xs font-bold flex items-center space-x-1.5 cursor-pointer transition-colors shadow-2xs"
            title="View Final Production File / Final Design"
          >
            <FileText className="w-3.5 h-3.5 text-purple-600" />
            <span>Final Design</span>
          </button>
        </div>

        {/* Details Link */}
        <button
          onClick={() => onSelectClient(client)}
          className="text-xs font-black text-orange-500 hover:text-orange-600 flex items-center space-x-0.5 cursor-pointer py-1 px-1.5 transition-colors"
        >
          <span>Details</span>
          <ChevronRight className="w-4 h-4 stroke-[2.5]" />
        </button>
      </div>
    </div>
  );
};
