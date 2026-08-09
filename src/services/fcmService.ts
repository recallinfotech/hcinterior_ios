import { PushNotifications } from '@capacitor/push-notifications';
import { Device } from '@capacitor/device';
import { Capacitor } from '@capacitor/core';

/**
 * Checks whether a string is a real Firebase Cloud Messaging (FCM) / APNs token
 * vs a synthetic hardware device fallback ID.
 */
function isRealFcmToken(token?: string | null): boolean {
  if (!token || typeof token !== 'string') return false;
  const trimmed = token.trim();
  if (trimmed.length < 20) return false;
  if (
    trimmed.startsWith('fcm_android_') ||
    trimmed.startsWith('fcm_ios_') ||
    trimmed.startsWith('fcm_device_') ||
    trimmed.startsWith('fcm_dev_')
  ) {
    return false;
  }
  return true;
}

/**
 * Dynamically fetches real Firebase Cloud Messaging (FCM) token from iOS/Android OS via @capacitor/push-notifications.
 * Prioritizes real native FCM tokens (e.g. f7isD7buRU...:APA91b...) and discards synthetic fallback IDs.
 */
export async function getDynamicFcmToken(): Promise<string> {
  if (typeof window === 'undefined') return '';

  // 1. Check if a valid REAL native FCM token is already cached
  const cachedToken = localStorage.getItem('fcm_device_token');
  if (isRealFcmToken(cachedToken)) {
    return cachedToken!.trim();
  }

  // 2. Request native FCM/APNs Registration
  try {
    const fcmPromise = new Promise<string>((resolve) => {
      let isDone = false;

      PushNotifications.addListener('registration', (token) => {
        if (!isDone) {
          isDone = true;
          if (token && token.value) {
            console.log('Native FCM Push Token acquired:', token.value);
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
      }, 6000);
    });

    const nativeToken = await Promise.race([fcmPromise, timeoutPromise]);
    if (isRealFcmToken(nativeToken)) {
      return nativeToken.trim();
    }
  } catch (e) {
    console.warn('Native FCM Push Token exception:', e);
  }

  // 3. Fallback to hardware ID if native push registration is unavailable
  return await generateUniqueDeviceToken();
}

/**
 * Fallback hardware device token generator with OS platform prefix.
 */
export async function generateUniqueDeviceToken(): Promise<string> {
  const platform = Capacitor.getPlatform(); // 'ios', 'android', 'web'
  const prefix = platform === 'ios' ? 'fcm_ios_' : 'fcm_android_';

  try {
    const info = await Device.getId();
    if (info && info.identifier) {
      const dynamicToken = `${prefix}${info.identifier.replace(/[^a-zA-Z0-9]/g, '')}`;
      localStorage.setItem('fcm_device_token', dynamicToken);
      return dynamicToken;
    }
  } catch (e) {
    console.warn('Device.getId fallback:', e);
  }

  const randomUuid = typeof crypto !== 'undefined' && crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).substring(2);
  const dynamicToken = `${prefix}${randomUuid.replace(/-/g, '')}`;
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
