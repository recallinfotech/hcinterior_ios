<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    /* Single page optimization */
    @page {
        size: A4;
        margin: 12mm 12mm; /* Tight margins to preserve single-page layout */
    }
    
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10.5pt;
        color: #2c3e50;
        line-height: 1.4;
        margin: 0;
        padding: 0;
    }

    /* Top Customer Details */
    .header-section {
        border-bottom: 2px solid #1a365d;
        padding-bottom: 6px;
        margin-bottom: 10px;
    }
    .client-title {
        font-size: 14pt;
        font-weight: bold;
        color: #1a365d;
        margin: 0 0 2px 0;
    }
    .client-address {
        font-size: 9.5pt;
        color: #555555;
        margin: 0;
    }
    .salutation {
        font-size: 10.5pt;
        margin: 8px 0;
    }

    /* Main Title */
    .document-title {
        text-align: center;
        font-size: 15pt;
        font-weight: bold;
        color: #1a365d;
        margin: 10px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Dashboard Grid / Cards */
    .summary-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px 0;
        margin: 8px -8px;
    }
    .summary-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        text-align: center;
        padding: 8px 5px;
        width: 25%;
    }
    .summary-title {
        font-size: 8.5pt;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 3px;
    }
    .summary-value {
        font-size: 12pt;
        font-weight: bold;
        color: #0f172a;
    }
    .highlight-balance {
        color: #b91c1c;
    }
    .highlight-received {
        color: #15803d;
    }

    /* Section Title */
    .section-title {
        font-size: 11.5pt;
        color: #1a365d;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 3px;
        margin-top: 15px;
        margin-bottom: 8px;
        font-weight: bold;
    }

    /* Milestone Table Layout */
    .milestone-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .milestone-table tr {
        border-bottom: 1px solid #e2e8f0;
    }
    .milestone-table tr:last-child {
        border-bottom: 2px solid #1a365d;
    }
    .milestone-table td {
        padding: 7px 5px;
        font-size: 10pt;
        vertical-align: middle;
    }
    .col-letter {
        width: 5%;
        font-weight: bold;
        color: #64748b;
    }
    .col-desc {
        width: 70%;
        color: #1e293b;
    }
    .col-percent {
        width: 25%;
        text-align: right;
        font-weight: bold;
        color: #1a365d;
    }

    /* Bottom Acceptance & Footer Signatures */
    .acceptance-text {
        font-size: 10pt;
        color: #475569;
        margin-top: 12px;
        margin-bottom: 8px;
    }
    .signature-section {
        width: 100%;
        border-collapse: collapse;
        margin-top: 35px; /* Compact height to avoid page overflow */
    }
    .signature-section td {
        width: 50%;
        border: none;
        font-size: 10.5pt;
        vertical-align: top;
    }
    .signature-line {
        margin-bottom: 4px;
        color: #94a3b8;
    }
</style>
</head>
<body>

<?php 
$user_id        = $this->session->userdata('user_id');
$user_type      = $this->session->userdata('user_type'); 
$designation_id = $this->session->userdata('designations_id');

$payments = get_result(
    'tbl_client_payment_record',
    array(
        'client_id' => $client_details->client_id
    )
);

$url = $this->uri->segment(5);
if ($url == 'payment_record') {
    $id = $this->uri->segment(6);
    $payment_info = $this->db
        ->where('id', $id)
        ->get('tbl_client_payment_record')
        ->row();
} else {
    $id = null;
    $payment_info = null;
}

$latest_proposal = $this->db
    ->where('client_id', $client_details->client_id)
    ->order_by('id', 'DESC')
    ->limit(1)
    ->get('proposals')
    ->row();

$total_amount = !empty($latest_proposal) ? (float)$latest_proposal->totalToBePaid : 0;

$total_paid = $this->db
    ->select_sum('paid_amount')
    ->where('client_id', $client_details->client_id)
    ->get('tbl_client_payment_record')
    ->row()
    ->paid_amount;

$total_paid = $total_paid ?: 0;
$balance_amount = $total_amount - $total_paid;
$received_percentage = 0;

if ($total_amount > 0) {
    $received_percentage = ($total_paid / $total_amount) * 100;
}
?>

<div class="header-section">
    <h5 class="client-title"><?php echo $client_details->name;?> <span style="font-size: 11pt; font-weight: normal; color: #64748b;">(Customer ID: <?php echo $client_details->client_sr_id;?>)</span></h5>
    <p class="client-address"><?php echo $client_details->address;?>, <?php echo $client_details->city;?></p>
</div>

<p class="salutation">Dear Sir,<br>Greetings of the day!!</p>

<div class="document-title">Payment Schedule (Annexure B)</div>

<div class="summary-value">Total Amount : ₹<?= number_format($total_amount, 2) ?></div>
<!-- <table class="summary-table">
    <tr>
        <td class="summary-card">
            <div class="summary-title">Total Amount</div>
            <div class="summary-value">₹<?= number_format($total_amount, 2) ?></div>
        </td>
        <td class="summary-card">
            <div class="summary-title">Total Paid</div>
            <div class="summary-value" style="color: #15803d;">₹<?= number_format($total_paid, 2) ?></div>
        </td>
        <td class="summary-card">
            <div class="summary-title">Balance Amount</div>
            <div class="summary-value highlight-balance">₹<?= number_format($balance_amount, 2) ?></div>
        </td>
        <td class="summary-card">
            <div class="summary-title">Received %</div>
            <div class="summary-value highlight-received"><?= number_format($received_percentage, 2) ?>%</div>
        </td>
    </tr>
</table> -->

<?php if ($user_type == 1 || $designation_id == 45) { ?>

    <?php
    if (!function_exists('getTaskStatus')) {
        function getTaskStatus($client_id, $task_name, $CI)
        {
            $row = $CI->db
                ->select('status')
                ->where('client_id', $client_id)
                ->where('task_name', $task_name)
                ->get('timeline_task_status')
                ->row();

            return $row->status ?? 'Pending';
        }
    }

    $boqAmount       = (float)$total_amount;
    $advanceStatus   = (!empty($client_details->amount_received) && $client_details->amount_received > 0) ? 'Done' : 'Pending';
    $designerStatus  = getTaskStatus($client_details->client_id, 'designer_site_visit_date', $this);
    $siteStartStatus = getTaskStatus($client_details->client_id, 'site_start_date', $this);
    $validationStatus= getTaskStatus($client_details->client_id, 'site_validation', $this);
    $dispatchStatus  = getTaskStatus($client_details->client_id, 'dispatch_date', $this);

    $plan = trim($client_details->payment_plan);
    $milestones = [];

    if (in_array($plan, ['Plan 1', 'Plan 3'])) {
        $milestones = [
            ['title' => 'Advance', 'percent' => 10, 'status' => $advanceStatus],
            ['title' => 'Designer Site Visit', 'percent' => 10, 'status' => $designerStatus],
            ['title' => 'Before Site Start', 'percent' => 40, 'status' => $siteStartStatus],
            ['title' => 'Before Site Validation', 'percent' => 40, 'status' => $validationStatus],
            ['title' => 'Before Dispatch', 'percent' => 20, 'status' => $dispatchStatus],
        ];
    } elseif ($plan == 'Plan 2') {
        $milestones = [
            ['title' => 'Advance', 'percent' => 10, 'status' => $advanceStatus],
            ['title' => 'Designer Site Visit', 'percent' => 10, 'status' => $designerStatus],
            ['title' => 'Before Site Validation', 'percent' => 40, 'status' => $validationStatus],
            ['title' => 'Before Dispatch', 'percent' => 40, 'status' => $dispatchStatus],
        ];
    }
    ?>

    <?php if (!empty($milestones)) { ?>
        
        <div class="section-title">Payment Terms</div>

        <table class="milestone-table">
            <?php
            $letters = ['A', 'B', 'C', 'D', 'E'];
            foreach ($milestones as $index => $row):
                $amount = ($boqAmount * $row['percent']) / 100;
                $description = $row['title'];

                switch ($row['title']) {
                    case 'Advance':
                        $description = 'Advance along with order';
                        break;
                    case 'Designer Site Visit':
                        $description = 'After designer site visit';
                        break;
                    case 'Before Site Start':
                        $description = 'Before start of site work';
                        break;
                    case 'Before Site Validation':
                        $description = 'Before site validation (factory work start)';
                        break;
                    case 'Before Dispatch':
                        $description = 'Before dispatch of wooden material from factory';
                        break;
                }
            ?>
            <tr>
                <td class="col-letter"><?= $letters[$index] ?>.</td>
                <td class="col-desc">
                    <strong>Rs. <?= number_format($amount, 2) ?> /-</strong> 
                    <span style="color: #475569; margin-left: 4px;">(<?= $description ?>)</span>
                </td>
                <td class="col-percent"><?= number_format($row['percent'], 0) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p class="acceptance-text">
            Please signify your acceptance of this costing by signing in the place provided below.
        </p>
        
        <p style="font-size: 10.5pt; font-weight: bold; color: #1a365d; margin-top: 10px; margin-bottom: 2px;">
            Owner's Acceptance:
        </p>

        <table class="signature-section">
            <tr>
                <td>
                    <div class="signature-line">___________________________</div>
                    <strong>Customer Signature</strong>
                </td>
                <td style="text-align: right;">
                    <div class="signature-line">___________________________</div>
                    <strong>Authorized Signatory</strong>
                </td>
            </tr>
        </table>
    <?php } ?>

<?php } ?>

</body>
</html>