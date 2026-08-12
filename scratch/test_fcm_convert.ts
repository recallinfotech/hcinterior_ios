import { convertApnsToFcmToken } from '../src/services/firebaseServer.ts';

async function test() {
  const apnsToken = '828E708E4378B4C624F5E0ADDE90AACAE788845F16113383B6D7DF1F96B66FD6';
  console.log('Testing conversion of NEW APNs token:', apnsToken);
  const result = await convertApnsToFcmToken(apnsToken);
  console.log('Converted FCM Token result:', result);
}

test();
