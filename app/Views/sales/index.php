<?php
/**
 * @var array $sales
 * @var array $summary
 * @var string $startDate
 * @var string $endDate
 */
require_once ROOT_DIR . '/app/Views/layouts/header.php';
?>

<div class="dashboard-wrapper" style="max-width: 1200px; margin: 0 auto; padding: 1rem;">
    
    <!-- Page Header & Filters -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 0;">Sales History & Records</h1>
            <p style="color: var(--text-muted, #64748b); margin: 0.25rem 0 0 0;">Audit store transactions, track revenue, and monitor gross profit.</p>
        </div>

        <!-- Date Range Filter Form -->
        <form method="GET" action="<?= BASE_URL; ?>/sales" style="display: flex; gap: 0.5rem; align-items: center; background: var(--card-bg, #fff); padding: 0.5rem; border-radius: 8px; border: 1px solid var(--border-color, #cbd5e1);">
            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem;">
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date" value="<?= \App\Core\Security::escape($startDate); ?>" style="padding: 0.3rem; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>
            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem;">
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date" value="<?= \App\Core\Security::escape($endDate); ?>" style="padding: 0.3rem; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Filter</button>
            <a href="<?= BASE_URL; ?>/sales" class="btn btn-secondary" style="padding: 0.35rem 0.5rem; font-size: 0.85rem; text-decoration: none; color: #64748b; background: #f1f5f9; border-radius: 4px;" title="Reset to Today">Today</a>
        </form>
    </div>

    <!-- KPI Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: #fff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Total Revenue</span>
            <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;">₱<?= number_format($summary['total_revenue'], 2); ?></div>
        </div>
        <div style="background: #fff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Gross Profit</span>
            <div style="font-size: 1.5rem; font-weight: 700; color: #16a34a; margin-top: 0.35rem;">₱<?= number_format($summary['total_profit'], 2); ?></div>
        </div>
        <div style="background: #fff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Transactions</span>
            <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;"><?= number_format($summary['total_transactions']); ?></div>
        </div>
        <div style="background: #fff; padding: 1.25rem; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Avg. Order Value</span>
            <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;">₱<?= number_format($summary['avg_order_value'], 2); ?></div>
        </div>
    </div>

    <!-- Transactions Table Card -->
    <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a;">
            Transaction Log (<?= count($sales); ?> records found)
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem;">Sale ID</th>
                        <th style="padding: 0.75rem 1rem;">Timestamp</th>
                        <th style="padding: 0.75rem 1rem;">Payment Method</th>
                        <th style="padding: 0.75rem 1rem;">Items Sold</th>
                        <th style="padding: 0.75rem 1rem;">Total Amount</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #94a3b8;">No sales records found for the selected date range.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales as $sale): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #2563eb;">#<?= $sale['id']; ?></td>
                                <td style="padding: 0.75rem 1rem; color: #334155;"><?= $sale['created_at']; ?></td>
                                <td style="padding: 0.75rem 1rem; color: #334155;"><?= \App\Core\Security::escape($sale['payment_method']); ?></td>
                                <td style="padding: 0.75rem 1rem; color: #334155;"><?= $sale['total_items']; ?> items</td>
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #0f172a;">₱<?= number_format($sale['total_amount'], 2); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <button type="button" onclick="viewSaleDetails(<?= $sale['id']; ?>)" style="background: #e0f2fe; color: #0369a1; border: none; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; cursor: pointer; font-weight: 500;">View Details</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div id="saleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: #fff; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin: 1rem;">
        <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modalTitle" style="margin: 0; font-size: 1.1rem; color: #0f172a;">Sale Items Breakdown</h3>
            <button onclick="closeModal()" style="background: transparent; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <div style="padding: 1.25rem; max-height: 400px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b;">
                        <th style="padding: 0.5rem;">Product Name</th>
                        <th style="padding: 0.5rem; text-align: center;">Qty</th>
                        <th style="padding: 0.5rem; text-align: right;">Unit Price</th>
                        <th style="padding: 0.5rem; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modalItemsList">
                    <!-- Populated dynamically via JS -->
                </tbody>
            </table>
        </div>
        <div style="padding: 0.75rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right;">
            <button onclick="closeModal()" class="btn btn-secondary" style="padding: 0.4rem 1rem; background: #cbd5e1; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Close</button>
        </div>
    </div>
</div>

<script>
function viewSaleDetails(saleId) {
    const modal = document.getElementById('saleModal');
    const modalTitle = document.getElementById('modalTitle');
    const tbody = document.getElementById('modalItemsList');
    
    modalTitle.textContent = `Sale #${saleId} - Items Breakdown`;
    tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 1.5rem; color: #64748b;">Loading items...</td></tr>';
    modal.style.display = 'flex';

    fetch(`<?= BASE_URL; ?>/sales/details?id=${saleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = '';
                data.items.forEach(item => {
                    tbody.innerHTML += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.5rem; color: #334155; font-weight: 500;">${item.product_name}</td>
                            <td style="padding: 0.5rem; text-align: center; color: #334155;">${item.quantity_sold}</td>
                            <td style="padding: 0.5rem; text-align: right; color: #334155;">₱${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td style="padding: 0.5rem; text-align: right; font-weight: 600; color: #0f172a;">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #dc2626; padding: 1.5rem;">${data.message}</td></tr>`;
            }
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #dc2626; padding: 1.5rem;">Failed to load transaction items.</td></tr>';
        });
}

function closeModal() {
    document.getElementById('saleModal').style.display = 'none';
}
</script>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>