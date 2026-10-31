// Test vector math and normalization
function extractSimulatedDescriptor(patternSeed) {
  const v = [];
  // 64 grid cells
  for (let y = 0; y < 8; y++) {
    for (let x = 0; x < 8; x++) {
      const val = Math.sin(x * patternSeed + y * 0.7) * 0.5 + Math.cos(x * 0.3 + y * patternSeed) * 0.5;
      v.push(val);
    }
  }
  // 32 edge gradients
  for (let i = 0; i < 32; i++) {
    v.push(Math.sin(i * patternSeed * 1.3) * 0.4);
  }
  // 16 color diffs
  for (let i = 0; i < 16; i++) {
    v.push(Math.cos(i * patternSeed * 0.9) * 0.3);
  }
  // 16 anthropometric ratios
  for (let i = 0; i < 16; i++) {
    v.push(0.5 + Math.sin(i + patternSeed) * 0.2);
  }

  // L2 normalize
  let sumSq = 0;
  for (let i = 0; i < v.length; i++) sumSq += v[i] * v[i];
  const mag = Math.sqrt(sumSq) || 1;
  return v.map(x => Number((x / mag).toFixed(4)));
}

function compareVectors(v1, v2) {
  if (!v1 || !v2 || v1.length !== v2.length) return { match: false, similarity: 0 };
  let dot = 0;
  for (let i = 0; i < v1.length; i++) dot += v1[i] * v2[i];
  
  // Calibrated similarity mapping: 0.35 -> 0%, 0.80 -> 75%, 0.95 -> 100%
  const sim = Math.round(Math.max(0, Math.min(100, ((dot - 0.35) / (0.95 - 0.35)) * 100)));
  return { match: sim >= 75, similarity: sim, rawCosine: dot };
}

// Person 1 (e.g. Abhishek Bhati)
const p1_enrolled = extractSimulatedDescriptor(1.42);
// Person 1 live scan (same person with natural variation)
const p1_live = extractSimulatedDescriptor(1.42 + 0.03);

// Person 2 (e.g. colleague / friend trying to punch for Abhishek)
const p2_live = extractSimulatedDescriptor(2.89);

console.log("Same Person (Abhishek vs Abhishek Live):", compareVectors(p1_enrolled, p1_live));
console.log("Different Person (Abhishek vs Colleague Live):", compareVectors(p1_enrolled, p2_live));

