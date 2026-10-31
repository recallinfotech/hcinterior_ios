<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<?php
$name = $profile_info->fullname ?? ($user_info->username ?? '');
$photo_url = !empty($form_info->photograph) ? base_url($form_info->photograph) : (!empty($profile_info->avatar) ? base_url($profile_info->avatar) : '');
?>

<div class="panel panel-custom" style="border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <div class="panel-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">
            <i class="fa fa-pencil-square-o text-primary" style="margin-right: 8px;"></i>
            Edit Employment Application Form &mdash; <?= html_escape($name) ?>
        </div>
        <div>
            <a href="<?= base_url('admin/employment_applications/view/' . $user_id) ?>" class="btn btn-xs btn-default">
                <i class="fa fa-eye"></i> View Form
            </a>
            <a href="<?= base_url('admin/employment_applications') ?>" class="btn btn-xs btn-default">
                <i class="fa fa-list"></i> All Applications
            </a>
        </div>
    </div>

    <div class="panel-body" style="padding: 25px;">
        <form action="<?= base_url('admin/employment_applications/save') ?>" method="post" enctype="multipart/form-data" class="form-horizontal">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">

            <!-- SECTION 1: PERSONAL & KYC -->
            <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin-bottom: 20px;">
                1. Basic & Personal Information
            </h4>

            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Applicant Full Name</label>
                        <div class="col-sm-12">
                            <input type="text" name="fullname" class="form-control" value="<?= html_escape($name) ?>" readonly style="background: #f8fafc;">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Date of Joining</label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" value="<?= !empty($profile_info->joining_date) ? date('d-m-Y', strtotime($profile_info->joining_date)) : 'N/A' ?>" readonly style="background: #f8fafc;">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Candidate Mobile</label>
                        <div class="col-sm-12">
                            <input type="text" name="candidate_mobile" class="form-control" value="<?= html_escape($profile_info->mobile ?? ($profile_info->phone ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Father Name</label>
                        <div class="col-sm-12">
                            <input type="text" name="father_name" class="form-control" value="<?= html_escape($profile_info->father_name ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Father / Emergency Mob. No</label>
                        <div class="col-sm-12">
                            <input type="text" name="father_mob_no" class="form-control" value="<?= html_escape($form_info->father_mob_no ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Date of Birth</label>
                        <div class="col-sm-12">
                            <input type="date" name="date_of_birth" class="form-control" value="<?= html_escape($profile_info->date_of_birth ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Gender</label>
                        <div class="col-sm-12">
                            <select name="gender" class="form-control">
                                <option value="male" <?= (($profile_info->gender ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= (($profile_info->gender ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= (($profile_info->gender ?? '') === 'other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Marital Status</label>
                        <div class="col-sm-12">
                            <select name="marital_status" class="form-control">
                                <option value="Single" <?= (strtolower($profile_info->maratial_status ?? '') === 'single') ? 'selected' : '' ?>>Single</option>
                                <option value="Married" <?= (strtolower($profile_info->maratial_status ?? '') === 'married') ? 'selected' : '' ?>>Married</option>
                                <option value="Other" <?= (strtolower($profile_info->maratial_status ?? '') === 'other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600; color: #0284c7;">Aadhaar Number <span class="text-danger">*</span></label>
                        <div class="col-sm-12">
                            <input type="text" name="aadhaar_no" class="form-control" value="<?= html_escape($form_info->aadhaar_no ?? '') ?>" placeholder="12-digit Aadhaar No">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600; color: #0284c7;">PAN Card Number <span class="text-danger">*</span></label>
                        <div class="col-sm-12">
                            <input type="text" name="pan_no" class="form-control" value="<?= html_escape($form_info->pan_no ?? '') ?>" placeholder="10-digit PAN No">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Date of Marriage</label>
                        <div class="col-sm-12">
                            <input type="date" name="date_of_marriage" class="form-control" value="<?= html_escape($form_info->date_of_marriage ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Spouse Name</label>
                        <div class="col-sm-12">
                            <input type="text" name="spouse_name" class="form-control" value="<?= html_escape($form_info->spouse_name ?? '') ?>" placeholder="Spouse Full Name">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Blood Group</label>
                        <div class="col-sm-12">
                            <input type="text" name="blood_group" class="form-control" value="<?= html_escape($form_info->blood_group ?? '') ?>" placeholder="e.g. O+, B+, A+">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Height & Weight</label>
                        <div class="col-sm-12">
                            <input type="text" name="height_weight" class="form-control" value="<?= html_escape($form_info->height_weight ?? '') ?>" placeholder="e.g. 5'8'' / 68kg">
                        </div>
                    </div>
                </div>
            </div>

            <hr style="border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <!-- SECTION 2: ADDRESSES -->
            <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin-bottom: 20px;">
                2. Address & Contact Information
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Present Address</label>
                        <div class="col-sm-12">
                            <textarea name="present_address" class="form-control" rows="3" placeholder="Current residential address"><?= html_escape($form_info->present_address ?? ($profile_info->present_address ?? '')) ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Present Address Contact No</label>
                        <div class="col-sm-12">
                            <input type="text" name="present_contact_no" class="form-control" value="<?= html_escape($form_info->present_contact_no ?? '') ?>" placeholder="Contact number at present address">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Permanent Address</label>
                        <div class="col-sm-12">
                            <textarea name="permanent_address" class="form-control" rows="3" placeholder="Permanent home / village address"><?= html_escape($form_info->permanent_address ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Permanent Address Contact No</label>
                        <div class="col-sm-12">
                            <input type="text" name="permanent_contact_no" class="form-control" value="<?= html_escape($form_info->permanent_contact_no ?? '') ?>" placeholder="Contact number at permanent address">
                        </div>
                    </div>
                </div>
            </div>

            <hr style="border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <!-- SECTION 3: SALARY & EXPERIENCE -->
            <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin-bottom: 20px;">
                3. Experience & Salary Particulars
            </h4>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Fresher / Experience</label>
                        <div class="col-sm-12">
                            <select name="fresher_experience" class="form-control">
                                <option value="Fresher" <?= (($form_info->fresher_experience ?? '') === 'Fresher') ? 'selected' : '' ?>>Fresher</option>
                                <option value="Experienced" <?= (($form_info->fresher_experience ?? '') === 'Experienced') ? 'selected' : '' ?>>Experienced</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Total Experience (Years)</label>
                        <div class="col-sm-12">
                            <input type="text" name="total_experience_years" class="form-control" value="<?= html_escape($form_info->total_experience_years ?? '') ?>" placeholder="e.g. 2.5">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Last Salary (₹)</label>
                        <div class="col-sm-12">
                            <input type="text" name="last_salary" class="form-control" value="<?= html_escape($form_info->last_salary ?? '') ?>" placeholder="Last drawn CTC/PM">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Present Salary (₹)</label>
                        <div class="col-sm-12">
                            <input type="text" name="present_salary" class="form-control" value="<?= html_escape($form_info->present_salary ?? '') ?>" placeholder="Offered / Present CTC">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Relative and / or Friend(s) in Company</label>
                        <div class="col-sm-12">
                            <textarea name="relative_friend_in_company" class="form-control" rows="2" placeholder="Give details if any relative/friend works in High Creation Interior"><?= html_escape($form_info->relative_friend_in_company ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Ever Applied or Interviewed in Company Before</label>
                        <div class="col-sm-12">
                            <textarea name="previously_applied" class="form-control" rows="2" placeholder="Give details if interviewed previously"><?= html_escape($form_info->previously_applied ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <hr style="border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <!-- SECTION 4: PROFESSIONAL REFERENCES -->
            <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin-bottom: 20px;">
                4. Professional References (Two persons other than relative)
            </h4>
            <div class="row">
                <?php for ($ri = 0; $ri < 2; $ri++): 
                    $ref = $ref_list[$ri] ?? null;
                ?>
                <div class="col-md-6">
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; background: #f8fafc; margin-bottom: 15px;">
                        <h5 style="margin: 0 0 10px 0; font-weight: 700;">Reference Person <?= $ri + 1 ?></h5>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="col-sm-12 control-label" style="text-align: left; font-size: 12px;">Full Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="ref_name[]" class="form-control input-sm" value="<?= html_escape($ref['name'] ?? '') ?>" placeholder="Reference Name">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="col-sm-12 control-label" style="text-align: left; font-size: 12px;">Designation / Relation</label>
                            <div class="col-sm-12">
                                <input type="text" name="ref_designation[]" class="form-control input-sm" value="<?= html_escape($ref['designation_or_relation'] ?? '') ?>" placeholder="e.g. Ex-Manager, Colleague">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="col-sm-12 control-label" style="text-align: left; font-size: 12px;">Contact No</label>
                            <div class="col-sm-12">
                                <input type="text" name="ref_contact[]" class="form-control input-sm" value="<?= html_escape($ref['contact_no'] ?? '') ?>" placeholder="Phone number">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>

            <hr style="border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <!-- SECTION 5: EDUCATIONAL QUALIFICATIONS -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin: 0;">
                    5. Educational / Professional Qualifications
                </h4>
                <button type="button" class="btn btn-xs btn-primary" onclick="addEduRow()">
                    <i class="fa fa-plus"></i> Add Qualification Row
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="eduTable" style="font-size: 12px;">
                    <thead style="background: #f1f5f9;">
                        <tr>
                            <th>School, College, University or Institution</th>
                            <th style="width: 100px;">From</th>
                            <th style="width: 100px;">To</th>
                            <th style="width: 110px;">Div / Grade</th>
                            <th style="width: 90px;">% Age</th>
                            <th>Subjects</th>
                            <th>Distinctions</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($edu_list)): foreach ($edu_list as $ei => $edu): ?>
                        <tr>
                            <td><input type="text" name="edu_institution[]" class="form-control input-sm" value="<?= html_escape($edu['institution'] ?? '') ?>" placeholder="Institution name"></td>
                            <td><input type="text" name="edu_from[]" class="form-control input-sm" value="<?= html_escape($edu['from_year'] ?? '') ?>" placeholder="YYYY"></td>
                            <td><input type="text" name="edu_to[]" class="form-control input-sm" value="<?= html_escape($edu['to_year'] ?? '') ?>" placeholder="YYYY"></td>
                            <td><input type="text" name="edu_div[]" class="form-control input-sm" value="<?= html_escape($edu['div_grade'] ?? '') ?>" placeholder="1st / A"></td>
                            <td><input type="text" name="edu_percentage[]" class="form-control input-sm" value="<?= html_escape($edu['percentage'] ?? '') ?>" placeholder="75%"></td>
                            <td><input type="text" name="edu_subjects[]" class="form-control input-sm" value="<?= html_escape($edu['subjects'] ?? '') ?>" placeholder="PCM, Interior, etc."></td>
                            <td><input type="text" name="edu_distinctions[]" class="form-control input-sm" value="<?= html_escape($edu['distinctions'] ?? '') ?>"></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td><input type="text" name="edu_institution[]" class="form-control input-sm" placeholder="e.g. Delhi University"></td>
                            <td><input type="text" name="edu_from[]" class="form-control input-sm" placeholder="2018"></td>
                            <td><input type="text" name="edu_to[]" class="form-control input-sm" placeholder="2021"></td>
                            <td><input type="text" name="edu_div[]" class="form-control input-sm" placeholder="1st"></td>
                            <td><input type="text" name="edu_percentage[]" class="form-control input-sm" placeholder="78%"></td>
                            <td><input type="text" name="edu_subjects[]" class="form-control input-sm" placeholder="Interior Design"></td>
                            <td><input type="text" name="edu_distinctions[]" class="form-control input-sm"></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr style="border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <!-- SECTION 6: WORK EXPERIENCE -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin: 0;">
                    6. Past Work Experience (Starting with current job)
                </h4>
                <button type="button" class="btn btn-xs btn-primary" onclick="addExpRow()">
                    <i class="fa fa-plus"></i> Add Experience Row
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="expTable" style="font-size: 12px;">
                    <thead style="background: #f1f5f9;">
                        <tr>
                            <th>Name of the Company</th>
                            <th style="width: 120px;">From Date</th>
                            <th style="width: 120px;">To Date</th>
                            <th>Positions Held / Nature of Work</th>
                            <th style="width: 130px;">CTC (PM)</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($exp_list)): foreach ($exp_list as $xi => $exp): ?>
                        <tr>
                            <td><input type="text" name="exp_company[]" class="form-control input-sm" value="<?= html_escape($exp['company_name'] ?? '') ?>" placeholder="Company name"></td>
                            <td><input type="text" name="exp_from[]" class="form-control input-sm" value="<?= html_escape($exp['from_date'] ?? '') ?>" placeholder="MM/YYYY"></td>
                            <td><input type="text" name="exp_to[]" class="form-control input-sm" value="<?= html_escape($exp['to_date'] ?? '') ?>" placeholder="MM/YYYY"></td>
                            <td><input type="text" name="exp_position[]" class="form-control input-sm" value="<?= html_escape($exp['position_nature'] ?? '') ?>" placeholder="Role / Specialization"></td>
                            <td><input type="text" name="exp_ctc[]" class="form-control input-sm" value="<?= html_escape($exp['ctc'] ?? '') ?>" placeholder="e.g. ₹ 35,000"></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td><input type="text" name="exp_company[]" class="form-control input-sm" placeholder="Company Name"></td>
                            <td><input type="text" name="exp_from[]" class="form-control input-sm" placeholder="MM/YYYY"></td>
                            <td><input type="text" name="exp_to[]" class="form-control input-sm" placeholder="MM/YYYY"></td>
                            <td><input type="text" name="exp_position[]" class="form-control input-sm" placeholder="Designation / Role"></td>
                            <td><input type="text" name="exp_ctc[]" class="form-control input-sm" placeholder="CTC PM"></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr style="border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <!-- SECTION 7: FAMILY DETAILS -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin: 0;">
                    7. Family Details
                </h4>
                <button type="button" class="btn btn-xs btn-primary" onclick="addFamRow()">
                    <i class="fa fa-plus"></i> Add Family Member
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="famTable" style="font-size: 12px;">
                    <thead style="background: #f1f5f9;">
                        <tr>
                            <th>Family Member Name</th>
                            <th style="width: 140px;">Relation</th>
                            <th>Occupation</th>
                            <th style="width: 150px;">Date of Birth</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fam_list)): foreach ($fam_list as $fi => $fam): ?>
                        <tr>
                            <td><input type="text" name="fam_name[]" class="form-control input-sm" value="<?= html_escape($fam['name'] ?? '') ?>" placeholder="Name"></td>
                            <td>
                                <select name="fam_relation[]" class="form-control input-sm">
                                    <option value="Father" <?= (($fam['relation'] ?? '') === 'Father') ? 'selected' : '' ?>>Father</option>
                                    <option value="Mother" <?= (($fam['relation'] ?? '') === 'Mother') ? 'selected' : '' ?>>Mother</option>
                                    <option value="Spouse" <?= (($fam['relation'] ?? '') === 'Spouse') ? 'selected' : '' ?>>Spouse</option>
                                    <option value="Brother" <?= (($fam['relation'] ?? '') === 'Brother') ? 'selected' : '' ?>>Brother</option>
                                    <option value="Sister" <?= (($fam['relation'] ?? '') === 'Sister') ? 'selected' : '' ?>>Sister</option>
                                    <option value="Child" <?= (($fam['relation'] ?? '') === 'Child') ? 'selected' : '' ?>>Child</option>
                                    <option value="Other" <?= (($fam['relation'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </td>
                            <td><input type="text" name="fam_occupation[]" class="form-control input-sm" value="<?= html_escape($fam['occupation'] ?? '') ?>" placeholder="Occupation"></td>
                            <td><input type="date" name="fam_dob[]" class="form-control input-sm" value="<?= html_escape($fam['dob'] ?? '') ?>"></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td><input type="text" name="fam_name[]" class="form-control input-sm" placeholder="Member Name"></td>
                            <td>
                                <select name="fam_relation[]" class="form-control input-sm">
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Spouse">Spouse</option>
                                    <option value="Brother">Brother</option>
                                    <option value="Sister">Sister</option>
                                    <option value="Child">Child</option>
                                    <option value="Other">Other</option>
                                </select>
                            </td>
                            <td><input type="text" name="fam_occupation[]" class="form-control input-sm" placeholder="Occupation"></td>
                            <td><input type="date" name="fam_dob[]" class="form-control input-sm"></td>
                            <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove();"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr style="border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <!-- SECTION 8: DOCUMENTS & VERIFICATION STATUS -->
            <h4 style="font-weight: 800; text-transform: uppercase; font-size: 13px; color: #0f172a; border-left: 4px solid #f59e0b; padding-left: 10px; margin-bottom: 20px;">
                8. Upload Documents & Verification Status
            </h4>
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Recent Photograph</label>
                        <div class="col-sm-12">
                            <input type="file" name="photograph" accept="image/*">
                            <?php if (!empty($form_info->photograph)): ?>
                                <div style="margin-top: 5px;"><a href="<?= base_url($form_info->photograph) ?>" target="_blank" class="text-primary" style="font-size: 11px;"><i class="fa fa-file-image-o"></i> View Current Photo</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Aadhaar Card File</label>
                        <div class="col-sm-12">
                            <input type="file" name="aadhaar_card_doc" accept="image/*,application/pdf">
                            <?php if (!empty($form_info->aadhaar_card_doc)): ?>
                                <div style="margin-top: 5px;"><a href="<?= base_url($form_info->aadhaar_card_doc) ?>" target="_blank" class="text-primary" style="font-size: 11px;"><i class="fa fa-file"></i> View Current Aadhaar</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">PAN Card File</label>
                        <div class="col-sm-12">
                            <input type="file" name="pan_card_doc" accept="image/*,application/pdf">
                            <?php if (!empty($form_info->pan_card_doc)): ?>
                                <div style="margin-top: 5px;"><a href="<?= base_url($form_info->pan_card_doc) ?>" target="_blank" class="text-primary" style="font-size: 11px;"><i class="fa fa-file"></i> View Current PAN</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Applicant Signature File</label>
                        <div class="col-sm-12">
                            <input type="file" name="signature_doc" accept="image/*">
                            <?php if (!empty($form_info->signature_doc)): ?>
                                <div style="margin-top: 5px;"><a href="<?= base_url($form_info->signature_doc) ?>" target="_blank" class="text-primary" style="font-size: 11px;"><i class="fa fa-pencil"></i> View Signature</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 15px; background: #f8fafc; border-radius: 6px; padding: 15px 0;">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 700; color: #0284c7;">HR Verification Status</label>
                        <div class="col-sm-12">
                            <select name="status" class="form-control" style="font-weight: 700;">
                                <option value="approved" <?= (($form_info->status ?? '') === 'approved') ? 'selected' : '' ?>>✅ Approved & Verified</option>
                                <option value="submitted" <?= (($form_info->status ?? '') === 'submitted') ? 'selected' : '' ?>>🟡 Submitted (Pending Review)</option>
                                <option value="rejected" <?= (($form_info->status ?? '') === 'rejected') ? 'selected' : '' ?>>🔴 Rejected (Needs Fix)</option>
                                <option value="draft" <?= (($form_info->status ?? '') === 'draft') ? 'selected' : '' ?>>Draft / Incomplete</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Declaration Place</label>
                        <div class="col-sm-12">
                            <input type="text" name="declaration_place" class="form-control" value="<?= html_escape($form_info->declaration_place ?? 'Noida') ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="col-sm-12 control-label" style="text-align: left; font-weight: 600;">Declaration Date</label>
                        <div class="col-sm-12">
                            <input type="date" name="declaration_date" class="form-control" value="<?= html_escape($form_info->declaration_date ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right" style="margin-top: 25px;">
                <a href="<?= base_url('admin/employment_applications/view/' . $user_id) ?>" class="btn btn-default" style="margin-right: 8px;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 8px 24px;">
                    <i class="fa fa-save"></i> Save Application Form
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addEduRow() {
    var row = '<tr>' +
        '<td><input type="text" name="edu_institution[]" class="form-control input-sm" placeholder="Institution"></td>' +
        '<td><input type="text" name="edu_from[]" class="form-control input-sm" placeholder="YYYY"></td>' +
        '<td><input type="text" name="edu_to[]" class="form-control input-sm" placeholder="YYYY"></td>' +
        '<td><input type="text" name="edu_div[]" class="form-control input-sm" placeholder="1st"></td>' +
        '<td><input type="text" name="edu_percentage[]" class="form-control input-sm" placeholder="70%"></td>' +
        '<td><input type="text" name="edu_subjects[]" class="form-control input-sm" placeholder="Subjects"></td>' +
        '<td><input type="text" name="edu_distinctions[]" class="form-control input-sm"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest(\'tr\').remove();"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#eduTable tbody').append(row);
}

function addExpRow() {
    var row = '<tr>' +
        '<td><input type="text" name="exp_company[]" class="form-control input-sm" placeholder="Company Name"></td>' +
        '<td><input type="text" name="exp_from[]" class="form-control input-sm" placeholder="MM/YYYY"></td>' +
        '<td><input type="text" name="exp_to[]" class="form-control input-sm" placeholder="MM/YYYY"></td>' +
        '<td><input type="text" name="exp_position[]" class="form-control input-sm" placeholder="Role"></td>' +
        '<td><input type="text" name="exp_ctc[]" class="form-control input-sm" placeholder="CTC PM"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest(\'tr\').remove();"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#expTable tbody').append(row);
}

function addFamRow() {
    var row = '<tr>' +
        '<td><input type="text" name="fam_name[]" class="form-control input-sm" placeholder="Member Name"></td>' +
        '<td><select name="fam_relation[]" class="form-control input-sm"><option value="Father">Father</option><option value="Mother">Mother</option><option value="Spouse">Spouse</option><option value="Brother">Brother</option><option value="Sister">Sister</option><option value="Child">Child</option><option value="Other">Other</option></select></td>' +
        '<td><input type="text" name="fam_occupation[]" class="form-control input-sm" placeholder="Occupation"></td>' +
        '<td><input type="date" name="fam_dob[]" class="form-control input-sm"></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest(\'tr\').remove();"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#famTable tbody').append(row);
}
</script>
