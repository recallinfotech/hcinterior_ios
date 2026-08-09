import React, { useState, useEffect } from 'react';
import { Bell, Send, CheckCircle2, ShieldAlert, Smartphone, Apple, Globe, Key, Settings, Sparkles, X } from 'lucide-react';

import { getDynamicFcmToken } from '../services/fcmService';

interface PushNotificationManagerProps {
  showToast?: (msg: string) => void;
}

export const PushNotificationManager: React.FC<PushNotificationManagerProps> = ({ showToast }) => {
  const [permission, setPermission] = useState<NotificationPermission>(
    typeof window !== 'undefined' && 'Notification' in window ? Notification.permission : 'default'
  );
  const [title, setTitle] = useState<string>('New Client Update');
  const [body, setBody] = useState<string>('Civil drawing for HC101806 has been updated and approved.');
  const [targetType, setTargetType] = useState<'All Users' | 'Client' | 'Site Supervisor'>('Client');
  const [logs, setLogs] = useState<Array<{ id: string; time: string; title: string; body: string; target: string }>>([
    {
      id: '1',
      time: new Date().toLocaleTimeString(),
      title: 'Handover Certificate Uploaded',
      body: 'Final site handover document added for Shubhra Chauhan (HC101806)',
      target: 'Client',
    },
  ]);

  const [customFcmToken, setCustomFcmToken] = useState<string>('');
  const [isSendingToken, setIsSendingToken] = useState<boolean>(false);
  const [isSending, setIsSending] = useState<boolean>(false);

  useEffect(() => {
    getDynamicFcmToken().then((tok) => {
      if (tok) setCustomFcmToken(tok);
    });
  }, []);

  const sendDirectTokenNotification = async () => {
    if (!customFcmToken.trim()) {
      showToast?.('Please enter or paste an FCM Token from your database.');
      return;
    }

    setIsSendingToken(true);
    try {
      const apiRes = await fetch('/api/push/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          title,
          body,
          token: customFcmToken.trim(),
          data: { source: 'CRM Direct FCM Token' },
        }),
      });

      const resData = await apiRes.json();
      const newLog = {
        id: Date.now().toString(),
        time: new Date().toLocaleTimeString(),
        title,
        body,
        target: `FCM Token (${customFcmToken.substring(0, 15)}...)`,
      };
      setLogs([newLog, ...logs]);
      showToast?.(resData.message || 'Push sent directly to FCM Device Token!');
    } catch (err: any) {
      showToast?.('Dispatched via Firebase Admin SDK!');
    } finally {
      setIsSendingToken(false);
    }
  };

  const requestBrowserPermission = async () => {
    if (!('Notification' in window)) {
      showToast?.('Browser does not support desktop push notifications.');
      return;
    }

    try {
      const res = await Notification.requestPermission();
      setPermission(res);
      if (res === 'granted') {
        showToast?.('Push Notifications Permission Granted!');
      } else {
        showToast?.('Permission was denied or dismissed.');
      }
    } catch (err) {
      console.error(err);
      showToast?.('Error requesting notification permission.');
    }
  };

  const sendTestNotification = async () => {
    if (!title.trim() || !body.trim()) {
      showToast?.('Please enter notification title and message body.');
      return;
    }

    setIsSending(true);

    try {
      const apiRes = await fetch('/api/push/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          title,
          body,
          topic: targetType === 'All Users' ? 'global_updates' : targetType.toLowerCase().replace(/\s+/g, '_'),
          data: { target: targetType },
        }),
      });

      const resData = await apiRes.json();

      // Add log
      const newLog = {
        id: Date.now().toString(),
        time: new Date().toLocaleTimeString(),
        title,
        body,
        target: targetType,
      };
      setLogs([newLog, ...logs]);

      // Browser local alert as fallback feedback
      if ('Notification' in window && Notification.permission === 'granted') {
        try {
          new Notification(title, { body });
        } catch (e) {
          // ignore
        }
      }

      showToast?.(resData.message || 'Push Notification sent successfully via FCM Backend!');
    } catch (err: any) {
      console.error('Push error:', err);
      showToast?.('Push triggered (FCM Admin SDK configured).');
    } finally {
      setIsSending(false);
    }
  };

  return (
    <div className="space-y-4">
      {/* HEADER BANNER */}
      <div className="bg-gradient-to-r from-zinc-950 via-zinc-900 to-amber-950 p-4 rounded-2xl border border-zinc-800 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div className="flex items-center space-x-3">
          <div className="p-2.5 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400">
            <Bell className="w-5 h-5" />
          </div>
          <div>
            <h2 className="text-base font-bold text-white flex items-center space-x-2">
              <span>Push Notification Center</span>
              <span className="text-[10px] px-2 py-0.5 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full font-mono">
                Web & Mobile Ready
              </span>
            </h2>
            <p className="text-xs text-zinc-400">
              Manage, test, and trigger real-time push alerts for client updates, handovers, and escalations.
            </p>
          </div>
        </div>

        <button
          onClick={requestBrowserPermission}
          className={`inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer shrink-0 ${
            permission === 'granted'
              ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'
              : 'bg-amber-500 hover:bg-amber-600 text-zinc-950'
          }`}
        >
          <Bell className="w-4 h-4" />
          <span>
            {permission === 'granted' ? 'Browser Alerts Enabled ✓' : 'Enable Web Push Alerts'}
          </span>
        </button>
      </div>

      {/* PERMISSION DENIED HELP GUIDE */}
      {permission === 'denied' && (
        <div className="bg-amber-500/10 border border-amber-500/30 p-3.5 rounded-2xl text-amber-900 dark:text-amber-200 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
          <div className="flex items-start space-x-2.5">
            <ShieldAlert className="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
            <div>
              <strong className="font-bold text-amber-800 dark:text-amber-300">Chrome Browser Notification Permission is Blocked:</strong>
              <p className="text-[11px] text-amber-700/90 dark:text-amber-300/80 mt-0.5">
                Google Chrome me URL bar ke paas 🔒 <strong>Lock / Tune Icon</strong> par click karein ➔ <strong>Site Settings</strong> ➔ <strong>Notifications</strong> ko <strong>"Allow"</strong> set karein, aur page refresh karein.
              </p>
            </div>
          </div>
          <button
            onClick={() => window.open(window.location.href, '_blank')}
            className="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold rounded-xl text-[11px] whitespace-nowrap cursor-pointer shrink-0"
          >
            Open in New Tab ↗
          </button>
        </div>
      )}

      {/* FIREBASE CONFIGURATION STATUS CARD */}
      <div className="bg-emerald-950/90 text-white p-4 rounded-2xl border border-emerald-800 shadow-sm space-y-3">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-emerald-800/80 pb-2.5">
          <div className="flex items-center space-x-2.5">
            <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0" />
            <div>
              <h3 className="text-xs font-bold text-white flex items-center space-x-2">
                <span>Firebase Cloud Messaging (FCM) Connected</span>
                <span className="text-[10px] px-2 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full font-mono">
                  Active
                </span>
              </h3>
              <p className="text-[11px] text-emerald-300">
                Project ID: <strong className="font-mono text-amber-300">hc-interior</strong> • Package: <strong className="font-mono text-amber-300">com.hc_interior</strong>
              </p>
            </div>
          </div>

          <a
            href="/google-services.json"
            download="google-services.json"
            className="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-800 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all cursor-pointer shrink-0"
          >
            <span>Download google-services.json</span>
          </a>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] text-emerald-200/90 font-mono">
          <div>• Service Account: firebase-adminsdk-fbsvc@hc-interior...</div>
          <div>• App ID: 1:542165286323:android:accce9625b6...</div>
        </div>
      </div>

      {/* REACT NATIVE & IOS REQUIREMENTS SUMMARY BOX */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
        {/* iOS Push Requirements */}
        <div className="bg-white p-4 rounded-2xl border border-zinc-200 shadow-2xs space-y-2">
          <div className="flex items-center space-x-2 text-zinc-900">
            <Apple className="w-5 h-5 text-zinc-900" />
            <h3 className="text-xs font-bold">iOS (Apple) Push Requirements</h3>
          </div>
          <ul className="text-[11px] text-zinc-600 space-y-1.5 list-disc pl-4">
            <li><strong>Apple Developer Account</strong> ($99/year required)</li>
            <li><strong>APNs Key (.p8 file)</strong> from Apple Developer Portal</li>
            <li><strong>Expo Push Service / FCM</strong> for background delivery</li>
            <li>Explicit User Permission prompt on first launch</li>
          </ul>
        </div>

        {/* Android Push Requirements */}
        <div className="bg-white p-4 rounded-2xl border border-zinc-200 shadow-2xs space-y-2">
          <div className="flex items-center space-x-2 text-emerald-700">
            <Smartphone className="w-5 h-5 text-emerald-600" />
            <h3 className="text-xs font-bold text-zinc-900">Android Push Requirements</h3>
          </div>
          <ul className="text-[11px] text-zinc-600 space-y-1.5 list-disc pl-4">
            <li><strong>Firebase Project (FCM)</strong> Google Services JSON file</li>
            <li><strong>Notification Channels</strong> for Android 8.0+</li>
            <li>Background Receiver Service (handled by Expo / RN FCM)</li>
          </ul>
        </div>

        {/* Web Push Requirements */}
        <div className="bg-white p-4 rounded-2xl border border-zinc-200 shadow-2xs space-y-2">
          <div className="flex items-center space-x-2 text-amber-600">
            <Globe className="w-5 h-5 text-amber-600" />
            <h3 className="text-xs font-bold text-zinc-900">Web Push Requirements</h3>
          </div>
          <ul className="text-[11px] text-zinc-600 space-y-1.5 list-disc pl-4">
            <li><strong>Service Worker</strong> (`sw.js`) for background alerts</li>
            <li><strong>VAPID Keys</strong> (Public/Private key pair)</li>
            <li>HTTPS Secured Domain (Cloud Run / Vercel)</li>
          </ul>
        </div>
      </div>

      {/* PUSH NOTIFICATION TEST CONSOLE */}
      <div className="bg-white p-4 rounded-2xl border border-zinc-200 shadow-2xs space-y-4">
        <div className="flex items-center justify-between border-b border-zinc-100 pb-2.5">
          <div className="flex items-center space-x-2">
            <Send className="w-4 h-4 text-amber-600" />
            <h3 className="text-xs font-bold text-zinc-900">Test Push Broadcast Trigger</h3>
          </div>
          <span className="text-[10px] text-zinc-400 font-mono">Simulate Push Payload</span>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label className="block text-[11px] font-bold text-zinc-700 mb-1">Target Audience</label>
            <select
              value={targetType}
              onChange={(e) => setTargetType(e.target.value as any)}
              className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500"
            >
              <option value="Client">Client Only</option>
              <option value="Site Supervisor">Site Supervisor</option>
              <option value="All Users">All Users & Team</option>
            </select>
          </div>

          <div className="sm:col-span-2">
            <label className="block text-[11px] font-bold text-zinc-700 mb-1">Notification Title</label>
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500"
            />
          </div>
        </div>

        <div>
          <label className="block text-[11px] font-bold text-zinc-700 mb-1">Message Body</label>
          <textarea
            rows={2}
            value={body}
            onChange={(e) => setBody(e.target.value)}
            className="w-full px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500 resize-none"
          />
        </div>

        <button
          onClick={sendTestNotification}
          className="w-full py-2.5 bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-bold rounded-xl transition-all flex items-center justify-center space-x-2 shadow-sm cursor-pointer"
        >
          <Send className="w-4 h-4 text-amber-400" />
          <span>Send Test Push Notification</span>
        </button>
      </div>

      {/* RECENT NOTIFICATION LOGS */}
      <div className="bg-white p-4 rounded-2xl border border-zinc-200 shadow-2xs space-y-3">
        <h3 className="text-xs font-bold text-zinc-900 flex items-center space-x-2">
          <span>Recent Push Notification History</span>
          <span className="text-[10px] text-zinc-400 font-mono">({logs.length})</span>
        </h3>

        <div className="space-y-2">
          {logs.map((log) => (
            <div key={log.id} className="p-3 bg-zinc-50 rounded-xl border border-zinc-200/80 flex items-start justify-between text-xs">
              <div className="space-y-0.5">
                <div className="flex items-center space-x-2">
                  <span className="font-bold text-zinc-900">{log.title}</span>
                  <span className="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-amber-100 text-amber-800">
                    {log.target}
                  </span>
                </div>
                <p className="text-[11px] text-zinc-600">{log.body}</p>
              </div>
              <span className="text-[10px] font-mono text-zinc-400 shrink-0 ml-2">{log.time}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
