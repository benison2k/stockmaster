<div id="productModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Product</h2>
            <button id="closeModalBtn" class="close-btn">&times;</button>
        </div>

        <form action="<?= BASE_URL; ?>/inventory/add" method="POST" class="modal-form">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required placeholder="e.g. Century Tuna Flakes 180g">
            </div>

            <div class="form-group">
                <label for="barcode">Barcode (Optional)</label>
                <input type="text" id="barcode" name="barcode" placeholder="e.g. 4800016021021">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="categorySelect">Category</label>
                    <select id="categorySelect" name="category" class="category-dropdown">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= \App\Core\Security::escape($cat); ?>"><?= \App\Core\Security::escape($cat); ?></option>
                        <?php endforeach; ?>
                        <option value="custom" style="font-weight: bold; color: var(--primary);">+ Create Custom Category...</option>
                    </select>
                    <input type="text" id="addCustomCategory" name="custom_category" placeholder="Enter new category name" class="custom-cat-input" style="display: none; margin-top: 0.5rem;">
                </div>
                <div class="form-group">
                    <label for="quantity">Initial Quantity *</label>
                    <input type="number" id="quantity" name="quantity" min="0" value="0" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cost_price">Cost Price (₱) *</label>
                    <input type="number" step="0.01" id="cost_price" name="cost_price" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label for="selling_price">Selling Price (₱) *</label>
                    <input type="number" step="0.01" id="selling_price" name="selling_price" placeholder="0.00" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="cancelModalBtn" class="btn btn-light">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>