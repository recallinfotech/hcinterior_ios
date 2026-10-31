<?= message_box('success'); ?>
<?= message_box('error'); ?>
<?= message_box('warning'); ?>

<style>
.face-stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 16px 18px;
    margin-bottom: 15px;
    border: 1px solid #e6e9ed;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.2s ease-in-out;
    display: block;
    text-decoration: none !important;
    color: inherit;
    position: relative;
    overflow: hidden;
}
.face-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    color: inherit;
}
.face-stat-card.active-filter {
    border-color: #23b7e5;
    box-shadow: 0 0 0 2px rgba(35, 183, 229, 0.35);
}
.face-stat-card .stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    float: left;
    margin-right: 14px;
}
.face-stat-card .stat-num {
    font-size: 22px;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 2px;
}
.face-stat-card .stat-title {
    font-size: 11px;
    color: #7f8c8d;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.face-thumb-wrapper {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #ddd;
    cursor: pointer;
    background: #f5f5f5;
    display: inline-block;
    transition: all 0.2s;
}
.face-thumb-wrapper:hover {
    border-color: #3498db;
    transform: scale(1.08);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.face-thumb-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.status-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.status-pill-pending {
    background: #fef5e7;
    color: #d35400;
    border: 1px solid #f8c471;
}
.status-pill-approved {
    background: #e8f8f5;
    color: #1e8449;
    border: 1px solid #a3e4d7;
}
.status-pill-rejected {
    background: #fdedec;
    color: #c0392b;
    border: 1px solid #f5b7b1;
}
.status-pill-reset {
    background: #ebf5fb;
    color: #2980b9;
    border: 1px solid #aed6f1;
}
.status-pill-none {
    background: #f4f6f7;
    color: #7f8c8d;
    border: 1px solid #d5dbdb;
}
.bulk-toolbar {
    background: #f8f9f9;
    border: 1px solid #e5e8e8;
    border-radius: 6px;
    padding: 10px 15px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.btn-action-group .btn {
    margin-right: 3px;
    margin-bottom: 3px;
}
.panel-heading .btn-heading-link {
    color: #1e293b !important;
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    font-weight: 600 !important;
    text-shadow: none !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08) !important;
    text-decoration: none !important;
}
.panel-heading .btn-heading-link:hover {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <header class="panel-heading" style="padding: 12px 18px; border-bottom: 1px solid #eaeaea;">
                <div class="panel-title" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <strong style="font-size: 15px; color: #ffffff;">
                            <i class="fa fa-id-badge text-primary" style="margin-right: 6px;"></i> 
                            <?= (lang('face_id_management') && lang('face_id_management') !== 'face_id_management') ? lang('face_id_management') : 'Face ID Management' ?>
                        </strong>
                        <span style="font-size: 12px; margin-left: 8px; font-weight: normal; color: #cbd5e1;">
                            Verify, Approve, Reject or Reset Face IDs for All Staff
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a href="<?= base_url('admin/attendance/time_history') ?>" class="btn btn-xs btn-heading-link" style="color: #1e293b !important; background: #ffffff !important; border: 1px solid #cbd5e1; font-weight: 600; padding: 5px 12px; border-radius: 4px;">
                            <i class="fa fa-calendar text-info"></i> Attendance Calendar
                        </a>
                        <a href="<?= base_url('admin/attendance/attendance_report') ?>" class="btn btn-xs btn-heading-link" style="color: #1e293b !important; background: #ffffff !important; border: 1px solid #cbd5e1; font-weight: 600; padding: 5px 12px; border-radius: 4px;">
                            <i class="fa fa-file-text-o text-warning"></i> Attendance Reports
                        </a>
                        <a href="<?= base_url('admin/attendance/office_locations') ?>" class="btn btn-primary btn-xs" style="font-weight: 600; padding: 5px 14px; border-radius: 4px;">
                            <i class="fa fa-map-marker"></i> Office Location & Geofence
                        </a>
                    </div>
                </div>
            </header>

            <div class="panel-body">
                <!-- 5 STATUS SUMMARY CLICKABLE CARDS -->
                <div class="row" style="margin-bottom: 5px;">
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <a href="<?= base_url('admin/attendance/face_id_requests') ?>" 
                           class="face-stat-card <?= empty($status_filter) ? 'active-filter' : '' ?>" title="View All Staff">
                            <div class="stat-icon" style="background: #eaf2f8; color: #2980b9;">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="stat-num" style="color: #2980b9;"><?= !empty($total_employees) ? $total_employees : 0 ?></div>
                            <div class="stat-title">All Staff</div>
                        </a>
                    </div>

                    <div class="col-md-3 col-sm-4 col-xs-6">
                        <a href="<?= base_url('admin/attendance/face_id_requests?status=pending') ?>" 
                           class="face-stat-card <?= $status_filter === 'pending' ? 'active-filter' : '' ?>" title="Filter Pending Requests">
                            <div class="stat-icon" style="background: #fef5e7; color: #d35400;">
                                <i class="fa fa-clock-o <?= !empty($pending_count) ? 'fa-spin' : '' ?>"></i>
                            </div>
                            <div class="stat-num" style="color: #d35400;">
                                <span id="stat_pending_cnt"><?= !empty($pending_count) ? $pending_count : 0 ?></span>
                                <?php if (!empty($pending_count)) { ?>
                                    <small class="label label-warning" style="font-size: 9px; vertical-align: middle;">ACTION NEEDED</small>
                                <?php } ?>
                            </div>
                            <div class="stat-title">⏳ Pending Approval</div>
                        </a>
                    </div>

                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <a href="<?= base_url('admin/attendance/face_id_requests?status=approved') ?>" 
                           class="face-stat-card <?= $status_filter === 'approved' ? 'active-filter' : '' ?>" title="Filter Approved Face IDs">
                            <div class="stat-icon" style="background: #e8f8f5; color: #27ae60;">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="stat-num" style="color: #27ae60;" id="stat_approved_cnt"><?= !empty($approved_count) ? $approved_count : 0 ?></div>
                            <div class="stat-title">✅ Approved</div>
                        </a>
                    </div>

                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <a href="<?= base_url('admin/attendance/face_id_requests?status=rejected') ?>" 
                           class="face-stat-card <?= $status_filter === 'rejected' ? 'active-filter' : '' ?>" title="Filter Rejected Face IDs">
                            <div class="stat-icon" style="background: #fdedec; color: #c0392b;">
                                <i class="fa fa-times-circle"></i>
                            </div>
                            <div class="stat-num" style="color: #c0392b;" id="stat_rejected_cnt"><?= !empty($rejected_count) ? $rejected_count : 0 ?></div>
                            <div class="stat-title">❌ Rejected</div>
                        </a>
                    </div>

                    <div class="col-md-3 col-sm-4 col-xs-6">
                        <a href="<?= base_url('admin/attendance/face_id_requests?status=reset') ?>" 
                           class="face-stat-card <?= $status_filter === 'reset' ? 'active-filter' : '' ?>" title="Filter Reset / Ready to Enroll">
                            <div class="stat-icon" style="background: #ebf5fb; color: #3498db;">
                                <i class="fa fa-refresh"></i>
                            </div>
                            <div class="stat-num" style="color: #3498db;" id="stat_reset_cnt"><?= !empty($reset_count) ? $reset_count : 0 ?></div>
                            <div class="stat-title">🔄 Reset (Re-enroll)</div>
                        </a>
                    </div>
                </div>

                <!-- SEARCH & FILTER ROW -->
                <form method="get" action="<?= base_url('admin/attendance/face_id_requests') ?>" class="form-inline" style="margin-bottom: 15px;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter ?? '') ?>">
                    
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only">Department</label>
                        <select name="department_id" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">-- All Departments --</option>
                            <?php if (!empty($all_departments)): foreach ($all_departments as $d): ?>
                                <option value="<?= $d->departments_id ?>" <?= ($dept_filter == $d->departments_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d->deptname) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only">Office Filter</label>
                        <select name="office_id" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">-- All Offices --</option>
                            <option value="none" <?= ($office_filter === 'none') ? 'selected' : '' ?>>⚪ Unassigned / General</option>
                            <?php if (!empty($all_offices)): foreach ($all_offices as $off): ?>
                                <option value="<?= $off->id ?>" <?= ($office_filter == $off->id) ? 'selected' : '' ?>>
                                    🏢 <?= htmlspecialchars($off->location_name) ?><?= ($off->is_default == 1) ? ' (Default)' : '' ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only">Status Filter</label>
                        <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="" <?= empty($status_filter) ? 'selected' : '' ?>>-- All Statuses --</option>
                            <option value="pending" <?= ($status_filter === 'pending') ? 'selected' : '' ?>>⏳ Pending Admin Approval</option>
                            <option value="approved" <?= ($status_filter === 'approved') ? 'selected' : '' ?>>✅ Approved Face ID</option>
                            <option value="rejected" <?= ($status_filter === 'rejected') ? 'selected' : '' ?>>❌ Rejected</option>
                            <option value="reset" <?= ($status_filter === 'reset') ? 'selected' : '' ?>>🔄 Reset (Ready to Re-enroll)</option>
                            <option value="not_enrolled" <?= ($status_filter === 'not_enrolled') ? 'selected' : '' ?>>⚪ Not Enrolled</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right: 8px;">
                        <input type="text" name="search" class="form-control input-sm" placeholder="Search employee name, ID..." 
                               value="<?= htmlspecialchars($search_query ?? '') ?>" style="min-width: 220px;">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    
                    <?php if (!empty($status_filter) || !empty($dept_filter) || !empty($search_query)): ?>
                        <a href="<?= base_url('admin/attendance/face_id_requests') ?>" class="btn btn-default btn-sm" title="Clear all filters">
                            <i class="fa fa-times"></i> Clear Filter
                        </a>
                    <?php endif; ?>
                </form>

                <!-- BULK ACTIONS TOOLBAR -->
                <div class="bulk-toolbar">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <label style="margin: 0; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" id="check_all_staff" style="margin: 0; width: 16px; height: 16px;">
                            <span>Select All</span>
                        </label>
                        <button type="button" class="btn btn-default btn-xs" id="btn_select_pending">
                            <i class="fa fa-check-square-o text-warning"></i> Select All Pending
                        </button>
                        <span class="badge" style="background: #34495e; font-size: 11px;" id="selected_counter">0 selected</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 6px;">
                        <strong style="font-size: 11px; text-transform: uppercase; color: #7f8c8d;">Bulk Actions:</strong>
                        <button type="button" class="btn btn-success btn-sm" id="btn_bulk_approve" disabled>
                            <i class="fa fa-check"></i> Bulk Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="btn_bulk_reject" disabled>
                            <i class="fa fa-times"></i> Bulk Reject
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" id="btn_bulk_reset" disabled>
                            <i class="fa fa-refresh"></i> Bulk Reset
                        </button>
                    </div>
                </div>

                <!-- MAIN EMPLOYEES & FACE ID REQUESTS TABLE -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover DataTables" id="tbl_face_requests" width="100%">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="width: 30px; text-align: center;">#</th>
                                <th style="width: 230px;">Employee Details</th>
                                <th style="width: 150px;">Department & Designation</th>
                                <th style="width: 170px; text-align: center;">Assigned Office</th>
                                <th style="width: 100px; text-align: center;">Face ID Photo</th>
                                <th style="width: 140px;">Enrollment Details</th>
                                <th style="width: 130px; text-align: center;">Approval Status</th>
                                <th style="width: 120px; text-align: center;">Outside Punch</th>
                                <th style="width: 180px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($face_requests)): foreach ($face_requests as $row): 
                                $is_enrolled = (!empty($row->face_descriptor));
                                $status = !empty($row->biometric_id) ? (int)$row->is_approved : 2;
                                
                                // Face image resolution: thumbnail or base64 or avatar
                                $face_img = null;
                                if (!empty($row->face_image_path)) {
                                    $face_img = base_url($row->face_image_path);
                                } elseif (!empty($row->face_image_base64)) {
                                    $face_img = $row->face_image_base64;
                                } elseif (!empty($row->avatar)) {
                                    $face_img = base_url($row->avatar);
                                }
                            ?>
                            <tr id="row_user_<?= $row->user_id ?>" data-user-id="<?= $row->user_id ?>" data-status="<?= $status ?>">
                                <td style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" class="user-select-chk" value="<?= $row->user_id ?>" 
                                           data-status="<?= $status ?>" style="width: 16px; height: 16px;">
                                </td>

                                <td style="vertical-align: middle;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="<?= !empty($row->avatar) ? base_url($row->avatar) : base_url('uploads/default_avatar.jpg') ?>" 
                                             class="img-circle" style="width: 36px; height: 36px; object-fit: cover; border: 1px solid #ddd;"
                                             onerror="this.src='<?= base_url('uploads/default_avatar.jpg') ?>'">
                                        <div>
                                            <strong style="font-size: 13px; color: #2c3e50;"><?= htmlspecialchars($row->fullname ?: $row->username) ?></strong>
                                            <div style="font-size: 11px; color: #7f8c8d;">
                                                Emp ID: <strong class="text-primary"><?= htmlspecialchars($row->employment_id ?: 'EMP-' . $row->user_id) ?></strong>
                                            </div>
                                            <div style="font-size: 10px; color: #95a5a6;"><?= htmlspecialchars($row->email) ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td style="vertical-align: middle;">
                                    <strong style="color: #34495e; font-size: 12px;"><?= htmlspecialchars($row->deptname ?: 'Head Office') ?></strong>
                                    <div style="font-size: 11px; color: #7f8c8d;"><?= htmlspecialchars($row->designations ?: 'Staff') ?></div>
                                </td>

                                <td style="vertical-align: middle; text-align: center;">
                                    <select class="form-control input-sm select-user-office" data-user-id="<?= $row->user_id ?>" style="font-size: 11px; height: 30px; width: 100%; max-width: 160px; border-radius: 4px; display: inline-block;">
                                        <option value="">-- All / Default --</option>
                                        <?php if (!empty($all_offices)): foreach ($all_offices as $off): ?>
                                            <option value="<?= $off->id ?>" <?= ($row->office_location_id == $off->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($off->location_name) ?><?= ($off->is_default == 1) ? ' ★' : '' ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div id="office_feedback_<?= $row->user_id ?>" style="font-size: 10px; min-height: 14px; margin-top: 2px;"></div>
                                </td>

                                <td style="text-align: center; vertical-align: middle;">
                                    <?php if ($is_enrolled && !empty($face_img)): ?>
                                        <div class="face-thumb-wrapper" 
                                             onclick="openFacePreviewModal('<?= htmlspecialchars(addslashes($row->fullname ?: $row->username)) ?>', '<?= htmlspecialchars($row->employment_id ?: 'EMP-' . $row->user_id) ?>', '<?= $face_img ?>', '<?= $row->enrolled_at ? date('d M Y, h:i A', strtotime($row->enrolled_at)) : '' ?>', <?= $row->user_id ?>, <?= $status ?>)"
                                             title="Click to zoom Face Scan photo">
                                            <img src="<?= $face_img ?>" alt="Face Scan">
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 10px; font-style: italic;">
                                            <i class="fa fa-camera-retro"></i> No Photo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td style="vertical-align: middle; font-size: 11px;">
                                    <?php if ($is_enrolled): ?>
                                        <div><i class="fa fa-calendar-check-o text-success"></i> <strong><?= !empty($row->enrolled_at) ? date('d M Y', strtotime($row->enrolled_at)) : 'N/A' ?></strong></div>
                                        <div class="text-muted"><?= !empty($row->enrolled_at) ? date('h:i A', strtotime($row->enrolled_at)) : '' ?></div>
                                        <span class="label label-info" style="font-size: 9px;">64-D AI Biometrics</span>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fa fa-minus"></i> Not Registered</span>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: center; vertical-align: middle;" id="status_cell_<?= $row->user_id ?>">
                                    <?php if ($is_enrolled): ?>
                                        <?php if ($status === 0): ?>
                                            <span class="status-pill status-pill-pending">
                                                <i class="fa fa-clock-o fa-spin"></i> Pending Approval
                                            </span>
                                            <div style="font-size: 10px; color: #e67e22; margin-top: 3px;">
                                                <i class="fa fa-lock"></i> Punch Locked
                                            </div>
                                        <?php elseif ($status === 1): ?>
                                            <span class="status-pill status-pill-approved">
                                                <i class="fa fa-check-circle"></i> Approved
                                            </span>
                                            <?php if (!empty($row->approver_name)): ?>
                                                <div style="font-size: 9px; color: #27ae60; margin-top: 2px;">
                                                    By <?= htmlspecialchars($row->approver_name) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($status === 3): ?>
                                            <span class="status-pill status-pill-rejected" title="<?= htmlspecialchars($row->rejection_reason ?? '') ?>">
                                                <i class="fa fa-times-circle"></i> Rejected
                                            </span>
                                            <?php if (!empty($row->rejection_reason)): ?>
                                                <div style="font-size: 9px; color: #c0392b; margin-top: 2px; max-width: 140px; margin-left: auto; margin-right: auto; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="<?= htmlspecialchars($row->rejection_reason) ?>">
                                                    <?= htmlspecialchars($row->rejection_reason) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="status-pill status-pill-reset">
                                                <i class="fa fa-refresh"></i> Reset (Re-enroll)
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="status-pill status-pill-none">
                                            Not Enrolled
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: center; vertical-align: middle;" id="manual_cell_<?= $row->user_id ?>">
                                    <button type="button" 
                                            class="btn btn-xs <?= !empty($row->allow_manual_punch) ? 'btn-success' : 'btn-default' ?>"
                                            id="btn_manual_<?= $row->user_id ?>"
                                            onclick="toggleManualPunch(<?= $row->user_id ?>)"
                                            title="Click to toggle Outside / Field Punch permission for this employee"
                                            style="font-weight: 600; font-size: 11px; padding: 4px 9px; border-radius: 12px; transition: all 0.2s;">
                                        <i class="fa <?= !empty($row->allow_manual_punch) ? 'fa-check-circle' : 'fa-building-o' ?>"></i>
                                        <span id="label_manual_<?= $row->user_id ?>"><?= !empty($row->allow_manual_punch) ? 'Allowed (Field)' : 'Office Only' ?></span>
                                    </button>
                                </td>

                                <td style="text-align: right; vertical-align: middle;" class="btn-action-group" id="action_cell_<?= $row->user_id ?>">
                                    <?php if ($status === 0 && $is_enrolled): ?>
                                        <!-- Pending actions -->
                                        <button type="button" class="btn btn-success btn-xs" onclick="approveSingleFace(<?= $row->user_id ?>)" title="Approve Face ID">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs" onclick="openRejectModal(<?= $row->user_id ?>, '<?= htmlspecialchars(addslashes($row->fullname ?: $row->username)) ?>')" title="Reject Face ID">
                                            <i class="fa fa-times"></i> Reject
                                        </button>
                                        <button type="button" class="btn btn-warning btn-xs" onclick="resetSingleFace(<?= $row->user_id ?>)" title="Reset Face ID">
                                            <i class="fa fa-refresh"></i> Reset
                                        </button>
                                    <?php elseif ($status === 1): ?>
                                        <!-- Approved actions -->
                                        <button type="button" class="btn btn-warning btn-xs" onclick="resetSingleFace(<?= $row->user_id ?>)" title="Reset Face ID to allow re-enrollment">
                                            <i class="fa fa-refresh"></i> Reset Face ID
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs" onclick="openRejectModal(<?= $row->user_id ?>, '<?= htmlspecialchars(addslashes($row->fullname ?: $row->username)) ?>')" title="Reject Face ID">
                                            <i class="fa fa-times"></i> Reject
                                        </button>
                                    <?php elseif ($status === 3): ?>
                                        <!-- Rejected actions -->
                                        <button type="button" class="btn btn-success btn-xs" onclick="approveSingleFace(<?= $row->user_id ?>)" title="Re-approve Face ID">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-warning btn-xs" onclick="resetSingleFace(<?= $row->user_id ?>)" title="Reset to let user re-enroll">
                                            <i class="fa fa-refresh"></i> Reset
                                        </button>
                                    <?php else: ?>
                                        <!-- Reset or Not Enrolled -->
                                        <button type="button" class="btn btn-default btn-xs" onclick="resetSingleFace(<?= $row->user_id ?>)" title="Force Reset / Ready for Re-enrollment">
                                            <i class="fa fa-refresh"></i> Reset
                                        </button>
                                    <?php endif; ?>

                                    <a href="<?= base_url('admin/attendance/time_history?user_id=' . $row->user_id) ?>" 
                                       class="btn btn-default btn-xs" title="View Time History & Calendar">
                                        <i class="fa fa-calendar text-primary"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 30px;">
                                    <i class="fa fa-info-circle fa-2x" style="display: block; margin-bottom: 8px;"></i>
                                    No Face ID requests found matching your filter criteria.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: FACE PHOTO ZOOM PREVIEW -->
<div class="modal fade" id="modal_face_preview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background: #2c3e50; color: #fff; padding: 12px 15px;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title" style="font-size: 14px; font-weight: 700;">
                    <i class="fa fa-camera-retro"></i> Face ID Verification Photo
                </h4>
            </div>
            <div class="modal-body text-center" style="background: #fdfefe; padding: 20px;">
                <div style="width: 220px; height: 220px; margin: 0 auto 15px; border-radius: 50%; overflow: hidden; border: 4px solid #27ae60; box-shadow: 0 4px 15px rgba(0,0,0,0.15); background: #000;">
                    <img id="preview_face_img" src="" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h4 id="preview_user_name" style="margin: 0 0 4px; font-weight: 700; color: #2c3e50;"></h4>
                <p id="preview_emp_id" style="font-size: 12px; color: #7f8c8d; margin-bottom: 6px;"></p>
                <div id="preview_enrolled_time" style="font-size: 11px; color: #95a5a6; margin-bottom: 15px;"></div>

                <div id="preview_modal_actions" style="display: flex; justify-content: center; gap: 8px;">
                    <!-- Injected dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REJECTION REASON (INDIVIDUAL & BULK) -->
<div class="modal fade" id="modal_reject_reason" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 460px;">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header" style="background: #c0392b; color: #fff; padding: 12px 15px;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title" style="font-size: 14px; font-weight: 700;">
                    <i class="fa fa-times-circle"></i> Reject Face ID Request
                </h4>
            </div>
            <div class="modal-body" style="padding: 18px;">
                <input type="hidden" id="reject_target_mode" value="single"> <!-- 'single' or 'bulk' -->
                <input type="hidden" id="reject_target_user_id" value="">

                <p id="reject_modal_target_name" style="font-size: 13px; font-weight: 600; color: #2c3e50; margin-bottom: 12px;">
                    Select or enter a reason for rejecting this Face ID request:
                </p>

                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; font-weight: 600; color: #7f8c8d; text-transform: uppercase;">Quick Reasons:</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <button type="button" class="btn btn-default btn-xs reject-chip" onclick="setRejectReason('Blurry or dark photo')">Blurry or dark photo</button>
                        <button type="button" class="btn btn-default btn-xs reject-chip" onclick="setRejectReason('Face not clearly visible')">Face not clearly visible</button>
                        <button type="button" class="btn btn-default btn-xs reject-chip" onclick="setRejectReason('Photo does not match employee')">Photo mismatch</button>
                        <button type="button" class="btn btn-default btn-xs reject-chip" onclick="setRejectReason('Multiple faces detected')">Multiple faces detected</button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="reject_reason_text" style="font-size: 12px; font-weight: 600;">Reason / Remark for Employee:</label>
                    <textarea id="reject_reason_text" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 15px;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="btn_confirm_reject" onclick="submitRejection()">
                    <i class="fa fa-ban"></i> Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Base URLs for AJAX
var URL_APPROVE = "<?= base_url('admin/attendance/approve_face_id') ?>";
var URL_REJECT  = "<?= base_url('admin/attendance/reject_face_id') ?>";
var URL_RESET   = "<?= base_url('admin/attendance/reset_face_id') ?>";
var URL_BULK    = "<?= base_url('admin/attendance/bulk_face_id_action') ?>";

// Checkbox selection management
function updateBulkToolbar() {
    var checked = $('.user-select-chk:checked');
    var count = checked.length;
    $('#selected_counter').text(count + ' selected');

    if (count > 0) {
        $('#btn_bulk_approve, #btn_bulk_reject, #btn_bulk_reset').prop('disabled', false);
    } else {
        $('#btn_bulk_approve, #btn_bulk_reject, #btn_bulk_reset').prop('disabled', true);
    }
}

$(document).ready(function() {
    // Select All Toggle
    $('#check_all_staff').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.user-select-chk').prop('checked', isChecked);
        updateBulkToolbar();
    });

    // Select All Pending shortcut
    $('#btn_select_pending').on('click', function() {
        $('.user-select-chk').each(function() {
            var status = $(this).data('status');
            if (status === 0) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
        updateBulkToolbar();
    });

    // Row checkbox change
    $(document).on('change', '.user-select-chk', function() {
        updateBulkToolbar();
    });

    // Bulk Approve
    $('#btn_bulk_approve').on('click', function() {
        var userIds = getSelectedUserIds();
        if (userIds.length === 0) return;
        if (!confirm('Are you sure you want to BULK APPROVE ' + userIds.length + ' employee Face ID(s)? Attendance will be unlocked for them.')) return;

        executeBulkAction(userIds, 'approve', null);
    });

    // Bulk Reject
    $('#btn_bulk_reject').on('click', function() {
        var userIds = getSelectedUserIds();
        if (userIds.length === 0) return;

        $('#reject_target_mode').val('bulk');
        $('#reject_target_user_id').val('');
        $('#reject_modal_target_name').text('Reject ' + userIds.length + ' selected Face ID request(s):');
        $('#reject_reason_text').val('Face verification rejected by Admin in bulk review');
        $('#modal_reject_reason').modal('show');
    });

    // Bulk Reset
    $('#btn_bulk_reset').on('click', function() {
        var userIds = getSelectedUserIds();
        if (userIds.length === 0) return;
        if (!confirm('Are you sure you want to BULK RESET ' + userIds.length + ' employee Face ID(s)? They will be able to re-enroll fresh face scans.')) return;

        executeBulkAction(userIds, 'reset', null);
    });
});

function getSelectedUserIds() {
    var ids = [];
    $('.user-select-chk:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

// 1-Click Approve single user
function approveSingleFace(userId) {
    if (!confirm('Approve this employee\'s Face ID request?')) return;

    var btn = $('#action_cell_' + userId).find('.btn-success');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: URL_APPROVE,
        type: 'POST',
        data: { user_id: userId },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                updateRowAfterApproval(userId);
                showToastMessage(res.message, 'success');
            } else {
                alert(res.message || 'Error approving Face ID.');
            }
        },
        error: function() {
            alert('Server error processing approval.');
        }
    });
}

// 1-Click Reset single user
function resetSingleFace(userId) {
    if (!confirm('Reset this employee\'s Face ID? Once reset, they can re-enroll a fresh Face ID on the mobile app.')) return;

    var btn = $('#action_cell_' + userId).find('.btn-warning');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: URL_RESET,
        type: 'POST',
        data: { user_id: userId },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                updateRowAfterReset(userId);
                showToastMessage(res.message, 'info');
            } else {
                alert(res.message || 'Error resetting Face ID.');
            }
        },
        error: function() {
            alert('Server error processing reset.');
        }
    });
}

// Open Rejection Modal for single user
function openRejectModal(userId, userName) {
    $('#reject_target_mode').val('single');
    $('#reject_target_user_id').val(userId);
    $('#reject_modal_target_name').html('Reject Face ID for <strong>' + userName + '</strong>:');
    $('#reject_reason_text').val('Photo is blurry / does not match employee records');
    $('#modal_reject_reason').modal('show');
}

function setRejectReason(txt) {
    $('#reject_reason_text').val(txt);
}

// Submit Rejection (single or bulk)
function submitRejection() {
    var mode = $('#reject_target_mode').val();
    var reason = $('#reject_reason_text').val() || 'Face verification rejected by Admin';

    if (mode === 'single') {
        var userId = $('#reject_target_user_id').val();
        $('#btn_confirm_reject').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');

        $.ajax({
            url: URL_REJECT,
            type: 'POST',
            data: { user_id: userId, rejection_reason: reason },
            dataType: 'json',
            success: function(res) {
                $('#modal_reject_reason').modal('hide');
                $('#btn_confirm_reject').prop('disabled', false).html('<i class="fa fa-ban"></i> Confirm Rejection');
                if (res.status === 'success') {
                    updateRowAfterRejection(userId, reason);
                    showToastMessage(res.message, 'warning');
                } else {
                    alert(res.message || 'Error rejecting Face ID.');
                }
            },
            error: function() {
                $('#btn_confirm_reject').prop('disabled', false).html('<i class="fa fa-ban"></i> Confirm Rejection');
                alert('Server error processing rejection.');
            }
        });
    } else {
        // Bulk Reject
        var userIds = getSelectedUserIds();
        $('#modal_reject_reason').modal('hide');
        executeBulkAction(userIds, 'reject', reason);
    }
}

// Execute Bulk Action via AJAX
function executeBulkAction(userIds, action, reason) {
    var loadingMsg = 'Processing ' + action + ' for ' + userIds.length + ' users...';
    $('#btn_bulk_approve, #btn_bulk_reject, #btn_bulk_reset').prop('disabled', true);

    $.ajax({
        url: URL_BULK,
        type: 'POST',
        data: {
            user_ids: userIds,
            bulk_action: action,
            rejection_reason: reason
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                showToastMessage(res.message, 'success');
                // Update rows in DOM
                for (var i = 0; i < userIds.length; i++) {
                    var uid = userIds[i];
                    if (action === 'approve') updateRowAfterApproval(uid);
                    if (action === 'reject') updateRowAfterRejection(uid, reason);
                    if (action === 'reset') updateRowAfterReset(uid);
                }
                $('.user-select-chk').prop('checked', false);
                $('#check_all_staff').prop('checked', false);
                updateBulkToolbar();
            } else {
                alert(res.message || 'Bulk action failed.');
            }
        },
        error: function() {
            alert('Server error executing bulk action.');
        }
    });
}

// DOM row update helpers
function updateRowAfterApproval(userId) {
    $('#status_cell_' + userId).html(
        '<span class="status-pill status-pill-approved"><i class="fa fa-check-circle"></i> Approved</span>' +
        '<div style="font-size: 9px; color: #27ae60; margin-top: 2px;">Just Approved</div>'
    );
    $('#action_cell_' + userId).html(
        '<button type="button" class="btn btn-warning btn-xs" onclick="resetSingleFace(' + userId + ')" title="Reset Face ID"><i class="fa fa-refresh"></i> Reset Face ID</button> ' +
        '<button type="button" class="btn btn-danger btn-xs" onclick="openRejectModal(' + userId + ', \'Employee\')" title="Reject Face ID"><i class="fa fa-times"></i> Reject</button> ' +
        '<a href="<?= base_url('admin/attendance/time_history?user_id=') ?>' + userId + '" class="btn btn-default btn-xs"><i class="fa fa-calendar text-primary"></i></a>'
    );
    $('#row_user_' + userId).attr('data-status', '1');
    $('#row_user_' + userId).find('.user-select-chk').attr('data-status', '1');
}

function updateRowAfterRejection(userId, reason) {
    $('#status_cell_' + userId).html(
        '<span class="status-pill status-pill-rejected"><i class="fa fa-times-circle"></i> Rejected</span>' +
        '<div style="font-size: 9px; color: #c0392b; margin-top: 2px;">' + (reason || 'Rejected') + '</div>'
    );
    $('#action_cell_' + userId).html(
        '<button type="button" class="btn btn-success btn-xs" onclick="approveSingleFace(' + userId + ')" title="Re-approve Face ID"><i class="fa fa-check"></i> Approve</button> ' +
        '<button type="button" class="btn btn-warning btn-xs" onclick="resetSingleFace(' + userId + ')" title="Reset to allow re-enrollment"><i class="fa fa-refresh"></i> Reset</button> ' +
        '<a href="<?= base_url('admin/attendance/time_history?user_id=') ?>' + userId + '" class="btn btn-default btn-xs"><i class="fa fa-calendar text-primary"></i></a>'
    );
    $('#row_user_' + userId).attr('data-status', '3');
    $('#row_user_' + userId).find('.user-select-chk').attr('data-status', '3');
}

function updateRowAfterReset(userId) {
    $('#status_cell_' + userId).html(
        '<span class="status-pill status-pill-reset"><i class="fa fa-refresh"></i> Reset (Re-enroll)</span>'
    );
    $('#action_cell_' + userId).html(
        '<button type="button" class="btn btn-default btn-xs" onclick="resetSingleFace(' + userId + ')"><i class="fa fa-refresh"></i> Reset</button> ' +
        '<a href="<?= base_url('admin/attendance/time_history?user_id=') ?>' + userId + '" class="btn btn-default btn-xs"><i class="fa fa-calendar text-primary"></i></a>'
    );
    $('#row_user_' + userId).attr('data-status', '2');
    $('#row_user_' + userId).find('.user-select-chk').attr('data-status', '2');
}

// Face Preview Modal
function openFacePreviewModal(userName, empId, photoUrl, enrolledTime, userId, status) {
    $('#preview_user_name').text(userName);
    $('#preview_emp_id').text('Emp ID: ' + empId);
    $('#preview_enrolled_time').text(enrolledTime ? 'Enrolled at: ' + enrolledTime : '');
    $('#preview_face_img').attr('src', photoUrl);

    var actionsHtml = '';
    if (status === 0) {
        actionsHtml = 
            '<button type="button" class="btn btn-success btn-sm" onclick="$(\'#modal_face_preview\').modal(\'hide\'); approveSingleFace(' + userId + ')"><i class="fa fa-check"></i> Approve Face ID</button>' +
            '<button type="button" class="btn btn-danger btn-sm" onclick="$(\'#modal_face_preview\').modal(\'hide\'); openRejectModal(' + userId + ', \'' + userName.replace(/'/g, "\\'") + '\')"><i class="fa fa-times"></i> Reject</button>' +
            '<button type="button" class="btn btn-warning btn-sm" onclick="$(\'#modal_face_preview\').modal(\'hide\'); resetSingleFace(' + userId + ')"><i class="fa fa-refresh"></i> Reset</button>';
    } else if (status === 1) {
        actionsHtml = 
            '<span class="label label-success" style="font-size: 12px; padding: 6px 12px; margin-right: 8px;"><i class="fa fa-check-circle"></i> Approved</span>' +
            '<button type="button" class="btn btn-warning btn-sm" onclick="$(\'#modal_face_preview\').modal(\'hide\'); resetSingleFace(' + userId + ')"><i class="fa fa-refresh"></i> Reset Face ID</button>';
    } else if (status === 3) {
        actionsHtml = 
            '<button type="button" class="btn btn-success btn-sm" onclick="$(\'#modal_face_preview\').modal(\'hide\'); approveSingleFace(' + userId + ')"><i class="fa fa-check"></i> Re-Approve</button>' +
            '<button type="button" class="btn btn-warning btn-sm" onclick="$(\'#modal_face_preview\').modal(\'hide\'); resetSingleFace(' + userId + ')"><i class="fa fa-refresh"></i> Reset</button>';
    }
    $('#preview_modal_actions').html(actionsHtml);

    $('#modal_face_preview').modal('show');
}

function showToastMessage(msg, type) {
    // Check if toastr or alert exists
    if (typeof toastr !== 'undefined') {
        toastr[type || 'success'](msg);
    } else {
        var alertDiv = $('<div class="alert alert-' + (type === 'error' ? 'danger' : (type || 'success')) + ' alert-dismissible" style="position:fixed; top:20px; right:20px; z-index:99999; box-shadow:0 4px 15px rgba(0,0,0,0.2);">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            '<strong>' + (type === 'error' ? 'Error!' : 'Success!') + '</strong> ' + msg +
        '</div>');
        $('body').append(alertDiv);
        setTimeout(function() { alertDiv.fadeOut(function() { $(this).remove(); }); }, 4000);
    }
}

function toggleManualPunch(userId) {
    var btn = $('#btn_manual_' + userId);
    var label = $('#label_manual_' + userId);
    btn.prop('disabled', true).css('opacity', '0.6');
    
    $.ajax({
        url: '<?= base_url("admin/attendance/toggle_manual_punch") ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            user_id: userId,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        success: function(res) {
            btn.prop('disabled', false).css('opacity', '1');
            if (res && res.status) {
                if (res.allow_manual_punch == 1) {
                    btn.removeClass('btn-default').addClass('btn-success');
                    btn.find('i').removeClass('fa-building-o').addClass('fa-check-circle');
                    label.text('Allowed (Field)');
                } else {
                    btn.removeClass('btn-success').addClass('btn-default');
                    btn.find('i').removeClass('fa-check-circle').addClass('fa-building-o');
                    label.text('Office Only');
                }
                showToastMessage(res.message || 'Permission updated successfully', 'success');
            } else {
                alert(res.message || 'Failed to update permission');
            }
        },
        error: function(err) {
            btn.prop('disabled', false).css('opacity', '1');
            alert('Server error while toggling permission.');
        }
    });
}
window.toggleManualPunch = toggleManualPunch;

// GPS Auto-detect coordinates button
$('#btn_detect_gps').on('click', function() {
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Locating...');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            $('#cfg_office_lat').val(pos.coords.latitude.toFixed(6));
            $('#cfg_office_long').val(pos.coords.longitude.toFixed(6));
            btn.prop('disabled', false).html('<i class="fa fa-check text-success"></i> Detected!');
            setTimeout(function() { btn.html('<i class="fa fa-crosshairs text-primary"></i> Detect'); }, 2000);
        }, function(err) {
            alert('Could not fetch location: ' + err.message);
            btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-primary"></i> Detect');
        }, { enableHighAccuracy: true, timeout: 10000 });
    } else {
        alert('Geolocation not supported on this browser.');
        btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-primary"></i> Detect');
    }
});

// Handle User Office Assignment Dropdown Change
$(document).on('change', '.select-user-office', function() {
    var $select = $(this);
    var userId = $select.data('user-id');
    var officeId = $select.val();
    var $feedback = $('#office_feedback_' + userId);

    $feedback.html('<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Saving...</span>');
    $select.prop('disabled', true);

    $.ajax({
        url: "<?= base_url('admin/attendance/update_user_office') ?>",
        type: "POST",
        data: {
            user_id: userId,
            office_location_id: officeId,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        },
        dataType: "json",
        success: function(res) {
            $select.prop('disabled', false);
            if (res && res.status) {
                $feedback.html('<span class="text-success"><i class="fa fa-check"></i> ' + (res.office_name ? res.office_name : 'Saved') + '</span>');
                showToastMessage(res.message || 'Office assigned successfully', 'success');
                setTimeout(function() {
                    $feedback.fadeOut(400, function() {
                        $(this).html('').show();
                    });
                }, 2500);
            } else {
                $feedback.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Failed</span>');
                alert(res.message || 'Failed to update office assignment');
            }
        },
        error: function() {
            $select.prop('disabled', false);
            $feedback.html('<span class="text-danger"><i class="fa fa-times"></i> Error</span>');
            alert('Server error updating office assignment.');
        }
    });
});
</script>

<!-- Office Location & Geofence Settings Modal -->
<div class="modal fade" id="modal_office_location" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/attendance/save_office_location') ?>" method="post" class="form-horizontal">
                <div class="modal-header" style="background: #2b303a; color: #fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-map-marker text-warning"></i> Office Location & Geofence Settings</h4>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <p class="text-muted" style="margin-bottom: 18px; font-size: 12px;">
                        Set the office central coordinates and permitted radius. Mobile app attendance checks employee distance against these coordinates. Outsiders / on-site staff can punch in with Face ID and will be recorded as Present with their GPS distance logged.
                    </p>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Office Name / Address</label>
                        <div class="col-sm-8">
                            <input type="text" name="office_location" id="cfg_office_location" class="form-control" value="<?= html_escape($office_location ?? '') ?>" placeholder="e.g. HC Interior Head Office, Noida" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Latitude</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="text" name="office_lat" id="cfg_office_lat" class="form-control" value="<?= html_escape($office_lat ?? '28.535517') ?>" placeholder="28.535517" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" id="btn_detect_gps" title="Detect Current Browser Location">
                                        <i class="fa fa-crosshairs text-primary"></i> Detect
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Longitude</label>
                        <div class="col-sm-8">
                            <input type="text" name="office_long" id="cfg_office_long" class="form-control" value="<?= html_escape($office_long ?? '77.391029') ?>" placeholder="77.391029" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Allowed Radius (Meters)</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="number" name="allowed_radius" id="cfg_allowed_radius" class="form-control" value="<?= html_escape($allowed_radius ?? '250') ?>" placeholder="250" required min="50" max="50000">
                                <span class="input-group-addon">meters</span>
                            </div>
                            <span class="help-block" style="font-size: 11px; margin-bottom: 0;">Punches within this distance are tagged as <b>Office Range</b>. Punches outside are tagged as <b>Outsider / On-Site Punch</b>.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Enforce GPS Clock In</label>
                        <div class="col-sm-8">
                            <label class="checkbox-inline" style="padding-top: 5px;">
                                <input type="checkbox" name="allow_geo_clock_in" value="TRUE" <?= (!empty($allow_geo_clock_in) && $allow_geo_clock_in == 'TRUE') ? 'checked' : '' ?>>
                                Require GPS verification for Mobile Attendance
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Office Location</button>
                </div>
            </form>
        </div>
    </div>
</div>
