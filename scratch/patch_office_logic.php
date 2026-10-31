<?php
// Patch Client.php
$clientFile = __DIR__ . '/../crm.hcinterior.in/application/controllers/mobileapi/Client.php';
$content = file_get_contents($clientFile);

$oldCode = <<<'CODE'
        // Multi-Office Geofence configuration for this user
        $user_row = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        $user_office_id = !empty($user_row) ? $user_row->office_location_id : null;

        $target_office = null;
        if (!empty($user_office_id)) {
            $target_office = $this->db->where('id', $user_office_id)->where('status', 1)->get('tbl_office_locations')->row();
        }
        if (empty($target_office)) {
            $target_office = $this->db->where('is_default', 1)->where('status', 1)->get('tbl_office_locations')->row();
        }
        if (empty($target_office)) {
            $target_office = $this->db->where('status', 1)->order_by('id', 'ASC')->get('tbl_office_locations')->row();
        }
CODE;

$newCode = <<<'CODE'
        // Multi-Office Geofence configuration for this user
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

        // Allow passing office_id or office_location_id directly in query
        $req_office_id = $this->input->get_post('office_location_id', true) ?: $this->input->get_post('office_id', true);
        if (!empty($req_office_id)) {
            $user_office_id = (int)$req_office_id;
        }

        $target_office = null;
        if (!empty($user_office_id)) {
            $target_office = $this->db->where('id', $user_office_id)->where('status', 1)->get('tbl_office_locations')->row();
        }

        // If coordinates provided in request, check if inside any active office geofence
        $req_lat = $this->input->get_post('lat', true) ?: $this->input->get_post('latitude', true);
        $req_long = $this->input->get_post('long', true) ?: $this->input->get_post('longitude', true);
        if (empty($target_office) && !empty($req_lat) && !empty($req_long) && $this->db->table_exists('tbl_office_locations')) {
            $all_cand_offices = $this->db->where('status', 1)->get('tbl_office_locations')->result();
            foreach ($all_cand_offices as $co) {
                $d = 6371000 * 2 * asin(sqrt(
                    pow(sin(deg2rad(floatval($co->latitude) - floatval($req_lat)) / 2), 2) +
                    cos(deg2rad(floatval($req_lat))) * cos(deg2rad(floatval($co->latitude))) *
                    pow(sin(deg2rad(floatval($co->longitude) - floatval($req_long)) / 2), 2)
                ));
                if ($d <= floatval($co->allowed_radius ?: 250)) {
                    $target_office = $co;
                    break;
                }
            }
        }

        if (empty($target_office)) {
            $target_office = $this->db->where('is_default', 1)->where('status', 1)->get('tbl_office_locations')->row();
        }
        if (empty($target_office)) {
            $target_office = $this->db->where('status', 1)->order_by('id', 'ASC')->get('tbl_office_locations')->row();
        }
CODE;

// Normalize line endings
$contentNorm = str_replace("\r\n", "\n", $content);
$oldNorm = str_replace("\r\n", "\n", $oldCode);
$newNorm = str_replace("\r\n", "\n", $newCode);

if (strpos($contentNorm, $oldNorm) !== false) {
    $contentNorm = str_replace($oldNorm, $newNorm, $contentNorm);
    file_put_contents($clientFile, str_replace("\n", "\r\n", $contentNorm));
    echo "Client.php get_face_status patched successfully.\n";
} else {
    echo "Could not find target block in Client.php\n";
}

// Also patch mark_attendance in Client.php
$oldMark = <<<'CODE'
        // Multi-Office Geofence calculation
        $user_row = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        $user_office_id = !empty($user_row) ? $user_row->office_location_id : null;
CODE;

$newMark = <<<'CODE'
        // Multi-Office Geofence calculation
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
CODE;

$content = file_get_contents($clientFile);
$contentNorm = str_replace("\r\n", "\n", $content);
$oldMarkNorm = str_replace("\r\n", "\n", $oldMark);
$newMarkNorm = str_replace("\r\n", "\n", $newMark);

if (strpos($contentNorm, $oldMarkNorm) !== false) {
    $contentNorm = str_replace($oldMarkNorm, $newMarkNorm, $contentNorm);
    file_put_contents($clientFile, str_replace("\n", "\r\n", $contentNorm));
    echo "Client.php mark_attendance patched successfully.\n";
} else {
    echo "Could not find mark block in Client.php\n";
}

// Patch Settings.php
$settingsFile = __DIR__ . '/../crm.hcinterior.in/application/controllers/admin/Settings.php';
$sContent = file_get_contents($settingsFile);
$oldSettings = <<<'CODE'
                $data = array('value' => $value);
                $this->db->where('config_key', $key)->update('tbl_config', $data);
                $exists = $this->db->where('config_key', $key)->get('tbl_config');
                if ($exists->num_rows() == 0) {
                    $this->db->insert('tbl_config', array("config_key" => $key, "value" => $value));
                }
            }
CODE;

$newSettings = <<<'CODE'
                $data = array('value' => $value);
                $this->db->where('config_key', $key)->update('tbl_config', $data);
                $exists = $this->db->where('config_key', $key)->get('tbl_config');
                if ($exists->num_rows() == 0) {
                    $this->db->insert('tbl_config', array("config_key" => $key, "value" => $value));
                }
            }

            // Sync default office in tbl_office_locations if office config was updated
            $new_office_name = $this->input->post('office_location', true);
            $new_office_lat  = $this->input->post('office_lat', true);
            $new_office_long = $this->input->post('office_long', true);
            $new_radius      = $this->input->post('allowed_radius', true);
            if (!empty($new_office_name) && !empty($new_office_lat) && !empty($new_office_long)) {
                if ($this->db->table_exists('tbl_office_locations')) {
                    $def_office = $this->db->where('is_default', 1)->get('tbl_office_locations')->row();
                    if (!empty($def_office)) {
                        $this->db->where('id', $def_office->id)->update('tbl_office_locations', [
                            'location_name'  => $new_office_name,
                            'latitude'       => $new_office_lat,
                            'longitude'      => $new_office_long,
                            'allowed_radius' => intval($new_radius ?: 250),
                            'updated_at'     => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
CODE;

$sNorm = str_replace("\r\n", "\n", $sContent);
$oldSNorm = str_replace("\r\n", "\n", $oldSettings);
$newSNorm = str_replace("\r\n", "\n", $newSettings);

if (strpos($sNorm, $oldSNorm) !== false) {
    $sNorm = str_replace($oldSNorm, $newSNorm, $sNorm);
    file_put_contents($settingsFile, str_replace("\n", "\r\n", $sNorm));
    echo "Settings.php patched successfully.\n";
} else {
    echo "Could not find target block in Settings.php\n";
}
