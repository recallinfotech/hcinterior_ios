<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description"
          content="attendance, client management, finance, freelance, freelancer, goal tracking, Income Managment, lead management, payroll, project management, project manager, support ticket, task management, timecard">
    <meta name="keywords"
          content="	attendance, client management, finance, freelance, freelancer, goal tracking, Income Managment, lead management, payroll, project management, project manager, support ticket, task management, timecard">
    <title><?php echo $title; ?></title>
    <?php if (config_item('favicon') != '') : ?>
        <link rel="icon" href="<?php echo base_url() . config_item('favicon'); ?>" type="image/png">
    <?php else : ?>
        <link rel="icon" href="<?php echo base_url('assets/img/favicon.ico'); ?>" type="image/png">
    <?php endif; ?>
    <!-- =============== VENDOR STYLES ===============-->
    <!-- FONT AWESOME-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/fontawesome/css/font-awesome.min.css">
    <!-- SIMPLE LINE ICONS-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/simple-line-icons/css/simple-line-icons.css">
    <!-- ANIMATE.CSS-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/animate.css/animate.min.css">
    <!-- =============== PAGE VENDOR STYLES ===============-->
    
    <!-- =============== APP STYLES ===============-->
    <?php $direction = $this->session->userdata('direction');
    if (!empty($direction) && $direction == 'rtl') {
        $RTL = 'on';
    } else {
        $RTL = config_item('RTL');
    }
    
    ?>
    <?php
    if (!empty($RTL)) {
        ?>
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap-rtl.min.css" id="bscss">
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/app-rtl.min.css" id="maincss">
    <?php } else {
        ?>
        <!-- =============== BOOTSTRAP STYLES ===============-->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" id="bscss">
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/app.css" id="maincss">
    <?php }
    $custom_color = config_item('active_custom_color');
    if (!empty($custom_color) && $custom_color == 1) {
        include_once 'assets/css/bg-custom.php';
    } else {
        ?>
        <link id="autoloaded-stylesheet" rel="stylesheet"
              href="<?php echo base_url(); ?>assets/css/<?= config_item('sidebar_theme') ?>.css">
    <?php }
    ?>
    
    
    <!-- SELECT2-->
    
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/select2/dist/css/select2-bootstrap.min.css">
    <?php
    if (empty(config_item('enable_chat_pro'))) {
        ?>
        <link href="<?php echo base_url() ?>assets/plugins/chat/chat.min.css" rel="stylesheet">
    <?php }
    ?>
    <!-- Datepicker-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/datepicker.min.css">
    
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/timepicker.min.css">
    
    <!-- Toastr-->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/toastr.min.css">
    <!-- Data Table  CSS -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/dataTables/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/dataTables/css/dataTables.colVis.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/dataTables/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/dataTables/css/responsive.dataTables.min.css">
    <!-- summernote Editor -->
    
    <link href="<?php echo base_url(); ?>assets/plugins/summernote/summernote.min.css" rel="stylesheet" type="text/css">
    
    <!-- bootstrap-slider -->
    <link href="<?php echo base_url() ?>assets/plugins/bootstrap-slider/bootstrap-slider.min.css" rel="stylesheet">
    <!-- chartist -->
    <link href="<?php echo base_url() ?>assets/plugins/morris/morris.min.css" rel="stylesheet">
    
    <?= $this->module->load_css(); ?>
    
    <!--- bootstrap-select ---->
    <link href="<?php echo base_url() ?>assets/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/plugins/chat/chat.min.css" rel="stylesheet">
    
    <!-- JQUERY-->
    <script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
    
    <link href="<?php echo base_url() ?>asset/css/bootstrap-toggle.min.css" rel="stylesheet">
    <script src="<?php echo base_url() ?>asset/js/bootstrap-toggle.min.js"></script>
    
    
    <?php
    if (empty($unread_notifications)) {
        $unread_notifications = 0;
    }
    ?>
    <script>
        var total_unread_notifications = <?php echo $unread_notifications; ?>,
            autocheck_notifications_timer_id = 0,
            list = null,
            bulk_url = null,
            time_format = <?= (config_item('time_format') == 'H:i' ? 'false' : true) ?>,
            ttable = null,
            base_url = "<?php echo base_url(); ?>",
            new_notification = "<?php lang('new_notification'); ?>",
            credit_amount_bigger_then_remaining_credit = "<?= lang('credit_amount_bigger_then_remaining_credit'); ?>",
            credit_amount_bigger_then_invoice_due = "<?= lang('credit_amount_bigger_then_due_amount'); ?>",
            auto_check_for_new_notifications = <?php echo config_item('auto_check_for_new_notifications'); ?>,
            file_upload_instruction = "<?php echo lang('file_upload_instruction_js'); ?>",
            filename_too_long = "<?php echo lang('filename_too_long'); ?>";
        desktop_notifications = "<?php echo config_item('desktop_notifications'); ?>";
        lsetting = "<?php echo lang('settings'); ?>";
        lfull_conversation = "<?php echo lang('full_conversation'); ?>";
        ledit_name = "<?php echo lang('edit') . ' ' . lang('name') ?>";
        ldelete_conversation = "<?php echo lang('delete_conversation') ?>";
        lminimize = "<?php echo lang('minimize') ?>";
        lclose = "<?php echo lang('close') ?>";
        lnew = "<?php echo lang('new') ?>";
        ldelete_confirm = "<?php echo lang('delete_alert') ?>";
        no_result_found = "<?php echo lang('no_result_found') ?>";
        searchType = "<?php echo $this->uri->segment(2); ?>";
        available_tags = <?php echo json_encode(available_tags()); ?>;
    </script>
    <style>
        <?php
    if (!empty(config_item('sidebar_font_size'))){
        ?>
        .sidebar .nav > li > a,
        .sidebar .nav > li > .nav-item {
            font-size: <?= config_item('sidebar_font_size').'px !important'?>;
        }
        
        <?php
    }
    if (!empty(config_item('body_font_size'))){
        ?>
        body {
            font-size: <?= config_item('body_font_size').'px !important'?>;
        }

        .navbar-custom-nav li {
            font-size: <?= config_item('body_font_size').'px !important'?>;
        }
        
        <?php
    }
    ?>
    
    /* ==========================================================================
       GLOBAL PROFESSIONAL MODERN THEME & CLIENT UI ENHANCEMENTS
       ========================================================================== */
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        background-color: #f8fafc !important;
        color: #334155;
        -webkit-font-smoothing: antialiased;
    }

    /* Elevated Panels & Cards */
    .panel {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04), 0 2px 4px -2px rgba(0, 0, 0, 0.02) !important;
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }
    .panel:hover {
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.06), 0 4px 6px -2px rgba(0, 0, 0, 0.03) !important;
    }
    .panel-heading {
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 14px 20px !important;
    }
    .panel-title {
        font-weight: 600 !important;
        font-size: 15px !important;
        letter-spacing: -0.01em;
    }
    .panel-custom > .panel-heading {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
        color: #ffffff !important;
    }
    .panel-custom > .panel-heading .panel-title,
    .panel-custom > .panel-heading a {
        color: #ffffff !important;
    }

    /* DataTables & Responsive Tables */
    .table-responsive {
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
    }
    .table {
        margin-bottom: 0 !important;
    }
    .table > thead > tr > th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px 14px !important;
        vertical-align: middle !important;
    }
    .table > tbody > tr > td {
        padding: 12px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155 !important;
        font-size: 13px !important;
    }
    .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #ffffff !important;
    }
    .table-striped > tbody > tr:nth-of-type(even) {
        background-color: #f8fafc !important;
    }
    .table > tbody > tr:hover {
        background-color: #f1f5f9 !important;
    }

    /* Styled Pill Badges & Status Labels */
    .label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        padding: 4px 10px !important;
        border-radius: 20px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        letter-spacing: 0.02em !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
    .label-success {
        background-color: #f0fdf4 !important;
        color: #16a34a !important;
        border: 1px solid #dcfce7 !important;
    }
    .label-info {
        background-color: #f0f9ff !important;
        color: #0284c7 !important;
        border: 1px solid #e0f2fe !important;
    }
    .label-warning {
        background-color: #fffbeb !important;
        color: #d97706 !important;
        border: 1px solid #fef3c7 !important;
    }
    .label-danger {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fee2e2 !important;
    }
    .label-primary {
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #dbeafe !important;
    }
    .label-default {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
    }

    /* Sleek Buttons */
    .btn {
        border-radius: 6px !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        border: none !important;
        color: #ffffff !important;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%) !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
    }
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border: none !important;
        color: #ffffff !important;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25) !important;
    }
    .btn-info {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
        border: none !important;
        color: #ffffff !important;
    }
    .btn-info:hover {
        background: linear-gradient(135deg, #0369a1 0%, #075985 100%) !important;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25) !important;
    }
    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        border: none !important;
        color: #ffffff !important;
    }
    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25) !important;
    }
    .btn-default {
        background-color: #ffffff !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
    }
    .btn-default:hover {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
    }

    /* Modern Form Controls */
    .form-control {
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02) !important;
        padding: 6px 12px !important;
        font-size: 13px !important;
        color: #1e293b !important;
        transition: all 0.2s ease !important;
    }
    .form-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        outline: none !important;
    }

    /* Sub-Navigation Tabs & Nav Pills */
    .nav-tabs, .nav-pills {
        border-bottom: 2px solid #e2e8f0 !important;
        gap: 6px !important;
    }
    .nav-tabs > li > a, .nav-pills > li > a {
        border-radius: 8px 8px 0 0 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: #64748b !important;
        padding: 10px 16px !important;
        transition: all 0.2s ease !important;
    }
    .nav-tabs > li.active > a, .nav-pills > li.active > a {
        background-color: #ffffff !important;
        color: #2563eb !important;
        border-bottom: 2px solid #2563eb !important;
        font-weight: 700 !important;
    }
    .nav-tabs > li > a:hover, .nav-pills > li > a:hover {
        background-color: #f8fafc !important;
        color: #0f172a !important;
    }

    /* Client Detail Cards */
    .client-card {
        background: #ffffff !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04) !important;
        border: 1px solid #e2e8f0 !important;
        margin-bottom: 20px !important;
        padding: 20px !important;
    }
    .client-card h4 {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        border-bottom: 2px solid #f1f5f9 !important;
        padding-bottom: 10px !important;
        margin-bottom: 16px !important;
    }
    </style>

</head>