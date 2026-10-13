<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>

<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('55', 'created');
$edited = can_action('55', 'edited');
$deleted = can_action('55', 'deleted');
$kanban = $this->session->userdata('leads_kanban');
$uri_segment = $this->uri->segment(4);
if (!empty($kanban)) {
    $k_leads = 'kanban';
} elseif ($uri_segment == 'kanban') {
    $k_leads = 'kanban';
} else {
    $k_leads = 'list';
}

if ($k_leads == 'kanban') {
    $text = 'list';
    $btn = 'purple';
} else {
    $text = 'kanban';
    $btn = 'danger';
}
?>
 
<div class="row">
    <div class="col-sm-12">


        <?php if (!empty($created) || !empty($edited)) {
            ?>
        <?php $is_department_head = is_department_head();
         if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) { ?>

        <?php } ?>
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/items/associated_items') ?>"><?= lang('Associated Items') ?></a>
                </li>
                <!-- <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/leads/reminder_list') ?>"><?= lang('All Reminder') ?></a>
                </li> -->
                
            </ul>
            <div class="tab-content bg-white">
                <!-- ************** general *************-->
                <div class="tab-pane <?= $active == 2 ? 'active' : ''; ?>" id="manage">
                    <?php } else { ?>
                    <div class="panel panel-custom">
                        <header class="panel-heading ">
                            <div class="panel-title"><strong><?= lang('assocaited items') ?></strong></div>
                        </header>
                        <?php } ?>
                        <style type="text/css">
                        .custom-bulk-button {
                            display: initial;
                        }
                        </style>
                           <!-- <form method="get" action="">
<div class="row">
<div class="col-lg-4">
    <div style="display:flex">
    <label for="filter_date">Filter by Date:</label>
    <input type="date" class="form-control" name="filter_date" id="filter_date" value="<?= isset($_GET['filter_date']) ? $_GET['filter_date'] : '' ?>">
                    </div>

    </div>
    <div class="col-lg-2">
         <button class="btn btn-success" type="submit">Filter</button>
    <?php if (isset($_GET['filter_date'])): ?>
        <a class="btn btn-primary" href="<?= base_url('admin/leads/reminder_list') ?>">Clear</a>
    <?php endif; ?>
    </div>
    </div>
    </form> -->
<br>

                        <div class="table-responsive">
        <table class="table table-striped dataTable DataTables bulk_table"   cellspacing="0" width="100%" cellpadding="5"  >
    <thead>
        <tr>
            <th>#</th>
            <th>Items</th>
            <th>Item Type</th>
            <th>Linked Items</th>
    
            <th>Action </th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($item_list)): ?>
            <?php foreach ($item_list as $key => $item): ?>
                <tr>
                    <td><?= $key + 1 ?></td>
                  <td><?= $item->item_name ?></td>
                  <td><?= $item->product_type ?></td>
                  <td> 

   
  <?php 
    // Split linked items by comma
    $associatedItems = !empty($item->linked_item) ? explode(",", $item->linked_item) : []; 
  ?>

  <?php if (!empty($associatedItems)): ?>
    <?php foreach ($associatedItems as $sub): ?>
      <span style="
          display:inline-block;
          background:#eaf2ff;
          color:#333;
          border:1px solid #b5c8ff;
          border-radius:12px;
          padding:4px 10px;
          margin:2px;
          font-size:13px;
          font-weight:500;
      ">
        <?= trim($sub) ?>
      </span>
    <?php endforeach; ?>
  <?php else: ?>
    <em>No Linked Items</em>
  <?php endif; ?>
 

                </td>
                     
                    
                  
                   <td>
<a href="javascript:void(0)" 
   onclick="openSubProductModal(<?= $item->saved_items_id ?>)" 
   class="btn btn-xs btn-info">
   Associate Items
</a>
<a href="javascript:void(0)" 
   onclick="confirmDelete(<?= $item->saved_items_id ?>)" 
   class="btn btn-xs btn-danger" 
   data-toggle="tooltip" 
   data-placement="top" 
   title="Delete Item">
   <i class="fa fa-trash-o"></i>
</a> 
</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="11">No reminders found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


    <div class="modal fade" id="SubproductModal" tabindex="-1" role="dialog" aria-labelledby="productlistmodal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="productlistmodal">Sub Product List</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="statusLogContent">
   
        <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
      </div>
    </div>
  </div>
</div>



    <link rel="stylesheet" href="<?= base_url() ?>assets/plugins/datetimepicker/jquery.datetimepicker.min.css">
    <?php include_once 'assets/plugins/datetimepicker/jquery.datetimepicker.full.php'; ?>
    <script>
  function confirmDelete(assoc_id) {
    alert(assoc_id);
    if (confirm("Are you sure you want to delete this associated item?")) {
        window.location.href = "<?= base_url('admin/items/associated_deleted/') ?>" + assoc_id;
    }
}


    function openSubProductModal(main_id) {
    $('#SubproductModal').modal('show');
    $("#statusLogContent").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

    $.ajax({
        url: "<?= base_url('admin/items/get_sub_products/') ?>" + main_id,
        type: "GET",
        success: function(res) {
            $("#statusLogContent").html(res);
        }
    });
}

</script>


