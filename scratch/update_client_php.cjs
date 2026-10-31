const fs = require('fs');
let code = fs.readFileSync('crm.hcinterior.in/application/controllers/mobileapi/Client.php', 'utf8');

// 1. Add face_image_base64 to get_face_status response
const oldStatusDesc = "'face_descriptor'   => !empty($bio) ? $bio->face_descriptor : null,";
const newStatusDesc = `'face_descriptor'   => !empty($bio) ? $bio->face_descriptor : null,
            'face_image_base64' => (!empty($bio) && !empty($bio->face_image_base64)) ? $bio->face_image_base64 : null,`;

if (code.includes(oldStatusDesc) && !code.includes("'face_image_base64' => (!empty($bio)")) {
  code = code.replace(oldStatusDesc, newStatusDesc);
  console.log('Added face_image_base64 to get_face_status');
}

// 2. Add strict biometric verification in mark_face_attendance
const targetPoint = `        if ((int)$bio->is_approved === 3) {
            echo json_encode([
                'status'          => false,
                'is_enrolled'     => true,
                'is_face_rejected'=> true,
                'approval_status' => 3,
                'rejection_reason'=> $bio->rejection_reason,
                'message'         => 'Your Face ID was REJECTED by Admin' . (!empty($bio->rejection_reason) ? ' (' . $bio->rejection_reason . ')' : '') . '. Please re-register your Face ID.'
            ]);
            exit();
        }`;

const biometricsEnforcement = `        if ((int)$bio->is_approved === 3) {
            echo json_encode([
                'status'          => false,
                'is_enrolled'     => true,
                'is_face_rejected'=> true,
                'approval_status' => 3,
                'rejection_reason'=> $bio->rejection_reason,
                'message'         => 'Your Face ID was REJECTED by Admin' . (!empty($bio->rejection_reason) ? ' (' . $bio->rejection_reason . ')' : '') . '. Please re-register your Face ID.'
            ]);
            exit();
        }

        // STRICT 1-TO-1 BIOMETRIC FACE VERIFICATION (Anti-Proxy Punch Enforcement)
        // Ensure that the live face matches the approved enrolled face of this employee.
        // If similarity is below 75%, reject the punch immediately.
        if ($face_score < 75.0) {
            echo json_encode([
                'status'  => false,
                'message' => 'Biometric Verification Failed: Live face match score (' . round($face_score, 1) . '%) is below the required 75% threshold. Live face does not match the registered Face ID for this employee. Proxy attendance is strictly prohibited!'
            ]);
            exit();
        }

        $live_descriptor_raw = $this->input->post('live_descriptor', false);
        if (!empty($live_descriptor_raw) && !empty($bio->face_descriptor)) {
            $live_vec = json_decode($live_descriptor_raw, true);
            $enrolled_vec = json_decode($bio->face_descriptor, true);

            if (is_array($live_vec) && is_array($enrolled_vec) && count($live_vec) >= 32 && count($enrolled_vec) >= 32) {
                $len = min(count($live_vec), count($enrolled_vec));
                $dot = 0.0; $mag1 = 0.0; $mag2 = 0.0;
                for ($i = 0; $i < $len; $i++) {
                    $a = floatval($live_vec[$i]);
                    $b = floatval($enrolled_vec[$i]);
                    $dot += $a * $b;
                    $mag1 += $a * $a;
                    $mag2 += $b * $b;
                }
                $server_cosine = ($mag1 > 0 && $mag2 > 0) ? ($dot / (sqrt($mag1) * sqrt($mag2))) : 0;
                $server_score = round(max(0, min(100, (($server_cosine - 0.35) / (0.95 - 0.35)) * 100)), 1);

                if ($server_score < 75.0) {
                    echo json_encode([
                        'status'  => false,
                        'message' => 'Biometric Security Violation: Server verified that the live face in camera (' . $server_score . '% match) DOES NOT MATCH the registered Face ID of this employee! You cannot mark attendance using someone else\'s face.'
                    ]);
                    exit();
                }
            }
        }`;

if (code.includes(targetPoint) && !code.includes('STRICT 1-TO-1 BIOMETRIC FACE VERIFICATION')) {
  code = code.replace(targetPoint, biometricsEnforcement);
  console.log('Added strict server-side biometric verification in Client.php');
}

fs.writeFileSync('crm.hcinterior.in/application/controllers/mobileapi/Client.php', code, 'utf8');
console.log('Client.php updated successfully!');

