<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom" style="border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; margin-bottom: 20px;">
            <div class="panel-heading" style="background: #ffffff; border-bottom: 1px solid #f3f4f6; border-radius: 8px 8px 0 0; padding: 15px 20px;">
                <div class="panel-title" style="color: #111827; font-size: 16px; font-weight: 600;">
                    <i class="fa fa-filter text-primary" style="margin-right: 8px;"></i> <?= lang('attendance_report') ?> - Filter
                </div>
            </div>
            <div class="panel-body" style="padding: 20px;">
                <form id="attendance-report-filter" role="form" action="<?php echo base_url(); ?>admin/attendance/attendance_report" method="post">
                    <div class="row">
                        <!-- Month Picker -->
                        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
                            <label class="control-label" style="font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                <i class="fa fa-calendar" style="margin-right: 4px; color: #6b7280;"></i> <?= lang('month') ?>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control monthyear" value="<?php
                                if (!empty($date)) {
                                    echo date('Y-m', strtotime($date));
                                } else {
                                    echo date('Y-m');
                                }
                                ?>" name="date" style="border-radius: 6px 0 0 6px; height: 38px;" autocomplete="off">
                                <div class="input-group-addon" style="background: #f9fafb; border-radius: 0 6px 6px 0;">
                                    <i class="fa fa-calendar text-muted"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
                            <label class="control-label" style="font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                <i class="fa fa-sitemap" style="margin-right: 4px; color: #6b7280;"></i> <?= lang('department') ?>
                            </label>
                            <select name="departments_id" class="form-control select_box" style="width: 100%; height: 38px;">
                                <option value=""><?= lang('all') . ' ' . lang('department') ?></option>
                                <?php if (!empty($all_department)) : foreach ($all_department as $id => $department) : ?>
                                    <option value="<?php echo $id; ?>" <?php if (!empty($departments_id) && $id == $departments_id) echo 'selected'; ?>>
                                        <?php echo !empty($department) ? $department : lang('undefined_department'); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <!-- Office Branch Filter -->
                        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
                            <label class="control-label" style="font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">
                                <i class="fa fa-building-o" style="margin-right: 4px; color: #6b7280;"></i> Office Branch
                            </label>
                            <select name="office_location_id" class="form-control select_box" style="width: 100%; height: 38px;">
                                <option value="">All Office Branches</option>
                                <?php if (!empty($all_offices)) : foreach ($all_offices as $office) : ?>
                                    <option value="<?php echo $office->id; ?>" <?php if (!empty($office_location_id) && $office_location_id == $office->id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($office->location_name); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px; display: flex; align-items: flex-end; gap: 8px; height: 62px;">
                            <button type="submit" class="btn btn-primary" style="height: 38px; font-weight: 600; padding: 0 16px; border-radius: 6px; background: #3b82f6; border: none; box-shadow: 0 1px 2px rgba(59,130,246,0.3);">
                                <i class="fa fa-search" style="margin-right: 5px;"></i> <?= lang('search') ?>
                            </button>
                            <a href="<?php echo base_url(); ?>admin/attendance/attendance_report" class="btn btn-default" style="height: 38px; line-height: 24px; font-weight: 600; padding: 6px 12px; border-radius: 6px; border: 1px solid #d1d5db; color: #4b5563;">
                                <i class="fa fa-refresh" style="margin-right: 4px;"></i> <?= lang('reset') ?>
                            </a>
                            <button type="button" onclick="confirmClearAttendancePhotos()" class="btn btn-danger" style="height: 38px; font-weight: 600; padding: 0 12px; border-radius: 6px; background: #ef4444; border: none; box-shadow: 0 1px 2px rgba(239,68,68,0.3);" title="Remove all photos from attendance folder to free disk storage">
                                <i class="fa fa-trash-o" style="margin-right: 4px;"></i> Clear Photos
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>