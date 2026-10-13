<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('39', 'created');
$edited = can_action('39', 'edited');
$deleted = can_action('39', 'deleted');

?>
<?php if (!empty($created) || !empty($edited)) { ?>
    <div class="nav-tabs-custom">
        <!-- Tabs within a box -->
        <ul class="nav nav-tabs">
            <li class="active"><a href="<?= base_url('admin/product_category/manage') ?>"><?= lang('manage') . ' ' . lang('Product category') ?></a>
            </li>

            <li class=""><a href="<?= base_url('admin/product_category/create') ?>"><?= lang('new') . ' ' . lang('Propduct category') ?></a></li>

        </ul>
        <div class="tab-content bg-white">
        <?php } else { ?>
            <div class="panel panel-custom">
                <header class="panel-heading ">
                    <div class="panel-title"><strong><?= lang('all') . ' ' . lang('category') ?></strong></div>
                </header>
            <?php } ?>
            <div class="table-responsive">
                <table class="table table-striped DataTables " id="DataTables" width="100%">
                    <thead>
                        <tr>
                             <th><?= lang('category') . ' ' . lang('name') ?></th>
                             <th><?= lang('type') ?></th>
                             <th><?= lang('Product Category') ?></th>
                             <th><?= lang('Product') ?></th>
                             <!-- <th><?= lang('Sub Category') ?></th> -->
                 
                       
                            <?php if (!empty($edited) || !empty($deleted)) { ?>
                                <th class="col-options no-sort"><?= lang('action') ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <script type="text/javascript">
                        list = base_url + "admin/product_category/categoryList";
                    </script>
                </table>
            </div>
            </div>
        </div>
        <div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).on("click", '.make_default', function(e) {
            e.preventDefault();
            var werehouseId = $(this).data().id;
            $.ajax({
                type: 'GET', // define the type of HTTP verb we want to use (POST for our form)
                url: '<?= base_url() ?>admin/category/make_default/' +
                    werehouseId, // the url where we want to POST
                // data: formData, // our data object
                dataType: 'json', // what type of data do we expect back from the server
                encode: true,
                success: function(res) {
                    if (res) {
                        if (res.subview) {
                            // $('#myModal .modal-content').html(res.subview);
                            // $('#myModal').modal('show');

                            console.log(res.subview);
                        } else {
                            // toastr[res.status](res.message);
                        }
                    } else {
                        alert('There was a problem with AJAX');
                    }
                }
            })

        });
    </script>