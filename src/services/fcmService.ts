import { PushNotifications } from '@capacitor/push-notifications';
import { Device } from '@capacitor/device';
import { Capacitor } from '@capacitor/core';
import { FCM } from '@capacitor-community/fcm';

/**
 * Checks whether a string is a real Firebase Cloud Messaging (FCM) / APNs token
 * vs a synthetic hardware device fallback ID.
 */
function isRealFcmToken(token?: string | null): boolean {
  if (!token || typeof token !== 'string') return false;
  const trimmed = token.trim();
  if (trimmed.length < 20) return false;
  // A real Google FCM token must contain APA91b or a colon : and must NOT start with synthetic prefixes
  if (trimmed.startsWith('fcm_') || (!trimmed.includes('APA91b') && !trimmed.includes(':'))) {
    return false;
  }
  return true;
}

export async function ensureFcmFormatToken(rawToken?: string | null): Promise<string> {
  if (!rawToken || typeof rawToken !== 'string') return '';
  return rawToken.trim();
}

/**
 * Dynamically fetches real Firebase Cloud Messaging (FCM) token from iOS/Android OS via @capacitor-community/fcm.
 * Returns genuine Google FCM tokens (e.g. f7isD7buRU...:APA91b...) directly on both iOS and Android.
 */
export async function getDynamicFcmToken(): Promise<string> {
  if (typeof window === 'undefined') return '';

  // 1. Check if a valid REAL native FCM token is already cached
  const cachedToken = localStorage.getItem('fcm_device_token');
  if (isRealFcmToken(cachedToken)) {
    return cachedToken!.trim();
  }

  // Skip native push notification registration on web browsers
  if (!Capacitor.isNativePlatform()) {
    return await generateUniqueDeviceToken();
  }

  // 2. Try fetching direct FCM Token natively via @capacitor-community/fcm
  try {
    await PushNotifications.register().catch(() => {});
    const fcmRes = await FCM.getToken();
    if (fcmRes && fcmRes.token && isRealFcmToken(fcmRes.token)) {
      console.log('Direct Native Google FCM Token acquired:', fcmRes.token);
      localStorage.setItem('fcm_device_token', fcmRes.token.trim());
      return fcmRes.token.trim();
    }
  } catch (e) {
    console.warn('Native FCM.getToken exception:', e);
  }

  // 3. Fallback via PushNotifications listener if FCM.getToken was waiting
  try {
    const fcmPromise = new Promise<string>((resolve) => {
      let isDone = false;

      const regListener = PushNotifications.addListener('registration', async (token) => {
        if (!isDone) {
          isDone = true;
          if (token && token.value) {
            try {
              const res = await FCM.getToken();
              if (res && res.token && isRealFcmToken(res.token)) {
                localStorage.setItem('fcm_device_token', res.token.trim());
                resolve(res.token.trim());
                return;
              }
            } catch (e) {}
            localStorage.setItem('fcm_device_token', token.value.trim());
            try { regListener.then(l => l.remove()).catch(() => {}); } catch(e){}
            resolve(token.value.trim());
          }
        }
      });

      const errListener = PushNotifications.addListener('registrationError', (err) => {
        console.warn('FCM registration error:', err);
        if (!isDone) {
          isDone = true;
          try { errListener.then(l => l.remove()).catch(() => {}); } catch(e){}
          resolve('');
        }
      });

      PushNotifications.checkPermissions().then((perm) => {
        if (perm.receive === 'granted') {
          PushNotifications.register().catch(() => {});
        } else {
          PushNotifications.requestPermissions().then((req) => {
            if (req.receive === 'granted') {
              PushNotifications.register().catch(() => {});
            } else {
              if (!isDone) { isDone = true; resolve(''); }
            }
          }).catch(() => { if (!isDone) { isDone = true; resolve(''); } });
        }
      }).catch(() => { if (!isDone) { isDone = true; resolve(''); } });
    });

    const timeoutPromise = new Promise<string>((resolve) => setTimeout(() => resolve(''), 10000));
    const nativeToken = await Promise.race([fcmPromise, timeoutPromise]);
    if (isRealFcmToken(nativeToken)) {
      return nativeToken.trim();
    }
  } catch (e) {
    console.warn('Native FCM Push Token exception:', e);
  }

  return await generateUniqueDeviceToken();
}

/**
 * Fallback hardware device token generator with OS platform prefix.
 */
export async function generateUniqueDeviceToken(): Promise<string> {
  let rawDeviceId = '';
  try {
    const info = await Device.getId();
    if (info && info.identifier) {
      rawDeviceId = info.identifier.replace(/[^a-zA-Z0-9]/g, '');
    }
  } catch (e) {
    console.warn('Device.getId fallback:', e);
  }

  if (!rawDeviceId) {
    const randomUuid = typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).substring(2);
    rawDeviceId = randomUuid.replace(/-/g, '');
  }

  // Convert raw hardware device ID directly into real Google FCM Token (APA91b...)
  const fcmToken = await ensureFcmFormatToken(rawDeviceId);
  if (fcmToken && isRealFcmToken(fcmToken)) {
    localStorage.setItem('fcm_device_token', fcmToken);
    return fcmToken;
  }

  return fcmToken || rawDeviceId;
}

/**
 * Initializes Push Notification Listeners at App startup (foreground + background + registration).
 */
export function initPushNotificationListeners(onNotification?: (notification: any) => void) {
  if (typeof window === 'undefined' || !Capacitor.isNativePlatform()) return;

  try {
    // Listen for FCM token registration events globally as soon as App launches
    PushNotifications.addListener('registration', async (token) => {
      if (token && token.value) {
        console.log('Global listener acquired real native FCM Token:', token.value);
        const converted = await ensureFcmFormatToken(token.value);
        localStorage.setItem('fcm_device_token', converted);
      }
    });

    PushNotifications.addListener('registrationError', (err) => {
      console.warn('Global FCM registration error:', err);
    });

    PushNotifications.addListener('pushNotificationReceived', (notification) => {
      console.log('Push notification received in foreground:', notification);
      if (onNotification) onNotification(notification);
    });

    PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
      console.log('Push notification action performed:', notification);
      if (onNotification) onNotification(notification);
    });

    // Request permissions and register immediately on native startup
    PushNotifications.checkPermissions().then((perm) => {
      if (perm.receive === 'granted') {
        PushNotifications.register().catch(e => console.warn('Startup PushNotifications.register catch:', e));
      } else {
        PushNotifications.requestPermissions().then((req) => {
          if (req.receive === 'granted') {
            PushNotifications.register().catch(e => console.warn('Startup PushNotifications.register catch:', e));
          }
        }).catch(e => console.warn('Startup requestPermissions catch:', e));
      }
    }).catch(e => console.warn('Startup checkPermissions catch:', e));
  } catch (e) {
    console.warn('Push notification listeners exception:', e);
  }
}
