    <form name="myform" role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
    action="<?php echo base_url(); ?>admin/proposals/save_proposals/<?php
                                                                                                                                                                                if (!empty($proposals_info)) {
                                                                                                                                                                                    echo $proposals_info->proposals_id;
                                                                                                                                                                                }
                                                                                                                                                                                ?>" method="post" class="form-horizontal  ">

    <script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.js"></script>
    <?php include_once 'assets/admin-ajax.php'; ?>
    <?php include_once 'assets/js/sales.php'; ?>
    <?= message_box('success'); ?>
    <?= message_box('error'); ?>

    <?php
    $curency = $this->proposal_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
    ?>
    <div id="proposals_state_report_div"> <?php //$this->load->view("admin/proposals/proposals_state_report"); 
                                            ?></div>
    <?php
    $type = $this->uri->segment(5);

    if (empty($type)) {
        $type = '_' . date('Y');
    }
    ?>

    <?php
    if ($this->session->userdata('user_type') == 1) {
        $type = 'proposal';
        if ($h_s == 'block') {
            $title = lang('hide_quick_state');
            $url = 'hide';
            $icon = 'fa fa-eye-slash';
        } else {
            $title = lang('view_quick_state');
            $url = 'show';
            $icon = 'fa fa-eye';
        }
    ?>
    <div onclick="slideToggle('#state_report')" id="quick_state" data-toggle="tooltip" data-placement="top"
        title="<?= $title ?>" class="btn-xs btn btn-purple pull-left">
        <i class="fa fa-bar-chart"></i>
    </div>
    <div class="btn-xs btn btn-white pull-left ml ">
        <a class="text-dark" id="change_report"
            href="<?= base_url() ?>admin/dashboard/change_report/<?= $url . '/' . $type ?>"><i class="<?= $icon ?>"></i>
            <span><?= ' ' . lang('quick_state') . ' ' . lang($url) . ' ' . lang('always') ?></span></a>
    </div>
    <?php }
    $created = can_action('140', 'created');
    $edited = can_action('140', 'edited');
    $deleted = can_action('140', 'deleted');
    if (!empty($created) || !empty($edited)) {
    ?>

    <div class="row">
        <div class="col-sm-12">

            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs">
                    <li class=""><a href="<?= base_url('admin/proposals') ?>"><?= lang('all_proposals') ?></a>
                    </li>
                    <li class="active"><a
                            href="<?= base_url('admin/proposals/createnewproposal') ?>"><?= lang('create_proposal') ?></a>
                    </li>


                </ul>
                <div class="tab-content bg-white">
                    <!-- ************** general *************-->
                    <div class="tab-pane " id="manage">
                        <?php } else { ?>
                        <div class="panel panel-custom">
                            <header class="panel-heading ">
                                <div class="panel-title"><strong><?= lang('all_proposals') ?></strong></div>
                            </header>
                            <?php } ?>

                        </div>
                        <?php if (!empty($created) || !empty($edited)) { ?>
                        <div class="tab-pane active" id="new">
                            <div class="row mb-lg invoice proposal-template">
                                <div class="col-sm-6 col-xs-12 br pv">
                                    <div class="row">
                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('reference_no') ?> <span
                                                    class="text-danger">*</span></label>
                                            <div class="col-lg-7">
                                                <?php $this->load->helper('string'); ?>
                                                <input type="text" class="form-control" value="<?php
                                                                                                        if (!empty($proposals_info)) {
                                                                                                            echo $proposals_info->reference_no;
                                                                                                        } else {
                                                                                                            if (empty(config_item('proposal_number_format'))) {
                                                                                                                echo config_item('proposal_prefix');
                                                                                                            }
                                                                                                            if (config_item('increment_proposal_number') == 'FALSE') {
                                                                                                                $this->load->helper('string');
                                                                                                                echo random_string('nozero', 6);
                                                                                                            } else {
                                                                                                                echo $this->proposal_model->generate_proposal_number();
                                                                                                            }
                                                                                                        }
                                                                                                        ?>"
                                                    name="reference_no">
                                            </div>

                                        </div>
                                        <?php if (!empty($proposals_info->module)) {
                                                    if ($proposals_info->module == 'leads') {
                                                        $leads_id = $proposals_info->module_id;
                                                    }
                                                    if ($proposals_info->module == 'client') {
                                                        $client_id = $proposals_info->module_id;
                                                    }
                                                } elseif (!empty($module)) {
                                                    if ($module == 'leads') {
                                                        $leads_id = $module_id;
                                                    }
                                                    if ($module == 'client') {
                                                        $client_id = $module_id;
                                                    }
                                                ?>
                                        <input type="hidden" name="un_module" required class="form-control"
                                            value="<?php echo $module ?>" />
                                        <input type="hidden" name="un_module_id" required class="form-control"
                                            value="<?php echo $module_id ?>" />
                                        <?php }
                                                ?>
                                        <div class="form-group" id="border-none">
                                            <label for="field-1"
                                                class="col-sm-3 control-label"><?= lang('related_to') ?> </label>
                                            <div class="col-sm-7">
                                                <select name="module" class="form-control select_box" id="check_related"
                                                    required onchange="get_related_moduleName(this.value,true)"
                                                    style="width: 100%">
                                                    <option value=""> <?= lang('none') ?> </option>
                                                    <option value="leads" <?= (!empty($leads_id) ? 'selected' : '') ?>>
                                                        <?= lang('leads') ?> </option>
                                                    <option value="client"
                                                        <?= (!empty($client_id) ? 'selected' : '') ?>>
                                                        <?= lang('client') ?> </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group" id="related_to">

                                        </div>
                                        <?php if (!empty($leads_id)) : ?>
                                        <div class="form-group <?= $leads_id ? 'leads_module' : 'company' ?>"
                                            id="border-none">
                                            <label for="field-1"
                                                class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('leads') ?>
                                                <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <select name="leads_id" style="width: 100%"
                                                    class="select_box <?= $leads_id ? 'leads_module' : 'company' ?>"
                                                    required="1">
                                                    <?php
                                                                $all_leads_info = $this->db->get('tbl_leads')->result();
                                                                if (!empty($all_leads_info)) {
                                                                    foreach ($all_leads_info as $v_leads) {
                                                                ?>
                                                    <option value="<?= $v_leads->leads_id ?>" <?php
                                                                                                                    if (!empty($leads_id)) {
                                                                                                                        echo $v_leads->leads_id == $leads_id ? 'selected' : '';
                                                                                                                    }
                                                                                                                    ?>>
                                                        <?= $v_leads->lead_name ?></option>
                                                    <?php
                                                                    }
                                                                }
                                                                ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group <?= $leads_id ? 'leads_module' : 'company' ?>"
                                            id="border-none">
                                            <label for="field-1" class="col-sm-3 control-label"><?= lang('currency') ?>
                                                <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <select name="currency" style="width: 100%"
                                                    class="select_box <?= $leads_id ? 'leads_module' : 'company' ?>"
                                                    required="1">
                                                    <?php
                                                                $all_currency = $this->db->get('tbl_currencies')->result();
                                                                foreach ($all_currency as $v_currency) {
                                                                ?>
                                                    <option value="<?= $v_currency->code ?>"
                                                        <?= (config_item('default_currency') == $v_currency->code ? ' selected="selected"' : '') ?>>
                                                        <?= $v_currency->name ?></option>
                                                    <?php
                                                                }
                                                                ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php endif ?>
                                        <?php if (!empty($client_id)) : ?>
                                        <div class="form-group <?= $client_id ? 'client_module' : 'company' ?>"
                                            id="border-none">
                                            <label for="field-1"
                                                class="col-sm-3 control-label"><?= lang('select') . ' ' . lang('client') ?>
                                                <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <select name="client_id" style="width: 100%"
                                                    class="select_box <?= $client_id ? 'client_module' : 'company' ?>"
                                                    required="1">
                                                    <?php
                                                                if (!empty($all_client)) {
                                                                    foreach ($all_client as $v_client) {
                                                                ?>
                                                    <option value="<?= $v_client->client_id ?>" <?php
                                                                                                                    if (!empty($client_id)) {
                                                                                                                        echo $client_id == $v_client->client_id ? 'selected' : '';
                                                                                                                    }
                                                                                                                    ?>>
                                                        <?= $v_client->name ?></option>
                                                    <?php
                                                                    }
                                                                }
                                                                ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php endif ?>

                                        <div class="form-group">
                                            <label for="discount_type"
                                                class="control-label col-sm-3"><?= lang('discount_type') ?></label>
                                            <div class="col-sm-7">
                                                <select name="discount_type" class="selectpicker" data-width="100%">
                                                    <option value="" selected>
                                                        <?php echo lang('no') . ' ' . lang('discount'); ?></option>
                                                    <option value="before_tax" <?php
                                                                                        if (isset($proposals_info)) {
                                                                                            if ($proposals_info->discount_type == 'before_tax') {
                                                                                                echo 'selected';
                                                                                            }
                                                                                        } ?>>
                                                        <?php echo lang('before_tax'); ?></option>
                                                    <option value="after_tax" <?php if (isset($proposals_info)) {
                                                                                            if ($proposals_info->discount_type == 'after_tax') {
                                                                                                echo 'selected';
                                                                                            }
                                                                                        } ?>>
                                                        <?php echo lang('after_tax'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php $this->load->view('admin/items/warehouselist') ?>




                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('status') ?> </label>
                                            <div class="col-lg-7">
                                                <select name="status" class="selectpicker" data-width="100%">
                                                    <option <?php if (isset($proposals_info)) {
                                                                        if ($proposals_info->status == 'draft') {
                                                                            echo 'selected';
                                                                        }
                                                                    } ?> value="draft"><?= lang('draft') ?></option>
                                                    <option <?php if (isset($proposals_info)) {
                                                                        if ($proposals_info->status == 'sent') {
                                                                            echo 'selected';
                                                                        }
                                                                    } ?> value="sent"><?= lang('sent') ?></option>
                                                    <option <?php if (isset($proposals_info)) {
                                                                        if ($proposals_info->status == 'open') {
                                                                            echo 'selected';
                                                                        }
                                                                    } ?> value="open"><?= lang('open') ?></option>
                                                    <option <?php if (isset($proposals_info)) {
                                                                        if ($proposals_info->status == 'revised') {
                                                                            echo 'selected';
                                                                        }
                                                                    } ?> value="revised"><?= lang('revised') ?>
                                                    </option>
                                                    <option <?php if (isset($proposals_info)) {
                                                                        if ($proposals_info->status == 'declined') {
                                                                            echo 'selected';
                                                                        }
                                                                    } ?>value="declined"><?= lang('declined') ?>
                                                    </option>
                                                    <option <?php if (isset($proposals_info)) {
                                                                        if ($proposals_info->status == 'accepted') {
                                                                            echo 'selected';
                                                                        }
                                                                    } ?>value="accepted"><?= lang('accepted') ?>
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="discount_type"
                                                class="control-label col-sm-3"><?= lang('tags') ?></label>
                                            <div class="col-sm-7">
                                                <input type="text" name="tags" data-role="tagsinput"
                                                    class="form-control"
                                                    value="<?php
                                                                                                                                            if (!empty($proposals_info->tags)) {
                                                                                                                                                echo $proposals_info->tags;
                                                                                                                                            }
                                                                                                                                            ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-12 br pv">
                                    <div class="row">
                                        <div class="form-group">
                                            <label for="field-1"
                                                class="col-sm-3 control-label"><?= lang('assigned') . ' ' . lang('users') ?></label>
                                            <div class="col-sm-7">
                                                <select class="form-control select_box" required style="width: 100%"
                                                    name="user_id">
                                                    <option value="">
                                                        <?= lang('select') . ' ' . lang('assigned') . ' ' . lang('users') ?>
                                                    </option>
                                                    <?php
                                                            $all_user = $this->db->where('role_id != ', 2)->get('tbl_users')->result();
                                                            if (!empty($all_user)) {
                                                                foreach ($all_user as $v_user) {
                                                                    $profile_info = $this->db->where('user_id', $v_user->user_id)->get('tbl_account_details')->row();
                                                                    if (!empty($profile_info)) {
                                                            ?>
                                                    <option value="<?= $v_user->user_id ?>" <?php
                                                                                                                if (!empty($proposals_info->user_id)) {
                                                                                                                    echo $proposals_info->user_id == $v_user->user_id ? 'selected' : null;
                                                                                                                } else {
                                                                                                                    echo $this->session->userdata('user_id') == $v_user->user_id ? 'selected' : null;
                                                                                                                }
                                                                                                                ?>>
                                                        <?= $profile_info->fullname ?></option>
                                                    <?php
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('proposal_date') ?></label>
                                            <div class="col-lg-7">
                                                <div class="input-group">
                                                    <input type="text" name="proposal_date"
                                                        class="form-control datepicker"
                                                        value="<?php
                                                                                                                                            if (!empty($proposals_info->proposal_date)) {
                                                                                                                                                echo $proposals_info->proposal_date;
                                                                                                                                            } else {
                                                                                                                                                echo date('Y-m-d');
                                                                                                                                            }
                                                                                                                                            ?>"
                                                        data-date-format="<?= config_item('date_picker_format'); ?>">
                                                    <div class="input-group-addon">
                                                        <a href="#"><i class="fa fa-calendar"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('expire_date') ?></label>
                                            <div class="col-lg-7">
                                                <div class="input-group">
                                                    <input type="text" name="due_date" class="form-control datepicker"
                                                        value="<?php
                                                                                                                                        if (!empty($proposals_info->due_date)) {
                                                                                                                                            echo $proposals_info->due_date;
                                                                                                                                        } else {
                                                                                                                                            echo date('Y-m-d');
                                                                                                                                        }
                                                                                                                                        ?>"
                                                        data-date-format="<?= config_item('date_picker_format'); ?>">
                                                    <div class="input-group-addon">
                                                        <a href="#"><i class="fa fa-calendar"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                                if (!empty($proposals_info)) {
                                                    $proposals_id = $proposals_info->proposals_id;
                                                } else {
                                                    $proposals_id = null;
                                                }
                                                ?>
                                        <?= custom_form_Fields(11, $proposals_id); ?>

                                    </div>
                                    <?php
                                            $permissionL = null;
                                            if (!empty($proposals_info->permission)) {
                                                $permissionL = $proposals_info->permission;
                                            }
                                            ?>
                                    <?= get_permission(3, 7, $permission_user, $permissionL, ''); ?>

                                </div>
                            </div>
                            <div class="form-group terms">
                                <label class="col-lg-1 control-label"><?= lang('notes') ?> </label>
                                <div class="col-lg-11">
                                    <textarea name="notes" class="form-control textarea_"><?php
                                                                                                    if (!empty($proposals_info)) {
                                                                                                        echo $proposals_info->notes;
                                                                                                    } else {
                                                                                                        echo $this->config->item('proposal_terms');
                                                                                                    }
                                                                                                    ?></textarea>
                                </div>
                            </div>
                            <?php
                                    if (!empty($proposals_info)) {
                                        if ($proposals_info->module == 'client') {
                                            $client_info = $this->proposal_model->check_by(array('client_id' => $proposals_info->module_id), 'tbl_client');
                                            $currency = $this->proposal_model->client_currency_symbol($proposals_info->module_id);
                                            $client_lang = $client_info->language;
                                        } else {
                                            $client_info = $this->proposal_model->check_by(array('leads_id' => $proposals_info->module_id), 'tbl_leads');
                                            if (!empty($client_info)) {
                                                $client_info->name = $client_info->lead_name;
                                                $client_info->zipcode = null;
                                            }
                                            $client_lang = 'english';
                                            $currency = $this->proposal_model->check_by(array('code' => $proposals_info->currency), 'tbl_currencies');
                                        }
                                    } else {
                                        $client_lang = 'english';
                                        $currency = $this->proposal_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
                                    }
                                    unset($this->lang->is_loaded[5]);
                                    $language_info = $this->lang->load('sales_lang', $client_lang, TRUE, FALSE, '', TRUE);
                                    ?>

                            <style type="text/css">
                            .dropdown-menu>li>a {
                                white-space: normal;
                            }

                            .dragger {
                                background: url(../assets/img/dragger.png) 10px 32px no-repeat;
                                cursor: pointer;
                            }

                            <?php if ( !empty($proposals_info)) {
                                ?>.dragger {
                                    background: url(../../../../assets/img/dragger.png) 10px 32px no-repeat;
                                    cursor: pointer;
                                }

                                <?php
                            }

                            ?>.input-transparent {
                                box-shadow: none;
                                outline: 0;
                                border: 0 !important;
                                background: 0 0;
                                padding: 3px;
                            }
                            </style>



<div class="row">
 
 <script>
$(document).ready(function(){ 
    // Attach autocomplete dynamically
    function initAutocomplete(floorId){
        $("#itemSearch_"+floorId).autocomplete({
            source: function(request, response){
                $.ajax({
                    url: "<?= base_url('admin/proposals/search') ?>",
                    type: "POST",
                    data: { query: request.term },
                    dataType: "json",
                    success: function(data){
                        response($.map(data, function(item){
                            return {
                                label: item.item_name + " ("+item.product_type+")",
                                value: item.item_name,
                                data: item
                            };
                        }));
                    }
                });
            },
            minLength: 2,
            select: function(event, ui){
                let item = ui.item.data;
            ensureCategorySection(floorId, item.main_category_name);
                // Main row
                let row = `
        <tr data-id="${item.saved_items_id}" data-floor="${floorId}" data-category="${item.main_category_name}">
                        <td>
                            <input type="hidden" name="item_id[${floorId}][]" class="form-control" value="${item.saved_items_id}">
                            <input type="text" name="item_name[${floorId}][]" class="form-control" value="${item.item_name}">
                            <div class="related-items"></div>
                        </td>
                        <td><input type="text" name="description[${floorId}][]" class="form-control" value="${item.item_desc}"></td>
                        <td><input type="number" name="qty[${floorId}][]" class="form-control qty" value="1"></td>
                        <td><input type="text" name="price[${floorId}][]" class="form-control price" value="${item.unit_cost}" data-base="${item.unit_cost}"></td>
                        <td><input type="text" name="tax_rate[${floorId}][]" class="form-control" value="${item.item_tax_rate}"></td>
                        <td><input type="text" name="total[${floorId}][]" class="form-control total" value="${item.unit_cost}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                    </tr>
                    
                `;
            
   // Insert row
$("#itemTable_" + floorId + " tbody tr.category-row[data-category='" + item.main_category_name + "']")
    .after(row);

// Get the newly added row
let $newRow = $(`#itemTable_${floorId} tbody tr[data-id='${item.saved_items_id}']`);

// Calculate totals
calculateRowTotal($newRow);

let $floor = $newRow.closest(".floor-section");
calculateCategoryTotals($floor);
                // Fetch related items
                $.ajax({
                    url: "<?= base_url('admin/proposals/get_related_items') ?>",
                    type: "POST",
                    data: { main_id: item.saved_items_id },
                    dataType: "json",
 success: function(relatedItems){
      if(Object.keys(relatedItems).length > 0){
        let addedIds = new Set();
        let relatedHtml = `
    <button type="button" class="btn btn-link p-0 toggle-related" 
       data-toggle="collapse"
        href="#related_${floorId}_${item.saved_items_id}">
     Addon Items
    </button>
    <div id="related_${floorId}_${item.saved_items_id}" class="panel-collapse collapse mt-1 related-container">
`;

        let extraTotal = 0;
       // alert(relatedItems);
     for (let cat in relatedItems) {
    relatedHtml += `<div><strong>📂 ${cat}</strong></div>`;
    for (let sub in relatedItems[cat]) {
        relatedHtml += `<div style="margin-left:15px;"><em>📌 ${sub}</em></div>`;
        relatedItems[cat][sub].forEach(r => {
            relatedHtml += `
                <label style="margin-left:30px;">
                    <input type="checkbox" class="relatedCheckbox"
                        ${r.selected == 1 ? "checked" : ""}
                        data-floor="${floorId}"
                        data-main="${item.saved_items_id}" 
                        data-id="${r.saved_items_id}" 
                        data-name="${r.item_name}"
                        data-desc="${r.item_desc || ''}"
                        data-price="${r.unit_cost || 0}"
                        data-tax="${r.item_tax_rate || 0}">
                    ${r.item_name}
                </label><br>
            `;
        });
    }
}

relatedHtml += `</div>`; // close collapse

        let row = $(`#floor_${floorId} tr[data-id='${item.saved_items_id}']`);
        row.find(".related-items").html(relatedHtml);

         // recalc totals with pre-selected
            calculateRowTotal(row);
            
            // ✅ Now update the category and floor totals
let $floor = $newRow.closest(".floor-section");
calculateCategoryTotals($floor);
        // Add badges for pre-selected
        relatedItems.forEach(r => {
            if(r.selected == 1){
                row.find(".related-items").append(
                    `<div class="badge bg-info text-dark related-item" 
                        data-id="${r.saved_items_id}">+ ${r.item_name}</div>`
                );
            }
        });

        // Recalculate totals with pre-selected extras
        let qty = parseFloat(row.find(".qty").val()) || 0;
        let price = parseFloat(row.find(".price").val()) || 0;

        let extra = 0;
        relatedItems.forEach(r => {
            if(r.selected == 1){
                extra += parseFloat(r.unit_cost) || 0;
            }
        });

        row.find(".total").val(((qty * price) + extra).toFixed(2));
    }
}
                });

                  $("#itemTable_" + floorId + " tbody tr.category-row[data-category='" + item.category_name+"']")
        .after(row);


                $("#itemSearch_"+floorId).val("");
                return false;
            }
        });
    }

 
   

    $(document).on("click", ".removeRow", function(){
    let $row   = $(this).closest("tr");
    let $floor = $row.closest(".floor-section");
    $row.remove();

    // 🔄 Recalculate totals after removal
    calculateCategoryTotals($floor);
});
    
    $(document).on("click", ".removeblock", function(){
        $(this).closest("div").remove();
    });

    // Auto calculate total
    $(document).on("input", ".qty, .price", function(){
        let row = $(this).closest("tr");
        let qty = parseFloat(row.find(".qty").val()) || 0;
        let price = parseFloat(row.find(".price").val()) || 0;
        let total = qty * price;
        row.find(".total").val(total.toFixed(2));
    });

    // Add new floor dynamically
    let floorCount = 0;
    $("#addFloor").click(function(){
        floorCount++;
       let floorHtml = `
    <div class="floor-section" id="floor_${floorCount}">
        <span class="head">
            <h4>🏢 Floor ${floorCount}</h4>
            <button type="button" class="btn btn-danger btn-sm removeblock">X</button>
        </span>
                 <div class="items_search">
        <select id="mainCategory_${floorCount}" class="form-control mainCategory">
            <option value="">-- Select Category --</option>
               <option value="None">None</option>
            <?php foreach($all_category as $cat){ ?>
                <option value="<?= $cat->category_id ?>"><?= $cat->category_name ?></option>
            <?php } ?>
        </select>

        <select id="subCategory_${floorCount}" class="form-control subCategory">
            <option value="">-- Select Sub Category --</option>
        </select>

        <button type="button" class="btn btn-primary loadItemsBtn" data-floor="${floorCount}">
            Load Items
        </button>
    </div>

    <div id="availableItemsSection_${floorCount}" style="display:none; height:400px; overflow:auto">
        <h4>Available Items</h4>
        <table id="availableItems_${floorCount}" class="table table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                
                    <th>Unit Cost</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
        <div class="items_search">
        <input type="text" id="itemSearch_${floorCount}" class="form-control mb-2" placeholder="Search Item...">
       
 
        <button type="button" class="btn btn-secondary btn-sm addCustomItem" data-floor="${floorCount}">➕ Add Custom Item</button>
        </div>
        <div id="searchResults_${floorCount}"></div>
        <table class="table invoice-items-table items" id="itemTable_${floorCount}">
            <thead style="background: #e8e8e8">
                <tr>
                    <th >Item Name</th>
                    <th width="200px">Description</th>
                    <th width="100px">Qty</th>
                    <th width="150px">Price</th>
                    <th width="70px">Tax </th>
                    <th width="150px">Total</th>
                    <th width="50px">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
           <tfoot>
 
  <tr>
    <td colspan="5" class="text-right text-primary"><h4><strong>Final Floor Total:</strong></h4></td>
    <td><input type="text" class="floor-final-total form-control" readonly></td>
    <td></td>
  </tr>
</tfoot>
        </table>
    </div>
`;
        $("#floors-container").append(floorHtml);
        initAutocomplete(floorCount); // Initialize autocomplete for new floor
    });

    // Add first floor on page load
    $("#addFloor").trigger("click");
});
</script>

<div class="floor-area">
    <div id="floors-container"></div>
   
    <div class="floor_total">
    <button type="button" id="addFloor" class="btn btn-primary addFloor" >➕ Add Floor</button>


   <div class="grand-total-box text-end mt-3 btn-bottom-toolbar text-right form-group">
    <div id="floor-totals-list" class="mt-2 text-start floor-totals"></div>
  <h4><label class="bold text-danger">Grand Total: </label>
    <input type="text" id="grand-total" class="form-control d-inline-block" style="width:200px;" readonly>
  </h4>
  
</div>

</div>
</div>

   
 
<div class="btn-bottom-toolbar text-right">
    <input type="button" id="Preset" value="Reset" name="update" class="btn btn-danger">
            <input type="submit" value="Save As Draft" name="save_as_draft" class="btn btn-primary">
        <input type="submit" value="Update" name="update" class="btn btn-success">
    </div>

</form>

 </div>
<?php } else { ?>
</div>
<?php } ?>
</div>



<script type="text/javascript">
function slideToggle($id) {
    $('#quick_state').attr('data-original-title', '<?= lang('view_quick_state') ?>');
    $($id).slideToggle("slow");
}

$(document).ready(function() {
    init_items_sortable();
    });
</script>
<script>
$(document).ready(function() {
    ins_data(base_url + 'admin/proposals/proposals_state_report')
});


 // Add blank custom item row WITH category selection from dropdown
 // Add blank custom item row — uses category selected in the floor's mainCategory select
$(document).on("click", ".addCustomItem", function(){
    let floorId = $(this).data("floor");
    let $mainSelect = $("#mainCategory_" + floorId);

    // Check select exists
    if ($mainSelect.length === 0) {
        alert("Category selector not found for this floor.");
        return;
    }

    // Get selected category id and name
    let selectedCatId = $mainSelect.val();
    let categoryName = $mainSelect.find("option:selected").text().trim();

    if (!selectedCatId) {
        alert("Please select a category from the dropdown before adding a custom item.");
        $mainSelect.focus();
        return;
    }

    // Ensure category header rows exist
    ensureCategorySection(floorId, categoryName);

    // Create custom row
    let rowId = "custom_" + Date.now();
    let row = `
        <tr data-id="${rowId}" data-floor="${floorId}" data-category="${categoryName}">
            <td>
                <input type="text" name="item_name[${floorId}][]" class="form-control" placeholder="Item Name">
                <div class="related-items"></div>
            </td>
            <td><input type="text" name="description[${floorId}][]" class="form-control" placeholder="Description"></td>
            <td><input type="number" name="qty[${floorId}][]" class="form-control qty" value="1"></td>
            <td><input type="text" name="price[${floorId}][]" class="form-control price" value="0" data-base="0"></td>
            <td><input type="text" name="tax_rate[${floorId}][]" class="form-control tax_rate" value="0"></td>
            <td><input type="text" name="total[${floorId}][]" class="form-control total" value="0"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        </tr>
    `;

    // Find the category header row reliably (avoids selector-escaping issues)
    let $categoryRow = $("#itemTable_" + floorId + " tbody tr.category-row").filter(function(){
        return $(this).data("category") == categoryName;
    });

    if ($categoryRow.length > 0) {
        $categoryRow.after(row);
    } else {
        // Fallback (shouldn't happen because ensureCategorySection created it)
        $("#itemTable_" + floorId + " tbody").append(row);
    }

    // Calculate for new row
    let $newRow = $(`#itemTable_${floorId} tbody tr[data-id='${rowId}']`);
    calculateRowTotal($newRow);

    
});


$(document).on("change", ".relatedCheckbox", function(){
    let cb = $(this);
    let floorId = cb.data("floor");
    let mainId  = cb.data("main");

    // Only update inside the current floor
    let row = $(`#floor_${floorId} tr[data-id='${mainId}']`);

    // Add/remove badges
    if(cb.is(":checked")){
        let html = `<div class="badge bg-info text-dark related-item" data-id="${cb.data('id')}">+ ${cb.data('name')}</div>`;
        row.find(".related-items").append(html);
    } else {
        row.find(`.related-item[data-id='${cb.data("id")}']`).remove();
    }

    // Recalculate total
    let qty = parseFloat(row.find(".qty").val()) || 0;
    let price = parseFloat(row.find(".price").val()) || 0;
    let baseTotal = qty * price;

    let extra = 0;
    row.find(".relatedCheckbox:checked").each(function(){
        extra += parseFloat($(this).data("price")) || 0;
    });

    row.find(".total").val((baseTotal + extra).toFixed(2));
});



$(document).on("click", ".addItemBtn", function(){
    let floorId = $(this).data("floor");
    let name = $(this).data("name");
    let desc = $(this).data("desc");
    let price = parseFloat($(this).data("price")) || 0;
    let category = $(this).data("category"); // make sure backend sends category

    ensureCategorySection(floorId, category);

    let row = `
        <tr data-id="${$(this).data("id")}" data-floor="${floorId}" data-category="${category}">
            <td>${name}
                <input type="hidden" name="item_name[${floorId}][]" value="${name}">
                <div class="related-items"></div>
            </td>
            <td><input type="text" name="description[${floorId}][]" class="form-control" value="${desc}"></td>
            <td><input type="number" name="qty[${floorId}][]" class="form-control qty" value="1"></td>
            <td><input type="text" name="price[${floorId}][]" class="form-control price" value="${price}" data-base="${price}"></td>
            <td><input type="text" name="tax_rate[${floorId}][]" class="form-control tax_rate" value="0"></td>
            <td><input type="text" name="total[${floorId}][]" class="form-control total" value="${price.toFixed(2)}"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        </tr>
    `;
    
    $("#itemTable_" + floorId + " tbody tr.category-row[data-category='" + category +"']")
        .after(row);
        // Calculate totals right away
let $newRow = $("#itemTable_" + floorId + " tbody tr[data-id='" + $(this).data("id") + "']");
calculateRowTotal($newRow);

// ✅ Now update the category and floor totals
let $floor = $newRow.closest(".floor-section");
calculateCategoryTotals($floor);

});

 

function calculateRowTotal($row){
    let qty = parseFloat($row.find(".qty").val()) || 0;
    let taxRate = parseFloat($row.find(".tax_rate").val()) || 0;

    // Check if row is custom
    let isCustom = ($row.data("id") + "").startsWith("custom_");

    let basePrice = 0;
    let price = 0;

    if(isCustom){
        // For manual item, take user-entered price directly
        price = parseFloat($row.find(".price").val()) || 0;
    } else {
        // For system item, recalc base + related
        basePrice = parseFloat($row.find(".price").attr("data-base")) || 0;

        // Add prices of selected related items
        let relatedExtra = 0;
        $row.find(".relatedCheckbox:checked").each(function(){
            relatedExtra += parseFloat($(this).data("price")) || 0;
        });

        price = basePrice + relatedExtra;

        // Update price field (system items only)
        $row.find(".price").val(price.toFixed(2));
    }

    // Subtotal
    let subtotal = price * qty;

    // Tax
    let taxAmount = (subtotal * taxRate) / 100;

    // Final total
    let total = subtotal + taxAmount;

    $row.find(".total").val(total.toFixed(2));

    // Update floor total
    calculateFloorTotal($row.closest(".floor-section"));
}



function calculateFloorTotal($floorSection){
    let floorTotal = 0;
    $floorSection.find(".category-total").each(function(){
        floorTotal += parseFloat($(this).val()) || 0;
    });
    $floorSection.find(".floor-final-total").val(floorTotal.toFixed(2));

    // Update grand total (with floor breakdown)
    calculateGrandTotal();
}


function calculateGrandTotal(){
    let grandTotal = 0;
    let listHtml = "";

    $(".floor-section").each(function(index){
        let floorId = $(this).attr("id"); // e.g. floor_1, floor_2
        let floorNum = index + 1;

        let floorTotal = parseFloat($(this).find(".floor-final-total").val()) || 0;
        grandTotal += floorTotal;

        listHtml += `<h5 class="g-total"><label> ${floorNum} Floor Total:</label> <span>${floorTotal.toFixed(2)}</span></h5>`;
    });

    $("#grand-total").val(grandTotal.toFixed(2));
    $("#floor-totals-list").html(listHtml);
}


 $(document).on("input change", ".qty, .price, .tax_rate, .relatedCheckbox, .category-discount", function(){
    let $row = $(this).closest("tr");
    let $floor = $(this).closest(".floor-section");

    // Always recalc category totals for this floor
    calculateCategoryTotals($floor);
});


    // Bind events
    $(document).on("input change", ".qty, .price, .tax_rate, .relatedCheckbox", function(){
        let $row = $(this).closest("tr");
        calculateRowTotal($row);
    });



    $(document).ready(function(){

    // Load subcategories dynamically
  $(document).on("change", ".mainCategory", function(){
    let floorId = this.id.split("_")[1];  // extract floor number
    let mainCat = $(this).val();

    $.ajax({
        url: "<?= base_url('admin/proposals/get_subcategories') ?>",
        type: "POST",
        data: { main_category_id: mainCat },
        dataType: "json",
        success: function(res){
            let subSelect = $("#subCategory_" + floorId);
            subSelect.empty().append('<option value="">-- Select Sub Category --</option>');
            $.each(res, function(i, sub){
                subSelect.append(`<option value="${sub.category_id}">${sub.category_name}</option>`);
            });
        }
    });
});
 
 // Floor wise Load Items
$(document).on("click", ".loadItemsBtn", function(){
    let floorId = $(this).data("floor");
    let mainCat = $("#mainCategory_" + floorId).val();
    let subCat = $("#subCategory_" + floorId).val();

    $.ajax({
        url: "<?= base_url('admin/proposals/search_by_category') ?>",
        type: "POST",
        data: { main_category_id: mainCat, sub_category_id: subCat },
        dataType: "json",
        success: function(data){
            let tbody = $("#availableItems_" + floorId + " tbody");
            tbody.empty();

            if(data.length > 0){
                $("#availableItemsSection_" + floorId).show();

                $.each(data, function(i, item){
                    let row = `
                        <tr>
                            <td>${item.item_name}</td>
                        
                            <td>${item.unit_cost}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-success addItemBtn"
                                        data-floor="${floorId}"
                                        data-id="${item.saved_items_id}" 
                                        data-name="${item.item_name}" 
                                       
                                         data-desc="<?= htmlspecialchars($item->item_desc, ENT_QUOTES) ?>"
                                         data-price="${item.unit_cost}"
                                        data-category="${item.main_category_name}" >
                                    Add
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                $("#availableItemsSection_" + floorId).hide();
            }
        }
    });
});



});


 

function ensureCategorySection(floorId, categoryName){
    let tbody = $("#itemTable_" + floorId + " tbody");

    let $newRow = $("#itemTable_" + floorId + " tbody tr:last");
calculateRowTotal($newRow);

    let categoryRow = tbody.find("tr.category-row[data-category='" + categoryName + "']");
    if(categoryRow.length === 0){
        let row = `
            <tr class="category-row table-secondary" data-category="${categoryName}">
                <td colspan="7"><strong>📂 ${categoryName}</strong></td>
            </tr>
            
             <tr class="category-subtotal-row table-light" data-category="${categoryName}">
                <td colspan="5" class="text-right"><strong>Sub Total:</strong></td>
                <td><input type="text" class="form-control category-subtotal" readonly></td>
                <td></td>
            </tr>
            <tr class="category-discount-row table-light" data-category="${categoryName}">
                <td colspan="5" class="text-right"><strong>Discount:</strong></td>
                <td><input type="number" class="form-control category-discount" value="0"></td>
                <td></td>
            </tr>
            <tr class="category-total-row table-light" data-category="${categoryName}">
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td><input type="text" class="form-control category-total" readonly></td>
                <td></td>
            </tr>

        `;
        tbody.append(row);
    }
}


 
function calculateCategoryTotals($floorSection){
    let categories = {};

    // Sum item totals per category
    $floorSection.find("tbody tr").each(function(){
        let $tr = $(this);
        let category = $tr.data("category");

        if(category && !$tr.hasClass("category-row") 
                   && !$tr.hasClass("category-subtotal-row") 
                   && !$tr.hasClass("category-discount-row") 
                   && !$tr.hasClass("category-total-row")){
            let total = parseFloat($tr.find(".total").val()) || 0;
            if(!categories[category]) categories[category] = 0;
            categories[category] += total;
        }
    });

    // Update subtotal, apply discount, set final total
    for(let cat in categories){
        let $subtotalRow = $floorSection.find(`.category-subtotal-row[data-category='${cat}']`);
        let $discountRow = $floorSection.find(`.category-discount-row[data-category='${cat}']`);
        let $totalRow = $floorSection.find(`.category-total-row[data-category='${cat}']`);

        let subtotal = categories[cat];
        $subtotalRow.find(".category-subtotal").val(subtotal.toFixed(2));

        let discount = parseFloat($discountRow.find(".category-discount").val()) || 0;
        let total = subtotal - discount;
        if(total < 0) total = 0;

        $totalRow.find(".category-total").val(total.toFixed(2));
    }

    // Update floor total
    calculateFloorTotal($floorSection);
}



$(document).on("input change", ".category-discount", function(){
    let $floor = $(this).closest(".floor-section");
    calculateCategoryTotals($floor);
});

</script>


 <style>
    .floor-section {
      border: 1px solid #ccc;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
    }
    .floor-header {
      font-weight: bold;
      margin-bottom: 10px;
    }
   
     .floor-section {
      border: 1px solid #dedede;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 0;
    position: relative;
    box-shadow: 1px 1px 8px 0px #888888;
    }
    .delete-floor-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      background: red;
      color: white;
      border: none;
      padding: 5px 10px;
      cursor: pointer;
      border-radius: 3px;
    }
    .head {
        display:flex;
    }

    .toggle-related[aria-expanded="true"]::after {
    content: " ▼";
    }
    .toggle-related[aria-expanded="false"]::after {
    content: " ▶";
    }

    .items_search {
        display: flex;
        margin-bottom: 20px;
    }
   
    .grand-total-box h4 {
        display: flex;
    float: right;
    margin: 0;
    margin-right: 17px;
    }

    
    .grand-total-box h4 label{
       
    margin: 6px;
    
    }
    .floor_total {
            display: flex;
    justify-content: space-between;
    margin-bottom:20px;
    }
    .removeblock {
        font-size: 17px;
    border-radius: 50%;
    height: 35px;
    width: 35px;
    padding: 0px;
    margin: 2px;
    float: right;
    }
    
    .floor-totals{
        text-align: right;
    margin-right: 24px;
    }
    .g-total {
        font-size: 18px;
    }
    .g-total span {
        font-weight: 800;
    }
    .addFloor {
        height:30px;
    }
    .floor-area {
        margin:1em
    }
  </style>

