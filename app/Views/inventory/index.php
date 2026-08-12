<?php $pageTitle = "Inventory Management - StockMaster"; ?>
<?php require_once ROOT_DIR . '/app/Views/layouts/header.php'; ?>

<?php
$defaultCategories = ['General', 'Canned Goods', 'Beverages', 'Snacks', 'Personal Care'];
$existingCategories = $defaultCategories;

if (!empty($products)) {
    $dbCategories = array_filter(array_column($products, 'category'));
    $existingCategories = array_unique(array_merge($defaultCategories, $dbCategories));
    sort($existingCategories);
}

$csrfToken = \App\Core\Security::generateCSRFToken();
?>

<div class="inventory-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Stock Inventory</h1>
            <p class="page-subtitle">Manage store products, stock levels, and retail pricing.</p>
        </div>
        <button id="openModalBtn" class="btn btn-primary">+ Add Product</button>
    </div>

    <div class="inventory-toolbar">
        <div class="search-box">
            <input type="text" id="inventorySearch" placeholder="Search product name, barcode, or category..." class="search-input">
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="inventory-table" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th class="sortable" data-sort="name">Product Name <span class="sort-icon">↕</span></th>
                        <th>Category</th>
                        <th class="sortable" data-sort="cost">Cost Price <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="selling">Selling Price <span class="sort-icon">↕</span></th>
                        <th class="sortable" data-sort="stock">Stock Level <span class="sort-icon">↕</span></th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $index => $product): ?>
                            <?php $minStockAlert = $product['min_stock_alert'] ?? 5; ?>
                            <tr class="product-row" 
                                data-id="<?= $product['id']; ?>"
                                data-original-index="<?= $index; ?>"
                                data-name="<?= strtolower(\App\Core\Security::escape($product['name'])); ?>"
                                data-cost="<?= (float)$product['cost_price']; ?>"
                                data-selling="<?= (float)$product['selling_price']; ?>"
                                data-stock="<?= (int)$product['quantity']; ?>">
                                
                                <td class="col-barcode"><code><?= \App\Core\Security::escape($product['barcode'] ?: 'N/A'); ?></code></td>
                                <td class="col-name"><strong><?= \App\Core\Security::escape($product['name']); ?></strong></td>
                                <td class="col-category"><span class="category-badge"><?= \App\Core\Security::escape($product['category']); ?></span></td>
                                <td>₱<?= number_format($product['cost_price'], 2); ?></td>
                                <td>₱<?= number_format($product['selling_price'], 2); ?></td>
                                
                                <td class="col-stock-control">
                                    <div class="quick-stock-control" data-id="<?= $product['id']; ?>" data-min-alert="<?= $minStockAlert; ?>">
                                        <button type="button" class="btn-stock-qty btn-stock-minus" aria-label="Decrease stock">-</button>
                                        <input type="number" class="stock-input" value="<?= (int)$product['quantity']; ?>" min="0" data-initial-val="<?= (int)$product['quantity']; ?>">
                                        <button type="button" class="btn-stock-qty btn-stock-plus" aria-label="Increase stock">+</button>
                                        <button type="button" class="btn-stock-save" title="Save Stock">✓</button>
                                    </div>
                                </td>

                                <td class="col-status">
                                    <?php if ($product['quantity'] <= 0): ?>
                                        <span class="badge badge-danger">Out of Stock</span>
                                    <?php elseif ($product['quantity'] <= $minStockAlert): ?>
                                        <span class="badge badge-warning">Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">In Stock</span>
                                    <?php endif; ?>
                                </td>

                                <td class="col-actions">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-light edit-btn" data-product='<?= htmlspecialchars(json_encode($product), ENT_QUOTES, 'UTF-8'); ?>'>Edit</button>
                                        <form action="<?= BASE_URL; ?>/inventory/delete" method="POST" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken; ?>">
                                            <input type="hidden" name="id" value="<?= $product['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noProductsRow">
                            <td colspan="8" class="empty-state">No products found in inventory. Click <strong>+ Add Product</strong> to create one.</td>
                        </tr>
                    <?php endif; ?>
                    <tr id="noSearchResultRow" style="display: none;">
                        <td colspan="8" class="empty-state">No matching products found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
                        <?php foreach ($existingCategories as $cat): ?>
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
                        <?php foreach ($existingCategories as $cat): ?>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = "<?= $csrfToken; ?>";
    const baseUrl = "<?= BASE_URL; ?>";

    const stockControls = document.querySelectorAll('.quick-stock-control');

    stockControls.forEach(control => {
        const minusBtn = control.querySelector('.btn-stock-minus');
        const plusBtn = control.querySelector('.btn-stock-plus');
        const input = control.querySelector('.stock-input');
        const saveBtn = control.querySelector('.btn-stock-save');
        const row = control.closest('.product-row');
        const productId = control.getAttribute('data-id');
        const minAlert = parseInt(control.getAttribute('data-min-alert'), 10) || 5;

        function updateSaveButtonState() {
            const currentVal = parseInt(input.value, 10);
            const initialVal = parseInt(input.getAttribute('data-initial-val'), 10);

            if (!isNaN(currentVal) && currentVal !== initialVal && currentVal >= 0) {
                saveBtn.classList.add('active');
            } else {
                saveBtn.classList.remove('active');
            }
        }

        minusBtn.addEventListener('click', function () {
            let val = parseInt(input.value, 10) || 0;
            if (val > 0) {
                input.value = val - 1;
                updateSaveButtonState();
            }
        });

        plusBtn.addEventListener('click', function () {
            let val = parseInt(input.value, 10) || 0;
            input.value = val + 1;
            updateSaveButtonState();
        });

        input.addEventListener('input', updateSaveButtonState);

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (saveBtn.classList.contains('active')) {
                    saveStock();
                }
            }
        });

        saveBtn.addEventListener('click', saveStock);

        async function saveStock() {
            const newQty = parseInt(input.value, 10);
            if (isNaN(newQty) || newQty < 0) return;

            saveBtn.textContent = '...';
            saveBtn.disabled = true;

            try {
                const response = await fetch(`${baseUrl}/inventory/update-stock`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        csrf_token: csrfToken,
                        id: productId,
                        quantity: newQty
                    })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                input.setAttribute('data-initial-val', newQty);
                row.setAttribute('data-stock', newQty);

                const statusTd = row.querySelector('.col-status');
                if (newQty <= 0) {
                    statusTd.innerHTML = '<span class="badge badge-danger">Out of Stock</span>';
                } else if (newQty <= minAlert) {
                    statusTd.innerHTML = '<span class="badge badge-warning">Low Stock</span>';
                } else {
                    statusTd.innerHTML = '<span class="badge badge-success">In Stock</span>';
                }

                saveBtn.textContent = '✓';
                saveBtn.classList.remove('active');
                saveBtn.classList.add('saved');

                setTimeout(() => {
                    saveBtn.classList.remove('saved');
                    saveBtn.disabled = false;
                }, 1500);

            } catch (err) {
                console.error('Error updating stock quantity:', err);
                alert('Failed to update stock level.');
                saveBtn.textContent = '✓';
                saveBtn.disabled = false;
            }
        }
    });

    const tableBody = document.getElementById('inventoryTableBody');
    const sortableHeaders = document.querySelectorAll('.sortable');
    let currentSort = { key: null, state: 'none' };

    sortableHeaders.forEach(header => {
        header.addEventListener('click', function () {
            const sortKey = this.getAttribute('data-sort');

            if (currentSort.key === sortKey) {
                if (currentSort.state === 'asc') {
                    currentSort.state = 'desc';
                } else if (currentSort.state === 'desc') {
                    currentSort.state = 'none';
                    currentSort.key = null;
                }
            } else {
                currentSort.key = sortKey;
                currentSort.state = 'asc';
            }

            sortableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
                h.querySelector('.sort-icon').textContent = '↕';
            });

            if (currentSort.state === 'asc') {
                this.classList.add('sort-asc');
                this.querySelector('.sort-icon').textContent = '▲';
            } else if (currentSort.state === 'desc') {
                this.classList.add('sort-desc');
                this.querySelector('.sort-icon').textContent = '▼';
            }

            const rows = Array.from(tableBody.querySelectorAll('.product-row'));

            rows.sort((a, b) => {
                if (currentSort.state === 'none') {
                    const indexA = parseInt(a.getAttribute('data-original-index'), 10);
                    const indexB = parseInt(b.getAttribute('data-original-index'), 10);
                    return indexA - indexB;
                }

                let valA = a.getAttribute(`data-${currentSort.key}`);
                let valB = b.getAttribute(`data-${currentSort.key}`);

                if (currentSort.key === 'cost' || currentSort.key === 'selling' || currentSort.key === 'stock') {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                }

                if (valA < valB) return currentSort.state === 'asc' ? -1 : 1;
                if (valA > valB) return currentSort.state === 'asc' ? 1 : -1;
                return 0;
            });

            const noProductsRow = document.getElementById('noProductsRow');
            const noSearchResultRow = document.getElementById('noSearchResultRow');

            rows.forEach(row => tableBody.appendChild(row));
            if (noProductsRow) tableBody.appendChild(noProductsRow);
            if (noSearchResultRow) tableBody.appendChild(noSearchResultRow);
        });
    });

    const searchInput = document.getElementById('inventorySearch');
    const productRows = document.querySelectorAll('.product-row');
    const noSearchResultRow = document.getElementById('noSearchResultRow');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            productRows.forEach(row => {
                const barcode = row.querySelector('.col-barcode').textContent.toLowerCase();
                const name = row.querySelector('.col-name').textContent.toLowerCase();
                const category = row.querySelector('.col-category').textContent.toLowerCase();

                if (barcode.includes(query) || name.includes(query) || category.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noSearchResultRow) {
                noSearchResultRow.style.display = (visibleCount === 0 && productRows.length > 0) ? '' : 'none';
            }
        });
    }

    function setupCustomCategoryToggle(selectId, inputId) {
        const selectElem = document.getElementById(selectId);
        const inputElem = document.getElementById(inputId);

        selectElem.addEventListener('change', function () {
            if (this.value === 'custom') {
                inputElem.style.display = 'block';
                inputElem.required = true;
                inputElem.focus();
            } else {
                inputElem.style.display = 'none';
                inputElem.required = false;
                inputElem.value = '';
            }
        });
    }

    setupCustomCategoryToggle('categorySelect', 'addCustomCategory');
    setupCustomCategoryToggle('editCategorySelect', 'editCustomCategory');

    const addModal = document.getElementById('productModal');
    const openAddBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');

    function openAddModal() { addModal.classList.add('active'); }
    function closeAddModal() { 
        addModal.classList.remove('active');
        document.getElementById('addCustomCategory').style.display = 'none';
        document.getElementById('addCustomCategory').required = false;
    }

    openAddBtn.addEventListener('click', openAddModal);
    closeModalBtn.addEventListener('click', closeAddModal);
    cancelModalBtn.addEventListener('click', closeAddModal);

    const editModal = document.getElementById('editProductModal');
    const closeEditBtn = document.getElementById('closeEditModalBtn');
    const cancelEditBtn = document.getElementById('cancelEditModalBtn');
    const editButtons = document.querySelectorAll('.edit-btn');

    function closeEditModal() { 
        editModal.classList.remove('active'); 
        document.getElementById('editCustomCategory').style.display = 'none';
        document.getElementById('editCustomCategory').required = false;
    }

    closeEditBtn.addEventListener('click', closeEditModal);
    cancelEditBtn.addEventListener('click', closeEditModal);

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const product = JSON.parse(this.getAttribute('data-product'));
            
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_barcode').value = product.barcode || '';
            document.getElementById('edit_quantity').value = product.quantity;
            document.getElementById('edit_cost_price').value = product.cost_price;
            document.getElementById('edit_selling_price').value = product.selling_price;

            const editSelect = document.getElementById('editCategorySelect');
            const editCustomInput = document.getElementById('editCustomCategory');
            let optionExists = false;

            for (let i = 0; i < editSelect.options.length; i++) {
                if (editSelect.options[i].value === product.category) {
                    optionExists = true;
                    break;
                }
            }

            if (optionExists) {
                editSelect.value = product.category;
                editCustomInput.style.display = 'none';
                editCustomInput.required = false;
            } else {
                editSelect.value = 'custom';
                editCustomInput.style.display = 'block';
                editCustomInput.value = product.category;
                editCustomInput.required = true;
            }
            
            editModal.classList.add('active');
        });
    });

    window.addEventListener('click', function (e) {
        if (e.target === addModal) closeAddModal();
        if (e.target === editModal) closeEditModal();
    });
});
</script>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>