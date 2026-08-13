<?php
/**
 * @var array $todaySummary
 * @var array $recentSales
 * @var array $lowStockItems
 */
$pageTitle = "Dashboard - StockMaster";
require_once ROOT_DIR . '/app/Views/layouts/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Top Welcome Banner -->
    <div class="dashboard-banner">
        <div>
            <h1 class="dashboard-title">Store Overview</h1>
            <p class="dashboard-subtitle">
                Welcome back, <strong><?= \App\Core\Security::escape($_SESSION['username'] ?? 'Admin'); ?></strong>! Here is what's happening in your store today, <?= date('F d, Y'); ?>.
            </p>
        </div>
        <span class="user-role-badge">
            <?= strtoupper(\App\Core\Security::escape($_SESSION['role'] ?? 'Staff')); ?>
        </span>
    </div>

    <!-- Quick Stats Bar (Live Data) -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 2rem;">
        <div class="stat-card">
            <span class="stat-label">Today's Revenue</span>
            <span class="stat-value" style="color: #2563eb;">₱<?= number_format($todaySummary['total_revenue'] ?? 0, 2); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Today's Gross Profit</span>
            <span class="stat-value text-success" style="color: #16a34a;">₱<?= number_format($todaySummary['total_profit'] ?? 0, 2); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Transactions Today</span>
            <span class="stat-value"><?= number_format($todaySummary['total_transactions'] ?? 0); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Restock Warnings</span>
            <span class="stat-value" style="color: <?= count($lowStockItems) > 0 ? '#dc2626' : '#16a34a'; ?>;"><?= count($lowStockItems); ?> items</span>
        </div>
    </div>

    <!-- Main Workspace Action Cards (Using original CSS classes for reliable button functionality) -->
    <div class="dashboard-grid" style="margin-bottom: 2rem;">
        <!-- Quick POS Card -->
        <div class="action-card">
            <div class="card-header">
                <div class="card-icon pos-icon">💳</div>
                <h2>Quick POS</h2>
            </div>
            <p class="card-description">
                Process store transactions rapidly, scan item barcodes, calculate totals, and checkout customer orders in real time.
            </p>
            <div class="card-footer">
                <a href="<?= BASE_URL; ?>/pos" class="btn btn-primary btn-block">
                    Open POS Terminal &rarr;
                </a>
            </div>
        </div>

        <!-- Inventory Card -->
        <div class="action-card">
            <div class="card-header">
                <div class="card-icon inventory-icon">📦</div>
                <h2>Inventory Management</h2>
            </div>
            <p class="card-description">
                Manage product catalog, monitor current stock quantities, adjust cost and selling prices, and track low-stock alerts.
            </p>
            <div class="card-footer">
                <a href="<?= BASE_URL; ?>/inventory" class="btn btn-secondary btn-block">
                    View Inventory Catalog &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Secondary Action Cards for Sales & Analytics -->
    <div class="dashboard-grid" style="margin-bottom: 2rem;">
        <div class="action-card">
            <div class="card-header">
                <div class="card-icon" style="background: #f0fdf4; color: #16a34a;">🧾</div>
                <h2>Sales History</h2>
            </div>
            <p class="card-description">
                Audit past transactions, look up receipts, verify daily register summaries, and review historical logs.
            </p>
            <div class="card-footer">
                <a href="<?= BASE_URL; ?>/sales" class="btn btn-block" style="background: #16a34a; color: #fff; text-align: center; display: block; text-decoration: none; padding: 0.75rem; border-radius: 6px; font-weight: 600;">
                    View Sales Audit &rarr;
                </a>
            </div>
        </div>

        <div class="action-card">
            <div class="card-header">
                <div class="card-icon" style="background: #faf5ff; color: #9333ea;">📈</div>
                <h2>Store Analytics</h2>
            </div>
            <p class="card-description">
                Evaluate top-performing products, check category revenue earnings, and analyze daily performance trends.
            </p>
            <div class="card-footer">
                <a href="<?= BASE_URL; ?>/analytics" class="btn btn-block" style="background: #9333ea; color: #fff; text-align: center; display: block; text-decoration: none; padding: 0.75rem; border-radius: 6px; font-weight: 600;">
                    Open Analytics &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Live Data Tables: Recent Transactions & Low Stock Alerts -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(480px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
        
        <!-- Recent Transactions Today -->
        <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a; display: flex; justify-content: space-between; align-items: center;">
                <span>Recent Transactions Today</span>
                <a href="<?= BASE_URL; ?>/sales" style="font-size: 0.85rem; color: #2563eb; text-decoration: none;">View All →</a>
            </div>
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem;">Sale ID</th>
                        <th style="padding: 0.75rem 1rem;">Time</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Items</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentSales)): ?>
                        <tr><td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">No sales recorded yet today.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentSales as $sale): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #2563eb;">#<?= $sale['id']; ?></td>
                                <td style="padding: 0.75rem 1rem; color: #64748b;"><?= date('h:i A', strtotime($sale['created_at'])); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center; color: #334155;"><?= $sale['total_items']; ?> items</td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #0f172a;">₱<?= number_format($sale['total_amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Low Stock Warnings -->
        <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a; display: flex; justify-content: space-between; align-items: center;">
                <span>Restock Warnings (≤ 10 Units)</span>
                <a href="<?= BASE_URL; ?>/inventory" style="font-size: 0.85rem; color: #2563eb; text-decoration: none;">Manage Inventory →</a>
            </div>
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 0.75rem 1rem;">Product Name</th>
                        <th style="padding: 0.75rem 1rem;">Category</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Stock Left</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lowStockItems)): ?>
                        <tr><td colspan="3" style="text-align: center; padding: 2rem; color: #16a34a;">All stock levels look healthy! No warnings.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($lowStockItems, 0, 5) as $item): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 1rem; font-weight: 500; color: #0f172a;"><?= \App\Core\Security::escape($item['name']); ?></td>
                                <td style="padding: 0.75rem 1rem; color: #64748b;"><?= \App\Core\Security::escape($item['category']); ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: #dc2626;"><?= $item['quantity']; ?> units</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>