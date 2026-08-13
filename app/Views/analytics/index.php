<?php
/**
 * @var array $topProducts
 * @var array $categoryPerformance
 * @var array $dailyTrend
 * @var array $lowStockItems
 * @var string $startDate
 * @var string $endDate
 */
require_once ROOT_DIR . '/app/Views/layouts/header.php';
?>

<style>
/* Tooltip styling for info icons */
.tooltip-container {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    margin-left: 0.35rem;
    color: #64748b;
}
.tooltip-container:hover {
    color: #2563eb;
}
.tooltip-text {
    visibility: hidden;
    width: 220px;
    background-color: #0f172a;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 8px;
    position: absolute;
    z-index: 100;
    top: 150%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.2s ease;
    font-size: 0.75rem;
    font-weight: 400;
    line-height: 1.3;
    box-shadow: 0 4px 6px rgba(0,0,0,0.15);
}
.tooltip-container:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
}
</style>

<div class="dashboard-wrapper" style="max-width: 1200px; margin: 0 auto; padding: 1rem;">
    
    <!-- Page Header & Filter Form -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 0;">Store Analytics & Insights</h1>
            <p style="color: #64748b; margin: 0.25rem 0 0 0;">Practical metrics to evaluate top performers, category earnings, and inventory health.</p>
        </div>

        <form method="GET" action="<?= BASE_URL; ?>/analytics" style="display: flex; gap: 0.5rem; align-items: center; background: #fff; padding: 0.5rem; border-radius: 8px; border: 1px solid #cbd5e1;">
            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem;">
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date" value="<?= \App\Core\Security::escape($startDate); ?>" style="padding: 0.3rem; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>
            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem;">
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date" value="<?= \App\Core\Security::escape($endDate); ?>" style="padding: 0.3rem; border: 1px solid #cbd5e1; border-radius: 4px;">
            </div>
            <button type="submit" style="padding: 0.35rem 0.75rem; font-size: 0.85rem; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Filter</button>
            <a href="<?= BASE_URL; ?>/analytics" style="padding: 0.35rem 0.5rem; font-size: 0.85rem; text-decoration: none; color: #64748b; background: #f1f5f9; border-radius: 4px;">Reset</a>
        </form>
    </div>

    <!-- Grid Section: Top Products & Category Performance -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(540px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Top Selling Products Table -->
        <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a; display: flex; align-items: center;">
                Top 5 Best-Selling Products
                <span class="tooltip-container">
                    ⓘ
                    <span class="tooltip-text">Products ranked by total quantity sold within the filtered date range. Helps you identify fast movers.</span>
                </span>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 0.75rem 1rem;">Product Name</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">Qty Sold</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Revenue</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Est. Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">No sales records found for this period.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $p): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem 1rem; font-weight: 500; color: #0f172a;"><?= \App\Core\Security::escape($p['name']); ?></td>
                                    <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #2563eb;"><?= $p['total_qty']; ?></td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: #334155;">₱<?= number_format($p['total_revenue'], 2); ?></td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #16a34a;">₱<?= number_format($p['total_profit'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sales by Category Table -->
        <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a; display: flex; align-items: center;">
                Performance by Category
                <span class="tooltip-container">
                    ⓘ
                    <span class="tooltip-text">Aggregated revenue and profit grouped by product categories to see which department drives business.</span>
                </span>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 0.75rem 1rem;">Category</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">Items Sold</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Total Revenue</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Total Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categoryPerformance)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">No category data found for this period.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categoryPerformance as $cat): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.75rem 1rem; font-weight: 500; color: #0f172a;"><?= \App\Core\Security::escape($cat['category']); ?></td>
                                    <td style="padding: 0.75rem 1rem; text-align: center; color: #334155;"><?= $cat['total_qty']; ?></td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: #334155;">₱<?= number_format($cat['total_revenue'], 2); ?></td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #16a34a;">₱<?= number_format($cat['total_profit'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Daily Trend Summary Table -->
    <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 2rem;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a; display: flex; justify-content: space-between; align-items: center;">
            <span>Daily Sales Trend Breakdown</span>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: normal;">Click any date to view its transactions</span>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem;">Date</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Transactions</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Items Sold</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Revenue</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Est. Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dailyTrend)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8;">No daily trend data found for this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dailyTrend as $day): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.75rem 1rem; font-weight: 500;">
                                    <a href="<?= BASE_URL; ?>/sales?start_date=<?= $day['sale_date']; ?>&end_date=<?= $day['sale_date']; ?>" style="color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="View all transactions for this day">
                                        📅 <?= date('M d, Y', strtotime($day['sale_date'])); ?> 
                                        <span style="font-size: 0.75rem; background: #eff6ff; padding: 0.1rem 0.4rem; border-radius: 4px; color: #1d4ed8;">View →</span>
                                    </a>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: center; color: #334155;"><?= $day['total_transactions']; ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center; color: #334155;"><?= $day['total_items']; ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #334155;">₱<?= number_format($day['total_revenue'], 2); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #16a34a;">₱<?= number_format($day['total_profit'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Inventory Alert Table -->
    <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a; display: flex; align-items: center;">
            Inventory Restock Warnings (Items ≤ 10 stock)
            <span class="tooltip-container">
                ⓘ
                <span class="tooltip-text">Highlights items running dangerously low in stock so you can reorder before running out.</span>
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem;">Product Name</th>
                        <th style="padding: 0.75rem 1rem;">Category</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Remaining Stock</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Cost Price</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Selling Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lowStockItems)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem; color: #16a34a;">Great job! No items are currently below the low stock threshold.</td></tr>
                    <?php else: ?>
                        <?php foreach ($lowStockItems as $item): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem; font-weight: 500; color: #0f172a;"><?= \App\Core\Security::escape($item['name']); ?></td>
                                <td style="padding: 0.75rem 1rem; color: #64748b;"><?= \App\Core\Security::escape($item['category']); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 700; color: #dc2626;"><?= $item['quantity']; ?> units</td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #334155;">₱<?= number_format($item['cost_price'], 2); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #334155;">₱<?= number_format($item['selling_price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>