import React, { useState } from 'react';
import { Users, UserPlus, Trash2, CheckCircle2, ChevronDown } from 'lucide-react';
import { ClientProject } from '../../types';

interface AssignedTeamSectionProps {
  client: ClientProject;
  onUpdateTeam?: (clientId: string, roleKey: string, memberName: string) => void;
}

interface AssignedMemberRecord {
  id: string;
  department: string;
  designation: string;
  memberName: string;
  roleKey: string;
  date: string;
}

// 12 Departments/Roles matching the exact screenshot
const TEAM_ROLES = [
  { id: 'designer', title: 'Designer', department: 'Interior Design' },
  { id: 'projectManager', title: 'Project Manager', department: 'Project Management' },
  { id: 'supervisor', title: 'Supervisor', department: 'Site Execution' },
  { id: 'qcValidationManager', title: 'QC Validation Manager', department: 'Quality Control' },
  { id: 'factoryPlanningTeam', title: 'Factory Planning Team', department: 'Factory Planning' },
  { id: 'factoryProductionTeam', title: 'Factory Production Team', department: 'Factory Production' },
  { id: 'qc', title: 'QC', department: 'Quality Assurance' },
  { id: 'factoryPurchaseTeam', title: 'Factory Purchase Team', department: 'Procurement' },
  { id: '3d', title: '3D', department: '3D Visuals' },
  { id: 'factoryProductionTeam2', title: 'Factory Production Team', department: 'Factory Assembly' },
  { id: 'qcFactory', title: 'QC Factory', department: 'Factory QC' },
  { id: 'factoryStore', title: 'Factory Store', department: 'Logistics & Store' },
];

const MOCK_DEPARTMENTS = [
  'Interior Design',
  'Project Management',
  'Site Execution',
  'Quality Control',
  'Factory Planning',
  'Factory Production',
  'Procurement',
  '3D Visuals',
  'Logistics & Store',
];

const MOCK_DESIGNATIONS: Record<string, string[]> = {
  'Interior Design': ['Senior Designer', 'Lead Designer', 'Junior Designer', 'Concept Designer'],
  'Project Management': ['Senior Project Manager', 'Assistant PM', 'Project Coordinator'],
  'Site Execution': ['Site Supervisor', 'Civil Engineer', 'Site Executive'],
  'Quality Control': ['QC Manager', 'Validation Engineer', 'Site Auditor'],
  'Factory Planning': ['Production Planner', 'CAD Draftsman', 'BOM Specialist'],
  'Factory Production': ['Production Supervisor', 'CNC Operator', 'Assembly Lead'],
  'Procurement': ['Purchase Manager', 'Material Procurement Lead', 'Vendor Specialist'],
  '3D Visuals': ['3D Visualizer', 'Render Specialist', '3D Lead'],
  'Logistics & Store': ['Store Incharge', 'Inventory Supervisor', 'Dispatch Executive'],
};

const MOCK_TEAM_MEMBERS: Record<string, string[]> = {
  'Senior Designer': ['Mansi Goyal', 'Shivanshi Verma', 'Ananya Roy', 'Priya Sharma'],
  'Lead Designer': ['Ayush Kumar', 'Rohan Mehta', 'Sneha Kapoor'],
  'Junior Designer': ['Tanya Gupta', 'Karan Verma'],
  'Senior Project Manager': ['Abhishek Bhati', 'Ashish Yadav', 'Vikramaditya'],
  'Assistant PM': ['Gaurav Aggarohia', 'Himanshu Tyagi'],
  'Site Supervisor': ['Ramesh Verma', 'Suresh Patel', 'Subhash Chand'],
  'QC Manager': ['Nishant Singh', 'Pankaj Sharma'],
  'Production Planner': ['Sunil Gupta', 'Amit Saini'],
  'Production Supervisor': ['Rajendra Singh', 'Dharmendra Kumar'],
  'Purchase Manager': ['Deepak Yadav', 'Mohit Bansal'],
  '3D Visualizer': ['Rahul Kumar', 'Aakash Dave', 'Simran Arora'],
  'Store Incharge': ['Satish Chand', 'Vinod Kumar'],
};

export const AssignedTeamSection: React.FC<AssignedTeamSectionProps> = ({
  client,
  onUpdateTeam,
}) => {
  const [selectedRole, setSelectedRole] = useState<string>('designer');

  // Form selection state
  const [selectedDept, setSelectedDept] = useState<string>('Interior Design');
  const [selectedDesignation, setSelectedDesignation] = useState<string>('');
  const [selectedMember, setSelectedMember] = useState<string>('');

  // Toast feedback
  const [successToast, setSuccessToast] = useState<string | null>(null);

  // Initial members generator based on client
  const [assignedMembers, setAssignedMembers] = useState<AssignedMemberRecord[]>(() => {
    const list: AssignedMemberRecord[] = [];
    if (client.assignedTeam?.designer) {
      list.push({
        id: 'initial-des',
        department: 'Interior Design',
        designation: 'Lead Designer',
        memberName: client.assignedTeam.designer,
        roleKey: 'designer',
        date: client.date ? client.date.split(' ')[0] : '2026-07-28',
      });
    }
    if (client.assignedTeam?.projectManager) {
      list.push({
        id: 'initial-pm',
        department: 'Project Management',
        designation: 'Senior Project Manager',
        memberName: client.assignedTeam.projectManager,
        roleKey: 'projectManager',
        date: client.date ? client.date.split(' ')[0] : '2026-07-28',
      });
    }
    return list;
  });

  // Handle department change
  const handleDepartmentChange = (dept: string) => {
    setSelectedDept(dept);
    const designations = MOCK_DESIGNATIONS[dept] || [];
    const firstDesig = designations[0] || '';
    setSelectedDesignation(firstDesig);
    const members = MOCK_TEAM_MEMBERS[firstDesig] || [];
    setSelectedMember(members[0] || '');
  };

  // Handle designation change
  const handleDesignationChange = (desig: string) => {
    setSelectedDesignation(desig);
    const members = MOCK_TEAM_MEMBERS[desig] || [];
    setSelectedMember(members[0] || '');
  };

  // Switch Role tab and automatically sync form selects
  const handleSelectRoleTab = (roleId: string, deptName: string) => {
    setSelectedRole(roleId);
    handleDepartmentChange(deptName);
  };

  // Save Assignment
  const handleSaveMember = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedDept || !selectedDesignation || !selectedMember) return;

    const newRecord: AssignedMemberRecord = {
      id: `m-${Date.now()}`,
      department: selectedDept,
      designation: selectedDesignation,
      memberName: selectedMember,
      roleKey: selectedRole,
      date: new Date().toISOString().split('T')[0],
    };

    setAssignedMembers((prev) => [newRecord, ...prev]);

    if (onUpdateTeam) {
      onUpdateTeam(client.id, selectedRole, selectedMember);
    }

    setSuccessToast(`Assigned ${selectedMember} as ${selectedDesignation}`);
    setTimeout(() => setSuccessToast(null), 3000);
  };

  // Remove member assignment
  const handleRemoveMember = (id: string) => {
    setAssignedMembers((prev) => prev.filter((m) => m.id !== id));
  };

  // Filter members for currently active role/tab
  const currentRoleMembers = assignedMembers.filter((m) => m.roleKey === selectedRole);

  return (
    <div className="bg-white rounded-xl border border-slate-200 border-t-4 border-t-orange-500 shadow-sm overflow-hidden p-4 sm:p-6 space-y-6">
      {/* Toast Notification */}
      {successToast && (
        <div className="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold p-3 rounded-lg flex items-center space-x-2 animate-in fade-in">
          <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
          <span>{successToast}</span>
        </div>
      )}

      {/* Card Header Title */}
      <div className="flex items-center justify-between border-b border-slate-100 pb-3">
        <h3 className="text-sm font-extrabold text-slate-800 tracking-wide uppercase">
          Team
        </h3>
        <span className="text-xs text-slate-500 font-medium">
          Client: <strong className="text-slate-800">{client.name}</strong> ({client.id})
        </span>
      </div>

      {/* Role / Department Grid Tabs (12 buttons matching image) */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-2.5">
        {TEAM_ROLES.map((role) => {
          const isSelected = selectedRole === role.id;
          const assignedCount = assignedMembers.filter((m) => m.roleKey === role.id).length;

          return (
            <button
              key={role.id}
              onClick={() => handleSelectRoleTab(role.id, role.department)}
              className={`px-3 py-2.5 rounded text-xs font-bold flex items-center justify-between transition-all cursor-pointer ${
                isSelected
                  ? 'bg-[#f5a36c] text-slate-900 shadow-2xs border border-orange-400'
                  : 'bg-[#dbe2ea] hover:bg-[#cbd5e1] text-slate-800 border border-slate-300/60'
              }`}
            >
              <div className="flex items-center space-x-2 truncate">
                <Users className={`w-4 h-4 shrink-0 ${isSelected ? 'text-slate-900' : 'text-slate-700'}`} />
                <span className="truncate">{role.title}</span>
              </div>
              {assignedCount > 0 && (
                <span
                  className={`text-[10px] px-1.5 py-0.5 rounded-full font-black ${
                    isSelected ? 'bg-slate-900 text-white' : 'bg-slate-700 text-white'
                  }`}
                >
                  {assignedCount}
                </span>
              )}
            </button>
          );
        })}
      </div>

      {/* Assignment Form matching image layout */}
      <form onSubmit={handleSaveMember} className="space-y-4 pt-2">
        <div className="max-w-2xl mx-auto space-y-3">
          {/* Field 1: Department */}
          <div className="grid grid-cols-1 sm:grid-cols-3 items-center gap-2">
            <label className="text-xs font-bold text-slate-600 sm:text-right">
              Select Department <span className="text-red-500">*</span>
            </label>
            <div className="sm:col-span-2 relative">
              <select
                value={selectedDept}
                onChange={(e) => handleDepartmentChange(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded px-3 py-1.5 text-xs font-semibold text-slate-800 appearance-none focus:outline-none focus:border-orange-500 pr-8 cursor-pointer"
              >
                {MOCK_DEPARTMENTS.map((dept) => (
                  <option key={dept} value={dept}>
                    {dept}
                  </option>
                ))}
              </select>
              <ChevronDown className="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
          </div>

          {/* Field 2: Designation */}
          <div className="grid grid-cols-1 sm:grid-cols-3 items-center gap-2">
            <label className="text-xs font-bold text-slate-600 sm:text-right">
              Select Designation <span className="text-red-500">*</span>
            </label>
            <div className="sm:col-span-2 relative">
              <select
                value={selectedDesignation}
                onChange={(e) => handleDesignationChange(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded px-3 py-1.5 text-xs font-semibold text-slate-800 appearance-none focus:outline-none focus:border-orange-500 pr-8 cursor-pointer"
              >
                <option value="">Select Designation</option>
                {(MOCK_DESIGNATIONS[selectedDept] || [
                  'Senior Executive',
                  'Team Lead',
                  'Associate',
                ]).map((des) => (
                  <option key={des} value={des}>
                    {des}
                  </option>
                ))}
              </select>
              <ChevronDown className="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
          </div>

          {/* Field 3: Team Member */}
          <div className="grid grid-cols-1 sm:grid-cols-3 items-center gap-2">
            <label className="text-xs font-bold text-slate-600 sm:text-right">
              Select Team Member <span className="text-red-500">*</span>
            </label>
            <div className="sm:col-span-2 relative">
              <select
                value={selectedMember}
                onChange={(e) => setSelectedMember(e.target.value)}
                className="w-full bg-white border border-slate-300 rounded px-3 py-1.5 text-xs font-semibold text-slate-800 appearance-none focus:outline-none focus:border-orange-500 pr-8 cursor-pointer"
              >
                <option value="">Select Team Member</option>
                {(
                  MOCK_TEAM_MEMBERS[selectedDesignation] || [
                    'Ashish Yadav',
                    'Mansi Goyal',
                    'Abhishek Bhati',
                    'Shivanshi Verma',
                    'Gaurav Aggarohia',
                  ]
                ).map((mem) => (
                  <option key={mem} value={mem}>
                    {mem}
                  </option>
                ))}
              </select>
              <ChevronDown className="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
          </div>

          {/* Save Button Aligned Right */}
          <div className="flex justify-end pt-1">
            <button
              type="submit"
              className="bg-[#5b9bd5] hover:bg-[#4a89c4] text-white text-xs font-bold px-6 py-1.5 rounded shadow-2xs transition-colors cursor-pointer"
            >
              Save
            </button>
          </div>
        </div>
      </form>

      {/* Assigned Team Members Table Container */}
      <div className="border border-slate-200/90 rounded-lg p-3 sm:p-4 bg-white space-y-3">
        <div className="grid grid-cols-12 gap-2 text-xs font-extrabold text-slate-700 px-2 border-b border-slate-100 pb-2">
          <div className="col-span-4">Designation</div>
          <div className="col-span-4 text-center sm:text-left">Team Member</div>
          <div className="col-span-3 text-center">Date</div>
          <div className="col-span-1 text-center">Action</div>
        </div>

        {currentRoleMembers.length > 0 ? (
          <div className="divide-y divide-slate-100">
            {currentRoleMembers.map((member) => (
              <div
                key={member.id}
                className="grid grid-cols-12 gap-2 text-xs items-center py-2.5 px-2 hover:bg-slate-50 transition-colors"
              >
                <div className="col-span-4 font-bold text-slate-800">
                  {member.designation}
                </div>
                <div className="col-span-4 font-semibold text-slate-700 text-center sm:text-left">
                  {member.memberName}
                </div>
                <div className="col-span-3 font-mono text-slate-500 text-center text-[11px]">
                  {member.date}
                </div>
                <div className="col-span-1 text-center">
                  <button
                    onClick={() => handleRemoveMember(member.id)}
                    className="p-1 text-slate-400 hover:text-red-600 transition-colors cursor-pointer rounded hover:bg-red-50"
                    title="Remove Member"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="bg-[#f8fafc] text-slate-500 py-3 text-center text-xs font-medium border border-slate-100 rounded">
            No team member assigned
          </div>
        )}
      </div>
    </div>
  );
};
