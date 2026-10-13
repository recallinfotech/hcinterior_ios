<?php
$can_edit = $this->items_model->can_action('tbl_leads', 'edit', array('leads_id' => $leads_details->leads_id));
$can_delete = $this->items_model->can_action('tbl_leads', 'delete', array('leads_id' => $leads_details->leads_id));

$edited = can_action('55', 'edited');
$deleted = can_action('55', 'deleted');

?>

<div class="nav-tabs-custom ">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="<?= $active == 'proposals' ? 'active' : ''; ?>"><a href="#manageProposals" data-toggle="tab"><?= lang('all_proposals') ?></a>
        </li>
        <?php if (!empty($edited)) { ?>
            <li class=""><a href="<?= base_url() ?>admin/proposals/createnewproposal/<?= $leads_details->leads_id ?>"><?= lang('create_proposal') ?></a>
            </li>
        <?php } ?>
    </ul>
    <div class="tab-content bg-white">
        <!-- ************** general *************-->
        <div class="tab-pane <?= $active == 'proposals' ? 'active' : ''; ?>" id="manageProposals" style="position: relative;">

            <div class="box" style="border: none; padding-top: 15px;" data-collapsed="0">
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-striped " cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><?= lang('proposal') ?> #</th>
                                    <th><?= lang('proposal_date') ?></th>
                                    <th><?= lang('expire_date') ?></th>
                                    <th>Phone</th>
                                    <th><?= lang('status') ?></th>
                                    <th class="hidden-print"><?= lang('action') ?></th>
                                    <!-- <?php if (!empty($edited) || !empty($deleted)) { ?>
                                        <th class="hidden-print"><?= lang('action') ?></th>
                                    <?php } ?> -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $all_proposals_info = get_result('proposals', array('lead_id' => $leads_details->leads_id));
                                if (!empty($all_proposals_info)) {
                                    foreach ($all_proposals_info as $v_proposals) {
                                        $can_edit = $this->items_model->can_action('proposals', 'edit', array('id' => $v_proposals->id));
                                        $can_delete = $this->items_model->can_action('proposals', 'delete', array('id' => $v_proposals->id));

                                        if ($v_proposals->status == 'pending') {
                                            $label = "info";
                                        } elseif ($v_proposals->status == 'accepted') {
                                            $label = "success";
                                        } else {
                                            $label = "danger";
                                        }
                                ?>
                                        <tr id="leads_proposals_<?= $v_proposals->id ?>">

                                            <td>
                                           <a href="<?php echo base_url();?>admin/proposals/createnewproposal/edit_proposals/<?= $v_proposals->id ?>"><?= $v_proposals->ref_no ?></a> 
                                            </td>

                                            <td><?= strftime(config_item('date_format'), strtotime($v_proposals->proposal_date)) ?>
                                            </td>

                                            <td><?= strftime(config_item('date_format'), strtotime($v_proposals->proposal_date)) ?>
                                            </td>

                                            <td><?= $v_proposals->phone ?> </td>
                                          
                                            <td><span class="label label-<?= $label ?>"><?= lang($v_proposals->status) ?></span>
                                            </td>
                                             <td> <a  class="btn btn-xs btn-danger"  href="<?php echo base_url();?>admin/proposals/pdf/generate_pdf/<?= $v_proposals->id ?>">
                                                <i class="fa fa-file-pdf-o"></i></a>
                                            
                                            
                                            <a  class="btn btn-info btn-xs"  href="<?php echo base_url();?>admin/proposals/pdf/generate_pdf/<?= $v_proposals->id ?>">
                                              <span class="fa fa-list-alt"></span></a>
                                            
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
        </div>
    </div>
</div>