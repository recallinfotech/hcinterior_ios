<?= message_box('messages'); ?>
<?= message_box('errors'); ?>

<style>
.att-card {
    border-radius: 6px;
    padding: 14px 18px;
    margin-bottom: 20px;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.att-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.14);
}
.card-total   { background: linear-gradient(135deg, #3498db, #2980b9); }
.card-present { background: linear-gradient(135deg, #2ecc71, #27ae60); }
.card-pending { background: linear-gradient(135deg, #f39c12, #d35400); }
.card-absent  { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.card-leave   { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

.att-count { font-size: 26px; font-weight: 700; line-height: 1.1; }
.att-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; margin-top: 4px; }
.att-icon  { font-size: 32px; opacity: 0.3; }

.thumb-preview {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ddd;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.thumb-preview:hover {
    transform: scale(1.15);
    border-color: #23b7e5;
}
.table-team th {
    background: #f5f7fa;
    color: #2c3e50;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
}
.table-team td {
    vertical-align: middle !important;
}
</style>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>
                        <i class="fa fa-users text-primary"></i> <strong><?= $title ?></strong>
                        <span class="text-muted text-sm" style="font-size: 13px; font-weight: normal; margin-left: 10px;">
                            <i class="fa fa-calendar"></i> <?= date('d M Y (l)', strtotime($selected_date)) ?>
                        </span>
                    </h4>
                </div>
            </div>

            <div class="panel-body">
                <!-- Filters Bar -->
                <form method="get" action="<?= base_url('admin/attendance/team_attendance') ?>" class="form-inline" style="margin-bottom: 22px; padding: 12px 15px; background: #f9fbfd; border-radius: 6px; border: 1px solid #eef2f5;">
                    <div class="form-group" style="margin-right: 15px;">
                        <label style="margin-right: 6px; font-weight: 600;"><i class="fa fa-calendar"></i> <?= lang('date') ?>:</label>
                        <div class="input-group">
                            <input type="text" name="date" class="form-control input-sm datepicker" value="<?= $selected_date ?>" data-date-format="yyyy-mm-dd" style="width: 140px;">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>

                    <?php if (!empty($available_departments) && count($available_departments) > 1): ?>
                    <div class="form-group" style="margin-right: 15px;">
                        <label style="margin-right: 6px; font-weight: 600;"><i class="fa fa-sitemap"></i> <?= lang('department') ?>:</label>
                        <select name="department_id" class="form-control input-sm" style="min-width: 180px;">
                            <option value=""><?= lang('all') ?> <?= lang('departments') ?> (<?= count($available_departments) ?>)</option>
                            <?php foreach ($available_departments as $dept): ?>
                                <option value="<?= $dept->departments_id ?>" <?= (!empty($selected_dept_id) && $selected_dept_id == $dept->departments_id) ? 'selected' : '' ?>>
                                    <?= $dept->deptname ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> <?= lang('filter') ?></button>
                    <?php if ($selected_date !== date('Y-m-d') || !empty($selected_dept_id)): ?>
                        <a href="<?= base_url('admin/attendance/team_attendance') ?>" class="btn btn-default btn-sm" style="margin-left: 5px;"><i class="fa fa-refresh"></i> <?= lang('reset') ?></a>
                    <?php endif; ?>
                </form>

                <!-- Summary Cards Row -->
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                        <div class="att-card card-total">
                            <div>
                                <div class="att-count"><?= $summary['total_members'] ?></div>
                                <div class="att-label">Total Team</div>
                            </div>
                            <div class="att-icon"><i class="fa fa-users"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                        <div class="att-card card-present">
                            <div>
                                <div class="att-count"><?= $summary['present'] ?></div>
                                <div class="att-label">Present</div>
                            </div>
                            <div class="att-icon"><i class="fa fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                        <div class="att-card card-pending" style="cursor: pointer;" onclick="$('a[href=\'#tab_pending_approvals\']').tab('show');">
                            <div>
                                <div class="att-count" id="summary_pending_count"><?= $summary['pending'] ?></div>
                                <div class="att-label">Out of Range Pending</div>
                            </div>
                            <div class="att-icon"><i class="fa fa-clock-o"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                        <div class="att-card card-absent">
                            <div>
                                <div class="att-count"><?= $summary['absent'] ?></div>
                                <div class="att-label">Absent</div>
                            </div>
                            <div class="att-icon"><i class="fa fa-times-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4 col-xs-6" style="width: 20%;">
                        <div class="att-card card-leave">
                            <div>
                                <div class="att-count"><?= $summary['leave'] ?></div>
                                <div class="att-label">On Leave</div>
                            </div>
                            <div class="att-icon"><i class="fa fa-calendar-minus-o"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="nav-tabs-custom" style="margin-top: 10px;">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab_daily_attendance" data-toggle="tab">
                                <i class="fa fa-list text-primary"></i> <strong>Team Attendance Sheet</strong>
                                <span class="badge" style="background: #3498db; margin-left: 4px;"><?= count($attendance_data) ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="#tab_pending_approvals" data-toggle="tab">
                                <i class="fa fa-map-marker text-danger"></i> <strong>Out-of-Range Approvals</strong>
                                <span class="badge" style="background: #e67e22; margin-left: 4px;" id="badge_pending_approvals"><?= $pending_count ?></span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" style="padding-top: 20px;">
                        <!-- TAB 1: Daily Attendance Sheet -->
                        <div class="tab-pane active" id="tab_daily_attendance">
                            <?php if (empty($attendance_data)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> No employees found in your department.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover table-team dataTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">Photo</th>
                                                <th>Employee Name</th>
                                                <th>Department / Designation</th>
                                                <th>Status</th>
                                                <th>Clock In</th>
                                                <th>Clock Out</th>
                                                <th>Duration</th>
                                                <th>Location / Distance</th>
                                                <th style="width: 100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance_data as $row): 
                                                $mem = $row['member'];
                                                $photo_url = !empty($row['photo']) ? base_url($row['photo']) : (!empty($mem->avatar) ? base_url($mem->avatar) : base_url('assets/img/user/default_avatar.jpg'));
                                            ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <img src="<?= $photo_url ?>" class="thumb-preview" alt="<?= htmlspecialchars($mem->fullname) ?>" onclick="openPhotoModal('<?= $photo_url ?>', '<?= htmlspecialchars($mem->fullname) ?>', '<?= $row['clockin_time'] ?>', '<?= htmlspecialchars($row['address']) ?>', '<?= $row['distance'] ?>')" title="Click to view full photo">
                                                    </td>
                                                    <td>
                                                        <strong><?= $mem->fullname ?></strong>
                                                        <br>
                                                        <small class="text-muted"><i class="fa fa-id-badge"></i> <?= $mem->emp_code ?: ($mem->employment_id ?: $mem->username) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="text-bold"><?= $mem->deptname ?></span>
                                                        <br>
                                                        <small class="text-muted"><?= $mem->designations ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="label label-<?= $row['status_class'] ?>" style="font-size: 11px; padding: 4px 8px;">
                                                            <?= $row['status_label'] ?>
                                                        </span>
                                                        <?php if ($row['is_inside_geo'] === 0): ?>
                                                            <br><span class="label label-warning" style="font-size: 9px; margin-top: 3px; display: inline-block;">Outside Office</span>
                                                        <?php elseif ($row['is_inside_geo'] === 1): ?>
                                                            <br><span class="label label-success" style="font-size: 9px; margin-top: 3px; display: inline-block;">In Office</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="text-bold"><?= $row['clockin_time'] ?></span>
                                                    </td>
                                                    <td>
                                                        <?= $row['clockout_time'] ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background: #6c7a89;"><?= $row['work_duration'] ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($row['lat']) && !empty($row['lng'])): ?>
                                                            <a href="https://www.google.com/maps?q=<?= urlencode($row['lat'] . ',' . $row['lng']) ?>" target="_blank" class="text-primary" title="View on Google Maps">
                                                                <i class="fa fa-map-marker text-danger"></i> <?= !empty($row['address']) ? htmlspecialchars(mb_strimwidth($row['address'], 0, 30, '...')) : 'View Map' ?>
                                                            </a>
                                                            <?php if (!empty($row['distance'])): ?>
                                                                <br><small class="text-muted"><i class="fa fa-road"></i> <?= $row['distance'] ?> from office</small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($row['status_code'] === 2 && !empty($row['attendance'])): ?>
                                                            <button class="btn btn-success btn-xs" onclick="approveAttendance(<?= $row['attendance']->attendance_id ?>, this)" title="Approve out-of-range punch">
                                                                <i class="fa fa-check"></i> Approve
                                                            </button>
                                                            <button class="btn btn-danger btn-xs" style="margin-top: 4px;" onclick="rejectAttendance(<?= $row['attendance']->attendance_id ?>, this)" title="Reject punch">
                                                                <i class="fa fa-times"></i> Reject
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- TAB 2: Out-of-Range Approvals -->
                        <div class="tab-pane" id="tab_pending_approvals">
                            <div class="alert alert-warning" style="margin-bottom: 20px;">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong>Out-of-Range Attendance Queue:</strong> Employees marked below clocked in from outside the designated office boundary. As Department Head, you can review their location, punch photo, and approve or reject their attendance.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover table-team" id="pending_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">Photo</th>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Punch Date</th>
                                            <th>Clock In / Out</th>
                                            <th>Punch Location & Distance</th>
                                            <th>Comments</th>
                                            <th style="width: 140px;" class="text-center">Approval Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pending_queue_tbody">
                                        <?php if (empty($pending_approvals)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted" style="padding: 25px;">
                                                    <i class="fa fa-check-circle text-success" style="font-size: 24px;"></i>
                                                    <br>
                                                    <strong>No pending out-of-range approvals!</strong>
                                                    <br>
                                                    All team punches are either inside the office boundary or already approved.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($pending_approvals as $pa): 
                                                $photo_url = !empty($pa['photo']) ? base_url($pa['photo']) : (!empty($pa['avatar']) ? base_url($pa['avatar']) : base_url('assets/img/user/default_avatar.jpg'));
                                            ?>
                                                <tr id="pending_row_<?= $pa['attendance_id'] ?>">
                                                    <td class="text-center">
                                                        <img src="<?= $photo_url ?>" class="thumb-preview" alt="<?= htmlspecialchars($pa['fullname']) ?>" onclick="openPhotoModal('<?= $photo_url ?>', '<?= htmlspecialchars($pa['fullname']) ?>', '<?= $pa['clockin'] ?>', '<?= htmlspecialchars($pa['location']) ?>', '<?= $pa['distance'] ?>')">
                                                    </td>
                                                    <td>
                                                        <strong><?= $pa['fullname'] ?></strong>
                                                        <br>
                                                        <small class="text-muted"><i class="fa fa-id-badge"></i> <?= $pa['employment_id'] ?></small>
                                                        <br>
                                                        <small class="text-muted"><?= $pa['designations'] ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="label label-default"><?= $pa['deptname'] ?></span>
                                                    </td>
                                                    <td>
                                                        <strong><?= date('d M Y', strtotime($pa['date'])) ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= date('l', strtotime($pa['date'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="text-success"><i class="fa fa-sign-in"></i> <?= $pa['clockin'] ?></span>
                                                        <br>
                                                        <span class="text-danger"><i class="fa fa-sign-out"></i> <?= $pa['clockout'] ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($pa['maps_url'])): ?>
                                                            <a href="<?= $pa['maps_url'] ?>" target="_blank" class="text-primary font-bold">
                                                                <i class="fa fa-map-marker text-danger"></i> <?= !empty($pa['location']) ? htmlspecialchars(mb_strimwidth($pa['location'], 0, 36, '...')) : 'View Maps' ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted"><?= $pa['location'] ?: 'Location not recorded' ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($pa['distance'])): ?>
                                                            <br><span class="label label-danger" style="font-size: 10px;"><i class="fa fa-road"></i> <?= $pa['distance'] ?> away</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?= !empty($pa['comments']) ? htmlspecialchars($pa['comments']) : '-' ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group-vertical" style="width: 100%;">
                                                            <button class="btn btn-success btn-xs" style="margin-bottom: 4px;" onclick="approveAttendance(<?= $pa['attendance_id'] ?>, this)">
                                                                <i class="fa fa-check"></i> Approve (Present)
                                                            </button>
                                                            <button class="btn btn-danger btn-xs" onclick="rejectAttendance(<?= $pa['attendance_id'] ?>, this)">
                                                                <i class="fa fa-times"></i> Reject (Absent)
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="photoPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" style="max-width: 420px;">
        <div class="modal-content">
            <div class="modal-header" style="background: #2c3e50; color: #fff; padding: 12px 16px;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title" id="photoModalTitle" style="font-size: 15px; font-weight: 600;"><i class="fa fa-camera"></i> Punch Photo</h4>
            </div>
            <div class="modal-body text-center" style="padding: 16px;">
                <img id="photoModalImg" src="" alt="Punch photo" style="width: 100%; max-height: 380px; object-fit: contain; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                <div id="photoModalMeta" style="margin-top: 12px; font-size: 12px; text-align: left; background: #f9fbfd; padding: 10px; border-radius: 6px; border: 1px solid #eef2f5;">
                    <div><strong>Employee:</strong> <span id="photoMetaName">-</span></div>
                    <div><strong>Punch Time:</strong> <span id="photoMetaTime">-</span></div>
                    <div><strong>Location:</strong> <span id="photoMetaLoc">-</span></div>
                    <div><strong>Distance:</strong> <span id="photoMetaDist">-</span></div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 16px;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openPhotoModal(imgUrl, name, time, loc, dist) {
    $('#photoModalImg').attr('src', imgUrl);
    $('#photoMetaName').text(name || '-');
    $('#photoMetaTime').text(time || '-');
    $('#photoMetaLoc').text(loc || '-');
    $('#photoMetaDist').text(dist ? dist + ' from office' : '-');
    $('#photoPreviewModal').modal('show');
}

function approveAttendance(attId, btn) {
    if (!confirm('Approve this employee out-of-range punch as Present?')) {
        return;
    }
    var $btn = $(btn);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Approving...');

    $.ajax({
        url: '<?= base_url("admin/attendance/approve_attendance") ?>',
        type: 'POST',
        data: { attendance_id: attId },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message);
                } else {
                    alert(res.message);
                }
                $('#pending_row_' + attId).fadeOut(400, function() {
                    $(this).remove();
                    updateBadgeCount(-1);
                });
                setTimeout(function() {
                    location.reload();
                }, 900);
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(res.message);
                } else {
                    alert(res.message);
                }
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve');
            }
        },
        error: function() {
            alert('Failed to connect to server. Please try again.');
            $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Approve');
        }
    });
}

function rejectAttendance(attId, btn) {
    if (!confirm('Reject this punch and mark as Absent?')) {
        return;
    }
    var $btn = $(btn);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');

    $.ajax({
        url: '<?= base_url("admin/attendance/reject_attendance") ?>',
        type: 'POST',
        data: { attendance_id: attId },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message);
                } else {
                    alert(res.message);
                }
                $('#pending_row_' + attId).fadeOut(400, function() {
                    $(this).remove();
                    updateBadgeCount(-1);
                });
                setTimeout(function() {
                    location.reload();
                }, 900);
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(res.message);
                } else {
                    alert(res.message);
                }
                $btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject');
            }
        },
        error: function() {
            alert('Failed to connect to server. Please try again.');
            $btn.prop('disabled', false).html('<i class="fa fa-times"></i> Reject');
        }
    });
}

function updateBadgeCount(delta) {
    var cur = parseInt($('#badge_pending_approvals').text(), 10) || 0;
    var next = Math.max(0, cur + delta);
    $('#badge_pending_approvals').text(next);
    $('#summary_pending_count').text(next);
}
</script>

