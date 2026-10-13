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
<div class="row mb-lg">

    <!-- <div class="col-sm-2 ">
        <div class="pull-left pr-lg">
            <a href="<?= base_url() ?>admin/leads/create/<?= $text ?>" class="btn btn-xs btn-<?= $btn ?> pull-right"
                data-toggle="tooltip" data-placement="top" title="<?= lang('switch_to_kanban') ?>">
                <i class="fa fa-undo"> </i><?= ' ' . lang('switch_to_' . $text) ?>
            </a>
        </div>
    </div> -->
    <?php if ($text == 'kanban') {
        $type = $this->uri->segment(4);
        $id = $this->uri->segment(5);
    ?>
    <?php } ?>
</div>
<div class="row">
    <div class="col-sm-12">

        <?php if ($k_leads == 'kanban') { ?>
        <link rel="stylesheet" href="<?= base_url() ?>assets/plugins/kanban/kan-app.css" />
        <div class="app-wrapper">
            <p class="total-card-counter" id="totalCards"></p>
            <div class="board" id="board"></div>
        </div>
        <?php include_once 'assets/plugins/kanban/leads_kan-app.php'; ?>
        <?php } else { ?>
        <?php if (!empty($created) || !empty($edited)) {
            ?>

            <?php
$user_id = $this->session->userdata('user_id');
$this->db->where('user_id', $user_id);
$current_user = $this->db->get('tbl_account_details')->row();

$is_team_leader = !empty($current_user) && $current_user->team_leader == 1;
$is_admin = $this->session->userdata('user_type') == 1;
?>
     <?php if ($is_admin || $is_team_leader) { ?>
        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu group animated zoomIn" style="width:300px;">
                <li class="filter_by all_filter" id="all"><a href="#"><?php echo lang('all'); ?></a></li>
                <li class="divider"></li>



                <li class="dropdown-submenu pull-left" id="budget_filter">
    <a href="#" tabindex="-1"><?= lang('budget') ?></a>
    <ul class="dropdown-menu dropdown-menu-left" style="padding:10px; width:250px;">
        <li>
            <label><?= lang('budget_from') ?> (₹)</label>
            <input type="number" class="form-control input-sm filter_budget" id="filter_budget_from"
                placeholder="<?= lang('budget_from') ?>">
        </li>
        <li class="mt-2">
            <label><?= lang('budget_to') ?> (₹)</label>
            <input type="number" class="form-control input-sm filter_budget" id="filter_budget_to"
                placeholder="<?= lang('budget_to') ?>">
        </li>
        <li class="mt-2 text-right">
            <button class="btn btn-xs btn-primary" id="apply_budget_filter"><?= lang('apply') ?></button>
        </li>
    </ul>
</li>
           <div class="clearfix"></div>
<li class="dropdown-submenu pull-left" id="created_time_filter">
    <a href="#" tabindex="-1"><?= lang('Date ') ?></a>
    <ul class="dropdown-menu dropdown-menu-left" style="padding:0; width:200px;">
        <li>
            <label><?= lang('from_date') ?></label>
           <input class="form-control datepicker" type="text"  placeholder="<?= lang('Start Date') ?>"  id="filter_from_date"
              data-date-format="<?= config_item('date_picker_format'); ?>" style='width:200px'>
        </li>
        <li class="mt-2">
            <label><?= lang('to_date') ?></label>
            <input type="text" class="form-control datepicker " style='width:200px' id="filter_to_date"
                   placeholder="<?= lang('End Date') ?>"   data-date-format="<?= config_item('date_picker_format'); ?>">
        </li>
        <li class="mt-2 text-right">
            <button class="btn btn-xs btn-primary" id="apply_date_filter"><?= lang('apply') ?></button>
        </li>
    </ul>
</li>
          <div class="clearfix"></div>

                <li class="filter_by" id="converted"><a href="#"><?php echo lang('converted'); ?></a></li>
                                <li class="filter_by" id="assigned_to_me"><a href="#"><?php echo lang('assigned_to_me'); ?></a></li>

                <?php if (admin()) { ?>
                <li class="filter_by" id="everyone">
                    <a href="#"><?php echo lang('assigned_to') . ' ' . lang('everyone'); ?></a>
                </li>
                
                <?php } ?>

               <?php if ($is_admin) { ?>
                <li class="dropdown-submenu pull-left  " id="from_account">
                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('source'); ?></a>
                    <ul class="dropdown-menu dropdown-menu-left from_account" style="">
                        <?php
                                    $asource_info = $this->db->get('tbl_lead_source')->result();
                                    if (!empty($asource_info)) {
                                        foreach ($asource_info as $v_source) {
                                    ?>
                        <li class="filter_by" id="<?= $v_source->lead_source_id ?>" search-type="by_source">
                            <a href="#"><?php echo $v_source->lead_source; ?></a>
                        </li>
                        <?php }
                                    }
                                    ?>
                    </ul>
                </li>
                <?php } ?>
                <div class="clearfix"></div>

                <li class="dropdown-submenu pull-left" id="assigned_user_filter">
    <a href="#" tabindex="-1"><?= lang('assigned_to') ?></a>
    <ul class="dropdown-menu dropdown-menu-left assigned_user" style="">
        <?php
        $assign_users = $this->db->select('user_id,  fullname')
            ->from('tbl_account_details')
            ->get()->result();

        if (!empty($assign_users)) {
            foreach ($assign_users as $user) {
                ?>
                <li class="filter_by" id="<?= $user->user_id ?>" search-type="by_assigned">
                    <a href="#"><?= !empty($user->fullname) ? $user->fullname : ''; ?></a>
                </li>
            <?php }
        } ?>
    </ul>
</li>
    <div class="clearfix"></div>

                <li class="dropdown-submenu pull-left " id="to_account">
                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('status'); ?></a>
                    <ul class="dropdown-menu dropdown-menu-left to_account" style="">
                        <?php
                                    $astatus_info = $this->db->get('tbl_lead_status')->result();
                                    if (!empty($astatus_info)) {
                                        foreach ($astatus_info as $v_status) {
                                    ?>
                        <li class="filter_by" id="<?= $v_status->lead_status_id ?>" search-type="by_status">
                            <a href="#"><?php echo $v_status->lead_status; ?></a>
                        </li>
                        <?php }
                                        ?>
                        <div class="clearfix"></div>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    <?php } else { ?> 
     <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu group animated zoomIn" style="width:300px;">
                <li class="filter_by all_filter" id="all"><a href="#"><?php echo lang('all'); ?></a></li>
                <li class="divider"></li>



   
           <div class="clearfix"></div>
<li class="dropdown-submenu pull-left" id="created_time_filter">
    <a href="#" tabindex="-1"><?= lang('Date ') ?></a>
    <ul class="dropdown-menu dropdown-menu-left" style="padding:0; width:200px;">
        <li>
            <label><?= lang('from_date') ?></label>
           <input class="form-control datepicker" type="text"  placeholder="<?= lang('Start Date') ?>"  id="filter_from_date"
              data-date-format="<?= config_item('date_picker_format'); ?>" style='width:200px'>
        </li>
        <li class="mt-2">
            <label><?= lang('to_date') ?></label>
            <input type="text" class="form-control datepicker " style='width:200px' id="filter_to_date"
                   placeholder="<?= lang('End Date') ?>"   data-date-format="<?= config_item('date_picker_format'); ?>">
        </li>
        <li class="mt-2 text-right">
            <button class="btn btn-xs btn-primary" id="apply_date_filter"><?= lang('apply') ?></button>
        </li>
    </ul>
</li>
         
             
          
    <div class="clearfix"></div>

                <li class="dropdown-submenu pull-left " id="to_account">
                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('status'); ?></a>
                    <ul class="dropdown-menu dropdown-menu-left to_account" style="">
                        <?php
                                    $astatus_info = $this->db->get('tbl_lead_status')->result();
                                    if (!empty($astatus_info)) {
                                        foreach ($astatus_info as $v_status) {
                                    ?>
                        <li class="filter_by" id="<?= $v_status->lead_status_id ?>" search-type="by_status">
                            <a href="#"><?php echo $v_status->lead_status; ?></a>
                        </li>
                        <?php }
                                        ?>
                        <div class="clearfix"></div>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>

        <?php } ?>
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/leads') ?>"><?= lang('all_leads') ?></a>
                </li>
                <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/leads/create') ?>"><?= lang('new_leads') ?></a>
                </li>
                <li><a class="import" href="<?= base_url() ?>admin/leads/import_leads"><?= lang('import_leads') ?></a>
                </li>
                 <li><a class="import" href="<?= base_url() ?>admin/leads/call_logs"><?= lang('Call Recording') ?></a>
                </li>
            </ul>
            <div class="tab-content bg-white">
                <!-- ************** general *************-->
                <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
                    <?php } else { ?>
                    <div class="panel panel-custom">
                        <header class="panel-heading ">
                            <div class="panel-title"><strong><?= lang('all_leads') ?></strong></div>
                        </header>
                        <?php } ?>
                        <style type="text/css">
                        .custom-bulk-button {
                            display: initial;
                        }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-striped DataTables bulk_table" id="DataTables" cellspacing="0"
                                width="100%">
                                <thead>
                                    <tr>
                                        <?php if (!empty($deleted)) { ?>
                                        <th data-orderable="false">
                                            <div class="checkbox c-checkbox">
                                                <label class="needsclick">
                                                    <input id="select_all" type="checkbox">
                                                    <span class="fa fa-check"></span></label>
                                            </div>
                                        </th>
                                        <?php } ?>
                                        <!-- <th><?= lang('lead_name') ?></th> -->
                                        <th><?= lang('contact_name') ?></th>
                                        <th><?= lang('email') ?></th>
                                        <th><?= lang('phone') ?></th>
                                        <th><?= lang('city') ?></th>
                                        <!-- <th><?= lang('tags') ?></th> -->
                                     <?php if ($is_admin): ?>
    <th><?= lang('lead_source') ?></th>
<?php endif; ?>
                                        <th><?= lang('status') ?></th>
                                        <th><?= lang('last_contact') ?></th>
                                        <th><?= lang('Budget') ?></th>
                                        <th><?= lang('Follow Date ') ?></th>
                                        <th><?= lang('assigned_to') ?></th>
                                        <?php $show_custom_fields = custom_form_table(5, null);
                                            if (!empty($show_custom_fields)) {
                                                foreach ($show_custom_fields as $c_label => $v_fields) {
                                                    if (!empty($c_label)) {
                                            ?>
                                        <th><?= $c_label ?> </th>
                                        <?php }
                                                }
                                            }
                                            ?>
                                        <th class="col-options no-sort"><?= lang('action') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <script type="text/javascript">
                                $(document).ready(function() {
                                    list = base_url + "admin/leads/leadList";
                                    bulk_url = base_url + "admin/leads/bulk_delete";
                                
                                    $('.filtered > .dropdown-toggle').on('click', function() {
                                        if ($('.group').css('display') == 'block') {
                                            $('.group').css('display', 'none');
                                        } else {
                                            $('.group').css('display', 'block')
                                        }
                                    });
                                    $('.all_filter').on('click', function() {
                                        $('.to_account').removeAttr("style");
                                        $('.from_account').removeAttr("style");
                                    });
                                    $('.from_account li').on('click', function() {
                                        if ($('.to_account').css('display') == 'block') {
                                            $('.to_account').removeAttr("style");
                                            $('.from_account').css('display', 'block');
                                        } else {
                                            $('.from_account').css('display', 'block')
                                        }
                                    });

                                    $('.to_account li').on('click', function() {
                                        if ($('.from_account').css('display') == 'block') {
                                            $('.from_account').removeAttr("style");
                                            $('.to_account').css('display', 'block');
                                        } else {
                                            $('.to_account').css('display', 'block');
                                        }
                                    });
                                    $('.filter_by').on('click', function() {
                                        $('.filter_by').removeClass('active');
                                        $('.group').css('display', 'block');
                                        $(this).addClass('active');
                                        var filter_by = $(this).attr('id');
                                        if (filter_by) {
                                            filter_by = filter_by;
                                        } else {
                                            filter_by = '';
                                        }
                                        var search_type = $(this).attr('search-type');
                                        if (search_type) {
                                            search_type = '/' + search_type;
                                        } else {
                                            search_type = '';
                                        }

                                       
                                        table_url(base_url + "admin/leads/leadList/" + filter_by +
                                            search_type);
                                    });
                                    
                                });
                                </script>
                            </table>
                        </div>
                    </div>



                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <link rel="stylesheet" href="<?= base_url() ?>assets/plugins/datetimepicker/jquery.datetimepicker.min.css">
    <?php include_once 'assets/plugins/datetimepicker/jquery.datetimepicker.full.php'; ?>
    <script type="text/javascript">
    init_datepicker();

    // Date picker init with selected timeformat from settings
    function init_datepicker() {
        var datetimepickers = $('.datetimepicker');
        if (datetimepickers.length == 0) {
            return;
        }
        var opt_time;
        // Datepicker with time
        $.each(datetimepickers, function() {
            opt_time = {
                lazyInit: true,
                scrollInput: false,
                format: 'Y-m-d H:i',
                autoclose: true,
                endDate: "today",
                maxDate: "today"
            };

            opt_time.formatTime = 'H:i';
            // Check in case the input have date-end-date or date-min-date
            var max_date = $(this).data('date-end-date');
            var min_date = $(this).data('date-min-date');
            if (max_date) {
                opt_time.maxDate = max_date;
            }
            if (min_date) {
                opt_time.minDate = min_date;
            }
            // Init the picker
            $(this).datetimepicker(opt_time);
        });
    }

// $(document).ready(function () {
    // var table_url = base_url + "admin/leads/leadList";

    // var leadsTable = $('#DataTables').DataTable({
        // processing: true,
        // serverSide: true,
        // ajax: {
            // url: table_url,
            // type: "POST"
        // }
    // });

    // $('#apply_budget_filter, #apply_date_filter').on('click', function () {

        // var from_date = $('#filter_from_date').val();
    // var to_date = $('#filter_to_date').val();

    
        // var budget_from = $('#filter_budget_from').val();
        // var budget_to = $('#filter_budget_to').val();

    //     Rebuild the DataTable's ajax URL with new query params
    //    var new_url = table_url + "?budget_from=" + budget_from + "&budget_to=" + budget_to;
         // var new_url = table_url + "?from_date=" + encodeURIComponent(from_date) + 
                  // "&to_date=" + encodeURIComponent(to_date) +
                  // "&budget_from=" + encodeURIComponent(budget_from) +
                  // "&budget_to=" + encodeURIComponent(budget_to);
        // leadsTable.ajax.url(new_url).load();
    // });
// });

//     $(document).on('click', '#apply_date_filter', function () {
//     console.log("FROM:", $('#filter_from_date').val());
//     console.log("TO:", $('#filter_to_date').val());

//     leadsTable.ajax.reload();
// });

  $(document).ready(function () {
        var table_url = base_url + "admin/leads/leadList";

        var leadsTable = $('#DataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: table_url,
                type: "POST",
                data: function (d) {
                    d.budget_from = $('#filter_budget_from').val();
                    d.budget_to = $('#filter_budget_to').val();
                    d.from_date = $('#filter_from_date').val();
                    d.to_date = $('#filter_to_date').val();
                }
            },
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'print',
                    text: "<i class='fa fa-print'> </i>",
                    className: 'btn btn-danger btn-xs mr',
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"> </i>',
                    className: 'btn btn-purple mr btn-xs',
                },
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-excel-o"> </i>',
                    className: 'btn btn-primary mr btn-xs',
                },
            ],
            select: true,
            createdRow: function (row, data, dataIndex) {
                if (data['DT_RowClass'] === 'duplicate-row') {
                    $('td:eq(3)', row).css('color', 'red'); // Highlight phone column
                }
            }
        });

        // ✅ Apply filters
        $('#apply_budget_filter, #apply_date_filter').on('click', function () {
            leadsTable.ajax.reload(); // Uses POST data from .val() inputs
        });

        // 🔄 Optional Reset Filters
        $('#reset_filters').on('click', function () {
            $('#filter_budget_from, #filter_budget_to, #filter_from_date, #filter_to_date').val('');
            leadsTable.ajax.reload();
        });
    });


$('#DataTables').DataTable({
    // other options...
    createdRow: function (row, data, dataIndex) {
        // Get row's duplicate status
        var isDuplicate = $(row).data('is-duplicate');
        if (isDuplicate == 1) {
            // Make only the phone number column red (adjust index if needed)
            $('td:eq(3)', row).css('color', 'red');
        }
    }
});


    </script>




<style>
    tr.duplicate-row td {
    background: #ffbebe;
}
.dataTables_info {
display:none;
}
</style>

 
