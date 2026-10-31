<?php
$user_id = (int)$profile_info->user_id;
$this->load->helper('api_helper');
ensure_application_form_table();
$form_info = $this->db->where('user_id', $user_id)->get('tbl_employee_application_form')->row();
$status = $form_info->status ?? 'not_submitted';

// Parse JSON lists
$edu_list = !empty($form_info->educational_qualifications) ? json_decode($form_info->educational_qualifications, true) : [];
$exp_list = !empty($form_info->work_experience) ? json_decode($form_info->work_experience, true) : [];
$ref_list = !empty($form_info->professional_references) ? json_decode($form_info->professional_references, true) : [];
$fam_list = !empty($form_info->family_details) ? json_decode($form_info->family_details, true) : [];
?>

<div class="panel panel-custom" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 10px;">
    <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <div class="panel-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b;">
            <i class="fa fa-id-card-o text-primary" style="margin-right: 8px;"></i>
            Employment Application & KYC Details
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <?php if ($status === 'approved'): ?>
                <span class="label label-success" style="font-size: 11px; padding: 5px 10px; border-radius: 12px;">
                    <i class="fa fa-check-circle"></i> Approved & Verified by HR
                </span>
            <?php elseif ($status === 'submitted'): ?>
                <span class="label label-warning" style="font-size: 11px; padding: 5px 10px; border-radius: 12px; background: #f59e0b;">
                    <i class="fa fa-clock-o"></i> Pending HR Review
                </span>
            <?php elseif ($status === 'rejected'): ?>
                <span class="label label-danger" style="font-size: 11px; padding: 5px 10px; border-radius: 12px;">
                    <i class="fa fa-times-circle"></i> Changes Requested / Rejected
                </span>
            <?php else: ?>
                <span class="label label-default" style="font-size: 11px; padding: 5px 10px; border-radius: 12px;">
                    Draft / Incomplete
                </span>
            <?php endif; ?>

            <a href="<?= base_url('admin/employment_applications/view/' . $user_id) ?>" class="btn btn-xs btn-info" style="border-radius: 4px;">
                <i class="fa fa-external-link"></i> Full Application Form
            </a>
            <a href="<?= base_url('admin/employment_applications/edit/' . $user_id) ?>" class="btn btn-xs btn-primary" style="border-radius: 4px;">
                <i class="fa fa-pencil"></i> Edit Form
            </a>
            <?php if ($status !== 'approved'): ?>
                <a href="<?= base_url('admin/employment_applications/approve/' . $user_id) ?>" class="btn btn-xs btn-success" onclick="return confirm('Kya aap is application ko verify aur approve karna chahte hain?');" style="border-radius: 4px;">
                    <i class="fa fa-check"></i> Approve
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel-body" style="padding: 20px;">
        <?php if (!empty($form_info->rejection_reason)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 12px;">
            <strong><i class="fa fa-exclamation-triangle"></i> Rejection Feedback:</strong>
            <?= html_escape($form_info->rejection_reason) ?>
        </div>
        <?php endif; ?>

        <!-- Key Information Grid -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3 col-sm-6">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 10px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Aadhaar Number</div>
                    <div style="font-size: 13px; font-weight: 700; color: #0284c7; margin-top: 2px;">
                        <?= !empty($form_info->aadhaar_no) ? html_escape($form_info->aadhaar_no) : '<span class="text-muted font-normal">Not Provided</span>' ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 10px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">PAN Card Number</div>
                    <div style="font-size: 13px; font-weight: 700; color: #0284c7; margin-top: 2px;">
                        <?= !empty($form_info->pan_no) ? html_escape($form_info->pan_no) : '<span class="text-muted font-normal">Not Provided</span>' ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 10px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Experience & Last CTC</div>
                    <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                        <?= html_escape($form_info->fresher_experience ?? 'Fresher') ?>
                        <?= !empty($form_info->total_experience_years) ? ' (' . html_escape($form_info->total_experience_years) . ' yrs)' : '' ?>
                        <?= !empty($form_info->last_salary) ? ' &bull; ₹' . html_escape($form_info->last_salary) : '' ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 10px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Blood Group & Health</div>
                    <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                        <?= !empty($form_info->blood_group) ? html_escape($form_info->blood_group) : 'N/A' ?>
                        <?= !empty($form_info->height_weight) ? ' (' . html_escape($form_info->height_weight) . ')' : '' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-sm-6">
                <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px;">
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 4px;">Present Address:</div>
                    <div style="font-size: 12px; color: #334155;">
                        <?= nl2br(html_escape($form_info->present_address ?? ($profile_info->present_address ?? 'N/A'))) ?>
                    </div>
                    <div style="font-size: 11px; margin-top: 4px; color: #64748b;">
                        <strong>Contact:</strong> <?= html_escape($form_info->present_contact_no ?? ($profile_info->mobile ?? 'N/A')) ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px;">
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 4px;">Permanent Address:</div>
                    <div style="font-size: 12px; color: #334155;">
                        <?= !empty($form_info->permanent_address) ? nl2br(html_escape($form_info->permanent_address)) : '<span class="text-muted">Same as present / Not provided</span>' ?>
                    </div>
                    <div style="font-size: 11px; margin-top: 4px; color: #64748b;">
                        <strong>Contact:</strong> <?= html_escape($form_info->permanent_contact_no ?? 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents quick preview -->
        <div style="font-weight: 700; font-size: 12px; color: #0f172a; margin-bottom: 8px;">Uploaded Documents:</div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
            <?php if (!empty($form_info->aadhaar_card_doc)): ?>
                <a href="<?= base_url($form_info->aadhaar_card_doc) ?>" target="_blank" class="btn btn-xs btn-default" style="border-radius: 4px;">
                    <i class="fa fa-id-card text-primary"></i> Aadhaar Card File
                </a>
            <?php endif; ?>
            <?php if (!empty($form_info->pan_card_doc)): ?>
                <a href="<?= base_url($form_info->pan_card_doc) ?>" target="_blank" class="btn btn-xs btn-default" style="border-radius: 4px;">
                    <i class="fa fa-credit-card text-success"></i> PAN Card File
                </a>
            <?php endif; ?>
            <?php if (!empty($form_info->signature_doc)): ?>
                <a href="<?= base_url($form_info->signature_doc) ?>" target="_blank" class="btn btn-xs btn-default" style="border-radius: 4px;">
                    <i class="fa fa-pencil text-warning"></i> Signature
                </a>
            <?php endif; ?>
            <?php if (empty($form_info->aadhaar_card_doc) && empty($form_info->pan_card_doc) && empty($form_info->signature_doc)): ?>
                <span class="text-muted" style="font-size: 11px;">No documents attached yet.</span>
            <?php endif; ?>
        </div>

        <div style="text-align: right;">
            <a href="<?= base_url('admin/employment_applications/view/' . $user_id) ?>" class="btn btn-sm btn-primary" style="font-weight: 600;">
                <i class="fa fa-file-text-o"></i> View & Print Full Application (3 Pages)
            </a>
        </div>
    </div>
</div>
