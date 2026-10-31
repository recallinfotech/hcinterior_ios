<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <header class="panel-heading" style="padding: 12px 18px; border-bottom: 1px solid #eaeaea;">
                <div class="panel-title" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <strong style="font-size: 15px; color: #ffffff;">
                            <i class="fa fa-map-marker text-primary" style="margin-right: 6px;"></i> 
                            Office Locations & Geofence Management
                        </strong>
                        <span style="font-size: 12px; margin-left: 8px; font-weight: normal; color: #cbd5e1;">
                            Manage multiple branches, geofence radius, and employee office assignments
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a href="<?= base_url('admin/attendance/face_id_requests') ?>" class="btn btn-xs btn-heading-link" style="color: #1e293b !important; background: #ffffff !important; border: 1px solid #cbd5e1; font-weight: 600; padding: 5px 12px; border-radius: 4px;">
                            <i class="fa fa-id-badge text-info"></i> Face ID Management
                        </a>
                        <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modal_add_office" style="font-weight: 600; padding: 5px 14px; border-radius: 4px;">
                            <i class="fa fa-plus"></i> Add New Office Location
                        </button>
                    </div>
                </div>
            </header>

            <div class="panel-body">
                <!-- STATS CARDS -->
                <?php
                $total_offices = !empty($all_offices) ? count($all_offices) : 0;
                $active_offices = 0;
                $default_office_name = 'Not Set';
                $total_mapped_staff = 0;
                if (!empty($all_offices)) {
                    foreach ($all_offices as $off) {
                        if ($off->status == 1) $active_offices++;
                        if ($off->is_default == 1) $default_office_name = $off->location_name;
                        $total_mapped_staff += ($user_counts[$off->id] ?? 0);
                    }
                }
                ?>
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 15px;">
                            <div style="background: #e0f2fe; color: #0284c7; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa fa-building"></i>
                            </div>
                            <div>
                                <div style="font-size: 22px; font-weight: 700; color: #0f172a;"><?= $total_offices ?></div>
                                <div style="font-size: 12px; color: #64748b;">Total Offices / Branches</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="stat-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 15px;">
                            <div style="background: #dcfce7; color: #16a34a; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div>
                                <div style="font-size: 22px; font-weight: 700; color: #16a34a;"><?= $active_offices ?></div>
                                <div style="font-size: 12px; color: #64748b;">Active Geofence Branches</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="stat-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 15px;">
                            <div style="background: #fef3c7; color: #d97706; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa fa-star"></i>
                            </div>
                            <div>
                                <div style="font-size: 14px; font-weight: 700; color: #b45309; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;" title="<?= html_escape($default_office_name) ?>"><?= html_escape($default_office_name) ?></div>
                                <div style="font-size: 12px; color: #64748b;">Default Primary Office</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="stat-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 15px;">
                            <div style="background: #ede9fe; color: #7c3aed; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa fa-users"></i>
                            </div>
                            <div>
                                <div style="font-size: 22px; font-weight: 700; color: #7c3aed;"><?= $total_mapped_staff ?></div>
                                <div style="font-size: 12px; color: #64748b;">Staff Mapped to Offices</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE OF OFFICE LOCATIONS -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" id="tbl_offices">
                        <thead>
                            <tr style="background: #f1f5f9;">
                                <th style="width: 50px; text-align: center;">#</th>
                                <th style="width: 250px;">Office / Branch Name</th>
                                <th>Address Details</th>
                                <th style="width: 170px;">GPS Coordinates</th>
                                <th style="width: 140px; text-align: center;">Geofence Radius</th>
                                <th style="width: 120px; text-align: center;">Assigned Staff</th>
                                <th style="width: 100px; text-align: center;">Status</th>
                                <th style="width: 180px; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_offices)): $sn = 1; foreach ($all_offices as $office): 
                                $staff_count = $user_counts[$office->id] ?? 0;
                            ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;"><?= $sn++ ?></td>

                                <td style="vertical-align: middle;">
                                    <strong style="font-size: 14px; color: #1e293b;"><?= html_escape($office->location_name) ?></strong>
                                    <?php if ($office->is_default == 1): ?>
                                        <span class="label label-warning" style="margin-left: 6px; font-size: 10px; font-weight: 600;">
                                            <i class="fa fa-star"></i> Default Office
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td style="vertical-align: middle; color: #475569; font-size: 12px;">
                                    <?= !empty($office->address) ? nl2br(html_escape($office->address)) : '<span class="text-muted" style="font-style:italic;">No address details added</span>' ?>
                                </td>

                                <td style="vertical-align: middle; font-family: monospace; font-size: 12px;">
                                    <div>Lat: <strong><?= html_escape($office->latitude) ?></strong></div>
                                    <div>Lng: <strong><?= html_escape($office->longitude) ?></strong></div>
                                    <a href="https://www.google.com/maps/search/?api=1&query=<?= $office->latitude ?>,<?= $office->longitude ?>" target="_blank" class="text-primary" style="font-size: 11px;">
                                        <i class="fa fa-external-link"></i> View on Google Maps
                                    </a>
                                </td>

                                <td style="text-align: center; vertical-align: middle;">
                                    <span class="badge" style="background-color: #0284c7; font-size: 12px; padding: 5px 10px;">
                                        <?= (int)$office->allowed_radius ?> meters
                                    </span>
                                </td>

                                <td style="text-align: center; vertical-align: middle;">
                                    <a href="<?= base_url('admin/attendance/face_id_requests?office_id=' . $office->id) ?>" class="badge" style="background-color: #64748b; font-size: 12px; padding: 5px 10px; text-decoration: none;" title="Click to view staff assigned to this office">
                                        <i class="fa fa-users"></i> <?= $staff_count ?> staff
                                    </a>
                                </td>

                                <td style="text-align: center; vertical-align: middle;">
                                    <?php if ($office->status == 1): ?>
                                        <a href="<?= base_url('admin/attendance/toggle_office_status/' . $office->id) ?>" class="label label-success" title="Click to Deactivate">
                                            <i class="fa fa-check"></i> Active
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('admin/attendance/toggle_office_status/' . $office->id) ?>" class="label label-danger" title="Click to Activate">
                                            <i class="fa fa-times"></i> Inactive
                                        </a>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: right; vertical-align: middle;">
                                    <button type="button" class="btn btn-default btn-xs btn-edit-office" 
                                            data-id="<?= $office->id ?>"
                                            data-name="<?= html_escape($office->location_name) ?>"
                                            data-address="<?= html_escape($office->address) ?>"
                                            data-lat="<?= html_escape($office->latitude) ?>"
                                            data-lng="<?= html_escape($office->longitude) ?>"
                                            data-radius="<?= html_escape($office->allowed_radius) ?>"
                                            data-status="<?= $office->status ?>"
                                            data-is-default="<?= $office->is_default ?>"
                                            title="Edit Office Details">
                                        <i class="fa fa-pencil text-primary"></i> Edit
                                    </button>

                                    <?php if ($office->is_default == 0): ?>
                                        <a href="<?= base_url('admin/attendance/set_default_office/' . $office->id) ?>" class="btn btn-default btn-xs" title="Make this the default fallback office">
                                            <i class="fa fa-star text-warning"></i> Set Default
                                        </a>
                                        <a href="<?= base_url('admin/attendance/delete_office_location/' . $office->id) ?>" class="btn btn-default btn-xs" onclick="return confirm('Are you sure you want to delete this office location? Assigned staff will fall back to default office.');" title="Delete Office">
                                            <i class="fa fa-trash text-danger"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 30px;">
                                    <i class="fa fa-map-marker fa-3x" style="color: #cbd5e1; margin-bottom: 10px;"></i>
                                    <p>No office locations found. Click "Add New Office Location" to create one.</p>
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

<!-- MODAL: ADD OFFICE LOCATION -->
<div class="modal fade" id="modal_add_office" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/attendance/save_office_location') ?>" method="post" class="form-horizontal form-office-location">
                <div class="modal-header" style="background: #1e293b; color: #fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-plus-circle text-primary"></i> Add New Office Location</h4>
                </div>

                <div class="modal-body" style="padding: 20px;">
                    <p class="text-muted" style="font-size: 12px; margin-bottom: 18px;">
                        Set up coordinates and geofence radius for a new branch or site office. Mobile app users linked to this office will be verified against these coordinates.
                    </p>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Office Name <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="location_name" class="form-control" placeholder="e.g. Gurgaon Branch / Factory Manesar" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <textarea name="address" class="form-control" rows="2" placeholder="Full address or landmark details"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Latitude <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="text" name="latitude" id="add_office_lat" class="form-control" placeholder="28.535517" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-detect-gps" data-target-lat="#add_office_lat" data-target-lng="#add_office_lng" title="Detect Current Browser GPS">
                                        <i class="fa fa-crosshairs text-primary"></i> Detect GPS
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Longitude <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="longitude" id="add_office_lng" class="form-control" placeholder="77.391029" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Allowed Radius <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="number" name="allowed_radius" class="form-control" value="250" min="50" max="50000" required>
                                <span class="input-group-addon">meters</span>
                            </div>
                            <span class="help-block" style="font-size: 11px; margin-bottom: 0;">Typical office: 150 - 300 meters. Large site / factory: 500 - 1000 meters.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <label class="checkbox-inline" style="padding-top: 5px;">
                                <input type="checkbox" name="is_default" value="1">
                                Set as Default Fallback Office
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-save-office">
                        <i class="fa fa-save"></i> Save Office Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDIT OFFICE LOCATION -->
<div class="modal fade" id="modal_edit_office" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/attendance/save_office_location') ?>" method="post" id="form_edit_office" class="form-horizontal form-office-location">
                <input type="hidden" name="id" id="edit_office_id">

                <div class="modal-header" style="background: #1e293b; color: #fff;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-pencil text-warning"></i> Edit Office Location</h4>
                </div>

                <div class="modal-body" style="padding: 20px;">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Office Name <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="location_name" id="edit_office_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <textarea name="address" id="edit_office_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Latitude <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="text" name="latitude" id="edit_office_lat" class="form-control" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-detect-gps" data-target-lat="#edit_office_lat" data-target-lng="#edit_office_lng" title="Detect Current Browser GPS">
                                        <i class="fa fa-crosshairs text-primary"></i> Detect GPS
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Longitude <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="longitude" id="edit_office_lng" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Allowed Radius <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="number" name="allowed_radius" id="edit_office_radius" class="form-control" min="50" max="50000" required>
                                <span class="input-group-addon">meters</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <label class="checkbox-inline" style="padding-top: 5px;">
                                <input type="checkbox" name="is_default" id="edit_office_default" value="1">
                                Set as Default Fallback Office
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-save-office">
                        <i class="fa fa-save"></i> Update Office Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Populate Edit Modal
    $(document).on('click', '.btn-edit-office', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var address = $(this).data('address');
        var lat = $(this).data('lat');
        var lng = $(this).data('lng');
        var radius = $(this).data('radius');
        var isDefault = $(this).data('is-default');

        $('#edit_office_id').val(id);
        $('#edit_office_name').val(name);
        $('#edit_office_address').val(address);
        $('#edit_office_lat').val(lat);
        $('#edit_office_lng').val(lng);
        $('#edit_office_radius').val(radius);
        $('#edit_office_default').prop('checked', (isDefault == 1));

        $('#modal_edit_office').modal('show');
    });

    // Detect GPS button handler
    $(document).on('click', '.btn-detect-gps', function() {
        var latTarget = $(this).data('target-lat');
        var lngTarget = $(this).data('target-lng');
        var $btn = $(this);

        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Detecting...');

        navigator.geolocation.getCurrentPosition(
            function(position) {
                $(latTarget).val(position.coords.latitude.toFixed(6));
                $(lngTarget).val(position.coords.longitude.toFixed(6));
                $btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-success"></i> Detected!');
                setTimeout(function() {
                    $btn.html('<i class="fa fa-crosshairs text-primary"></i> Detect GPS');
                }, 2000);
            },
            function(error) {
                $btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-primary"></i> Detect GPS');
                alert('Could not retrieve GPS location: ' + error.message + '. Please ensure location permission is allowed.');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });

    // Prevent double form submissions with spinner
    $('.form-office-location').on('submit', function() {
        var $btn = $(this).find('.btn-save-office');
        $btn.addClass('disabled').css('pointer-events', 'none');
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        return true;
    });
});
</script>
