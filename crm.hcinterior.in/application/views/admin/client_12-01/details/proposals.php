<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title">
            <?= lang('all_proposals') ?>
            
            <div class="pull-right">
                <?php
                $prop_created = can_action('140', 'created');
                $prop_edited = can_action('140', 'edited');
                if (!empty($prop_created) || !empty($prop_edited)) {
                    ?>
                    <a href="<?= base_url() ?>admin/proposals/index/client/<?= $client_details->client_id ?>"
                       class="btn btn-purple btn-xs"><?= lang('create_proposal') ?></a>
                <?php } ?>
                <a data-toggle="modal" data-target="#myModal"
                   href="<?= base_url() ?>admin/invoice/zipped/proposal/<?= $client_details->client_id ?>"
                   class="btn btn-success btn-xs"><?= lang('zip_proposal') ?></a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped " id="datatable_action" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th><?= lang('proposal') ?> #</th>
                    <th><?= lang('proposal_date') ?></th>
                    <th><?= lang('phone') ?></th>
                    <th><?= lang('G Total') ?></th> 
                    <th><?= lang('Site Handling') ?></th>
                    <th><?= lang('To Be Paid') ?></th>
                    <th><?= lang('status') ?></th>
                     <th class="col-sm-3 hidden_print"><?= lang('action') ?></th>
                    <!-- <?php if (!empty($edited) || !empty($deleted)) { ?>
                        <th class="col-sm-3 hidden_print"><?= lang('action') ?></th>
                    <?php } ?> -->
                </tr>
                </thead>
                <tbody>
                <?php
                $all_proposals_info = get_order_by('proposals', array( 'lead_id' => $client_details->leads_id), 'id');
                
                if (!empty($all_proposals_info)) {
                    foreach ($all_proposals_info as $v_proposals) {
                        $can_edit = $this->client_model->can_action('proposals', 'edit', array('id' => $v_proposals->id));
                        $can_delete = $this->client_model->can_action('proposals', 'delete', array('id' => $v_proposals->id));
                        
                        if ($v_proposals->status == 'pending') {
                            $label = "info";
                        } elseif ($v_proposals->status == 'accepted') {
                            $label = "success";
                        } else {
                            $label = "danger";
                        }
                        ?>
                        <tr>
                            <td>
                                <a class="text-info"
                                   href="<?= base_url() ?>admin/proposals/index/proposals_details/<?= $v_proposals->id ?>"><?= $v_proposals->ref_no ?></a>
                          
                            </td>
                            <td><?= strftime(config_item('date_format'), strtotime($v_proposals->proposal_date)) ?>
                            </td>
                            </td>
                            <td><?= $v_proposals->phone ?>  </td>
                            <td><?= $v_proposals->grand_total ?>  </td>
                            <td><?= $v_proposals->siteHandling ?>  </td>
                            <td><?= $v_proposals->totalToBePaid ?><?= $v_proposals->totalToBePaid ?>   </td>
                            
                            </td>
                            <?php ?>
                            <td><span class="label label-<?= $label ?>"><?= lang($v_proposals->status) ?></span>
                            </td>
                          
                                <td>
                                  
                                        <?= btn_edit('admin/proposals/createnewproposal/edit_proposals/'.$v_proposals->id.'/'.$client_details->client_id) ?>
                                        <?= btn_delete('admin/proposals/delete/delete_proposals/' . $v_proposals->id) ?>
                                   
                            
                                        <!-- <div class="btn-group">
                                            <button class="btn btn-xs btn-default dropdown-toggle"
                                                    data-toggle="dropdown">
                                                <?= lang('change_status') ?>
                                                <span class="caret"></span></button>
                                            <ul class="dropdown-menu animated zoomIn">
                                                <li>
                                                    <a href="<?= base_url() ?>admin/proposals/index/email_proposals/<?= $v_proposals->id ?>"><?= lang('send_email') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/proposals/index/proposals_details/<?= $v_proposals->id ?>"><?= lang('view_details') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/proposals/index/proposals_history/<?= $v_proposals->id ?>"><?= lang('history') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/proposals/change_status/declined/<?= $v_proposals->id ?>"><?= lang('declined') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/proposals/change_status/accepted/<?= $v_proposals->id ?>"><?= lang('accepted') ?></a>
                                                </li>
                                            
                                            </ul>
                                        </div> -->
                                   
                                </td>
                          
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>