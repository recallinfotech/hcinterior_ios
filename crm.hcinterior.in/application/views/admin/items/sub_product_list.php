<form method="post" action="<?= base_url('admin/items/save_associated_items') ?>">
    <input type="hidden" name="main_id" value="<?= $main_id ?>">

    <!-- Sub Products -->
    <div class="form-group">
        <label><strong>Sub Products:</strong></label>
        <div>
            <?php if (!empty($sub_products)): ?>
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
            <?php else: ?>
                <em>No Sub Products Found</em>
            <?php endif; ?>
        </div>
    </div>

    <!-- Child Products -->
    <div class="form-group">
        <label><strong>Child Products:</strong></label>
        <div>
            <?php if (!empty($child_products)): ?>
                <?php foreach($child_products as $child): ?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="associated_items[]" 
                                   value="<?= $child->saved_items_id ?>"
                                   <?= in_array($child->saved_items_id, $linked_ids) ? 'checked' : '' ?>>
                            <?= $child->item_name ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <em>No Child Products Found</em>
            <?php endif; ?>
        </div>
    </div>

    <button type="submit" class="btn btn-success">Save Association</button>
</form>
