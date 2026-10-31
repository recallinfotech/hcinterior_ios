<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

$user_id = 92;
$user_type = 3;
$designation_id = 48;

// Permissions check
$has_all_client_perm = false;
$has_region_client_perm = false;
$has_only_assigned_perm = true;

$assigned_client_ids = [];
$res = $m->query("SELECT client_id FROM tbl_client_assign_team WHERE user_id = $user_id");
while ($r = $res->fetch_assoc()) {
    $assigned_client_ids[] = (int)$r['client_id'];
}
$assigned_client_ids = array_unique($assigned_client_ids);

$permission_lead_ids = [];
$res = $m->query("SELECT leads_id FROM tbl_leads WHERE permission LIKE '%\"$user_id\"%'");
while ($r = $res->fetch_assoc()) {
    $permission_lead_ids[] = (int)$r['leads_id'];
}

echo "Assigned Client IDs: " . implode(',', $assigned_client_ids) . "\n";
echo "Permission Lead IDs: " . implode(',', $permission_lead_ids) . "\n";

// Now, what was custom_where in clientList?
$or_conds = [];
if (!empty($assigned_client_ids)) {
    $or_conds[] = "tbl_client.client_id IN (" . implode(',', $assigned_client_ids) . ")";
}
if (!empty($permission_lead_ids)) {
    $or_conds[] = "tbl_client.leads_id IN (" . implode(',', $permission_lead_ids) . ")";
}

$where_clause = "";
if (!empty($or_conds)) {
    $where_clause = "WHERE (" . implode(' OR ', $or_conds) . ")";
} else {
    $where_clause = "WHERE tbl_client.client_id = 0";
}

$sql = "SELECT tbl_client.client_id, tbl_client.name FROM tbl_client $where_clause ORDER BY client_id DESC";
echo "Query: $sql\n";
$res = $m->query($sql);
echo "Count of clients returned: " . $res->num_rows . "\n";
while ($r = $res->fetch_assoc()) {
    echo "Client: " . $r['client_id'] . " - " . $r['name'] . "\n";
}
