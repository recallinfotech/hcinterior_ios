// Simulate Face Detection logic test
function testFaceDetection(scenario) {
  // scenario 1: Real face centered with pink shirt at bottom
  // scenario 2: Cream wall with hair at bottom (Screenshot 3)
  
  // Target 120 x 150 (portrait 4:5 aspect ratio)
  const W = 120;
  const H = 150;
  
  let eyeValleyDetected = false;
  let mouthValleyDetected = false;
  let skinInOvalRatio = 0;
  
  if (scenario === 'real_face_with_shirt') {
    // Real face has eye sockets (darker than cheeks)
    const eyeZoneLum = 95;
    const cheekZoneLum = 145; // delta = 50
    const mouthZoneLum = 110;
    const chinZoneLum = 140; // delta = 30
    
    const eyeContrast = cheekZoneLum - eyeZoneLum; // 50 > 8
    const mouthContrast = chinZoneLum - mouthZoneLum; // 30 > 6
    eyeValleyDetected = eyeContrast >= 8;
    mouthValleyDetected = mouthContrast >= 6;
    skinInOvalRatio = 0.55;
    
    // Face center derived from eye position and nose
    const faceCenterY = 0.48; // centered!
    const isCentered = Math.abs(faceCenterY - 0.48) <= 0.18;
    
    return {
      hasFace: eyeValleyDetected && mouthValleyDetected && skinInOvalRatio >= 0.20 && isCentered,
      feedback: isCentered ? "✅ Human Face Detected" : "Please center"
    };
  } else if (scenario === 'cream_wall_hair_at_bottom') {
    // Cream wall has uniform color, no eye sockets or mouth inside the oval!
    const eyeZoneLum = 180;
    const cheekZoneLum = 182; // delta = 2 (flat wall!)
    const mouthZoneLum = 181;
    const chinZoneLum = 182; // delta = 1 (flat wall!)
    
    const eyeContrast = cheekZoneLum - eyeZoneLum; // 2 < 8
    const mouthContrast = chinZoneLum - mouthZoneLum; // 1 < 6
    eyeValleyDetected = eyeContrast >= 8; // FALSE!
    mouthValleyDetected = mouthContrast >= 6; // FALSE!
    
    return {
      hasFace: eyeValleyDetected && mouthValleyDetected,
      feedback: "⚠️ No Human Face Detected — Look into camera"
    };
  }
}

console.log("Scenario 1 (Abhishek Face + Shirt):", testFaceDetection('real_face_with_shirt'));
console.log("Scenario 2 (Cream Wall + Hair at Bottom):", testFaceDetection('cream_wall_hair_at_bottom'));

