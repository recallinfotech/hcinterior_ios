<style>
/* Operation Permission Matrix Custom Styles */
.matrix-card {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    background: #ffffff;
    margin-bottom: 25px;
}
.matrix-header {
    background: #1e293b;
    color: #ffffff;
    padding: 12px 18px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.matrix-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.matrix-controls-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Outer Wrapper for Table & Floating Arrows */
.matrix-outer-wrapper {
    position: relative;
    padding: 15px;
    background: #ffffff;
}

/* Floating Navigation Arrows */
.matrix-float-arrow {
    position: absolute;
    top: 55%;
    transform: translateY(-50%);
    z-index: 45;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #1e293b;
    color: #ffffff;
    border: 2px solid #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0.9;
    transition: all 0.2s ease;
    padding: 0;
}
.matrix-float-arrow:hover {
    opacity: 1;
    background: #2563eb;
    transform: translateY(-50%) scale(1.12);
    color: #ffffff;
}
.matrix-float-arrow.arrow-left {
    left: 270px; /* Positions right after sticky first column */
}
.matrix-float-arrow.arrow-right {
    right: 25px;
}
.matrix-float-arrow:disabled, .matrix-float-arrow.disabled {
    opacity: 0.3;
    cursor: not-allowed;
    pointer-events: none;
}

/* Table Container with Custom Visible Scrollbar & Vertical Height Limit */
.matrix-table-container {
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 220px);
    min-height: 480px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    position: relative;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
}
.matrix-table-container.matrix-full-height {
    max-height: none !important;
}
.matrix-table-container::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}
.matrix-table-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 6px;
}
.matrix-table-container::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 6px;
}
.matrix-table-container::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Top Synchronized Scrollbar */
.matrix-top-scroll-wrapper {
    overflow-x: auto;
    overflow-y: hidden;
    height: 10px;
    margin-bottom: 6px;
    border-radius: 4px;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
}
.matrix-top-scroll-wrapper::-webkit-scrollbar {
    height: 10px;
}
.matrix-top-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.matrix-top-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 4px;
}
.matrix-top-scroll-dummy {
    height: 10px;
}

/* Table Styling */
.matrix-table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

/* Frozen Header Row (Sticky Top) */
.matrix-table thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: #1e293b !important;
    color: #f8fafc !important;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    text-align: center;
    vertical-align: middle;
    padding: 12px 8px;
    border-top: none !important;
    border-bottom: 2px solid #0f172a !important;
    border-right: 1px solid #334155 !important;
    white-space: normal;
    min-width: 110px;
    max-width: 135px;
    line-height: 1.3;
    box-shadow: 0 4px 6px -2px rgba(0, 0, 0, 0.25);
}

/* Sticky First Column (Permission Name) */
.matrix-table thead th:first-child,
.matrix-table tbody td:first-child {
    position: sticky;
    left: 0;
    width: 260px;
    min-width: 260px;
    max-width: 260px;
    text-align: left;
}
.matrix-table tbody td:first-child {
    z-index: 10;
    box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.12);
}

/* Top-Left Cell: Permission Name Header (Sticky Both Vertically & Horizontally) */
.matrix-table thead th:first-child {
    position: sticky;
    top: 0;
    left: 0;
    background-color: #0f172a !important;
    color: #38bdf8 !important;
    z-index: 35 !important;
    font-size: 12px;
    box-shadow: 4px 4px 8px -2px rgba(0, 0, 0, 0.35);
}
.matrix-table tbody tr td:first-child {
    background-color: #ffffff;
    border-right: 2px solid #cbd5e1 !important;
}
.matrix-table tbody tr:nth-child(even) td:first-child {
    background-color: #f8fafc;
}
.matrix-table tbody tr:hover td:first-child {
    background-color: #f1f5f9;
}

/* Table Body Cells */
.matrix-table tbody td {
    vertical-align: middle;
    text-align: center;
    padding: 8px 6px;
    border-bottom: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
    transition: background-color 0.15s ease;
}
.matrix-table tbody tr:hover td {
    background-color: #f1f5f9;
}
.matrix-table tbody tr.permission-row-highlight td {
    background-color: #fef3c7 !important;
}

/* Checkbox Styling */
.matrix-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    margin: 0;
    vertical-align: middle;
    accent-color: #2563eb;
    transition: transform 0.1s ease;
}
.matrix-checkbox:hover {
    transform: scale(1.15);
}

/* Floating Toast Alert Notification */
#matrix_toast_alert {
    position: fixed;
    top: 75px;
    right: 25px;
    z-index: 999999;
    min-width: 290px;
    max-width: 380px;
    padding: 12px 18px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    display: none;
    align-items: center;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
}
#matrix_toast_alert.toast-success {
    background-color: #10b981;
    color: #ffffff;
    border-left: 5px solid #047857;
}
#matrix_toast_alert.toast-error {
    background-color: #ef4444;
    color: #ffffff;
    border-left: 5px solid #b91c1c;
}
</style>

<div style="width: 100%; margin-top: 8px;">
    <!-- Floating Toast Alert Notification -->
    <div id="matrix_toast_alert">
        <i id="matrix_toast_icon" class="fa fa-check-circle" style="font-size: 18px; margin-right: 8px;"></i>
        <span id="matrix_toast_text">Permission Updated</span>
    </div>

    <div class="matrix-card">
                
                <!-- Panel Header with Search & Scroll Controls -->
                <div class="matrix-header">
                    <div class="matrix-title">
                        <i class="fa fa-shield" style="color: #38bdf8; font-size: 18px;"></i>
                        <span>Operation Permission Matrix</span>
                        <span class="badge" style="background-color: #2563eb; font-size: 11px; padding: 4px 10px; font-weight: 600; border-radius: 12px;">
                            <?= count($roles) ?> Roles &bull; <span id="matrix_perm_count"><?= count($permissions) ?></span> Permissions
                        </span>
                    </div>

                    <div class="matrix-controls-bar">
                        <!-- Search Box -->
                        <div style="position: relative; width: 220px;">
                            <i class="fa fa-search" style="position: absolute; left: 10px; top: 9px; color: #94a3b8; font-size: 12px;"></i>
                            <input type="text" id="matrix_permission_search" class="form-control input-sm" placeholder="Search permission..." style="padding-left: 28px; height: 32px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: #ffffff; font-size: 12px;">
                        </div>

                        <!-- Left / Right Scroll Navigation Buttons -->
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-default" id="btn_scroll_start" title="Scroll to First Column" style="height: 32px; font-weight: 600; background: #ffffff; color: #1e293b; border-color: #cbd5e1; border-radius: 6px 0 0 6px;">
                                <i class="fa fa-angle-double-left"></i>
                            </button>
                            <button type="button" class="btn btn-primary" id="btn_scroll_left" title="Scroll Left (Previous Roles)" style="height: 32px; font-weight: 600; background-color: #2563eb; border-color: #1d4ed8; padding: 5px 12px;">
                                <i class="fa fa-chevron-left" style="margin-right: 4px;"></i> Scroll Left
                            </button>
                            <button type="button" class="btn btn-primary" id="btn_scroll_right" title="Scroll Right (Next Roles)" style="height: 32px; font-weight: 600; background-color: #2563eb; border-color: #1d4ed8; padding: 5px 12px;">
                                Scroll Right <i class="fa fa-chevron-right" style="margin-left: 4px;"></i>
                            </button>
                            <button type="button" class="btn btn-default" id="btn_scroll_end" title="Scroll to Last Column (Site Dispatcher)" style="height: 32px; font-weight: 600; background: #ffffff; color: #1e293b; border-color: #cbd5e1; border-radius: 0 6px 6px 0;">
                                <i class="fa fa-angle-double-right"></i>
                            </button>
                        </div>

                        <!-- Toggle Fit Screen / Full Height View -->
                        <button type="button" class="btn btn-default btn-sm" id="btn_toggle_height" title="Toggle Full Height / Fit Screen View" style="height: 32px; font-weight: 600; background: #ffffff; color: #1e293b; border-color: #cbd5e1; border-radius: 6px;">
                            <i class="fa fa-arrows-v"></i> <span id="toggle_height_text">Full View</span>
                        </button>
                    </div>
                </div>

                <!-- Matrix Outer Wrapper with Top Scrollbar and Table -->
                <div class="matrix-outer-wrapper">
                    
                    <!-- Top Synchronized Scrollbar -->
                    <div class="matrix-top-scroll-wrapper" id="matrix_top_scroll">
                        <div class="matrix-top-scroll-dummy" id="matrix_top_scroll_dummy"></div>
                    </div>

                    <!-- Floating Navigation Arrows -->
                    <button type="button" class="matrix-float-arrow arrow-left" id="btn_float_left" title="Scroll Left (Previous Roles)">
                        <i class="fa fa-chevron-left"></i>
                    </button>
                    <button type="button" class="matrix-float-arrow arrow-right" id="btn_float_right" title="Scroll Right (Next Roles)">
                        <i class="fa fa-chevron-right"></i>
                    </button>

                    <!-- Table Scroll Container -->
                    <div class="matrix-table-container" id="matrix_table_container">
                        <table class="table table-bordered matrix-table" id="matrix_table">
                            <thead>
                                <tr>
                                    <th>Permission Name</th>
                                    <?php foreach ($roles as $role): ?>
                                        <th title="<?php echo html_escape($role->role_name); ?>">
                                            <?php echo html_escape($role->role_name); ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody id="matrix_table_body">
                                <?php foreach ($permissions as $perm): ?>
                                    <tr class="permission-row" data-perm-name="<?php echo strtolower(html_escape($perm->permission_name . ' ' . $perm->permission_key)); ?>">
                                        <!-- Sticky Permission Title Column -->
                                        <td>
                                            <strong style="color: #0f172a; font-size: 13px;"><?php echo html_escape($perm->permission_name); ?></strong>
                                            <br>
                                            <small class="text-muted" style="font-size: 11px; font-family: monospace; color: #64748b;"><?php echo html_escape($perm->permission_key); ?></small>
                                        </td>

                                        <!-- Roles Checkbox Columns -->
                                        <?php foreach ($roles as $role): ?>
                                            <?php 
                                                $isChecked = isset($mapped[$role->id][$perm->id]); 
                                            ?>
                                            <td>
                                                <input 
                                                    type="checkbox" 
                                                    class="permission-checkbox matrix-checkbox" 
                                                    data-role-id="<?php echo $role->id; ?>" 
                                                    data-perm-id="<?php echo $perm->id; ?>"
                                                    <?php echo $isChecked ? 'checked' : ''; ?>
                                                    title="<?php echo html_escape($role->role_name . ' - ' . $perm->permission_name); ?>"
                                                >
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <tr id="matrix_no_results_row" style="display: none;">
                                    <td colspan="<?php echo count($roles) + 1; ?>" class="text-center text-muted" style="padding: 30px; font-size: 14px;">
                                        <i class="fa fa-info-circle fa-2x" style="color: #94a3b8; display: block; margin-bottom: 8px;"></i>
                                        No permissions matching your search query.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Info / Helper Bar -->
                    <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; color: #64748b; font-size: 12px;">
                        <div>
                            <i class="fa fa-info-circle text-info"></i> <strong>Tip:</strong> Use <strong>Scroll Left / Right</strong> buttons, drag the top/bottom scrollbar, or use <strong>Shift + Mouse Scroll</strong> to view all role columns.
                        </div>
                        <div id="matrix_scroll_indicator" style="font-weight: 600; color: #475569;">
                            Viewing Roles (Use buttons or scroll to view more)
                        </div>
                    </div>

                </div>
            </div>
        </div>

<!-- jQuery AJAX & Interactive Controls Script -->
<script type="text/javascript">
$(document).ready(function() {
    var $tableContainer = $('#matrix_table_container');
    var $topScrollWrapper = $('#matrix_top_scroll');
    var $topScrollDummy = $('#matrix_top_scroll_dummy');
    var $table = $('#matrix_table');

    // 1. Synchronize Top & Bottom Scrollbars
    function updateScrollbarWidths() {
        var tableWidth = $table.outerWidth();
        $topScrollDummy.width(tableWidth);
        updateArrowStates();
    }
    updateScrollbarWidths();
    $(window).on('resize', updateScrollbarWidths);

    var isSyncingTop = false;
    var isSyncingBottom = false;

    $topScrollWrapper.on('scroll', function() {
        if (!isSyncingTop) {
            isSyncingBottom = true;
            $tableContainer.scrollLeft($(this).scrollLeft());
        }
        isSyncingTop = false;
        updateArrowStates();
    });

    $tableContainer.on('scroll', function() {
        if (!isSyncingBottom) {
            isSyncingTop = true;
            $topScrollWrapper.scrollLeft($(this).scrollLeft());
        }
        isSyncingBottom = false;
        updateArrowStates();
    });

    // 2. Update Arrow & Indicator State
    function updateArrowStates() {
        var scrollLeft = $tableContainer.scrollLeft();
        var maxScroll = $table.outerWidth() - $tableContainer.outerWidth();

        if (scrollLeft <= 5) {
            $('#btn_scroll_left, #btn_scroll_start, #btn_float_left').prop('disabled', true).addClass('disabled');
        } else {
            $('#btn_scroll_left, #btn_scroll_start, #btn_float_left').prop('disabled', false).removeClass('disabled');
        }

        if (scrollLeft >= maxScroll - 5) {
            $('#btn_scroll_right, #btn_scroll_end, #btn_float_right').prop('disabled', true).addClass('disabled');
            $('#matrix_scroll_indicator').html('<span class="text-success"><i class="fa fa-check"></i> Reached End (All Roles Visible)</span>');
        } else {
            $('#btn_scroll_right, #btn_scroll_end, #btn_float_right').prop('disabled', false).removeClass('disabled');
            $('#matrix_scroll_indicator').text('Scroll right to view Site Dispatcher & more roles');
        }
    }
    updateArrowStates();

    // 3. Scroll Button Actions (Smooth Scroll by 350px)
    var scrollStep = 350;

    $('#btn_scroll_left, #btn_float_left').on('click', function(e) {
        e.preventDefault();
        var current = $tableContainer.scrollLeft();
        $tableContainer.animate({ scrollLeft: current - scrollStep }, 300);
    });

    $('#btn_scroll_right, #btn_float_right').on('click', function(e) {
        e.preventDefault();
        var current = $tableContainer.scrollLeft();
        $tableContainer.animate({ scrollLeft: current + scrollStep }, 300);
    });

    $('#btn_scroll_start').on('click', function(e) {
        e.preventDefault();
        $tableContainer.animate({ scrollLeft: 0 }, 400);
    });

    $('#btn_scroll_end').on('click', function(e) {
        e.preventDefault();
        var maxScroll = $table.outerWidth() - $tableContainer.outerWidth();
        $tableContainer.animate({ scrollLeft: maxScroll }, 400);
    });

    // 4. Live Search Filter
    $('#matrix_permission_search').on('keyup', function() {
        var query = $(this).val().toLowerCase().trim();
        var matchCount = 0;

        $('.permission-row').each(function() {
            var permData = $(this).data('perm-name') || '';
            if (query === '' || permData.indexOf(query) !== -1) {
                $(this).show();
                matchCount++;
            } else {
                $(this).hide();
            }
        });

        $('#matrix_perm_count').text(matchCount);

        if (matchCount === 0) {
            $('#matrix_no_results_row').show();
        } else {
            $('#matrix_no_results_row').hide();
        }
    });

    // 5. Toast Notification Helper
    var toastTimeout = null;
    function showMatrixToast(message, isSuccess) {
        var $toast = $('#matrix_toast_alert');
        var $icon = $('#matrix_toast_icon');
        var $text = $('#matrix_toast_text');

        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }

        $text.text(message);
        if (isSuccess) {
            $toast.removeClass('toast-error').addClass('toast-success');
            $icon.attr('class', 'fa fa-check-circle');
        } else {
            $toast.removeClass('toast-success').addClass('toast-error');
            $icon.attr('class', 'fa fa-exclamation-circle');
        }

        $toast.stop(true, true).fadeIn(200);
        toastTimeout = setTimeout(function() {
            $toast.fadeOut(300);
        }, 2200);
    }

    // 6. Checkbox Change AJAX Listener
    $('.permission-checkbox').on('change', function() {
        var checkbox = $(this);
        var roleId = checkbox.data('role-id');
        var permId = checkbox.data('perm-id');
        var isStatus = checkbox.is(':checked') ? 1 : 0;
        var row = checkbox.closest('tr');

        checkbox.prop('disabled', true);
        row.addClass('permission-row-highlight');

        $.ajax({
            url: "<?php echo base_url('admin/operationpermission/update_permission'); ?>",
            type: "POST",
            data: {
                operation_role_id: roleId,
                permission_id: permId,
                status: isStatus
            },
            dataType: "json",
            success: function(response) {
                checkbox.prop('disabled', false);
                row.removeClass('permission-row-highlight');
                if (response.status) {
                    showMatrixToast(response.message || 'Permission Updated Successfully!', true);
                } else {
                    checkbox.prop('checked', !isStatus);
                    showMatrixToast(response.message || 'Error updating permission', false);
                }
            },
            error: function() {
                checkbox.prop('disabled', false);
                row.removeClass('permission-row-highlight');
                checkbox.prop('checked', !isStatus);
                showMatrixToast('Server Connection Error! Please try again.', false);
            }
        });
    });

    // 6. Toggle Full Height / Fit Screen View
    $('#btn_toggle_height').on('click', function(e) {
        e.preventDefault();
        var $tc = $('#matrix_table_container');
        if ($tc.hasClass('matrix-full-height')) {
            $tc.removeClass('matrix-full-height');
            $('#toggle_height_text').text('Full View');
            $(this).find('i').removeClass('fa-compress').addClass('fa-arrows-v');
        } else {
            $tc.addClass('matrix-full-height');
            $('#toggle_height_text').text('Fit Screen');
            $(this).find('i').removeClass('fa-arrows-v').addClass('fa-compress');
        }
    });

});
</script>