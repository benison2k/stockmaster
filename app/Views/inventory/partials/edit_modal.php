<div id="editProductModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Product</h2>
            <button id="closeEditModalBtn" class="close-btn">&times;</button>
        </div>

        <form action="<?= BASE_URL; ?>/inventory/update" method="POST" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
            <input type="hidden" id="edit_id" name="id">

            <div class="form-group">
                <label for="edit_name">Product Name *</label>
                <input type="text" id="edit_name" name="name" required>
            </div>

            <div class="form-group">
                <label for="edit_barcode">Barcode (Optional)</label>
                <input type="text" id="edit_barcode" name="barcode">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editCategorySelect">Category</label>
                    <select id="editCategorySelect" name="category" class="category-dropdown">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= \App\Core\Security::escape($cat); ?>"><?= \App\Core\Security::escape($cat); ?></option>
                        <?php endforeach; ?>
                        <option value="custom" style="font-weight: bold; color: var(--primary);">+ Create Custom Category...</option>
                    </select>
                    <input type="text" id="editCustomCategory" name="custom_category" placeholder="Enter new category name" class="custom-cat-input" style="display: none; margin-top: 0.5rem;">
                </div>
                <div class="form-group">
                    <label for="edit_quantity">Current Quantity *</label>
                    <input type="number" id="edit_quantity" name="quantity" min="0" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edit_cost_price">Cost Price (₱) *</label>
                    <input type="number" step="0.01" id="edit_cost_price" name="cost_price" required>
                </div>
                <div class="form-group">
                    <label for="edit_selling_price">Selling Price (₱) *</label>
                    <input type="number" step="0.01" id="edit_selling_price" name="selling_price" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="cancelEditModalBtn" class="btn btn-light">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>