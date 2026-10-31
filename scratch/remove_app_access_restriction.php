<?php
// 1. Patch Login.php
$loginFile = __DIR__ . '/../crm.hcinterior.in/application/controllers/Login.php';
$loginCode = file_get_contents($loginFile);
$loginNorm = str_replace("\r\n", "\n", $loginCode);

// Replace app_bundle_id default
$oldBundle = "(strtolower(\$app_access_param) === 'operation') ? 'com.HCIP.Operation' : 'com.HCIP.HCSales';";
$newBundle = "(strtolower(\$app_access_param) === 'sales') ? 'com.HCIP.HCSales' : 'com.HCIP.Operation';";
if (strpos($loginNorm, $oldBundle) !== false) {
    $loginNorm = str_replace($oldBundle, $newBundle, $loginNorm);
    echo "Login.php bundle ID patched.\n";
}

// Remove blocking app_access check in Login.php
$oldCheck = <<<'CODE'
    // Check app access permission
    $user_app_access = strtolower(trim($details->app_access ?? 'none'));
    if ($user_app_access === 'none' || empty($user_app_access)) {
        $this->db->where('user_id', $user->user_id);
        $this->db->update('tbl_users', array('api_token' => NULL));

        echo json_encode([
            'status'  => false,
            'message' => 'You do not have permission to access the app'
        ]);
        return;
    }
CODE;

$newCheck = <<<'CODE'
    // App access permission check bypassed: All active users can access the Operation app
    $user_app_access = !empty($details->app_access) ? strtolower(trim($details->app_access)) : 'operation';
CODE;

$oldCheckNorm = str_replace("\r\n", "\n", $oldCheck);
$newCheckNorm = str_replace("\r\n", "\n", $newCheck);

if (strpos($loginNorm, $oldCheckNorm) !== false) {
    $loginNorm = str_replace($oldCheckNorm, $newCheckNorm, $loginNorm);
    echo "Login.php app access restriction removed.\n";
} else {
    echo "Warning: target block in Login.php not found.\n";
}

file_put_contents($loginFile, str_replace("\n", "\r\n", $loginNorm));

// 2. Patch admin/User.php
$adminUserFile = __DIR__ . '/../crm.hcinterior.in/application/controllers/admin/User.php';
$adminUserCode = file_get_contents($adminUserFile);
$adminUserNorm = str_replace("\r\n", "\n", $adminUserCode);

$oldAdminCheck = <<<'CODE'
                if (empty($profile_data['app_access']) || strtolower(trim($profile_data['app_access'])) === 'none') {
                    $this->db->where('user_id', $id);
                    $this->db->update('tbl_users', array('api_token' => NULL));
                }
CODE;

$newAdminCheck = <<<'CODE'
                // App access check bypassed - do not revoke token for active users
                // if (empty($profile_data['app_access']) || strtolower(trim($profile_data['app_access'])) === 'none') {
                //     $this->db->where('user_id', $id);
                //     $this->db->update('tbl_users', array('api_token' => NULL));
                // }
CODE;

$oldAdminNorm = str_replace("\r\n", "\n", $oldAdminCheck);
$newAdminNorm = str_replace("\r\n", "\n", $newAdminCheck);

if (strpos($adminUserNorm, $oldAdminNorm) !== false) {
    $adminUserNorm = str_replace($oldAdminNorm, $newAdminNorm, $adminUserNorm);
    echo "admin/User.php token revocation removed.\n";
} else {
    echo "Warning: target block in admin/User.php not found.\n";
}
file_put_contents($adminUserFile, str_replace("\n", "\r\n", $adminUserNorm));

// 3. Patch models/User.php
$modelUserFile = __DIR__ . '/../crm.hcinterior.in/application/models/User.php';
$modelUserCode = file_get_contents($modelUserFile);
$modelUserNorm = str_replace("\r\n", "\n", $modelUserCode);

$oldModelCheck = <<<'CODE'
                if (empty($profile_data['app_access']) || strtolower(trim($profile_data['app_access'])) === 'none') {
                    $this->db->where('user_id', $id);
                    $this->db->update('tbl_users', array('api_token' => NULL));
                }
CODE;

$newModelCheck = <<<'CODE'
                // App access check bypassed - do not revoke token for active users
                // if (empty($profile_data['app_access']) || strtolower(trim($profile_data['app_access'])) === 'none') {
                //     $this->db->where('user_id', $id);
                //     $this->db->update('tbl_users', array('api_token' => NULL));
                // }
CODE;

$oldModelNorm = str_replace("\r\n", "\n", $oldModelCheck);
$newModelNorm = str_replace("\r\n", "\n", $newModelCheck);

if (strpos($modelUserNorm, $oldModelNorm) !== false) {
    $modelUserNorm = str_replace($oldModelNorm, $newModelNorm, $modelUserNorm);
    echo "models/User.php token revocation removed.\n";
} else {
    echo "Warning: target block in models/User.php not found.\n";
}
file_put_contents($modelUserFile, str_replace("\n", "\r\n", $modelUserNorm));
