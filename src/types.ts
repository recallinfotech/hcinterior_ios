export type PhaseType = 'Phase 1' | 'Phase 2' | 'Phase 3' | 'Phase 4';
export type StatusType = 'Pending' | 'Approved' | 'Draft' | 'In Progress' | 'Accepted' | 'Order Placed' | 'Received' | 'Rejected' | 'Hold';

export interface ClientProject {
  id: string; // e.g., 'HC101806'
  clientIdNum?: number; // e.g., 529 or 520
  name: string; // e.g., 'Shubhra Chauhan'
  date: string; // e.g., '2026-07-28 17:33:05'
  salesManager: string; // e.g., 'Ashish Yadav'
  assignedTeam: {
    designer?: string; // e.g., 'Mansi Goyal, Ayush'
    projectManager?: string; // e.g., 'Abhishek Bhati'
  };
  ktRequest: {
    status: 'Accepted' | 'Pending' | 'NA';
    date: string; // e.g. '24 July 2026' or 'NA'
  };
  validationDate: string; // e.g. '25 July 2026'
  phase: PhaseType;
  email?: string;
  mobile?: string;
  address?: string;
  city?: string;
  overallProgress?: number; // % e.g. 38%
  activeSubPhase?: string;
  freezeBOQAmount?: number;
  amountReceived?: number;
}

export interface EscalationComment {
  id: number | string;
  escalation_id: number | string;
  user_id: number | string;
  user_name: string;
  comment: string;
  created_at: string;
}

export interface EscalationItem {
  id: string | number;
  client_id?: number | string;
  client_name?: string;
  client_sr_id?: string;
  client_phone?: string;
  raised_by?: number | string;
  raised_by_name?: string;
  assigned_to?: number | string;
  assigned_to_name?: string;
  remark?: string;
  status: string;
  created_at?: string;
  updated_at?: string | null;
  comments?: EscalationComment[];
  total_comments?: number;

  // Fallbacks / Legacy UI compatibility
  clientId?: string;
  clientName?: string;
  category?: string;
  severity?: string;
  description?: string;
  assignedTo?: string;
  createdAt?: string;
  resolvedAt?: string;
}

export interface DetailSectionItem {
  id: string;
  title: string;
  key: string;
  completed: boolean;
  count?: number;
}

export interface BOQItem {
  quotationNo: string;
  date: string;
  phone: string;
  gTotal: number;
  siteHandling: number;
  toBePaid: number;
  status: 'Draft' | 'Approved' | 'Pending';
}

export interface MOMItem {
  id: string;
  fileName: string;
  fileUrl: string;
  date: string;
  thumbnailUrl?: string;
}

export interface CivilDrawing {
  id: string;
  fileName: string;
  fileUrl: string;
  designType: string;
  status: 'Approved' | 'Pending' | 'Rejected';
  uploadedBy: string;
  date: string;
}

export interface KTRecord {
  requestType: string;
  requestDate: string;
  status: 'Approved' | 'Pending' | 'In Progress';
  ktCheck: 'Done' | 'Pending';
}

export interface Design3DRequest {
  id: string;
  requestType: string;
  requestDate: string;
  status: 'Pending' | 'Approved' | 'Rejected';
  files?: { name: string; url: string; date: string }[];
}

export interface ValidationDrawing {
  id: string;
  fileName: string;
  fileUrl: string;
  designType: string;
  url?: string;
  status: 'Approved' | 'Pending' | 'Reject';
  uploadedBy: string;
  date: string;
  rejectionReason?: string;
}

export interface HandoverItem {
  id: string;
  clientId?: string;
  clientName?: string;
  title: string;
  handoverType: 'Final Site Handover' | 'Interim Handover' | 'Key Handover' | 'Snag List Signoff' | 'Warranty & Manuals' | string;
  fileName: string;
  fileUrl: string;
  fileSize?: string;
  fileType?: string;
  handoverDate: string;
  handoverBy: string;
  handoverTo?: string;
  status: 'Completed' | 'Pending Sign-off' | 'Draft' | 'Approved';
  remarks?: string;
  createdAt: string;
}

export interface BOMRecord {
  id: string;
  clientId?: string;
  client_id?: number;
  clientName?: string;
  client_sr_id?: string;
  fileName: string;
  category: string;
  type: string;
  fileUrl: string;
  imageUrl?: string;
  date: string;
  status: 'Accepted' | 'Pending' | 'Rejected' | string;
  parentId?: string;
  children?: BOMRecord[];
  remark?: string;
  upload_file?: string[];
  vendor?: string;
  design_type?: string;
}

export interface PurchaseRequestItem {
  id: string;
  fileName: string;
  bomName: string;
  vendorName: string;
  vendorImage?: string;
  fileUrl: string;
  date: string;
  status: 'Order Placed' | 'Pending' | 'Delivered' | 'Received';
  isOnSite?: boolean;
}

export interface DispatchQuery {
  id: number | string;
  dispatch_id?: number | string;
  client_id?: number | string;
  remarks: string;
  uploaded_by?: number | string;
  uploaded_by_name?: string;
  file_name?: string;
  file_url?: string;
  created_date?: string;
  updated_date?: string;
}

export interface DispatchItem {
  id: string | number;
  client_id?: number | string;
  clientName: string;
  clientId: string;
  client_sr_id?: string;
  client_name?: string;
  itemName: string;
  file_name?: string;
  status: string;
  status_approve_by?: string;
  status_approve_date?: string;
  upload_file?: string[];
  upload_url?: string;
  remark?: string;
  created_date?: string;
  updated_date?: string;
  is_return?: any;
  return_remark?: any;
  is_recived?: any;
  recived_date?: string;
  queries?: DispatchQuery[];
  total_queries?: number;
  imageUrl?: string;
  date?: string;
  requestType?: string;
  dispatchType?: string;
}

export interface LooseFurnitureRecord {
  id: string;
  requestType: string;
  requestDate: string;
  dispatchDate: string;
  status: 'Approved' | 'Pending';
  purchases: {
    fileName: string;
    fileUrl: string;
    url: string;
    date: string;
    status: string;
  }[];
}

export interface PaymentRecord {
  id: string;
  amount: number;
  paymentMode: 'Cash' | 'UPI' | 'Bank Transfer' | 'Cheque';
  paymentDate: string;
  transactionDetail: string;
  status: 'Paid' | 'Pending' | 'Failed';
  date: string;
}

export interface RemarkItem {
  id: string;
  text: string;
  author: string;
  date: string;
  sectionKey?: string;
}

export interface QCDesignItem {
  id: string | number;
  design_id?: number;
  clientName: string;
  clientId: string;
  client_id?: number;
  client_sr_id?: string;
  client_name?: string;
  fileName?: string;
  fileUrl?: string;
  isFinal: 'Yes' | 'No' | number | string;
  is_final?: number | string;
  designType?: string;
  design_type?: string;
  designStyle?: string;
  design_style?: string;
  url?: string;
  status: 'Pending' | 'Reject' | 'Approved' | string;
  uploadedBy?: string;
  uploaded_by?: number | string;
  uploaded_by_name?: string;
  date?: string;
  created_at?: string;
  remark?: string;
  upload_file?: string[];
  upload_url?: string;
}

export interface FinalValidationItem {
  id: string | number;
  design_id?: number;
  clientName: string;
  clientId: string;
  client_id?: number;
  client_sr_id?: string;
  client_name?: string;
  fileName: string;
  fileUrl: string;
  isFinal: 'Yes' | 'No' | string | number;
  is_final?: number | string;
  designType: string;
  design_type?: string;
  designStyle?: string;
  design_style?: string;
  url: string;
  status: 'Approved' | 'Pending' | 'Reject' | string;
  uploadedBy: string;
  uploaded_by_name?: string;
  date: string;
  created_at?: string;
  remark?: string;
  upload_file?: string[];
  upload_url?: string;
}

export interface LooseFurnitureItem {
  id: string | number;
  clientName: string;
  clientId: string;
  client_id?: number;
  client_sr_id?: string;
  client_name?: string;
  fileName: string;
  file_name?: string;
  status: string;
  status_approve_by?: string;
  status_approve_date?: string;
  upload_file?: string[];
  upload_url?: string;
  remark?: string;
  date: string;
  created_date?: string;
  updated_date?: string;
}

export interface ApiBoqItem {
  id: number;
  client_id: number;
  lead_id?: number;
  ref_no: string;
  proposal_date: string;
  phone: string;
  status: string;
  exclusive_product?: boolean;
  can_view_price?: boolean;
  pdf_with_price: string | null;
  pdf_without_price: string | null;
  grand_total: number | null;
  site_handling: number | null;
  total_to_be_paid: number | null;
}

export interface ApiBoqListResponse {
  status: boolean;
  message: string;
  client?: {
    client_id: number;
    client_sr_id: string;
    client_name: string;
    email: string | null;
    mobile: string | null;
    lead_id: number;
  };
  permissions?: {
    can_view_price: boolean;
    can_view_without_price_pdf: boolean;
  };
  page?: number;
  limit?: number;
  total?: number;
  total_pages?: number;
  has_next_page?: boolean;
  has_previous_page?: boolean;
  latest_boq?: ApiBoqItem | null;
  old_boq?: ApiBoqItem[];
}

export interface ApiExecutionTask {
  id: number;
  client_id: number;
  task_name: string;
  start_date: string | null;
  end_date: string | null;
  duration: number;
  is_validation: boolean;
}

export interface ApiExecutionTimelineResponse {
  status: boolean;
  message: string;
  client?: {
    client_id: number;
    client_sr_id: string;
    client_name: string;
    email?: string | null;
    mobile?: string | null;
  };
  permissions?: {
    can_view: boolean;
    can_edit: boolean;
  };
  total?: number;
  data?: ApiExecutionTask[];
}

export interface ApiUpdateTimelineTaskPayload {
  id: number;
  start_date: string;
  end_date: string;
}

export interface ApiUpdateTimelineResponse {
  status: boolean;
  message: string;
  client?: {
    client_id: number;
    client_sr_id: string;
    client_name: string;
  };
  total_updated?: number;
  total_errors?: number;
  updated_tasks?: {
    id: number;
    task_name: string;
    start_date: string;
    end_date: string;
    duration: number;
  }[];
  errors?: any[];
}


export interface OnSitePurchaseItem {
  id: string | number;
  clientName: string;
  clientId: string;
  client_id?: number;
  client_sr_id?: string;
  client_name?: string;
  fileName: string;
  fileUrl: string;
  status: 'Recieved' | 'Pending' | 'Approved' | string;
  date: string;
  created_date?: string;
  updated_date?: string;
  status_approve_date?: string;
  status_approve_by?: string;
  remark?: string;
  uploaded_by?: string;
  upload_file?: string[];
  upload_url?: string;
  brand?: string;
  message?: string;
  site_message?: string;
}
