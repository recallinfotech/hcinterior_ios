<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="">
            <a href="<?= base_url('admin/purchase_item_brand/manage') ?>">Manage Purchase Item Brand</a>
        </li>
        <li class="active">
            <a href="<?= base_url('admin/purchase_item_brand/create') ?>"><i class="fa fa-plus"></i> New Purchase Item Brand</a>
        </li>
    </ul>

    <div class="tab-content bg-white">
        <form role="form" data-parsley-validate="" novalidate="" action="<?= base_url('admin/purchase_item_brand/save_brand/' . (!empty($brand_info) ? $brand_info->id : '')); ?>" method="post" class="form-horizontal">
            
            <div class="form-group">
                <label class="col-lg-3 control-label">Purchase Item Category <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <select name="purchase_item_category_id" class="form-control select_box" style="width: 100%" required="">
                        <option value="">Select Category</option>
                        <?php if (!empty($categories)): foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= (!empty($brand_info) && $brand_info->purchase_item_category_id == $cat->id) ? 'selected' : ''; ?>>
                                <?= $cat->category_name ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label">Brand Name <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" value="<?= (!empty($brand_info) ? $brand_info->brand_name : ''); ?>" name="brand_name" required="" placeholder="Enter Brand Name">
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label">Status</label>
                <div class="col-lg-5">
                    <label class="radio-inline c-radio">
                        <input type="radio" name="status" value="1" <?= (empty($brand_info) || $brand_info->status == 1 || $brand_info->status == 'published') ? 'checked' : ''; ?>>
                        <span class="fa fa-circle"></span> Active
                    </label>
                    <label class="radio-inline c-radio">
                        <input type="radio" name="status" value="0" <?= (!empty($brand_info) && ($brand_info->status == 0 || $brand_info->status == 'unpublished')) ? 'checked' : ''; ?>>
                        <span class="fa fa-circle"></span> Inactive
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-5">
                    <button type="submit" class="btn btn-sm btn-primary"><?= (!empty($brand_info) ? 'Update' : 'Save'); ?></button>
                    <a href="<?= base_url('admin/purchase_item_brand/manage'); ?>" class="btn btn-sm btn-default">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
