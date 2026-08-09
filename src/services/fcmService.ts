import { PushNotifications } from '@capacitor/push-notifications';
import { Device } from '@capacitor/device';

/**
 * Dynamically fetches real Firebase Cloud Messaging (FCM) token from Android OS via @capacitor/push-notifications.
 * Falls back to native hardware device identifier if FCM registration times out or permission is denied.
 */
export async function getDynamicFcmToken(): Promise<string> {
  if (typeof window === 'undefined') return '';

  const cachedToken = localStorage.getItem('fcm_device_token');

  try {
    const fcmPromise = new Promise<string>((resolve) => {
      let isDone = false;

      PushNotifications.addListener('registration', (token) => {
        if (!isDone) {
          isDone = true;
          if (token && token.value) {
            localStorage.setItem('fcm_device_token', token.value);
            resolve(token.value);
          }
        }
      });

      PushNotifications.addListener('registrationError', (err) => {
        console.warn('FCM registration error:', err);
      });

      PushNotifications.checkPermissions().then((perm) => {
        if (perm.receive === 'granted') {
          PushNotifications.register().catch(e => console.warn('PushNotifications.register catch:', e));
        } else {
          PushNotifications.requestPermissions().then((req) => {
            if (req.receive === 'granted') {
              PushNotifications.register().catch(e => console.warn('PushNotifications.register catch:', e));
            }
          }).catch(e => console.warn('requestPermissions catch:', e));
        }
      }).catch(e => console.warn('checkPermissions catch:', e));
    });

    const timeoutPromise = new Promise<string>((resolve) => {
      setTimeout(() => {
        resolve('');
      }, 2500);
    });

    const nativeToken = await Promise.race([fcmPromise, timeoutPromise]);
    if (nativeToken && nativeToken.trim().length > 10) {
      return nativeToken;
    }
  } catch (e) {
    console.warn('Native FCM Push Token exception:', e);
  }

  if (cachedToken && cachedToken.trim().length > 10) {
    return cachedToken;
  }

  return await generateUniqueDeviceToken();
}

/**
 * Fallback native hardware device token generator.
 */
export async function generateUniqueDeviceToken(): Promise<string> {
  try {
    const info = await Device.getId();
    if (info && info.identifier) {
      const dynamicToken = `fcm_android_${info.identifier.replace(/[^a-zA-Z0-9]/g, '')}`;
      localStorage.setItem('fcm_device_token', dynamicToken);
      return dynamicToken;
    }
  } catch (e) {
    console.warn('Device.getId fallback:', e);
  }

  const randomUuid = typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).substring(2);
  const uaHash = typeof btoa !== 'undefined' ? btoa(navigator.userAgent || '').replace(/[^a-zA-Z0-9]/g, '').slice(0, 15) : 'dev';
  const dynamicToken = `fcm_${uaHash}_${randomUuid.replace(/-/g, '')}`;
  localStorage.setItem('fcm_device_token', dynamicToken);
  return dynamicToken;
}

/**
 * Initializes Push Notification Listeners (foreground + action performed).
 */
export function initPushNotificationListeners(onNotification?: (notification: any) => void) {
  try {
    PushNotifications.addListener('pushNotificationReceived', (notification) => {
      console.log('Push notification received in foreground:', notification);
      if (onNotification) onNotification(notification);
    });

    PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
      console.log('Push notification action performed:', notification);
      if (onNotification) onNotification(notification);
    });
  } catch (e) {
    console.warn('Push notification listeners exception:', e);
  }
}
