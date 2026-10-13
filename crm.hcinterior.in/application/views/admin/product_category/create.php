<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class=""><a
                href="<?= base_url('admin/product_category/manage') ?>"><?= lang('manage') . ' ' . lang('category') ?></a>
        </li>
        <li class="active"><a
                href="<?= base_url('admin/product_category/create') ?>"><?= lang('new') . ' ' . lang('category') ?></a>
        </li>
    </ul>
    <?php // print_r ($category_info);?>
    <div class="tab-content bg-white">
        <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="category_form"  action="<?= base_url('admin/product_category/save_category/' . (!empty($category_info) ? $category_info->product_category_id : '0') . '/' . $inline); ?>"
            method="post" class="form-horizontal  <?= (!empty($inline) ? 'myInline' : '') ?> ">


            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('category') . ' ' . lang('name') ?> <span
                        class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" value="<?php
                    if (!empty($category_info)) {
                        echo $category_info->category_name;
                    }
                    ?>" name="category_name" required="">
                </div>
            </div>
          <div class="form-group "  >
    <label class="col-lg-3 control-label">Main Product <span class="text-danger">*</span></label>
    <div class="col-lg-5">
        <select class="form-control" name="saved_items_id" id="main_product">
            <option value="">Select Main Product </option>
            <?php foreach($main_product as $mp): ?>
                <option value="<?= $mp->saved_items_id ?>"
                    <?= (!empty($category_info->saved_items_id) && $category_info->saved_items_id == $mp->saved_items_id ? 'selected' : '') ?>>
                    <?= $mp->item_name ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('Select Type') ?> <span
                        class="text-danger">*</span></label>
                <div class="col-lg-5">

                    <select class="form-control" name="type" id="category_type">
                        <option value="">Select Type</option>
                        <option value="Main"
                            <?= (!empty($category_info->type) && $category_info->type == 'Main' ? 'selected' : '') ?>>
                            Main</option>
                        <option value="Sub Category"
                            <?= (!empty($category_info->type) && $category_info->type == 'Sub Category' ? 'selected' : '') ?>>
                            Sub Category</option>
                        <!-- <option value="Grand Sub Category" <?= (!empty($category_info->type) && $category_info->type == 'Grand Sub Category' ? 'selected' : '') ?>>Grand Sub Category</option> -->
                    </select>
                </div>
            </div>


            <div class="form-group main-category-dropdown" style="display:none;">
                <label class="col-lg-3 control-label">Main Category <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <select class="form-control" name="main_category_id" id="main_category">
                        <option value="">Select Main Category</option>
                        <?php foreach($main_categories as $mc): ?>
                        <option value="<?= $mc->product_category_id ?>"><?= $mc->category_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group sub-category-dropdown" style="display:none;">
                <label class="col-lg-3 control-label">Sub Category <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <select class="form-control" name="sub_category_id" id="sub_category">
                        <option value="">Select Sub Category</option>
                    </select>
                </div>
            </div>









            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label"><?= lang('status') ?></label>

                <div class="col-sm-8">
                    <div class="col-sm-4 row">
                        <div class="checkbox-inline c-checkbox">
                            <label>
                                <input <?= empty($category_info) ? 'checked' : '' ?>
                                    <?= (!empty($category_info->status) && $category_info->status == 'published' ? 'checked' : ''); ?>
                                    class="select_one" type="checkbox" name="status" value="published">
                                <span class="fa fa-check"></span> <?= lang('published') ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox-inline c-checkbox">
                            <label>
                                <input
                                    <?= (!empty($category_info->status) && $category_info->status == 'unpublished' ? 'checked' : ''); ?>
                                    class="select_one" type="checkbox" name="status" value="unpublished">
                                <span class="fa fa-check"></span> <?= lang('unpublished') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>








            <div style="display:none">
                <?php
            if (!empty($category_info)) {
                $category_id = $category_info->category_id;
                $permissionL = $category_info->permission;
            } else {
                $category_id = null;
                $permissionL = null;
            }
            if (!empty($inline)) {
                echo get_permission_modal(3, 9, $permission_user, $permissionL, '');
            } else {
                echo get_permission(3, 9, $permission_user, $permissionL, '');
            }
            ?>
            </div>

            <div class="btn-bottom-toolbar text-right">
                <?php
                if (!empty($category_info)) { ?>
                <button type="submit" class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                <button type="button" onclick="goBack()" class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                <?php } else {
                    ?>
                <button type="submit" class="btn btn-sm btn-primary"><?= lang('create') ?></button>
                <?php }
                ?>
            </div>
        </form>
    </div>
</div>



<script>
$(document).ready(function() {

    $("#category_type").change(function() {
        var type = $(this).val();
        if (type == "Main") {
            $(".main-category-dropdown").hide();
            $(".sub-category-dropdown").hide();
        } else if (type == "Sub Category") {
            $(".main-category-dropdown").show();
            $(".sub-category-dropdown").hide();
        } else if (type == "Grand Sub Category") {
            $(".main-category-dropdown").show();
            $(".sub-category-dropdown").show();
        }
    });

    // Trigger it on page load
    $("#category_type").trigger("change");

    // Listen for main category change → load subcategories
    $("#main_category").change(function() {
        var mainId = $(this).val();
        if ($("#category_type").val() == "Grand Sub Category" && mainId) {
            $.ajax({
                url: "<?= base_url('admin/product_category/get_sub_categories') ?>",
                type: "POST",
                data: {
                    main_id: mainId
                },
                dataType: "json",
                success: function(res) {
                    var html = '<option value="">Select Sub Category</option>';
                    $.each(res, function(i, item) {
                        html += '<option value="' + item.product_category_id +
                            '">' + item.category_name + '</option>';
                    });
                    $("#sub_category").html(html);
                }
            });
        }
    });

    // Auto-load in case of edit
    var selectedMain =
    "<?= !empty($category_info->main_category_id) ? $category_info->main_category_id : '' ?>";
    var selectedSub = "<?= !empty($category_info->sub_category_id) ? $category_info->sub_category_id : '' ?>";

    if (selectedMain) {
        $("#main_category").val(selectedMain).trigger("change");
        if ($("#category_type").val() == "Grand Sub Category") {
            $.ajax({
                url: "<?= base_url('admin/product_category/get_sub_categories') ?>",
                type: "POST",
                data: {
                    main_id: selectedMain
                },
                dataType: "json",
                success: function(res) {
                    var html = '<option value="">Select Sub Category</option>';
                    $.each(res, function(i, item) {
                        html += '<option value="' + item.product_category_id + '" ' + (item
                                .product_category_id == selectedSub ? 'selected' : '') +
                            '>' + item.category_name + '</option>';
                    });
                    $("#sub_category").html(html);
                }
            });
        }
    }

});
</script>