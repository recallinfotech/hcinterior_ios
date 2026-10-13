    <form name="myform" role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
    action="<?php echo base_url(); ?>admin/proposals/save_proposals/<?php
                                                                                                                                                                                if (!empty($proposals_info)) {
                                                                                                                                                                                    echo $proposals_info->id;
                                                                                                                                                                                }
                                                                                                                                                                                ?>" method="post" class="form-horizontal  ">
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.js"></script>
    <?php include_once 'assets/admin-ajax.php'; ?>
    <?php include_once 'assets/js/sales.php'; ?>
    <?= message_box('success'); ?>
    <?= message_box('error'); ?>

    <?php
    //$curency = $this->proposal_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
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
        $h_s = '';
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

                            <input type="hidden" name="proposal_id" id="proposal_id" value="<?= $proposal_id ?? '' ?>">
                             <input type="hidden" name="client_id" id="client_id" value="<?= $client_id ?? '' ?>">

                        <div class="tab-pane active" id="new">
                            <div class="row mb-lg invoice proposal-template">
                                <div class="col-sm-6 col-xs-12 br pv">
                                 
                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('reference_no') ?> <span
                                                    class="text-danger">*</span></label>
                                            <div class="col-lg-7">
                                            <?php // echo  $proposals_info->ref_no ?>
                                            <?php // echo  $proposals_info->version_no ?>
                                            <input type="text" class="form-control" id="ref_no" value="<?php
$ref_no = $proposals_info->ref_no ?? null;
$version_no = $proposals_info->version_no ?? 1; // default to 1 if not set

if (!empty($ref_no)) {
    // Remove existing version if last part is only digits (1–2 digits)
    $base_ref = preg_replace('/\/\d{1,2}$/', '', $ref_no);

    // Always append version number
    echo $base_ref . '/' . $version_no;
}
?>
"
                                            name="reference_no">
                                            </div>

                                        </div>

                                        
   

<div class="form-group">
    <label class="col-sm-3 control-label">Enter Phone</label>
    <div class="col-sm-7 input-wrapper">
        <input type="text" id="phone" class="form-control" value="<?= $proposals_info->phone?>" placeholder="Enter phone">
        <span id="phone-status" class="status-icon">&#10003;</span> <!-- ✔ -->
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Leads</label>
    <div class="col-sm-7">
        <select class="form-control" id="lead_id" name="lead_id">
            <option value="">-- Select Lead --</option>
        </select>
    </div>
</div>

<script>
    let timer;

    $("#phone").on("keyup", function() {
        clearTimeout(timer);
        let phone = $(this).val();

        $("#phone-status").hide(); // hide tick while typing

        if (phone.length >= 5) {
            timer = setTimeout(function() {
                $.post("<?= base_url('/admin/proposals/get_lead_by_phone') ?>", { phone: phone }, function(res) {
                    let data = JSON.parse(res);

                    if (data.status) {
                        $("#lead_id").html(
                            `<option value="${data.lead.leads_id}" selected>
                                ${data.lead.contact_name} (${data.lead.phone})
                            </option>`
                        );
                        $("#phone-status").show(); // ✅ show blue tick
                    } else {
                        $("#lead_id").html('<option value="">-- No Lead Found --</option>');
                        $("#phone-status").hide(); // ❌ hide tick
                    }
                });
            }, 500);
        }
    });
</script>
 
 
                                        <div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('status') ?> </label>
                                            <div class="col-lg-7">
                                                <select name="status" id="status" class="selectpicker" data-width="100%">
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
                                                    <input type="text" id="proposal_date" name="proposal_date" 
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
                                                    <input type="text" id="expiry_date" name="due_date" class="form-control datepicker"
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


                                         <div class="form-group">
    <label class="col-lg-3 control-label"><?= lang('Exclusion List Title') ?></label>

 

    <!-- Text input -->
    <div class="col-lg-5" id="exclusive_product_container"  >
        <input 
            type="text" 
            id="exclusive_product" 
            name="exclusive_product" 
            value="<?= !empty($proposals_info->exclusive_product) ? $proposals_info->exclusive_product : '' ?>" 
            class="form-control" 
            placeholder="Enter Exclusion List title "
        >
    </div>
</div>


                                         <!--<div class="form-group">
                                            <label class="col-lg-3 control-label"><?= lang('Checked By') ?></label>
                                            <div class="col-lg-7">
                                            
                                                    <input type="text" id="checked_by" name="checked_by" class="form-control"
                                                        value="<?php echo $proposals_info->checked_by ?>">
                                                    
                                                
                                            </div>
                                        </div>
										-->
										
										   <?php
$user_id = $this->session->userdata('user_id');
$this->db->where('user_id', $user_id);
$current_user = $this->db->get('tbl_account_details')->row();

$is_team_leader = !empty($current_user) && $current_user->team_leader == 1;
$is_regional_manager = !empty($current_user) && $current_user->regional_manager == 1;
$is_admin = $this->session->userdata('user_type') == 1;



  
?>
<?php if ($is_admin || $is_team_leader || $is_regional_manager) { ?>

    <?php
    $checked_by_name = '';
    if (!empty($proposals_info->checked_by)) {
        // Get fullname from tbl_account_details using checked_by user_id
        $this->db->where('user_id', $proposals_info->checked_by);
        $checked_user = $this->db->get('tbl_account_details')->row();
        if ($checked_user) {
            $checked_by_name = $checked_user->fullname;
        }
    }
    ?>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Checked By') ?></label>
        <div class="col-lg-7">
            <input type="text" id="checked_by" name="checked_by" class="form-control"
                   value="<?php echo $checked_by_name; ?>" readonly>
        </div>
    </div>

<?php } else { ?>

    <div class="form-group">
        <label class="col-lg-3 control-label"><?= lang('Checked By') ?></label>
        <div class="col-lg-7">
            <?php 
            // 1. Get current user
            $user_id = $this->session->userdata('user_id');
            $this->db->where('user_id', $user_id);
            $current_user = $this->db->get('tbl_account_details')->row();

            // 2. Get team_leader_id from current user
            $team_leader_id = $current_user->team_leader_id ?? '';

            // 3. Fetch team leader details
            $team_leader_name = '';
            if (!empty($team_leader_id)) {
                $this->db->where('user_id', $team_leader_id);
                $team_leader = $this->db->get('tbl_account_details')->row();
                if ($team_leader) {
                    $team_leader_name = $team_leader->fullname;
                }
            }
            ?>

            <!-- Hidden input for team leader ID -->
            <input type="hidden" id="checked_by_id" name="checked_by_id" value="<?php echo $team_leader_id; ?>">

            <!-- Disabled text box to show team leader name -->
            <input type="text" id="checked_by" class="form-control" 
                   value="<?php echo $team_leader_name; ?>" disabled>
        </div>
    </div>

<?php } ?>



                                        <?php
                                                if (!empty($proposals_info)) {
                                                    $proposals_id = $proposals_info->id;
                                                } else {
                                                    $proposals_id = null;
                                                }
                                                ?>
                                        <?= custom_form_Fields(11, $proposals_id); ?>

                                    </div>
                                    <!-- <?php
                                            $permissionL = null;
                                            if (!empty($proposals_info->permission)) {
                                                $permissionL = $proposals_info->permission;
                                            }
                                            ?>
                                    <?= get_permission(3, 7, $permission_user, $permissionL, ''); ?> -->

                                </div>
                            </div>
                            <div class="form-group terms">
                                <label class="col-lg-1 control-label"><?= lang('notes') ?> </label>
                                <div class="col-lg-11">
                                    <textarea name="notes" id="notes" class="form-control  "><?php echo $proposals_info->notes ?></textarea>
                                </div>
                            </div>
                            
  </div>
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


                    <div id="floorWrapper">

                     <?php 
                      foreach($proposals as $pro_view) { ?> 
                                 <div id="floors-container2">

                         <?php $i = 1;
                          foreach($proposal_floors as $floor_view) { ?> 
                                    <div class="floor-section" id="floor_<?= $i;?>">
                                        <span class="head">
                                            <h4 class="editable-heading" contenteditable="true"> <?php echo $floor_view['floor_name'];?></h4>
                                            <button type="button" class="btn btn-danger btn-sm removeblock">X</button>
                                        </span>

                                      <div class="items_search">
                                    <select id="mainCategory_<?=$i;?>" class="form-control mainCategory">
                                        <option value="">-- Select Category --</option>
                                        <option value="None">None</option>
                                        <?php foreach($all_category as $cat){ ?>
                                            <option value="<?= $cat->category_id ?>"><?= $cat->category_name ?></option>
                                        <?php } ?>
                                    </select>

                                    <select id="subCategory_<?=$i;?>" class="form-control subCategory">
                                        <option value="">-- Select Sub Category --</option>
                                    </select>

      
     
                                <select id="location_<?=$i;?>" class="form-control location_"  style="">
                                    <option value="">-- Select Location --</option>
                                    <option value="None">None</option>
                                    <?php foreach($category_location as $location){ ?>
                                        <option value="<?= $location->category_location_id ?>"><?= $location->category_location ?></option>
                                    <?php } ?>
                                </select>
                                    </div>
                                        <div class="items_search">
                                            <input type="text" id="itemSearch_<?= $i;?>"
                                                class="form-control mb-2 ui-autocomplete-input"
                                                placeholder="Search Main Item..." style="width:100%" autocomplete="off"
                                               >

                                         <!-- <button type="button" class="btn btn-secondary btn-sm addCustomItem" disabled data-floor="${floorCount}" style="width:15%">➕ Add Custom Item</button> -->
                                        </div>
                                        <div id="searchResults_<?= $i;?>"></div>
                                        <table class="table invoice-items-table items" id="itemTable_<?= $i;?>">
                                            <thead style="background: #e8e8e8">
                                                <tr>
                                                    <th>Item Name</th>
                                                    <th width="200px">Description</th>
                                                    <th width="100px">Qty</th>
                                                    <th width="150px">Price</th>
                                                    <th width="70px">Unit </th>
                                                    <th width="150px">Total</th>
                                                    <th width="100px">Action</th>
                                                </tr>
                                            </thead>
                                           <tbody class="ui-sortable">
                  <?php foreach($proposal_floor_categories as $category_view){ 
                    if($category_view['floor_id'] == $floor_view['id']) { ?>   
                                             
                <tr class="category-row table-secondary" data-category="<?php echo  $category_view['category_name'];?>">
                    <td colspan="7"><strong>📂  <?php echo  $category_view['category_name'];?></strong></td>
                </tr>
            
 

           <?php 
$last_location = ''; // to track previous location

foreach($proposal_items as $item_view) { 
    if($item_view['category_id'] == $category_view['id']){  
        // Check if location has changed
        if($last_location != $item_view['location_name']){
            $last_location = $item_view['location_name']; ?>
            
            <!-- Location Row -->
            <tr class="location-row table-info"
                data-category="<?php echo $category_view['category_name'];?>"
                data-location="<?php echo $item_view['location_name'];?>"
                data-location_name_id="<?php echo $item_view['location_name_id'];?>">
                <td colspan="7"><strong><?php echo $item_view['location_name']; ?></strong></td>
            </tr>
        <?php } ?>

        <!-- Item Row -->
        <tr data-id="<?php echo $item_view['item_id'];?>"
            data-floor="<?= $i;?>"
            data-category="<?php echo $category_view['category_name'];?>"
            data-category_name_id="<?php echo $category_view['id'];?>"
            data-location="<?php echo $item_view['location_name'];?>"
            data-location_name_id="<?php echo $item_view['location_name_id'];?>">

            <td>
                <input type="hidden" name="item_id[<?= $i;?>][]" class="form-control" value="<?php echo $item_view['item_id']; ?>">
                <input type="text" name="item_name[<?= $i;?>][]" class="form-control" value="<?php echo $item_view['item_name']; ?>">
    <?php if($item_view['item_id'] != 0) { ?>       
<div class="related-items">
    <button type="button"
        class="btn btn-link p-0 toggle-related"
        data-toggle="collapse"
        href="#related_<?php echo $i; ?>_<?php echo $item_view['item_id']; ?>"
        aria-expanded="false">
        Addon Items
    </button>
    <div id="related_<?php echo $i; ?>_<?php echo $item_view['item_id']; ?>"
        class="panel-collapse mt-1 related-container collapse" >
        <!-- Related items will be injected here by JS -->
    </div>
 
       <div class="related-items_default<?php echo $item_view['item_id'];?>_<?= $i;?>">
                 <?php foreach($proposal_item_addons as $item_addon) { 
                            if($item_addon['main_item_id'] == $item_view['item_id'] && $item_addon['floor_id'] == $i) { ?>
                               <div class="badge bg-info text-dark related-item" data-id="">+ <?php echo $item_addon['addon_name']; ?></div>

                            <?php }} ?>
                             </div> 
</div>
	<?php } ?>
 
            </td>
            <td><textarea type="text" name="description[<?= $i;?>][]" class="form-control"  ><?php echo $item_view['description']; ?></textarea></td>
            <td><input type="number" name="qty[<?= $i;?>][]" class="form-control qty" value="<?php echo $item_view['qty']; ?>" min="0"></td>
            <td><input type="text" name="price[<?= $i;?>][]" class="form-control price" value="<?php echo $item_view['price']; ?>" data-base="<?php echo $item_view['price']; ?>" data-edit-price="<?php echo $item_view['unit_cost']; ?>" readonly ></td>
            <td><input type="text" name="unit_type[<?= $i;?>][]" class="form-control unit_type" value="<?php echo $item_view['unit_type']; ?>"></td>
            <td><input type="text" name="total[<?= $i;?>][]" class="form-control total" value="<?php echo $item_view['total']; ?>" readonly></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                <button type="button" class="btn btn-primary btn-sm addRowAfter">+</button>
            </td>
        </tr>
        <?php 
            } 
        } 
        ?> 

        <?php 
         $maxDiscount = 0;
//print_r($is_admin);
if ($category_view['category_name'] === 'Electrical + Plumbing + Civil (EPC)') {

    if ($is_admin == 1) {
        $maxDiscount  = 20;
    } 
    else if ($is_regional_manager == 1) {
        $maxDiscount  = 10;
    } 
    else {
        $maxDiscount  = 8;
    }

} 
else if ($category_view['category_name'] === 'Wooden Work') {

    if ($is_admin == 1) {
        $maxDiscount = 40;
    } 
    else if ($is_regional_manager == 1) {
        $maxDiscount = 30;
    } 
    else {
        $maxDiscount = 25;
    }
}
?>
        
        <tr class="category-subtotal-row table-light" data-category="<?php echo  $category_view['category_name'];?>">
                <td colspan="5" class="text-right"><strong>Sub Total:</strong></td>
                <td><input type="text" class="form-control category-subtotal" readonly="" value="<?php echo  $category_view['subtotal'];?>"></td>
                <td></td>
            </tr>
            <tr class="category-discount-row table-light" data-category="<?php echo  $category_view['category_name'];?>">
                <td colspan="5" class="text-right"><strong>Discount (%): </strong></td>
                <td>
				 <input 
type="number" 
class="form-control category-discount"

<?php 
if($proposals_info->updated_by == 5 && $is_admin != 1){
    echo 'readonly';
}
?>

value="<?php echo $category_view['discount'];?>"

data-max="<?php 
echo ($proposals_info->updated_by == 5 && $is_admin != 1) 
        ? $category_view['discount'] 
        : $maxDiscount; 
?>"
>
            <small class="discount-error text-danger" style="display:none;"></small>
        </td>
                <td></td>
            </tr>
            <tr class="category-total-row table-light" data-category="<?php echo  $category_view['category_name'];?>">
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td><input type="text" class="form-control category-total" readonly="" value="<?php echo  $category_view['total'];?>"></td>
                <td></td>
            </tr>
 
                <?php } }?>    
                
       
        </tbody>
                                            <tfoot>

                                                <tr>
                                                    <td colspan="5" class="text-right text-primary">
                                                        <h5><strong>Final Floor Total:</strong></h5>
                                                    </td>
                                                    <td><input type="text" class="floor-final-total form-control"
                                                            readonly="" value="<?php echo $floor_view['total'];?>"></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                     <?php  $i++; }  ?>
                                </div>

                                <?php }?>

   </div>

<div class="row">
 

 <script>
$(document).ready(function(){ 


$(document).on("click", ".toggle-related", function () {
    //$('#saveProposalBtn').hide();
    let container = $($(this).attr("href")); // e.g. #related_1_25
    let mainId = $(this).attr("href").split("_").pop(); // get item_id (25 in your example)
    let proposalId = "<?= $proposal_id ?>"; // from PHP
    let parts = $(this).attr("href").split("_"); 
    let floorId = parts[1];      // "1"

      $(`.related-items_default${mainId}_${floorId}`).hide();


    // Prevent multiple reloads
    if (container.data("loaded")) return;

    $.ajax({
    url: "<?= base_url('admin/proposals/get_related_items_for_proposal_item'); ?>/" + proposalId + "/" + mainId+ "/" + floorId,
    type: "GET",
    dataType: "json",
    success: function (res) {
        let html = "";
        let badgesHtml = ""; // 🟢 collect selected badges

        $.each(res, function (category, subcats) {
            html += `<div><strong>📂 ${category}</strong></div>`;

            $.each(subcats, function (subcat, items) {
                html += `<div style="margin-left:15px;"><em>📌 ${subcat}</em></div>`;

                $.each(items, function (i, item) {
                    html += `
                        <label style="margin-left:30px;">
                            <input type="checkbox" 
                                class="relatedCheckbox"
                                data-main="${mainId}"
                                data-id="${item.saved_items_id}"
                                data-name="${item.item_name}"
                                data-desc="${item.item_desc}"
                                data-price="${item.unit_cost}"
                                data-tax="${item.item_tax_rate}"
                                data-unit="${item.item_unit_type}"
                                data-floor="${floorId}"
                                ${item.selected == 1 ? "checked" : ""}>
                            ${item.item_name}
                        </label><br>
                    `;

                    // 🟢 If pre-selected, also create badge
                    if (item.selected == 1) {
                        badgesHtml += `
                            <div class="badge bg-info text-dark related-item me-1 mb-1" 
                                data-id="${item.saved_items_id}">
                                + ${item.item_name}
                            </div>`;
                    }
                });
            });
        });

        // collapse content
        container.html(html);

        // append selected badges just after collapse
        container.after(badgesHtml);

        container.data("loaded", true); // mark as loaded
    }
});

 

});


$(document).on("change", ".relatedCheckbox", function () {
     $('#saveProposalBtn').hide();
    let $checkbox = $(this);
    let itemId = $checkbox.data("id");
    let itemName = $checkbox.data("name");
    let $relatedContainer = $checkbox.closest(".related-container");

    if ($checkbox.is(":checked")) {
        // ✅ Add badge if not already present
        if ($relatedContainer.siblings(`.related-item[data-id="${itemId}"]`).length === 0) {
            $relatedContainer.after(`
                <div class="badge bg-info text-dark related-item me-1 mb-1" 
                     data-id="${itemId}">
                     + ${itemName}
                </div>
            `);
        }
    } else {
        // ❌ Remove badge if unchecked
        $relatedContainer.siblings(`.related-item[data-id="${itemId}"]`).remove();
    }
});

   $("#checkproposal").on("click", function(e){
    $(".toggle-related").trigger("click");
	    calculateFinalTotal();
    });  
 
$(function () {
    // Auto-click ALL toggle-related buttons
    // $(".toggle-related").each(function () {
    //     $(this).trigger("click");
    // });

    // If you only want the first one:
    // $(".toggle-related").first().trigger("click");

    // Or if you want a specific one:
    // $("a.toggle-related[href='#related_1_25']").trigger("click");
});


    $(document).on("click", ".editable-heading", function () {
         $('#saveProposalBtn').hide();
    let currentText = $(this).text();
    let input = $('<input type="text" class="form-control heading-input" />');
    input.val(currentText);

    $(this).replaceWith(input);
    input.focus();

    // Save on blur or Enter
    input.on("blur keypress", function (e) {
        if (e.type === "blur" || e.which === 13) {
            let newText = $(this).val();
            let h4 = $('<h4 class="editable-heading">' + newText + '</h4>');
            $(this).replaceWith(h4);
        }
    });
});


  $(".floor-section").each(function(){
     $('#saveProposalBtn').hide();
        let floorId = $(this).attr("id").replace("floor_", "");
        initAutocomplete(floorId);
    });

    
    // Attach autocomplete dynamically
     function initAutocomplete(floorId){
        console.log('floor id', floorId);
        $("#itemSearch_"+floorId).autocomplete({
            source: function(request, response){
                  let locationId = $("#location_" + floorId).val(); // Get selected location
  $('#saveProposalBtn').hide();
                $.ajax({
                    url: "<?= base_url('admin/proposals/search') ?>",
                    type: "POST",
                    data: { 
                        query: request.term,
                        category_location_id: locationId,
        main_category_id: $("#mainCategory_" + floorId).val(),  // ✅ send main category
        sub_category_id: $("#subCategory_" + floorId).val()  
                    },
                    dataType: "json",
                    success: function(data){
                        response($.map(data, function(item){ 
                            return {
                                label: item.item_name + " (" + item.unit_cost + " Rs per " + item.unit_type + ")",
                                value: item.item_name,
                                location: item.location_name, // 👈 location instead of main_category
                                data: item
                            };
                        }));
                    }
                });
            },
            minLength: 0,
            
            select: function(event, ui){
                let item = ui.item.data;
                ensureCategorySection(floorId, item.main_category_name, item.location_name);
                // Main row
                let row = `
                    <tr data-id="${item.saved_items_id}" data-floor="${floorId}" data-category="${item.main_category_name}" data-category_name_id="${item.main_category_id}"  data-location="${item.location_name}"
                     data-location_name_id="${item.category_location_id}">
                        <td>
                             <input type="hidden" name="item_id[${floorId}][]" class="form-control" value="${item.saved_items_id}">
                            <input type="text" name="item_name[${floorId}][]" class="form-control" value="${item.item_name}">
                            <div class="related-items"></div>
                        </td>
                        <td><textarea type="text" name="description[${floorId}][]" class="form-control" title="${item.item_desc}"   >${item.item_desc}</textarea></td>
                        <td><input type="number" name="qty[${floorId}][]" class="form-control qty" value="1" min="0"></td>
                        <td><input type="text" name="price[${floorId}][]" class="form-control price" value="${item.unit_cost}" data-base="${item.unit_cost}"  data-edit-price="${item.unit_cost}" readonly></td>  
                        <td><input type="text" name="unit_type[${floorId}][]" class="form-control unit_type" value="${item.unit_type}"></td>
                        <td><input type="text" name="total[${floorId}][]" class="form-control total" value="${item.unit_cost}" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                          <button type="button" class="btn btn-primary btn-sm addRowAfter">+</button>
                          </td>
                    </tr>
                    
                `;
            
                // Insert row
       
             // Insert row
        let $existingRows = $("#itemTable_" + floorId + " tbody tr[data-category='" + item.main_category_name + "'][data-location='" + item.location_name + "']");

if ($existingRows.length) {
    // Insert after the last row in the same location group
    $existingRows.last().after(row);
} else {
    // Otherwise, insert after the category header row
    $("#itemTable_" + floorId + " tbody tr.category-row[data-category='" + item.main_category_name + "']").after(row);
}


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
                                    data-unit="${r.unit_type || 0}"
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

                $("#itemSearch_"+floorId).val("");
                return false;
            }
        }) 
 // ✅ these must be chained here, *outside* of select
    .focus(function () {
        $(this).autocomplete("search", "");
    })
    .click(function () {
        $(this).autocomplete("search", "");
    });
   
   
    }

$(document).on("click", ".removeRow", function(){
     $('#saveProposalBtn').hide();
    let $row = $(this).closest("tr");
    let $floor = $row.closest(".floor-section");
    let category = $row.data("category");
    let location = $row.data("location");

    // ❌ Remove the row
    $row.remove();

    // ✅ Check if any item remains under this location
    let hasLocationItems = $floor.find("tbody tr").filter(function(){
        let $tr = $(this);
        return $tr.data("category") === category 
               && $tr.data("location") === location
               && !$tr.hasClass("category-row") 
               && !$tr.hasClass("category-subtotal-row") 
               && !$tr.hasClass("category-discount-row") 
               && !$tr.hasClass("category-total-row") 
               && !$tr.hasClass("location-row");
    }).length > 0;

    if(!hasLocationItems){
        // remove location header if no items left
        $floor.find(`tbody tr.location-row[data-category='${category}'][data-location='${location}']`).remove();
    }

    // ✅ Check if category has any remaining item rows
    let hasCategoryItems = $floor.find("tbody tr").filter(function(){
        let $tr = $(this);
        return $tr.data("category") === category 
               && !$tr.hasClass("category-row") 
               && !$tr.hasClass("category-subtotal-row") 
               && !$tr.hasClass("category-discount-row") 
               && !$tr.hasClass("category-total-row") 
               && !$tr.hasClass("location-row");
    }).length > 0;

    if(!hasCategoryItems){
        // remove category header + all totals/discount rows
        $floor.find("tbody tr.category-row[data-category='"+category+"']").remove();
        $floor.find("tbody tr.category-subtotal-row[data-category='"+category+"']").remove();
        $floor.find("tbody tr.category-discount-row[data-category='"+category+"']").remove();
        $floor.find("tbody tr.category-total-row[data-category='"+category+"']").remove();
        $floor.find("tbody tr.location-row[data-category='"+category+"']").remove();
    }

    // ✅ Recalculate totals
    calculateCategoryTotals($floor);
	calculateGrandTotal();
    calculateFinalTotal();
});




$(document).on("click", ".removeblock", function(){
     $('#saveProposalBtn').hide();
        $(this).closest("div").remove();
		let $floor = $(this).closest(".floor-section");
    calculateCategoryTotals($floor);
			calculateGrandTotal();
			    calculateFinalTotal();

				 

    });

    // Auto calculate total
    $(document).on("input", ".qty, .price", function(){
         $('#saveProposalBtn').hide();
        let row = $(this).closest("tr");
        let qty = parseFloat(row.find(".qty").val()) || 0;
        let price = parseFloat(row.find(".price").val()) || 0;
        let total = qty * price;
        row.find(".total").val(total.toFixed(2));
    });

    // Add new floor dynamically
    let floorCount = <?= $proposals_count;?>;
    $("#addFloor").click(function(){
        floorCount++;
             let floorLabel = (floorCount === 1) ? "Ground Floor" : "Floor " + (floorCount - 1);
       let floorHtml = `       
    <div class="floor-section" id="floor_${floorCount}">
        <span class="head">
            <h4 class="editable-heading" contenteditable="true">🏢 ${floorLabel}</h4>
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

      
     
          <select id="location_${floorCount}" class="form-control location_"  style="">
            <option value="">-- Select Location --</option>
               <option value="None">None</option>
            <?php foreach($category_location as $location){ ?>
                <option value="<?= $location->category_location_id ?>"><?= $location->category_location ?></option>
            <?php } ?>
        </select>
        </div>
   <div class="items_search">
        <input type="text" id="itemSearch_${floorCount}" class="form-control mb-2" placeholder="Search Main Item..."  style="width:85%">
     <button type="button" class="btn btn-secondary btn-sm addCustomItem" disabled data-floor="${floorCount}" style="width:15%">➕ Add Custom Item</button>
       
        </div>
        <div id="searchResults_${floorCount}"></div>
        <table class="table invoice-items-table items" id="itemTable_${floorCount}">
            <thead style="background: #e8e8e8">
                <tr>
                    <th >Item Name</th>
                    <th width="200px">Description</th>
                    <th width="100px">Qty</th>
                    <th width="150px">Price</th>
                    <th width="70px">Unit </th>
                    <th width="150px">Total</th>
                    <th width="100px">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
           <tfoot>
 
  <tr>
    <td colspan="5" class="text-right text-primary"><h5><strong>Final Floor Total:</strong></h5></td>
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
   // $("#addFloor").trigger("click");
    $(".price").trigger("click");
});
</script>
 
<div class="floor-area">
    <div id="floors-container"></div>
    <div class="floor_total">
    <button type="button" id="addFloor" class="btn btn-primary addFloor" >➕ Add Floor</button>
   <div class="grand-total-box text-end mt-3 btn-bottom-toolbar text-right form-group">
    <div id="floor-totals-list" class="mt-2 text-start floor-totals"></div>
 
 
    <h4><label style="width:200px" class="bold text-danger">Grand Total: </label>
    <input type="text" id="grand-total" class="form-control d-inline-block" style="width:300px;" readonly>
  </h4>
  <h4><label style="width:200px" class="bold text-danger">Site Handling Charges (%):</label>
        <input type="number" id="siteHandling" class="form-control d-inline-block" style="width:100px;"   value="<?= $proposals_info->siteHandling?>" min="0" max="7">
           <input type="text" id="siteHandlingAmount" class="form-control" value="<?= $proposals_info->siteHandlingAmount?>" style="width:200px;" readonly>
  </h4> 
  <h4><label style="width:200px" class="bold text-danger">Paid Amount: </label>
    <input type="text" id="totalToBePaid" class="form-control d-inline-block" style="width:300px;" value="<?= $proposals_info->totalToBePaid?>" readonly>
    
  </h4>

  
</div>

</div>
</div>

<div class="btn-bottom-toolbar text-right">
  <button type="button" id="checkproposal" class="btn btn-warning m-2">
    Check
  </button>
  <button type="button" id="saveProposalBtn" class="btn btn-primary m-2" style="display:none">
    Save Proposal
  </button>
</div>

 
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
 
$("#phone").trigger("keyup");
    calculateGrandTotal();
    init_items_sortable();
    ins_data(base_url + 'admin/proposals/proposals_state_report')
});
 
 
 $('#checkproposal').on('click', function() {
    $('#saveProposalBtn').show();
  });

   $(document).on("input", "#siteHandling", function(){
        $('#saveProposalBtn').hide();
    });

 // Add blank custom item row — uses category selected in the floor's mainCategory select
$(document).on("click", ".addCustomItem", function(){
     $('#saveProposalBtn').hide();
    let floorId = $(this).data("floor");
    let $mainSelect = $("#mainCategory_" + floorId);
    let $locationSelect = $("#location_" + floorId);

    // Get selected category and location data
    let selectedCatId = $mainSelect.val();
    let categoryName = $mainSelect.find("option:selected").text().trim();
    let selectedLocationId = $locationSelect.val();
    let locationName = $locationSelect.find("option:selected").text().trim();

    // Validate that a category and location have been selected
    if (!selectedCatId) {
        alert("Please select a category from the dropdown before adding a custom item.");
        $mainSelect.focus();
        return;
    }
    if (!selectedLocationId) {
        alert("Please select a location from the dropdown before adding a custom item.");
        $locationSelect.focus();
        return;
    }

    // Ensure the necessary headers are in place (Category and Location)
    // This function will only create them if they are not already present.
    ensureCustomCategorySection(floorId, selectedCatId, categoryName, selectedLocationId, locationName);

    // Find the last item in the specific location to insert the new item after it.
    let $lastItemInLocation = $(`#itemTable_${floorId} tbody tr[data-location-id='${selectedLocationId}']`)
        .not('.location-row, .category-row, .category-subtotal-row, .category-discount-row, .category-total-row')
        .last();

    let $insertionPoint;
    if ($lastItemInLocation.length > 0) {
        $insertionPoint = $lastItemInLocation;
    } else {
        // If no items exist, insert after the location header row
        $insertionPoint = $(`#itemTable_${floorId} tbody tr.location-row[data-location-id='${selectedLocationId}']`);
    }

    // Create the new row HTML
    let rowId = "custom_" + Date.now();
    let row = `
        <tr data-id="${rowId}" 
            data-floor="${floorId}" 
            data-category-id="${selectedCatId}" 
            data-location-id="${selectedLocationId}"
            data-category="${categoryName}" 
            data-location="${locationName}">
            <td>
                <input type="text" name="item_name[${floorId}][]" class="form-control" placeholder="Item Name">
                <div class="related-items"></div>
            </td>
            <td><input type="text" name="description[${floorId}][]" class="form-control" placeholder="Description"></td>
            <td><input type="number" name="qty[${floorId}][]" class="form-control qty" value="1" min="0"></td>
            <td><input type="text" name="price[${floorId}][]" class="form-control price" value="0" data-base="0"></td>
            <td><input type="text" name="unit_type[${floorId}][]" class="form-control unit_type" value="0"></td>
            <td><input type="text" name="total[${floorId}][]" class="form-control total" value="0" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        </tr>
    `;

    // Insert the new row at the correct insertion point
    $insertionPoint.after(row);
    
    // Calculate for new row
    let $newRow = $(`#itemTable_${floorId} tbody tr[data-id='${rowId}']`);
    calculateRowTotal($newRow);
});



$(document).on("change", ".relatedCheckbox", function(){
     $('#saveProposalBtn').hide();
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

 //   row.find(".total").val((baseTotal + extra).toFixed(2));
});



$(document).on("click", ".addItemBtn", function(){
     $('#saveProposalBtn').hide();
    let floorId = $(this).data("floor");
    let name = $(this).data("name");
    let desc = $(this).data("desc");
    let price = parseFloat($(this).data("price")) || 0;
    let category = $(this).data("category"); // make sure backend sends category
    let location = $(this).data("location"); // make sure backend sends category

    ensureCategorySection(floorId, category, location);

    let row = `
        <tr data-id="${$(this).data("id")}" data-floor="${floorId}" data-category="${category}"  data-location="${location}" >
            <td>${name}
                <input type="hidden" name="item_name[${floorId}][]" value="${name}">
                <div class="related-items"></div>
            </td>
            <td><input type="text" name="description[${floorId}][]" class="form-control" value="${desc}"></td>
            <td><input type="number" name="qty[${floorId}][]" class="form-control qty" value="1" min="0"></td>
            <td><input type="text" name="price[${floorId}][]" class="form-control price" value="${price}" data-base="${price}"></td>
            <td><input type="text" name="unit_type[${floorId}][]" class="form-control unit_type" value="0"></td>
            <td><input type="text" name="total[${floorId}][]" class="form-control total" value="${price.toFixed(2)}" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button>
              <button type="button" class="btn btn-primary btn-sm addRowAfter">+</button></td>
        </tr>
    `;
    
 let $locRow = $("#itemTable_" + floorId + " tbody tr.location-row[data-category='" + category + "'][data-location='" + location + "']");
if ($locRow.length) {
    $locRow.after(row);
} else {
    $("#itemTable_" + floorId + " tbody tr.category-row[data-category='" + category + "']").after(row);
}


        
        // Calculate totals right away
let $newRow = $("#itemTable_" + floorId + " tbody tr[data-id='" + $(this).data("id") + "']");
calculateRowTotal($newRow);

// ✅ Now update the category and floor totals
let $floor = $newRow.closest(".floor-section");
calculateCategoryTotals($floor);

});


$(document).on("click", ".addRowAfter", function(){
     $('#saveProposalBtn').hide();
    let $currentRow = $(this).closest("tr");
    let floorId = $currentRow.data("floor");
    let category = $currentRow.data("category");
    let location = $currentRow.data("location");

    let rowId = "custom_" + Date.now();
    let newRow = `
        <tr data-id="${rowId}" 
            data-floor="${floorId}" 
            data-category="${category}" 
            data-location="${location}">
            <td>
                <input type="text" name="item_name[${floorId}][]" class="form-control" placeholder="Item Name">
                <div class="related-items"></div>
            </td>
            <td><textarea type="text" name="description[${floorId}][]" class="form-control" placeholder="Description"></textarea></td>
            <td><input type="number" name="qty[${floorId}][]" class="form-control qty" value="1" min="0"></td>
            <td><input type="text" name="price[${floorId}][]" class="form-control price" value="0"></td>
            <td><input type="text" name="unit_type[${floorId}][]" class="form-control unit_type" value="0"></td>
            <td><input type="text" name="total[${floorId}][]" class="form-control total" value="0" readonly></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                <button type="button" class="btn btn-primary btn-sm addRowAfter">+</button>
            </td>
        </tr>
    `;

    // Insert after current row
    $currentRow.after(newRow);

    // Calculate totals for new row
    let $newRow = $(`#itemTable_${floorId} tbody tr[data-id='${rowId}']`);
        ($newRow);
});

function calculateRowTotal($row, e) {
    let qty = parseFloat($row.find(".qty").val()) || 0;
    let taxRate = parseFloat($row.find(".tax_rate").val()) || 0;

    let isCustom = ($row.data("id") + "").startsWith("custom_");

    let priceInput = $row.find(".price");
    let currentPrice = parseFloat(priceInput.val()) || 0;

    let triggeredByAddon =
        e && $(e.target).hasClass("relatedCheckbox");

    let triggeredByQty =
        e && $(e.target).hasClass("qty");

    let basePrice = 0;
    let addonTotal = 0;
    let finalUnitPrice = currentPrice;

    if (!isCustom) {

        // 🔥 ONLY when addon checkbox is clicked
        if (triggeredByAddon) {
            basePrice = parseFloat(
                priceInput.attr("data-edit-price")
            ) || 0;

            $row.find(".relatedCheckbox:checked").each(function () {
                addonTotal += parseFloat($(this).data("price")) || 0;
            });

            finalUnitPrice = basePrice + addonTotal;

            // ✅ update price ONLY here
            priceInput.val(finalUnitPrice.toFixed(2));
        }

        // ❌ Qty change should NOT recalc price
        // finalUnitPrice stays same
    }

    // ✅ Total always recalculated
    let subtotal = finalUnitPrice * qty;
    let taxAmount = (subtotal * taxRate) / 100;
    let total = subtotal + taxAmount;

    $row.find(".total").val(total.toFixed(2));

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
   //  $("#totalToBePaid").val(grandTotal.toFixed(2));
    $("#floor-totals-list").html(listHtml);
}

 $(document).on("input change", ".qty, .price, .tax_rate, .unit_type, .relatedCheckbox, .category-discount", function(){
     $('#saveProposalBtn').hide();
    let $row = $(this).closest("tr");
    let $floor = $(this).closest(".floor-section");

    // Always recalc category totals for this floor
    calculateCategoryTotals($floor);
	calculateFinalTotal();
});
    // Bind events
    // $(document).on("input change", ".qty, .price, .tax_rate, .unit_type, .relatedCheckbox", function(){
         // $('#saveProposalBtn').hide();
        // let $row = $(this).closest("tr");
        // calculateRowTotal($row);
		// calculateFinalTotal();
    // });
	
	$(document).on(  "input change",  ".qty, .price, .tax_rate, .unit_type, .relatedCheckbox",  function (e) {
    $('#saveProposalBtn').hide();
    let $row = $(this).closest("tr");

    calculateRowTotal($row, e);   // 👈 event pass
    calculateFinalTotal();
  }
);

    $(document).ready(function(){
    // Load subcategories dynamically
  $(document).on("change", ".mainCategory", function(){
     $('#saveProposalBtn').hide();
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
     $('#saveProposalBtn').hide();
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

               $.each(data, function(i, item) {
    let listItem = `
         <li class="available-item-li addItemBtn"
            data-floor="${floorId}"
            data-id="${item.saved_items_id}" 
            data-name="${item.item_name}" 
            data-desc="${(item.item_desc || '').replace(/"/g, '&quot;')}"
            data-price="${item.unit_cost}"
            data-category="${item.main_category_name}" 
            data-location="${item.location_name}">
            
            <span class="item-name">${item.item_name}</span>
            <span class="item-cost"> (${item.unit_cost} Rs)</span>
        </li>
    `;
    $("#availableItemsList_" + floorId).append(listItem);
});
            } else {
                $("#availableItemsSection_" + floorId).hide();
            }
        }
    });
});
});

   const IS_ADMIN = <?php echo !empty($is_admin) ? 1 : 0; ?>;
    const IS_REGIONAL_MANAGER = <?php echo !empty($is_regional_manager) ? 1 : 0; ?>;

    
 function ensureCategorySection(floorId, categoryName, locationName){
    let tbody = $("#itemTable_" + floorId + " tbody");

      let maxDiscount = 0;

if (categoryName === 'Electrical + Plumbing + Civil (EPC)') {

    if (IS_ADMIN == 1) {
        maxDiscount = 20;
    } 
    else if (IS_REGIONAL_MANAGER == 1) {
        maxDiscount = 10;
    } 
    else {
        maxDiscount = 8;
    }

} 
else if (categoryName === 'Wooden Work') {

    if (IS_ADMIN == 1) {
        maxDiscount = 40;
    } 
    else if (IS_REGIONAL_MANAGER == 1) {
        maxDiscount = 30;
    } 
    else {
        maxDiscount = 25;
    }
}
    // Ensure CATEGORY header exists
    let categoryRow = tbody.find(`tr.category-row[data-category='${categoryName}']`);
    if(categoryRow.length === 0){
        let row = `
            <tr class="category-row table-secondary" data-category="${categoryName}" >
                <td colspan="7"><strong>📂 ${categoryName}</strong></td>
            </tr>
            <tr class="category-subtotal-row table-light" data-category="${categoryName}">
                <td colspan="5" class="text-right"><strong>Sub Total:</strong></td>
                <td><input type="text" class="form-control category-subtotal" readonly></td>
                <td></td>
            </tr>
            <tr class="category-discount-row table-light" data-category="${categoryName}">
                <td colspan="5" class="text-right"><strong>Discount (%):</strong></td>
                <td>
                <input type="number" class="form-control category-discount" value="0"  data-max="${maxDiscount}" >
                 <small class="discount-error text-danger" style="display:none;"></small>
                 
                 </td>
                <td></td>
            </tr>
            <tr class="category-total-row table-light" data-category="${categoryName}">
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td><input type="text" class="form-control category-total" readonly></td>
                <td></td>
            </tr>
        `;
        tbody.append(row);
        categoryRow = tbody.find(`tr.category-row[data-category='${categoryName}']`);
    }

    // Ensure LOCATION row exists
    let locationRow = tbody.find(`tr.location-row[data-category='${categoryName}'][data-location='${locationName}']`);
    if(locationRow.length === 0){
        let locRow = `
            <tr class="location-row table-info" data-category="${categoryName}" data-location="${locationName}">
                <td colspan="7"><strong>📍 ${locationName}</strong></td>
            </tr>
        `;
        // Insert location row just BEFORE subtotal row
        tbody.find(`.category-subtotal-row[data-category='${categoryName}']`).before(locRow);
    }
}



 $(document).on('blur keydown', '.category-discount', function (e) {

    if (e.type === "keydown" && e.key !== "Enter") return;

    validateDiscount($(this));
});

 
function validateDiscount(discountInput) {

    let enteredValue = parseFloat(discountInput.val()) || 0;
    let maxValue = parseFloat(discountInput.data("max")) || 0;

    let errorElement = discountInput.siblings(".discount-error");

    if (enteredValue > maxValue) {

        errorElement
            .text("Maximum discount allowed is " + maxValue + "%")
            .show();

        discountInput.addClass("is-invalid");

        // 🔄 Auto Reset
        discountInput.val(maxValue);

        return false;
    }

    // ✅ Remove error if valid
    errorElement.hide();
    discountInput.removeClass("is-invalid");

    // ✅ Run your calculation
    calculateCategoryTotal();
}


function ensureCustomCategorySection(floorId, categoryId, categoryName, locationId, locationName){
    let tbody = $("#itemTable_" + floorId + " tbody");

    // 1. Ensure CATEGORY header exists
    let categoryRow = tbody.find(`tr.category-row[data-category-id='${categoryId}']`);
    if(categoryRow.length === 0){
        let row = `<tr class="category-row table-secondary" data-category-id="${categoryId}" data-category="${categoryName}"><td colspan="7"><strong>📂 ${categoryName}</strong></td></tr>`;
        tbody.append(row);
    }

    // 2. Ensure LOCATION row exists
    let locationRow = tbody.find(`tr.location-row[data-category-id='${categoryId}'][data-location-id='${locationId}']`);
    if(locationRow.length === 0){
        let locRow = `<tr class="location-row table-info" data-category-id="${categoryId}" data-location-id="${locationId}" data-category="${categoryName}" data-location="${locationName}"><td colspan="7"><strong>📍 ${locationName}</strong></td></tr>`;
        tbody.find(`.category-subtotal-row[data-category-id='${categoryId}']`).before(locRow);
    }

    // 3. Ensure Category totals rows exist
    let subtotalRow = tbody.find(`.category-subtotal-row[data-category-id='${categoryId}']`);
    if(subtotalRow.length === 0){
        let totalsRows = `
            <tr class="category-subtotal-row table-light" data-category-id="${categoryId}">
                <td colspan="5" class="text-right"><strong>Sub Total:</strong></td>
                <td><input type="text" class="form-control category-subtotal" readonly></td>
                <td></td>
            </tr>
            <tr class="category-discount-row table-light" data-category-id="${categoryId}">
                <td colspan="5" class="text-right"><strong>Discount (%):</strong></td>
                <td><input type="number" class="form-control category-discount" value="0" min="0"></td>
                <td></td>
            </tr>
            <tr class="category-total-row table-light" data-category-id="${categoryId}">
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td><input type="text" class="form-control category-total" readonly></td>
                <td></td>
            </tr>
        `;
        tbody.append(totalsRows);
    }
}


function calculateCategoryTotals($floorSection){
    let categories = {};

    // Sum item totals per category + location
    $floorSection.find("tbody tr").each(function(){
        let $tr = $(this);
        let category = $tr.data("category");
        let location = $tr.data("location");

        if(category && !$tr.hasClass("category-row") 
                   && !$tr.hasClass("category-subtotal-row") 
                   && !$tr.hasClass("category-discount-row") 
                   && !$tr.hasClass("category-total-row")
                   && !$tr.hasClass("location-row")){
            let total = parseFloat($tr.find(".total").val()) || 0;
            if(!categories[category]) categories[category] = {};
            if(!categories[category][location]) categories[category][location] = 0;
            categories[category][location] += total;
        }
    });

    // Update per-category subtotal/discount/total
    for(let cat in categories){
        let subtotal = 0;
        for(let loc in categories[cat]){
            subtotal += categories[cat][loc];
        }
        let $subtotalRow = $floorSection.find(`.category-subtotal-row[data-category='${cat}']`);
        let $discountRow = $floorSection.find(`.category-discount-row[data-category='${cat}']`);
        let $totalRow = $floorSection.find(`.category-total-row[data-category='${cat}']`);

        $subtotalRow.find(".category-subtotal").val(subtotal.toFixed(2));

        // let discount = parseFloat($discountRow.find(".category-discount").val()) || 0;
        // let total = subtotal - discount;
         let discountPercent = parseFloat($discountRow.find(".category-discount").val()) || 0;
let discount = (subtotal * discountPercent) / 100;
let total = subtotal - discount;

        if(total < 0) total = 0;

        $totalRow.find(".category-total").val(total.toFixed(2));
    }

    // Update floor total
    calculateFloorTotal($floorSection);
}

$(document).on("input change", ".category-discount", function(){
     $('#saveProposalBtn').hide();
    let $floor = $(this).closest(".floor-section");
    calculateCategoryTotals($floor);
});



function calculateFinalTotal() {
    let grandTotal = parseFloat($("#grand-total").val()) || 0;  
    let siteHandlingPercent = parseFloat($("#siteHandling").val()) || 0;

    if (siteHandlingPercent > 7) {
        siteHandlingPercent = 7; 
        $("#siteHandling").val(7);
    }

    let siteHandlingAmount = (grandTotal * siteHandlingPercent) / 100;
    $("#siteHandlingAmount").val(siteHandlingAmount.toFixed(2)); 

    let totalToBePaid = grandTotal + siteHandlingAmount;

    // console.log('grandTotal', grandTotal);
    // console.log('siteHandlingAmount', siteHandlingAmount);
    // console.log('totalToBePaid', totalToBePaid);

    $("#totalToBePaid").val(totalToBePaid.toFixed(2));
}

// Trigger on change
$(document).on("input", "#siteHandling", calculateFinalTotal);

// Also recalc when Grand Total changes
function updateGrandTotal(grandTotal) {
    $("#grand-total").val(grandTotal.toFixed(2));
    calculateFinalTotal();
    
}


</script>



<script>
$(document).ready(function(){

  $("#saveProposalBtn").on("click", function(e){
    e.preventDefault();

    
     let phone = $("#phone").val().trim();

     if (phone === "") {
      alert("Phone number should not be blank.");
      $("#phone").focus();
      return false;   
    }
     let lead_id = $("#lead_id").val().trim();

     if (lead_id === "") {
      alert("Lead should not be blank.");
      $("#lead_id").focus();
      return false;   
    }  

    let formData = {
      proposal_id: $("#proposal_id").val(),
	  client_id: $("#client_id").val(),
      checked_by: $("#checked_by").val(),
      ref_no: $("#ref_no").val(),
      lead_id: $("#lead_id").val(),
      phone: $("#phone").val(),
      proposal_date: $("#proposal_date").val(),
      expiry_date: $("#expiry_date").val(),
      notes: $("#notes").val(),
      grand_total: $("#grand-total").val(),
      
      siteHandling: $("#siteHandling").val(),
      siteHandlingAmount: $("#siteHandlingAmount").val(),
      totalToBePaid: $("#totalToBePaid").val(),
       exclusive_product: $("#exclusive_product").val(),
      status: $("#status").val(),
      floors: []
    };

    $(".floor-section").each(function(){
      let floor = {
    name: $(this).find("h4").text().replace("🏢", "").trim(), // clean name
       subtotal: null, // we don’t track floor subtotal separately
        discount: null, // no floor discount input
        total: $(this).find(".floor-final-total").val(),
        categories: []
      };

      $(this).find(".category-row").each(function(){
        let catName = $(this).data("category");
            let itemRow = $(this).nextUntil(".category-total-row").filter("tr[data-id]").first();
    let category_name_id = itemRow.data("category_name_id") || null;
        let category = {
          name: catName,
          category_name_id: category_name_id,
          subtotal: $(this).nextAll(".category-subtotal-row:first").find(".category-subtotal").val(),
          discount: $(this).nextAll(".category-discount-row:first").find(".category-discount").val(),
          total: $(this).nextAll(".category-total-row:first").find(".category-total").val(),
          items: []
        };

        $(this).nextUntil(".category-total-row").filter("tr[data-id]").each(function(){
          let item = {
            id: $(this).data("id"),
            name: $(this).find("input[name*='item_name']").val(),
            desc: $(this).find("textarea[name*='description']").val(),
            qty: $(this).find(".qty").val(),
            price: $(this).find(".price").val(),
            tax_rate: $(this).find(".tax_rate").val(),
            unit_type: $(this).find(".unit_type").val(),
            total: $(this).find(".total").val(),
            location: $(this).data("location"),
            location_name_id: $(this).data("location_name_id"),
            addons: []
          };

          $(this).find(".relatedCheckbox:checked").each(function(){
            item.addons.push({
              floorId: $(this).data("floor"),
              id: $(this).data("id"),
              main_item_id: $(this).data("main"),
              name: $(this).data("name"),
              price: $(this).data("price")
            });
          });

          category.items.push(item);
        });

        floor.categories.push(category);
      });

      formData.floors.push(floor);
    });
console.log(formData);
// return;
    // Send to controller
 $.post("<?= base_url('admin/proposals/save') ?>", formData, function(res){
  if(res.status){
    alert(res.message);
    window.location.href = res.redirect_url; // ✅ redirect via JS
  } else {
    alert("Something went wrong!");
  }
}, 'json');

  });

});
</script>


<script>
document.addEventListener('input', function (e) {
  if (e.target.classList.contains('category-discount') || e.target.classList.contains('qty')) {
    if (e.target.value < 0) e.target.value = 0;
  }
});

document.addEventListener('keydown', function (e) {
  if ((e.target.classList.contains('category-discount') || e.target.classList.contains('qty')) && e.key === '-') {
    e.preventDefault(); // block minus key
  }
});


 


</script>



         <style>
                    .input-wrapper {
                        position: relative;
                    }

                    .input-wrapper input {
                        padding-right: 30px;
                        /* space for tick */
                    }

                    .status-icon {
                        position: absolute;
                        right: 25px;
                        top: 50%;
                        transform: translateY(-50%);
                        font-size: 18px;
                        display: none;
                        color: #009650ff;
                        /* Bootstrap blue */
                    }
                    </style>

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
                        display: flex;
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
                         margin: 0;
                        margin-right: 17px;
                    }


                    .grand-total-box h4 label {

                        margin: 6px;

                    }

                    .floor_total {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 20px;
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

                    .floor-totals {
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
                        height: 30px;
                    }

                    .floor-area {
                        margin: 1em
                    }


                    /* Container for all items */
                    .item_list_class {
                        display: flex;
                        /* Enables flexbox layout */
                        flex-wrap: wrap;
                        /* Allows items to wrap to the next line */
                        gap: 10px;
                        /* Provides consistent spacing between items */
                        margin-top: 10px;
                        list-style: none;
                        margin-left: -20px;
                    }

                    /* Individual item styling */
                    .available-item-li {
                        background-color: #e8e8e8;
                        border: 1px solid #ccc;
                        padding: 8px 12px;
                        border-radius: 5px;
                        cursor: pointer;
                        transition: background-color 0.2s, box-shadow 0.2s;
                        /* This is the key part to make 3 items per row */
                        flex-basis: calc(33.333% - 10px);
                        /* Sets the base width for 3 items with a 10px gap */
                        text-align: center;
                        /* Centers the text within each item */
                    }

                    /* Optional: Add responsiveness for smaller screens */
                    @media (max-width: 768px) {
                        .available-item-li {
                            flex-basis: calc(50% - 10px);
                            /* 2 items per row on tablets */
                        }
                    }

                    @media (max-width: 480px) {
                        .available-item-li {
                            flex-basis: 100%;
                            /* 1 item per row on mobile phones */
                        }
                    }
					
					 .related-container {
                        max-height:300px;
                        overflow:auto;
                    }
                    </style>