import { initializeApp, cert, getApps, App } from 'firebase-admin/app';
import { getMessaging } from 'firebase-admin/messaging';

const RAW_KEY = `-----BEGIN PRIVATE KEY-----
MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDW1Tjv2vlVxJep
FfcdORKoPXEfz+4Axf0eAn2T4xrcWAXq/1UTiqNwEubxpuhb3a57Hdy4w7S5b1kY
DsUXXgwzUbZ0AnMXiEC+r2ZmA13wU83/rHnTJYbTNLDXlHzUMdKhnKVTfZPqlX5J
IftA/5rFOupAh6RC9E+uudc2oE9If6Cd+GgWDjMdlWV/BBuoHi10crxhtQNFVlwt
17WJWaIsCtdA8MRTBjQIdeB1oMGoc2Hw2XBf5nuv2ZWX2pXt9hOVMz4Ai79BYndl
A/l9Kd6bMNT+QKj0C5+tp9c8GFOxwFEmz4X2Vp4soawhGycIuk56WwrL/mwWPmNt
kS6D9/6tAgMBAAECggEAAIISl2aiLlS/sxOtOPF4nNNAYD3ipzfQqHCDEYV4+8pt
gSfHcLtkNZCl4S5u4EC+9+WTLxCvbbzYrhji2HJNfxWTY+TZltWoqYrKqDUb1MSt
+QvUYe51yiwWuvvL68iOYfl33qGuZuChotSkvnucRxkjkrF/bvUa9yPTYXqxlU9O
2pGqz0nVbMt5X8JvS8J1iKb3nIZC15lUyDQi2P6vo5ghUGermNl3tzDThGlMmkdL
PgZKHYfeW1bdPCBLVhj5/SCVGHHX1vgqUHUpqHJlJShNq/vJdaxM1K1bkgTZXLFO
/k67GdNxzMqP9i+t0vto6ZaEIEMvfo+AnhHl/WEJoQKBgQD3HFheVNa7hOCVanS4
kt3ZfAOz4sn8sWH++2h8P6eHWNtBq3drVythTpyYSTqZW2C2vIuxLkp4Eys0pCbn
+2ZCbaeJ34+U17cW23krslYwFB6pwZlsc2oWpYX16CNpwmFnlKkglk/uwQEiTDOv
aC4iuceijklaJNVhJfWUaLpImQKBgQDej6EQbzmXlziptRUTfA5Lh0vp9GSjtloi
gdjXoFmFt31ci5cSwfEDL964FzsxQa62PVXGGNyc3mF0yYmgFmFt4uLfBqkdgm9z
U+caaUtFkD1kkceJXlgIPlMFCpfLT7VQmt0M64MsjbZq3cF2iQneIVAp2HHBbvAl
q4ET3hYPNQKBgQChRDxfg5qH0lYG7OyzsBVcY9S+xtjvMowzrbsoqxoX+GNO3ioX
QVIsNPN5ZwD5KGtblnzL6tvqtQfTWPWTG7xGye02y1GW35i7MAxJ+h7JTbgdLR2F
a2Tm1qswKolB8ftDb/9YJwCPXiHxUi3A4YqKWxfv/E/epn8i4XT9n8NCsQKBgQCM
/lS9AplrrYNaD7vQYjD0LDwtdIQlKqqjXbsvrwfHrFygulX0riSvLi5cVWtMYx35
mZWzL6DjSAZZZCvp3QPQB4JOY/vTFATi/O5VTws+gIhEJA5Sug/u+PzDHtjXFiH5
dIJBYxnwqb48qUucemhj7prIR7SZJFzCoInfOjyjWQKBgQDu5CR4/WW02l7R/ei0
dnhIsgYDhzZhfd7m4V+EmHq+kLYmM4AcfZR7RvDXlVIkIpMMdhtR2Prl30ZuO9jW
0a9qJvIzO2X1GMuA5VYgCxERFJgCpDln3pRAhUt40aaibevcad5JU+jLJt0mY2A4
tFBRE26j3QYIJec4AhRx0tACgg==
-----END PRIVATE KEY-----`;

function getFormattedPrivateKey(): string {
  if (process.env.FIREBASE_PRIVATE_KEY) {
    return process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n');
  }
  return RAW_KEY.trim();
}

// Service Account Credentials provided for hc-interior
const serviceAccount = {
  projectId: process.env.FIREBASE_PROJECT_ID || 'hc-interior',
  clientEmail: process.env.FIREBASE_CLIENT_EMAIL || 'firebase-adminsdk-fbsvc@hc-interior.iam.gserviceaccount.com',
  privateKey: getFormattedPrivateKey(),
};

let firebaseApp: App | null = null;

export function getFirebaseAdmin(): App | null {
  if (!firebaseApp) {
    const existingApps = getApps();
    if (existingApps.length === 0) {
      try {
        firebaseApp = initializeApp({
          credential: cert(serviceAccount),
        });
        console.log('Firebase Admin SDK initialized successfully for project:', serviceAccount.projectId);
      } catch (err) {
        console.error('Failed to initialize Firebase Admin SDK:', err);
        return null;
      }
    } else {
      firebaseApp = existingApps[0]!;
    }
  }
  return firebaseApp;
}

// Memory cache for registered device tokens
const registeredDeviceTokens: Set<string> = new Set();

/**
 * Converts a raw 64-character Apple APNs token to a real Google FCM Registration Token
 * via Google's Instance ID (IID) batchImport API.
 */
export async function convertApnsToFcmToken(apnsToken: string, isSandbox = false): Promise<string> {
  if (!apnsToken || typeof apnsToken !== 'string') return apnsToken;
  const trimmed = apnsToken.trim();

  // If already a real Google FCM token (contains : or APA91b), return as is
  if (trimmed.includes(':') || trimmed.includes('APA91b')) {
    return trimmed;
  }

  // Convert raw 32 to 64 character hexadecimal APNs / device token
  if (trimmed.length < 32 || trimmed.length > 64 || !/^[0-9a-fA-F]+$/.test(trimmed)) {
    return apnsToken;
  }

  const app = getFirebaseAdmin();
  if (!app) return trimmed;

  try {
    const credential = (app.options as any).credential;
    if (credential && typeof credential.getAccessToken === 'function') {
      const authObj = await credential.getAccessToken();
      const accessToken = authObj?.access_token;

      if (accessToken) {
        const res = await fetch('https://iid.googleapis.com/iid/v1:batchImport', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${accessToken}`,
            'access_token_auth': 'true',
          },
          body: JSON.stringify({
            application: 'com.HCIP.HCOperation',
            sandbox: isSandbox,
            apns_tokens: [trimmed],
          }),
        });

        const data = await res.json();
        console.log('Google FCM BatchImport IID result:', JSON.stringify(data));
        if (data && data.results && data.results[0] && data.results[0].status === 'OK') {
          const fcmToken = data.results[0].registration_token || data.results[0].token;
          console.log('Successfully exchanged APNs token for Google FCM Token:', fcmToken);
          return fcmToken;
        }
      }
    }
  } catch (err) {
    console.warn('Failed to convert APNs token to FCM token:', err);
  }
  return trimmed;
}

export async function registerFCMToken(token: string) {
  if (token) {
    // Automatically convert raw APNs token to FCM token if needed
    const effectiveToken = await convertApnsToFcmToken(token);
    registeredDeviceTokens.add(effectiveToken);

    const app = getFirebaseAdmin();
    if (app) {
      try {
        const messaging = getMessaging(app);
        await messaging.subscribeToTopic([effectiveToken], 'global_updates');
        await messaging.subscribeToTopic([effectiveToken], 'all');
      } catch (err) {
        console.warn('FCM Topic subscription warning:', err);
      }
    }
  }
}

export function getRegisteredTokens(): string[] {
  return Array.from(registeredDeviceTokens);
}

export async function sendFCMNotification(params: {
  title: string;
  body: string;
  topic?: string;
  token?: string;
  data?: Record<string, string>;
}) {
  const app = getFirebaseAdmin();
  if (!app) {
    console.warn('Firebase Admin App not available, returning simulated status');
    return 'simulated-message-id-' + Date.now();
  }

  const messaging = getMessaging(app);

  const payload = {
    notification: {
      title: params.title,
      body: params.body,
    },
    apns: {
      headers: {
        'apns-priority': '10',
        'apns-push-type': 'alert',
        'apns-topic': 'com.HCIP.HCOperation',
      },
      payload: {
        aps: {
          alert: {
            title: params.title,
            body: params.body,
          },
          sound: 'default',
          badge: 1,
          'content-available': 1,
        },
      },
    },
    android: {
      priority: 'high' as const,
      notification: {
        title: params.title,
        body: params.body,
        sound: 'default',
        channelId: 'default',
      },
    },
    data: params.data || {},
  };

  if (params.token) {
    const effectiveToken = await convertApnsToFcmToken(params.token);
    return await messaging.send({
      ...payload,
      token: effectiveToken,
    });
  } else if (params.topic) {
    return await messaging.send({
      ...payload,
      topic: params.topic,
    });
  } else {
    return await messaging.send({
      ...payload,
      topic: 'global_updates',
    });
  }
}
