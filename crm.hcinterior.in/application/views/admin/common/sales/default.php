<?php
if (is_file(config_item('invoice_logo'))) {
    $img = base_url() . config_item('invoice_logo');
} else {
    $img = base_url() . 'uploads/default_logo.png';
}
$client_lang = 'english';
?>
<?php if (!empty($sales_info->overdue_days)) { ?>
    <div class="alert bg-danger-light hidden-print">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <i class="fa fa-warning"></i>
        <?= $sales_info->overdue_days ?>
    </div>
    <?php
} ?>
<div class="panel" id="sales_details">
    <div class="panel-body mt-lg">
        <div class="row">
            <div class="col-lg-6 hidden-xs">
                <img class="pl-lg" style="width: 233px;height: 120px;" src="<?= $img ?>">
            </div>
            <div class="col-lg-6 col-xs-12 ">

                <div class="pull-right pr-lg">
                    <h4 class="mb0"><?= $sales_info->ref_no ?></h4>
                    <?= $sales_info->start_date ?>
                    <?php if (!empty($sales_info->end_date)) { ?>
                        <br><?= $sales_info->end_date ?>
                    <?php }
                    if (!empty($sales_info->sales_agent)) { ?>
                        <br><?= $sales_info->sales_agent; ?>
                    <?php }
                    ?>
                    <br><?= $sales_info->status ?>
                    <br><?= $sales_info->custom_field ?>
                </div>
                <?php
                if (!empty($qrcode)) { ?>
                    <div class="pull-right pr-lg mt-lg">
                        <?= (!empty($qrcode) ? $qrcode : '') ?>
                    </div>
                <?php }
                ?>

            </div>

        </div>

        <div class="row mb-lg">
            <div class="col-lg-6 col-xs-6">
                <h5 class="p-md bg-items mr-15">
                    <?= lang('our_info') ?>:
                </h5>
                <div class="pl-sm">
                    <h4 class="mb0">
                        <?= (config_item('company_legal_name_' . $client_lang) ? config_item('company_legal_name_' . $client_lang) : config_item('company_legal_name')) ?>
                    </h4>
                    <?= (config_item('company_address_' . $client_lang) ? config_item('company_address_' . $client_lang) : config_item('company_address')) ?>
                    <br><?= (config_item('company_city_' . $client_lang) ? config_item('company_city_' . $client_lang) : config_item('company_city')) ?>
                    , <?= config_item('company_zip_code') ?>
                    <br><?= (config_item('company_country_' . $client_lang) ? config_item('company_country_' . $client_lang) : config_item('company_country')) ?>
                    <br/><?= lang('phone') ?> : <?= config_item('company_phone') ?>
                    <br/><?= lang('vat_number') ?> : <?= config_item('company_vat') ?>
                </div>
            </div>
            <div class="col-lg-6 col-xs-6 ">
                <h5 class="p-md bg-items ml-13">
                    <?= lang('customer') ?>:
                </h5>
                <div class="pl-sm">
                    <h4 class="mb0"><?= (!empty($sales_info->name) ? $sales_info->name : '') ?></h4>
                    <?= (!empty($sales_info->address) ? $sales_info->address : '') ?>
                    <br> <?= (!empty($sales_info->city) ? $sales_info->city : '') ?>
                    , <?= (!empty($sales_info->zipcode) ? $sales_info->zipcode : '') ?>
                    <br><?= (!empty($sales_info->country) ? $sales_info->country : '') ?>
                    <br><?= lang('phone') ?>: <?= (!empty($sales_info->phone) ? $sales_info->phone : '') ?>
                    <?php if (!empty($sales_info->vat)) { ?>
                        <br><?= lang('vat_number') ?>: <?= $sales_info->vat ?>
                    <?php } ?>
                </div>
            </div>

        </div>
        <style type="text/css">
            .dragger {
                background: url(../../../../assets/img/dragger.png) 0px 11px no-repeat;
                cursor: pointer;
            }

            .table > tbody > tr > td {
                vertical-align: initial;
            }
        </style>

        <div class="table-responsive mb-lg">
            <table class="table items invoice-items-preview table-bordered" page-break-inside: auto;>
                <thead class="bg-items">
                <tr>
                    <th>#</th>
                    <th><?= lang('items') ?></th>
                    <th><?= lang('description') ?></th>
                    <?php
                    $invoice_view = config_item('invoice_view');
                    if (!empty($invoice_view) && $invoice_view == '2') {
                        ?>
                        <th><?= lang('hsn_code') ?></th>
                    <?php } ?>
                    <?php
                    $qty_heading = lang('Area');
                    if (isset($sales_info) && $sales_info->show_quantity_as == 'hours' || isset($hours_quantity)) {
                        $qty_heading = lang('hours');
                    } else if (isset($sales_info) && $sales_info->show_quantity_as == 'qty_hours') {
                        $qty_heading = lang('qty') . '/' . lang('hours');
                    }
                    ?>
                    <th><?php echo $qty_heading; ?></th>
                    <th class="col-sm-1"><?= lang('Unit') ?></th>
                    <th class="col-sm-1"><?= lang('Unit Rate') ?></th>
                    <!-- <th class="col-sm-2"><?= lang('tax') ?></th> -->
                    <th class="col-sm-1"><?= lang('Amount') ?></th>

                </tr>
                </thead>
                <tbody>
<?php
$itemId = (!empty($items_id)) ? $items_id : 'items_id';
$printed_locations = []; // Track printed category_location values
$printed_main_category = []; // Track printed category_location values

if (!empty($all_items)) :
    foreach ($all_items as $key => $v_item) :
        $item_name = $v_item->item_name ? $v_item->item_name : $v_item->item_desc;
        $item_tax_name = json_decode($v_item->item_tax_name);

        // Get saved_items_id from current row
        $saved_items_id = $v_item->saved_items_id;

        // Fetch the matching saved item from the DB
        $this->db->where('saved_items_id', $saved_items_id);
        $saved_item = $this->db->get('tbl_saved_items')->row();

        $location_id = $saved_item->category_location_id ?? null;
        $main_category_id = $saved_item->main_category_id ?? null;

        // Fetch the matching category location
        if ($location_id) {
            $this->db->where('category_location_id', $location_id);
            $category_location = $this->db->get('tbl_category_location')->row();
        } else {
            $category_location = null;
        }
        // Fetch the matching category location
        if ($main_category_id) {
            $this->db->where('category_id', $main_category_id);
            $main_category_location = $this->db->get('tbl_category')->row();
        } else {
            $main_category_location = null;
        }
       
?>
 
   <?php  if (!empty($main_category_location->category_name) && !in_array($main_category_location->category_name, $printed_main_category)) { ?>
<tr>
    <td style="background:#ff5d95; color:#000; padding:2px; text-align:center" colspan="7"> <b><?php 
      // Show category_location only if not printed before
               
                    echo $main_category_location->category_name;
                    $printed_main_category[] = $main_category_location->category_name;
               
                ?></b></td>
    </tr>
    <?php } ?>

<?php  if (!empty($category_location->category_location) && !in_array($category_location->category_location, $printed_locations)) { ?>
<tr>
    <td style="background:#a7c8ff; color:#000; padding:2px" colspan="7"> <b><?php 
      // Show category_location only if not printed before
               
                    echo $category_location->category_location;
                    $printed_locations[] = $category_location->category_location;
               
                ?></b></td>
    </tr>
    <?php } ?>




        <tr class="sortable item" data-item-id="<?= $v_item->$itemId ?>">
            <td class="item_no dragger pl-lg"><?= $key + 1 ?></td>
            <td>
                <strong class="block"><?= $item_name ?></strong>
                <?php
                // Show image(s)
                if (!empty($saved_item) && !empty($saved_item->upload_file)) {
                    $files = json_decode($saved_item->upload_file);
                    if (!empty($files) && is_array($files)) {
                        foreach ($files as $file) {
                            if (!empty($file->is_image) && $file->is_image == '1') {
                                echo '<img src="' . base_url($file->path) . '" alt="' . htmlspecialchars($file->fileName) . '" style="max-width:200px;margin-right:5px;">';
                            } else {
                                echo '<a href="' . base_url($file->path) . '" target="_blank">' . htmlspecialchars($file->fileName) . '</a>';
                            }
                        }
                    }
                }

              
                ?>
            </td>
            <td><?= nl2br($v_item->item_desc) ?></td>
            <?php
            $invoice_view = config_item('invoice_view');
            if (!empty($invoice_view) && $invoice_view == '2') {
                echo '<td>' . $v_item->hsn_code . '</td>';
            }
            ?>
            <td><?= $v_item->quantity ?></td>
            <td><?= $v_item->unit ?></td>
            <td><?= display_money($v_item->unit_cost) ?></td>
            <td><?= display_money($v_item->total_cost) ?></td>
        </tr>
<?php
    endforeach;
else :
?>
    <tr>
        <td colspan="8"><?= lang('nothing_to_display') ?></td>
    </tr>
<?php endif ?>
</tbody>

            </table>
        </div>
        <div class="row" style="margin-top: 35px">
            <div class="col-xs-8">
                <p class="well well-sm mt">
                    <?= $sales_info->notes ?>
                </p>
            </div>
            <div class="col-sm-4 pv">
                <div class="clearfix">
                    <p class="pull-left"><?= lang('sub_total') ?></p>
                    <p class="pull-right mr">
                        <?= $sales_info->sub_total ? display_money($sales_info->sub_total) : '0.00' ?>
                    </p>
                </div>
                <?php if ($sales_info->discount > 0) : ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('discount') ?>
                            (<?php echo $sales_info->discount_percent; ?>
                            %)</p>
                        <p class="pull-right mr">
                            <?= $sales_info->discount ? display_money($sales_info->discount) : '0.00' ?>
                        </p>
                    </div>
                <?php endif ?>
                <?php
                $tax_info = json_decode($sales_info->total_tax);
                $tax_total = 0;
                if (!empty($tax_info)) {
                    $tax_name = $tax_info->tax_name;
                    $total_tax = $tax_info->total_tax;
                    if (!empty($tax_name)) {
                        foreach ($tax_name as $t_key => $v_tax_info) {
                            $tax = explode('|', $v_tax_info);
                            $tax_total += $total_tax[$t_key];
                            ?>
                            <div class="clearfix">
                                <p class="pull-left"><?= $tax[0] . ' (' . $tax[1] . ' %)' ?></p>
                                <p class="pull-right mr">
                                    <?= display_money($total_tax[$t_key]); ?>
                                </p>
                            </div>
                        <?php }
                    }
                } ?>
                <?php if ($tax_total > 0) : ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('total') . ' ' . lang('tax') ?></p>
                        <p class="pull-right mr">
                            <?= display_money($tax_total); ?>
                        </p>
                    </div>
                <?php endif ?>
                <?php if ($sales_info->adjustment > 0) : ?>
                    <div class="clearfix">
                        <p class="pull-left"><?= lang('adjustment') ?></p>
                        <p class="pull-right mr">
                            <?= display_money($sales_info->adjustment); ?>
                        </p>
                    </div>
                <?php endif;
                $currency = get_sales_currency($sales_info);
                ?>
                <div class="clearfix">
                    <p class="pull-left"><?= lang('total') ?></p>
                    <p class="pull-right mr">
                        <?= display_money($sales_info->total, $currency->symbol) ?>
                    </p>
                </div>
                <?php
                if (!empty($paid_amount) && $paid_amount > 0) {
                    $total = lang('total_due');
                    if ($paid_amount > 0) {
                        $text = 'text-danger';
                        ?>
                        <div class="clearfix">
                            <p class="pull-left"><?= lang('paid_amount') ?> </p>
                            <p class="pull-right mr">
                                <?= display_money($paid_amount, $currency->symbol); ?>
                            </p>
                        </div>
                    <?php } else {
                        $text = '';
                    } ?>
                <?php } ?>

                <?php
                if (!empty($paid_amount) && $paid_amount > 0) { ?>
                    <div class="clearfix">
                        <p class="pull-left h3 <?= $text ?>"><?= $total ?></p>
                        <p class="pull-right mr h3"><?= display_money(($invoice_due), $currency->symbol); ?></p>
                    </div>
                <?php } ?>

                <?php
                if (!empty($invoice_due) && $invoice_due > 0) {
                    $due_amount = $invoice_due;
                } else {
                    $due_amount = $sales_info->total;
                }
                if (config_item('amount_to_words') == 'Yes' && !empty($due_amount) && $due_amount > 0) { ?>
                    <div class="clearfix">
                        <p class="pull-right h4"><strong class="h3"><?= lang('num_word') ?>
                                : </strong> <?= number_to_word($currency->code, $due_amount); ?></p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?= !empty($invoice_view) && $invoice_view > 0 ? $this->gst->summary($all_items) : ''; ?>
</div>

<?php include_once 'assets/js/sales.php'; ?>

<script type="text/javascript">
    $(document).ready(function () {
        init_items_sortable(true);
    });

    function print_sales_details(sales_details) {
        var printContents = document.getElementById(sales_details).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>