async function testClientIid() {
  const apiKey = 'AIzaSyBHJ72CYf_NBHh7rwWvLz7OiYPnhzFBmXc';
  const apnsToken = '828E708E4378B4C624F5E0ADDE90AACAE788845F16113383B6D7DF1F96B66FD6';

  console.log('Testing Direct Client-Side Google IID Conversion...');
  try {
    const res = await fetch('https://iid.googleapis.com/iid/v1:batchImport', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `key=${apiKey}`,
      },
      body: JSON.stringify({
        application: 'com.HCIP.HCOperation',
        sandbox: false,
        apns_tokens: [apnsToken],
      }),
    });
    console.log('Status:', res.status);
    const data = await res.json();
    console.log('Data:', JSON.stringify(data));
  } catch (e) {
    console.error('Fetch error:', e);
  }
}

testClientIid();
