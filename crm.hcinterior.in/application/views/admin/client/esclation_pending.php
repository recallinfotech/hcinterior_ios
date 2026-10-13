<?= message_box('success'); ?>
<?= message_box('error'); ?>
<div class="panel panel-custom">
    <header class="panel-heading">
        <div class="panel-title"><strong><?= lang('Pending Esclation') . ' ' . lang('List') ?></strong>
            <div class="pull-right hidden-print">

            </div>

        </div>
    </header>
    <div class="panel-body">
<?php if($this->session->userdata('user_type') == 1){ ?>

<div class="row" style="margin-bottom:15px;">

    <div class="col-md-3">
        <label>Assigned To</label>
        <select id="assigned_to_filter" class="form-control">
            <option value="">All</option>

            <?php
            $users = $this->db
                ->order_by('fullname','ASC')
                ->get('tbl_account_details')
                ->result();

            foreach($users as $user){
            ?>
                <option value="<?= $user->fullname ?>">
                    <?= $user->fullname ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="col-md-3">
        <label>Raised By</label>
        <select id="raised_by_filter" class="form-control">
            <option value="">All</option>

            <?php
            foreach($users as $user){
            ?>
                <option value="<?= $user->fullname ?>">
                    <?= $user->fullname ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="col-md-2">
        <label>Status</label>
        <select id="status_filter" class="form-control">
            <option value="">All</option>
            <option value="Open">Open</option>
            <option value="Replied">Replied</option>
            <option value="Closed">Closed</option>
        </select>
    </div>
 
</div>

<?php } ?>

<table id="escalationTable" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Sr.</th>
         <th>Client</th>
            <th>Phone</th>  
            <th>Raised By</th>
            <th>Assigned To</th>
            <th>Remark</th>
            <th>Status</th>
            <th>Date</th>
            <th>Comments</th>
        </tr>
    </thead>

    <tbody>

    <?php $i= 1;
     foreach($escalations as $row){ ?>

        <tr>

            <td><?= $i ?></td>

           <td>
    <strong><a href="<?= base_url('admin/client/details/'.$row->client_id) ?>" target="_blank">
            <?= html_escape($row->client_name) ?> (<?= $row->client_sr_id ?>)
        </a></strong>
</td>

<td>
    <?= $row->client_phone ?>
</td>

            <td><?= $row->raised_by_name ?></td>

            <td><?= $row->assigned_to_name ?></td>

            <td><?= $row->remark ?></td>

            <td>
                <?php
                if($row->status == 'Open'){
                    echo '<span class="label label-warning">Open</span>';
                }
                elseif($row->status == 'Replied'){
                    echo '<span class="label label-info">Replied</span>';
                }
                elseif($row->status == 'Closed'){
                    echo '<span class="label label-success">Closed</span>';
                }
                ?>
            </td>

            <td>
                <?= date('d M Y h:i A', strtotime($row->created_at)) ?>
            </td>

            <td width="150">

                <button
                    class="btn btn-primary btn-xs"
                    data-toggle="collapse"
                    data-target="#comments_<?= $row->id ?>">

                    View Comments

                </button>

         

            </td>

        </tr>

       <tr id="comments_<?= $row->id ?>"
    class="collapse bg-light">

    <td colspan="9" style="padding:20px;background:#f8f9fa;">

        <div class="timeline-thread">

            <?php
            $comments = $this->db
                ->where('escalation_id', $row->id)
                ->order_by('id','ASC')
                ->get('tbl_client_escalation_comments')
                ->result();

            if(empty($comments)){
            ?>

                <div class="alert alert-info">
                    No replies found
                </div>

            <?php } else { ?>

                <?php foreach($comments as $comment){

                    $user = $this->db
                        ->select('fullname')
                        ->where('user_id',$comment->user_id)
                        ->get('tbl_account_details')
                        ->row();
                ?>

                    <div class="comment-card">

                        <div class="comment-header">

                            <span class="comment-user">
                                <i class="fa fa-user-circle"></i>
                                <?= $user->fullname ?? 'User' ?>
                            </span>

                            <span class="comment-date">
                                <?= date(
                                    'd M Y h:i A',
                                    strtotime($comment->created_at)
                                ) ?>
                            </span>

                        </div>

                        <div class="comment-body">

                            <?= nl2br($comment->comment) ?>

                        </div>

                    </div>

                <?php } ?>

            <?php } ?>

        </div>

        

<div class="row" style="margin-top:15px;">
    <div class="col-md-12">

        <textarea
            class="form-control escalation_reply"
            data-id="<?= $row->id ?>"
            rows="3"
            placeholder="Write reply..."></textarea>

        <br>

        <button
            type="button"
            class="btn btn-success save_reply_btn"
            data-id="<?= $row->id ?>">

            Reply

        </button>

           <?php if(
    $row->raised_by  == $this->session->userdata('user_id')    && $row->status != 'Closed'
){ ?>
        <button
            type="button"
            class="btn btn-danger close_escalation_btn"
            data-id="<?= $row->id ?>">

            Close

        </button>
<?php } ?>

    </div>
</div>

 

    </td>

</tr>

    <?php $i++;
     } ?>

    </tbody>
</table>
    </div>
</div>

 
    <style>

.timeline-thread{
    max-width:100%;
}

.comment-card{
    background:#fff;
    border-left:4px solid #23b7e5;
    padding:12px 15px;
    margin-bottom:12px;
    border-radius:6px;
    box-shadow:0 1px 3px rgba(0,0,0,.08);
}

.comment-header{
    display:flex;
    justify-content:space-between;
    margin-bottom:8px;
}

.comment-user{
    font-weight:600;
    color:#333;
}

.comment-date{
    color:#888;
    font-size:12px;
}

.comment-body{
    color:#555;
    line-height:22px;
}

</style>


<script>

$(document).ready(function(){

    $('#assigned_to_filter, #raised_by_filter, #status_filter, #from_date_filter, #to_date_filter')
    .on('change keyup', function(){

        filterEscalationTable();

    });

});

function filterEscalationTable(){

    let assignedTo = $('#assigned_to_filter').val().toLowerCase();
    let raisedBy   = $('#raised_by_filter').val().toLowerCase();
    let status     = $('#status_filter').val().toLowerCase();
    let fromDate   = $('#from_date_filter').val();
    let toDate     = $('#to_date_filter').val();

    $('#escalationTable tbody tr').not('.collapse').each(function(){

        let row = $(this);

        let rowAssignedTo = row.find('td:eq(4)').text().toLowerCase();
        let rowRaisedBy   = row.find('td:eq(3)').text().toLowerCase();
        let rowStatus     = row.find('td:eq(6)').text().toLowerCase();
        let rowDate       = row.find('td:eq(7)').text();

        let show = true;

        if(
            assignedTo &&
            rowAssignedTo.indexOf(assignedTo) === -1
        ){
            show = false;
        }

        if(
            raisedBy &&
            rowRaisedBy.indexOf(raisedBy) === -1
        ){
            show = false;
        }

        if(
            status &&
            rowStatus.indexOf(status) === -1
        ){
            show = false;
        }

        if(show){
            row.show();
        }else{
            row.hide();
        }

    });

}


$(document).on('click','.save_reply_btn',function(){

    let escalation_id = $(this).data('id');

    let comment = $(this)
        .closest('td')
        .find('.escalation_reply')
        .val();

    if(comment == ''){
        alert('Enter reply');
        return;
    }

    $.post(
        base_url + 'admin/client/save_escalation_reply',
        {
            escalation_id : escalation_id,
            comment : comment
        },
        function(){
            location.reload();
        }
    );

});


$(document).on('click','.close_escalation_btn',function(){

    if(!confirm('Close Escalation?')){
        return;
    }

    let escalation_id = $(this).data('id');

    $.post(
        base_url + 'admin/client/close_escalation',
        {
            escalation_id : escalation_id
        },
        function(){
            location.reload();
        }
    );

});
</script>