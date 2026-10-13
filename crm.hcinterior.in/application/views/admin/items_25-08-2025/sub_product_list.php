   <form method="post" action="<?= base_url('admin/items/save_associated_items') ?>">
    <input type="hidden" name="main_id" value="<?= $main_id ?>">

    <div class="form-group">
        <label>Select Sub Products:</label>
        <div>
        <?php foreach($sub_products as $sub): ?>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="associated_items[]" 
                           value="<?= $sub->saved_items_id ?>"
                           <?= in_array($sub->saved_items_id, $linked_ids) ? 'checked' : '' ?>>
                    <?= $sub->item_name ?>
                </label>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-success">Save Association</button>
</form>