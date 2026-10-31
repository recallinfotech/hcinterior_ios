<?php
$m = new mysqli('localhost', 'root', '', 'hcinterior_git');

// Check sample clients and payments
$res = $m->query("SELECT c.client_id, c.name, c.client_sr_id, c.amount_received, c.leads_id FROM tbl_client c ORDER BY c.client_id DESC LIMIT 5");
echo "--- Sample Clients ---\n";
while ($c = $res->fetch_assoc()) {
    $cid = $c['client_id'];
    $lid = $c['leads_id'];
    
    // get proposal
    $freeze_boq = 0;
    if ($lid) {
        $p_res = $m->query("SELECT totalToBePaid FROM proposals WHERE lead_id = '$lid' ORDER BY id DESC LIMIT 1");
        if ($p = $p_res->fetch_assoc()) {
            $freeze_boq = floatval(str_replace(',', '', (string)$p['totalToBePaid']));
        }
    }
    if ($freeze_boq == 0) {
        $p_res = $m->query("SELECT totalToBePaid FROM proposals WHERE client_id = '$cid' ORDER BY id DESC LIMIT 1");
        if ($p = $p_res->fetch_assoc()) {
            $freeze_boq = floatval(str_replace(',', '', (string)$p['totalToBePaid']));
        }
    }
    
    // get total paid
    $pay_res = $m->query("SELECT SUM(paid_amount) as total_paid FROM tbl_client_payment_record WHERE client_id = '$cid'");
    $pay = $pay_res->fetch_assoc();
    $raw_amt = floatval(str_replace(',', '', (string)$c['amount_received']));
    $total_paid = ($pay && $pay['total_paid'] > 0) ? floatval($pay['total_paid']) : $raw_amt;
    
    $balance = ($freeze_boq > $total_paid) ? ($freeze_boq - $total_paid) : 0;
    
    echo "Client ID: {$cid} | Name: {$c['name']} | Freeze BOQ: {$freeze_boq} | Total Paid: {$total_paid} | Balance: {$balance}\n";
}

// Test inserting dummy record into tbl_client_invoices and deleting
$m->query("INSERT INTO tbl_client_invoices (client_id, amount, invoice_file, original_name, created_at) VALUES (99999, 15000.50, 'uploads/client_invoices/test.pdf', 'test.pdf', NOW())");
$test_id = $m->insert_id;
echo "Inserted test invoice ID: $test_id\n";

$check = $m->query("SELECT * FROM tbl_client_invoices WHERE id = $test_id")->fetch_assoc();
echo "Fetched test invoice: Amount={$check['amount']}, File={$check['invoice_file']}\n";

$m->query("DELETE FROM tbl_client_invoices WHERE id = $test_id");
echo "Deleted test invoice successfully.\n";
