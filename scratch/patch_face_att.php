<?php
$clientFile = __DIR__ . '/../crm.hcinterior.in/application/controllers/mobileapi/Client.php';
$content = file_get_contents($clientFile);

$old = <<<'CODE'
        $user_row = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        $user_office_id = !empty($user_row) ? $user_row->office_location_id : null;

        $candidate_offices = [];
CODE;

$new = <<<'CODE'
        $user_row = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        $acc_row = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();
        $user_office_id = null;
        if (!empty($user_row) && !empty($user_row->office_location_id)) {
            $user_office_id = (int)$user_row->office_location_id;
        } elseif (!empty($acc_row) && !empty($acc_row->office_location_id)) {
            $user_office_id = (int)$acc_row->office_location_id;
        } elseif (!empty($user_row) && !empty($user_row->office_id)) {
            $user_office_id = (int)$user_row->office_id;
        } elseif (!empty($acc_row) && !empty($acc_row->office_id)) {
            $user_office_id = (int)$acc_row->office_id;
        }

        $candidate_offices = [];
CODE;

$contentNorm = str_replace("\r\n", "\n", $content);
$oldNorm = str_replace("\r\n", "\n", $old);
$newNorm = str_replace("\r\n", "\n", $new);

if (strpos($contentNorm, $oldNorm) !== false) {
    $contentNorm = str_replace($oldNorm, $newNorm, $contentNorm);
    file_put_contents($clientFile, str_replace("\n", "\r\n", $contentNorm));
    echo "mark_face_attendance patched successfully.\n";
} else {
    echo "Could not find target in mark_face_attendance.\n";
}
