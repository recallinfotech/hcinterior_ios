<form name="myform" role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
    action="<?php echo base_url(); ?>admin/proposals/save_proposals/<?php
        if (!empty($proposals_info)) {
            echo $proposals_info->id; // ✅ use correct primary key
        }
    ?>" method="post" class="form-horizontal">

    <script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.js"></script>
    <?php include_once 'assets/admin-ajax.php'; ?>
    <?php include_once 'assets/js/sales.php'; ?>
    <?= message_box('success'); ?>
    <?= message_box('error'); ?>

    <?php
    $created = can_action('140', 'created');
    $edited  = can_action('140', 'edited');
    $deleted = can_action('140', 'deleted');

    if (!empty($created) || !empty($edited)) {
        $curency = $this->proposal_model->check_by(['code' => config_item('default_currency')], 'tbl_currencies');
        $type    = $this->uri->segment(5);
        if (empty($type)) {
            $type = '_' . date('Y');
        }
    ?>

    <!-- Filter Section -->
    <div class="btn-group mb-lg pull-left mr">
        <button class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-search"></i> <?= lang('filter_by'); ?>
            <span id="showed_result">
                <?php
                if (!empty($type) && !is_numeric($type)) {
                    $ex = explode('_', $type);
                    if (!empty($ex[1]) && is_numeric($ex[1])) {
                        echo ' : ' . $ex[1];
                    } else {
                        echo ' : ' . lang($type);
                    }
                }
                ?>
            </span>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu animated zoomIn">
            <li id="all" class="filter_by_type" search-type="<?= lang('all'); ?>">
                <a href="#"><?= lang('all'); ?></a>
            </li>
            <?php
            $invoiceFilter = $this->proposal_model->get_invoice_filter();
            if (!empty($invoiceFilter)) {
                foreach ($invoiceFilter as $v_Filter) { ?>
                    <li class="filter_by_type" search-type="<?= $v_Filter['name'] ?>" id="<?= $v_Filter['value'] ?>">
                        <a href="#"><?= $v_Filter['name'] ?></a>
                    </li>
            <?php }
            } ?>
        </ul>
    </div>

    <!-- Quick State (admin only) -->
    <?php if ($this->session->userdata('user_type') == 1) {
        $type = 'proposal';
        $h_s = '';
                if ($h_s == 'block') {
            $title = lang('hide_quick_state');
            $url   = 'hide';
            $icon  = 'fa fa-eye-slash';
        } else {
            $title = lang('view_quick_state');
            $url   = 'show';
            $icon  = 'fa fa-eye';
        }
    ?>
    <div onclick="slideToggle('#state_report')" id="quick_state" data-toggle="tooltip" title="<?= $title ?>" class="btn-xs btn btn-purple pull-left">
        <i class="fa fa-bar-chart"></i>
    </div>
    <div class="btn-xs btn btn-white pull-left ml">
        <a class="text-dark" id="change_report"
            href="<?= base_url() ?>admin/dashboard/change_report/<?= $url . '/' . $type ?>">
            <i class="<?= $icon ?>"></i>
            <span><?= ' ' . lang('quick_state') . ' ' . lang($url) . ' ' . lang('always') ?></span>
        </a>
    </div>
    <?php } ?>

    <a data-toggle="modal" data-target="#myModal" href="<?= base_url() ?>admin/invoice/zipped/proposal"
        class="btn btn-success btn-xs ml-lg"><?= lang('zip_proposal') ?></a>

    <div class="row">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="<?= base_url('admin/proposals') ?>"><?= lang('all_proposals') ?></a></li>
                    <li><a href="<?= base_url('admin/proposals/createnewproposal') ?>"><?= lang('create_proposal') ?></a></li>
                </ul>

                <div class="tab-content bg-white">
                    <div class="tab-pane active" id="manage">

                        <div class="table-responsive">
                            <table class="table table-striped DataTables" id="DataTables" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><?= lang('proposal') ?> #</th>
                                        <th><?= lang('proposal_date') ?></th>
                                       <!-- <th><?= lang('expire_date') ?></th>-->
                                        <th><?= strtoupper(lang('TO')) ?></th>
                                
                                        <th><?= lang('status') ?></th>
                                                <th>Version</th>
                                        <?php
                                        $show_custom_fields = custom_form_table(11, null);
                                        if (!empty($show_custom_fields)) {
                                            foreach ($show_custom_fields as $c_label => $v_fields) {
                                                if (!empty($c_label)) { ?>
                                                    <th><?= $c_label ?></th>
                                        <?php   }
                                            }
                                        } ?>
                                        <?php if (!empty($edited) || !empty($deleted)) { ?>
                                            <th class="hidden-print"><?= lang('action') ?></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php } else { ?>
    <div class="panel panel-custom">
        <header class="panel-heading ">
            <div class="panel-title"><strong><?= lang('all_proposals') ?></strong></div>
        </header>
    </div>
    <?php } ?>
</form>

<script type="text/javascript">
function slideToggle($id) {
    $('#quick_state').attr('data-original-title', '<?= lang('view_quick_state') ?>');
    $($id).slideToggle("slow");
}

$(document).ready(function() {
    list = base_url + "admin/proposals/proposalsList";
    init_items_sortable();
    ins_data(base_url + 'admin/proposals/proposals_state_report');
});


 $(document).ready(function () {

  // Open modal and load data
  $(document).on('click', '.open-exclusive-modal', function () {
    var proposalId = $(this).data('proposal-id');
    $('#proposal_id').val(proposalId);
    $('#product_table_body').html('');

    // 🔹 Load existing products via AJAX
    $.ajax({
      url: base_url + 'admin/proposals/get_exclusive_products/' + proposalId,
      type: 'GET',
      dataType: 'json',
      success: function (products) {
        if (products.length > 0) {
          products.forEach(function (p) {
            addRow(p);
          });
        } else {
          addRow(); // empty row for new product
        }
      },
      error: function () {
        addRow();
      }
    });
  });

  // Add new product row
  $('#add_row').on('click', function () {
    addRow();
  });

  // Remove row
  $(document).on('click', '.remove-row', function () {
    $(this).closest('tr').remove();
  });

  // Auto calculate total
  $(document).on('input', '.area, .price', function () {
    var row = $(this).closest('tr');
    var area = parseFloat(row.find('.area').val()) || 0;
    var price = parseFloat(row.find('.price').val()) || 0;
    var total = area * price;
    row.find('.total').val(total.toFixed(2));
  });

  // Submit form
  $('#exclusive_product_form').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: $(this).serialize(),
      success: function (res) {
        try {
          var data = JSON.parse(res);
          if (data.success) {
            alert('Products saved successfully!');
            $('#exclusive_product').modal('hide');
          } else {
            alert('Error saving data');
          }
        } catch (e) {
          alert('Invalid response');
        }
      },
      error: function () {
        alert('Request failed!');
      }
    });
  });

  // Helper to add row (supports existing data)
  function addRow(data = {}) {
    var row = `
      <tr>
        <td>
          <input type="hidden" name="product_id[]" value="${data.id || ''}">
          <input type="text" name="item_name[]" class="form-control" value="${data.item_name || ''}" required>
        </td>
        <td><input type="text" name="description[]" class="form-control" value="${data.description || ''}"></td>
        <td><input type="number" name="area[]" class="form-control area" step="0.01" value="${data.area || ''}"></td>
        <td><input type="text" name="unit[]" class="form-control" value="${data.unit || ''}"></td>
        <td><input type="number" name="price[]" class="form-control price" step="0.01" value="${data.price || ''}"></td>
        <td><input type="number" name="total[]" class="form-control total" step="0.01" value="${data.total || ''}" readonly></td>
        <td><input type="text" name="brand[]" class="form-control" value="${data.brand || ''}"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
      </tr>`;
    $('#product_table_body').append(row);
  }
});


 

$(document).on('click', '.remove-row', function () {
  var id = $(this).closest('tr').find('input[name="product_id[]"]').val();
  if (id) {
    let deleted = $('#deleted_ids').val();
    $('#deleted_ids').val(deleted ? deleted + ',' + id : id);
  }
  $(this).closest('tr').remove();
});


</script>


 <div class="modal fade" id="exclusive_product" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="exclusive_product_form" method="post" action="<?= base_url('admin/proposals/save_exclusive_products') ?>" class="form-horizontal">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
          <h4 class="modal-title">Add Exclusion Product</h4>
        </div>

        <div class="modal-body">
          <input type="hidden" name="proposal_id" id="proposal_id">
          <input type="hidden" name="deleted_ids" id="deleted_ids">
          <table class="table table-bordered" id="product_table">
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Description</th>
                <th>Area</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Total</th>
                <th>Brand</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="product_table_body">
              <tr>
                <td><input type="text" name="item_name[]" class="form-control" required></td>
                <td><input type="text" name="description[]" class="form-control"></td>
                <td><input type="number" name="area[]" class="form-control area" step="0.01"></td>
                <td><input type="text" name="unit[]" class="form-control"></td>
                <td><input type="number" name="price[]" class="form-control price" step="0.01"></td>
                <td><input type="number" name="total[]" class="form-control total" step="0.01" readonly></td>
                <td><input type="text" name="brand[]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
              </tr>
            </tbody>
          </table>

          <button type="button" id="add_row" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add Row</button>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
          <button type="submit" class="btn btn-primary">Save Products</button>
        </div>
      </form>
    </div>
  </div>
</div>

