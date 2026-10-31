import React, { useState, useEffect } from 'react';
import {
  User,
  GraduationCap,
  Briefcase,
  Users,
  FileCheck,
  Plus,
  Trash2,
  Lock,
  Unlock,
  CheckCircle2,
  AlertCircle,
  Clock,
  Upload,
  ArrowLeft,
  Save,
  Send,
  Building,
  Phone,
  Calendar,
  Shield,
  FileText,
  Eye,
  Check,
} from 'lucide-react';
import { UserData } from '../../services/authApi';
import {
  getEmploymentApplicationApi,
  saveEmploymentApplicationApi,
  EmploymentApplicationData,
  EducationRow,
  ReferenceRow,
  FamilyRow,
  ExperienceRow,
} from '../../services/userPortalApi';

interface EmploymentApplicationSectionProps {
  userData: UserData | null;
  authToken: string;
  showToast: (msg: string) => void;
  onBack: () => void;
  onStatusUpdated?: (status: string) => void;
}

type TabType = 'personal' | 'experience' | 'education' | 'family' | 'documents';

export const EmploymentApplicationSection: React.FC<EmploymentApplicationSectionProps> = ({
  userData,
  authToken,
  showToast,
  onBack,
  onStatusUpdated,
}) => {
  const [activeTab, setActiveTab] = useState<TabType>('personal');
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [isSaving, setIsSaving] = useState<boolean>(false);
  const [isUnlocked, setIsUnlocked] = useState<boolean>(false);

  // Form Fields
  const [formData, setFormData] = useState<EmploymentApplicationData>({
    fullname: userData?.fullname || '',
    date_of_birth: '',
    gender: 'Male',
    maratial_status: 'Single',
    mobile: userData?.mobile || '',
    present_address: '',
    joining_date: '',
    employment_id: userData?.employment_id || '',
    father_name: '',
    father_mob_no: '',
    fresher_experience: 'Fresher',
    total_experience_years: '',
    last_salary: '',
    present_salary: '',
    aadhaar_no: '',
    pan_no: '',
    date_of_marriage: '',
    spouse_name: '',
    blood_group: '',
    height_weight: '',
    present_contact_no: '',
    permanent_address: '',
    permanent_contact_no: '',
    relative_friend_in_company: '',
    previously_applied: '',
    declaration_date: new Date().toISOString().split('T')[0],
    declaration_place: 'Noida',
    status: 'draft',
    educational_qualifications: [],
    professional_references: [
      { name: '', designation_relation: '', contact_no: '' },
      { name: '', designation_relation: '', contact_no: '' },
    ],
    family_details: [],
    work_experience: [],
  });

  const [educationList, setEducationList] = useState<EducationRow[]>([
    {
      school_college: '',
      from_period: '',
      to_period: '',
      division_grade: '',
      percentage: '',
      subjects: '',
      scholarships_distinctions: '',
    },
  ]);

  const [experienceList, setExperienceList] = useState<ExperienceRow[]>([]);

  const [familyList, setFamilyList] = useState<FamilyRow[]>([
    { name: '', relation: '', occupation: '', date_of_birth: '' },
  ]);

  const [references, setReferences] = useState<ReferenceRow[]>([
    { name: '', designation_relation: '', contact_no: '' },
    { name: '', designation_relation: '', contact_no: '' },
  ]);

  const [declared, setDeclared] = useState<boolean>(true);

  // Load existing application
  useEffect(() => {
    let isMounted = true;
    async function loadData() {
      setIsLoading(true);
      try {
        const res = await getEmploymentApplicationApi(authToken, userData?.user_id);
        if (res && res.status && res.data && isMounted) {
          const d = res.data;
          setFormData((prev) => ({
            ...prev,
            ...d,
            fullname: d.fullname || userData?.fullname || prev.fullname,
            mobile: d.mobile || userData?.mobile || prev.mobile,
            employment_id: d.employment_id || userData?.employment_id || prev.employment_id,
          }));

          if (d.status) {
            onStatusUpdated?.(d.status);
          }

          // Education
          if (Array.isArray(d.educational_qualifications) && d.educational_qualifications.length > 0) {
            setEducationList(d.educational_qualifications);
          } else if (typeof d.educational_qualifications === 'string' && d.educational_qualifications.trim()) {
            try {
              const parsed = JSON.parse(d.educational_qualifications);
              if (Array.isArray(parsed) && parsed.length > 0) setEducationList(parsed);
            } catch (_) {}
          }

          // Experience
          if (Array.isArray(d.work_experience) && d.work_experience.length > 0) {
            setExperienceList(d.work_experience);
          } else if (typeof d.work_experience === 'string' && d.work_experience.trim()) {
            try {
              const parsed = JSON.parse(d.work_experience);
              if (Array.isArray(parsed)) setExperienceList(parsed);
            } catch (_) {}
          }

          // Family
          if (Array.isArray(d.family_details) && d.family_details.length > 0) {
            setFamilyList(d.family_details);
          } else if (typeof d.family_details === 'string' && d.family_details.trim()) {
            try {
              const parsed = JSON.parse(d.family_details);
              if (Array.isArray(parsed) && parsed.length > 0) setFamilyList(parsed);
            } catch (_) {}
          }

          // References
          if (Array.isArray(d.professional_references) && d.professional_references.length > 0) {
            const refs = [...d.professional_references];
            while (refs.length < 2) {
              refs.push({ name: '', designation_relation: '', contact_no: '' });
            }
            setReferences(refs);
          } else if (typeof d.professional_references === 'string' && d.professional_references.trim()) {
            try {
              const parsed = JSON.parse(d.professional_references);
              if (Array.isArray(parsed) && parsed.length > 0) {
                while (parsed.length < 2) {
                  parsed.push({ name: '', designation_relation: '', contact_no: '' });
                }
                setReferences(parsed);
              }
            } catch (_) {}
          }
        }
      } catch (e) {
        console.error('Error fetching employment application:', e);
      } finally {
        if (isMounted) setIsLoading(false);
      }
    }
    loadData();
    return () => {
      isMounted = false;
    };
  }, [authToken, userData?.user_id]);

  const isFormLocked = formData.status === 'approved' && !isUnlocked;

  // File Upload Helper
  const handleFileUpload = (field: 'photograph' | 'aadhaar_card_doc' | 'pan_card_doc' | 'signature_doc', file: File) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const b64 = e.target?.result as string;
      setFormData((prev) => ({
        ...prev,
        [field]: b64,
        [`${field}_url`]: b64,
      }));
      showToast(`${field.replace(/_/g, ' ').toUpperCase()} selected!`);
    };
    reader.readAsDataURL(file);
  };

  // Submit / Draft Handler
  const handleSubmit = async (action: 'draft' | 'submit') => {
    if (action === 'submit' && !declared) {
      showToast('Please check the declaration box before submitting.');
      return;
    }

    if (action === 'submit') {
      if (!formData.aadhaar_no && !formData.pan_no) {
        showToast('Please provide Aadhaar Number or PAN Number.');
        return;
      }
    }

    setIsSaving(true);
    try {
      const payload: any = {
        ...formData,
        educational_qualifications: educationList,
        work_experience: experienceList,
        family_details: familyList,
        professional_references: references,
        action,
        token: authToken,
        user_id: userData?.user_id,
      };

      const res = await saveEmploymentApplicationApi(payload);
      if (res && res.status) {
        showToast(action === 'submit' ? 'Application submitted for HR Verification!' : 'Draft saved successfully!');
        setFormData((prev) => ({
          ...prev,
          status: res.form_status as any || (action === 'submit' ? 'submitted' : 'draft'),
        }));
        onStatusUpdated?.(res.form_status || (action === 'submit' ? 'submitted' : 'draft'));
        if (action === 'submit') {
          setIsUnlocked(false);
        }
      } else {
        showToast(res?.message || 'Failed to save form. Please try again.');
      }
    } catch (e) {
      console.error(e);
      showToast('An error occurred while saving the form.');
    } finally {
      setIsSaving(false);
    }
  };

  if (isLoading) {
    return (
      <div className="py-12 flex flex-col items-center justify-center space-y-3">
        <div className="w-8 h-8 border-2 border-amber-400 border-t-transparent rounded-full animate-spin" />
        <p className="text-xs text-zinc-400 font-medium">Loading Employment Application...</p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {/* Top Banner / Status Card */}
      <div className="bg-zinc-950 border border-zinc-800 rounded-2xl p-4 shadow-lg relative overflow-hidden">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2.5">
            <div className="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
              <FileCheck className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-xs font-bold text-white tracking-wide">Employment Application Form</h3>
              <p className="text-[10px] text-zinc-400">High Creation Interior Projects Pvt. Ltd.</p>
            </div>
          </div>

          <div>
            {formData.status === 'approved' && (
              <span className="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                <CheckCircle2 className="w-3 h-3" />
                <span>HR Approved</span>
              </span>
            )}
            {formData.status === 'submitted' && (
              <span className="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/40">
                <Clock className="w-3 h-3" />
                <span>Pending HR Review</span>
              </span>
            )}
            {formData.status === 'rejected' && (
              <span className="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/40">
                <AlertCircle className="w-3 h-3" />
                <span>Changes Requested</span>
              </span>
            )}
            {(!formData.status || formData.status === 'draft') && (
              <span className="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-zinc-800 text-zinc-300 border border-zinc-700">
                <span>Draft / Not Submitted</span>
              </span>
            )}
          </div>
        </div>

        {/* Status Alert Details */}
        {formData.status === 'approved' && (
          <div className="mt-3 p-2.5 rounded-xl bg-emerald-950/30 border border-emerald-500/30 text-emerald-300 text-[11px] flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <Check className="w-4 h-4 shrink-0 text-emerald-400" />
              <span>Your employment profile is verified and approved.</span>
            </div>
            {isFormLocked ? (
              <button
                type="button"
                onClick={() => {
                  if (confirm('Editing an approved form will require HR re-verification. Continue?')) {
                    setIsUnlocked(true);
                  }
                }}
                className="text-[10px] px-2 py-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-amber-300 flex items-center space-x-1 cursor-pointer transition-colors"
              >
                <Unlock className="w-3 h-3" />
                <span>Edit Info</span>
              </button>
            ) : (
              <span className="text-[10px] text-amber-400 font-bold flex items-center space-x-1">
                <Unlock className="w-3 h-3" />
                <span>Unlocked for editing</span>
              </span>
            )}
          </div>
        )}

        {formData.status === 'rejected' && formData.rejection_reason && (
          <div className="mt-3 p-2.5 rounded-xl bg-rose-950/40 border border-rose-500/40 text-rose-300 text-[11px] flex items-start space-x-2">
            <AlertCircle className="w-4 h-4 shrink-0 text-rose-400 mt-0.5" />
            <div>
              <span className="font-bold">HR Feedback: </span>
              <span>{formData.rejection_reason}</span>
            </div>
          </div>
        )}
      </div>

      {/* Navigation Stepper / Tabs */}
      <div className="flex bg-zinc-950 p-1 rounded-xl border border-zinc-800 space-x-1 overflow-x-auto text-[11px] scrollbar-none">
        <button
          type="button"
          onClick={() => setActiveTab('personal')}
          className={`flex-1 py-1.5 px-2 rounded-lg font-bold flex items-center justify-center space-x-1 whitespace-nowrap transition-colors cursor-pointer ${
            activeTab === 'personal' ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'text-zinc-400 hover:text-zinc-200'
          }`}
        >
          <User className="w-3.5 h-3.5" />
          <span>Personal</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('experience')}
          className={`flex-1 py-1.5 px-2 rounded-lg font-bold flex items-center justify-center space-x-1 whitespace-nowrap transition-colors cursor-pointer ${
            activeTab === 'experience' ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'text-zinc-400 hover:text-zinc-200'
          }`}
        >
          <Briefcase className="w-3.5 h-3.5" />
          <span>Experience</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('education')}
          className={`flex-1 py-1.5 px-2 rounded-lg font-bold flex items-center justify-center space-x-1 whitespace-nowrap transition-colors cursor-pointer ${
            activeTab === 'education' ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'text-zinc-400 hover:text-zinc-200'
          }`}
        >
          <GraduationCap className="w-3.5 h-3.5" />
          <span>Education</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('family')}
          className={`flex-1 py-1.5 px-2 rounded-lg font-bold flex items-center justify-center space-x-1 whitespace-nowrap transition-colors cursor-pointer ${
            activeTab === 'family' ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'text-zinc-400 hover:text-zinc-200'
          }`}
        >
          <Users className="w-3.5 h-3.5" />
          <span>Family & Ref</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('documents')}
          className={`flex-1 py-1.5 px-2 rounded-lg font-bold flex items-center justify-center space-x-1 whitespace-nowrap transition-colors cursor-pointer ${
            activeTab === 'documents' ? 'bg-amber-500 text-zinc-950 shadow-sm' : 'text-zinc-400 hover:text-zinc-200'
          }`}
        >
          <Shield className="w-3.5 h-3.5" />
          <span>Docs & Sign</span>
        </button>
      </div>

      {/* ========================================================= */}
      {/* TAB 1: PERSONAL & IDENTITY DETAILS                        */}
      {/* ========================================================= */}
      {activeTab === 'personal' && (
        <div className="space-y-3.5 bg-zinc-900/60 border border-zinc-800/80 p-3.5 rounded-2xl">
          {/* Read-only / Pre-synced Info Alert */}
          <div className="text-[10px] text-zinc-400 bg-zinc-950/70 p-2.5 rounded-xl border border-zinc-800 flex items-center space-x-2">
            <Shield className="w-3.5 h-3.5 text-amber-400 shrink-0" />
            <span>Core account fields (Name, Mobile, DOB, Gender) are pre-synced from your profile.</span>
          </div>

          <div className="grid grid-cols-2 gap-2.5">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Applicant Full Name</label>
              <input
                type="text"
                disabled
                value={formData.fullname || ''}
                className="w-full mt-1 bg-zinc-950/90 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-zinc-300 font-medium opacity-80 cursor-not-allowed"
              />
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Date of Joining</label>
              <input
                type="text"
                disabled
                value={formData.joining_date || 'N/A'}
                className="w-full mt-1 bg-zinc-950/90 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-zinc-300 font-medium opacity-80 cursor-not-allowed"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-2.5">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Father's Name</label>
              <input
                type="text"
                disabled={isFormLocked}
                value={formData.father_name || ''}
                onChange={(e) => setFormData({ ...formData, father_name: e.target.value })}
                placeholder="Father's Name"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Father's Mobile No.</label>
              <input
                type="tel"
                disabled={isFormLocked}
                value={formData.father_mob_no || ''}
                onChange={(e) => setFormData({ ...formData, father_mob_no: e.target.value })}
                placeholder="e.g. 9876543210"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
          </div>

          {/* Identity Card Numbers */}
          <div className="grid grid-cols-2 gap-2.5">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Aadhaar Card Number *</label>
              <input
                type="text"
                disabled={isFormLocked}
                maxLength={14}
                value={formData.aadhaar_no || ''}
                onChange={(e) => setFormData({ ...formData, aadhaar_no: e.target.value })}
                placeholder="XXXX-XXXX-XXXX"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none font-mono"
              />
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">PAN Card Number *</label>
              <input
                type="text"
                disabled={isFormLocked}
                maxLength={10}
                value={formData.pan_no || ''}
                onChange={(e) => setFormData({ ...formData, pan_no: e.target.value.toUpperCase() })}
                placeholder="ABCDE1234F"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none font-mono uppercase"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-2.5">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Marital Status</label>
              <select
                disabled={isFormLocked}
                value={formData.maratial_status || 'Single'}
                onChange={(e) => setFormData({ ...formData, maratial_status: e.target.value })}
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
              >
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorced">Divorced</option>
                <option value="Widowed">Widowed</option>
              </select>
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Date of Birth</label>
              <input
                type="date"
                disabled={isFormLocked}
                value={formData.date_of_birth || ''}
                onChange={(e) => setFormData({ ...formData, date_of_birth: e.target.value })}
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
              />
            </div>
          </div>

          {/* Conditional Marriage Details */}
          {formData.maratial_status === 'Married' && (
            <div className="grid grid-cols-2 gap-2.5 p-2.5 bg-zinc-950/50 rounded-xl border border-zinc-800/80">
              <div>
                <label className="text-[10px] font-semibold text-zinc-400">Date of Marriage</label>
                <input
                  type="date"
                  disabled={isFormLocked}
                  value={formData.date_of_marriage || ''}
                  onChange={(e) => setFormData({ ...formData, date_of_marriage: e.target.value })}
                  className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
                />
              </div>
              <div>
                <label className="text-[10px] font-semibold text-zinc-400">Spouse Name</label>
                <input
                  type="text"
                  disabled={isFormLocked}
                  value={formData.spouse_name || ''}
                  onChange={(e) => setFormData({ ...formData, spouse_name: e.target.value })}
                  placeholder="Husband / Wife Name"
                  className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
                />
              </div>
            </div>
          )}

          <div className="grid grid-cols-2 gap-2.5">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Blood Group</label>
              <select
                disabled={isFormLocked}
                value={formData.blood_group || ''}
                onChange={(e) => setFormData({ ...formData, blood_group: e.target.value })}
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-none"
              >
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Height & Weight</label>
              <input
                type="text"
                disabled={isFormLocked}
                value={formData.height_weight || ''}
                onChange={(e) => setFormData({ ...formData, height_weight: e.target.value })}
                placeholder="e.g. 5'8&quot; / 70 kg"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
          </div>

          {/* Addresses */}
          <div className="space-y-2.5">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Present Address</label>
              <textarea
                rows={2}
                disabled={isFormLocked}
                value={formData.present_address || ''}
                onChange={(e) => setFormData({ ...formData, present_address: e.target.value })}
                placeholder="Current residence address"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
            <div className="grid grid-cols-2 gap-2.5">
              <div>
                <label className="text-[10px] font-semibold text-zinc-400">Present Contact No.</label>
                <input
                  type="tel"
                  disabled={isFormLocked}
                  value={formData.present_contact_no || formData.mobile || ''}
                  onChange={(e) => setFormData({ ...formData, present_contact_no: e.target.value })}
                  placeholder="Primary Phone"
                  className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
                />
              </div>
              <div>
                <label className="text-[10px] font-semibold text-zinc-400">Permanent Contact No.</label>
                <input
                  type="tel"
                  disabled={isFormLocked}
                  value={formData.permanent_contact_no || ''}
                  onChange={(e) => setFormData({ ...formData, permanent_contact_no: e.target.value })}
                  placeholder="Alternate Phone"
                  className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
                />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between">
                <label className="text-[10px] font-semibold text-zinc-400">Permanent Address</label>
                {!isFormLocked && (
                  <button
                    type="button"
                    onClick={() => setFormData({ ...formData, permanent_address: formData.present_address })}
                    className="text-[9px] text-amber-400 hover:underline"
                  >
                    Same as Present
                  </button>
                )}
              </div>
              <textarea
                rows={2}
                disabled={isFormLocked}
                value={formData.permanent_address || ''}
                onChange={(e) => setFormData({ ...formData, permanent_address: e.target.value })}
                placeholder="Native / Permanent residence address"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl p-2.5 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
          </div>

          {/* Company Connections */}
          <div className="space-y-2">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">
                Any relative or friend(s) in our Company? (If yes, give details)
              </label>
              <input
                type="text"
                disabled={isFormLocked}
                value={formData.relative_friend_in_company || ''}
                onChange={(e) => setFormData({ ...formData, relative_friend_in_company: e.target.value })}
                placeholder="e.g. None or Name, Designation"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">
                Ever applied or interviewed in our Company before? (If yes, give details)
              </label>
              <input
                type="text"
                disabled={isFormLocked}
                value={formData.previously_applied || ''}
                onChange={(e) => setFormData({ ...formData, previously_applied: e.target.value })}
                placeholder="e.g. No or Month / Year, Position"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
          </div>
        </div>
      )}

      {/* ========================================================= */}
      {/* TAB 2: WORK EXPERIENCE & COMPENSATION                    */}
      {/* ========================================================= */}
      {activeTab === 'experience' && (
        <div className="space-y-3.5 bg-zinc-900/60 border border-zinc-800/80 p-3.5 rounded-2xl">
          {/* Fresher vs Experience Toggle */}
          <div className="flex bg-zinc-950 p-1 rounded-xl border border-zinc-800">
            <button
              type="button"
              disabled={isFormLocked}
              onClick={() => setFormData({ ...formData, fresher_experience: 'Fresher' })}
              className={`flex-1 py-1.5 text-xs font-bold rounded-lg transition-colors cursor-pointer ${
                formData.fresher_experience === 'Fresher'
                  ? 'bg-amber-500 text-zinc-950 shadow-sm'
                  : 'text-zinc-400 hover:text-white'
              }`}
            >
              Fresher
            </button>
            <button
              type="button"
              disabled={isFormLocked}
              onClick={() => setFormData({ ...formData, fresher_experience: 'Experience' })}
              className={`flex-1 py-1.5 text-xs font-bold rounded-lg transition-colors cursor-pointer ${
                formData.fresher_experience === 'Experience'
                  ? 'bg-amber-500 text-zinc-950 shadow-sm'
                  : 'text-zinc-400 hover:text-white'
              }`}
            >
              Experienced
            </button>
          </div>

          <div className="grid grid-cols-3 gap-2">
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Total Exp. (Yrs)</label>
              <input
                type="text"
                disabled={isFormLocked || formData.fresher_experience === 'Fresher'}
                value={formData.fresher_experience === 'Fresher' ? '0' : formData.total_experience_years || ''}
                onChange={(e) => setFormData({ ...formData, total_experience_years: e.target.value })}
                placeholder="e.g. 2.5"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Last Salary</label>
              <input
                type="text"
                disabled={isFormLocked || formData.fresher_experience === 'Fresher'}
                value={formData.last_salary || ''}
                onChange={(e) => setFormData({ ...formData, last_salary: e.target.value })}
                placeholder="Rs. PM"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
            <div>
              <label className="text-[10px] font-semibold text-zinc-400">Present / Offered</label>
              <input
                type="text"
                disabled={isFormLocked}
                value={formData.present_salary || ''}
                onChange={(e) => setFormData({ ...formData, present_salary: e.target.value })}
                placeholder="Rs. PM"
                className="w-full mt-1 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-600 focus:border-amber-400 focus:outline-none"
              />
            </div>
          </div>

          {/* Past Employment Experience Table */}
          {formData.fresher_experience === 'Experience' && (
            <div className="space-y-2 pt-2 border-t border-zinc-800">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-bold text-white flex items-center space-x-1.5">
                  <Building className="w-3.5 h-3.5 text-amber-400" />
                  <span>Work Experience Details</span>
                </span>
                {!isFormLocked && (
                  <button
                    type="button"
                    onClick={() =>
                      setExperienceList([
                        ...experienceList,
                        { company_name: '', from_period: '', to_period: '', position_work: '', ctc_pm: '' },
                      ])
                    }
                    className="text-[10px] px-2 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/40 rounded-lg flex items-center space-x-1 cursor-pointer hover:bg-amber-500/30"
                  >
                    <Plus className="w-3 h-3" />
                    <span>Add Company</span>
                  </button>
                )}
              </div>

              {experienceList.length === 0 ? (
                <p className="text-[10px] text-zinc-500 italic p-3 text-center bg-zinc-950/40 rounded-xl border border-zinc-800/60">
                  No previous companies added. Click "+ Add Company" to list work history.
                </p>
              ) : (
                <div className="space-y-2">
                  {experienceList.map((exp, idx) => (
                    <div key={idx} className="p-2.5 bg-zinc-950/80 rounded-xl border border-zinc-800 space-y-2 relative">
                      <div className="flex items-center justify-between">
                        <span className="text-[10px] font-bold text-amber-400">Employer #{idx + 1}</span>
                        {!isFormLocked && (
                          <button
                            type="button"
                            onClick={() => setExperienceList(experienceList.filter((_, i) => i !== idx))}
                            className="text-rose-400 hover:text-rose-300 p-0.5"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        )}
                      </div>

                      <div>
                        <input
                          type="text"
                          disabled={isFormLocked}
                          value={exp.company_name}
                          onChange={(e) => {
                            const updated = [...experienceList];
                            updated[idx].company_name = e.target.value;
                            setExperienceList(updated);
                          }}
                          placeholder="Name of the Company"
                          className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                        />
                      </div>

                      <div className="grid grid-cols-2 gap-2">
                        <input
                          type="text"
                          disabled={isFormLocked}
                          value={exp.from_period}
                          onChange={(e) => {
                            const updated = [...experienceList];
                            updated[idx].from_period = e.target.value;
                            setExperienceList(updated);
                          }}
                          placeholder="From (e.g. Jan 2022)"
                          className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                        />
                        <input
                          type="text"
                          disabled={isFormLocked}
                          value={exp.to_period}
                          onChange={(e) => {
                            const updated = [...experienceList];
                            updated[idx].to_period = e.target.value;
                            setExperienceList(updated);
                          }}
                          placeholder="To (e.g. Feb 2024)"
                          className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                        />
                      </div>

                      <div className="grid grid-cols-2 gap-2">
                        <input
                          type="text"
                          disabled={isFormLocked}
                          value={exp.position_work}
                          onChange={(e) => {
                            const updated = [...experienceList];
                            updated[idx].position_work = e.target.value;
                            setExperienceList(updated);
                          }}
                          placeholder="Position held / Sector"
                          className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                        />
                        <input
                          type="text"
                          disabled={isFormLocked}
                          value={exp.ctc_pm}
                          onChange={(e) => {
                            const updated = [...experienceList];
                            updated[idx].ctc_pm = e.target.value;
                            setExperienceList(updated);
                          }}
                          placeholder="CTC (PM)"
                          className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                        />
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}
        </div>
      )}

      {/* ========================================================= */}
      {/* TAB 3: EDUCATIONAL & PROFESSIONAL QUALIFICATION          */}
      {/* ========================================================= */}
      {activeTab === 'education' && (
        <div className="space-y-3.5 bg-zinc-900/60 border border-zinc-800/80 p-3.5 rounded-2xl">
          <div className="flex items-center justify-between">
            <div>
              <h4 className="text-xs font-bold text-white flex items-center space-x-1.5">
                <GraduationCap className="w-4 h-4 text-amber-400" />
                <span>Qualifications (School, College, Degree)</span>
              </h4>
              <p className="text-[10px] text-zinc-400">List all your educational degrees or certifications</p>
            </div>
            {!isFormLocked && (
              <button
                type="button"
                onClick={() =>
                  setEducationList([
                    ...educationList,
                    {
                      school_college: '',
                      from_period: '',
                      to_period: '',
                      division_grade: '',
                      percentage: '',
                      subjects: '',
                      scholarships_distinctions: '',
                    },
                  ])
                }
                className="text-[10px] px-2 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/40 rounded-lg flex items-center space-x-1 cursor-pointer hover:bg-amber-500/30"
              >
                <Plus className="w-3 h-3" />
                <span>Add Record</span>
              </button>
            )}
          </div>

          <div className="space-y-3">
            {educationList.map((edu, idx) => (
              <div key={idx} className="p-3 bg-zinc-950/80 rounded-xl border border-zinc-800 space-y-2 relative">
                <div className="flex items-center justify-between">
                  <span className="text-[10px] font-bold text-amber-400">Qualification #{idx + 1}</span>
                  {!isFormLocked && educationList.length > 1 && (
                    <button
                      type="button"
                      onClick={() => setEducationList(educationList.filter((_, i) => i !== idx))}
                      className="text-rose-400 hover:text-rose-300 p-0.5"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                <div>
                  <input
                    type="text"
                    disabled={isFormLocked}
                    value={edu.school_college}
                    onChange={(e) => {
                      const updated = [...educationList];
                      updated[idx].school_college = e.target.value;
                      setEducationList(updated);
                    }}
                    placeholder="School / College / University or Institution"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                  />
                </div>

                <div className="grid grid-cols-4 gap-1.5">
                  <input
                    type="text"
                    disabled={isFormLocked}
                    value={edu.from_period}
                    onChange={(e) => {
                      const updated = [...educationList];
                      updated[idx].from_period = e.target.value;
                      setEducationList(updated);
                    }}
                    placeholder="From Yr"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                  />
                  <input
                    type="text"
                    disabled={isFormLocked}
                    value={edu.to_period}
                    onChange={(e) => {
                      const updated = [...educationList];
                      updated[idx].to_period = e.target.value;
                      setEducationList(updated);
                    }}
                    placeholder="To Yr"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                  />
                  <input
                    type="text"
                    disabled={isFormLocked}
                    value={edu.division_grade}
                    onChange={(e) => {
                      const updated = [...educationList];
                      updated[idx].division_grade = e.target.value;
                      setEducationList(updated);
                    }}
                    placeholder="Div / Grade"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                  />
                  <input
                    type="text"
                    disabled={isFormLocked}
                    value={edu.percentage}
                    onChange={(e) => {
                      const updated = [...educationList];
                      updated[idx].percentage = e.target.value;
                      setEducationList(updated);
                    }}
                    placeholder="% Age"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                  />
                </div>

                <div className="grid grid-cols-2 gap-2">
                  <input
                    type="text"
                    disabled={isFormLocked}
                    value={edu.subjects}
                    onChange={(e) => {
                      const updated = [...educationList];
                      updated[idx].subjects = e.target.value;
                      setEducationList(updated);
                    }}
                    placeholder="Subjects / Major"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                  />
                  <input
                    type="text"
                    disabled={isFormLocked}
                    value={edu.scholarships_distinctions}
                    onChange={(e) => {
                      const updated = [...educationList];
                      updated[idx].scholarships_distinctions = e.target.value;
                      setEducationList(updated);
                    }}
                    placeholder="Scholarships / Distinctions"
                    className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                  />
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* ========================================================= */}
      {/* TAB 4: FAMILY DETAILS & PROFESSIONAL REFERENCES          */}
      {/* ========================================================= */}
      {activeTab === 'family' && (
        <div className="space-y-4 bg-zinc-900/60 border border-zinc-800/80 p-3.5 rounded-2xl">
          {/* Professional References */}
          <div className="space-y-2.5">
            <h4 className="text-xs font-bold text-white flex items-center space-x-1.5">
              <Users className="w-4 h-4 text-amber-400" />
              <span>Two Professional References (Non-Relative)</span>
            </h4>
            <p className="text-[10px] text-zinc-400">
              Provide two persons other than relatives to whom company may write for reference.
            </p>

            <div className="space-y-2">
              {references.slice(0, 2).map((ref, idx) => (
                <div key={idx} className="p-2.5 bg-zinc-950/80 rounded-xl border border-zinc-800 space-y-2">
                  <span className="text-[10px] font-bold text-amber-400">Reference #{idx + 1}</span>
                  <div className="grid grid-cols-3 gap-1.5">
                    <input
                      type="text"
                      disabled={isFormLocked}
                      value={ref.name}
                      onChange={(e) => {
                        const updated = [...references];
                        updated[idx].name = e.target.value;
                        setReferences(updated);
                      }}
                      placeholder="Full Name"
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                    />
                    <input
                      type="text"
                      disabled={isFormLocked}
                      value={ref.designation_relation}
                      onChange={(e) => {
                        const updated = [...references];
                        updated[idx].designation_relation = e.target.value;
                        setReferences(updated);
                      }}
                      placeholder="Designation / Rel."
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                    />
                    <input
                      type="tel"
                      disabled={isFormLocked}
                      value={ref.contact_no}
                      onChange={(e) => {
                        const updated = [...references];
                        updated[idx].contact_no = e.target.value;
                        setReferences(updated);
                      }}
                      placeholder="Contact No."
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Family Details */}
          <div className="space-y-2.5 pt-2 border-t border-zinc-800">
            <div className="flex items-center justify-between">
              <div>
                <h4 className="text-xs font-bold text-white flex items-center space-x-1.5">
                  <Users className="w-4 h-4 text-emerald-400" />
                  <span>Family Details</span>
                </h4>
                <p className="text-[10px] text-zinc-400">Members of your immediate family (Parents, Spouse, Children)</p>
              </div>
              {!isFormLocked && (
                <button
                  type="button"
                  onClick={() =>
                    setFamilyList([...familyList, { name: '', relation: '', occupation: '', date_of_birth: '' }])
                  }
                  className="text-[10px] px-2 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 rounded-lg flex items-center space-x-1 cursor-pointer hover:bg-emerald-500/30"
                >
                  <Plus className="w-3 h-3" />
                  <span>Add Member</span>
                </button>
              )}
            </div>

            <div className="space-y-2">
              {familyList.map((fam, idx) => (
                <div key={idx} className="p-2.5 bg-zinc-950/80 rounded-xl border border-zinc-800 space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="text-[10px] font-bold text-emerald-400">Member #{idx + 1}</span>
                    {!isFormLocked && familyList.length > 1 && (
                      <button
                        type="button"
                        onClick={() => setFamilyList(familyList.filter((_, i) => i !== idx))}
                        className="text-rose-400 hover:text-rose-300 p-0.5"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    )}
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <input
                      type="text"
                      disabled={isFormLocked}
                      value={fam.name}
                      onChange={(e) => {
                        const updated = [...familyList];
                        updated[idx].name = e.target.value;
                        setFamilyList(updated);
                      }}
                      placeholder="Name"
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                    />
                    <input
                      type="text"
                      disabled={isFormLocked}
                      value={fam.relation}
                      onChange={(e) => {
                        const updated = [...familyList];
                        updated[idx].relation = e.target.value;
                        setFamilyList(updated);
                      }}
                      placeholder="Relation (Father/Mother/etc)"
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-2">
                    <input
                      type="text"
                      disabled={isFormLocked}
                      value={fam.occupation}
                      onChange={(e) => {
                        const updated = [...familyList];
                        updated[idx].occupation = e.target.value;
                        setFamilyList(updated);
                      }}
                      placeholder="Occupation"
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-zinc-600 focus:outline-none"
                    />
                    <input
                      type="date"
                      disabled={isFormLocked}
                      value={fam.date_of_birth}
                      onChange={(e) => {
                        const updated = [...familyList];
                        updated[idx].date_of_birth = e.target.value;
                        setFamilyList(updated);
                      }}
                      className="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-2 py-1 text-xs text-white focus:outline-none"
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* ========================================================= */}
      {/* TAB 5: DOCUMENTS UPLOAD & DECLARATION                     */}
      {/* ========================================================= */}
      {activeTab === 'documents' && (
        <div className="space-y-4 bg-zinc-900/60 border border-zinc-800/80 p-3.5 rounded-2xl">
          <div className="text-[11px] font-bold text-white flex items-center space-x-1.5">
            <FileText className="w-4 h-4 text-amber-400" />
            <span>Document Attachments & Photo</span>
          </div>

          <div className="grid grid-cols-2 gap-2.5">
            {/* 1. Photograph */}
            <div className="p-3 bg-zinc-950/80 rounded-xl border border-zinc-800 flex flex-col items-center justify-center text-center space-y-2">
              <span className="text-[10px] font-bold text-zinc-300">Recent Photograph</span>
              {formData.photograph || formData.photograph_url ? (
                <div className="w-16 h-16 rounded-xl overflow-hidden border border-amber-500/40 shadow-sm relative group">
                  <img
                    src={formData.photograph_url || formData.photograph}
                    alt="Photo"
                    className="w-full h-full object-cover"
                  />
                </div>
              ) : (
                <div className="w-16 h-16 rounded-xl bg-zinc-900 border border-dashed border-zinc-700 flex items-center justify-center text-zinc-500">
                  <User className="w-6 h-6" />
                </div>
              )}
              {!isFormLocked && (
                <label className="text-[9px] px-2.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/40 rounded-lg cursor-pointer hover:bg-amber-500/30 transition-colors">
                  Upload Photo
                  <input
                    type="file"
                    accept="image/*"
                    className="hidden"
                    onChange={(e) => e.target.files?.[0] && handleFileUpload('photograph', e.target.files[0])}
                  />
                </label>
              )}
            </div>

            {/* 2. Signature */}
            <div className="p-3 bg-zinc-950/80 rounded-xl border border-zinc-800 flex flex-col items-center justify-center text-center space-y-2">
              <span className="text-[10px] font-bold text-zinc-300">Applicant Signature</span>
              {formData.signature_doc || formData.signature_url ? (
                <div className="w-24 h-14 rounded-xl overflow-hidden border border-emerald-500/40 bg-white p-1">
                  <img
                    src={formData.signature_url || formData.signature_doc}
                    alt="Signature"
                    className="w-full h-full object-contain"
                  />
                </div>
              ) : (
                <div className="w-24 h-14 rounded-xl bg-zinc-900 border border-dashed border-zinc-700 flex items-center justify-center text-zinc-500 text-[10px]">
                  Sign on paper
                </div>
              )}
              {!isFormLocked && (
                <label className="text-[9px] px-2.5 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 rounded-lg cursor-pointer hover:bg-emerald-500/30 transition-colors">
                  Upload Sign
                  <input
                    type="file"
                    accept="image/*"
                    className="hidden"
                    onChange={(e) => e.target.files?.[0] && handleFileUpload('signature_doc', e.target.files[0])}
                  />
                </label>
              )}
            </div>

            {/* 3. Aadhaar Card */}
            <div className="p-3 bg-zinc-950/80 rounded-xl border border-zinc-800 flex flex-col items-center justify-center text-center space-y-2">
              <span className="text-[10px] font-bold text-zinc-300">Aadhaar Card Copy</span>
              {formData.aadhaar_card_doc || formData.aadhaar_card_url ? (
                <div className="text-[10px] text-emerald-400 font-semibold flex items-center space-x-1">
                  <CheckCircle2 className="w-4 h-4" />
                  <span>Attached</span>
                </div>
              ) : (
                <span className="text-[10px] text-zinc-500">Not uploaded</span>
              )}
              {!isFormLocked && (
                <label className="text-[9px] px-2.5 py-1 bg-sky-500/20 text-sky-300 border border-sky-500/40 rounded-lg cursor-pointer hover:bg-sky-500/30 transition-colors">
                  Attach Aadhaar
                  <input
                    type="file"
                    accept="image/*,application/pdf"
                    className="hidden"
                    onChange={(e) => e.target.files?.[0] && handleFileUpload('aadhaar_card_doc', e.target.files[0])}
                  />
                </label>
              )}
            </div>

            {/* 4. PAN Card */}
            <div className="p-3 bg-zinc-950/80 rounded-xl border border-zinc-800 flex flex-col items-center justify-center text-center space-y-2">
              <span className="text-[10px] font-bold text-zinc-300">PAN Card Copy</span>
              {formData.pan_card_doc || formData.pan_card_url ? (
                <div className="text-[10px] text-emerald-400 font-semibold flex items-center space-x-1">
                  <CheckCircle2 className="w-4 h-4" />
                  <span>Attached</span>
                </div>
              ) : (
                <span className="text-[10px] text-zinc-500">Not uploaded</span>
              )}
              {!isFormLocked && (
                <label className="text-[9px] px-2.5 py-1 bg-sky-500/20 text-sky-300 border border-sky-500/40 rounded-lg cursor-pointer hover:bg-sky-500/30 transition-colors">
                  Attach PAN
                  <input
                    type="file"
                    accept="image/*,application/pdf"
                    className="hidden"
                    onChange={(e) => e.target.files?.[0] && handleFileUpload('pan_card_doc', e.target.files[0])}
                  />
                </label>
              )}
            </div>
          </div>

          {/* Legal Declaration */}
          <div className="p-3 bg-zinc-950/90 rounded-xl border border-zinc-800 space-y-2.5">
            <label className="flex items-start space-x-2.5 cursor-pointer">
              <input
                type="checkbox"
                disabled={isFormLocked}
                checked={declared}
                onChange={(e) => setDeclared(e.target.checked)}
                className="mt-0.5 rounded border-zinc-700 text-amber-500 focus:ring-amber-500/20 bg-zinc-900"
              />
              <span className="text-[10px] text-zinc-300 leading-relaxed select-none">
                <strong>Declaration:</strong> I hereby declare that the above particulars are true to the extent of my knowledge and I understand that any misrepresentation or omission of facts can call for cause of disqualification.
              </span>
            </label>

            <div className="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-800/80">
              <div>
                <label className="text-[10px] text-zinc-400 font-semibold">Place / City</label>
                <input
                  type="text"
                  disabled={isFormLocked}
                  value={formData.declaration_place || ''}
                  onChange={(e) => setFormData({ ...formData, declaration_place: e.target.value })}
                  placeholder="e.g. Noida / Delhi"
                  className="w-full mt-1 bg-zinc-900 border border-zinc-800 rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none"
                />
              </div>
              <div>
                <label className="text-[10px] text-zinc-400 font-semibold">Date</label>
                <input
                  type="date"
                  disabled={isFormLocked}
                  value={formData.declaration_date || ''}
                  onChange={(e) => setFormData({ ...formData, declaration_date: e.target.value })}
                  className="w-full mt-1 bg-zinc-900 border border-zinc-800 rounded-lg px-2 py-1 text-xs text-white focus:outline-none"
                />
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Bottom Sticky Action Bar */}
      <div className="bg-zinc-950 p-3 rounded-2xl border border-zinc-800 shadow-xl flex items-center justify-between space-x-2">
        <button
          type="button"
          onClick={onBack}
          className="px-3 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 text-xs font-semibold flex items-center space-x-1.5 transition-colors cursor-pointer"
        >
          <ArrowLeft className="w-4 h-4" />
          <span>Back</span>
        </button>

        {!isFormLocked ? (
          <div className="flex items-center space-x-2">
            <button
              type="button"
              disabled={isSaving}
              onClick={() => handleSubmit('draft')}
              className="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-bold flex items-center space-x-1.5 transition-colors cursor-pointer"
            >
              <Save className="w-3.5 h-3.5 text-zinc-400" />
              <span>{isSaving ? 'Saving...' : 'Save Draft'}</span>
            </button>

            <button
              type="button"
              disabled={isSaving}
              onClick={() => handleSubmit('submit')}
              className="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 active:from-amber-600 active:to-amber-500 text-zinc-950 text-xs font-bold flex items-center space-x-1.5 transition-colors shadow-md shadow-amber-950/30 cursor-pointer"
            >
              <Send className="w-3.5 h-3.5" />
              <span>{isSaving ? 'Submitting...' : 'Submit to HR'}</span>
            </button>
          </div>
        ) : (
          <div className="text-[11px] text-emerald-400 font-bold flex items-center space-x-1.5">
            <Lock className="w-4 h-4" />
            <span>Form is Locked & Approved</span>
          </div>
        )}
      </div>
    </div>
  );
};
