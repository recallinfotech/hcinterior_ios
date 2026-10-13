<?php
$get_client_id    = $this->input->get('client_id', true);
$get_status       = $this->input->get('status', true);
$get_request_done = $this->input->get('request_done', true);
$get_start_date   = $this->input->get('start_date', true);
$get_end_date     = $this->input->get('end_date', true);
?>

<div class="panel panel-custom" style="margin-top:10px">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>KT Request List</strong>
        </div>
    </div>

    <div class="panel-body">
        
        <!-- Filter Bar -->
        <form method="get" action="" class="form-horizontal mb-lg">
            <div class="row" style="margin-bottom: 15px;">
                
                <div class="col-md-2">
                    <label>Client ID</label>
                    <input type="text" name="client_id" class="form-control" placeholder="Search Client ID" value="<?= html_escape($get_client_id) ?>">
                </div>

                <div class="col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="Approved" <?= ($get_status == 'Approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="Pending" <?= ($get_status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Reject" <?= ($get_status == 'Reject') ? 'selected' : '' ?>>Reject</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>KT Check</label>
                    <select name="request_done" class="form-control">
                        <option value="">All Check Status</option>
                        <option value="Done" <?= ($get_request_done == 'Done') ? 'selected' : '' ?>>Done</option>
                        <option value="Pending" <?= ($get_request_done == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Reject" <?= ($get_request_done == 'Reject') ? 'selected' : '' ?>>Reject</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>From Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= html_escape($get_start_date) ?>">
                </div>

                <div class="col-md-2">
                    <label>To Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= html_escape($get_end_date) ?>">
                </div>

                <div class="col-md-2" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= current_url(); ?>" class="btn btn-default btn-sm">Reset</a>
                </div>

            </div>
        </form>

        <!-- KT Request Table List -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Request Type</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>KT Check</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($kt_requests)): ?>
                        <?php foreach ($kt_requests as $kt): ?>
                            <tr>
                                <td><?= !empty($kt->client_name) ? $kt->client_name : $kt->client_id ?></td>
                                <td><span class="text-muted">KT</span></td>
                                <td><?= !empty($kt->request_date) ? date('d-m-Y', strtotime($kt->request_date)) : '-' ?></td>
                                <td>
                                    <span class="label label-<?= ($kt->status == 'Approved') ? 'success' : (($kt->status == 'Reject') ? 'danger' : 'warning') ?>">
                                        <?= !empty($kt->status) ? $kt->status : 'Pending' ?>
                                    </span>

                                    <?php if ($kt->status == 'Reject' && !empty($kt->remark)): ?>
                                        <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= $kt->remark ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="label label-<?= ($kt->request_done == 'Done') ? 'success' : (($kt->request_done == 'Reject') ? 'danger' : 'warning') ?>">
                                        <?= !empty($kt->request_done) ? $kt->request_done : 'Pending' ?>
                                    </span>

                                    <?php if ($kt->request_done == 'Reject' && !empty($kt->remark)): ?>
                                        <p class="text-danger small" style="margin-top:4px; margin-bottom:0;"><?= $kt->remark ?></p>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($kt->created_date) ? date('d M Y h:i A', strtotime($kt->created_date)) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No KT Requests Found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>