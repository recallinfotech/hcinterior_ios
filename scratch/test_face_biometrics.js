// Test biometric vector extraction & comparison math
function computeCosineSimilarity(v1, v2) {
  if (!v1 || !v2 || v1.length !== v2.length) return 0;
  let dot = 0;
  let mag1 = 0;
  let mag2 = 0;
  for (let i = 0; i < v1.length; i++) {
    dot += v1[i] * v2[i];
    mag1 += v1[i] * v1[i];
    mag2 += v2[i] * v2[i];
  }
  if (mag1 === 0 || mag2 === 0) return 0;
  return dot / (Math.sqrt(mag1) * Math.sqrt(mag2));
}

function l2Normalize(v) {
  let sumSq = 0;
  for (let i = 0; i < v.length; i++) sumSq += v[i] * v[i];
  const mag = Math.sqrt(sumSq) || 1;
  return v.map(x => x / mag);
}

// Person A template
const personA = l2Normalize(Array.from({ length: 128 }, (_, i) => Math.sin(i * 0.3) + Math.cos(i * 0.7) * 0.5));
// Person A with slight lighting / tilt shift (same person)
const personA_shifted = l2Normalize(personA.map((x, i) => x + (Math.sin(i * 1.5) * 0.08)));
// Person B (different human face structure)
const personB = l2Normalize(Array.from({ length: 128 }, (_, i) => Math.sin(i * 0.9) - Math.cos(i * 0.2) * 0.7));

const sameSim = computeCosineSimilarity(personA, personA_shifted);
const diffSim = computeCosineSimilarity(personA, personB);

console.log("Same Person Similarity:", sameSim, "Percentage:", Math.round(sameSim * 100) + "%");
console.log("Different Person Similarity:", diffSim, "Percentage:", Math.round(diffSim * 100) + "%");

