
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
<div class="row mb-lg">

    <div class="col-md-3">
        <div class="panel panel-info">
            <div class="panel-heading text-center">
                <strong>Total Amount</strong>
            </div>
            <div class="panel-body text-center">
                <h3>₹<?= number_format($total_amount, 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-success">
            <div class="panel-heading text-center">
                <strong>Total Paid</strong>
            </div>
            <div class="panel-body text-center">
                <h3>₹<?= number_format($total_paid, 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-danger">
            <div class="panel-heading text-center">
                <strong>Balance Amount</strong>
            </div>
            <div class="panel-body text-center">
                <h3>₹<?= number_format($balance_amount, 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
    <div class="panel panel-primary">
        <div class="panel-heading text-center">
            <strong>Received %</strong>
        </div>
        <div class="panel-body text-center">
            <h3><?= number_format($received_percentage, 2) ?>%</h3>
        </div>
    </div>
</div>

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
    
<div class="alert alert-danger">

    <b>Payment Alert</b><br>

    Current Stage :
    <?= $currentStage ?>

    <br>

    Required Payment :
    <?= $requiredPercent ?>%

    (₹<?= number_format($requiredAmount,2) ?>)

    <br>

    Received :
    <?= round($paidPercent,2) ?>%

    (₹<?= number_format($total_paid,2) ?>)

    <br>

    Pending :
    ₹<?= number_format($shortAmount,2) ?>

</div>

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
 
<a href="<?= base_url('admin/client/payment_schedule_pdf/'.$client_details->client_id) ?>"
   target="_blank"
   class="btn btn-danger">
    <i class="fa fa-file-pdf-o"></i> Download Payment Schedule
</a>
<br>

<section class="panel panel-custom">

    <div class="payment_record">

        <div class="panel-heading">

            <div class="panel-title">

                <strong>
                    <?= lang('Payment Record') ?>
                </strong>

                <button id="new_payment"
                        class="btn btn-xs pull-right b0">

                    Add Payment

                </button>

            </div>

        </div>

        <div class="panel-body">

            <!-- PAYMENT FORM -->

            <div class="new_payment mb-lg"
                 style="display: <?= !empty($payment_info) ? 'block' : 'none' ?>">

                <form action="<?= base_url() ?>admin/client/add_payment_record/<?= $id ?>"
                      method="post"
                      class="form-horizontal">

                    <input type="hidden"
                           name="client_id"
                           value="<?= $client_details->client_id ?>">

                    <!-- PAID AMOUNT -->

                    <div class="form-group">

                        <label class="col-lg-4 control-label">

                            Paid Amount
                            <span class="text-danger">*</span>

                        </label>

                        <div class="col-lg-8">

                            <input type="number"
                                   step="0.01"
                                   name="paid_amount"
                                   class="form-control"
                                   value="<?= !empty($payment_info->paid_amount) ? $payment_info->paid_amount : '' ?>"
                                   required>

                        </div>

                    </div>

                    <!-- PAYMENT MODE -->

                    <div class="form-group">

                        <label class="col-lg-4 control-label">

                            Payment Mode

                        </label>

                        <div class="col-lg-8">

                            <select name="payment_mode"
                                    class="form-control">

                                <option value="Cash"
                                    <?= (!empty($payment_info->payment_mode) && $payment_info->payment_mode == 'Cash') ? 'selected' : '' ?>>

                                    Cash

                                </option>

                                <option value="UPI"
                                    <?= (!empty($payment_info->payment_mode) && $payment_info->payment_mode == 'UPI') ? 'selected' : '' ?>>

                                    UPI

                                </option>

                                <option value="Cheque"
                                    <?= (!empty($payment_info->payment_mode) && $payment_info->payment_mode == 'Cheque') ? 'selected' : '' ?>>

                                    Cheque

                                </option>

                                <option value="Bank Transfer"
                                    <?= (!empty($payment_info->payment_mode) && $payment_info->payment_mode == 'Bank Transfer') ? 'selected' : '' ?>>

                                    Bank Transfer

                                </option>

                                <option value="Card"
                                    <?= (!empty($payment_info->payment_mode) && $payment_info->payment_mode == 'Card') ? 'selected' : '' ?>>

                                    Card

                                </option>

                            </select>

                        </div>

                    </div>

                    <!-- PAYMENT DATE -->

                    <div class="form-group">

                        <label class="col-lg-4 control-label">

                            Payment Date

                        </label>

                        <div class="col-lg-8">

                            <input type="date"
                                   name="payment_date"
                                   class="form-control"
                                   value="<?= !empty($payment_info->payment_date) ? $payment_info->payment_date : date('Y-m-d') ?>"
                                   required>

                        </div>

                    </div>

                    <!-- TRANSACTION DETAIL -->

                    <div class="form-group">

                        <label class="col-lg-4 control-label">

                            Transaction Detail

                        </label>

                        <div class="col-lg-8">

                            <textarea name="transaction_detail"
                                      class="form-control"
                                      rows="3"><?= !empty($payment_info->transaction_detail) ? $payment_info->transaction_detail : '' ?></textarea>

                        </div>

                    </div>

                    <!-- STATUS -->

                    <input type="hidden"
                           name="status"
                           value="Paid">

                    <!-- BUTTON -->

                    <div class="form-group">

                        <div class="col-lg-offset-4 col-lg-8">

                            <button class="btn btn-primary"
                                    type="submit">

                                <?= lang('save') ?>

                            </button>

                        </div>

                    </div>

                </form>

            </div>

            <!-- PAYMENT TABLE -->

            <table class="table table-striped"
                   cellspacing="0"
                   width="100%">

                <thead>

                    <tr>

                        <th>
                            Amount
                        </th>

                        <th>
                            Payment Mode
                        </th>

                        <th>
                            Payment Date
                        </th>

                        <th>
                            Transaction Detail
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Date
                        </th>

                        <th class="col-sm-1">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php
                    if (!empty($payments)) {

                        foreach ($payments as $payment) {
                    ?>

                    <tr>

                        <!-- AMOUNT -->

                        <td>

                            ₹<?= number_format($payment->paid_amount, 2) ?>

                        </td>

                        <!-- MODE -->

                        <td>

                            <?= $payment->payment_mode ?>

                        </td>

                        <!-- PAYMENT DATE -->

                        <td>

                            <?= date(
                                'd M Y',
                                strtotime($payment->payment_date)
                            ) ?>

                        </td>

                        <!-- TRANSACTION -->

                        <td>

                            <?= !empty($payment->transaction_detail)
                                ? $payment->transaction_detail
                                : '-' ?>

                        </td>

                        <!-- STATUS -->

                        <td>

                            <span class="label label-success">

                                <?= $payment->status ?>

                            </span>

                        </td>

                        <!-- CREATED DATE -->

                        <td>

                            <?= strftime(
                                config_item('date_format'),
                                strtotime($payment->created_date)
                            ) ?>

                        </td>

                        <!-- ACTION -->

                        <td>

                            <?= btn_edit(
                                'admin/client/details/' .
                                $client_details->client_id .
                                '/payment_record/' .
                                $payment->id
                            ) ?>

                        </td>

                    </tr>

                    <?php
                        }
                    }
                    ?>

                </tbody>

            </table>

        </div>

    </div>

</section>

<script>

$(document).ready(function () {

    $('#new_payment').click(function () {

        $('.new_payment').toggle('slow');

    });

});

</script>