<div class="remarks-box">
<h3>Remarks </h3>
   <!-- Remarks List -->
    <div id="remarks_list">
        <?php if (!empty($remarks)) {
            foreach ($remarks as $r) { ?>
                
                <div class="remark-item mb-sm">
                    <strong><?= $r->fullname ?></strong>
                    <small class="text-muted">
                        (<?= date('d M Y h:i A', strtotime($r->created_at)) ?>)
                    </small>
                    <p><?= $r->remarks ?></p>
                </div>

        <?php } } ?>
    </div>

    <hr>
    <!-- Add Remark -->
    <textarea class="form-control mb-sm" id="remark_text" placeholder="Enter remark..."></textarea>

    <button class="btn btn-primary btn-sm" id="save_remark_btn">
        Add Remark
    </button>

 
 
</div>


<script>
$('#save_remark_btn').click(function(){

    var remark = $('#remark_text').val();

    if(!remark){
        alert('Enter remark');
        return;
    }

    $.post("<?= base_url('admin/client/save_remark') ?>", {
        client_id: "<?= $client_id ?>",
        module: "<?= $module ?>",
         remarks: remark
    }, function(res){
        location.reload();
    });

});
</script>


<style>
   .remarks-box {
    background: #edfbff;
    padding: 10px;
}

.remarks-box h3{
    font-size: 18px;
    margin: 0;
    margin-bottom: 15px;
    border-bottom: 2px solid #3996e2;
    padding-bottom: 7px;
}
    </style>