import { ensureFcmFormatToken } from './fcmService';

export interface UserData {
  user_id: string;
  username: string;
  email: string;
  role_id: string;
  fullname: string;
  employment_id: string;
  designation_id: string;
  avatar: string;
  phone: string;
  mobile: string;
  reporting_to: string;
  regional_manager: string;
  team_leader: string;
}

export interface LoginResponse {
  status: boolean;
  message: string;
  token?: string;
  data?: UserData;
}

export interface LogoutResponse {
  status: boolean;
  message: string;
}

export function isMobileApkEnvironment(): boolean {
  if (typeof window === 'undefined') return false;
  const protocol = window.location.protocol;
  return (
    protocol === 'file:' ||
    protocol === 'capacitor:' ||
    protocol === 'ionic:' ||
    (!!(window as any).Capacitor && (window as any).Capacitor.isNativePlatform && (window as any).Capacitor.isNativePlatform()) ||
    !!(window as any).cordova
  );
}

const DIRECT_URL = 'https://crm.hcinterior.in';
const CLOUD_PROXY_URL = 'https://ais-pre-p4l3km6ranu5hkwm757tro-734024418090.asia-southeast1.run.app/crm-api';
const PROXY_URL = '/crm-api';

async function safeParseJson<T>(res: Response): Promise<T | null> {
  try {
    const text = await res.text();
    if (!text || text.trim().startsWith('<') || (!text.trim().startsWith('{') && !text.trim().startsWith('['))) {
      console.warn('Received non-JSON response:', text.slice(0, 150));
      return null;
    }
    return JSON.parse(text) as T;
  } catch (e) {
    console.warn('Error parsing JSON:', e);
  }
}

export async function loginUser(
  username: string,
  password: string,
  fcmToken?: string,
  deviceType: string = 'android'
): Promise<LoginResponse> {
  const effectiveFcmToken = await ensureFcmFormatToken(fcmToken);

  const payloadObj = {
    username,
    password,
    fcm_token: effectiveFcmToken || '',
    device_type: deviceType,
  };

  const jsonBody = JSON.stringify(payloadObj);

  const urlParams = new URLSearchParams();
  urlParams.append('username', username);
  urlParams.append('password', password);
  if (effectiveFcmToken) urlParams.append('fcm_token', effectiveFcmToken);
  urlParams.append('device_type', deviceType);

  const formData = new FormData();
  formData.append('username', username);
  formData.append('password', password);
  if (effectiveFcmToken) formData.append('fcm_token', effectiveFcmToken);
  formData.append('device_type', deviceType);

  // Candidate paths for CodeIgniter / PHP login
  const endpointPaths = [
    '/login/mobile_login',
    '/mobileapi/Login/mobile_login',
    '/mobileapi/login/mobile_login',
    '/index.php/login/mobile_login',
    '/index.php/mobileapi/login/mobile_login',
  ];

  const isApk = isMobileApkEnvironment();

  // Try Cloud Proxy first (bypasses CORS in APKs), then Direct URL, then local proxy
  const bases = isApk 
    ? [DIRECT_URL, CLOUD_PROXY_URL] 
    : [PROXY_URL, DIRECT_URL, CLOUD_PROXY_URL];

  for (const base of bases) {
    for (const path of endpointPaths) {
      const fullUrl = `${base}${path}`;

      // 1. Try JSON Body
      try {
        const res = await fetch(fullUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonBody,
        });
        const data = await safeParseJson<LoginResponse>(res);
        if (data && typeof data.status !== 'undefined') {
          return data;
        }
      } catch (e) {
        console.warn(`JSON login failed on ${fullUrl}:`, e);
      }

      // 2. Try URL Search Params (x-www-form-urlencoded)
      try {
        const res = await fetch(fullUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
          },
          body: urlParams.toString(),
        });
        const data = await safeParseJson<LoginResponse>(res);
        if (data && typeof data.status !== 'undefined') {
          return data;
        }
      } catch (e) {
        console.warn(`URLSearchParams login failed on ${fullUrl}:`, e);
      }

      // 3. Try FormData
      try {
        const res = await fetch(fullUrl, {
          method: 'POST',
          body: formData,
        });
        const data = await safeParseJson<LoginResponse>(res);
        if (data && typeof data.status !== 'undefined') {
          return data;
        }
      } catch (e) {
        console.warn(`FormData login failed on ${fullUrl}:`, e);
      }
    }
  }

  throw new Error('Unable to connect to CRM authentication server. Please check your network or login credentials.');
}

export async function logoutUser(providedToken?: string, userId?: string): Promise<LogoutResponse> {
  let token = providedToken || '';

  // If providedToken looks like a numeric user_id, fallback to looking up stored auth_token
  if ((!token || /^\d+$/.test(token)) && typeof localStorage !== 'undefined') {
    const storedToken = localStorage.getItem('auth_token');
    if (storedToken) {
      token = storedToken;
    }
  }

  if (!token && typeof localStorage !== 'undefined') {
    const rawUserData = localStorage.getItem('user_data');
    if (rawUserData) {
      try {
        const u = JSON.parse(rawUserData);
        token = String(u.token || u.auth_token || u.access_token || '');
      } catch (e) {}
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

  // Pass token in payload without user_id
  const payloadObj: Record<string, any> = {};
  if (token) {
    payloadObj.token = token;
  }

  const jsonBody = JSON.stringify(payloadObj);

  const urlParams = new URLSearchParams();
  if (token) urlParams.append('token', token);

  const formData = new FormData();
  if (token) formData.append('token', token);

  const endpointPaths = [
    '/login/mobile_logout',
    '/mobileapi/Login/mobile_logout',
    '/mobileapi/login/mobile_logout',
    '/index.php/login/mobile_logout',
    '/index.php/mobileapi/login/mobile_logout',
  ];

  const isApk = isMobileApkEnvironment();
  const bases = isApk 
    ? [DIRECT_URL, CLOUD_PROXY_URL] 
    : [PROXY_URL, DIRECT_URL, CLOUD_PROXY_URL];

  for (const base of bases) {
    for (const path of endpointPaths) {
      const fullUrl = `${base}${path}`;

      // 1. Try JSON Body
      try {
        const res = await fetch(fullUrl, {
          method: 'POST',
          headers: {
            ...baseHeaders,
            'Content-Type': 'application/json',
          },
          body: jsonBody,
        });
        const data = await safeParseJson<LogoutResponse>(res);
        if (data && typeof data.status !== 'undefined') {
          return data;
        }
      } catch (e) {
        console.warn(`JSON logout failed on ${fullUrl}:`, e);
      }

      // 2. Try URL Search Params
      try {
        const res = await fetch(fullUrl, {
          method: 'POST',
          headers: {
            ...baseHeaders,
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: urlParams.toString(),
        });
        const data = await safeParseJson<LogoutResponse>(res);
        if (data && typeof data.status !== 'undefined') {
          return data;
        }
      } catch (e) {
        console.warn(`URLSearchParams logout failed on ${fullUrl}:`, e);
      }

      // 3. Try FormData
      try {
        const res = await fetch(fullUrl, {
          method: 'POST',
          headers: baseHeaders,
          body: formData,
        });
        const data = await safeParseJson<LogoutResponse>(res);
        if (data && typeof data.status !== 'undefined') {
          return data;
        }
      } catch (e) {
        console.warn(`FormData logout failed on ${fullUrl}:`, e);
      }
    }
  }

  return { status: true, message: 'Logged out successfully' };
}
