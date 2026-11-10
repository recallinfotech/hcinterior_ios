<?= message_box('success'); ?>
<?= message_box('error'); ?>
<?= message_box('warning'); ?>

<style>
.out-stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 16px 18px;
    margin-bottom: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
}
.out-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.out-stat-info h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.1;
}
.out-stat-info p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.out-table-card {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    overflow: hidden;
}
.out-filter-bar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 12px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.out-filter-left {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.out-badge-dist {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-block;
}
.out-thumb-img {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid #f59e0b;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform .15s ease;
}
.out-thumb-img:hover {
    transform: scale(1.08);
}
</style>

<!-- Top Summary Cards -->
<div class="row">
    <div class="col-md-4 col-sm-6">
        <div class="out-stat-card">
            <div class="out-stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fa fa-map-marker"></i>
            </div>
            <div class="out-stat-info">
                <h3 id="stat_pending_count"><?= !empty($pending_approvals_count) ? $pending_approvals_count : 0 ?></h3>
                <p>Pending Out-of-Range</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="out-stat-card">
            <div class="out-stat-icon" style="background: #ecfdf5; color: #059669;">
                <i class="fa fa-shield"></i>
            </div>
            <div class="out-stat-info">
                <h3>250 m</h3>
                <p>Allowed Geofence Radius</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-12">
        <div class="out-stat-card">
            <div class="out-stat-icon" style="background: #eff6ff; color: #0284c7;">
                <i class="fa fa-users"></i>
            </div>
            <div class="out-stat-info">
                <h3>Review & Action</h3>
                <p>Verify Location on Maps</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="out-table-card">
    <!-- Header Banner -->
    <div style="background: #1e293b; color: #fff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <strong style="font-size: 16px;"><i class="fa fa-map-marker text-warning"></i> Out of Office Approvals</strong>
            <div style="font-size: 12px; color: #cbd5e1; margin-top: 2px;">
                Employees whose punch location was detected outside their office geofence range.
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-info" onclick="loadOutQueue()" style="font-weight: 600;">
                <i class="fa fa-refresh"></i> Refresh Queue
            </button>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="out-filter-bar">
        <div class="out-filter-left">
            <div class="input-group input-group-sm" style="width: 260px;">
                <span class="input-group-addon" style="background: #fff;"><i class="fa fa-search text-muted"></i></span>
                <input type="text" id="filter_search" class="form-control" placeholder="Search employee, ID, role..." style="height: 32px;">
            </div>

            <?php if (!empty($all_departments)): ?>
            <select id="filter_dept" class="form-control input-sm" style="width: 180px; height: 32px; font-weight: 600;">
                <option value="">🏢 All Departments</option>
                <?php foreach ($all_departments as $d): ?>
                    <option value="<?= htmlspecialchars($d->deptname) ?>"><?= htmlspecialchars($d->deptname) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </div>

        <div class="text-right">
            <span id="queue_count_info" style="font-size: 12px; font-weight: 600; color: #64748b;">
                Showing 0 pending requests
            </span>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover" id="tbl_out_queue" style="margin-bottom: 0;">
            <thead style="background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .5px;">
                <tr>
                    <th style="width: 60px; text-align: center;">Photo</th>
                    <th style="min-width: 200px;">Employee</th>
                    <th style="min-width: 120px;">Date & Day</th>
                    <th style="min-width: 130px; text-align: center;">Punch In / Out</th>
                    <th style="min-width: 150px;">Distance from Office</th>
                    <th style="min-width: 200px;">Detected Location</th>
                    <th style="width: 110px; text-align: center;">Google Map</th>
                    <th style="width: 160px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody id="out_queue_body">
                <tr>
                    <td colspan="8" class="text-center" style="padding: 30px;">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p style="margin-top: 10px; color: #64748b;">Loading pending out-of-range punches...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Verification Photo Full Size -->
<div class="modal fade" id="modalOutPhoto" tabindex="-1" role="dialog" style="z-index: 105000;">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 380px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);">
            <div class="modal-header" style="background: #1e293b; color: #ffffff; padding: 12px 16px; border: none;">
                <button type="button" class="close" data-dismiss="modal" style="color: #ffffff; opacity: 0.8;">&times;</button>
                <h4 class="modal-title" style="font-size: 14px; font-weight: 700;" id="outPhotoModalStaffName">Punch Photo Verification</h4>
            </div>
            <div class="modal-body text-center" style="padding: 16px; background: #0f172a;">
                <img id="outPhotoModalImg" src="" style="width: 100%; max-height: 380px; object-fit: contain; border-radius: 8px; border: 1px solid #334155;" alt="Punch Photo">
                <div style="margin-top: 12px; text-align: left; background: #1e293b; padding: 10px 14px; border-radius: 8px; color: #e2e8f0; font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="text-muted">Punch Time:</span>
                        <strong id="outPhotoModalTime" style="color: #38bdf8;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span class="text-muted">Distance:</span>
                        <span id="outPhotoModalDist" class="badge" style="background: #f59e0b;">-</span>
                    </div>
                    <div style="margin-top: 6px; font-size: 11px; color: #94a3b8; word-break: break-word;" id="outPhotoModalLoc"></div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 8px 16px; background: #1e293b; border-top: 1px solid #334155; text-align: right;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="border-radius: 6px;">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var queueData = [];

function loadOutQueue() {
    var tbody = $('#out_queue_body');
    tbody.html('<tr><td colspan="8" class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i><p style="margin-top:10px; color:#64748b;">Loading pending out-of-range punches...</p></td></tr>');

    $.ajax({
        url: '<?= base_url() ?>admin/attendance/get_pending_attendances',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success' && res.data) {
                queueData = res.data;
                renderQueueTable();
            } else {
                renderEmptyQueue();
            }
        },
        error: function() {
            tbody.html('<tr><td colspan="8" class="text-center text-danger" style="padding: 25px;"><i class="fa fa-exclamation-triangle fa-2x"></i><p style="margin-top:8px;">Failed to load queue. Please refresh.</p></td></tr>');
        }
    });
}

function renderQueueTable() {
    var search = $('#filter_search').val().toLowerCase().trim();
    var dept = $('#filter_dept').val() || '';

    var filtered = queueData.filter(function(item) {
        if (search) {
            var haystack = (item.fullname + ' ' + (item.employment_id || '') + ' ' + (item.designations || '') + ' ' + (item.location || '')).toLowerCase();
            if (haystack.indexOf(search) === -1) return false;
        }
        if (dept) {
            var matchDept = (item.deptname && item.deptname === dept);
            var matchDesig = (item.designations && item.designations.indexOf(dept) !== -1);
            if (!matchDept && !matchDesig) return false;
        }
        return true;
    });

    $('#stat_pending_count').text(queueData.length);
    $('#queue_count_info').html('Showing <strong>' + filtered.length + '</strong> of <strong>' + queueData.length + '</strong> pending requests');

    if (filtered.length === 0) {
        if (queueData.length === 0) {
            renderEmptyQueue();
        } else {
            $('#out_queue_body').html('<tr><td colspan="8" class="text-center text-muted" style="padding: 30px;"><i class="fa fa-search fa-2x"></i><p style="margin-top:8px;">No matching records found for current filters.</p></td></tr>');
        }
        return;
    }

    var html = '';
    $.each(filtered, function(idx, item) {
        var photoHtml = '<span class="text-muted"><i class="fa fa-user-circle-o fa-2x" style="color: #cbd5e1;"></i></span>';
        if (item.photo) {
            photoHtml = '<img src="' + item.photo + '" class="out-thumb-img" alt="Photo" onclick="viewOutPhotoModal(\'' + item.photo + '\', \'' + item.fullname.replace(/'/g, "\\'") + '\', \'' + item.clockin + '\', \'' + (item.distance || '') + '\', \'' + (item.location ? item.location.replace(/'/g, "\\'") : '') + '\')">';
        }

        var mapBtn = '<span class="text-muted">No GPS</span>';
        if (item.maps_url) {
            mapBtn = '<a href="' + item.maps_url + '" target="_blank" class="btn btn-xs btn-default" style="color:#b45309; border-color:#f59e0b; font-weight:600;"><i class="fa fa-external-link text-warning"></i> View Map</a>';
        }

        var distDisplay = '<span class="text-muted">Outside Office</span>';
        if (item.distance && item.distance.indexOf('1000') === -1) {
            distDisplay = '<span class="out-badge-dist"><i class="fa fa-map-marker"></i> ~' + item.distance + ' from office</span>';
        }

        var locDisplay = item.location ? ('<div style="font-size:12px; color:#1e293b; font-weight:600; line-height:1.3;">' + item.location + '</div>') : '<span class="text-muted">-</span>';
        if (item.lat && item.lng) {
            locDisplay += '<div style="font-size:10px; color:#64748b; font-family:monospace; margin-top:2px;">' + parseFloat(item.lat).toFixed(4) + ', ' + parseFloat(item.lng).toFixed(4) + '</div>';
        }

        html += '<tr id="queue_row_' + item.attendance_id + '">' +
                '<td style="text-align:center; vertical-align:middle;">' + photoHtml + '</td>' +
                '<td style="vertical-align:middle;">' +
                    '<div style="font-weight:700; color:#1e293b; font-size:13px;">' + item.fullname + '</div>' +
                    '<div style="font-size:11px; color:#64748b;">' + (item.employment_id ? ('<span class="badge" style="background:#e2e8f0; color:#334155; font-size:10px; padding:2px 5px;">' + item.employment_id + '</span> ') : '') + (item.designations || '') + '</div>' +
                '</td>' +
                '<td style="vertical-align:middle;"><strong>' + item.date + '</strong></td>' +
                '<td style="text-align:center; vertical-align:middle;">' +
                    '<div style="color:#16a34a; font-weight:700; font-size:12px;"><i class="fa fa-sign-in"></i> ' + item.clockin + '</div>' +
                    (item.clockout && item.clockout !== '-' ? ('<div style="color:#7c3aed; font-weight:700; font-size:12px; margin-top:2px;"><i class="fa fa-sign-out"></i> ' + item.clockout + '</div>') : '') +
                '</td>' +
                '<td style="vertical-align:middle;">' + distDisplay + '</td>' +
                '<td style="vertical-align:middle;">' + locDisplay + '</td>' +
                '<td style="text-align:center; vertical-align:middle;">' + mapBtn + '</td>' +
                '<td style="text-align:center; vertical-align:middle;">' +
                    '<button type="button" class="btn btn-xs btn-success" style="font-weight:700; padding:4px 10px; margin-right:4px;" onclick="approveOutAtt(' + item.attendance_id + ', \'' + item.fullname.replace(/'/g, "\\'") + '\')">' +
                        '<i class="fa fa-check"></i> Approve' +
                    '</button>' +
                    '<button type="button" class="btn btn-xs btn-danger" style="font-weight:700; padding:4px 10px;" onclick="rejectOutAtt(' + item.attendance_id + ', \'' + item.fullname.replace(/'/g, "\\'") + '\')">' +
                        '<i class="fa fa-times"></i> Reject' +
                    '</button>' +
                '</td>' +
                '</tr>';
    });

    $('#out_queue_body').html(html);
}

function renderEmptyQueue() {
    $('#stat_pending_count').text('0');
    $('#queue_count_info').text('No pending requests');
    $('#out_queue_body').html('<tr><td colspan="8" class="text-center" style="padding: 40px 20px;">' +
        '<div style="width:64px; height:64px; border-radius:50%; background:#ecfdf5; color:#059669; display:inline-flex; align-items:center; justify-content:center; font-size:28px; margin-bottom:12px;"><i class="fa fa-check-circle"></i></div>' +
        '<h4 style="font-weight:700; color:#1e293b; margin:0 0 6px;">All Caught Up!</h4>' +
        '<p style="color:#64748b; font-size:13px; max-width:400px; margin:0 auto;">There are currently no out-of-range punches pending approval. All attendance punches are within range or have already been reviewed.</p>' +
        '</td></tr>');
}

function approveOutAtt(attId, name) {
    if (!confirm('Approve out-of-range attendance for ' + name + ' as Present?')) return;
    var row = $('#queue_row_' + attId);
    row.css('opacity', '0.4');

    $.ajax({
        url: '<?= base_url() ?>admin/attendance/approve_attendance/' + attId,
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                row.fadeOut(300, function() {
                    $(this).remove();
                    queueData = queueData.filter(function(i) { return i.attendance_id != attId; });
                    renderQueueTable();
                });
            } else {
                alert(res.message || 'Approval failed.');
                row.css('opacity', '1');
            }
        },
        error: function() {
            alert('Server error occurred while approving.');
            row.css('opacity', '1');
        }
    });
}

function rejectOutAtt(attId, name) {
    if (!confirm('Reject out-of-range attendance for ' + name + ' (mark as Absent)?')) return;
    var row = $('#queue_row_' + attId);
    row.css('opacity', '0.4');

    $.ajax({
        url: '<?= base_url() ?>admin/attendance/reject_attendance/' + attId,
        type: 'POST',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                row.fadeOut(300, function() {
                    $(this).remove();
                    queueData = queueData.filter(function(i) { return i.attendance_id != attId; });
                    renderQueueTable();
                });
            } else {
                alert(res.message || 'Rejection failed.');
                row.css('opacity', '1');
            }
        },
        error: function() {
            alert('Server error occurred while rejecting.');
            row.css('opacity', '1');
        }
    });
}

function viewOutPhotoModal(url, name, time, dist, loc) {
    if (!url) return;
    $('#outPhotoModalStaffName').text(name || 'Punch Photo');
    $('#outPhotoModalImg').attr('src', url);
    $('#outPhotoModalTime').text(time || '-');
    $('#outPhotoModalDist').text(dist ? '~' + dist + ' from office' : 'Out of range');
    $('#outPhotoModalLoc').text(loc ? 'Location: ' + loc : '');
    $('#modalOutPhoto').modal('show');
}

$(document).ready(function() {
    loadOutQueue();

    $('#filter_search').on('keyup', function() {
        renderQueueTable();
    });

    $('#filter_dept').on('change', function() {
        renderQueueTable();
    });
});
</script>

