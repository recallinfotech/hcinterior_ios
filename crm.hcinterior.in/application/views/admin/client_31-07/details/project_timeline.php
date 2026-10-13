<?php 
 
                           $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
$client_id = $client_details->client_id;

// Check properly
$this->db->where('client_id', $client_id);
$existing = $this->db->get('tbl_client_timeline')->num_rows();

if ($existing == 0) {

    $tasks = [
        'KT meetings done',
        'documentation',
        'call to cx',
        'cx end documenattion done',
        'floor protection order',
        'electrical material order',
        'plumbing material order',
        'Demolition',
        'debris removal',
        'civil material order',
        'brick work + few repairing',
        'plaster',
        'falceiling gypsum with ceiling electrical',
        'Kitchen ceiling (punning)',
        'wall punning',
        'internal plumbing of kitchen',
        'internal plumbing of washrooms 2nos',
        'water proofing other areas on plater',
        'slab casting',
        'PCC of bathroom',
        'Tiling bathroom',
        'tiling of all rooms and living and kitchen',
        'SS gate order',
        'Repairing of chiseling and dhar core',
        'validation',
        'Wall electrical',
        'Aluminium measurement',
        'ceiling light installation',
        'Putty & primer base coat',
        'cleaning of all civil and debris',
        'dispatch',
        'carpenter align same day hardware check with BOM and hardware list',
        'mail to factory if wooden 1st slot and final slot have gap more hen 3 days',
        'kitchen counter top installation & dedo tile isnatllation',
        'handover',
        'deepcleaning',
        'make snag list',
        'closing with final touchup Final touchup'
    ];

    foreach ($tasks as $task) {
        $this->db->insert('tbl_client_timeline', [
            'client_id' => $client_id,
            'task_name' => $task
        ]);
    }
}

// Fetch data
$this->db->where('client_id', $client_id);
$this->db->order_by('id','ASC');
$timeline = $this->db->get('tbl_client_timeline')->result();

?>

 <section class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <strong>Execution Timeline</strong>
        </div>
    </div>

    <div class="panel-body">

        <form action="<?= base_url('admin/client/update_timeline') ?>" method="post">

            <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">

            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Task Name</th>
                    <th width="180">Start Date</th>
                    <th width="180">End Date</th>
                    <th width="100">Days</th>
                </tr>
                </thead>

                <tbody>
                <?php if (!empty($timeline)) {
                    $i = 1;
                    foreach ($timeline as $row) { ?>

                    <?php 
$rowClass = '';
if ($row->task_name == 'validation') {
    $rowClass = 'validation-row';
}
?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= $i++; ?></td>

                            <td>
                                <?= $row->task_name ?>
                                <input type="hidden"
                                       name="tasks[<?= $row->id ?>][id]"
                                       value="<?= $row->id ?>">
                            </td>

                            <td>

                            
                                <input type="date"
                                       class="form-control time_line_start_date"
                                       name="tasks[<?= $row->id ?>][start_date]"
                                       value="<?= !empty($row->start_date) ? date('Y-m-d', strtotime($row->start_date)) : '' ?>">
                            </td>

                            <td>
                                <input type="date"
                                       class="form-control time_line_end_date"
                                       name="tasks[<?= $row->id ?>][end_date]"
                                        value="<?= !empty($row->end_date) ? date('Y-m-d', strtotime($row->end_date)) : '' ?>"
                                       >
                            </td>

                            <td class="duration">
                                <?= $row->duration ?>
                            </td>
                        </tr>
                <?php } } ?>
                </tbody>
            </table>
<?php if($user_type == 1 || $designation_id == 45 || has_client_permission($client_details->client_id,'validation_accept')  || has_client_permission($client_details->client_id,'all_access')): ?>

            <div class="text-right">
                <button type="submit"
                        class="btn btn-success">
                    Update Timeline
                </button>
            </div>
<?php endif; ?>  
        </form>

    </div>
</section>


<script>
$(document).on('change', '.time_line_start_date, .time_line_end_date', function(){

    var row = $(this).closest('tr');

    var start = row.find('.time_line_start_date').val();
    var end   = row.find('.time_line_end_date').val();

    if(start && end){

        // Safe split method (NO timezone issue)
        var sArr = start.split("-");
        var eArr = end.split("-");

        var s = new Date(sArr[0], sArr[1]-1, sArr[2]);
        var e = new Date(eArr[0], eArr[1]-1, eArr[2]);

        var diff = Math.round((e - s) / (1000 * 60 * 60 * 24));

        row.find('.duration').text(diff >= 0 ? diff : 0);

    } else {
        row.find('.duration').text('');
    }

});
</script>

<style>
    .validation-row {
    background-color: #eb9898 !important; /* light yellow */
    font-weight: bold;
    font-size: 16px;
}
</style>