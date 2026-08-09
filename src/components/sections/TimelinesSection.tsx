import React, { useState } from 'react';
import { ClientProject } from '../../types';
import {
  Pencil,
  Building2,
  Boxes,
  CheckSquare,
  Truck,
  Check,
  Calendar,
  User,
  MapPin,
  Briefcase,
  DollarSign,
  FileCheck,
  Clock,
  CheckCircle2,
  AlertCircle,
  Hash,
  Sparkles,
  LayoutGrid,
  Table as TableIcon,
} from 'lucide-react';

interface TimelinesSectionProps {
  client?: ClientProject;
}

export const TimelinesSection: React.FC<TimelinesSectionProps> = ({ client }) => {
  const [activeTimelineTab, setActiveTimelineTab] = useState<
    'designing' | 'civil' | 'modular' | 'validation' | 'dispatch'
  >('designing');

  const [civilViewMode, setCivilViewMode] = useState<'cards' | 'table'>('cards');

  // Done status state for interactive buttons
  const [doneItems, setDoneItems] = useState<Record<string, boolean>>({
    designer_site_visit: true,
    site_work_start: false,
    civil_site_start: false,
    civil_drawings: false,
    mod_ppt: false,
    mod_finalization: false,
    mod_2d: false,
    mod_material: false,
    mod_3d: false,
    val_site: false,
    val_mom: false,
    val_elec: false,
    prod_correction: false,
    prod_approval: false,
    prod_dispatch: false,
  });

  const toggleDone = (key: string) => {
    setDoneItems((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  const tabs = [
    { id: 'designing', label: 'Designing', icon: Pencil },
    { id: 'civil', label: 'Civil Work', icon: Building2 },
    { id: 'modular', label: 'Modular Work', icon: Boxes },
    { id: 'validation', label: 'Site Validation', icon: CheckSquare },
    { id: 'dispatch', label: 'Production & Dispatch', icon: Truck },
  ] as const;

  const siteName = client?.name
    ? `test permanent address, ${client.name}`
    : 'test permanent address, testjddvnjksd';
  const managerName = client?.salesManager || 'Abhishek Bhati';
  const clientName = client ? `${client.name}` : 'Mr. Test Client : 22 july';
  const customerId = client?.id || 'HC101800';
  const projectValue = client?.amountTotal
    ? `₹${client.amountTotal.toLocaleString('en-IN')}`
    : '₹188,126';

  return (
    <div className="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-sm space-y-5">
      {/* Title & Navigation Tabs */}
      <div className="space-y-3 border-b border-slate-100 pb-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <div className="p-1.5 bg-orange-50 text-orange-600 rounded-lg">
              <Clock className="w-4 h-4" />
            </div>
            <h2 className="text-base font-bold text-slate-900 tracking-tight">Project Timelines</h2>
          </div>
          <span className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200/60">
            {customerId}
          </span>
        </div>

        {/* Navigation Sub-Tabs */}
        <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none pt-1">
          {tabs.map((tab) => {
            const Icon = tab.icon;
            const isActive = activeTimelineTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTimelineTab(tab.id)}
                className={`flex items-center space-x-2 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer ${
                  isActive
                    ? 'bg-slate-900 text-white shadow-sm'
                    : 'bg-slate-50 text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/60'
                }`}
              >
                <Icon
                  className={`w-3.5 h-3.5 ${
                    isActive ? 'text-orange-400' : 'text-slate-400'
                  }`}
                />
                <span>{tab.label}</span>
              </button>
            );
          })}
        </div>
      </div>

      {/* Tab 1: Designing Timeline */}
      {activeTimelineTab === 'designing' && (
        <div className="space-y-4">
          <div className="bg-emerald-50 border border-emerald-200/80 rounded-xl p-3 flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <Sparkles className="w-4 h-4 text-emerald-600" />
              <span className="text-xs font-extrabold text-emerald-900 uppercase tracking-wider">
                Designing Phase Timeline
              </span>
            </div>
            <span className="text-[11px] font-bold text-emerald-700 bg-emerald-100/80 px-2.5 py-0.5 rounded-full">
              Phase Active
            </span>
          </div>

          {/* Clean Grid Overview */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div className="bg-slate-50/80 p-3 rounded-xl border border-slate-200/70 space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">
                Site Location
              </span>
              <p className="text-xs font-bold text-slate-800 truncate">{siteName}</p>
            </div>

            <div className="bg-slate-50/80 p-3 rounded-xl border border-slate-200/70 space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">
                Designer Name
              </span>
              <p className="text-xs font-bold text-slate-800">NA</p>
            </div>

            <div className="bg-slate-50/80 p-3 rounded-xl border border-slate-200/70 space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">
                Project Manager
              </span>
              <p className="text-xs font-bold text-slate-800">{managerName}</p>
            </div>

            <div className="bg-slate-50/80 p-3 rounded-xl border border-slate-200/70 space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">
                Project Value
              </span>
              <p className="text-xs font-extrabold text-emerald-600">{projectValue}</p>
            </div>

            <div className="bg-slate-50/80 p-3 rounded-xl border border-slate-200/70 space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">
                Client Name
              </span>
              <p className="text-xs font-bold text-slate-800 truncate">{clientName}</p>
            </div>

            <div className="bg-slate-50/80 p-3 rounded-xl border border-slate-200/70 space-y-1">
              <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">
                Customer ID
              </span>
              <p className="text-xs font-mono font-bold text-indigo-600">{customerId}</p>
            </div>
          </div>

          {/* Important Milestone Cards */}
          <div className="space-y-2.5 pt-1">
            {/* Designer Site Visit Card */}
            <div className="bg-purple-50/80 border border-purple-200/80 rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all">
              <div className="space-y-0.5">
                <span className="text-[10px] font-extrabold uppercase tracking-wider text-purple-700">
                  Milestone Date
                </span>
                <h4 className="text-xs font-bold text-slate-900">DESIGNER SITE VISIT DATE</h4>
              </div>

              <div className="flex items-center space-x-3 shrink-0">
                <span className="font-mono text-xs font-bold text-slate-800 bg-white px-2.5 py-1 rounded-lg border border-purple-200/60 shadow-2xs">
                  22-07-2026
                </span>
                <button
                  onClick={() => toggleDone('designer_site_visit')}
                  className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center space-x-1.5 transition-all cursor-pointer shadow-xs ${
                    doneItems['designer_site_visit']
                      ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                      : 'bg-orange-500 text-white hover:bg-orange-600'
                  }`}
                >
                  <CheckCircle2 className="w-3.5 h-3.5" />
                  <span>{doneItems['designer_site_visit'] ? 'Done ✓' : 'Mark Done'}</span>
                </button>
              </div>
            </div>

            {/* Site Work Start Card */}
            <div className="bg-rose-50/80 border border-rose-200/80 rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all">
              <div className="space-y-0.5">
                <span className="text-[10px] font-extrabold uppercase tracking-wider text-rose-700">
                  Site Kickoff
                </span>
                <h4 className="text-xs font-bold text-slate-900">SITE WORK START DATE</h4>
              </div>

              <div className="flex items-center space-x-3 shrink-0">
                <span className="font-mono text-xs font-bold text-slate-800 bg-white px-2.5 py-1 rounded-lg border border-rose-200/60 shadow-2xs">
                  16-08-2026
                </span>
                <button
                  onClick={() => toggleDone('site_work_start')}
                  className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center space-x-1.5 transition-all cursor-pointer shadow-xs ${
                    doneItems['site_work_start']
                      ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                      : 'bg-orange-500 text-white hover:bg-orange-600'
                  }`}
                >
                  <CheckCircle2 className="w-3.5 h-3.5" />
                  <span>{doneItems['site_work_start'] ? 'Done ✓' : 'Mark Done'}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Tab 2: Civil Work Timeline */}
      {activeTimelineTab === 'civil' && (
        <div className="space-y-4">
          {/* Section Sub-Header */}
          <div className="bg-sky-50 border border-sky-200/80 rounded-xl p-3 sm:p-3.5 flex items-center justify-between gap-3">
            <div className="flex items-center space-x-2">
              <Building2 className="w-4 h-4 text-sky-600 shrink-0" />
              <span className="text-xs font-extrabold text-sky-950 uppercase tracking-wider">
                Civil Work Schedule & Deliverables
              </span>
            </div>
          </div>

          {/* Table View (Clean Spreadsheet Layout) */}
          <div className="border border-slate-300 rounded-xl overflow-hidden text-xs bg-white shadow-xs">
            <div className="bg-sky-100 text-sky-950 font-extrabold text-center py-2.5 border-b border-sky-200 tracking-wider text-xs uppercase flex items-center justify-center space-x-2">
              <Building2 className="w-4 h-4 text-sky-600" />
              <span>CIVIL WORK TIMELINE</span>
            </div>

            <div className="overflow-x-auto scrollbar-thin">
              <table className="w-full text-left border-collapse min-w-[620px]">
                <thead>
                  <tr className="bg-sky-200/60 text-slate-900 font-extrabold border-b border-sky-300 text-xs">
                    <th className="py-2.5 px-4 border-r border-sky-300 w-16 text-center whitespace-nowrap">S. No.</th>
                    <th className="py-2.5 px-5 border-r border-sky-300 text-left">Details</th>
                    <th className="py-2.5 px-6 text-center w-[300px] min-w-[300px] whitespace-nowrap">Time Line</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-200 text-slate-800">
                  <tr className="bg-slate-50/70 border-b border-slate-300 hover:bg-sky-50/30 transition-colors">
                    <td className="py-2.5 px-4 border-r border-slate-200 text-center font-bold text-slate-400">—</td>
                    <td className="py-2.5 px-5 border-r border-slate-200 font-extrabold text-slate-900 uppercase">
                      SITE START DATE
                    </td>
                    <td className="py-2.5 px-6 text-center bg-white">
                      <div className="flex items-center justify-center space-x-3">
                        <span className="font-mono font-bold text-slate-900 whitespace-nowrap text-xs bg-slate-100 border border-slate-200 px-2.5 py-1 rounded">
                          16-08-2026
                        </span>
                        <button
                          onClick={() => toggleDone('civil_site_start')}
                          className={`px-3 py-1 rounded text-xs font-bold flex items-center space-x-1 cursor-pointer shrink-0 whitespace-nowrap transition-all ${
                            doneItems['civil_site_start']
                              ? 'bg-emerald-600 text-white'
                              : 'bg-orange-500 hover:bg-orange-600 text-white'
                          }`}
                        >
                          <Pencil className="w-3.5 h-3.5" />
                          <span>{doneItems['civil_site_start'] ? 'Done ✓' : 'Mark Done'}</span>
                        </button>
                      </div>
                    </td>
                  </tr>

                  {[
                    { id: 1, name: 'Layout Finalization' },
                    { id: 2, name: 'Dismantling Drawings' },
                    { id: 3, name: 'Brick Work Drawing' },
                    { id: 4, name: 'Washroom Plumbing & Electrical Drawings' },
                    { id: 5, name: 'False Ceiling Drawings (Lights & Looping)' },
                    { id: 6, name: 'Washroom and Full House Tiles Finalization' },
                  ].map((row, idx) => (
                    <tr key={row.id} className="border-b border-slate-200 hover:bg-slate-50/50 transition-colors">
                      <td className="py-2.5 px-4 border-r border-slate-200 text-center font-semibold text-slate-600">
                        {row.id}
                      </td>
                      <td className="py-2.5 px-5 border-r border-slate-200 font-medium text-slate-800">
                        {row.name}
                      </td>
                      {idx === 0 ? (
                        <td
                          rowSpan={6}
                          className="py-4 px-6 text-center align-middle bg-white border-l border-slate-200 w-[300px] min-w-[300px]"
                        >
                          <div className="flex items-center justify-center space-x-3">
                            <span className="font-mono font-bold text-slate-900 whitespace-nowrap text-xs bg-slate-100 border border-slate-200 px-2.5 py-1 rounded">
                              06-08-2026 – 11-08-2026
                            </span>
                            <button
                              onClick={() => toggleDone('civil_drawings')}
                              className={`px-3 py-1 rounded text-xs font-bold flex items-center space-x-1 cursor-pointer shrink-0 whitespace-nowrap transition-all ${
                                doneItems['civil_drawings']
                                  ? 'bg-emerald-600 text-white'
                                  : 'bg-orange-500 hover:bg-orange-600 text-white'
                              }`}
                            >
                              <Pencil className="w-3.5 h-3.5" />
                              <span>{doneItems['civil_drawings'] ? 'Done ✓' : 'Mark Done'}</span>
                            </button>
                          </div>
                        </td>
                      ) : null}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="bg-amber-50 p-2.5 text-amber-900 italic font-medium text-center border-t border-amber-200 text-xs flex items-center justify-center space-x-2">
              <AlertCircle className="w-4 h-4 text-amber-600 shrink-0" />
              <span>Note : All Civil Drawings to be finalised except Wall Electrical Drawings</span>
            </div>
          </div>
        </div>
      )}

      {/* Tab 3: Modular Work Timeline */}
      {activeTimelineTab === 'modular' && (
        <div className="space-y-4">
          <div className="bg-indigo-50 border border-indigo-200/80 rounded-xl p-3.5 flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <Boxes className="w-4 h-4 text-indigo-600" />
              <span className="text-xs font-extrabold text-indigo-900 uppercase tracking-wider">
                Modular Work Submissions
              </span>
            </div>
            <span className="text-xs font-bold text-indigo-700 bg-indigo-100/90 px-3 py-1 rounded-full">
              6 Deliverables
            </span>
          </div>

          {/* Cards for each item */}
          <div className="space-y-3">
            {[
              { id: 1, name: 'Design Presentation (PPT)', date: '26-07-2026 – 27-07-2026', key: 'mod_ppt' },
              { id: 2, name: 'Design Finalization', date: '02-08-2026 – 05-08-2026', key: 'mod_finalization' },
              { id: 3, name: 'Wall Designs Modular 2D Working Drawings', date: '05-08-2026 – 14-08-2026', key: 'mod_2d' },
              { id: 4, name: 'Material Selection (Includes Handles & Paint)', date: '14-08-2026 – 16-08-2026', key: 'mod_material' },
              { id: 5, name: '3D Design File to be submitted (Optional)', date: '06-08-2026', key: 'mod_3d' },
              { id: 6, name: 'Wall Electrical Drawings', date: 'Before Validation', key: 'mod_elec' },
            ].map((row) => (
              <div
                key={row.id}
                className="bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-2xs space-y-3 hover:border-slate-300 transition-all"
              >
                <div className="flex items-start space-x-3">
                  <span className="w-6 h-6 rounded-lg bg-orange-100 text-orange-700 font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5">
                    {row.id}
                  </span>
                  <h4 className="text-xs font-bold text-slate-800 leading-snug">{row.name}</h4>
                </div>

                <div className="flex flex-wrap items-center justify-between pt-2 border-t border-slate-100 gap-2">
                  <div className="flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200/70 text-xs font-mono font-semibold text-slate-700">
                    <Calendar className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <span className="whitespace-nowrap">{row.date}</span>
                  </div>

                  {row.key !== 'mod_elec' && (
                    <button
                      onClick={() => toggleDone(row.key)}
                      className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center space-x-1.5 cursor-pointer transition-all ${
                        doneItems[row.key]
                          ? 'bg-emerald-600 text-white'
                          : 'bg-orange-500 hover:bg-orange-600 text-white'
                      }`}
                    >
                      <CheckCircle2 className="w-3.5 h-3.5" />
                      <span>{doneItems[row.key] ? 'Completed ✓' : 'Mark Done'}</span>
                    </button>
                  )}
                </div>
              </div>
            ))}
          </div>

          <div className="bg-amber-50 border border-amber-200/70 p-3 rounded-xl text-amber-900 text-xs font-medium flex items-center space-x-2">
            <AlertCircle className="w-4 h-4 text-amber-600 shrink-0" />
            <span>Note: All items in the BOQ to be finalised</span>
          </div>
        </div>
      )}

      {/* Tab 4: Site Validation Timeline */}
      {activeTimelineTab === 'validation' && (
        <div className="space-y-4">
          <div className="bg-emerald-50 border border-emerald-200/80 rounded-xl p-3.5 flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <CheckSquare className="w-4 h-4 text-emerald-600" />
              <span className="text-xs font-extrabold text-emerald-900 uppercase tracking-wider">
                Site Validation Checklist
              </span>
            </div>
            <span className="text-xs font-bold text-emerald-700 bg-emerald-100/90 px-3 py-1 rounded-full">
              3 Checkpoints
            </span>
          </div>

          <div className="space-y-3">
            {[
              { id: 1, name: 'Site Validation', date: '20-09-2026', key: 'val_site' },
              { id: 2, name: 'MOM Checklist', date: 'Pending', key: 'val_mom' },
              { id: 3, name: 'Wall Electrical Drawings', date: 'After 24-07-2026 of validation', key: 'val_elec' },
            ].map((row) => (
              <div
                key={row.id}
                className="bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-2xs space-y-3 hover:border-slate-300 transition-all"
              >
                <div className="flex items-start space-x-3">
                  <span className="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5">
                    {row.id}
                  </span>
                  <h4 className="text-xs font-bold text-slate-800 leading-snug">{row.name}</h4>
                </div>

                <div className="flex flex-wrap items-center justify-between pt-2 border-t border-slate-100 gap-2">
                  <div className="flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200/70 text-xs font-mono font-semibold text-slate-700">
                    <Calendar className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <span className="whitespace-nowrap">{row.date}</span>
                  </div>

                  <button
                    onClick={() => toggleDone(row.key)}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center space-x-1.5 cursor-pointer transition-all ${
                      doneItems[row.key]
                        ? 'bg-emerald-600 text-white'
                        : 'bg-orange-500 hover:bg-orange-600 text-white'
                    }`}
                  >
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>{doneItems[row.key] ? 'Completed ✓' : 'Mark Done'}</span>
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Tab 5: Production & Dispatch Timeline */}
      {activeTimelineTab === 'dispatch' && (
        <div className="space-y-4">
          <div className="bg-purple-50 border border-purple-200/80 rounded-xl p-3.5 flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <Truck className="w-4 h-4 text-purple-600" />
              <span className="text-xs font-extrabold text-purple-900 uppercase tracking-wider">
                Production & Dispatch Schedule
              </span>
            </div>
            <span className="text-xs font-bold text-purple-700 bg-purple-100/90 px-3 py-1 rounded-full">
              40-45 Days Handover
            </span>
          </div>

          <div className="space-y-3">
            {[
              { id: 1, name: 'Correction Drawings', date: 'After 27-07-2026', key: 'prod_correction' },
              { id: 2, name: 'Production Drawings for Approval', date: '31-07-2026', key: 'prod_approval' },
              { id: 3, name: 'Dispatch Date', date: 'Pending Dispatch', key: 'prod_dispatch' },
            ].map((row) => (
              <div
                key={row.id}
                className="bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-2xs space-y-3 hover:border-slate-300 transition-all"
              >
                <div className="flex items-start space-x-3">
                  <span className="w-6 h-6 rounded-lg bg-purple-100 text-purple-800 font-extrabold text-xs flex items-center justify-center shrink-0 mt-0.5">
                    {row.id}
                  </span>
                  <h4 className="text-xs font-bold text-slate-800 leading-snug">{row.name}</h4>
                </div>

                <div className="flex flex-wrap items-center justify-between pt-2 border-t border-slate-100 gap-2">
                  <div className="flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200/70 text-xs font-mono font-semibold text-slate-700">
                    <Calendar className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <span className="whitespace-nowrap">{row.date}</span>
                  </div>

                  <button
                    onClick={() => toggleDone(row.key)}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center space-x-1.5 cursor-pointer transition-all ${
                      doneItems[row.key]
                        ? 'bg-emerald-600 text-white'
                        : 'bg-orange-500 hover:bg-orange-600 text-white'
                    }`}
                  >
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>{doneItems[row.key] ? 'Completed ✓' : 'Mark Done'}</span>
                  </button>
                </div>
              </div>
            ))}
          </div>

          <div className="bg-rose-50 border border-rose-200/80 p-3.5 rounded-xl text-rose-900 text-xs font-bold text-center">
            Handover Expectation: 40–45 days after Production Approval
          </div>
        </div>
      )}
    </div>
  );
};

