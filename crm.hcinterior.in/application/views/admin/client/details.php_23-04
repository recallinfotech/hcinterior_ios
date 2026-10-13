<?= message_box('success'); ?>
<?= message_box('error');
$eeror_message = $this->session->userdata('error');
if (!empty($eeror_message)) : foreach ($eeror_message as $key => $message) :
    ?>
    <div class="alert alert-danger">
        <?php echo $message; ?>
    </div>
<?php
endforeach;
endif;
$this->session->unset_userdata('error');

?>
<?php
$client_outstanding = $this->invoice_model->client_outstanding($client_details->client_id);
$client_payments = $this->invoice_model->get_sum('tbl_payments', 'amount', $array = array('paid_by' => $client_details->client_id));
$client_payable = $client_payments + $client_outstanding;
$client_currency = $this->invoice_model->client_currency_symbol($client_details->client_id);
if (!empty($client_currency)) {
    $cur = $client_currency->symbol;
} else {
    $currency = get_row('tbl_currencies', array('code' => config_item('default_currency')));
    $cur = $currency->symbol;
}
if ($client_payable > 0 and $client_payments > 0) {
    $perc_paid = round(($client_payments / $client_payable) * 100, 1);
    if ($perc_paid > 100) {
        $perc_paid = '100';
    }
} else {
    $perc_paid = 0;
}
$edited = can_action('4', 'edited');
?>
     
 <section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><strong><?= $client_details->name ?> - <?= lang('details') ?> </strong>
            <div class="pull-right">
                <?php
                if ($client_details->leads_id != 0) {
                    echo lang('converted_from')
                    ?>
                    <a href="<?= base_url() ?>admin/leads/leads_details/<?= $client_details->leads_id ?>"><?= lang('leads') ?></a>
                <?php }
                if (!empty($edited)) {
                    ?>
                    <a href="<?php echo base_url() ?>admin/client/create_client/<?= $client_details->client_id ?>"
                       class="btn-xs "><i class="fa fa-edit"></i> <?= lang('edit') ?></a>
                <?php } ?>
            </div>
        </div>
    </div>
     <?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="client-details-wrapper">

<!-- ================= CONTACT DETAILS ================= -->
<div class="client-card">
    <h4><?= lang('Contact Details') ?></h4>

    <div class="inline-fields">
        <div class="label-col">Client ID</div>
        <div class="value-col"><?= $client_details->client_sr_id ?></div>
    </div>
    <div class="inline-fields">
        <div class="label-col">Name</div>
        <div class="value-col"><?= $client_details->name ?></div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Email</div>
        <div class="value-col"><?= $client_details->email ?></div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Mobile</div>
        <div class="value-col">
            <a href="tel:<?= $client_details->mobile ?>">
                <?= $client_details->mobile ?>
            </a>
        </div>
    </div>

    <div class="inline-fields">
        <div class="label-col">City</div>
        <div class="value-col"><?= $client_details->city ?></div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Zip Code</div>
        <div class="value-col"><?= $client_details->zipcode ?></div>
    </div>
</div>
<?php $user_id = $this->session->userdata('user_id');
        $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();   
        if($assign_team->member_profile != 'Operation Team') { 
            ?>
<!-- ================= HAND OVER DETAILS ================= -->
<div class="client-card">
    <h4><?= lang('Hand Over Details') ?></h4>

    <div class="inline-fields">
        <div class="label-col">Freeze BOQ</div>
        <div class="value-col amount-highlight">
            ₹<?= number_format($client_details->freeze_boq_amount, 2) ?>
        </div>
    </div>

    <div class="inline-fields">
        <div class="label-col">WD BOQ</div>
        <div class="value-col">
            ₹<?= number_format($client_details->wd_boq_amount, 2) ?>
        </div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Discount (Modular)</div>
        <div class="value-col"><?= $client_details->discount_modular ?>%</div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Discount (Civil)</div>
        <div class="value-col"><?= $client_details->discount_civil ?>%</div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Amount Received</div>
        <div class="value-col"><?= $client_details->amount_received ?></div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Payment Mode</div>
        <div class="value-col"><?= $client_details->payment_mode ?></div>
    </div>
    <div class="inline-fields">
        <div class="label-col">Payment Screenshot</div>
        <div class="value-col"><?php if (!empty($client_details->payment_screenshot)): ?>
    <?php 
        $payment = json_decode($client_details->payment_screenshot); 
    ?>

    <?php if ($payment && $payment->is_image == 1): ?>
        <!-- Image Preview -->
        <img src="<?= base_url($payment->path) ?>" 
             style="max-width:200px;border:1px solid #ccc;padding:5px;border-radius:6px;">

    <?php else: ?>
        <!-- File Download Link -->
        <a href="<?= base_url($payment->path) ?>" target="_blank" class="btn btn-sm btn-primary">
            View File
        </a>
    <?php endif; ?>

<?php endif; ?></div>
    </div>
</div>
<?php } ?>

<!-- ================= DOCUMENT DETAILS ================= -->
<div class="client-card">
    <h4><?= lang('Documents') ?></h4>

    <div class="inline-fields">
        <div class="label-col">Aadhar Front</div>
        <div class="value-col">
           <?php if (!empty($client_details->aadhaar_card_front)): ?>
    <?php $aadhaar_front = json_decode($client_details->aadhaar_card_front); ?>

    <img src="<?= base_url($aadhaar_front->path) ?>" 
         style="max-width:200px;border:1px solid #ccc;padding:5px;border-radius:6px;">
<?php endif; ?>

        </div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Aadhar Back</div>
        <div class="value-col">
           <?php if (!empty($client_details->aadhaar_card_back)): ?>
    <?php $aadhaar_back = json_decode($client_details->aadhaar_card_back); ?>

    <img src="<?= base_url($aadhaar_back->path) ?>" 
         style="max-width:200px;border:1px solid #ccc;padding:5px;border-radius:6px;">
<?php endif; ?>

        </div>
    </div>

    <div class="inline-fields">
        <div class="label-col">PAN Card</div>
        <div class="value-col">
           <?php if (!empty($client_details->pan_card_file)): ?>
    <?php $pan = json_decode($client_details->pan_card_file); ?>

    <?php if ($pan->is_image == 1): ?>
        <img src="<?= base_url($pan->path) ?>" 
             style="max-width:200px;border:1px solid #ccc;padding:5px;border-radius:6px;">
    <?php else: ?>
        <a href="<?= base_url($pan->path) ?>" target="_blank" class="btn btn-sm btn-success">
            View PAN File
        </a>
    <?php endif; ?>
<?php endif; ?>

        </div>
    </div>
</div>

<!-- ================= SALES PERSON DETAILS ================= -->
<div class="client-card">
    <h4><?= lang('Sales Person Detail') ?></h4>

    <div class="inline-fields">
        <div class="label-col">Sales Person</div>
        <div class="value-col"><?= $client_details->sales_person_name ?></div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Sales Manager</div>
        <div class="value-col"><?= $client_details->sales_manage_name ?></div>
    </div>

    <div class="inline-fields">
        <div class="label-col">Email</div>
        <div class="value-col"><?= $client_details->sales_person_email ?></div>
    </div>
</div>

</div>

</div>
 

 

<style>
.client-details-wrapper {
    background: #f5f7fb;
    padding: 20px;
}

.client-card {
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    margin-bottom: 25px;
    padding: 20px;
}

.client-card h4 {
    font-size: 15px;
    font-weight: 600;
    color: #6c757d;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

.inline-fields {
    display: flex;
    margin-bottom: 10px;
    font-size: 14px;
}

.label-col {
    width: 40%;
    color: #6b7280;
    font-weight: 500;
}

.value-col {
    width: 60%;
    color: #111827;
    font-weight: 500;
}

.amount-highlight {
    color: #059669;
    font-weight: 700;
}

.doc-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #2563eb;
}

.doc-link i {
    font-size: 15px;
}

.section-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .section-grid {
        grid-template-columns: 1fr;
    }
}
</style>
