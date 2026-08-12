document.addEventListener('DOMContentLoaded', function () {

    // --- 1. QUICK STOCK UPDATE LOGIC ---
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
                const response = await fetch(`${BASE_URL}/inventory/update-stock`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        csrf_token: CSRF_TOKEN,
                        id: productId,
                        quantity: newQty
                    })
                });

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

    // --- 2. 3-STATE TABLE SORTING LOGIC ---
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

    // --- 3. REAL-TIME SEARCH FILTER ---
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

    // --- 4. CUSTOM CATEGORY TOGGLE LOGIC ---
    function setupCustomCategoryToggle(selectId, inputId) {
        const selectElem = document.getElementById(selectId);
        const inputElem = document.getElementById(inputId);

        if (!selectElem || !inputElem) return;

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

    // --- 5. MODAL LOGIC ---
    const addModal = document.getElementById('productModal');
    const openAddBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');

    function openAddModal() { addModal.classList.add('active'); }
    function closeAddModal() { 
        addModal.classList.remove('active');
        const customCat = document.getElementById('addCustomCategory');
        if (customCat) {
            customCat.style.display = 'none';
            customCat.required = false;
        }
    }

    if (openAddBtn) openAddBtn.addEventListener('click', openAddModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeAddModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeAddModal);

    const editModal = document.getElementById('editProductModal');
    const closeEditBtn = document.getElementById('closeEditModalBtn');
    const cancelEditBtn = document.getElementById('cancelEditModalBtn');
    const editButtons = document.querySelectorAll('.edit-btn');

    function closeEditModal() { 
        editModal.classList.remove('active'); 
        const editCustomCat = document.getElementById('editCustomCategory');
        if (editCustomCat) {
            editCustomCat.style.display = 'none';
            editCustomCat.required = false;
        }
    }

    if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditModal);
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);

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