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
                    <!-- <input type="text" class="form-control" value="<?php
                    if (!empty($category_info)) {
                        echo $category_info->phone;
                    }
                    ?>" name="phone" required=""> -->

    <select class="form-control" name="type" id="category_type">
    <option value="">Select Type</option>
    <option value="Main" <?= (!empty($category_info->type) && $category_info->type == 'Main' ? 'selected' : '') ?>>Main</option>
    <option value="Sub Category" <?= (!empty($category_info->type) && $category_info->type == 'Sub Category' ? 'selected' : '') ?>>Sub Category</option>
    <option value="Grand Sub Category" <?= (!empty($category_info->type) && $category_info->type == 'Grand Sub Category' ? 'selected' : '') ?>>Grand Sub Category</option>
</select>
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
           
<div class="image_section" style="display:none;">
            <div class="form-group "  >
                <label class="col-lg-3 control-label"><?= lang('image') ?></label>
                <div class="col-lg-9">
                    <div class="fileinput fileinput-new" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="width: 210px;">
                            <?php if (!empty($category_info->image)) : ?>
                                <img src="<?php echo base_url($category_info->image); ?>">
                            <?php else : ?>
                                <img src="<?= base_url('uploads/default_avatar.jpg') ?>"
                                     alt="Please Connect Your Internet">
                            <?php endif; ?>
                        </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="width: 210px;"></div>
                        <div>
                            <span class="btn btn-default btn-file">
                                <span class="fileinput-new">
                                    <input type="file" name="image" value="upload"
                                           data-buttonText="<?= lang('choose_file') ?>" id="myImg"/>
                                    <span class="fileinput-exists"><?= lang('change') ?></span>
                                </span>
                                <a href="#" class="btn btn-default fileinput-exists"
                                   data-dismiss="fileinput"><?= lang('remove') ?></a>

                        </div>

                        <div id="valid_msg" style="color: #e11221"></div>

                    </div>
                </div>
            </div>


               <div class="form-group">
                <label class="col-lg-3 control-label">Description </label>
                <div class="col-lg-5">
                    <textarea  rows="4" class="form-control"  name="description"  ><?php
                    if (!empty($category_info)) {
                        echo $category_info->description;
                    }
                    ?></textarea>
                </div>
               </div>

               <div class="form-group">
                <label class="col-lg-3 control-label">Area </label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" value="<?php
                    if (!empty($category_info)) {
                        echo $category_info->area;
                    }
                    ?>" name="area"  >
                </div>
            </div>
               <div class="form-group">
                <label class="col-lg-3 control-label">Unit </label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" value="<?php
                    if (!empty($category_info)) {
                        echo $category_info->unit;
                    }
                    ?>" name="unit"  >
                </div>
            </div>
               <div class="form-group">
                <label class="col-lg-3 control-label">Unit Rate </label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" value="<?php
                    if (!empty($category_info)) {
                        echo $category_info->unit_rate;
                    }
                    ?>" name="unit_rate"  >
                </div>
            </div>

               
               <div class="form-group">
                <label class="col-lg-3 control-label">Remark</label>
                <div class="col-lg-5">
                    <input type="text" class="form-control" value="<?php
                    if (!empty($category_info)) {
                        echo $category_info->remark;
                    }
                    ?>" name="remark"  >
                </div>
            </div>


            <div class="form-group">
                <label class="col-lg-3 control-label">Item Link <span class="text-danger">*</span></label>
                <div class="col-lg-5">
                    <select class="form-control" name="item_link[]" id="item_link" multiple>
                        <option value="" disabled>Select item</option>
                        <?php foreach($items_info as $item): ?>
                            <option value="<?= $item->saved_items_id ?>"
                                <?= in_array($item->saved_items_id, $selected_item_links) ? 'selected' : '' ?>>
                                <?= $item->item_name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
$(document).ready(function(){
    // Your existing change handler
    $("#category_type").change(function(){
        var type = $(this).val();
        if(type == "Main"){
            $(".main-category-dropdown").hide();
            $(".sub-category-dropdown").hide();
            $(".image_section").show();
        }
        else if(type == "Sub Category"){
            $(".main-category-dropdown").show();
            $(".sub-category-dropdown").hide();
            $(".image_section").hide();
        }
        else if(type == "Grand Sub Category"){
            $(".main-category-dropdown").show();
            $(".sub-category-dropdown").show();
            $(".image_section").hide();
        }
    });

    // Trigger it on page load
    $("#category_type").trigger("change");

    // Handle auto-load sub-categories on edit
    var selectedMain = "<?= !empty($category_info->main_category_id) ? $category_info->main_category_id : '' ?>";
    var selectedSub  = "<?= !empty($category_info->sub_category_id) ? $category_info->sub_category_id : '' ?>";

    if(selectedMain){
        $("#main_category").val(selectedMain);

        if($("#category_type").val() == "Grand Sub Category"){
            $.ajax({
                url: "<?= base_url('admin/category/get_sub_categories') ?>",
                type: "POST",
                data: { main_id: selectedMain },
                dataType: "json",
                success: function(res){
                    var html = '<option value="">Select Sub Category</option>';
                    $.each(res, function(i, item){
                        html += '<option value="'+item.category_id+'" '+(item.category_id == selectedSub ? 'selected' : '')+'>'+item.category_name+'</option>';
                    });
                    $("#sub_category").html(html);
                }
            });
        }
    }
});

</script>

