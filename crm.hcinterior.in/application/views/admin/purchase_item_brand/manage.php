<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="nav-tabs-custom">
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="<?= base_url('admin/purchase_item_brand/manage') ?>">Manage Purchase Item Brand</a>
        </li>
        <li class="">
            <a href="<?= base_url('admin/purchase_item_brand/create') ?>"><i class="fa fa-plus"></i> New Purchase Item Brand</a>
        </li>
    </ul>

    <div class="tab-content bg-white">
        <div class="table-responsive">
            <table class="table table-striped DataTables " id="DataTables" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Brand Name</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th class="col-options no-sort">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <script type="text/javascript">
                    list = base_url + "admin/purchase_item_brand/brandList";
                </script>
            </table>
        </div>
    </div>
</div>
