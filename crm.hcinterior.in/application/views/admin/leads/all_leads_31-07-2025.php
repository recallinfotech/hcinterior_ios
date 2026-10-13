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
$is_regional_manager = !empty($current_user) && $current_user->regional_manager == 1;
$is_admin = $this->session->userdata('user_type') == 1;
?>
        <?php if ($is_admin || $is_team_leader || $is_regional_manager) { ?>

        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i><?= lang('Sub Status') ?>
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px; padding:10px;">

                <div class="form-group">
                    <label for="department_filter">Sub Status </label>


                    <select class="form-control" name="sub_status" id="sub_status_filter">

                        <option value="">Select Sub Status </option>
                        <option value="Meeting Done">Meeting Done </option>
                        <option value="Lost">Lost </option>
                        <option value="Nap">Nap </option>
                        <option value="Won">Won </option>
                        <option value="Lost after meeting done">Lost after meeting done </option>
                        <option value="Special">Special </option>
                    </select>

                </div>


            </div>
        </div>


        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i><?= lang('Lead Status') ?>
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px; padding:10px;">

                <div class="form-group">
                    <label for="department_filter">Lead Status </label>
                    <?php
 
$CI =& get_instance();
$CI->load->database();

 
$lead_status_list = $CI->db
    ->order_by('order_no', 'ASC')
    ->get('tbl_lead_status')
    ->result();
?>

                    <select class="form-control" name="lead_status" id="lead_status_filter">
                        <option value="">Select Lead Status</option>
                        <?php foreach ($lead_status_list as $status): ?>
                        <option value="<?= htmlspecialchars($status->lead_status_id) ?>">
                            <?= htmlspecialchars($status->lead_status) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                </div>


            </div>
        </div>

        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i><?= lang('Team Wise') ?>
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px; padding:10px;">

                <div class="form-group">
                    <label for="department_filter">Team</label>
                    <select class="form-control" id="department_filter" name="department_id">
                        <option value="">-- Select Team --</option>
                        <!-- Options will be added via JS -->
                    </select>
                </div>


            </div>
        </div>




        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i><?= lang('assigned_to') ?>
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px; padding:10px;">



                <div class="form-group">
                    <label>Department</label>
                    <select class="form-control" id="department_select">
                        <option value="">Select Department</option>
                        <?php
        $departments = $this->db->get('tbl_departments')->result();
        foreach ($departments as $dept) {
            echo "<option value='{$dept->departments_id}'>{$dept->deptname}</option>";
        }
        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>User</label>
                    <select class="form-control" id="user_select">
                        <option value="">Select User</option>
                    </select>
                </div>




            </div>
        </div>





        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i> Budget Filter
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px;">
                <ul class="dropdown-menu-left" style="padding:10px;   list-style: none;" id="budget_filter">
                    <li>
                        <label><?= lang('Budget from') ?> (₹)</label>
                        <input type="number" class="form-control input-sm filter_budget" id="filter_budget_from">
                    </li>
                    <li class="mt-2">
                        <label><?= lang('Budget to') ?> (₹)</label>
                        <input type="number" class="form-control input-sm filter_budget" id="filter_budget_to">
                    </li>
                    <li class="mt-2 text-right">
                        <button class="  btn btn-sm btn-primary" id="apply_budget_filter"><?= lang('apply') ?></button>
                    </li>
                </ul>

            </div>
        </div>



        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i> Date Filter
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px;">
                <ul class="dropdown-menu-left" style="padding:10px;   list-style: none;" id="created_time_filter">


                    <li>
                        <label><?= lang('from_date') ?></label>
                        <input class="form-control datepicker" type="text" placeholder="<?= lang('Start Date') ?>"
                            id="filter_from_date" data-date-format="<?= config_item('date_picker_format'); ?>">
                    </li>
                    <li class="mt-2">
                        <label><?= lang('to_date') ?></label>
                        <input type="text" class="form-control datepicker " id="filter_to_date"
                            placeholder="<?= lang('End Date') ?>"
                            data-date-format="<?= config_item('date_picker_format'); ?>">
                    </li>
                    <li class="mt-2 text-right">
                        <button class="btn btn-sm btn-primary" id="apply_date_filter"><?= lang('apply') ?></button>
                    </li>


                </ul>

            </div>
        </div>

        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i> All
            </button>
            <ul class="dropdown-menu group animated zoomIn" style="width:300px;">
                <li class="filter_by all_filter" id="all"><a href="#"><?php echo lang('all'); ?></a></li>
                <li class="divider"></li>
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
                <!-- <div class="clearfix"></div>

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
                        <?php } ?>
                        <div class="clearfix"></div>
                        <?php } ?>
                    </ul>
                </li> -->
            </ul>
        </div>
        <?php } else { ?>


             <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i><?= lang('Lead Status') ?>
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px; padding:10px;">

                <div class="form-group">
                    <label for="department_filter">Lead Status </label>
                    <?php
 
$CI =& get_instance();
$CI->load->database();

 
$lead_status_list = $CI->db
    ->order_by('order_no', 'ASC')
    ->get('tbl_lead_status')
    ->result();
?>

                    <select class="form-control" name="lead_status" id="lead_status_filter">
                        <option value="">Select Lead Status</option>
                        <?php foreach ($lead_status_list as $status): ?>
                        <option value="<?= htmlspecialchars($status->lead_status_id) ?>">
                            <?= htmlspecialchars($status->lead_status) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                </div>


            </div>
        </div>




        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i><?= lang('Sub Status') ?>
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px; padding:10px;">

                <div class="form-group">
                    <label for="department_filter">Sub Status </label>
                    <select class="form-control" name="sub_status" id="sub_status_filter">
                        <option value="">Select Sub Status </option>
                        <option value="Meeting Done">Meeting Done </option>
                        <option value="Lost">Lost </option>
                        <option value="Nap">Nap </option>
                        <option value="Won">Won </option>
                        <option value="Lost after meeting done">Lost after meeting done </option>
                        <option value="Special">Special </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i> More
            </button>
            <ul class="dropdown-menu group animated zoomIn" style="width:300px;">
                <li class="filter_by all_filter" id="all"><a href="#"><?php echo lang('all'); ?></a></li>
                <li class="divider"></li>






                <!-- <div class="clearfix"></div>

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
                </li> -->
            </ul>
        </div>
        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
            data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i> Date Filter
            </button>
            <div class="dropdown-menu group animated zoomIn" style="width:300px;">
                <ul class="dropdown-menu-left" style="padding:10px;   list-style: none;" id="created_time_filter">


                    <li>
                        <label><?= lang('from_date') ?></label>
                        <input class="form-control datepicker" type="text" placeholder="<?= lang('Start Date') ?>"
                            id="filter_from_date" data-date-format="<?= config_item('date_picker_format'); ?>">
                    </li>
                    <li class="mt-2">
                        <label><?= lang('to_date') ?></label>
                        <input type="text" class="form-control datepicker " id="filter_to_date"
                            placeholder="<?= lang('End Date') ?>"
                            data-date-format="<?= config_item('date_picker_format'); ?>">
                    </li>
                    <li class="mt-2 text-right">
                        <button class="btn btn-sm btn-primary" id="apply_date_filter"><?= lang('apply') ?></button>
                    </li>


                </ul>

            </div>


        </div>
        <?php } ?>
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/leads') ?>"><?= lang('all_leads') ?></a>
                </li>

                
                <?php if ($is_admin || $is_team_leader || $is_regional_manager) { ?>
                <li class="<?= $active == 2 ? 'active' : ''; ?>"><a
                        href="<?= base_url('admin/leads/create') ?>"><?= lang('new_leads') ?></a>
                </li>
                <li><a class="import" href="<?= base_url() ?>admin/leads/import_leads"><?= lang('import_leads') ?></a>
                </li>
                <li><a class="import" href="<?= base_url() ?>admin/leads/call_logs"><?= lang('Call Recording') ?></a>
                </li>
                <?php } ?>

                <li><a class="import" href="<?= base_url() ?>admin/leads/reminder_today"><?= lang('Reminder List') ?></a>
                </li>

            </ul>
            <style type="text/css">
            .custom-bulk-button {
                display: initial;
            }
            </style>
            <div class="tab-content bg-white">
                <!-- ************** general *************-->
                <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
                    <?php } else { ?>
                    <div class="panel panel-custom">
                        <header class="panel-heading ">
                            <div class="panel-title"><strong><?= lang('all_leads') ?></strong></div>
                        </header>
                        <?php } ?>

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
                                        <!-- <th><?= lang('email') ?></th> -->
                                        <th><?= lang('phone') ?></th>
                                        <th><?= lang('city') ?></th>
                                        <!-- <th><?= lang('tags') ?></th> -->
                                        <?php if ($is_admin): ?>
                                        <th><?= lang('lead_source') ?></th>
                                        <?php endif; ?>
                                        <th><?= lang('status') ?></th>
                                        <th><?= lang('Sub Status') ?></th>
                                        <th><?= lang('Lead Date') ?></th>
                                        <th><?= lang('Budget') ?></th>
                                        <!-- <th><?= lang('Follow Date ') ?></th> -->
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

                                    $('.filtered > .dropdown-toggle').on('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        // Close all other open dropdowns
                                        $('.filtered .group').not($(this).siblings('.group')).hide();

                                        // Toggle only the current dropdown
                                        $(this).siblings('.group').toggle();
                                    });

                                    $('.all_filter').on('click', function() {
                                        $('.to_account').removeAttr("style");
                                        $('.from_account').removeAttr("style");
                                        // Assuming your "From Date" input has the ID 'from_date'
                                        $('#filter_from_date').val('');
                                        // Assuming your "To Date" input has the ID 'to_date'
                                        $('#filter_to_date').val('');
                                        window.location.href = '<?php echo base_url(); ?>admin/leads';
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
                                        $('.group').hide();
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

    // Function to get URL parameter by name
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    };

    $(document).ready(function() {
        // Set filter values from URL parameters on page load
        var urlFromDate = getUrlParameter('from_date');
        var urlToDate = getUrlParameter('to_date');
        var url_lead_status_new = getUrlParameter('lead_status_new');
        //alert(leadstatus);
        if (urlFromDate) {
            $('#filter_from_date').val(urlFromDate);
        }

        if (urlToDate) {
            $('#filter_to_date').val(urlToDate);
        }

        if (url_lead_status_new) {
            $('#lead_status_filter').val(url_lead_status_new);
        }

        //alert(url_lead_status_new);
        var leadsTable = $('#DataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: base_url + "admin/leads/leadList", // Base URL for the AJAX call
                type: "POST", // Still use POST for other filters to avoid excessively long GET URLs
                data: function(d) {
                    // Get values from current filter inputs
                    d.budget_from = $('#filter_budget_from').val();
                    d.budget_to = $('#filter_budget_to').val();
                    d.from_date = $('#filter_from_date').val();
                    d.to_date = $('#filter_to_date').val();
                    d.user_id = $('#user_select').val();
                    d.department_id = $('#department_filter').val();
                    d.sub_status = $('#sub_status_filter').val();
                    d.lead_status_new = $('#lead_status_filter').val();

                    // IMPORTANT: Ensure URL parameters are not overwritten by empty input values on initial load
                    // if the URL parameters are present. The inputs are already set from URL parameters above.
                    if (!d.from_date && urlFromDate) {
                        d.from_date = urlFromDate;
                    }
                    if (!d.to_date && urlToDate) {
                        d.to_date = urlToDate;
                    }
                    if (!d.lead_status_new && url_lead_status_new) {
                        d.lead_status_new = url_lead_status_new;
                    }

                    // --- Add this line to print the POST data in an alert ---
                    // alert('Data being sent via POST: ' +  d);
                    // --- End of addition ---

                    // return d; // Don't forget to return the data object!

                },
                dataSrc: function(json) {
                    // The URL parameters are now handled directly when applying the filter,
                    // so no need to clear them here after initial load.
                    return json.data;
                }
            },


            <?php if ($is_admin || $is_team_leader || $is_regional_manager) { ?>
            dom: 'lBfrtip',
            buttons: [{
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
                {
                    text: '<?= lang('bulk_delete') ?>',
                    className: 'btn btn-xs btn-default custom-bulk-button',
                },
            ],

            <?php } ?>
            select: true,
            createdRow: function(row, data, dataIndex) {
                if (data['DT_RowClass'] === 'duplicate-row') {
                    $('td:eq(3)', row).css('color', 'red'); // Highlight phone column
                }
            }
        });

        // Function to update URL parameters
        function updateUrlParams(params) {
            var url = new URL(window.location.href);
            for (var key in params) {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                } else {
                    url.searchParams.delete(key);
                }
            }
            history.replaceState({}, document.title, url.toString());
        }

        // Apply filters (including date filters from inputs)
        $('#apply_budget_filter, #apply_date_filter').on('click', function() {
            var params = {
                from_date: $('#filter_from_date').val(),
                to_date: $('#filter_to_date').val(),
                // Add other filters here if you want them in the URL
                // budget_from: $('#filter_budget_from').val(),
                // budget_to: $('#filter_budget_to').val(),
                // user_id: $('#user_select').val(),
                // department_id: $('#department_filter').val(),
                // sub_status: $('#sub_status_filter').val()
            };
            updateUrlParams(params);
            leadsTable.ajax.reload();
            $('.group').hide();
        });

        $('#lead_status_filter').on('change', function() {
            var params = {
                lead_status_new: $('#lead_status_filter').val(),

            };
            updateUrlParams(params);

            leadsTable.ajax.reload();
            $('.group').hide();
        });


        // Optional Reset Filters
        $('#reset_filters').on('click', function() {
            $('#filter_budget_from, #filter_budget_to, #filter_from_date, #filter_to_date').val('');
            updateUrlParams({
                from_date: '',
                to_date: ''
            }); // Clear date parameters from URL
            leadsTable.ajax.reload();
            $('.group').hide();
        });

        $('#user_select').on('change', function() {
            leadsTable.ajax.reload();
            $('.group').hide();
        });

        $('#department_filter').on('change', function() {
            leadsTable.ajax.reload();
            $('.group').hide();
        });

        $('#sub_status_filter').on('change', function() {
            leadsTable.ajax.reload();
            $('.group').hide();
        });

    });
    </script>

    <script>
    $(document).ready(function() {
        $.ajax({
            url: "<?= base_url('admin/leads/get_departments') ?>",
            method: "GET",
            dataType: "json",
            success: function(response) {
                var $dropdown = $('#department_filter');
                $.each(response, function(index, dept) {
                    $dropdown.append(
                        $('<option>', {
                            value: dept.id,
                            text: dept.name
                        })
                    );
                });
            }
        });
    });

    $(document).ready(function() {
        $('#department_select').on('change', function() {
            var departmentId = $(this).val();

            if (departmentId !== '') {
                $.ajax({
                    url: '<?= base_url('admin/leads/get_users_by_department') ?>',
                    type: 'POST',
                    data: {
                        departments_id: departmentId
                    },
                    success: function(res) {
                        $('#user_select').html(res);
                    }
                });
            } else {
                $('#user_select').html('<option value="">Select User</option>');
            }
        });
    });
    </script>

    <script>
    $(document).on('click', '.fa-pencil-square-o', function(e) {

        // Save current URL before the redirect
        const currentUrl = window.location.href;
        //alert(currentUrl);
        // Send AJAX to save the URL in session
        $.post("<?php echo base_url('admin/leads/save_current_url'); ?>", {
            current_url: currentUrl
        });

        // Let the click continue (don't use e.preventDefault() unless you're modifying behavior)
    });
    </script>

    <style>
    tr.duplicate-row td {
        background: #ffbebe;
    }



    .mt-2 {
        margin-top: 10px;
    }

    .form-control {
        width: 100% !important;
    }
    </style>