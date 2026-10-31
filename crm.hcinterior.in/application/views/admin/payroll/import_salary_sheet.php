<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<style type="text/css">
    .salary-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        overflow: hidden;
    }
    .salary-card-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .salary-card-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        color: #fff;
    }
    .salary-card-body {
        padding: 22px;
    }
    .badge-pill-soft {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-present { background: #dcfce7; color: #166534; }
    .badge-absent { background: #fee2e2; color: #991b1b; }
    .badge-paid { background: #e0f2fe; color: #075985; }
    .upload-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 25px 20px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color 0.2s, background-color 0.2s;
    }
    .upload-dropzone:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="salary-card">
            <div class="salary-card-header">
                <div>
                    <h3 class="salary-card-title">
                        <i class="fa fa-file-excel-o text-success"></i> ⚡ High Creation Salary Slip Generator &amp; Excel/CSV Import
                    </h3>
                    <small style="color: #cbd5e1;">Generate company-standard PDF salary slips with automated attendance &amp; formulas from live CRM or uploaded Excel/CSV sheet.</small>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="<?= base_url('admin/payroll/download_sample_salary_csv?month=' . $payment_month) ?>" class="btn btn-sm btn-info" style="font-weight: 600;">
                        <i class="fa fa-download"></i> Download Sample CSV
                    </a>
                    <a href="<?= base_url('admin/payroll/generate_payslip') ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-list"></i> Regular Payslip View
                    </a>
                </div>
            </div>

            <div class="salary-card-body">
                <form action="<?= base_url('admin/payroll/import_salary_sheet') ?>" method="post" enctype="multipart/form-data" class="form-horizontal">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group" style="margin: 0 0 15px 0;">
                                <label style="font-weight: 600; margin-bottom: 6px; display:block;">Select Salary Month <span class="text-danger">*</span></label>
                                <input type="month" name="payment_month" class="form-control" value="<?= htmlspecialchars($payment_month) ?>" required>
                                <small class="text-muted">Target salary period</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group" style="margin: 0 0 15px 0;">
                                <label style="font-weight: 600; margin-bottom: 6px; display:block;">
                                    <i class="fa fa-calendar-check-o text-success"></i> Salary Count Days / Month <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="1" max="31" name="salary_count_days" class="form-control" value="<?= htmlspecialchars(!empty($salary_count_days) ? $salary_count_days : '26') ?>" required>
                                    <span class="input-group-btn">
                                        <button type="submit" formaction="<?= base_url('admin/payroll/save_salary_settings') ?>" class="btn btn-default" title="Save this count day setting" style="font-weight: 600;">Save</button>
                                    </span>
                                </div>
                                <small class="text-muted">Per day = CTC / <?= htmlspecialchars(!empty($salary_count_days) ? $salary_count_days : '26') ?></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group" style="margin: 0 0 15px 0;">
                                <label style="font-weight: 600; margin-bottom: 6px; display:block;">Upload Excel / CSV Sheet (Optional if generating from CRM Attendance)</label>
                                <input type="file" name="salary_csv" accept=".csv,text/csv,application/vnd.ms-excel" class="form-control" style="padding: 4px;">
                                <small class="text-muted">Matches columns: Employee ID, Name, CTC, Absent, Week Off, Leaves, Bank Acc, etc.</small>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap; gap: 10px;">
                        <div class="text-muted" style="font-size: 12px;">
                            <i class="fa fa-info-circle text-primary"></i> <strong>Tip:</strong> If no CSV is uploaded, clicking <em>"Auto-Generate from CRM Attendance"</em> will automatically compute punches, week offs, holidays, and leaves for all staff!
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <?php if (!empty($raw_csv_content)) : ?>
                                <textarea name="cached_csv" style="display:none;"><?= htmlspecialchars($raw_csv_content) ?></textarea>
                            <?php endif; ?>
                            <button type="submit" name="process_csv" value="1" class="btn btn-primary" style="font-weight: 700; padding: 7px 18px;">
                                <i class="fa fa-cogs"></i> Process &amp; Generate Slips
                            </button>
                            <?php if (!empty($parsed_slips)) : ?>
                                <button type="submit" name="process_csv" value="1" formaction="<?= base_url('admin/payroll/import_salary_sheet') ?>" onclick="$(this).closest('form').append('<input type=\'hidden\' name=\'export_bulk_pdf\' value=\'1\'>')" class="btn btn-success" style="font-weight: 700;">
                                    <i class="fa fa-file-pdf-o"></i> Download All as Multi-Page PDF
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($parsed_slips)) : ?>
<div class="row">
    <div class="col-md-12">
        <div class="salary-card">
            <div class="salary-card-header" style="background:#0f172a;">
                <div>
                    <h4 class="salary-card-title">
                        <i class="fa fa-table text-warning"></i> Calculated Salary Slips Preview for <?= date('F Y', strtotime($payment_month . '-01')) ?>
                        <span class="badge" style="background: #3b82f6; margin-left: 6px;"><?= count($parsed_slips) ?> Staff</span>
                    </h4>
                </div>
                <div>
                    <a href="<?= base_url('admin/payroll/bulk_high_creation_pdf?payment_month=' . $payment_month) ?>" target="_blank" class="btn btn-success btn-sm" style="font-weight: 700;">
                        <i class="fa fa-file-pdf-o"></i> Download All PDF (Bulk Multi-Page)
                    </a>
                </div>
            </div>

            <div class="table-responsive" style="padding: 15px;">
                <table class="table table-striped table-hover table-bordered" id="table_parsed_salary_slips" style="font-size: 12px; margin-bottom: 0;">
                    <thead style="background: #f1f5f9; text-transform: uppercase; font-size: 11px;">
                        <tr>
                            <th style="width: 30px; text-align: center;">#</th>
                            <th>Employee Code &amp; Name</th>
                            <th>Department &amp; Designation</th>
                            <th style="text-align: right;">CTC Salary</th>
                            <th style="text-align: center;">Attendance Breakdown</th>
                            <th style="text-align: center;">Paid / Total</th>
                            <th style="text-align: right;">Absent Deduct</th>
                            <th style="text-align: right;">Gross Salary</th>
                            <th style="text-align: right; color:#166534;">Net Payable</th>
                            <th style="text-align: center; width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $s_idx = 1;
                        foreach ($parsed_slips as $slip) : 
                        ?>
                        <tr>
                            <td style="text-align: center; vertical-align: middle;"><?= $s_idx++ ?></td>
                            <td style="vertical-align: middle;">
                                <strong style="color: #0f172a; font-size: 13px;"><?= htmlspecialchars($slip['emp_name']) ?></strong>
                                <div style="color: #64748b; font-size: 11px;">
                                    <span class="label label-default"><?= htmlspecialchars($slip['emp_code']) ?></span> &bull; 
                                    DOJ: <?= htmlspecialchars($slip['joining_date_formatted']) ?>
                                </div>
                            </td>
                            <td style="vertical-align: middle;">
                                <div><?= htmlspecialchars($slip['dept_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($slip['designation_name']) ?></small>
                            </td>
                            <td style="text-align: right; vertical-align: middle; font-weight: 700; color: #1e293b;">
                                ₹<?= number_format($slip['salary_ctc'], 2) ?>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <span class="badge-pill-soft badge-present" title="Present Days">P: <?= number_format($slip['present_days'], 1) ?></span>
                                <span class="badge-pill-soft" style="background:#fef3c7; color:#92400e;" title="Week Off">WO: <?= number_format($slip['week_off_days'], 1) ?></span>
                                <span class="badge-pill-soft" style="background:#e0e7ff; color:#3730a3;" title="Leaves (CL+EL+SL)">L: <?= number_format($slip['cl_avail_days'] + $slip['el_avail_days'] + $slip['sl_avail_days'], 1) ?></span>
                                <?php if ($slip['absent_days'] > 0) : ?>
                                    <span class="badge-pill-soft badge-absent" title="Absent Days">A: <?= number_format($slip['absent_days'], 1) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; vertical-align: middle; font-weight: 600;">
                                <span class="badge-pill-soft badge-paid">
                                    <?= number_format($slip['paid_days'], 1) ?> / <?= number_format($slip['total_working_days'], 0) ?>
                                </span>
                            </td>
                            <td style="text-align: right; vertical-align: middle; color: #dc2626; font-weight: 600;">
                                <?= ($slip['absent_deduction'] > 0) ? '-₹' . number_format($slip['absent_deduction'], 2) : '₹0.00' ?>
                            </td>
                            <td style="text-align: right; vertical-align: middle; font-weight: 600;">
                                ₹<?= number_format($slip['gross_salary'], 2) ?>
                            </td>
                            <td style="text-align: right; vertical-align: middle; font-weight: 800; font-size: 13px; color: #15803d;">
                                ₹<?= number_format($slip['net_payable'], 2) ?>
                            </td>
                            <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                <a href="<?= base_url('admin/payroll/high_creation_payslip_pdf/' . (!empty($slip['user_id']) ? $slip['user_id'] : $slip['emp_code']) . '?month=' . $payment_month) ?>" 
                                   target="_blank" class="btn btn-xs btn-primary" title="Download High Creation PDF Salary Slip" style="font-weight: 600;">
                                    <i class="fa fa-download"></i> PDF
                                </a>
                                <a href="<?= base_url('admin/payroll/high_creation_payslip_view/' . (!empty($slip['user_id']) ? $slip['user_id'] : $slip['emp_code']) . '?month=' . $payment_month) ?>" 
                                   target="_blank" class="btn btn-xs btn-default" title="View Full Slip Preview in Browser">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function() {
    if ($('#table_parsed_salary_slips').length) {
        $('#table_parsed_salary_slips').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']]
        });
    }
});
</script>
