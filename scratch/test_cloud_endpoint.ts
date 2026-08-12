async function testCloudEndpoint() {
  const url = 'https://ais-pre-p4l3km6ranu5hkwm757tro-734024418090.asia-southeast1.run.app/crm-api/api/push/convert-token';
  const token = '828E708E4378B4C624F5E0ADDE90AACAE788845F16113383B6D7DF1F96B66FD6';
  
  console.log('Testing Correct Cloud Run Endpoint:', url);
  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token }),
    });
    console.log('Status:', res.status);
    const data = await res.json();
    console.log('Data:', JSON.stringify(data));
  } catch (e) {
    console.error('Fetch error:', e);
  }
}

testCloudEndpoint();
