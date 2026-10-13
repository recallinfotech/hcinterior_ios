<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{
    font-family: DejaVu Sans, sans-serif;
    font-size:12px;
    color:#333;
    padding:15px;
}

.header{
    text-align:center;
    margin-bottom:20px;
}

.header h2{
    margin:5px 0;
    font-size:22px;
}

.client-box{
    border:1px solid #ccc;
    padding:10px;
    margin-bottom:20px;
    background:#f8f8f8;
}

.summary-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:20px;
}

.summary-table td{
    width:25%;
    border:1px solid #ddd;
    text-align:center;
    padding:15px;
}

.summary-title{
    font-size:13px;
    font-weight:bold;
    margin-bottom:5px;
}

.summary-value{
    font-size:18px;
    font-weight:bold;
}

.alert-box{
    border:1px solid #d9534f;
    background:#fdf2f2;
    padding:12px;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#2c3e50;
    color:#fff;
    padding:10px;
    border:1px solid #ddd;
}

table td{
    border:1px solid #ddd;
    padding:8px;
}

.done{
    color:green;
    font-weight:bold;
}

.pending{
    color:red;
    font-weight:bold;
}

.footer{
    margin-top:40px;
    text-align:right;
}
</style>
</head>
<body>
 


<?php 
                            $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
                       //    echo  $designation_id ;
                           ?>
<?php



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

?>

<?php

$latest_proposal = $this->db
    ->where('client_id', $client_details->client_id)
    ->order_by('id', 'DESC') // ya created_date DESC
    ->limit(1)
    ->get('proposals')
    ->row();

    $total_amount = !empty($latest_proposal)
    ? (float)$latest_proposal->totalToBePaid
    : 0;

 

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

<h5><?php echo  $client_details->name;?> (Customer ID: <?php echo  $client_details->client_sr_id;?>) </h5>
<p><?php echo  $client_details->address;?>, <?php echo  $client_details->city;?></p>

<br>
<p>Dear Sir, <br> Greeting of the day!! </p>

<h2 style="text-align:center">PAYMENT SCHEDULE(ANENXURE B) </h2>

<table class="summary-table">
<tr>

<td>
    <div class="summary-title">Total Amount</div>
    <div class="summary-value">
        ₹<?= number_format($total_amount,2) ?>
    </div>
</td>

<td>
    <div class="summary-title">Total Paid</div>
    <div class="summary-value">
        ₹<?= number_format($total_paid,2) ?>
    </div>
</td>

<td>
    <div class="summary-title">Balance Amount</div>
    <div class="summary-value">
        ₹<?= number_format($balance_amount,2) ?>
    </div>
</td>

<td>
    <div class="summary-title">Received %</div>
    <div class="summary-value">
        <?= number_format($received_percentage,2) ?>%
    </div>
</td>

</tr>
</table>
</div>



 <?php   if ($user_type == 1 || $designation_id  == 45) { ?>

    <?php
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
    ?>

<!-- <?= getTaskStatus($client_details->client_id, 'designer_site_visit_date', $this); ?>

<?= getTaskStatus($client_details->client_id, 'site_start_date', $this); ?>

<?= getTaskStatus($client_details->client_id, 'site_validation', $this); ?>

<?= getTaskStatus($client_details->client_id, 'dispatch_date', $this); ?>
<?php
$advance = (!empty($client_details->amount_received) && $client_details->amount_received > 0)
    ? 'Done'
    : 'Pending';

echo $advance;
?> -->


<?php 
$boqAmount = (float)$total_amount;

$advanceStatus   = (!empty($client_details->amount_received) && $client_details->amount_received > 0) ? 'Done' : 'Pending';

$designerStatus  = getTaskStatus($client_details->client_id, 'designer_site_visit_date', $this);
$siteStartStatus = getTaskStatus($client_details->client_id, 'site_start_date', $this);
$validationStatus= getTaskStatus($client_details->client_id, 'site_validation', $this);
$dispatchStatus  = getTaskStatus($client_details->client_id, 'dispatch_date', $this);

$plan = trim($client_details->payment_plan);

if(in_array($plan, ['Plan 1','Plan 3'])){

    $milestones = [
        [
            'title' => 'Advance',
            'percent' => 10,
            'status' => $advanceStatus
        ],
        [
            'title' => 'Designer Site Visit',
            'percent' => 10,
            'status' => $designerStatus
        ],
        [
            'title' => 'Before Site Start',
            'percent' => 40,
            'status' => $siteStartStatus
        ],
        [
            'title' => 'Before Site Validation',
            'percent' => 40,
            'status' => $validationStatus
        ],
        [
            'title' => 'Before Dispatch',
            'percent' => 20,
            'status' => $dispatchStatus
        ],
    ];

}
elseif($plan == 'Plan 2'){

    $milestones = [
        [
            'title' => 'Advance',
            'percent' => 10,
            'status' => $advanceStatus
        ],
        [
            'title' => 'Designer Site Visit',
            'percent' => 10,
            'status' => $designerStatus
        ],
        [
            'title' => 'Before Site Validation',
            'percent' => 40,
            'status' => $validationStatus
        ],
        [
            'title' => 'Before Dispatch',
            'percent' => 40,
            'status' => $dispatchStatus
        ],
    ];

}

$requiredPercent = 0;
$currentStage = 'Advance';

if($advanceStatus == 'Done'){
    $requiredPercent = 10;
    $currentStage = 'Advance';
}

if($designerStatus == 'Done'){
    $requiredPercent = 20;
    $currentStage = 'Designer Site Visit';
}

if($siteStartStatus == 'Done'){
    $requiredPercent = 40;
    $currentStage = 'Site Start';
}

if($validationStatus == 'Done'){
    $requiredPercent = 80;
    $currentStage = 'Site Validation';
}

if($dispatchStatus == 'Done'){
    $requiredPercent = 100;
    $currentStage = 'Dispatch';
}

$paidPercent = 0;

if($total_amount > 0){
    $paidPercent = ($total_paid / $total_amount) * 100;
}
?>
<?php 
if($paidPercent < $requiredPercent){

    $requiredAmount =
        ($total_amount * $requiredPercent) / 100;

    $shortAmount =
        $requiredAmount - $total_paid;
?>
    
 
<?php
}
 ?>
<?php if(!empty($milestones)){ ?>
<section class="panel panel-custom">
      <div class="panel-body">
<table class="table table-bordered">

    <tr>
        <th>Stage</th>
        <th>%</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>

    <?php foreach($milestones as $row): ?>

        <?php
        $amount = ($boqAmount * $row['percent']) / 100;
        ?>

        <tr>

            <td><?= $row['title'] ?></td>

            <td><?= $row['percent'] ?>%</td>

            <td>₹ <?= number_format($amount,2) ?></td>

            <td>

                <?php if($row['status'] == 'Done'){ ?>

                    <span class="label label-success">
                        Done
                    </span>

                <?php } else { ?>

                    <span class="label label-warning">
                        Pending
                    </span>

                <?php } ?>

            </td>

        </tr>

    <?php endforeach; ?>

</table>
</div></div>
<?php } ?>

<?php } ?>
 
 
 

 

</body>
</html>