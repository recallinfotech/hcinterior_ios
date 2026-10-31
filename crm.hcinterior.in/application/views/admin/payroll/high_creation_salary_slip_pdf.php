<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Salary Slip - <?= htmlspecialchars($emp_code) ?> - <?= htmlspecialchars($month_name . ' ' . $year) ?></title>
    <style type="text/css">
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000000;
            background: #ffffff;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }
        .outer-wrapper {
            width: 100%;
            border: 2px solid #000000;
            box-sizing: border-box;
            background: #ffffff;
        }
        /* Top Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000000;
        }
        .header-table td {
            padding: 6px 10px;
            vertical-align: middle;
        }
        .company-title {
            font-size: 14.5px;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.3px;
            line-height: 1.25;
            text-align: center;
        }
        .company-address {
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
            margin-top: 2px;
            text-align: center;
        }
        .slip-title {
            font-size: 13.5px;
            font-weight: bold;
            color: #b91c1c; /* Crimson Red matching original */
            margin-top: 4px;
            text-align: center;
        }
        .logo-cell {
            width: 80px;
            text-align: right;
            padding-right: 8px;
        }
        .company-logo {
            height: 48px;
            width: auto;
            display: inline-block;
        }
        /* Employee Details Grid */
        .emp-details-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000000;
        }
        .emp-details-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            font-size: 11px;
            line-height: 1.2;
            vertical-align: middle;
        }
        .emp-details-table .lbl {
            font-weight: bold;
            color: #000000;
            white-space: nowrap;
        }
        .emp-details-table .val {
            color: #000000;
        }
        /* Main 4-Column Breakdown Table */
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }
        .breakdown-table th {
            border: 1px solid #000000;
            padding: 5px 4px;
            font-size: 11.5px;
            font-weight: bold;
            text-align: center;
            background-color: #ffffff;
            color: #000000;
        }
        .breakdown-table td {
            border: 1px solid #000000;
            padding: 3.5px 5px;
            font-size: 10.5px;
            line-height: 1.2;
            vertical-align: middle;
        }
        .col-lbl {
            text-align: left;
            font-weight: normal;
        }
        .col-val {
            text-align: right;
            font-weight: normal;
            white-space: nowrap;
        }
        .col-val-center {
            text-align: center;
            font-weight: normal;
            white-space: nowrap;
        }
        .bold {
            font-weight: bold !important;
        }
        /* Bottom Footer */
        .footer-cell {
            border-top: 1.5px solid #000000;
            padding: 6px 10px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<div class="outer-wrapper">
    <!-- TOP HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 10%;"></td>
            <td style="width: 80%; text-align: center;">
                <div class="company-title">
                    HIGH CREATION INTERIOR PROJECTS PRIVATE LIMITED
                </div>
                <div class="company-address">
                    H-56, First Floor, SECTOR 63, NOIDA-201301
                </div>
                <div class="slip-title">
                    Salary Slip for The Month of <?= htmlspecialchars($month_name) ?>,<?= htmlspecialchars($year) ?>
                </div>
            </td>
            <td class="logo-cell" style="width: 10%;">
                <?php if (!empty($logo_base64)) : ?>
                    <img src="<?= $logo_base64 ?>" class="company-logo" alt="Logo">
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- EMPLOYEE DETAILS GRID -->
    <table class="emp-details-table">
        <tr>
            <td class="lbl" style="width: 12%;">Emp Code:</td>
            <td class="val" style="width: 25%; font-weight: bold;"><?= htmlspecialchars($emp_code) ?></td>
            <td class="lbl" style="width: 13%;">Joining Date</td>
            <td class="val" style="width: 20%;"><?= htmlspecialchars($joining_date_formatted) ?></td>
            <td style="width: 15%; border-right: 0;"></td>
            <td style="width: 15%; border-left: 0;"></td>
        </tr>
        <tr>
            <td class="lbl">Name:</td>
            <td class="val" style="font-weight: bold;"><?= htmlspecialchars($emp_name) ?></td>
            <td class="lbl">Bank Acc. No:</td>
            <td class="val" style="font-weight: bold;"><?= htmlspecialchars(!empty($bank_acc) ? $bank_acc : '-') ?></td>
            <td class="lbl">Total Working Days</td>
            <td class="val" style="text-align: right; font-weight: bold;"><?= number_format($total_working_days, 2) ?></td>
        </tr>
        <tr>
            <td class="lbl">Dept :</td>
            <td class="val"><?= htmlspecialchars($dept_name) ?></td>
            <td class="lbl">Bank IFSC</td>
            <td class="val"><?= htmlspecialchars(!empty($bank_ifsc) ? $bank_ifsc : '-') ?></td>
            <td class="lbl">Paid Days</td>
            <td class="val" style="text-align: right; font-weight: bold;"><?= number_format($paid_days, 2) ?></td>
        </tr>
        <tr>
            <td class="lbl">Designation:</td>
            <td class="val"><?= htmlspecialchars($designation_name) ?></td>
            <td class="lbl">Bank</td>
            <td class="val"><?= htmlspecialchars(!empty($bank_name) ? $bank_name : '-') ?></td>
            <td class="lbl">Salary CTC</td>
            <td class="val" style="text-align: right; font-weight: bold;"><?= number_format($salary_ctc, 2) ?></td>
        </tr>
        <tr>
            <td class="lbl">P.FNO:</td>
            <td class="val"><?= htmlspecialchars(!empty($pf_no) ? $pf_no : '-') ?></td>
            <td style="border-right: 0;"></td>
            <td style="border-left: 0;"></td>
            <td style="border-right: 0;"></td>
            <td style="border-left: 0;"></td>
        </tr>
    </table>

    <!-- MAIN 4-COLUMN BREAKDOWN TABLE -->
    <table class="breakdown-table">
        <thead>
            <tr>
                <th colspan="2" style="width: 22%;">Attendance</th>
                <th style="width: 17%;">Heads Name</th>
                <th style="width: 12%;">Actual Earning</th>
                <th colspan="2" style="width: 25%;">Deductions</th>
                <th colspan="2" style="width: 24%;">Net Amount</th>
            </tr>
        </thead>
        <tbody>
            <!-- ROW 1 -->
            <tr>
                <td class="col-lbl" style="width: 13%;">Present</td>
                <td class="col-val-center" style="width: 9%;"><?= number_format($present_days, 2) ?></td>
                <td class="col-lbl bold">Basic</td>
                <td class="col-val"><?= number_format($basic_salary, 2) ?></td>
                <td class="col-lbl" style="width: 15%;">EPF Employee</td>
                <td class="col-val" style="width: 10%;"><?= number_format($epf_employee, 2) ?></td>
                <td class="col-lbl" style="width: 14%;"></td>
                <td class="col-val" style="width: 10%;"></td>
            </tr>

            <!-- ROW 2 -->
            <tr>
                <td class="col-lbl">Week Off</td>
                <td class="col-val-center"><?= number_format($week_off_days, 2) ?></td>
                <td class="col-lbl bold">Variable</td>
                <td class="col-val"><?= number_format($variable_amount, 2) ?></td>
                <td class="col-lbl">EPF Employer</td>
                <td class="col-val"><?= number_format($epf_employer, 2) ?></td>
                <td class="col-lbl bold">CTC Salary</td>
                <td class="col-val bold"><?= number_format($salary_ctc, 2) ?></td>
            </tr>

            <!-- ROW 3 -->
            <tr>
                <td class="col-lbl">Holidays</td>
                <td class="col-val-center"><?= number_format($holiday_days, 2) ?></td>
                <td class="col-lbl bold">H.R.A.</td>
                <td class="col-val"><?= number_format($hra_amount, 2) ?></td>
                <td class="col-lbl">VPF</td>
                <td class="col-val"><?= !empty($vpf_amount) ? number_format($vpf_amount, 2) : '' ?></td>
                <td class="col-lbl bold">Absent Deduction</td>
                <td class="col-val"><?= number_format($absent_deduction, 2) ?></td>
            </tr>

            <!-- ROW 4 -->
            <tr>
                <td class="col-lbl">EL Avail</td>
                <td class="col-val-center"><?= number_format($el_avail_days, 2) ?></td>
                <td class="col-lbl bold">Conveyance Allowances</td>
                <td class="col-val"><?= number_format($conveyance_amount, 2) ?></td>
                <td class="col-lbl">TDS</td>
                <td class="col-val"><?= !empty($tds_amount) ? number_format($tds_amount, 2) : '' ?></td>
                <td class="col-lbl bold">Total Deduction</td>
                <td class="col-val"><?= number_format($total_deductions, 2) ?></td>
            </tr>

            <!-- ROW 5 -->
            <tr>
                <td class="col-lbl">CL Avail</td>
                <td class="col-val-center"><?= number_format($cl_avail_days, 2) ?></td>
                <td class="col-lbl bold">Medical Allowances</td>
                <td class="col-val"><?= number_format($medical_amount, 2) ?></td>
                <td class="col-lbl">PROFTAX</td>
                <td class="col-val"><?= !empty($proftax_amount) ? number_format($proftax_amount, 2) : '' ?></td>
                <td class="col-lbl bold">Gross Salary</td>
                <td class="col-val bold"><?= number_format($gross_salary, 2) ?></td>
            </tr>

            <!-- ROW 6 -->
            <tr>
                <td class="col-lbl">SL Avail</td>
                <td class="col-val-center"><?= number_format($sl_avail_days, 2) ?></td>
                <td class="col-lbl bold">SPA</td>
                <td class="col-val"><?= number_format($spa_amount, 2) ?></td>
                <td class="col-lbl">LOANDEDU.</td>
                <td class="col-val"><?= !empty($loan_dedu_amount) ? number_format($loan_dedu_amount, 2) : '' ?></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
            </tr>

            <!-- ROW 7 -->
            <tr>
                <td class="col-lbl">Absent</td>
                <td class="col-val-center"><?= number_format($absent_days, 2) ?></td>
                <td class="col-lbl">Leave Encashment</td>
                <td class="col-val"><?= !empty($leave_encashment) ? number_format($leave_encashment, 2) : '' ?></td>
                <td class="col-lbl">ADVANCE</td>
                <td class="col-val"><?= number_format($advance_amount, 2) ?></td>
                <td class="col-lbl">Incentive Amt</td>
                <td class="col-val"><?= !empty($incentive_amt) ? number_format($incentive_amt, 2) : '' ?></td>
            </tr>

            <!-- ROW 8 -->
            <tr>
                <td class="col-lbl">NAD</td>
                <td class="col-val-center"><?= number_format($nad_days, 2) ?></td>
                <td class="col-lbl bold">Total</td>
                <td class="col-val bold"><?= number_format($total_earnings, 2) ?></td>
                <td class="col-lbl bold">ABSENT</td>
                <td class="col-val"><?= number_format($absent_deduction, 2) ?></td>
                <td class="col-lbl">Incentive Dedu</td>
                <td class="col-val"><?= !empty($incentive_dedu) ? number_format($incentive_dedu, 2) : '' ?></td>
            </tr>

            <!-- ROW 9 -->
            <tr>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl">Net Incentive:</td>
                <td class="col-val"><?= !empty($net_incentive) ? number_format($net_incentive, 2) : '' ?></td>
            </tr>

            <!-- ROW 10 -->
            <tr>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
            </tr>

            <!-- ROW 11: NET PAYABLE -->
            <tr style="background-color: #fafafa;">
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl"></td>
                <td class="col-val"></td>
                <td class="col-lbl bold" style="font-size: 11.5px;">Net Payable</td>
                <td class="col-val bold" style="font-size: 11.5px;"><?= number_format($net_payable, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- FOOTER SIGNATURE NOTE -->
    <div class="footer-cell">
        THIS IS COMPUTER GENERATED SIGNATURES NOT REQUIRED
    </div>
</div>

</body>
</html>
