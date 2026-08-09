const https = require('https');

async function testIosLogin() {
  console.log("=== TESTING LOGIN AS IOS DEVICE ===");
  
  // Simulated iOS FCM Token (APNs / FCM token generated on iPhone)
  const simulatedIosFcmToken = "fcm_ios_d9a8f27e1b5c432098471abc0983ef124567890abcdef1234567890abcdef12";
  
  const loginPayload = {
    username: 'recallinfotech',
    password: 'Nishant@123',
    device_type: 'ios',
    fcm_token: simulatedIosFcmToken
  };

  console.log("Payload sent to CRM API:", JSON.stringify(loginPayload, null, 2));

  const dataStr = JSON.stringify(loginPayload);
  const loginRes = await new Promise((resolve, reject) => {
    const req = https.request('https://crm.hcinterior.in/login/mobile_login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
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

  console.log("\n=== CRM API LOGIN RESPONSE ===");
  console.log(JSON.stringify(loginRes, null, 2));
}

testIosLogin().catch(console.error);
