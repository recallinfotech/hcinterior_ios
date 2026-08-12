const http = require('http');

async function testLocalLogin() {
  console.log("=== TESTING LOCAL PROXY CRM LOGIN ===");
  
  const payload = {
    username: 'recallinfotech',
    password: 'Nishant@123',
    device_type: 'android',
    fcm_token: 'test_token'
  };

  const dataStr = JSON.stringify(payload);

  const res = await new Promise((resolve, reject) => {
    const req = http.request('http://localhost:3000/crm-api/login/mobile_login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(dataStr)
      }
    }, (res) => {
      let b = '';
      res.on('data', c => b += c);
      res.on('end', () => {
        try { resolve(JSON.parse(b)); } catch(e) { resolve(b); }
      });
    });
    req.on('error', reject);
    req.write(dataStr);
    req.end();
  });

  console.log("LOGIN VIA PROXY RESPONSE:", JSON.stringify(res, null, 2));
}

testLocalLogin().catch(console.error);
