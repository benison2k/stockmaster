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

<style>
.pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color, #e2e8f0);
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text-muted, #64748b);
}
.pagination-buttons {
    display: flex;
    gap: 0.35rem;
    align-items: center;
}
.pagination-ellipsis {
    padding: 0 0.25rem;
}
</style>

<div class="inventory-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Stock Inventory</h1>
            <p class="page-subtitle">Manage store products, stock levels, and retail pricing.</p>
        </div>
        <button id="openModalBtn" class="btn btn-primary">+ Add Product</button>
    </div>

    <div class="inventory-toolbar" style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 250px;">
            <input type="text" id="inventorySearch" placeholder="Search product name, barcode, or category..." class="search-input">
        </div>
        <button type="button" id="filterLowStockBtn" class="btn btn-light" style="border: 1px solid var(--border-color); white-space: nowrap;">
            ⚠️ View Resupply List (<span id="lowStockCount">0</span>)
        </button>
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
        <!-- Dynamic Pagination Bar injected here -->
        <div id="inventoryPagination" class="pagination-bar" style="display: none;"></div>
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
    const productRows = Array.from(document.querySelectorAll('.product-row'));
    const tableBody = document.getElementById('inventoryTableBody');
    const noProductsRow = document.getElementById('noProductsRow');
    const noSearchResultRow = document.getElementById('noSearchResultRow');
    const searchInput = document.getElementById('inventorySearch');

    // --- PAGINATION CONFIGURATION ---
    let currentPage = 1;
    const rowsPerPage = 10;

    // --- LOW STOCK / RESUPPLY FILTER LOGIC ---
    const filterLowStockBtn = document.getElementById('filterLowStockBtn');
    const lowStockCountSpan = document.getElementById('lowStockCount');
    let showingLowStockOnly = false;

    function updateLowStockCount() {
        let count = 0;
        productRows.forEach(row => {
            const statusBadge = row.querySelector('.col-status .badge');
            if (statusBadge && (statusBadge.classList.contains('badge-danger') || statusBadge.classList.contains('badge-warning'))) {
                count++;
            }
        });
        if (lowStockCountSpan) {
            lowStockCountSpan.textContent = count;
        }
    }
    updateLowStockCount();

    if (filterLowStockBtn) {
        filterLowStockBtn.addEventListener('click', function () {
            showingLowStockOnly = !showingLowStockOnly;
            currentPage = 1; // Reset to page 1 on filter change

            if (showingLowStockOnly) {
                this.classList.remove('btn-light');
                this.classList.add('btn-warning');
                this.style.color = '#fff';
                this.textContent = `Showing All Products`;
            } else {
                this.classList.remove('btn-warning');
                this.classList.add('btn-light');
                this.style.color = '';
                this.innerHTML = `⚠️ View Resupply List (<span id="lowStockCount">${document.querySelectorAll('.product-row .badge-danger, .product-row .badge-warning').length}</span>)`;
            }
            updateTableDisplay();
        });
    }

    // --- SORTING STATE ---
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

            currentPage = 1;
            updateTableDisplay();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentPage = 1;
            updateTableDisplay();
        });
    }

    // --- CENTRAL TABLE RENDER / FILTER / SORT / PAGINATE ENGINE ---
    function updateTableDisplay() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let filteredRows = [];

        productRows.forEach(row => {
            const barcode = row.querySelector('.col-barcode').textContent.toLowerCase();
            const name = row.querySelector('.col-name').textContent.toLowerCase();
            const category = row.querySelector('.col-category').textContent.toLowerCase();
            const statusBadge = row.querySelector('.col-status .badge');
            const isLowOrOut = statusBadge && (statusBadge.classList.contains('badge-danger') || statusBadge.classList.contains('badge-warning'));

            const matchesSearch = barcode.includes(query) || name.includes(query) || category.includes(query);
            const matchesResupply = !showingLowStockOnly || isLowOrOut;

            if (matchesSearch && matchesResupply) {
                filteredRows.push(row);
            }
        });

        // Apply sorting
        if (currentSort.key && currentSort.state !== 'none') {
            filteredRows.sort((a, b) => {
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
        } else {
            // Default sort by original template index
            filteredRows.sort((a, b) => {
                return parseInt(a.getAttribute('data-original-index'), 10) - parseInt(b.getAttribute('data-original-index'), 10);
            });
        }

        // Pagination calculations
        const totalFiltered = filteredRows.length;
        const totalPages = Math.ceil(totalFiltered / rowsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const paginatedRows = filteredRows.slice(startIndex, endIndex);

        // Hide all rows first
        productRows.forEach(row => row.style.display = 'none');

        // Show only paginated slice and re-append in correct order
        paginatedRows.forEach(row => {
            row.style.display = '';
            tableBody.appendChild(row);
        });

        if (noProductsRow) tableBody.appendChild(noProductsRow);
        if (noSearchResultRow) {
            tableBody.appendChild(noSearchResultRow);
            noSearchResultRow.style.display = (totalFiltered === 0 && productRows.length > 0) ? '' : 'none';
        }

        renderPaginationControls(totalFiltered, totalPages);
    }

    function renderPaginationControls(totalFiltered, totalPages) {
        const paginationContainer = document.getElementById('inventoryPagination');
        if (!paginationContainer) return;

        if (totalFiltered <= rowsPerPage && totalPages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        } else {
            paginationContainer.style.display = 'flex';
        }

        const startItem = totalFiltered === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
        const endItem = Math.min(currentPage * rowsPerPage, totalFiltered);

        let html = `
            <div class="pagination-info">Showing <strong>${startItem}</strong> to <strong>${endItem}</strong> of <strong>${totalFiltered}</strong> products</div>
            <div class="pagination-buttons">
                <button type="button" class="btn btn-sm btn-light" ${currentPage === 1 ? 'disabled' : ''} id="prevPageBtn">Previous</button>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<button type="button" class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-light'} page-num-btn" data-page="${i}">${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
        }

        html += `
                <button type="button" class="btn btn-sm btn-light" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''} id="nextPageBtn">Next</button>
            </div>
        `;

        paginationContainer.innerHTML = html;

        document.getElementById('prevPageBtn')?.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                updateTableDisplay();
            }
        });

        document.getElementById('nextPageBtn')?.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                updateTableDisplay();
            }
        });

        paginationContainer.querySelectorAll('.page-num-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentPage = parseInt(btn.getAttribute('data-page'), 10);
                updateTableDisplay();
            });
        });
    }

    // Initialize display on load
    updateTableDisplay();

    // --- QUICK STOCK CONTROLS & AJAX SAVE ---
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

                updateLowStockCount();
                updateTableDisplay();

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

    // --- CATEGORY TOGGLE & MODAL HELPERS ---
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