<?php $pageTitle = "Sales Bookkeeping - StockMaster"; ?>
<?php require_once ROOT_DIR . '/app/Views/layouts/header.php'; ?>

<?php $csrfToken = \App\Core\Security::generateCSRFToken(); ?>

<style>
.pos-container {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 1.5rem;
    height: calc(100vh - 140px);
}
@media (max-width: 1024px) {
    .pos-container { grid-template-columns: 1fr; height: auto; }
}
.pos-catalog-card, .pos-cart-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.pos-header-bar {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.pos-product-grid {
    padding: 1.25rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1rem;
    overflow-y: auto;
    max-height: calc(100vh - 250px);
}
.pos-product-tile {
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fafafa;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.pos-product-tile:hover {
    border-color: var(--primary, #2563eb);
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}
.pos-product-tile.out-of-stock {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f1f5f9;
}
.cart-items-list {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    max-height: calc(100vh - 360px);
}
.cart-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color, #f1f5f9);
}
.cart-summary-footer {
    padding: 1.25rem;
    border-top: 1px solid var(--border-color, #e2e8f0);
    background: #f8fafc;
}
.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
}
</style>

<div class="pos-container">
    <!-- Left: Product Catalog Selection -->
    <div class="pos-catalog-card">
        <div class="pos-header-bar">
            <h2 class="page-title" style="font-size: 1.25rem; margin:0;">Quick Sale Catalog</h2>
            <div class="search-box" style="width: 250px;">
                <input type="text" id="posSearch" placeholder="Search products..." class="search-input" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
            </div>
        </div>
        <div class="pos-product-grid" id="posProductGrid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="pos-product-tile <?= $product['quantity'] <= 0 ? 'out-of-stock' : ''; ?>"
                         data-id="<?= $product['id']; ?>"
                         data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES); ?>"
                         data-price="<?= $product['selling_price']; ?>"
                         data-stock="<?= $product['quantity']; ?>">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($product['category']); ?></div>
                            <div style="font-weight: 600; font-size: 0.95rem; margin: 0.25rem 0;"><?= htmlspecialchars($product['name']); ?></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                            <span style="font-weight: 700; color: var(--primary);">₱<?= number_format($product['selling_price'], 2); ?></span>
                            <span style="font-size: 0.75rem;" class="badge <?= $product['quantity'] > 0 ? 'badge-success' : 'badge-danger'; ?>">
                                Stock: <?= $product['quantity']; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 2rem;">No products available.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Active Cart & Bookkeeping Summary -->
    <div class="pos-cart-card">
        <div class="pos-header-bar">
            <h2 style="font-size: 1.25rem; margin:0;">Current Sale</h2>
            <button type="button" id="clearCartBtn" class="btn btn-sm btn-light" style="color: var(--danger, #dc2626);">Clear Cart</button>
        </div>

        <div class="cart-items-list" id="cartItemsList">
            <div id="emptyCartMessage" style="text-align: center; color: var(--text-muted); margin-top: 3rem;">
                Cart is empty. Click products from the catalog to add them.
            </div>
        </div>

        <div class="cart-summary-footer">
            <div class="total-row">
                <span>Total Amount:</span>
                <span id="cartTotalDisplay">₱0.00</span>
            </div>
            <button type="button" id="completeSaleBtn" class="btn btn-primary" style="width: 100%; padding: 0.75rem;" disabled>
                Record Sale & Update Stock
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = "<?= $csrfToken; ?>";
    const baseUrl = "<?= BASE_URL; ?>";
    const productTiles = document.querySelectorAll('.pos-product-tile:not(.out-of-stock)');
    const cartItemsList = document.getElementById('cartItemsList');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const cartTotalDisplay = document.getElementById('cartTotalDisplay');
    const completeSaleBtn = document.getElementById('completeSaleBtn');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const posSearch = document.getElementById('posSearch');

    let cart = {};

    // Search filter for catalog
    if (posSearch) {
        posSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('.pos-product-tile').forEach(tile => {
                const name = tile.getAttribute('data-name').toLowerCase();
                tile.style.display = name.includes(query) ? '' : 'none';
            });
        });
    }

    productTiles.forEach(tile => {
        tile.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = parseFloat(this.getAttribute('data-price'));
            const maxStock = parseInt(this.getAttribute('data-stock'), 10);

            if (!cart[id]) {
                cart[id] = { id, name, price, qty: 1, maxStock };
            } else {
                if (cart[id].qty < maxStock) {
                    cart[id].qty++;
                } else {
                    alert('Cannot exceed available stock level.');
                    return;
                }
            }
            renderCart();
        });
    });

    function renderCart() {
        cartItemsList.innerHTML = '';
        let total = 0;
        const keys = Object.keys(cart);

        if (keys.length === 0) {
            cartItemsList.appendChild(emptyCartMessage);
            cartTotalDisplay.textContent = '₱0.00';
            completeSaleBtn.disabled = true;
            return;
        }

        keys.forEach(id => {
            const item = cart[id];
            const subtotal = item.price * item.qty;
            total += subtotal;

            const row = document.createElement('div');
            row.className = 'cart-item-row';
            row.innerHTML = `
                <div style="flex: 1; padding-right: 0.5rem;">
                    <div style="font-weight: 600; font-size: 0.9rem;">${item.name}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">₱${item.price.toFixed(2)} each</div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-sm btn-light decrease-qty" data-id="${id}" style="padding: 0.1rem 0.4rem;">-</button>
                    <span style="font-weight: 600; font-size: 0.9rem; min-width: 20px; text-align: center;">${item.qty}</span>
                    <button type="button" class="btn btn-sm btn-light increase-qty" data-id="${id}" style="padding: 0.1rem 0.4rem;">+</button>
                </div>
                <div style="text-align: right; min-width: 70px; font-weight: 600; font-size: 0.9rem;">
                    ₱${subtotal.toFixed(2)}
                </div>
            `;
            cartItemsList.appendChild(row);
        });

        cartTotalDisplay.textContent = `₱${total.toFixed(2)}`;
        completeSaleBtn.disabled = false;

        // Attach listeners to cart item buttons
        cartItemsList.querySelectorAll('.increase-qty').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (cart[id].qty < cart[id].maxStock) {
                    cart[id].qty++;
                    renderCart();
                } else {
                    alert('Maximum available stock reached.');
                }
            });
        });

        cartItemsList.querySelectorAll('.decrease-qty').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                cart[id].qty--;
                if (cart[id].qty <= 0) {
                    delete cart[id];
                }
                renderCart();
            });
        });
    }

    clearCartBtn.addEventListener('click', () => {
        cart = {};
        renderCart();
    });

    completeSaleBtn.addEventListener('click', async function () {
        if (Object.keys(cart).length === 0) return;

        if (!confirm('Complete transaction and deduct stock?')) return;

        completeSaleBtn.disabled = true;
        completeSaleBtn.textContent = 'Processing Sale...';

        try {
            const response = await `${baseUrl}/pos/checkout`; // adjust controller endpoint as needed
            // Formulating payload for backend transaction handling
            const itemsPayload = Object.values(cart).map(i => ({ id: i.id, quantity: i.qty, price: i.price }));

            const res = await fetch(`${baseUrl}/pos/checkout`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    items: itemsPayload
                })
            });

            const result = await res.json();

            if (result.success) {
                alert('Sale successfully recorded and inventory updated!');
                window.location.reload();
            } else {
                alert('Checkout failed: ' + (result.message || 'Unknown error'));
                completeSaleBtn.disabled = false;
                completeSaleBtn.textContent = 'Record Sale & Update Stock';
            }
        } catch (err) {
            console.error('Checkout error:', err);
            alert('An error occurred during checkout.');
            completeSaleBtn.disabled = false;
            completeSaleBtn.textContent = 'Record Sale & Update Stock';
        }
    });
});
</script>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>