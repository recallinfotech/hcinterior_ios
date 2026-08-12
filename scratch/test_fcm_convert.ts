import { convertApnsToFcmToken } from '../src/services/firebaseServer.ts';

async function test() {
  const apnsToken = 'fcm_ios_7B67E56811514B5B88DEB016E87D7569';
  console.log('Testing conversion of prefixed token:', apnsToken);
  const result = await convertApnsToFcmToken(apnsToken);
  console.log('Converted FCM Token result:', result);
}

test();
