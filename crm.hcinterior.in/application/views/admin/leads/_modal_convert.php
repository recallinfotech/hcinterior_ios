<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('convert_to_client') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <form data-parsley-validate="" novalidate="" enctype="multipart/form-data" 
            action="<?php echo base_url() ?>admin/leads/converted/<?php if (!empty($leads_info->leads_id)) echo $leads_info->leads_id; ?>"
            method="post" class="form-horizontal form-groups-bordered">
           
                <div class="row">
                     <div class="col-sm-12">
                        <div class="nav-tabs-custom">
                            <!-- Tabs within a box -->
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#general_compnay"
                                        data-toggle="tab"><?= lang('general') ?></a>
                                </li>
                                <li><a href="#contact_compnay"
                                        data-toggle="tab"><?= lang('Permanent ') . ' ' . lang('Address') ?></a>
                                </li>
                                <li><a href="#site_address" data-toggle="tab"><?= lang('Site Address') ?></a></li>
                                <li><a href="#web_compnay" data-toggle="tab"><?= lang('Sales Person Detail') ?></a></li>
                                <li><a href="#hosting_compnay" data-toggle="tab"><?= lang('Handover Detail') ?></a>
                                </li>
                            </ul>
                            <div class="tab-content bg-white">
                                <!-- ************** general *************-->
                                <div class="chart tab-pane active" id="general_compnay">

                                


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Customer Name') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" required="" value="<?php
                                                   if (!empty($leads_info->contact_name)) {
                                                       echo $leads_info->contact_name;
                                                   }
                                                   ?>" name="name">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Email address') ?>
                                        </label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control company" required="" value="<?php
                                                   if (!empty($leads_info->email)) {
                                                       echo $leads_info->email;
                                                   }
                                                   ?>" name="email">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Customer Mobile number') ?>
                                            <span class="text-danger"> *</span></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" required="" value="<?php
                                                   if (!empty($leads_info->mobile)) {
                                                       echo $leads_info->mobile;
                                                   }
                                                   ?>" name="mobile">
                                        </div>
                                    </div>

                                    <?php 
                                    // last record fetch
$last = $this->db
    ->select('client_sr_id')
    ->order_by('client_id', 'DESC')
    ->limit(1)
    ->get('tbl_client')
    ->row();

if ($last && !empty($last->client_sr_id)) {
    
    // HC101700 → 101700 extract
    $number = (int) preg_replace('/[^0-9]/', '', $last->client_sr_id);
    
    // increment
    $new_number = $number + 1;

} else {
    // first case
    $new_number = 101700;
}

// final id
$new_client_sr_id = 'HC' . $new_number;

?>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Client ID') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" readonly name="client_sr_id" value="<?php echo $new_client_sr_id;?>">
                                        </div>
                                    </div>



                                  
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('short_note') ?></label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control company" name="short_note"> </textarea>
                                        </div>
                                    </div>

                                    <div class="wizard-buttons text-right">
                                        <button type="button" class="btn btn-primary next-step">Next</button>
                                    </div>

                                </div><!-- ************** general *************-->

                                <!-- ************** Contact *************-->
                                <div class="chart tab-pane" id="contact_compnay">
                                   
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Address') ?></label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control company" name="address"><?php
                                                if (!empty($leads_info->address)) {
                                                    echo $leads_info->address;
                                                }
                                                ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('City') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($leads_info->city)) {
                                                echo $leads_info->city;
                                            }
                                            ?>" name="city">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('State') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($leads_info->state)) {
                                                echo $leads_info->state;
                                            }
                                            ?>" name="state">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Pin Code') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" name="zipcode">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Country') ?></label>
                                        <div class="col-lg-9">
                                            <select name="country" class="form-control company select_box"
                                                style="width: 100%">
                                                <optgroup label="Default Country">
                                                    <option value="<?= $this->config->item('company_country') ?>">
                                                        <?= $this->config->item('company_country') ?></option>
                                                </optgroup>
                                                <optgroup label="<?= lang('other_countries') ?>">
                                                    <?php if (!empty($countries)): foreach ($countries as $country): ?>
                                                    <option value="<?= $country->value ?>"
                                                        <?= (!empty($leads_info->country) && $leads_info->country == $country->value ? 'selected' : NULL) ?>>
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

                                     
                                </div><!-- ************** Contact *************-->


                                  <!-- ************** Contact *************-->
                                <div class="chart tab-pane" id="site_address">
                                   
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Address') ?></label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control company" name="site_address" required><?php
                                                if (!empty($leads_info->address)) {
                                                    echo $leads_info->address;
                                                }
                                                ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('City') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($leads_info->city)) {
                                                echo $leads_info->city;
                                            }
                                            ?>" name="site_city" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('State') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($leads_info->state)) {
                                                echo $leads_info->state;
                                            }
                                            ?>" name="site_state" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Pin Code') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" name="site_zipcode" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Country') ?></label>
                                        <div class="col-lg-9">
                                            <select name="site_country" class="form-control company select_box"
                                                style="width: 100%" required>
                                                <optgroup label="Default Country">
                                                    <option value="<?= $this->config->item('company_country') ?>">
                                                        <?= $this->config->item('company_country') ?></option>
                                                </optgroup>
                                                <optgroup label="<?= lang('other_countries') ?>">
                                                    <?php if (!empty($countries)): foreach ($countries as $country): ?>
                                                    <option value="<?= $country->value ?>"
                                                        <?= (!empty($leads_info->country) && $leads_info->country == $country->value ? 'selected' : NULL) ?>>
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

                                  
                                </div><!-- ************** Contact *************-->


                                <!-- ************** Web *************-->
                                <div class="chart tab-pane" id="web_compnay">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales person name') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text"   class="form-control company" value="<?php
                                            if (!empty($user_info->fullname)) {
                                                echo $user_info->fullname;
                                            }
                                            ?>" name="sales_person_name">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales Person Phone') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($user_info->phone)) {
                                                echo $user_info->phone;
                                            }
                                            ?>"   name="sales_person_phone">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales Person Email') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($user_info->email)) {
                                                echo $user_info->email;
                                            }
                                            ?>"   name="sales_person_email">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Sales Manager Name') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($team_leader->fullname)) {
                                                echo $team_leader->fullname;
                                            }
                                            ?>"   name="sales_manage_name">
                                            <input type="hidden" class="form-control company" value="<?php
                                            if (!empty($team_leader->user_id)) {
                                                echo $team_leader->user_id;
                                            }
                                            ?>"   name="sales_manager_id">
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
                                    <input type="hidden" class="form-control company" value="<?php
                                            if (!empty($praposal_info->id)) {
                                                echo $praposal_info->id;
                                            }
                                            ?>" name="proposal_id">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Freeze BOQ AMOUNT') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" readonly value="<?php
                                            if (!empty($praposal_info->totalToBePaid)) {
                                                echo $praposal_info->totalToBePaid;
                                            }
                                            ?>" name="freeze_boq_amount">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('WD BOQ AMOUNT') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" readonly value="<?php
                                            if (!empty($praposal_info->grand_total)) {
                                                echo $praposal_info->grand_total;
                                            }
                                            ?>" name="wd_boq_amount">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label
                                            class="col-lg-3 control-label"><?= lang('Remark OR Any specific information') ?></label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control company" name="short_note"><?php
                                                if (!empty($leads_info->notes)) {
                                                    echo $leads_info->notes;
                                                }
                                                ?></textarea>
                                        </div>
                                    </div>
                                                   
<?php
$civil_discount = '';
$modular_discount = '';

if (!empty($floor_categories)) {
    foreach ($floor_categories as $category) {

        if ($category->category_name == 'Electrical + Plumbing + Civil (EPC)') {
            $civil_discount = $category->discount;
        }

        if ($category->category_name == 'Wooden Work') {
            $modular_discount = $category->discount;
        }
    }
}
?>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Discount (modular)%') ?>
                                        </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" readonly value="<?= $modular_discount ?>" name="discount_modular">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Discount (Civil)%') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" readonly 
                                              value="<?= $civil_discount ?>"
                                               name="discount_civil">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Amount Received') ?></label>
                                        <div class="col-lg-9">
                                            <input type="number" class="form-control company" value="<?php
                                            if (!empty($client_info->port)) {
                                                echo $client_info->port;
                                            }
                                            ?>" name="amount_received">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Payment Mode') ?></label>
                                        <div class="col-lg-9">
                                            <select class="form-control company" name="payment_mode">
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
                                            <input type="file" class="form-control company" name="payment_screenshot">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('City') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($leads_info->city)) {
                                                echo $leads_info->city;
                                            }
                                            ?>" name="lead_city">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Region') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" value="<?php
                                            if (!empty($leads_info->region)) {
                                                echo $leads_info->region;
                                            }
                                            ?>" name="lead_region">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Aadhar Card Number') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" name="aadhaar_number">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Aadhar Card Front') ?></label>
                                        <div class="col-lg-9">
                                            <input type="file" class="form-control company" name="aadhaar_card_front">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Aadhar Card Back') ?></label>
                                        <div class="col-lg-9">
                                            <input type="file" class="form-control company" name="aadhaar_card_back">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Pan Card Number') ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control company" name="pan_card_number">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('Pan Card File') ?></label>
                                        <div class="col-lg-9">
                                            <input type="file" class="form-control company" name="pan_card_file">
                                        </div>
                                    </div>

                                    <div class="wizard-buttons text-right">
                                        <button type="button" class="btn btn-default prev-step">Previous</button>
                                       <button type="submit" id="submitBtn" class="btn btn-success">Submit</button>
                                    </div>

                                </div><!-- ************** Hosting *************-->
                            </div>
                        </div><!-- /.nav-tabs-custom -->
                    </div>
                    <div class="modal-footer">
                        <!-- <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
                        <button type="submit" class="btn btn-primary"><?= lang('save') ?></button> -->
                    </div>

            </form>

    </div>
</div>


<script type="text/javascript">

    $(document).ready(function () {

    $('form').on('submit', function () {

        var btn = $('#submitBtn');

        // prevent double click
        btn.prop('disabled', true);
        btn.html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        return true;
    });

});


function fetch_lat_long_from_google_cprofile() {
    var data = {};
    data.address = $('textarea[name="address"]').val();
    data.city = $('input[name="city"]').val();
    data.country = $('select[name="country"] option:selected').text();
    console.log(data);
    $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
    $.post('<?= base_url()?>admin/common/fetch_address_info_gmaps', data).done(function(data) {
        data = JSON.parse(data);
        $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
        if (data.response.status == 'OK') {
            $('input[name="latitude"]').val(data.lat);
            $('input[name="longitude"]').val(data.lng);
        } else {
            if (data.response.status == 'ZERO_RESULTS') {
                toastr.warning("<?php echo lang('g_search_address_not_found'); ?>");
            } else {
                toastr.warning(data.response.status);
            }
        }
    });
}


$(document).ready(function() {

    var tabs = $('.nav-tabs li'); // tabs li
    var totalSteps = tabs.length;
$(".next-step").click(function() {

    var currentTab = $('.tab-pane.active');
    var valid = true;

    // current tab ke required fields check karo
    currentTab.find('[required]').each(function () {

        if ($(this).val() == '' || $(this).val() == null) {

            $(this).addClass('parsley-error');

            if ($(this).next('.error-msg').length == 0) {
                $(this).after(
                    '<span class="error-msg text-danger">This field is required</span>'
                );
            }

            valid = false;

        } else {

            $(this).removeClass('parsley-error');
            $(this).next('.error-msg').remove();
        }

    });


    // agar required empty hai to aage mat jao
    if (!valid) {
        return false;
    }


    var active = $('.nav-tabs li.active');
    var next = active.next('li');

    if (next.length) {
        next.find('a').trigger('click');
    }

});

    $(".prev-step").click(function() {
        var active = $('.nav-tabs li.active');
        var prev = active.prev('li');

        if (prev.length) {
            prev.find('a').trigger('click');
        }
    });

});


$('.nav-tabs a').on('shown.bs.tab', function() {
    $('.tab-content .tab-pane').hide().removeClass('active');
    var selected = $(this).attr('href');
    $(selected).fadeIn(300).addClass('active');
});
</script>


<style>

.parsley-error {
    border:1px solid red !important;
}

.error-msg {
    display:block;
    margin-top:5px;
}

</style>