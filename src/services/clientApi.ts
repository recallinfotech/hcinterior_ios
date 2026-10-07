import { ClientProject, PhaseType, QCDesignItem, EscalationItem, EscalationComment, FinalValidationItem, OnSitePurchaseItem, OnSitePurchaseRequestItem, OnSitePurchaseLineItem, OnSitePurchasePhoto, OnSitePurchaseFormRow, PurchaseItemCategory, PurchaseItemBrand, PurchaseItemCategoryResponse, PurchaseItemBrandResponse, LooseFurnitureItem, DispatchItem, DispatchQuery, BOMRecord, ApiBoqItem, ApiBoqListResponse, ApiExecutionTask, ApiExecutionTimelineResponse, ApiUpdateTimelineTaskPayload, ApiUpdateTimelineResponse } from '../types';
import { isMobileApkEnvironment } from './authApi';

export interface ApiAssignedTeamMember {
  role: string;
  name: string;
}

export interface ApiClientItem {
  client_id: number;
  client_sr_id: string;
  name: string;
  email: string | null;
  mobile: string | null;
  sales_person_name: string | null;
  customer_group: string | null;
  date_added: string | null;
  freeze_boq_amount: number | null;
  total_paid: number | null;
  payment_percentage: number | null;
  required_percentage: number | null;
  payment_alert: string | null;
  handover_status: string | null;
  handover_remark: string | null;
  handover_request_date: string | null;
  current_phase: number | null;
  escalation_status: string | null;
  assigned_team: ApiAssignedTeamMember[] | null;
}

export interface ApiClientListResponse {
  status: boolean;
  message: string;
  page?: number;
  limit?: number;
  total?: number;
  data?: ApiClientItem[];
}

export interface ClientListFilters {
  page?: number;
  limit?: number;
  search?: string;
  payment_filter?: string;
  kt_meeting_from?: string;
  kt_meeting_to?: string;
  dispatch_date_from?: string;
  dispatch_date_to?: string;
  hand_over_request_date_from?: string;
  hand_over_request_date_to?: string;
}

const DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/clientList';
const CLOUD_PROXY_URL = 'https://ais-pre-p4l3km6ranu5hkwm757tro-734024418090.asia-southeast1.run.app/crm-api/mobileapi/Client/clientList';
const PROXY_URL = '/crm-api/mobileapi/Client/clientList';

export function mapApiClientToClientProject(item: ApiClientItem): ClientProject {
  if (!item || typeof item !== 'object') {
    return {
      id: 'HC-000',
      clientIdNum: 0,
      name: 'Unknown Client',
      date: 'N/A',
      salesManager: 'N/A',
      assignedTeam: { designer: 'Not Assigned', projectManager: 'Not Assigned' },
      ktRequest: { status: 'NA', date: 'NA' },
      validationDate: 'N/A',
      phase: 'Phase 1',
      overallProgress: 0,
      freezeBOQAmount: 0,
      amountReceived: 0,
    };
  }

  const assignedTeamList = Array.isArray(item.assigned_team) ? item.assigned_team : [];

  const designers = assignedTeamList
    .filter((t) => t && typeof t.role === 'string' && t.role.toLowerCase().includes('design'))
    .map((t) => (t && t.name) || '')
    .filter(Boolean)
    .join(', ');

  const projectManagers = assignedTeamList
    .filter((t) => t && typeof t.role === 'string' && t.role.toLowerCase().includes('manager'))
    .map((t) => (t && t.name) || '')
    .filter(Boolean)
    .join(', ');

  let phaseStr: PhaseType = 'Phase 1';
  if (item.current_phase === 2 || (item.current_phase as any) === '2') phaseStr = 'Phase 2';
  else if (item.current_phase === 3 || (item.current_phase as any) === '3') phaseStr = 'Phase 3';
  else if (item.current_phase === 4 || (item.current_phase as any) === '4') phaseStr = 'Phase 4';

  return {
    id: item.client_sr_id || (item.client_id ? `HC${item.client_id}` : 'HC-000'),
    clientIdNum: typeof item.client_id === 'number' ? item.client_id : parseInt(String(item.client_id || 0), 10),
    name: item.name || 'Unnamed Client',
    date: item.date_added || 'N/A',
    salesManager: item.sales_person_name || 'N/A',
    assignedTeam: {
      designer: designers || 'Not Assigned',
      projectManager: projectManagers || 'Not Assigned',
    },
    ktRequest: {
      status: item.handover_status === 'Approved' ? 'Accepted' : item.handover_status ? 'Pending' : 'NA',
      date: item.handover_request_date || 'NA',
    },
    validationDate: item.handover_request_date || 'N/A',
    phase: phaseStr,
    email: item.email || undefined,
    mobile: item.mobile || undefined,
    overallProgress: item.payment_percentage ? Math.round(Number(item.payment_percentage)) : 0,
    activeSubPhase: item.escalation_status && item.escalation_status !== 'None' ? item.escalation_status : undefined,
    freezeBOQAmount: item.freeze_boq_amount || 0,
    amountReceived: item.total_paid || 0,
  };
}

export function notifySessionExpired(message?: string) {
  if (typeof window !== 'undefined') {
    const event = new CustomEvent('auth:session_expired', {
      detail: { message: message || 'Authorization header missing or session invalidated. Please log in again.' }
    });
    window.dispatchEvent(event);
  }
}

function checkAndNotifyTokenError(data: any) {
  if (!data) return;
  if (data.status === false && typeof data.message === 'string') {
    const msg = data.message.toLowerCase();
    if (
      msg.includes('authorization header missing') ||
      msg.includes('invalid token') ||
      msg.includes('token expired') ||
      msg.includes('unauthenticated') ||
      msg.includes('session expired') ||
      msg.includes('logged in from another') ||
      msg.includes('account logged in elsewhere')
    ) {
      console.warn('Authentication error detected from API:', data.message);
      notifySessionExpired(data.message);
    }
  }
}

async function safeParseJson<T>(res: Response): Promise<T | null> {
  if (res.status === 401) {
    console.warn('HTTP 401 Unauthorized');
    notifySessionExpired('Session expired or unauthorized request (HTTP 401)');
    return null;
  }
  try {
    const text = await res.text();
    if (!text || (!text.trim().startsWith('{') && !text.trim().startsWith('['))) {
      console.warn('Received non-JSON response:', text.slice(0, 150));
      return null;
    }
    const parsed = JSON.parse(text) as T;
    checkAndNotifyTokenError(parsed);
    return parsed;
  } catch (e) {
    console.warn('Error parsing JSON:', e);
    return null;
  }
}

export function getEffectiveTokenAndUserId(providedToken?: string): { token: string; userId: string } {
  let token = providedToken || '';
  let userId = '';

  if (!token && typeof localStorage !== 'undefined') {
    token = localStorage.getItem('auth_token') || '';
  }

  if (typeof localStorage !== 'undefined') {
    const rawUserData = localStorage.getItem('user_data');
    if (rawUserData) {
      try {
        const u = JSON.parse(rawUserData);
        userId = String(u.user_id || u.id || '');
        if (!token) {
          token = String(u.token || u.auth_token || u.access_token || u.user_id || '');
        }
      } catch (e) {}
    }
  }

  return { token, userId };
}

async function fetchCrmEndpoint<T>(
  directUrl: string,
  proxyUrl: string,
  providedToken: string,
  payloadObj: Record<string, any> = {}
): Promise<T | null> {
  const { token, userId } = getEffectiveTokenAndUserId(providedToken);
  const fullPayload: Record<string, any> = {
    token,
    user_id: userId,
    ...payloadObj,
  };

  const jsonBody = JSON.stringify(fullPayload);

  const urlParams = new URLSearchParams();
  for (const [k, v] of Object.entries(fullPayload)) {
    if (v !== undefined && v !== null) {
      urlParams.append(k, String(v));
    }
  }

  const formData = new FormData();
  for (const [k, v] of Object.entries(fullPayload)) {
    if (v !== undefined && v !== null) {
      formData.append(k, String(v));
    }
  }

  const baseHeaders: Record<string, string> = {
    'Accept': 'application/json',
  };

  if (token) {
    baseHeaders['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    baseHeaders['token'] = token;
    baseHeaders['X-Api-Token'] = token;
  }

  const isApk = isMobileApkEnvironment();
  const urls = isApk ? [directUrl, proxyUrl] : [proxyUrl, directUrl];

  for (const url of urls) {
    if (!url) continue;

    // 1. Try JSON Body
    try {
      const headers = { ...baseHeaders, 'Content-Type': 'application/json' };
      const res = await fetch(url, { method: 'POST', headers, body: jsonBody });
      const data = await safeParseJson<T>(res);
      if (data && (data as any).status !== false) return data;
    } catch (e) {
      console.warn(`JSON fetch from ${url} failed:`, e);
    }

    // 2. Try x-www-form-urlencoded
    try {
      const headers = { ...baseHeaders, 'Content-Type': 'application/x-www-form-urlencoded' };
      const res = await fetch(url, { method: 'POST', headers, body: urlParams.toString() });
      const data = await safeParseJson<T>(res);
      if (data && (data as any).status !== false) return data;
    } catch (e) {
      console.warn(`Form fetch from ${url} failed:`, e);
    }

    // 3. Try FormData
    try {
      const res = await fetch(url, { method: 'POST', headers: baseHeaders, body: formData });
      const data = await safeParseJson<T>(res);
      if (data && (data as any).status !== false) return data;
    } catch (e) {
      console.warn(`FormData fetch from ${url} failed:`, e);
    }

    // 4. Try GET with query params
    try {
      const queryString = urlParams.toString();
      const getUrl = queryString ? `${url}?${queryString}` : url;
      const res = await fetch(getUrl, { method: 'GET', headers: baseHeaders });
      const data = await safeParseJson<T>(res);
      if (data && (data as any).status !== false) return data;
    } catch (e) {
      console.warn(`GET fetch from ${url} failed:`, e);
    }
  }

  return null;
}

export async function fetchClientList(
  providedToken: string,
  filters: ClientListFilters = {}
): Promise<ApiClientListResponse> {
  const { token, userId } = getEffectiveTokenAndUserId(providedToken);

  const payload: Record<string, any> = {
    page: filters.page || 1,
    limit: filters.limit || 50,
    search: filters.search || '',
    token: token,
    user_id: userId,
    ...filters,
  };

  const jsonBody = JSON.stringify(payload);

  const urlParams = new URLSearchParams();
  for (const [k, v] of Object.entries(payload)) {
    if (v !== undefined && v !== null) {
      urlParams.append(k, String(v));
    }
  }

  const formData = new FormData();
  for (const [k, v] of Object.entries(payload)) {
    if (v !== undefined && v !== null) {
      formData.append(k, String(v));
    }
  }

  const baseHeaders: Record<string, string> = {
    'Accept': 'application/json',
  };

  if (token) {
    baseHeaders['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    baseHeaders['token'] = token;
    baseHeaders['X-Api-Token'] = token;
  }

  const isApk = isMobileApkEnvironment();
  const targetUrls = isApk ? [DIRECT_URL, CLOUD_PROXY_URL] : [PROXY_URL, DIRECT_URL, CLOUD_PROXY_URL];

  for (const url of targetUrls) {
    // 1. Try JSON Body
    try {
      const headers = { ...baseHeaders, 'Content-Type': 'application/json' };
      const res = await fetch(url, { method: 'POST', headers, body: jsonBody });
      const data = await safeParseJson<ApiClientListResponse>(res);
      if (data && typeof data.status !== 'undefined' && data.status !== false) {
        return data;
      }
    } catch (e) {
      console.warn(`JSON fetch client list from ${url} failed:`, e);
    }

    // 2. Try x-www-form-urlencoded
    try {
      const headers = { ...baseHeaders, 'Content-Type': 'application/x-www-form-urlencoded' };
      const res = await fetch(url, { method: 'POST', headers, body: urlParams.toString() });
      const data = await safeParseJson<ApiClientListResponse>(res);
      if (data && typeof data.status !== 'undefined' && data.status !== false) {
        return data;
      }
    } catch (e) {
      console.warn(`Form fetch client list from ${url} failed:`, e);
    }

    // 3. Try FormData
    try {
      const res = await fetch(url, { method: 'POST', headers: baseHeaders, body: formData });
      const data = await safeParseJson<ApiClientListResponse>(res);
      if (data && typeof data.status !== 'undefined' && data.status !== false) {
        return data;
      }
    } catch (e) {
      console.warn(`FormData fetch client list from ${url} failed:`, e);
    }
  }

  return { status: false, message: 'CRM API client list response formatted as text', data: [] };
}

export async function fetchAllClientList(
  token: string,
  filters: ClientListFilters = {}
): Promise<ApiClientListResponse> {
  const initialLimit = filters.limit || 1000;
  let firstRes: ApiClientListResponse;

  try {
    firstRes = await fetchClientList(token, { ...filters, page: 1, limit: initialLimit });
  } catch (e) {
    console.warn('Failed to fetch initial client list page:', e);
    return { status: false, message: 'CRM API connecting...', data: [] };
  }

  if (!firstRes || !firstRes.status || !Array.isArray(firstRes.data)) {
    return firstRes || { status: false, message: 'Empty client dataset', data: [] };
  }

  let accumulated = [...firstRes.data];
  const totalRecords = typeof firstRes.total === 'number' ? firstRes.total : (firstRes as any).total_records || accumulated.length;
  const pageSize = firstRes.data.length;

  if (totalRecords > accumulated.length && pageSize > 0) {
    let currentPage = 1;
    const maxPages = Math.ceil(totalRecords / pageSize) + 2;

    while (accumulated.length < totalRecords && currentPage < maxPages) {
      currentPage++;
      try {
        const nextRes = await fetchClientList(token, {
          ...filters,
          page: currentPage,
          limit: pageSize,
        });

        if (nextRes && nextRes.status && Array.isArray(nextRes.data) && nextRes.data.length > 0) {
          const existingIds = new Set(accumulated.map((c) => c.client_id || c.client_sr_id));
          let addedCount = 0;
          for (const item of nextRes.data) {
            const key = item.client_id || item.client_sr_id;
            if (!existingIds.has(key)) {
              accumulated.push(item);
              existingIds.add(key);
              addedCount++;
            }
          }
          if (addedCount === 0) {
            break;
          }
        } else {
          break;
        }
      } catch (err) {
        console.warn(`Error fetching page ${currentPage} of client list:`, err);
        break;
      }
    }
  }

  return {
    ...firstRes,
    data: accumulated,
    total: accumulated.length,
  };
}

export interface ApiWorkflowPhaseItem {
  name: string;
  status: string;
}

export interface ApiWorkflowPhase {
  phase_number: number;
  title: string;
  status: string;
  items: ApiWorkflowPhaseItem[];
}

export interface ApiWorkflowMilestone {
  stage: string;
  percentage: number;
  amount: number;
  status: string;
}

export interface ApiWorkflow {
  current_active_phase: number;
  progress_percentage: number;
  phases: ApiWorkflowPhase[];
  milestones: ApiWorkflowMilestone[];
}

export interface ApiClientDetailData {
  client_id: number;
  client_sr_id: string;
  name: string;
  email: string | null;
  mobile: string | null;
  address: string | null;
  city: string | null;
  site_address: string | null;
  site_city: string | null;
  site_zipcode: string | null;
  client_hold: number;
  freeze_boq_amount: number | null;
  wd_boq_amount: number | null;
  discount_modular: number | null;
  discount_civil: number | null;
  amount_received: number | null;
  payment_mode: string | null;
  payment_plan: string | null;
  payment_screenshot: string | null;
  documents?: {
    aadhaar_front: string | null;
    aadhaar_back: string | null;
    pan_card: string | null;
  };
  sales_person?: {
    name: string;
    phone: string;
    email: string;
    manager_name: string;
  };
  workflow?: ApiWorkflow;
}

export interface ApiClientDetailResponse {
  status: boolean;
  message: string;
  data?: ApiClientDetailData;
}

const DETAIL_DIRECT_BASE = 'https://crm.hcinterior.in/mobileapi/Client/clientDetail';
const DETAIL_CLOUD_PROXY_BASE = 'https://ais-pre-p4l3km6ranu5hkwm757tro-734024418090.asia-southeast1.run.app/crm-api/mobileapi/Client/clientDetail';
const DETAIL_PROXY_BASE = '/crm-api/mobileapi/Client/clientDetail';

export async function fetchClientDetail(
  token: string,
  clientId: number | string
): Promise<ApiClientDetailResponse> {
  const directUrl = `${DETAIL_DIRECT_BASE}/${clientId}`;
  const cloudProxyUrl = `${DETAIL_CLOUD_PROXY_BASE}/${clientId}`;
  const proxyUrl = `${DETAIL_PROXY_BASE}/${clientId}`;

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    headers['token'] = token;
    headers['X-Api-Token'] = token;
  }

  const payload = JSON.stringify({ client_id: clientId, token });
  const isApk = isMobileApkEnvironment();
  const urlPairs = isApk
    ? [
        { url: cloudProxyUrl, method: 'POST', body: payload },
        { url: cloudProxyUrl, method: 'GET' },
        { url: directUrl, method: 'POST', body: payload },
        { url: directUrl, method: 'GET' },
      ]
    : [
        { url: proxyUrl, method: 'POST', body: payload },
        { url: proxyUrl, method: 'GET' },
        { url: cloudProxyUrl, method: 'POST', body: payload },
        { url: directUrl, method: 'POST', body: payload },
      ];

  for (const pair of urlPairs) {
    try {
      const res = await fetch(pair.url, {
        method: pair.method,
        headers,
        body: pair.body,
      });
      const data = await safeParseJson<ApiClientDetailResponse>(res);
      if (data && typeof data.status !== 'undefined') {
        return data;
      }
    } catch (e) {
      console.warn(`${pair.method} detail on ${pair.url} failed:`, e);
    }
  }

  throw new Error('Failed to fetch client details from CRM API.');
}

export function mapApiItemToQCDesign(raw: any): QCDesignItem {
  const id = raw.id || raw.design_id || Math.random().toString();
  const clientId = raw.client_sr_id || (raw.client_id ? `HC${raw.client_id}` : raw.clientId || 'N/A');
  const clientName = raw.client_name || raw.clientName || 'N/A';

  let fileUrl = raw.fileUrl || raw.url || raw.upload_url || '';
  if (!fileUrl && Array.isArray(raw.upload_file) && raw.upload_file.length > 0) {
    fileUrl = raw.upload_file[0];
  }

  let fileName = raw.fileName || '';
  if (!fileName && fileUrl) {
    const parts = fileUrl.split('/');
    const lastPart = parts[parts.length - 1];
    fileName = lastPart && lastPart.includes('.') ? lastPart : `${clientName}_QC_Design.pdf`;
  } else if (!fileName) {
    fileName = `${clientName}_QC_Design.pdf`;
  }

  const isFinalVal =
    raw.is_final === 1 || raw.is_final === '1' || raw.isFinal === 'Yes' || raw.isFinal === 1 ? 'Yes' : 'No';

  return {
    id: id,
    design_id: raw.design_id || (typeof id === 'number' ? id : parseInt(id, 10) || undefined),
    client_id: raw.client_id,
    client_sr_id: raw.client_sr_id || clientId,
    client_name: clientName,
    clientName: clientName,
    clientId: clientId,
    design_type: raw.design_type || raw.designType || 'Modular Design',
    designType: raw.design_type || raw.designType || 'Modular Design',
    design_style: raw.design_style || raw.designStyle || 'Post Validation Design',
    designStyle: raw.design_style || raw.designStyle || 'Post Validation Design',
    is_final: raw.is_final ?? (isFinalVal === 'Yes' ? 1 : 0),
    isFinal: isFinalVal,
    status: raw.status || 'Pending',
    remark: raw.remark || raw.remarks || '',
    uploaded_by: raw.uploaded_by,
    uploaded_by_name: raw.uploaded_by_name || raw.uploadedBy || 'N/A',
    uploadedBy: raw.uploaded_by_name || raw.uploadedBy || 'N/A',
    created_at: raw.created_at || raw.date || '',
    date: raw.created_at || raw.date || new Date().toISOString(),
    upload_file: Array.isArray(raw.upload_file) ? raw.upload_file : fileUrl ? [fileUrl] : [],
    upload_url: raw.upload_url || fileUrl,
    url: fileUrl,
    fileName: fileName,
    fileUrl: fileUrl,
  };
}

export interface ApiQcDesignResponse {
  status: boolean;
  message?: string;
  data?: any;
}

const QC_DESIGN_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/qc_design';
const QC_DESIGN_PROXY_URL = '/crm-api/mobileapi/Client/qc_design';

export async function fetchQcDesignList(
  token: string,
  clientId?: string | number
): Promise<QCDesignItem[]> {
  const payloadObj: Record<string, any> = {};
  if (clientId) payloadObj.client_id = clientId;

  const rawResponse = await fetchCrmEndpoint<ApiQcDesignResponse>(
    QC_DESIGN_DIRECT_URL,
    QC_DESIGN_PROXY_URL,
    token,
    payloadObj
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('qc_design API response status is false or empty:', rawResponse?.message);
    return [];
  }

  let list: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    list = rawResponse.data;
  } else if (rawResponse.data && Array.isArray(rawResponse.data.data)) {
    list = rawResponse.data.data;
  } else if (rawResponse.data && typeof rawResponse.data === 'object') {
    const possibleArray = Object.values(rawResponse.data).find((v) => Array.isArray(v));
    if (possibleArray && Array.isArray(possibleArray)) {
      list = possibleArray;
    }
  }

  return list.map(mapApiItemToQCDesign);
}

export function mapApiItemToEscalation(raw: any): EscalationItem {
  const id = raw.id || raw.escalation_id || Math.random().toString();
  const clientName = raw.client_name || raw.clientName || 'N/A';
  const clientId = raw.client_sr_id || (raw.client_id ? `HC${raw.client_id}` : raw.clientId || 'N/A');
  const description = raw.remark || raw.description || 'No description provided';
  const assignedTo = raw.assigned_to_name || raw.assignedTo || 'Unassigned';
  const raisedBy = raw.raised_by_name || 'N/A';
  const createdAt = raw.created_at || raw.createdAt || '';

  const commentsList: EscalationComment[] = Array.isArray(raw.comments)
    ? raw.comments.map((c: any) => ({
        id: c.id,
        escalation_id: c.escalation_id || id,
        user_id: c.user_id,
        user_name: c.user_name || 'User',
        comment: c.comment || '',
        created_at: c.created_at || '',
      }))
    : [];

  return {
    id,
    client_id: raw.client_id,
    client_name: clientName,
    client_sr_id: raw.client_sr_id || clientId,
    client_phone: raw.client_phone || '',
    raised_by: raw.raised_by,
    raised_by_name: raisedBy,
    assigned_to: raw.assigned_to,
    assigned_to_name: assignedTo,
    remark: raw.remark || description,
    status: raw.status || 'Open',
    created_at: createdAt,
    updated_at: raw.updated_at || null,
    comments: commentsList,
    total_comments: raw.total_comments ?? commentsList.length,

    // Backward compatibility UI fields
    clientId: clientId,
    clientName: clientName,
    category: raw.category || 'General Escalation',
    severity: raw.severity || (raw.status === 'Open' ? 'High' : 'Medium'),
    description: description,
    assignedTo: assignedTo,
    createdAt: createdAt,
  };
}

const ESCALATION_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/esclation';
const ESCALATION_PROXY_URL = '/crm-api/mobileapi/Client/esclation';

const SAVE_ESCALATION_REPLY_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/save_escalation_reply';
const SAVE_ESCALATION_REPLY_PROXY_URL = '/crm-api/mobileapi/Client/save_escalation_reply';

export interface ApiEscalationResponse {
  status: boolean;
  message?: string;
  data?: any;
  page?: number;
  total?: number;
  total_pages?: number;
}

const CREATE_ESCALATION_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/create_escalation';
const CREATE_ESCALATION_PROXY_URL = '/crm-api/mobileapi/Client/create_escalation';

export async function createEscalation(
  token: string,
  clientId: number | string,
  remark: string,
  assignedTo: number = 5
): Promise<{ success: boolean; message: string; data?: EscalationItem }> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    headers['token'] = token;
    headers['X-Api-Token'] = token;
  }

  const clientIdNum = typeof clientId === 'number' ? clientId : parseInt(String(clientId).replace(/\D/g, ''), 10) || 513;

  const payload = JSON.stringify({
    token,
    client_id: clientIdNum,
    assigned_to: assignedTo,
    remark,
  });

  let rawResponse: any = null;

  try {
    const res = await fetch(CREATE_ESCALATION_PROXY_URL, { method: 'POST', headers, body: payload });
    rawResponse = await safeParseJson<any>(res);
  } catch (e) {
    console.warn('Proxy POST create_escalation failed:', e);
  }

  if (!rawResponse || typeof rawResponse.status === 'undefined') {
    try {
      const res = await fetch(CREATE_ESCALATION_DIRECT_URL, { method: 'POST', headers, body: payload });
      rawResponse = await safeParseJson<any>(res);
    } catch (e) {
      console.warn('Direct POST create_escalation failed:', e);
    }
  }

  if (!rawResponse || !rawResponse.status) {
    return {
      success: false,
      message: rawResponse?.message || 'Failed to create escalation.',
    };
  }

  const newEscalation = rawResponse.data ? mapApiItemToEscalation(rawResponse.data) : undefined;

  return {
    success: true,
    message: rawResponse.message || 'Escalation created successfully',
    data: newEscalation,
  };
}

export async function fetchEscalationList(
  token: string,
  clientId?: string | number,
  page = 1,
  limit = 50,
  status?: string
): Promise<EscalationItem[]> {
  const payloadObj: Record<string, any> = {
    page,
    limit,
  };

  if (clientId !== undefined && clientId !== null && clientId !== '') {
    const numId = typeof clientId === 'number' ? clientId : parseInt(String(clientId).replace(/\D/g, ''), 10);
    if (!isNaN(numId) && numId > 0) {
      payloadObj.client_id = numId;
    }
  }

  if (status && status.trim()) {
    payloadObj.status = status.trim();
  }

  const rawResponse = await fetchCrmEndpoint<ApiEscalationResponse>(
    ESCALATION_DIRECT_URL,
    ESCALATION_PROXY_URL,
    token,
    payloadObj
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('Escalation API response status false/empty:', rawResponse?.message);
    return [];
  }

  let list: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    list = rawResponse.data;
  } else if (rawResponse.data && Array.isArray(rawResponse.data.data)) {
    list = rawResponse.data.data;
  }

  return list.map(mapApiItemToEscalation);
}

export async function saveEscalationReply(
  token: string,
  escalationId: number | string,
  comment: string
): Promise<{ success: boolean; message: string; newComment?: EscalationComment; newStatus?: string }> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    headers['token'] = token;
    headers['X-Api-Token'] = token;
  }

  const payload = JSON.stringify({
    token,
    escalation_id: Number(escalationId) || escalationId,
    comment,
  });

  let rawResponse: any = null;

  try {
    const res = await fetch(SAVE_ESCALATION_REPLY_PROXY_URL, { method: 'POST', headers, body: payload });
    rawResponse = await safeParseJson<any>(res);
  } catch (e) {
    console.warn('Proxy POST save_escalation_reply failed:', e);
  }

  if (!rawResponse || typeof rawResponse.status === 'undefined') {
    try {
      const res = await fetch(SAVE_ESCALATION_REPLY_DIRECT_URL, { method: 'POST', headers, body: payload });
      rawResponse = await safeParseJson<any>(res);
    } catch (e) {
      console.warn('Direct POST save_escalation_reply failed:', e);
    }
  }

  if (!rawResponse || !rawResponse.status) {
    return {
      success: false,
      message: rawResponse?.message || 'Failed to save escalation comment.',
    };
  }

  const data = rawResponse.data || {};
  const newComment: EscalationComment = {
    id: data.id || Date.now(),
    escalation_id: data.escalation_id || escalationId,
    user_id: data.user_id || '',
    user_name: data.user_name || 'You',
    comment: data.comment || comment,
    created_at: data.created_at || new Date().toISOString(),
  };

  return {
    success: true,
    message: rawResponse.message || 'Escalation reply saved successfully',
    newComment,
    newStatus: data.escalation_status,
  };
}

export function mapApiItemToFinalValidation(raw: any): FinalValidationItem {
  const id = raw.design_id || raw.id || Math.random().toString();
  const clientName = raw.client_name || raw.clientName || 'N/A';
  const clientId =
    raw.client_sr_id && raw.client_sr_id !== '-'
      ? raw.client_sr_id
      : raw.client_id
      ? `HC${raw.client_id}`
      : raw.clientId || 'N/A';

  const designType = raw.design_type || raw.designType || raw.design_style || 'Modular Design';
  const designStyle = raw.design_style || raw.designStyle || 'Post Validation Design';
  const isFinalBadge =
    raw.is_final === 1 || raw.is_final === '1' || raw.isFinal === 'Yes' || raw.isFinal === '1' ? 'Yes' : 'No';

  const uploadFiles: string[] = Array.isArray(raw.upload_file)
    ? raw.upload_file
    : typeof raw.upload_file === 'string' && raw.upload_file
    ? [raw.upload_file]
    : [];

  const mainPdfUrl = uploadFiles[0] || '';
  const driveUrl = raw.upload_url || '';

  const fileName = mainPdfUrl
    ? mainPdfUrl.split('/').pop() || `${clientName}_Final_Validation.pdf`
    : `${clientName}_Final_Validation.pdf`;

  const fileUrl = mainPdfUrl
    ? mainPdfUrl.split('/').pop() || 'FILE_PDF'
    : 'FILE_PDF';

  const primaryUrl = driveUrl || mainPdfUrl || '#';

  return {
    id,
    design_id: raw.design_id,
    clientName,
    clientId,
    client_id: raw.client_id,
    client_sr_id: raw.client_sr_id,
    client_name: raw.client_name,
    fileName,
    fileUrl,
    isFinal: isFinalBadge,
    is_final: raw.is_final,
    designType,
    design_type: raw.design_type,
    designStyle,
    design_style: raw.design_style,
    url: primaryUrl,
    status: raw.status || 'Approved',
    uploadedBy: raw.uploaded_by_name || raw.uploadedBy || 'N/A',
    uploaded_by_name: raw.uploaded_by_name,
    date: raw.created_at || raw.date || '',
    created_at: raw.created_at,
    remark: raw.remark || '',
    upload_file: uploadFiles,
    upload_url: driveUrl,
  };
}

const FINAL_VALIDATION_DESIGN_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/final_validation_design';
const FINAL_VALIDATION_DESIGN_PROXY_URL = '/crm-api/mobileapi/Client/final_validation_design';

export interface ApiFinalValidationResponse {
  status: boolean;
  message?: string;
  data?: any;
  page?: number;
  total?: number;
  total_pages?: number;
}

export async function fetchFinalValidationDesignList(
  token: string,
  clientId?: string | number,
  designStyle = 'Post Validation Design',
  status = '',
  page = 1,
  limit = 50
): Promise<FinalValidationItem[]> {
  const payloadObj: Record<string, any> = {
    page,
    limit,
    client_id: clientId || '',
    design_style: designStyle || '',
    status: status || '',
  };

  const rawResponse = await fetchCrmEndpoint<ApiFinalValidationResponse>(
    FINAL_VALIDATION_DESIGN_DIRECT_URL,
    FINAL_VALIDATION_DESIGN_PROXY_URL,
    token,
    payloadObj
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('Final validation design API response status false/empty:', rawResponse?.message);
    return [];
  }

  let list: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    list = rawResponse.data;
  } else if (rawResponse.data && Array.isArray(rawResponse.data.data)) {
    list = rawResponse.data.data;
  } else if (rawResponse.data && typeof rawResponse.data === 'object') {
    const possibleArray = Object.values(rawResponse.data).find((v) => Array.isArray(v));
    if (possibleArray && Array.isArray(possibleArray)) {
      list = possibleArray;
    }
  }

  return list.map(mapApiItemToFinalValidation);
}

export function mapApiItemToOnSitePurchase(raw: any): OnSitePurchaseItem {
  const id = raw.id || Math.random().toString();
  const clientName = raw.client_name || raw.clientName || 'N/A';
  const clientId =
    raw.client_sr_id && raw.client_sr_id !== '-'
      ? raw.client_sr_id
      : raw.client_id
      ? `HC${raw.client_id}`
      : raw.clientId || 'N/A';

  const rawUploadFiles: any[] = Array.isArray(raw.upload_file)
    ? raw.upload_file
    : typeof raw.upload_file === 'string' && raw.upload_file
    ? [raw.upload_file]
    : [];

  const cleanFileUrls: string[] = [];
  for (const item of rawUploadFiles) {
    let u = '';
    if (typeof item === 'object' && item) {
      u = item.file_url || item.path || item.url || '';
    } else if (typeof item === 'string') {
      u = item;
    }
    if (u && u !== '#') {
      if (!u.startsWith('http://') && !u.startsWith('https://')) {
        u = `https://crm.hcinterior.in/${u.replace(/^\//, '')}`;
      }
      cleanFileUrls.push(u);
    }
  }

  let fileUrl = cleanFileUrls[0] || '';
  if (!fileUrl) {
    let rawUrl = raw.upload_url || raw.fileUrl || '';
    if (rawUrl && rawUrl !== '#') {
      if (!rawUrl.startsWith('http://') && !rawUrl.startsWith('https://')) {
        fileUrl = `https://crm.hcinterior.in/${rawUrl.replace(/^\//, '')}`;
      } else {
        fileUrl = rawUrl;
      }
    } else {
      fileUrl = '#';
    }
  }

  const firstFileObj = rawUploadFiles[0];
  const fileName =
    raw.file_name ||
    raw.fileName ||
    (typeof firstFileObj === 'object' && firstFileObj?.fileName ? firstFileObj.fileName : `${clientName}_OnSitePurchase`);

  return {
    id,
    clientName,
    clientId,
    client_id: raw.client_id,
    client_sr_id: raw.client_sr_id,
    client_name: raw.client_name,
    fileName,
    fileUrl,
    status: raw.status || 'Pending',
    date: raw.created_date || raw.date || raw.created_at || '',
    created_date: raw.created_date,
    updated_date: raw.updated_date,
    status_approve_date: raw.status_approve_date,
    status_approve_by: raw.status_approve_by,
    remark: raw.remark || '',
    uploaded_by: raw.uploaded_by,
    upload_file: cleanFileUrls,
    upload_url: fileUrl,
    brand: raw.brand || '',
    message: raw.site_message || raw.message || '',
    site_message: raw.site_message || raw.message || '',
  };
}

const ON_SITE_PURCHASE_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/on_site_purchase_list';
const ON_SITE_PURCHASE_PROXY_URL = '/crm-api/mobileapi/Client/on_site_purchase_list';

const CREATE_ON_SITE_PURCHASE_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/new_on_site_purchase';
const CREATE_ON_SITE_PURCHASE_PROXY_URL = '/crm-api/mobileapi/Client/new_on_site_purchase';

export interface ApiOnSitePurchaseResponse {
  status: boolean;
  message?: string;
  data?: any;
  page?: number;
  total?: number;
  total_pages?: number;
}

export async function createOnSitePurchase(
  token: string,
  clientId: number | string,
  fileName: string,
  file?: File | Blob | null,
  brand?: string,
  message?: string
): Promise<{ success: boolean; message: string; data?: OnSitePurchaseItem }> {
  const { token: effectiveToken } = getEffectiveTokenAndUserId(token);

  const rawIdStr = String(clientId || '').trim();
  const cleanNumericId = rawIdStr.replace(/\D/g, '');
  const finalClientId = cleanNumericId || rawIdStr;

  const formData = new FormData();
  formData.append('token', effectiveToken);
  formData.append('client_id', finalClientId);
  formData.append('file_name', fileName);
  if (brand !== undefined && brand !== null && brand.trim() !== '') {
    formData.append('brand', brand.trim());
  }
  if (message !== undefined && message !== null && message.trim() !== '') {
    formData.append('message', message.trim());
  }
  if (file) {
    formData.append('files', file);
  }

  const baseHeaders: Record<string, string> = {
    'Accept': 'application/json',
  };

  if (effectiveToken) {
    baseHeaders['Authorization'] = effectiveToken.startsWith('Bearer ') ? effectiveToken : `Bearer ${effectiveToken}`;
    baseHeaders['token'] = effectiveToken;
    baseHeaders['X-Api-Token'] = effectiveToken;
  }

  const isApk = isMobileApkEnvironment();
  const urls = isApk
    ? [CREATE_ON_SITE_PURCHASE_DIRECT_URL, CREATE_ON_SITE_PURCHASE_PROXY_URL]
    : [CREATE_ON_SITE_PURCHASE_PROXY_URL, CREATE_ON_SITE_PURCHASE_DIRECT_URL];

  let rawResponse: any = null;

  for (const url of urls) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: baseHeaders,
        body: formData,
      });
      rawResponse = await safeParseJson<any>(res);
      if (rawResponse && typeof rawResponse.status !== 'undefined') {
        break;
      }
    } catch (e) {
      console.warn(`Fetch createOnSitePurchase from ${url} failed:`, e);
    }
  }

  if (!rawResponse || !rawResponse.status) {
    return {
      success: false,
      message: rawResponse?.message || 'Failed to create On-Site Purchase request.',
    };
  }

  const newPurchaseItem = rawResponse.data ? mapApiItemToOnSitePurchase(rawResponse.data) : undefined;

  return {
    success: true,
    message: rawResponse.message || 'On site purchase request created successfully',
    data: newPurchaseItem,
  };
}

export async function fetchOnSitePurchaseList(
  token: string,
  clientId?: string | number,
  status = '',
  startDate = '',
  endDate = '',
  page = 1,
  limit = 50
): Promise<OnSitePurchaseItem[]> {
  const payloadObj: Record<string, any> = {
    page,
    limit,
  };

  if (clientId !== undefined && clientId !== null && clientId !== '') {
    const numId = typeof clientId === 'number' ? clientId : parseInt(String(clientId).replace(/\D/g, ''), 10);
    if (!isNaN(numId) && numId > 0) {
      payloadObj.client_id = numId;
    }
  }

  if (status && status !== 'All') {
    payloadObj.status = status;
  }
  if (startDate) payloadObj.start_date = startDate;
  if (endDate) payloadObj.end_date = endDate;

  const rawResponse = await fetchCrmEndpoint<ApiOnSitePurchaseResponse>(
    ON_SITE_PURCHASE_DIRECT_URL,
    ON_SITE_PURCHASE_PROXY_URL,
    token,
    payloadObj
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('On Site Purchase API response status false/empty:', rawResponse?.message);
    return [];
  }

  let list: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    list = rawResponse.data;
  } else if (rawResponse.data && Array.isArray(rawResponse.data.data)) {
    list = rawResponse.data.data;
  } else if (rawResponse.data && typeof rawResponse.data === 'object') {
    const possibleArray = Object.values(rawResponse.data).find((v) => Array.isArray(v));
    if (possibleArray && Array.isArray(possibleArray)) {
      list = possibleArray;
    }
  }

  return list.map(mapApiItemToOnSitePurchase);
}

// ============================================================================
// ON SITE PURCHASE REQUEST (NEW) - WITH LINE ITEMS APIS
// ============================================================================

const OSP_LIST_NEW_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/on_site_purchase_list_new';
const OSP_LIST_NEW_PROXY = '/crm-api/mobileapi/client/on_site_purchase_list_new';

const OSP_CREATE_NEW_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/create_on_site_purchase_request_new';
const OSP_CREATE_NEW_PROXY = '/crm-api/mobileapi/client/create_on_site_purchase_request_new';

const OSP_ACCEPT_REJECT_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/accept_reject_purchase_request_new';
const OSP_ACCEPT_REJECT_PROXY = '/crm-api/mobileapi/client/accept_reject_purchase_request_new';

const OSP_UPDATE_PARENT_STATUS_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/update_parent_request_status_new';
const OSP_UPDATE_PARENT_STATUS_PROXY = '/crm-api/mobileapi/client/update_parent_request_status_new';

const OSP_ITEM_ORDER_TOGGLE_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/on_site_purchase_item_order_toggle_new';
const OSP_ITEM_ORDER_TOGGLE_PROXY = '/crm-api/mobileapi/client/on_site_purchase_item_order_toggle_new';

const OSP_ITEM_STATUS_UPDATE_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/on_site_purchase_item_status_update_new';
const OSP_ITEM_STATUS_UPDATE_PROXY = '/crm-api/mobileapi/client/on_site_purchase_item_status_update_new';

const OSP_UPLOAD_PHOTOS_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/upload_on_site_purchase_photos_new';
const OSP_UPLOAD_PHOTOS_PROXY = '/crm-api/mobileapi/client/upload_on_site_purchase_photos_new';

const OSP_DELETE_PHOTO_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/delete_on_site_purchase_photo_new';
const OSP_DELETE_PHOTO_PROXY = '/crm-api/mobileapi/client/delete_on_site_purchase_photo_new';

const OSP_DELETE_REQUEST_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/delete_on_site_purchase_request_new';
const OSP_DELETE_REQUEST_PROXY = '/crm-api/mobileapi/client/delete_on_site_purchase_request_new';

const OSP_CATEGORY_LIST_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/get_purchase_item_category_list';
const OSP_CATEGORY_LIST_PROXY = '/crm-api/mobileapi/client/get_purchase_item_category_list';

const OSP_BRAND_LIST_DIRECT = 'https://crm.hcinterior.in/mobileapi/client/get_purchase_item_brand_list';
const OSP_BRAND_LIST_PROXY = '/crm-api/mobileapi/client/get_purchase_item_brand_list';

export interface ApiOnSitePurchaseListNewResponse {
  status: boolean;
  message?: string;
  total_records?: number;
  page?: number;
  limit?: number;
  data?: any[];
}

export function mapApiItemToOnSitePurchaseRequest(raw: any): OnSitePurchaseRequestItem {
  const id = Number(raw.id || raw.purchase_id || 0);
  const client_id = Number(raw.client_id || 0);
  const client_name = raw.client_name || raw.clientName || '';
  const client_sr_id = raw.client_sr_id || (client_id ? `HC${client_id}` : '');
  const purchase_no = raw.purchase_no || raw.purchaseNo || `OSPR-${String(id).padStart(4, '0')}`;
  const request_status = raw.request_status || raw.status || 'Pending';
  const status = raw.status || request_status;
  const remark = raw.remark || '';
  const uploaded_by = raw.uploaded_by || '';
  const creator_name = raw.creator_name || raw.requested_by || 'Staff';
  const created_date = raw.created_date || raw.created_at || raw.date || '';

  // Process items
  const rawItems = Array.isArray(raw.items) ? raw.items : [];
  const items: OnSitePurchaseLineItem[] = rawItems.map((it: any, index: number) => ({
    id: Number(it.id || index + 1),
    purchase_id: Number(it.purchase_id || id),
    item_category: it.item_category || it.category || 'General',
    item: it.item || it.item_name || it.description || '',
    qty: it.qty || it.quantity || 1,
    unit: it.unit || 'Pcs',
    brand: it.brand || '',
    remarks: it.remarks || it.remark || '',
    status: it.status || 'Pending',
    is_ordered: typeof it.is_ordered !== 'undefined' ? Number(it.is_ordered) : (it.status === 'Ordered' || it.status === 'Send' || it.status === 'Recieved' ? 1 : 0),
    approver_name: it.approver_name || '',
    status_approve_date: it.status_approve_date || it.approve_date || '',
    approve_date: it.approve_date || it.status_approve_date || '',
    approval_remarks: it.approval_remarks || '',
  }));

  // Process photos
  const rawPhotos = Array.isArray(raw.photos) ? raw.photos : [];
  const photos: OnSitePurchasePhoto[] = rawPhotos.map((p: any) => {
    let url = p.file_url || p.url || p.path || '';
    if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
      url = `https://crm.hcinterior.in/${url.replace(/^\//, '')}`;
    }
    return {
      fileName: p.fileName || p.file_name || p.name || 'photo.jpg',
      path: p.path || '',
      is_image: typeof p.is_image !== 'undefined' ? Boolean(p.is_image) : true,
      file_url: url,
    };
  });

  let pdf_url = raw.pdf_url || '';
  if (!pdf_url && id) {
    pdf_url = `https://crm.hcinterior.in/admin/client/on_site_purchase_pdf/${id}/0`;
  }
  let pdf_download = raw.pdf_download || '';
  if (!pdf_download && id) {
    pdf_download = `https://crm.hcinterior.in/admin/client/on_site_purchase_pdf/${id}/1`;
  }

  return {
    id,
    client_id,
    client_name,
    client_sr_id,
    purchase_no,
    request_status,
    status,
    remark,
    uploaded_by,
    creator_name,
    created_date,
    items_count: items.length || Number(raw.items_count || 0),
    items,
    photos_count: photos.length || Number(raw.photos_count || 0),
    photos,
    pdf_url,
    pdf_download,
  };
}

// 1. GET ON SITE PURCHASE REQUESTS LIST
export async function fetchOnSitePurchaseListNew(
  token: string,
  clientId?: number | string,
  requestStatus = '',
  page = 1,
  limit = 50
): Promise<{ list: OnSitePurchaseRequestItem[]; total: number; message?: string }> {
  const payload: Record<string, any> = {
    page,
    limit,
  };

  if (clientId !== undefined && clientId !== null && clientId !== '') {
    const numId = typeof clientId === 'number' ? clientId : parseInt(String(clientId).replace(/\D/g, ''), 10);
    if (!isNaN(numId) && numId > 0) {
      payload.client_id = numId;
    }
  }

  if (requestStatus && requestStatus !== 'All') {
    payload.request_status = requestStatus;
  }

  const rawResponse = await fetchCrmEndpoint<ApiOnSitePurchaseListNewResponse>(
    OSP_LIST_NEW_DIRECT,
    OSP_LIST_NEW_PROXY,
    token,
    payload
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('On Site Purchase List (New) API failed/empty:', rawResponse?.message);
    return { list: [], total: 0, message: rawResponse?.message };
  }

  let rawList: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    rawList = rawResponse.data;
  } else if (rawResponse.data && Array.isArray((rawResponse.data as any).data)) {
    rawList = (rawResponse.data as any).data;
  }

  const list = rawList.map(mapApiItemToOnSitePurchaseRequest);
  const total = rawResponse.total_records || list.length;

  return { list, total, message: rawResponse.message };
}

// 2. CREATE NEW ON SITE PURCHASE REQUEST
export async function createOnSitePurchaseRequestNew(
  token: string,
  clientId: number | string,
  items: Array<{
    item_category: string;
    item: string;
    qty: string | number;
    unit: string;
    brand?: string;
    remarks?: string;
  }>
): Promise<{ success: boolean; message: string; purchase_id?: number; purchase_no?: string; data?: any }> {
  const { token: effectiveToken, userId } = getEffectiveTokenAndUserId(token);
  const numId = typeof clientId === 'number' ? clientId : parseInt(String(clientId).replace(/\D/g, ''), 10);

  const payload = {
    token: effectiveToken,
    user_id: userId,
    client_id: numId,
    items,
  };

  const isApk = isMobileApkEnvironment();
  const urls = isApk ? [OSP_CREATE_NEW_DIRECT, OSP_CREATE_NEW_PROXY] : [OSP_CREATE_NEW_PROXY, OSP_CREATE_NEW_DIRECT];

  const baseHeaders: Record<string, string> = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  if (effectiveToken) {
    baseHeaders['Authorization'] = effectiveToken.startsWith('Bearer ') ? effectiveToken : `Bearer ${effectiveToken}`;
    baseHeaders['token'] = effectiveToken;
    baseHeaders['X-Api-Token'] = effectiveToken;
  }

  for (const url of urls) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: baseHeaders,
        body: JSON.stringify(payload),
      });
      const data = await safeParseJson<any>(res);
      if (data && typeof data.status !== 'undefined') {
        if (data.status) {
          return {
            success: true,
            message: data.message || 'On site purchase request created successfully',
            purchase_id: data.purchase_id,
            purchase_no: data.purchase_no,
            data: data.data || data,
          };
        } else {
          return {
            success: false,
            message: data.message || 'Failed to create on site purchase request',
          };
        }
      }
    } catch (e: any) {
      console.warn(`Create on site purchase request failed via ${url}:`, e);
    }
  }

  // Fallback to form-data format if json endpoint returns error or fails
  try {
    const formData = new FormData();
    formData.append('token', effectiveToken);
    formData.append('user_id', userId);
    formData.append('client_id', String(numId));
    items.forEach((it) => {
      formData.append('item_category[]', it.item_category);
      formData.append('item[]', it.item);
      formData.append('qty[]', String(it.qty));
      formData.append('unit[]', it.unit || 'Pcs');
      formData.append('brand[]', it.brand || '');
      formData.append('remarks[]', it.remarks || '');
    });

    for (const url of urls) {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          ...(effectiveToken ? { 'Authorization': `Bearer ${effectiveToken}`, 'token': effectiveToken } : {}),
        },
        body: formData,
      });
      const data = await safeParseJson<any>(res);
      if (data && data.status) {
        return {
          success: true,
          message: data.message || 'On site purchase request created successfully',
          purchase_id: data.purchase_id,
          purchase_no: data.purchase_no,
          data: data.data || data,
        };
      }
    }
  } catch (err: any) {
    console.warn('FormData fallback failed:', err);
  }

  return { success: false, message: 'Server connection error while creating purchase request' };
}

// 3. ACCEPT OR REJECT PURCHASE REQUEST (PARENT LEVEL)
export async function acceptRejectPurchaseRequestNew(
  token: string,
  purchaseId: number,
  action: 'accept' | 'reject',
  remark = ''
): Promise<{ success: boolean; message: string; request_status?: string; data?: any }> {
  const payload: Record<string, any> = {
    purchase_id: purchaseId,
    action,
    remark,
  };

  const rawResponse = await fetchCrmEndpoint<any>(
    OSP_ACCEPT_REJECT_DIRECT,
    OSP_ACCEPT_REJECT_PROXY,
    token,
    payload
  );

  if (rawResponse && rawResponse.status) {
    return {
      success: true,
      message: rawResponse.message || `Purchase request ${action}ed successfully`,
      request_status: rawResponse.request_status,
      data: rawResponse,
    };
  }

  return {
    success: false,
    message: rawResponse?.message || `Failed to ${action} purchase request`,
  };
}

// 4. UPDATE PARENT REQUEST STATUS
export async function updateParentRequestStatusNew(
  token: string,
  purchaseId: number,
  requestStatus: 'Accepted' | 'Completed' | 'Partial' | string
): Promise<{ success: boolean; message: string; request_status?: string; data?: any }> {
  const payload: Record<string, any> = {
    purchase_id: purchaseId,
    request_status: requestStatus,
  };

  const rawResponse = await fetchCrmEndpoint<any>(
    OSP_UPDATE_PARENT_STATUS_DIRECT,
    OSP_UPDATE_PARENT_STATUS_PROXY,
    token,
    payload
  );

  if (rawResponse && rawResponse.status) {
    return {
      success: true,
      message: rawResponse.message || `Request status updated to ${requestStatus}`,
      request_status: rawResponse.request_status || requestStatus,
      data: rawResponse,
    };
  }

  return {
    success: false,
    message: rawResponse?.message || 'Failed to update request status',
  };
}

// 5. TOGGLE ITEM MATERIAL ORDER STATUS (LINE ITEM LEVEL)
export async function toggleItemMaterialOrderNew(
  token: string,
  itemId: number,
  isOrdered: 1 | 0
): Promise<{ success: boolean; message: string; item_status?: string; data?: any }> {
  const payload: Record<string, any> = {
    item_id: itemId,
    is_ordered: isOrdered,
  };

  const rawResponse = await fetchCrmEndpoint<any>(
    OSP_ITEM_ORDER_TOGGLE_DIRECT,
    OSP_ITEM_ORDER_TOGGLE_PROXY,
    token,
    payload
  );

  if (rawResponse && rawResponse.status) {
    return {
      success: true,
      message: rawResponse.message || `Item status updated`,
      item_status: rawResponse.item_status || (isOrdered === 1 ? 'Ordered' : 'Pending'),
      data: rawResponse,
    };
  }

  return {
    success: false,
    message: rawResponse?.message || 'Failed to update item order status',
  };
}

// 6. UPDATE ITEM RECEIVING / REJECTION STATUS (LINE ITEM LEVEL)
export async function updateItemStatusNew(
  token: string,
  requestId: number,
  status: 'Recieved' | 'Reject',
  approvalRemarks = ''
): Promise<{ success: boolean; message: string; item_status?: string; approver_name?: string; approve_date?: string; data?: any }> {
  const payload: Record<string, any> = {
    request_id: requestId,
    status,
    approval_remarks: approvalRemarks,
  };

  const rawResponse = await fetchCrmEndpoint<any>(
    OSP_ITEM_STATUS_UPDATE_DIRECT,
    OSP_ITEM_STATUS_UPDATE_PROXY,
    token,
    payload
  );

  if (rawResponse && rawResponse.status) {
    return {
      success: true,
      message: rawResponse.message || `Item status updated to ${status}`,
      item_status: rawResponse.item_status || status,
      approver_name: rawResponse.approver_name,
      approve_date: rawResponse.approve_date,
      data: rawResponse,
    };
  }

  return {
    success: false,
    message: rawResponse?.message || `Failed to update item status to ${status}`,
  };
}

// 7. UPLOAD SITE PHOTOS FOR PURCHASE REQUEST
export async function uploadOnSitePurchasePhotosNew(
  token: string,
  purchaseId: number,
  files: File[] | FileList
): Promise<{ success: boolean; message: string; photos?: OnSitePurchasePhoto[]; uploaded_count?: number }> {
  const { token: effectiveToken, userId } = getEffectiveTokenAndUserId(token);
  const formData = new FormData();
  formData.append('token', effectiveToken);
  formData.append('user_id', userId);
  formData.append('purchase_id', String(purchaseId));

  const fileArray = Array.from(files);
  fileArray.forEach((f) => {
    formData.append('photos[]', f);
    formData.append('files[]', f);
  });

  const baseHeaders: Record<string, string> = {
    'Accept': 'application/json',
  };
  if (effectiveToken) {
    baseHeaders['Authorization'] = effectiveToken.startsWith('Bearer ') ? effectiveToken : `Bearer ${effectiveToken}`;
    baseHeaders['token'] = effectiveToken;
  }

  const isApk = isMobileApkEnvironment();
  const urls = isApk ? [OSP_UPLOAD_PHOTOS_DIRECT, OSP_UPLOAD_PHOTOS_PROXY] : [OSP_UPLOAD_PHOTOS_PROXY, OSP_UPLOAD_PHOTOS_DIRECT];

  for (const url of urls) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: baseHeaders,
        body: formData,
      });
      const data = await safeParseJson<any>(res);
      if (data && data.status) {
        const rawPhotos = Array.isArray(data.photos) ? data.photos : [];
        const photos: OnSitePurchasePhoto[] = rawPhotos.map((p: any) => {
          let u = p.file_url || p.url || p.path || '';
          if (u && !u.startsWith('http://') && !u.startsWith('https://')) {
            u = `https://crm.hcinterior.in/${u.replace(/^\//, '')}`;
          }
          return {
            fileName: p.fileName || p.file_name || 'photo.jpg',
            path: p.path || '',
            is_image: true,
            file_url: u,
          };
        });
        return {
          success: true,
          message: data.message || `${fileArray.length} photos uploaded successfully`,
          photos,
          uploaded_count: data.uploaded_count || fileArray.length,
        };
      }
    } catch (e: any) {
      console.warn(`Upload photos failed via ${url}:`, e);
    }
  }

  return { success: false, message: 'Failed to upload photos' };
}

// 8. DELETE SITE PHOTO
export async function deleteOnSitePurchasePhotoNew(
  token: string,
  purchaseId: number,
  fileName: string
): Promise<{ success: boolean; message: string; photos?: OnSitePurchasePhoto[] }> {
  const payload: Record<string, any> = {
    purchase_id: purchaseId,
    file_name: fileName,
  };

  const rawResponse = await fetchCrmEndpoint<any>(
    OSP_DELETE_PHOTO_DIRECT,
    OSP_DELETE_PHOTO_PROXY,
    token,
    payload
  );

  if (rawResponse && rawResponse.status) {
    const rawPhotos = Array.isArray(rawResponse.photos) ? rawResponse.photos : [];
    const photos: OnSitePurchasePhoto[] = rawPhotos.map((p: any) => {
      let u = p.file_url || p.url || p.path || '';
      if (u && !u.startsWith('http://') && !u.startsWith('https://')) {
        u = `https://crm.hcinterior.in/${u.replace(/^\//, '')}`;
      }
      return {
        fileName: p.fileName || p.file_name || '',
        path: p.path || '',
        is_image: true,
        file_url: u,
      };
    });
    return {
      success: true,
      message: rawResponse.message || 'Photo deleted successfully',
      photos,
    };
  }

  return {
    success: false,
    message: rawResponse?.message || 'Failed to delete photo',
  };
}

// 9. DELETE ON SITE PURCHASE REQUEST
export async function deleteOnSitePurchaseRequestNew(
  token: string,
  purchaseId: number
): Promise<{ success: boolean; message: string }> {
  const payload: Record<string, any> = {
    purchase_id: purchaseId,
  };

  const rawResponse = await fetchCrmEndpoint<any>(
    OSP_DELETE_REQUEST_DIRECT,
    OSP_DELETE_REQUEST_PROXY,
    token,
    payload
  );

  if (rawResponse && rawResponse.status) {
    return {
      success: true,
      message: rawResponse.message || 'Purchase request deleted successfully',
    };
  }

  return {
    success: false,
    message: rawResponse?.message || 'Failed to delete purchase request',
  };
}

// 10. GET PURCHASE ITEM CATEGORIES LIST
export async function fetchPurchaseItemCategories(
  token?: string
): Promise<{ success: boolean; data: PurchaseItemCategory[]; message: string }> {
  const { token: effectiveToken } = getEffectiveTokenAndUserId(token);
  const rawResponse = await fetchCrmEndpoint<PurchaseItemCategoryResponse>(
    OSP_CATEGORY_LIST_DIRECT,
    OSP_CATEGORY_LIST_PROXY,
    effectiveToken,
    {}
  );

  if (!rawResponse || !rawResponse.status || !Array.isArray(rawResponse.data)) {
    console.warn('Purchase item categories API response:', rawResponse?.message);
    return {
      success: false,
      data: [],
      message: rawResponse?.message || 'Failed to fetch purchase item categories',
    };
  }

  return {
    success: true,
    data: rawResponse.data,
    message: rawResponse.message || 'Purchase item categories fetched successfully',
  };
}

// 11. GET PURCHASE ITEM BRANDS LIST (Optionally Filtered by Category ID)
export async function fetchPurchaseItemBrands(
  token?: string,
  categoryId?: number | string
): Promise<{ success: boolean; data: PurchaseItemBrand[]; message: string }> {
  const { token: effectiveToken } = getEffectiveTokenAndUserId(token);
  const payload: Record<string, any> = {};
  if (categoryId !== undefined && categoryId !== null && categoryId !== '') {
    payload.category_id = categoryId;
    payload.purchase_item_category_id = categoryId;
  }

  const rawResponse = await fetchCrmEndpoint<PurchaseItemBrandResponse>(
    OSP_BRAND_LIST_DIRECT,
    OSP_BRAND_LIST_PROXY,
    effectiveToken,
    payload
  );

  if (!rawResponse || !rawResponse.status || !Array.isArray(rawResponse.data)) {
    console.warn('Purchase item brands API response:', rawResponse?.message);
    return {
      success: false,
      data: [],
      message: rawResponse?.message || 'Failed to fetch purchase item brands',
    };
  }

  return {
    success: true,
    data: rawResponse.data,
    message: rawResponse.message || 'Purchase item brands fetched successfully',
  };
}

export function mapApiItemToLooseFurniture(raw: any): LooseFurnitureItem {
  const id = raw.id || Math.random().toString();
  const clientName = raw.client_name || raw.clientName || 'N/A';
  const clientId =
    raw.client_sr_id && raw.client_sr_id !== '-'
      ? raw.client_sr_id
      : raw.client_id
      ? `HC${raw.client_id}`
      : raw.clientId || 'N/A';

  const uploadFiles: string[] = Array.isArray(raw.upload_file)
    ? raw.upload_file
    : typeof raw.upload_file === 'string' && raw.upload_file
    ? [raw.upload_file]
    : [];

  const fileName =
    raw.file_name ||
    raw.fileName ||
    (uploadFiles[0] ? uploadFiles[0].split('/').pop() : 'Loose Furniture File');

  return {
    id,
    clientName,
    clientId,
    client_id: raw.client_id,
    client_sr_id: raw.client_sr_id,
    client_name: raw.client_name,
    fileName,
    file_name: raw.file_name,
    status: raw.status || 'Pending',
    status_approve_by: raw.status_approve_by,
    status_approve_date: raw.status_approve_date,
    upload_file: uploadFiles,
    upload_url: raw.upload_url || '',
    remark: raw.remark || '',
    date: raw.created_date || raw.date || '',
    created_date: raw.created_date,
    updated_date: raw.updated_date,
  };
}

const LOOSE_FURNITURE_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/client/loose_furniture_list';
const LOOSE_FURNITURE_PROXY_URL = '/crm-api/mobileapi/client/loose_furniture_list';

export interface ApiLooseFurnitureResponse {
  status: boolean;
  message?: string;
  data?: any;
  page?: number;
  total?: number;
  total_pages?: number;
}

export async function fetchLooseFurnitureList(
  token: string,
  clientId?: string | number,
  status = '',
  page = 1,
  limit = 50
): Promise<LooseFurnitureItem[]> {
  const payloadObj: Record<string, any> = {
    page,
    limit,
    client_id: clientId || '',
    status: status || '',
  };

  const rawResponse = await fetchCrmEndpoint<ApiLooseFurnitureResponse>(
    LOOSE_FURNITURE_DIRECT_URL,
    LOOSE_FURNITURE_PROXY_URL,
    token,
    payloadObj
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('Loose Furniture API response status false/empty:', rawResponse?.message);
    return [];
  }

  let list: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    list = rawResponse.data;
  } else if (rawResponse.data && Array.isArray(rawResponse.data.data)) {
    list = rawResponse.data.data;
  } else if (rawResponse.data && typeof rawResponse.data === 'object') {
    const possibleArray = Object.values(rawResponse.data).find((v) => Array.isArray(v));
    if (possibleArray && Array.isArray(possibleArray)) {
      list = possibleArray;
    }
  }

  return list.map(mapApiItemToLooseFurniture);
}

export function mapApiItemToDispatch(raw: any): DispatchItem {
  const id = raw.id || Math.random().toString();
  const clientName = raw.client_name || raw.clientName || 'N/A';
  const clientId =
    raw.client_sr_id && raw.client_sr_id !== '-'
      ? raw.client_sr_id
      : raw.client_id
      ? `HC${raw.client_id}`
      : raw.clientId || 'N/A';

  const uploadFiles: string[] = Array.isArray(raw.upload_file)
    ? raw.upload_file
    : typeof raw.upload_file === 'string' && raw.upload_file
    ? [raw.upload_file]
    : [];

  const fileName =
    raw.file_name ||
    raw.fileName ||
    (uploadFiles[0] ? uploadFiles[0].split('/').pop() : 'Dispatch File');

  const queries: DispatchQuery[] = Array.isArray(raw.queries)
    ? raw.queries.map((q: any) => ({
        id: q.id || Math.random().toString(),
        dispatch_id: q.dispatch_id || raw.id,
        client_id: q.client_id || raw.client_id,
        remarks: q.remarks || '',
        uploaded_by: q.uploaded_by,
        uploaded_by_name: q.uploaded_by_name || 'Staff',
        file_name: q.file_name || '',
        file_url: q.file_url || '',
        created_date: q.created_date || '',
        updated_date: q.updated_date || '',
      }))
    : [];

  return {
    id,
    client_id: raw.client_id,
    clientName,
    clientId,
    client_sr_id: raw.client_sr_id,
    client_name: raw.client_name,
    itemName: fileName,
    file_name: raw.file_name,
    status: raw.status || 'Pending',
    status_approve_by: raw.status_approve_by,
    status_approve_date: raw.status_approve_date,
    upload_file: uploadFiles,
    upload_url: raw.upload_url || '',
    remark: raw.remark || '',
    created_date: raw.created_date,
    updated_date: raw.updated_date,
    is_return: raw.is_return,
    return_remark: raw.return_remark,
    is_recived: raw.is_recived,
    recived_date: raw.recived_date,
    queries,
    total_queries: raw.total_queries ?? queries.length,
    imageUrl: uploadFiles[0] || 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=400&q=80',
    date: raw.created_date || raw.date || '',
    requestType: 'dispatch',
    dispatchType: 'Full',
  };
}

const DISPATCH_LIST_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/client/dispatch_list';
const DISPATCH_LIST_PROXY_URL = '/crm-api/mobileapi/client/dispatch_list';

export interface ApiDispatchListResponse {
  status: boolean;
  message?: string;
  data?: any;
  page?: number;
  total?: number;
  total_pages?: number;
}

export async function fetchDispatchList(
  token: string,
  clientId?: string | number,
  status = '',
  isRecived?: string | number,
  startDate = '',
  endDate = '',
  page = 1,
  limit = 50
): Promise<DispatchItem[]> {
  const payloadObj: Record<string, any> = { page, limit };
  if (clientId) payloadObj.client_id = clientId;
  if (status) payloadObj.status = status;
  if (isRecived !== undefined && isRecived !== '') payloadObj.is_recived = isRecived;
  if (startDate) payloadObj.start_date = startDate;
  if (endDate) payloadObj.end_date = endDate;

  const rawResponse = await fetchCrmEndpoint<ApiDispatchListResponse>(
    DISPATCH_LIST_DIRECT_URL,
    DISPATCH_LIST_PROXY_URL,
    token,
    payloadObj
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('Dispatch API response status false/empty:', rawResponse?.message);
    return [];
  }

  let list: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    list = rawResponse.data;
  } else if (rawResponse.data && Array.isArray(rawResponse.data.data)) {
    list = rawResponse.data.data;
  } else if (rawResponse.data && typeof rawResponse.data === 'object') {
    const possibleArray = Object.values(rawResponse.data).find((v) => Array.isArray(v));
    if (possibleArray && Array.isArray(possibleArray)) {
      list = possibleArray;
    }
  }

  return list.map(mapApiItemToDispatch);
}

const ADD_DISPATCH_QUERY_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/client/add_dispatch_query';
const ADD_DISPATCH_QUERY_PROXY_URL = '/crm-api/mobileapi/client/add_dispatch_query';

export async function addDispatchQuery(
  token: string,
  dispatchId: number | string,
  clientId: number | string,
  remarks: string,
  file?: File | null
): Promise<{ status: boolean; message?: string; data?: any }> {
  const formData = new FormData();
  if (token) formData.append('token', token);
  formData.append('dispatch_id', String(dispatchId));
  formData.append('client_id', String(clientId));
  formData.append('remarks', remarks);
  if (file) {
    formData.append('file', file);
  }

  const headers: Record<string, string> = {};
  if (token) {
    headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    headers['token'] = token;
    headers['X-Api-Token'] = token;
  }

  try {
    const res = await fetch(ADD_DISPATCH_QUERY_PROXY_URL, {
      method: 'POST',
      headers,
      body: formData,
    });
    const parsed = await safeParseJson<any>(res);
    if (parsed && parsed.status) {
      return parsed;
    }
  } catch (e) {
    console.warn('Proxy POST add_dispatch_query failed:', e);
  }

  try {
    const res = await fetch(ADD_DISPATCH_QUERY_DIRECT_URL, {
      method: 'POST',
      headers,
      body: formData,
    });
    const parsed = await safeParseJson<any>(res);
    if (parsed) {
      return parsed;
    }
  } catch (e) {
    console.warn('Direct POST add_dispatch_query failed:', e);
  }

  return { status: false, message: 'Failed to add dispatch query' };
}

export function mapApiItemToBOM(raw: any): BOMRecord {
  let uploadFiles: string[] = [];
  if (Array.isArray(raw.upload_file)) {
    uploadFiles = raw.upload_file.map((f: any) => String(f));
  } else if (typeof raw.upload_file === 'string' && raw.upload_file) {
    uploadFiles = [raw.upload_file];
  }

  const img = uploadFiles[0] || raw.upload_url || '';

  const rawChildren = Array.isArray(raw.children) ? raw.children : [];
  const mappedChildren = rawChildren.map(mapApiItemToBOM);

  return {
    id: String(raw.id),
    clientId: raw.client_sr_id || (raw.client_id ? `HC${raw.client_id}` : ''),
    client_id: raw.client_id,
    clientName: raw.client_name || '',
    client_sr_id: raw.client_sr_id || '',
    fileName: raw.file_name || 'BOM',
    category: raw.category || 'Category 1',
    type: raw.type || 'BOM',
    fileUrl: img || '#',
    imageUrl: img || undefined,
    date: raw.created_date || raw.date || '',
    status: raw.status || 'Accepted',
    parentId: raw.parent_bom_id ? String(raw.parent_bom_id) : undefined,
    children: mappedChildren,
    remark: raw.remark || '',
    upload_file: uploadFiles,
    vendor: raw.vendor || '',
    design_type: raw.design_type || '',
  };
}

const BOM_LIST_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/client/bom_list';
const BOM_LIST_PROXY_URL = '/crm-api/mobileapi/client/bom_list';

export interface ApiBOMListResponse {
  status: boolean;
  message?: string;
  data?: any;
  page?: number;
  total?: number;
  total_pages?: number;
}

export async function fetchBOMList(
  token: string,
  clientId?: string | number,
  status = '',
  startDate = '',
  endDate = '',
  page = 1,
  limit = 50
): Promise<BOMRecord[]> {
  const payloadObj: Record<string, any> = { page, limit };
  if (clientId) payloadObj.client_id = clientId;
  if (status) payloadObj.status = status;
  if (startDate) payloadObj.start_date = startDate;
  if (endDate) payloadObj.end_date = endDate;

  const rawResponse = await fetchCrmEndpoint<ApiBOMListResponse>(
    BOM_LIST_DIRECT_URL,
    BOM_LIST_PROXY_URL,
    token,
    payloadObj
  );

  if (!rawResponse || !rawResponse.status) {
    console.warn('BOM API response status false/empty:', rawResponse?.message);
    return [];
  }

  let list: any[] = [];
  if (Array.isArray(rawResponse.data)) {
    list = rawResponse.data;
  } else if (rawResponse.data && Array.isArray(rawResponse.data.data)) {
    list = rawResponse.data.data;
  } else if (rawResponse.data && typeof rawResponse.data === 'object') {
    const possibleArray = Object.values(rawResponse.data).find((v) => Array.isArray(v));
    if (possibleArray && Array.isArray(possibleArray)) {
      list = possibleArray;
    }
  }

  return list.map(mapApiItemToBOM);
}

const BOQ_LIST_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/boq_list';
const BOQ_LIST_PROXY_URL = '/crm-api/mobileapi/Client/boq_list';

export async function fetchBoqList(
  token: string,
  clientId: number | string,
  page = 1,
  limit = 20
): Promise<ApiBoqListResponse | null> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    headers['token'] = token;
    headers['X-Api-Token'] = token;
  }

  const payload = JSON.stringify({
    client_id: Number(clientId),
    page,
    limit,
    token,
  });

  try {
    const res = await fetch(BOQ_LIST_PROXY_URL, {
      method: 'POST',
      headers,
      body: payload,
    });
    const data = await safeParseJson<ApiBoqListResponse>(res);
    if (data && typeof data.status !== 'undefined') {
      return data;
    }
  } catch (e) {
    console.warn('Proxy fetch BOQ list failed:', e);
  }

  try {
    const res = await fetch(BOQ_LIST_DIRECT_URL, {
      method: 'POST',
      headers,
      body: payload,
    });
    const data = await safeParseJson<ApiBoqListResponse>(res);
    if (data && typeof data.status !== 'undefined') {
      return data;
    }
  } catch (e) {
    console.warn('Direct fetch BOQ list failed:', e);
  }

  return null;
}

const EXECUTION_TIMELINE_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/execution_timeline';
const EXECUTION_TIMELINE_PROXY_URL = '/crm-api/mobileapi/Client/execution_timeline';

const UPDATE_TIMELINE_DIRECT_URL = 'https://crm.hcinterior.in/mobileapi/Client/update_timeline';
const UPDATE_TIMELINE_PROXY_URL = '/crm-api/mobileapi/Client/update_timeline';

export async function fetchExecutionTimeline(
  token: string,
  clientId: number | string
): Promise<ApiExecutionTimelineResponse | null> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    headers['token'] = token;
    headers['X-Api-Token'] = token;
  }

  const clientIdNum = typeof clientId === 'number' ? clientId : parseInt(String(clientId).replace(/\D/g, ''), 10) || 531;

  const payload = JSON.stringify({
    token,
    client_id: clientIdNum,
  });

  try {
    const res = await fetch(EXECUTION_TIMELINE_PROXY_URL, {
      method: 'POST',
      headers,
      body: payload,
    });
    const data = await safeParseJson<ApiExecutionTimelineResponse>(res);
    if (data && typeof data.status !== 'undefined') {
      return data;
    }
  } catch (e) {
    console.warn('Proxy fetch execution timeline failed:', e);
  }

  try {
    const res = await fetch(EXECUTION_TIMELINE_DIRECT_URL, {
      method: 'POST',
      headers,
      body: payload,
    });
    const data = await safeParseJson<ApiExecutionTimelineResponse>(res);
    if (data && typeof data.status !== 'undefined') {
      return data;
    }
  } catch (e) {
    console.warn('Direct fetch execution timeline failed:', e);
  }

  return null;
}

export async function updateExecutionTimeline(
  token: string,
  clientId: number | string,
  tasks: ApiUpdateTimelineTaskPayload[]
): Promise<ApiUpdateTimelineResponse | null> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    headers['token'] = token;
    headers['X-Api-Token'] = token;
  }

  const clientIdNum = typeof clientId === 'number' ? clientId : parseInt(String(clientId).replace(/\D/g, ''), 10) || 531;

  const payload = JSON.stringify({
    token,
    client_id: clientIdNum,
    tasks,
  });

  try {
    const res = await fetch(UPDATE_TIMELINE_PROXY_URL, {
      method: 'POST',
      headers,
      body: payload,
    });
    const data = await safeParseJson<ApiUpdateTimelineResponse>(res);
    if (data && typeof data.status !== 'undefined') {
      return data;
    }
  } catch (e) {
    console.warn('Proxy update execution timeline failed:', e);
  }

  try {
    const res = await fetch(UPDATE_TIMELINE_DIRECT_URL, {
      method: 'POST',
      headers,
      body: payload,
    });
    const data = await safeParseJson<ApiUpdateTimelineResponse>(res);
    if (data && typeof data.status !== 'undefined') {
      return data;
    }
  } catch (e) {
    console.warn('Direct update execution timeline failed:', e);
  }

  return null;
}



