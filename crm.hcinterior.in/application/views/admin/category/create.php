
<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class=""><a
                    href="<?= base_url('admin/category/manage') ?>"><?= lang('manage') . ' ' . lang('category') ?></a>
        </li>
        <li class="active"><a
                    href="<?= base_url('admin/category/create') ?>"><?= lang('new') . ' ' . lang('category') ?></a>
        </li>
    </ul>
    <div class="tab-content bg-white">
        <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="category_form"
              action="<?= base_url('admin/category/save_category/' . (!empty($category_info) ? $category_info->category_id : '0') . '/' . $inline); ?>"
              method="post" class="form-horizontal  <?= (!empty($inline) ? 'myInline' : '') ?> ">

          <input type="hidden" name="apply_price_update" id="apply_price_update" value="0">
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
          
          
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('Select Type') ?> <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                
    <select class="form-control" name="type" id="category_type">
    <option value="">Select Type</option>
    <option value="Main" <?= (!empty($category_info->type) && $category_info->type == 'Main' ? 'selected' : '') ?>>Main</option>
    <option value="Sub Category" <?= (!empty($category_info->type) && $category_info->type == 'Sub Category' ? 'selected' : '') ?>>Sub Category</option>
    <option value="Grand Sub Category" <?= (!empty($category_info->type) && $category_info->type == 'Grand Sub Category' ? 'selected' : '') ?>>Grand Sub Category</option>
    </select>
                </div>
            </div>
             
            <?php 
$isMainEdit = (!empty($category_info) && $category_info->type == 'Main');
?>

<div class="form-group increase-price-field" style="<?= $isMainEdit ? '' : 'display:none;' ?>">

<a href="<?= base_url('admin/category/export_items_sql/'.$category_info->category_id) ?>" 
   class="btn btn-success btn-sm" target="_blank">
  Backup Item Price
</a>

    <label class="col-lg-3 control-label">Increase Price (+/-)</label>
    <div class="col-lg-5">
        <input type="number" step="1" class="form-control" 
               name="increase_price" 
               placeholder="Enter +2 or -2"
               value="<?= !empty($category_info->increase_price) ? $category_info->increase_price : '' ?>">
        <small class="text-muted">Example: 2 or -2</small>
    </div>
</div>

            <div class="form-group main-category-dropdown" style="display:none;">
    <label class="col-lg-3 control-label">Main Category <span class="text-danger">*</span></label>
    <div class="col-lg-5">
        <select class="form-control" name="main_category_id" id="main_category">
            <option value="">Select Main Category</option>
            <?php foreach($main_categories as $mc): ?>
                <option value="<?= $mc->category_id ?>"><?= $mc->category_name ?></option>
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
                                <input
                                    <?= empty($category_info) ? 'checked' : '' ?>
                                    <?= (!empty($category_info->status) && $category_info->status == 'published' ? 'checked' : ''); ?>
                                        class="select_one" type="checkbox" name="status" value="published">
                                <span class="fa fa-check"></span> <?= lang('published') ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox-inline c-checkbox">
                            <label>
                                <input <?= (!empty($category_info->status) && $category_info->status == 'unpublished' ? 'checked' : ''); ?>
                                        class="select_one" type="checkbox" name="status" value="unpublished">
                                <span class="fa fa-check"></span> <?= lang('unpublished') ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>








                                <div style="display:none" >
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
                    <button type="button" onclick="goBack()"
                            class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
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
     $(document).ready(function () {

    $("#category_type").change(function () {
        var type = $(this).val();
        if (type == "Main") {
            $(".main-category-dropdown").hide();
            $(".sub-category-dropdown").hide();
        }
        else if (type == "Sub Category") {
            $(".main-category-dropdown").show();
            $(".sub-category-dropdown").hide();
        }
        else if (type == "Grand Sub Category") {
            $(".main-category-dropdown").show();
            $(".sub-category-dropdown").show();
        }
    });

    // Trigger it on page load
    $("#category_type").trigger("change");

    // Listen for main category change → load subcategories
    $("#main_category").change(function () {
        var mainId = $(this).val();
        if ($("#category_type").val() == "Grand Sub Category" && mainId) {
            $.ajax({
                url: "<?= base_url('admin/category/get_sub_categories') ?>",
                type: "POST",
                data: { main_id: mainId },
                dataType: "json",
                success: function (res) {
                    var html = '<option value="">Select Sub Category</option>';
                    $.each(res, function (i, item) {
                        html += '<option value="' + item.category_id + '">' + item.category_name + '</option>';
                    });
                    $("#sub_category").html(html);
                }
            });
        }
    });

    // Auto-load in case of edit
    var selectedMain = "<?= !empty($category_info->main_category_id) ? $category_info->main_category_id : '' ?>";
    var selectedSub  = "<?= !empty($category_info->sub_category_id) ? $category_info->sub_category_id : '' ?>";

    if (selectedMain) {
        $("#main_category").val(selectedMain).trigger("change");
        if ($("#category_type").val() == "Grand Sub Category") {
            $.ajax({
                url: "<?= base_url('admin/category/get_sub_categories') ?>",
                type: "POST",
                data: { main_id: selectedMain },
                dataType: "json",
                success: function (res) {
                    var html = '<option value="">Select Sub Category</option>';
                    $.each(res, function (i, item) {
                        html += '<option value="' + item.category_id + '" ' + (item.category_id == selectedSub ? 'selected' : '') + '>' + item.category_name + '</option>';
                    });
                    $("#sub_category").html(html);
                }
            });
        }
    }

});


$("#category_type").change(function () {
    var type = $(this).val();

    if (type == "Main") {
        $(".main-category-dropdown").hide();
        $(".sub-category-dropdown").hide();

        // Show only if edit mode
        <?php if(!empty($category_info)) { ?>
            $(".increase-price-field").show();
        <?php } else { ?>
            $(".increase-price-field").hide();
        <?php } ?>
    }
    else if (type == "Sub Category") {
        $(".main-category-dropdown").show();
        $(".sub-category-dropdown").hide();
        $(".increase-price-field").hide();
    }
    else if (type == "Grand Sub Category") {
        $(".main-category-dropdown").show();
        $(".sub-category-dropdown").show();
        $(".increase-price-field").hide();
    }
});

 
 $('#category_form').on('submit', function(e) {

    var increasePrice = $('input[name="increase_price"]').val();

    if (increasePrice !== '' && increasePrice !== null) {

        var confirmUpdate = confirm("Do you want to update all item prices by " + increasePrice + "%?");

        if (confirmUpdate) {
            $('#apply_price_update').val(1);
        } else {
            $('#apply_price_update').val(0);
        }
    }
});
</script>

