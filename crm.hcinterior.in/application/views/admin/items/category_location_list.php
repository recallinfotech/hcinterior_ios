<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('39', 'created');
$edited = can_action('39', 'edited');
$deleted = can_action('39', 'deleted');
if (!empty($created) || !empty($edited)) {
?>
<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">

        <li class=""><a href="<?= base_url('admin/items/items_list') ?>"><?= lang('all_items') ?></a>
        </li>
        <li class=""><a href="<?= base_url('admin/items/new_items') ?>"><?= lang('new_items') ?></a>
        </li>
        <li class=""><a
                href="<?= base_url('admin/items/newitems_group') ?>"><?= lang('group') . ' ' . lang('list') ?></a>
        </li>
        <li class="active"><a
                href="<?= base_url('admin/items/items_category_location_list') ?>"><?= lang('category_location') . ' ' . lang('list') ?></a>
        </li>


        <li><a class="import" href="<?= base_url() ?>admin/items/import"><?= lang('import') . ' ' . lang('items') ?></a>
        </li>
    </ul>
    <style type="text/css">
    .custom-bulk-button {
        display: initial;
    }
    </style>
    <div class="tab-content bg-white">
        <div class="tab-pane active" id="category_location">

            <div class="table-responsive">
                <table class="table table-striped ">
                    <thead>
                        <tr>
                            <th><?= lang('category location') ?></th>
                            <th><?= lang('description') ?></th>
                            <th><?= lang('Order') ?></th>
                            <?php if (!empty($edited) || !empty($deleted)) { ?>
                            <th><?= lang('action') ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $all_category_locationInfo = get_result('tbl_category_location');
                            if (!empty($all_category_locationInfo)) {
                                foreach ($all_category_locationInfo as $category_locationInfo) {
                            ?>
                        <tr id="table_category_location_<?= $category_locationInfo->category_location_id ?>">
                            <td><?php
                                            $id = $this->uri->segment(5);
                                            if (!empty($id) && $id == $category_locationInfo->category_location_id) { ?>
                                <form method="post"
                                    action="<?= base_url() ?>admin/items/saved_category_location/<?php
                                                                                                                            if (!empty($category_location_info)) {
                                                                                                                                echo $category_location_info->category_location_id;
                                                                                                                            }
                                                                                                                            ?>"
                                    class="form-horizontal">
                                    <input type="text" name="category_location" value="<?php
                                                                                                    if (!empty($category_location_info)) {
                                                                                                        echo $category_location_info->category_location;
                                                                                                    }
                                                                                                    ?>"
                                        class="form-control"
                                        placeholder="<?= lang('enter') . ' ' . lang('group') . ' ' . lang('name') ?>"
                                        required>
                                    <?php } else {
                                                echo $category_locationInfo->category_location;
                                            }
                                                ?>
                            </td>
                            <td><?php
                                            $id = $this->uri->segment(5);
                                            if (!empty($id) && $id == $category_locationInfo->category_location_id) { ?>
                                <textarea name="description" rows="1"
                                    class="form-control"><?php
                                                                                                            if (!empty($category_location_info)) {
                                                                                                                echo $category_location_info->description;
                                                                                                            }
                                                                                                            ?></textarea>
                                <?php } else {
                                                echo $category_locationInfo->description;
                                            }
                                            ?>
                            </td>
                             <td><?php
                                            $id = $this->uri->segment(5);
                                            if (!empty($id) && $id == $category_locationInfo->category_location_id) { ?>
                                <input name="set_order"    class="form-control" value="<?php  if (!empty($category_location_info)) {
                                                                                                echo $category_location_info->set_order;
                                                                                                   } ?>">
                                <?php } else {
                                                echo $category_locationInfo->set_order;
                                            }
                                            ?>
                            </td>
                            <td>
                                <?php
                                            $id = $this->uri->segment(5);
                                            if (!empty($id) && $id == $category_locationInfo->category_location_id) { ?>
                                <?= btn_update() ?>
                                </form>
                                <?= btn_cancel('admin/items/items_category_location/category_location/') ?>
                                <?php } else { ?>
                                <?php if (!empty($edited)) { ?>
                                <?= btn_edit('admin/items/items_category_location/category_location/' . $category_locationInfo->category_location_id) ?>
                                <?php if (!empty($deleted)) { ?>
                                <?php echo ajax_anchor(base_url("admin/items/delete_category_location/" . $category_locationInfo->category_location_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_items_group_" . $category_locationInfo->category_location_id)); ?>
                            </td>
                            <?php }
                                                }
                                            } ?>
                        </tr>
                        <?php }
                            } ?>
                        <form role="form" enctype="multipart/form-data" id="form"
                            action="<?php echo base_url(); ?>admin/items/saved_category_location" method="post"
                            class="form-horizontal  ">
                            <tr>
                                <td><input required type="text" name="category_location" class="form-control"
                                        placeholder="<?= lang('enter') . ' ' . lang('category_location') . ' ' . lang('name') ?>">
                                </td>
                                <td>
                                    <textarea name="description" rows="1" class="form-control"></textarea>
                                </td>
                                 <td>
                                   <input type="number" class="form-control" name="set_order">
                                </td>
                                <td><?= btn_add() ?></td>
                            </tr>
                        </form>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>
</div>