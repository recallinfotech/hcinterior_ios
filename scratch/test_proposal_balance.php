<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

$res = $m->query("SELECT p.lead_id, p.client_id, p.totalToBePaid, c.name, c.client_id as cid FROM proposals p JOIN tbl_client c ON (c.client_id = p.client_id OR c.leads_id = p.lead_id) WHERE p.totalToBePaid > 0 LIMIT 5");
echo "--- Proposals with positive totalToBePaid ---\n";
while ($row = $res->fetch_assoc()) {
    $cid = $row['cid'];
    $freeze_boq = floatval(str_replace(',', '', (string)$row['totalToBePaid']));
    $pay_res = $m->query("SELECT SUM(paid_amount) as total_paid FROM tbl_client_payment_record WHERE client_id = '$cid'")->fetch_assoc();
    $total_paid = floatval($pay_res['total_paid'] ?? 0);
    $balance = ($freeze_boq > $total_paid) ? ($freeze_boq - $total_paid) : 0;
    echo "Client ID: {$cid} | Name: {$row['name']} | Freeze BOQ: {$freeze_boq} | Paid: {$total_paid} | Balance: {$balance}\n";
}
