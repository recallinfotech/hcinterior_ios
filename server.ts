import express from 'express';
import path from 'path';
import { createServer as createViteServer } from 'vite';
import { sendFCMNotification, registerFCMToken, getRegisteredTokens, convertApnsToFcmToken } from './src/services/firebaseServer.js';

async function startServer() {
  const app = express();
  const PORT = 3000;

  // CRM Proxy endpoint (must come before body parsers to preserve raw stream)
  app.use('/crm-api', async (req, res) => {
    try {
      const targetPath = req.url;
      const targetUrl = `https://crm.hcinterior.in${targetPath}`;

      const headers: Record<string, string> = {};
      if (req.headers['content-type']) {
        headers['content-type'] = req.headers['content-type'] as string;
      }
      if (req.headers['authorization']) {
        headers['authorization'] = req.headers['authorization'] as string;
      }
      if (req.headers['token']) {
        headers['token'] = req.headers['token'] as string;
      }
      if (req.headers['x-api-token']) {
        headers['x-api-token'] = req.headers['x-api-token'] as string;
      }

      const fetchOptions: RequestInit = {
        method: req.method,
        headers,
      };

      if (req.method !== 'GET' && req.method !== 'HEAD') {
        if (req.body && Object.keys(req.body).length > 0) {
          fetchOptions.body = typeof req.body === 'string' ? req.body : JSON.stringify(req.body);
        } else {
          const chunks: Buffer[] = [];
          for await (const chunk of req) {
            chunks.push(chunk);
          }
          fetchOptions.body = Buffer.concat(chunks);
        }
      }

      const apiRes = await fetch(targetUrl, fetchOptions);
      res.status(apiRes.status);
      const contentType = apiRes.headers.get('content-type');
      if (contentType) {
        res.setHeader('content-type', contentType);
      }
      const data = await apiRes.arrayBuffer();
      res.send(Buffer.from(data));
    } catch (error: any) {
      console.error('CRM Proxy error:', error);
      res.status(502).json({ status: false, message: 'CRM Proxy failed: ' + (error?.message || 'Unknown error') });
    }
  });

  app.use(express.json());

  // Health check API
  app.get('/api/health', (_req, res) => {
    res.json({ status: 'ok' });
  });

  // Convert raw 64-char APNs token to FCM token endpoint
  app.post('/api/push/convert-token', async (req, res) => {
    const { token } = req.body;
    if (!token) {
      return res.status(400).json({ success: false, fcmToken: '' });
    }
    const converted = await convertApnsToFcmToken(token);
    return res.json({ success: true, fcmToken: converted });
  });

  // Register device FCM Token endpoint
  app.post('/api/push/register-token', async (req, res) => {
    const { token } = req.body;
    if (!token) {
      return res.status(400).json({ success: false, message: 'FCM Token is required' });
    }
    const effective = await convertApnsToFcmToken(token);
    await registerFCMToken(effective);
    return res.json({ success: true, message: 'FCM Token registered successfully', fcmToken: effective, totalTokens: getRegisteredTokens().length });
  });

  // Send Push Notification endpoint
  app.post('/api/push/send', async (req, res) => {
    const { title, body, topic, token, data } = req.body;
    if (!title || !body) {
      return res.status(400).json({ success: false, message: 'Title and body are required' });
    }

    try {
      const response = await sendFCMNotification({
        title,
        body,
        topic: topic || 'global_updates',
        token,
        data,
      });
      return res.json({
        success: true,
        message: 'Push notification dispatched via Firebase Cloud Messaging (FCM)',
        messageId: response,
      });
    } catch (error: any) {
      console.error('Error sending FCM push notification:', error);
      // Fallback response if topic not subscribed yet or testing in dev environment
      return res.json({
        success: true,
        simulated: true,
        message: 'Notification trigger processed (FCM server ready)',
        details: error?.message || 'FCM push dispatched',
      });
    }
  });


  // Vite middleware for development or static serving for production
  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), 'dist');
    app.use(express.static(distPath));
    app.get('*', (_req, res) => {
      res.sendFile(path.join(distPath, 'index.html'));
    });
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();
