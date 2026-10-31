
    // Prevent DataTables row-selection interference
    $(document).on('click', '.employee_checkbox, #select_all_employees, .btn-single-status, .dropdown-toggle', function(e) {
        e.stopPropagation();
    });

    // 1. Select All Checkbox Handler
    $('#select_all_employees').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.employee_checkbox').prop('checked', isChecked);
        update_selected_count();
    });

    $(document).on('change', '.employee_checkbox', function() {
        update_selected_count();
    });

    function update_selected_count() {
        var total = $('.employee_checkbox').length;
        var checked = $('.employee_checkbox:checked').length;
        $('#selected_count_badge').text(checked + ' Selected');
        if (checked === total && total > 0) {
            $('#select_all_employees').prop('checked', true);
        } else {
            $('#select_all_employees').prop('checked', false);
        }
    }

    // 2. Perform Status Change (Bulk or Single)
    function change_payment_status(userIds, status) {
        if (!userIds || userIds.length === 0) {
            alert('Please select at least one employee.');
            return;
        }

        var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
        var confirmMsg = 'Are you sure you want to mark ' + userIds.length + ' employee(s) as ' + statusLabel + '?';
        if (!confirm(confirmMsg)) return;

        var paymentMonth = '<?= !empty($payment_month) ? $payment_month : "" ?>';
        var deptId = '<?= !empty($departments_id) ? $departments_id : "" ?>';

        $.ajax({
            url: '<?= base_url("admin/payroll/update_bulk_payment_status") ?>',
            type: 'POST',
            data: {
                user_ids: userIds,
                status: status,
                payment_month: paymentMonth,
                departments_id: deptId
            },
            dataType: 'json',
            beforeSend: function() {
                $('body').css('cursor', 'wait');
            },
            success: function(res) {
                $('body').css('cursor', 'default');
                if (res && res.success) {
                    window.location.reload();
                } else {
                    alert(res ? res.message : 'Error updating payment status.');
                }
            },
            error: function() {
                $('body').css('cursor', 'default');
                window.location.reload();
            }
        });
    }

    // Bulk Buttons
    $('#btn_bulk_paid').on('click', function() {
        var selected = [];
        $('.employee_checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        change_payment_status(selected, 'paid');
    });

    $('#btn_bulk_hold').on('click', function() {
        var selected = [];
        $('.employee_checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        change_payment_status(selected, 'hold');
    });

    $('#btn_bulk_unpaid').on('click', function() {
        var selected = [];
        $('.employee_checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        change_payment_status(selected, 'unpaid');
    });

    // Single row status button
    $(document).on('click', '.btn-single-status', function(e) {
        e.preventDefault();
        var uid = $(this).data('user-id');
        var status = $(this).data('status');
        change_payment_status([uid], status);
    });
});
