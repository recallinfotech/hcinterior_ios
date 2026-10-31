const fs = require('fs');
let code = fs.readFileSync('src/utils/audioFeedback.ts', 'utf8');

const extraFunc = `
/**
 * Play a biometric security error buzz & rejection tone when face verification fails (proxy punch attempt).
 */
export function playPunchErrorSound(): void {
  if (typeof navigator !== 'undefined' && 'vibrate' in navigator && typeof navigator.vibrate === 'function') {
    try {
      navigator.vibrate([200, 100, 200, 100, 300]);
    } catch {}
  }

  const ctx = getAudioContext();
  if (!ctx) return;
  const now = ctx.currentTime;

  const osc = ctx.createOscillator();
  const gain = ctx.createGain();
  osc.type = 'sawtooth';
  osc.frequency.setValueAtTime(220, now);
  osc.frequency.linearRampToValueAtTime(140, now + 0.35);

  gain.gain.setValueAtTime(0, now);
  gain.gain.linearRampToValueAtTime(0.5, now + 0.04);
  gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

  osc.connect(gain);
  gain.connect(ctx.destination);
  osc.start(now);
  osc.stop(now + 0.45);
}
`;

if (!code.includes('playPunchErrorSound')) {
  code = code.trimEnd() + '\n' + extraFunc;
  fs.writeFileSync('src/utils/audioFeedback.ts', code, 'utf8');
  console.log('Added playPunchErrorSound to audioFeedback.ts');
} else {
  console.log('playPunchErrorSound already present');
}

