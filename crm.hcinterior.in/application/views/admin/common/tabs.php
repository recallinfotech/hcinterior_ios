<?php
if (!empty($global)) {
    echo $global;
}
?>
<div class="row mt-lg">
    <div class="col-sm-2">
        <ul class="nav nav-pills nav-stacked navbar-custom-nav">
            <?php
            
            if (!empty($all_tabs)) {
                foreach ($all_tabs as $key => $v_tab) {
                    if (!empty($v_tab['hidden'])) {
                        continue;
                    }
                    ?>
                   
                   <?php 
$current_tab = $this->uri->segment(5) ?: 'details';

// 🔥 priority: active → name → key
$isActive = false;

if (!empty($active)) {
    $isActive = ($active === $v_tab['name']);
} else {
    $isActive = ($current_tab === $v_tab['name']);
}
?>

<li class="<?= $isActive ? 'active' : '' ?>">
    
                        <a href="<?= base_url($v_tab['url']) ?>">
                            <?php if (!empty($v_tab['icon'])) { ?>
                                <i class="<?= $v_tab['icon'] ?>"></i>
                            <?php } ?>
                           <?php  if($v_tab['tab_name']) { ?>
 <?= lang($v_tab['tab_name']) ?>
                          <?php  } else { ?>
                            <?= lang($v_tab['name']) ?>

                        <?php } ?>
                            <strong class="pull-right">
                              <strong class="pull-right">

    <?php if (!empty($v_tab['badge'])) { ?>
        <span class="label label-success">
            <i class="fa fa-check"></i>
        </span>
    <?php } ?>

    <?php if (!empty($v_tab['count'])) { ?>
        <span class="label label-inverse">
            <?= $v_tab['count'] ?>
        </span>
    <?php } ?>

</strong>
                            </strong>
                        </a>
                    </li>
                    <?php
                }
            }
            ?>
        
        </ul>
    </div>
    
    <div class="col-sm-10">

    <?php
    // Current Client ID
    $client_id = $this->uri->segment(4);

    // Client Operation Status
    $operation_status = null;

    if (!empty($client_id)) {

        $client_status_data = $this->db
            ->select('operation_status')
            ->where('client_id', $client_id)
            ->get('tbl_client')
            ->row();

        if (!empty($client_status_data)) {
            $operation_status = $client_status_data->operation_status;
        }
    }

    $is_read_only = (!empty($operation_status) && $operation_status != 'Unhold');
    ?>


    <!-- ================= OPERATION STATUS MESSAGE ================= -->

    <?php if ($operation_status == 'Hold') { ?>

        <div class="alert alert-danger text-center">
            <i class="fa fa-lock"></i>
            <strong>This Client is On Hold</strong>
        </div>

    <?php } elseif ($operation_status == 'Cancelled') { ?>

        <div class="alert alert-warning text-center">
            <i class="fa fa-times-circle"></i>
            <strong>This Client is Cancelled</strong>
        </div>

    <?php } elseif ($operation_status == 'Completed') { ?>

        <div class="alert alert-success text-center">
            <i class="fa fa-check-circle"></i>
            <strong>This Client is Completed</strong>
        </div>

    <?php } ?>


    <!-- ================= TAB CONTENT ================= -->

    <div class="tab-content <?= $is_read_only ? 'hold-mode' : '' ?>" style="border:0;padding:0;">

        <?php
        $view = tab_load_view($all_tabs, $active);
        $this->load->view($view);
        ?>

    </div>

    <?php if ($is_read_only) { ?>
        <style>
            .hold-mode {
                pointer-events: none !important;
                opacity: 0.75;
            }

            .hold-mode #operation_status,
            .hold-mode #updateOperationStatus,
            .hold-mode .panel-heading .pull-right {
                pointer-events: auto !important;
            }
        </style>
    <?php } ?>

</div>
</div>

<script>
$(document).ready(function() {
    // Universal Save/Submit button handler for all client tabs
    $(document).on('submit', 'form', function(e) {
        if (e.isDefaultPrevented()) {
            return;
        }

        var $form = $(this);

        // Skip GET forms, target=_blank, or forms explicitly marked with .no-disable
        if ($form.hasClass('no-disable') || 
            ($form.attr('method') && $form.attr('method').toLowerCase() === 'get') || 
            $form.attr('target') === '_blank') {
            return;
        }

        var $btn = $form.find('button[type="submit"], input[type="submit"]').filter(':visible');
        if (!$btn.length) {
            $btn = $form.find('button[type="submit"], input[type="submit"]');
        }

        if ($btn.length) {
            $btn.each(function() {
                var $b = $(this);
                if ($b.is('input')) {
                    $b.val('Please wait...');
                } else {
                    $b.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                }
                $b.css({
                    'pointer-events': 'none',
                    'opacity': '0.7'
                });
                setTimeout(function() {
                    $b.prop('disabled', true);
                }, 20);
            });
        }
    });
});
</script>