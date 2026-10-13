<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<?php
$all_customer_group = $this->db->where('type', 'client')->order_by('customer_group_id', 'DESC')->get('tbl_customer_group')->result();

$curency = $this->client_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');


$id = $this->uri->segment(5);
$search_by = $this->uri->segment(4);
$created = can_action('4', 'created');
$edited = can_action('4', 'edited');
$deleted = can_action('4', 'deleted');
?>
<div class="row">
    <div class="col-sm-12">

        <?php

        if (!empty($created) || !empty($edited)) { ?>
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : '' ?>"><a
                            href="<?= base_url('admin/client/manage_client') ?>"><?= lang('client_list') ?></a></li>
                <li class="<?= $active == 2 ? 'active' : '' ?>"><a
                            href="<?= base_url('admin/client/create_client') ?>"><?= lang('new_client') ?></a></li>
                <li><a class="import"
                       href="<?= base_url() ?>admin/client/import"><?= lang('import') . ' ' . lang('client') ?></a>
                </li>
            </ul>
            <style type="text/css">
                .custom-bulk-button {
                    display: initial;
                }
            </style>
            <div class="tab-content bg-white">

                <div class="tab-pane <?= $active == 2 ? 'active' : '' ?>" id="new_client" style="position: relative;">
                    <form role="form" enctype="multipart/form-data" id="form" data-parsley-validate="" novalidate=""
                          action="<?php echo base_url(); ?>admin/client/save_client/<?php
                          if (!empty($client_info)) {
                              echo $client_info->client_id;
                          }
                          ?>"
                          method="post" class="form-horizontal  ">
                        <div class="panel-body">
                             <div class="col-sm-12">
                                <div class="nav-tabs-custom">
                                    <!-- Tabs within a box -->
                                    <ul class="nav nav-tabs inner_nav_tabs">
                                         <li class="active"><a href="#general_compnay"
                                        data-toggle="tab"><?= lang('general') ?></a>
                                </li>
                                <li><a href="#contact_compnay"
                                        data-toggle="tab"><?= lang('client_contact') . ' ' . lang('details') ?></a>
                                </li>
                                <li><a href="#web_compnay" data-toggle="tab"><?= lang('Sales Person Detail') ?></a></li>
                                <li><a href="#hosting_compnay" data-toggle="tab"><?= lang('Handover Detail') ?></a>
                                </li>
                                    </ul>
                                   <div class="tab-content inner-tab-content bg-white">
                                <!-- ************** general *************-->
                                <div class="chart tab-pane active" id="general_compnay">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Customer Name') ?></label>
                                        <div class="col-lg-9">
                                             <input type="text" class="form-control" required="" value="<?php
                                                    if (!empty($client_info->name)) {
                                                        echo $client_info->name;
                                                    }
                                                    ?>"
                                                           name="name">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Email address') ?>
                                        </label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control " required="" value="<?php
                                                   if (!empty($client_info->email)) {
                                                       echo $client_info->email;
                                                   }
                                                   ?>" name="email">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Customer Mobile number') ?>
                                            <span class="text-danger"> *</span></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " required="" value="<?php
                                                   if (!empty($client_info->mobile)) {
                                                       echo $client_info->mobile;
                                                   }
                                                   ?>" name="mobile">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Client ID') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " name="client_sr_id"  value="<?php
                                                   if (!empty($client_info->client_sr_id)) {
                                                       echo $client_info->client_sr_id;
                                                   }
                                                   ?>" >
                                        </div>
                                    </div>
                                    <!-- <div class="form-group">
                                        <label
                                            class="col-sm-3 control-label"><?= lang('customer_group') ?></label>
                                        <div class="col-sm-5">
                                            <select name="customer_group_id" class="form-control select_box"
                                                    style="width: 100%">
                                                <?php
                                                $all_customer_group = $this->db->order_by('customer_group_id', 'DESC')->get('tbl_customer_group')->result();
                                                if (!empty($all_customer_group)) {
                                                    foreach ($all_customer_group as $customer_group) : ?>
                                                        <option
                                                            value="<?= $customer_group->customer_group_id ?>"<?php
                                                        if (!empty($client_info->customer_group_id) && $client_info->customer_group_id == $customer_group->customer_group_id) {
                                                            echo 'selected';
                                                        } ?>
                                                        ><?= $customer_group->customer_group; ?></option>
                                                    <?php endforeach;
                                                } ?>
                                            </select>
                                        </div>
                                    </div> -->
                                    <!-- <div class="form-group">
                                        <label
                                            class="col-sm-3 control-label"><?= lang('language') ?></label>
                                        <div class="col-sm-5">
                                            <select name="language" class="form-control  select_box"
                                                    style="width: 100%">
                                                <?php foreach ($languages as $lang) : ?>
                                                    <option
                                                        value="<?= $lang->name ?>"<?php
                                                    if (!empty($client_info->language) && $client_info->language == $lang->name) {
                                                        echo 'selected';
                                                    } elseif (empty($client_info->language) && $this->config->item('language') == $lang->name) {
                                                        echo 'selected';
                                                    } ?>
                                                    ><?= ucfirst($lang->name) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div> -->
                                    <!-- <div class="form-group">
                                        <label
                                            class="col-lg-3 control-label"><?= lang('currency') ?></label>
                                        <div class="col-lg-9">
                                            <select name="currency" class="form-control  select_box"
                                                    style="width: 100%">

                                                <?php if (!empty($currencies)): foreach ($currencies as $currency): ?>
                                                    <option
                                                        value="<?= $currency->code ?>"
                                                        <?php
                                                        if (!empty($client_info->currency) && $client_info->currency == $currency->code) {
                                                            echo 'selected';
                                                        } elseif (empty($client_info->currency) && $this->config->item('default_currency') == $currency->code) {
                                                            echo 'selected';
                                                        } ?>
                                                    ><?= $currency->name ?></option>
                                                    <?php
                                                endforeach;
                                                endif;
                                                ?>
                                            </select>
                                        </div>
                                    </div> -->
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('short_note') ?></label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control " name="short_note"> </textarea>
                                        </div>
                                    </div>

                                    <div class="wizard-buttons text-right">
                                        <button type="button" class="btn btn-primary next-step">Next</button>
                                    </div>

                                </div><!-- ************** general *************-->

                                <!-- ************** Contact *************-->
                                <div class="chart tab-pane" id="contact_compnay">
                                    <!-- <div class="form-group">
                                        <label
                                            class="col-lg-3 control-label"><?= lang('_phone') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->phone)) {
                                                echo $client_info->phone;
                                            }
                                            ?>" name="phone">
                                        </div>
                                    </div> -->

                                    <!-- <div class="form-group">
                                        <label
                                            class="col-lg-3 control-label"><?= lang('_fax') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->fax)) {
                                                echo $client_info->fax;
                                            }
                                            ?>" name="fax">
                                        </div>
                                    </div> -->



                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('_address') ?></label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control " name="address"><?php
                                                if (!empty($client_info->address)) {
                                                    echo $client_info->address;
                                                }
                                                ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('City') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->city)) {
                                                echo $client_info->city;
                                            }
                                            ?>" name="city">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('State') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->state)) {
                                                echo $client_info->state;
                                            }
                                            ?>" name="state">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Pin Code') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " name="zipcode">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('_country') ?></label>
                                        <div class="col-lg-9">
                                            <select name="country" class="form-control  select_box"
                                                style="width: 100%">
                                                <optgroup label="Default Country">
                                                    <option value="<?= $this->config->item('_country') ?>">
                                                        <?= $this->config->item('_country') ?></option>
                                                </optgroup>
                                                <optgroup label="<?= lang('other_countries') ?>">
                                                    <?php if (!empty($countries)): foreach ($countries as $country): ?>
                                                    <option value="<?= $country->value ?>"
                                                        <?= (!empty($client_info->country) && $client_info->country == $country->value ? 'selected' : NULL) ?>>
                                                        <?= $country->value ?>
                                                    </option>
                                                    <?php
                                                    endforeach;
                                                    endif;
                                                    ?>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="wizard-buttons text-right">
                                        <button type="button" class="btn btn-default prev-step">Previous</button>
                                        <button type="button" class="btn btn-primary next-step">Next</button>
                                    </div>

                                    <!-- <div class="form-group">
                                        <label
                                            class="col-lg-3 control-label">
                                            <a href="#"
                                               onclick="fetch_lat_long_from_google_cprofile(); return false;"
                                               data-toggle="tooltip"
                                               data-title="<?php echo lang('fetch_from_google') . ' - ' . lang('customer_fetch_lat_lng_usage'); ?>"><i
                                                    id="gmaps-search-icon" class="fa fa-google"
                                                    aria-hidden="true"></i></a>
                                            <?= lang('latitude') . '( ' . lang('google_map') . ' )' ?></label>
                                        <div class="col-lg-5">
                                            <input type="text" class="form-control" value="<?php
                                            if (!empty($client_info->latitude)) {
                                                echo $client_info->latitude;
                                            }
                                            ?>" name="latitude">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label
                                            class="col-lg-3 control-label"><?= lang('longitude') . '( ' . lang('google_map') . ' )' ?></label>
                                        <div class="col-lg-5">
                                            <input type="text" class="form-control"
                                                   value="<?php
                                                   if (!empty($client_info->longitude)) {
                                                       echo $client_info->longitude;
                                                   }
                                                   ?>" name="longitude">
                                        </div>
                                    </div> -->
                                </div><!-- ************** Contact *************-->
                                <!-- ************** Web *************-->
                                <div class="chart tab-pane" id="web_compnay">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales person name') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text"   class="form-control " value="<?php
                                            if (!empty($client_info->sales_person_name)) {
                                                echo $client_info->sales_person_name;
                                            }
                                            ?>" name="sales_person_name">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales Person Phone') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->sales_person_phone)) {
                                                echo $client_info->sales_person_phone;
                                            }
                                            ?>"   name="sales_person_phone">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales Person Email') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->sales_person_email)) {
                                                echo $client_info->sales_person_email;
                                            }
                                            ?>"   name="sales_person_email">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales Manager Name') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->sales_manage_name)) {
                                                echo $client_info->sales_manage_name;
                                            }
                                            ?>"   name="sales_manage_name">
                                        </div>
                                    </div>


                                    <div class="wizard-buttons text-right">
                                        <button type="button" class="btn btn-default prev-step">Previous</button>
                                        <button type="button" class="btn btn-primary next-step">Next</button>
                                    </div>
                                </div>


                                <!-- ************** Web *************-->
                                <!-- ************** Hosting *************-->
                                <div class="chart tab-pane" id="hosting_compnay">
                                    <input type="hidden" class="form-control " value="<?php
                                            if (!empty($client_info->proposal_id)) {
                                                echo $client_info->proposal_id;
                                            }
                                            ?>" name="proposal_id">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Freeze BOQ AMOUNT') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->freeze_boq_amount)) {
                                                echo $client_info->freeze_boq_amount;
                                            }
                                            ?>" name="freeze_boq_amount">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('WD BOQ AMOUNT') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->wd_boq_amount)) {
                                                echo $client_info->wd_boq_amount;
                                            }
                                            ?>" name="wd_boq_amount">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            class="col-lg-3 control-label"><?= lang('Remark OR Any specific information') ?></label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control " name="short_note"><?php
                                                if (!empty($client_info->short_note)) {
                                                    echo $client_info->short_note;
                                                }
                                                ?></textarea>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Discount (modular)%') ?>
                                        </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->discount_modular)) {
                                                    echo $client_info->discount_modular;
                                                }
                                            ?>" name="discount_modular">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Discount (Civil)%') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                           if (!empty($client_info->discount_civil)) {
                                                    echo $client_info->discount_civil;
                                                }
                                            ?>" name="discount_civil">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Amount Received') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                          if (!empty($client_info->amount_received)) {
                                                    echo $client_info->amount_received;
                                                }
                                            ?>" name="amount_received">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Payment Mode') ?></label>
                                        <div class="col-lg-9">
                                            <select class="form-control " name="payment_mode" >
                                                <option value="Cash">Cash</option>
                                                <option value="UPI">UPI</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                                <option value="Cheque">Cheque</option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Payment Screenshot') ?></label>
                                        <div class="col-lg-9">
                                            <?php if (!empty($client_info->payment_screenshot)): ?>
    <?php 
        $payment = json_decode($client_info->payment_screenshot); 
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

<?php endif; ?>
                                            <input type="file" class="form-control " name="payment_screenshot">
                                            
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('City') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->lead_city)) {
                                                echo $client_info->lead_city;
                                            }
                                            ?>" name="lead_city">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Region') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " value="<?php
                                            if (!empty($client_info->lead_region)) {
                                                echo $client_info->lead_region;
                                            }
                                            ?>" name="lead_region">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Aadhar Card Number') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control " name="aadhaar_number">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Aadhar Card Front') ?></label>
                                        <div class="col-lg-9">
                                            <?php if (!empty($client_info->aadhaar_card_front)): ?>
    <?php $aadhaar_front = json_decode($client_info->aadhaar_card_front); ?>

    <img src="<?= base_url($aadhaar_front->path) ?>" 
         style="max-width:200px;border:1px solid #ccc;padding:5px;border-radius:6px;">
<?php endif; ?>

                                            <input type="file" class="form-control " name="aadhaar_card_front">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Aadhar Card Back') ?></label>
                                        <div class="col-lg-9">
                                            <?php if (!empty($client_info->aadhaar_card_back)): ?>
    <?php $aadhaar_back = json_decode($client_info->aadhaar_card_back); ?>

    <img src="<?= base_url($aadhaar_back->path) ?>" 
         style="max-width:200px;border:1px solid #ccc;padding:5px;border-radius:6px;">
<?php endif; ?>

                                            <input type="file" class="form-control " name="aadhaar_card_back">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Pan Card Number') ?></label>
                                        <div class="col-lg-9">
                                            <?php if (!empty($client_info->pan_card_file)): ?>
    <?php $pan = json_decode($client_info->pan_card_file); ?>

    <?php if ($pan->is_image == 1): ?>
        <img src="<?= base_url($pan->path) ?>" 
             style="max-width:200px;border:1px solid #ccc;padding:5px;border-radius:6px;">
    <?php else: ?>
        <a href="<?= base_url($pan->path) ?>" target="_blank" class="btn btn-sm btn-success">
            View PAN File
        </a>
    <?php endif; ?>
<?php endif; ?>

                                            <input type="text" class="form-control " name="pan_card_number">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Pan Card File') ?></label>
                                        <div class="col-lg-9">
                                            <input type="file" class="form-control " name="pan_card_file">
                                        </div>
                                    </div>

                                    <div class="wizard-buttons text-right">
                                        <button type="button" class="btn btn-default prev-step">Previous</button>
                                        <button type="submit" class="btn btn-success">Submit</button>
                                    </div>

                                </div><!-- ************** Hosting *************-->
                            </div>
                                </div><!-- /.nav-tabs-custom -->

                                <!-- <div class="btn-bottom-toolbar text-right">
                                    <?php
                                    if (!empty($client_info)) { ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                                        <button type="button" onclick="goBack()"
                                                class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                                    <?php } else {
                                        ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-primary"><?= lang('save') ?></button>

                                        <button type="submit" name="save_and_create_contact" value="1"
                                                class="btn btn-sm btn-warning"><?= lang('save_and_create_contact') ?></button>
                                    <?php }
                                    ?>
                                </div> -->

                            </div>
                    </form>
                    <?php } else { ?>
                </div>
                <?php } ?>
            </div>
        </div>


        <script type="text/javascript">
 

$(document).ready(function() {

    var tabs = $('.inner_nav_tabs li'); // tabs li
    var totalSteps = tabs.length;

    $(".next-step").click(function() {
        var active = $('.inner_nav_tabs li.active');
        var next = active.next('li');

        if (next.length) {
            next.find('a').trigger('click');
        }
    });

    $(".prev-step").click(function() {
        var active = $('.inner_nav_tabs li.active');
        var prev = active.prev('li');

        if (prev.length) {
            prev.find('a').trigger('click');
        }
    });

});


$('.inner_nav_tabs a').on('shown.bs.tab', function() {
    $('.inner-tab-content .tab-pane').hide().removeClass('active');
    var selected = $(this).attr('href');
    $(selected).fadeIn(300).addClass('active');
});
</script>

 