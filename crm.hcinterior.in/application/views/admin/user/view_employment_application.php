<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<?php
$status = $form_info->status ?? 'not_submitted';
$name = $profile_info->fullname ?? ($user_info->username ?? 'Applicant');
$photo_url = !empty($form_info->photograph) ? base_url($form_info->photograph) : (!empty($profile_info->avatar) ? base_url($profile_info->avatar) : base_url('assets/img/user/default_avatar.jpg'));
?>

<!-- Action Bar (Top) -->
<div class="panel panel-default" style="border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
    <div class="panel-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding: 15px 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="<?= base_url('admin/employment_applications') ?>" class="btn btn-sm btn-default" style="border-radius: 4px;">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <h4 style="margin: 0; font-weight: 700; color: #1e293b;">
                <?= html_escape($name) ?>
                <span style="font-size: 13px; font-weight: normal; color: #64748b;">(ID: <?= html_escape($profile_info->employment_id ?? 'N/A') ?>)</span>
            </h4>
            <!-- Status Badge -->
            <?php if ($status === 'approved'): ?>
                <span class="label label-success" style="font-size: 12px; padding: 5px 12px; border-radius: 12px;">
                    <i class="fa fa-check-circle"></i> Approved & Verified by HR
                </span>
            <?php elseif ($status === 'submitted'): ?>
                <span class="label label-warning" style="font-size: 12px; padding: 5px 12px; border-radius: 12px; background: #f59e0b;">
                    <i class="fa fa-clock-o"></i> Pending HR Verification
                </span>
            <?php elseif ($status === 'rejected'): ?>
                <span class="label label-danger" style="font-size: 12px; padding: 5px 12px; border-radius: 12px;">
                    <i class="fa fa-times-circle"></i> Changes Requested / Rejected
                </span>
            <?php else: ?>
                <span class="label label-default" style="font-size: 12px; padding: 5px 12px; border-radius: 12px;">
                    Draft / Incomplete
                </span>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 8px;">
            <button onclick="window.print();" class="btn btn-sm btn-default" style="border-radius: 4px;">
                <i class="fa fa-print"></i> Print Form
            </button>
            <a href="<?= base_url('admin/employment_applications/edit/' . $user_id) ?>" class="btn btn-sm btn-primary" style="border-radius: 4px;">
                <i class="fa fa-pencil"></i> Edit Form
            </a>
            <?php if ($status !== 'approved'): ?>
                <a href="<?= base_url('admin/employment_applications/approve/' . $user_id) ?>" class="btn btn-sm btn-success" onclick="return confirm('Kya aap is application ko verify aur approve karna chahte hain?');" style="border-radius: 4px;">
                    <i class="fa fa-check"></i> Approve & Verify
                </a>
            <?php endif; ?>
            <?php if ($status !== 'rejected'): ?>
                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal" style="border-radius: 4px;">
                    <i class="fa fa-times"></i> Reject / Request Fix
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($form_info->rejection_reason)): ?>
<div class="alert alert-danger" style="border-radius: 6px;">
    <strong><i class="fa fa-exclamation-triangle"></i> Rejection / Feedback Note for Employee:</strong>
    <p style="margin: 4px 0 0 0;"><?= nl2br(html_escape($form_info->rejection_reason)) ?></p>
</div>
<?php endif; ?>

<!-- Printable Form Container -->
<div id="printableForm" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 35px 45px; max-width: 900px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b;">

    <!-- Company Letterhead Header -->
    <div style="border-bottom: 3px solid #f59e0b; padding-bottom: 12px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 style="margin: 0; font-size: 24px; font-weight: 900; letter-spacing: 1px; color: #0f172a; text-transform: uppercase;">
                    High Creation Interior
                </h2>
                <div style="font-size: 13px; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 2px;">
                    Projects Private Limited
                </div>
            </div>
            <div style="text-align: right; font-size: 11px; color: #475569;">
                <strong>GSTIN:</strong> 09AAECH5510R1Z5<br>
                <span>H 56, 1st Floor, Sector-63, Noida, U.P. - 201301</span><br>
                <span><i class="fa fa-phone"></i> 1800 1200 532 &bull; info@hcinterior.in</span>
            </div>
        </div>
    </div>

    <!-- Title -->
    <div style="text-align: center; margin-bottom: 25px;">
        <h3 style="margin: 0; font-size: 18px; font-weight: 800; text-decoration: underline; text-transform: uppercase; letter-spacing: 1px; color: #0f172a;">
            Employment Application Form
        </h3>
    </div>

    <!-- Basic & Personal Grid -->
    <div style="position: relative; margin-bottom: 25px;">
        <!-- Photo Box -->
        <div style="position: absolute; right: 0; top: 0; width: 130px; height: 160px; border: 2px dashed #94a3b8; border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8fafc;">
            <?php if (!empty($form_info->photograph) || !empty($profile_info->avatar)): ?>
                <img src="<?= $photo_url ?>" alt="Applicant Photo" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <div style="text-align: center; color: #64748b; font-size: 11px; padding: 10px;">
                    <i class="fa fa-user fa-3x" style="color: #cbd5e1; margin-bottom: 6px;"></i><br>
                    <strong>Recent Photograph</strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- Left Fields -->
        <table style="width: calc(100% - 150px); font-size: 13px; line-height: 2;">
            <tr>
                <td style="width: 170px; font-weight: 700;">Name of the Applicant:</td>
                <td style="border-bottom: 1px dotted #94a3b8; font-weight: 600; color: #0f172a;">
                    <?= html_escape($profile_info->fullname ?? ($user_info->username ?? '')) ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Date of Joining:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= !empty($profile_info->joining_date) ? date('d-m-Y', strtotime($profile_info->joining_date)) : 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Father Name:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= html_escape($profile_info->father_name ?? 'N/A') ?>
                    <span style="margin-left: 20px; font-weight: 700;">Mob. No:</span>
                    <span style="font-weight: 600;"><?= html_escape($form_info->father_mob_no ?? 'N/A') ?></span>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Fresher / Experience:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= html_escape($form_info->fresher_experience ?? 'Fresher') ?>
                    <?php if (!empty($form_info->total_experience_years)): ?>
                        &bull; <?= html_escape($form_info->total_experience_years) ?> Year(s)
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Last Salary:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= !empty($form_info->last_salary) ? '₹ ' . html_escape($form_info->last_salary) : 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Present Salary:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= !empty($form_info->present_salary) ? '₹ ' . html_escape($form_info->present_salary) : 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Date of Birth:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= !empty($profile_info->date_of_birth) ? date('d-m-Y', strtotime($profile_info->date_of_birth)) : 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Gender:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= ucfirst(html_escape($profile_info->gender ?? 'N/A')) ?>
                    <span style="margin-left: 30px; font-weight: 700;">Aadhaar No:</span>
                    <span style="font-weight: 600; color: #0284c7;"><?= html_escape($form_info->aadhaar_no ?? 'N/A') ?></span>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Marital Status:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= ucfirst(html_escape($profile_info->maratial_status ?? 'N/A')) ?>
                    <span style="margin-left: 30px; font-weight: 700;">PAN No:</span>
                    <span style="font-weight: 600; color: #0284c7;"><?= html_escape($form_info->pan_no ?? 'N/A') ?></span>
                </td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Date of Marriage:</td>
                <td style="border-bottom: 1px dotted #94a3b8;">
                    <?= !empty($form_info->date_of_marriage) ? date('d-m-Y', strtotime($form_info->date_of_marriage)) : 'N/A' ?>
                    <span style="margin-left: 20px; font-weight: 700;">Spouse Name:</span>
                    <span><?= html_escape($form_info->spouse_name ?? 'N/A') ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Personal Information (Addresses) -->
    <div style="margin-bottom: 20px;">
        <div style="font-weight: 800; text-transform: uppercase; font-size: 13px; margin-bottom: 8px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 8px;">
            Personal Information
        </div>
        <div class="row">
            <div class="col-xs-6" style="padding-right: 10px;">
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; height: 110px;">
                    <div style="font-weight: 700; font-size: 12px; margin-bottom: 4px;">Present Address:</div>
                    <div style="font-size: 12px; color: #334155; line-height: 1.4;">
                        <?= nl2br(html_escape($form_info->present_address ?? ($profile_info->present_address ?? 'N/A'))) ?>
                    </div>
                    <div style="font-size: 11px; margin-top: 6px; color: #64748b;">
                        <strong>Contact No:</strong> <?= html_escape($form_info->present_contact_no ?? ($profile_info->mobile ?? 'N/A')) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6" style="padding-left: 10px;">
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; height: 110px;">
                    <div style="font-weight: 700; font-size: 12px; margin-bottom: 4px;">Permanent Address:</div>
                    <div style="font-size: 12px; color: #334155; line-height: 1.4;">
                        <?= nl2br(html_escape($form_info->permanent_address ?? 'N/A')) ?>
                    </div>
                    <div style="font-size: 11px; margin-top: 6px; color: #64748b;">
                        <strong>Contact No:</strong> <?= html_escape($form_info->permanent_contact_no ?? 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Relatives / Prior Interview Questions -->
    <div style="margin-bottom: 20px;">
        <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; margin-bottom: 10px; font-size: 12px;">
            <strong>If you have any relative and / or friend(s) in our Company, please give the details:</strong>
            <p style="margin: 4px 0 0 0; color: #334155; font-style: italic;">
                <?= !empty($form_info->relative_friend_in_company) ? nl2br(html_escape($form_info->relative_friend_in_company)) : 'None / NA' ?>
            </p>
        </div>
        <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; font-size: 12px;">
            <strong>If you have ever applied or interviewed in our Company, please give the details:</strong>
            <p style="margin: 4px 0 0 0; color: #334155; font-style: italic;">
                <?= !empty($form_info->previously_applied) ? nl2br(html_escape($form_info->previously_applied)) : 'None / First Time' ?>
            </p>
        </div>
    </div>

    <!-- Professional References -->
    <div style="margin-bottom: 20px;">
        <div style="font-weight: 800; text-transform: uppercase; font-size: 13px; margin-bottom: 8px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 8px;">
            Professional References (Two Persons other than relative)
        </div>
        <div class="row">
            <?php for ($ri = 0; $ri < 2; $ri++): 
                $r = $ref_list[$ri] ?? null;
            ?>
            <div class="col-xs-6">
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; font-size: 12px;">
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">Reference <?= $ri + 1 ?></div>
                    <div><strong>Name:</strong> <?= html_escape($r['name'] ?? 'N/A') ?></div>
                    <div><strong>Designation / Rel:</strong> <?= html_escape($r['designation_or_relation'] ?? 'N/A') ?></div>
                    <div><strong>Contact No:</strong> <?= html_escape($r['contact_no'] ?? 'N/A') ?></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Educational / Professional Qualifications -->
    <div style="margin-bottom: 20px;">
        <div style="font-weight: 800; text-transform: uppercase; font-size: 13px; margin-bottom: 8px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 8px;">
            Educational / Professional Qualification
        </div>
        <table class="table table-bordered" style="font-size: 12px; margin-bottom: 0;">
            <thead style="background: #f1f5f9;">
                <tr>
                    <th>School / College / University</th>
                    <th style="width: 120px;">Period</th>
                    <th style="width: 90px;">Div / Grade</th>
                    <th style="width: 80px;">% Age</th>
                    <th>Subjects</th>
                    <th>Scholarships / Distinctions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($edu_list)): foreach ($edu_list as $edu): ?>
                <tr>
                    <td><strong><?= html_escape($edu['institution'] ?? '') ?></strong></td>
                    <td><?= html_escape($edu['from_year'] ?? '') ?> - <?= html_escape($edu['to_year'] ?? '') ?></td>
                    <td><?= html_escape($edu['div_grade'] ?? '') ?></td>
                    <td><?= html_escape($edu['percentage'] ?? '') ?></td>
                    <td><?= html_escape($edu['subjects'] ?? '') ?></td>
                    <td><?= html_escape($edu['distinctions'] ?? '') ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted pv-sm">No educational details recorded.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Work Experience -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 8px;">
                Work Experience (Beginning with current job)
            </div>
            <div style="font-size: 12px; color: #475569;">
                <strong>Candidate Mob:</strong> <?= html_escape($profile_info->mobile ?? 'N/A') ?> &bull; 
                <strong>Total Exp:</strong> <?= html_escape($form_info->total_experience_years ?? 'Fresher') ?> yrs
            </div>
        </div>
        <table class="table table-bordered" style="font-size: 12px; margin-bottom: 0;">
            <thead style="background: #f1f5f9;">
                <tr>
                    <th style="width: 140px;">Period</th>
                    <th>Name of the Company</th>
                    <th>Positions Held / Nature of Work</th>
                    <th style="width: 130px;">CTC (PM)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($exp_list)): foreach ($exp_list as $exp): ?>
                <tr>
                    <td><?= html_escape($exp['from_date'] ?? '') ?> to <?= html_escape($exp['to_date'] ?? '') ?></td>
                    <td><strong><?= html_escape($exp['company_name'] ?? '') ?></strong></td>
                    <td><?= html_escape($exp['position_nature'] ?? '') ?></td>
                    <td style="font-weight: 600; color: #047857;"><?= html_escape($exp['ctc'] ?? '') ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted pv-sm">No past work experience recorded (Fresher).</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Health & Family Details -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 8px;">
                Family Details
            </div>
            <div style="font-size: 12px; color: #475569;">
                <strong>Blood Group:</strong> <?= html_escape($form_info->blood_group ?? 'N/A') ?> &bull; 
                <strong>Height & Weight:</strong> <?= html_escape($form_info->height_weight ?? 'N/A') ?>
            </div>
        </div>
        <table class="table table-bordered" style="font-size: 12px; margin-bottom: 0;">
            <thead style="background: #f1f5f9;">
                <tr>
                    <th style="width: 50px;">S.No</th>
                    <th>Name</th>
                    <th>Relation</th>
                    <th>Occupation</th>
                    <th style="width: 120px;">Date of Birth</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fam_list)): foreach ($fam_list as $fk => $fam): ?>
                <tr>
                    <td><?= $fk + 1 ?></td>
                    <td><strong><?= html_escape($fam['name'] ?? '') ?></strong></td>
                    <td><?= html_escape($fam['relation'] ?? '') ?></td>
                    <td><?= html_escape($fam['occupation'] ?? '') ?></td>
                    <td><?= !empty($fam['dob']) ? date('d-m-Y', strtotime($fam['dob'])) : 'N/A' ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted pv-sm">No family members recorded.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Uploaded Documents Preview Section -->
    <div style="margin-bottom: 25px;">
        <div style="font-weight: 800; text-transform: uppercase; font-size: 13px; margin-bottom: 10px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 8px;">
            Uploaded KYC & Identity Documents
        </div>
        <div class="row">
            <div class="col-xs-4">
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; text-align: center; background: #f8fafc;">
                    <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">Aadhaar Card</div>
                    <?php if (!empty($form_info->aadhaar_card_doc)): ?>
                        <a href="<?= base_url($form_info->aadhaar_card_doc) ?>" target="_blank" class="btn btn-xs btn-primary">
                            <i class="fa fa-external-link"></i> View Aadhaar File
                        </a>
                    <?php else: ?>
                        <span class="text-muted" style="font-size: 11px;">Not Uploaded</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-xs-4">
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; text-align: center; background: #f8fafc;">
                    <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">PAN Card</div>
                    <?php if (!empty($form_info->pan_card_doc)): ?>
                        <a href="<?= base_url($form_info->pan_card_doc) ?>" target="_blank" class="btn btn-xs btn-primary">
                            <i class="fa fa-external-link"></i> View PAN Card File
                        </a>
                    <?php else: ?>
                        <span class="text-muted" style="font-size: 11px;">Not Uploaded</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-xs-4">
                <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; text-align: center; background: #f8fafc;">
                    <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">Applicant Signature</div>
                    <?php if (!empty($form_info->signature_doc)): ?>
                        <a href="<?= base_url($form_info->signature_doc) ?>" target="_blank" class="btn btn-xs btn-primary">
                            <i class="fa fa-external-link"></i> View Signature
                        </a>
                    <?php else: ?>
                        <span class="text-muted" style="font-size: 11px;">Not Uploaded</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Declaration -->
    <div style="border-top: 1px solid #cbd5e1; padding-top: 15px; margin-top: 25px; font-size: 11px; color: #475569; line-height: 1.5;">
        <p>
            <em>I hereby declare that the above particulars are true to the extent of my knowledge and I understand that the misrepresentation or omission of facts can call for cause of disqualification.</em>
        </p>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 30px;">
            <div>
                <strong>Date:</strong> <?= !empty($form_info->declaration_date) ? date('d-m-Y', strtotime($form_info->declaration_date)) : date('d-m-Y') ?><br>
                <strong>Place:</strong> <?= html_escape($form_info->declaration_place ?? 'Noida') ?>
            </div>
            <div style="text-align: center;">
                <?php if (!empty($form_info->signature_doc)): ?>
                    <img src="<?= base_url($form_info->signature_doc) ?>" alt="Signature" style="max-height: 45px; max-width: 150px; margin-bottom: 4px;"><br>
                <?php endif; ?>
                <div style="border-top: 1px solid #94a3b8; width: 180px; padding-top: 4px; font-weight: 700;">
                    (Signature of Applicant)
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <form action="<?= base_url('admin/employment_applications/reject/' . $user_id) ?>" method="post">
            <div class="modal-content" style="border-radius: 8px;">
                <div class="modal-header bg-danger text-white">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color: #fff;"><i class="fa fa-times-circle"></i> Reject Application</h4>
                </div>
                <div class="modal-body">
                    <p style="font-size: 12px; color: #64748b;">
                        Please provide feedback or a reason for rejection so the employee can review and correct it in the mobile app:
                    </p>
                    <div class="form-group">
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="e.g. Aadhaar number is incorrect, or Permanent Address is missing..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-send"></i> Submit Rejection</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printableForm, #printableForm * {
        visibility: visible;
    }
    #printableForm {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .panel-default, .alert {
        display: none !important;
    }
}
</style>
