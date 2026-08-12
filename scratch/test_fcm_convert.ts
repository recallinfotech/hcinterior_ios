import { convertApnsToFcmToken } from '../src/services/firebaseServer.ts';

async function test() {
  const apnsToken = '561B297F147CA60C06E01B6CF44DD15C52C0D8E83087FFDC649DAD6FF16275C5';
  console.log('Testing conversion of APNs token:', apnsToken);
  const result = await convertApnsToFcmToken(apnsToken);
  console.log('Converted FCM Token result:', result);
}

test();
