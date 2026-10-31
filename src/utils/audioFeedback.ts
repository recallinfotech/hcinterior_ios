/**
 * Audio Feedback and Haptic Vibration Utility for Attendance Punch In / Punch Out.
 * Uses the Web Audio API to synthesize high-quality, crisp confirmation tones.
 * Works offline, requires no external sound files, and works on mobile/desktop browsers.
 */

let sharedAudioContext: AudioContext | null = null;

function getAudioContext(): AudioContext | null {
  if (typeof window === 'undefined') return null;
  const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
  if (!AudioContextClass) return null;

  try {
    if (!sharedAudioContext) {
      sharedAudioContext = new AudioContextClass();
    }
    if (sharedAudioContext.state === 'suspended') {
      sharedAudioContext.resume().catch(() => {});
    }
    return sharedAudioContext;
  } catch (err) {
    console.warn('Could not initialize AudioContext:', err);
    return null;
  }
}

/**
 * Play a high-fidelity biometric machine confirmation beep.
 * @param type 'in' for Punch In (crisp ascending chime / double beep), 'out' for Punch Out (distinct descending tone)
 */
export function playPunchSound(type: 'in' | 'out'): void {
  // Trigger mobile haptic vibration if supported
  if (typeof navigator !== 'undefined' && 'vibrate' in navigator && typeof navigator.vibrate === 'function') {
    try {
      if (type === 'in') {
        navigator.vibrate([120, 60, 180]); // Dual pulse
      } else {
        navigator.vibrate([180, 80, 120]); // Long then short
      }
    } catch {
      // Ignore vibration error on unsupported hardware
    }
  }

  const ctx = getAudioContext();
  if (!ctx) return;

  const now = ctx.currentTime;

  if (type === 'in') {
    // === PUNCH IN SOUND ===
    // Crisp biometric punch machine double-beep / success chime
    // Tone 1: 880 Hz (A5) -> Tone 2: 1320 Hz (E6)
    
    // First Beep
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(880, now);
    
    gain1.gain.setValueAtTime(0, now);
    gain1.gain.linearRampToValueAtTime(0.4, now + 0.02);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.12);

    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(now);
    osc1.stop(now + 0.12);

    // Second Beep (Higher & slightly longer - confirmation)
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'triangle'; // Richer harmonic
    osc2.frequency.setValueAtTime(1318.5, now + 0.13); // E6

    gain2.gain.setValueAtTime(0, now + 0.13);
    gain2.gain.linearRampToValueAtTime(0.45, now + 0.15);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.38);

    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(now + 0.13);
    osc2.stop(now + 0.38);

  } else {
    // === PUNCH OUT SOUND ===
    // Clear descending double-beep denoting shift completion / log out
    // Tone 1: 1046 Hz (C6) -> Tone 2: 659.25 Hz (E5)
    
    // First Beep
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(1046.5, now);

    gain1.gain.setValueAtTime(0, now);
    gain1.gain.linearRampToValueAtTime(0.4, now + 0.02);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.13);

    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(now);
    osc1.stop(now + 0.13);

    // Second Beep (Lower tone)
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(659.25, now + 0.14);

    gain2.gain.setValueAtTime(0, now + 0.14);
    gain2.gain.linearRampToValueAtTime(0.45, now + 0.16);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.42);

    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(now + 0.14);
    osc2.stop(now + 0.42);
  }
}

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
