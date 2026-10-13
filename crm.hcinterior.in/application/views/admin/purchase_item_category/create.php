<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="">
            <a href="<?= base_url('admin/purchase_item_category/manage') ?>">Manage Purchase Item Category</a>
        </li>
        <li class="active">
            <a href="<?= base_url('admin/purchase_item_category/create') ?>"><i class="fa fa-plus"></i> New Purchase Item Category</a>
        </li>
    </ul>

    <div class="tab-content bg-white">
        <form role="form" data-parsley-validate="" novalidate="" action="<?= base_url('admin/purchase_item_category/save_category/' . (!empty($category_info) ? $category_info->id : '')); ?>" method="post" class="form-horizontal">
            
            <div class="form-group">
                <label class="col-lg-3 control-label">Category Name <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" value="<?= (!empty($category_info) ? $category_info->category_name : ''); ?>" name="category_name" required="" placeholder="Enter Category Name">
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label">Status</label>
                <div class="col-lg-5">
                    <label class="radio-inline c-radio">
                        <input type="radio" name="status" value="1" <?= (empty($category_info) || $category_info->status == 1 || $category_info->status == 'published') ? 'checked' : ''; ?>>
                        <span class="fa fa-circle"></span> Active
                    </label>
                    <label class="radio-inline c-radio">
                        <input type="radio" name="status" value="0" <?= (!empty($category_info) && ($category_info->status == 0 || $category_info->status == 'unpublished')) ? 'checked' : ''; ?>>
                        <span class="fa fa-circle"></span> Inactive
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-5">
                    <button type="submit" class="btn btn-sm btn-primary"><?= (!empty($category_info) ? 'Update' : 'Save'); ?></button>
                    <a href="<?= base_url('admin/purchase_item_category/manage'); ?>" class="btn btn-sm btn-default">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
