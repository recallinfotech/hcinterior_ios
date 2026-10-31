<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row" style="margin-bottom: 20px;">
    <!-- Metric Cards -->
    <div class="col-md-3 col-sm-6">
        <div class="panel widget bg-primary" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div class="row row-table">
                <div class="col-xs-4 text-center pv-lg" style="background: rgba(0,0,0,0.1);">
                    <em class="fa fa-users fa-3x"></em>
                </div>
                <div class="col-xs-8 pv-lg">
                    <div class="h2 m0 text-bold"><?= $count_all ?></div>
                    <div class="text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Employees</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel widget bg-warning" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background-color: #f59e0b !important;">
            <div class="row row-table">
                <div class="col-xs-4 text-center pv-lg" style="background: rgba(0,0,0,0.1);">
                    <em class="fa fa-clock-o fa-3x"></em>
                </div>
                <div class="col-xs-8 pv-lg">
                    <div class="h2 m0 text-bold"><?= $count_pending ?></div>
                    <div class="text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Pending Approval</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel widget bg-success" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background-color: #10b981 !important;">
            <div class="row row-table">
                <div class="col-xs-4 text-center pv-lg" style="background: rgba(0,0,0,0.1);">
                    <em class="fa fa-check-circle fa-3x"></em>
                </div>
                <div class="col-xs-8 pv-lg">
                    <div class="h2 m0 text-bold"><?= $count_approved ?></div>
                    <div class="text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Approved / Verified</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel widget bg-danger" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background-color: #ef4444 !important;">
            <div class="row row-table">
                <div class="col-xs-4 text-center pv-lg" style="background: rgba(0,0,0,0.1);">
                    <em class="fa fa-exclamation-triangle fa-3x"></em>
                </div>
                <div class="col-xs-8 pv-lg">
                    <div class="h2 m0 text-bold"><?= $count_rejected ?></div>
                    <div class="text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Rejected / Changes Req.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-custom" style="border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <div class="panel-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">
            <i class="fa fa-id-card-o text-primary" style="margin-right: 8px;"></i> Employee Employment Application & KYC Verification
        </div>
        <div class="btn-group">
            <a href="<?= base_url('admin/employment_applications/index/all') ?>" class="btn btn-xs <?= ($filter == 'all') ? 'btn-primary' : 'btn-default' ?>">All (<?= $count_all ?>)</a>
            <a href="<?= base_url('admin/employment_applications/index/pending') ?>" class="btn btn-xs <?= ($filter == 'pending') ? 'btn-warning' : 'btn-default' ?>">Pending (<?= $count_pending ?>)</a>
            <a href="<?= base_url('admin/employment_applications/index/approved') ?>" class="btn btn-xs <?= ($filter == 'approved') ? 'btn-success' : 'btn-default' ?>">Approved (<?= $count_approved ?>)</a>
            <a href="<?= base_url('admin/employment_applications/index/rejected') ?>" class="btn btn-xs <?= ($filter == 'rejected') ? 'btn-danger' : 'btn-default' ?>">Rejected (<?= $count_rejected ?>)</a>
        </div>
    </div>

    <div class="panel-body" style="padding: 20px;">
        <div class="table-responsive">
            <table class="table table-striped table-hover dataTable" id="Transation_DataTables">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="width: 50px;">Photo</th>
                        <th>Employee Details</th>
                        <th>Designation / Dept</th>
                        <th>Aadhaar & PAN</th>
                        <th>Experience</th>
                        <th>Status</th>
                        <th>Submitted / Updated</th>
                        <th style="text-align: right; width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_employees)): foreach ($all_employees as $emp): 
                        $status = $emp->app_status ?? 'not_submitted';
                        $avatar_url = !empty($emp->avatar) ? base_url($emp->avatar) : base_url('assets/img/user/default_avatar.jpg');
                    ?>
                    <tr style="vertical-align: middle;">
                        <td>
                            <img src="<?= $avatar_url ?>" alt="Avatar" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 13px;">
                                <a href="<?= base_url('admin/employment_applications/view/' . $emp->user_id) ?>" style="color: #0284c7;">
                                    <?= html_escape($emp->fullname ?: $emp->username) ?>
                                </a>
                            </div>
                            <div style="font-size: 11px; color: #64748b;">
                                <?php if (!empty($emp->employment_id)): ?>
                                    <span class="badge" style="background: #e2e8f0; color: #334155; font-weight: 600;"><?= html_escape($emp->employment_id) ?></span> &bull; 
                                <?php endif; ?>
                                <i class="fa fa-phone"></i> <?= html_escape($emp->mobile ?: 'N/A') ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 12px;"><?= html_escape($emp->designations ?: 'Staff') ?></div>
                            <div style="font-size: 11px; color: #64748b;"><?= html_escape($emp->deptname ?: 'HC Interior') ?></div>
                        </td>
                        <td>
                            <div style="font-size: 11px;">
                                <strong>UID:</strong> <?= !empty($emp->aadhaar_no) ? html_escape($emp->aadhaar_no) : '<span class="text-muted">Not filled</span>' ?><br>
                                <strong>PAN:</strong> <?= !empty($emp->pan_no) ? html_escape($emp->pan_no) : '<span class="text-muted">Not filled</span>' ?>
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 12px; font-weight: 600; color: #334155;">
                                <?= !empty($emp->total_experience_years) ? html_escape($emp->total_experience_years) . ' yrs' : 'Fresher' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($status === 'approved'): ?>
                                <span class="label label-success" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                                    <i class="fa fa-check"></i> Approved & Verified
                                </span>
                            <?php elseif ($status === 'submitted'): ?>
                                <span class="label label-warning" style="font-size: 11px; padding: 4px 8px; border-radius: 12px; background: #f59e0b;">
                                    <i class="fa fa-clock-o"></i> Pending HR Review
                                </span>
                            <?php elseif ($status === 'rejected'): ?>
                                <span class="label label-danger" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                                    <i class="fa fa-times"></i> Rejected / Needs Fix
                                </span>
                            <?php elseif ($status === 'draft'): ?>
                                <span class="label label-default" style="font-size: 11px; padding: 4px 8px; border-radius: 12px;">
                                    <i class="fa fa-pencil"></i> Incomplete Draft
                                </span>
                            <?php else: ?>
                                <span class="label label-default" style="font-size: 11px; padding: 4px 8px; border-radius: 12px; opacity: 0.6;">
                                    Not Submitted
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="font-size: 11px; color: #64748b;">
                                <?= !empty($emp->app_updated_at) ? date('d M Y, h:i A', strtotime($emp->app_updated_at)) : '-' ?>
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="<?= base_url('admin/employment_applications/view/' . $emp->user_id) ?>" class="btn btn-xs btn-info" title="View Full Application Form" style="border-radius: 4px;">
                                <i class="fa fa-eye"></i> View
                            </a>
                            <a href="<?= base_url('admin/employment_applications/edit/' . $emp->user_id) ?>" class="btn btn-xs btn-primary" title="Edit Form" style="border-radius: 4px;">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                            <?php if ($status === 'submitted' || $status === 'draft'): ?>
                                <a href="<?= base_url('admin/employment_applications/approve/' . $emp->user_id) ?>" class="btn btn-xs btn-success" title="Quick Approve & Verify" onclick="return confirm('Kya aap is application ko verify aur approve karna chahte hain?');" style="border-radius: 4px;">
                                    <i class="fa fa-check"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="8" class="text-center pv-lg text-muted">No employee applications found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
