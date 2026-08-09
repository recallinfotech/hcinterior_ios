const https = require('https');

async function testFullBackendFlow() {
  console.log("=== TESTING CRM API BACKEND FIX ===");

  // 1. Login
  const loginBody = JSON.stringify({
    username: 'recallinfotech',
    password: 'Nishant@123',
    device_type: 'android',
    fcm_token: 'fcm_test_device_token_999'
  });

  const loginRes = await new Promise((resolve) => {
    const req = https.request('https://crm.hcinterior.in/login/mobile_login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(loginBody) }
    }, (res) => {
      let b = ''; res.on('data', c => b += c); res.on('end', () => resolve(JSON.parse(b)));
    });
    req.write(loginBody); req.end();
  });

  console.log("Login Result:", loginRes.status, "Message:", loginRes.message);
  console.log("Token:", loginRes.token);
  console.log("User ID:", loginRes.data ? loginRes.data.user_id : 'missing');

  const token = loginRes.token;
  const userId = loginRes.data.user_id;

  // 2. Fetch Client List
  const clientBody = JSON.stringify({ token, user_id: userId, page: 1, limit: 10 });
  const clientRes = await new Promise((resolve) => {
    const req = https.request('https://crm.hcinterior.in/mobileapi/Client/clientList', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Content-Length': Buffer.byteLength(clientBody)
      }
    }, (res) => {
      let b = ''; res.on('data', c => b += c); res.on('end', () => {
        try { resolve(JSON.parse(b)); } catch(e) { resolve(b); }
      });
    });
    req.write(clientBody); req.end();
  });

  console.log("\n=== CLIENT LIST RESPONSE ===");
  console.log("Type:", typeof clientRes);
  if (typeof clientRes === 'object') {
    console.log("Status:", clientRes.status);
    console.log("Message:", clientRes.message);
    console.log("Total Records:", clientRes.total);
    console.log("Data Items:", clientRes.data ? clientRes.data.length : 0);
    if (clientRes.data && clientRes.data.length > 0) {
      console.log("\nSample Client 1:", JSON.stringify(clientRes.data[0], null, 2));
    }
  } else {
    console.log("Raw Response String:", clientRes.slice(0, 300));
  }
}

testFullBackendFlow().catch(console.error);
