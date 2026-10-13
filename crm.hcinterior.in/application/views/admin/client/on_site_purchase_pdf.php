<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>On Site Purchase Request - <?= html_escape($purchase_info->purchase_no) ?></title>
    <style>
        body {
            font-family: 'dejavusanscondensed', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #1b365d;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            text-align: right;
            color: #337ab7;
            text-transform: uppercase;
        }
        .info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
        }
        .info-box td {
            padding: 8px 12px;
            width: 50%;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 110px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background-color: #337ab7;
            color: #ffffff;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border: 1px solid #337ab7;
            font-size: 11px;
        }
        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .status-badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
            display: inline-block;
        }
        .status-received {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .status-partial {
            background-color: #d9edf7;
            color: #31708f;
            border: 1px solid #bce8f1;
        }
        .status-pending {
            background-color: #fcf8e3;
            color: #8a6d3b;
            border: 1px solid #faebcc;
        }
        .status-reject {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }
        .footer td {
            width: 50%;
            vertical-align: bottom;
            font-size: 11px;
        }
        .sign-line {
            border-top: 1px solid #aaa;
            margin-top: 40px;
            width: 180px;
            text-align: center;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="company-title"><?= config_item('company_name') ?: 'HC INTERIOR' ?></div>
                <div style="font-size: 11px; color: #666; margin-top: 3px;">
                    <?= config_item('company_address') ?>
                </div>
            </td>
            <td class="doc-title">
                ON SITE PURCHASE REQUEST<br>
                <span style="font-size: 13px; color: #555;"><?= html_escape($purchase_info->purchase_no) ?></span>
            </td>
        </tr>
    </table>

    <table class="info-box">
        <tr>
            <td>
                <div><span class="info-label">Client Name:</span> <strong><?= html_escape($client_details->name) ?></strong></div>
                <div><span class="info-label">Client SR ID:</span> <strong style="color: #2b542c;"><?= html_escape($client_details->client_sr_id) ?></strong></div>
                <div><span class="info-label">Mobile / Phone:</span> <?= html_escape($client_details->mobile ?: $client_details->phone) ?></div>
                <div><span class="info-label">Site Address:</span> <?= html_escape($client_details->site_address ?: $client_details->address) ?></div>
            </td>
            <td>
                <div><span class="info-label">Request No:</span> <strong><?= html_escape($purchase_info->purchase_no) ?></strong></div>
                <div><span class="info-label">Request Date:</span> <?= !empty($purchase_info->created_date) ? date('d M Y, h:i A', strtotime($purchase_info->created_date)) : '-' ?></div>
                <div><span class="info-label">Requested By:</span> <?= html_escape($creator_name) ?></div>
                <div><span class="info-label">Total Items:</span> <?= count($purchase_items) ?> Items</div>
                <div>
                    <span class="info-label">Delivery Status:</span> 
                    <?php 
                    $pdf_del_status = !empty($purchase_info->delivery_status) ? $purchase_info->delivery_status : 'Pending';
                    $del_badge_cls = 'status-pending';
                    if (in_array($pdf_del_status, ['Completed', 'Accept Full', 'Recieved', 'Received'])) {
                        $del_badge_cls = 'status-received';
                    } else if (in_array($pdf_del_status, ['Partial', 'Approve Partial', 'Partially Received', 'Partially Approved'])) {
                        $del_badge_cls = 'status-partial';
                    } else if (in_array($pdf_del_status, ['Reject', 'Rejected'])) {
                        $del_badge_cls = 'status-reject';
                    }
                    ?>
                    <span class="status-badge <?= $del_badge_cls ?>"><?= html_escape($pdf_del_status) ?></span>
                </div>
            </td>
        </tr>
    </table>

    <h4 style="margin-bottom: 5px; color: #337ab7;">Requested Purchase Items</h4>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 18%;">Item Category</th>
                <th style="width: 22%;">Item Description</th>
                <th style="width: 8%;">Qty</th>
                <th style="width: 10%;">Unit</th>
                <th style="width: 15%;">Brand</th>
                <th style="width: 12%;">Remarks</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($purchase_items)) {
                $sl = 1;
                foreach ($purchase_items as $item) { 
                    $status_class = ($item->status == 'Recieved' || $item->status == 'Received') ? 'status-received' : (($item->status == 'Reject' || $item->status == 'Rejected') ? 'status-reject' : 'status-pending');
                    $status_text = ($item->status == 'Recieved' || $item->status == 'Received') ? 'Received at Site' : (($item->status == 'Reject' || $item->status == 'Rejected') ? 'Reject' : (($item->status == 'Ordered') ? 'Approve Partial' : ($item->status ?: 'Pending')));
            ?>
                <tr>
                    <td style="text-align: center;"><?= $sl++ ?></td>
                    <td><strong><?= html_escape($item->item_category) ?></strong></td>
                    <td><?= html_escape($item->item) ?></td>
                    <td style="text-align: center;"><strong><?= html_escape($item->qty) ?></strong></td>
                    <td style="text-align: center;"><?= html_escape($item->unit) ?: '-' ?></td>
                    <td><?= html_escape($item->brand) ?: '-' ?></td>
                    <td><?= html_escape($item->remarks) ?: '-' ?></td>
                    <td style="text-align: center;">
                        <span class="status-badge <?= $status_class ?>"><?= html_escape($status_text) ?></span>
                    </td>
                </tr>
            <?php 
                } 
            } else { 
            ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #888;">No items found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td>
                <div class="sign-line">Requested By Signature</div>
            </td>
            <td style="text-align: right;">
                <div class="sign-line" style="margin-left: auto;">Approved / Receiver Signature</div>
            </td>
        </tr>
    </table>

</body>
</html>
