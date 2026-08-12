<?php $pageTitle = "Dashboard - StockMaster"; ?>
<?php require_once ROOT_DIR . '/app/Views/layouts/header.php'; ?>

<div class="dashboard-wrapper">
    <!-- Top Welcome Banner -->
    <div class="dashboard-banner">
        <div>
            <h1 class="dashboard-title">Store Overview</h1>
            <p class="dashboard-subtitle">
                Welcome back, <strong><?= \App\Core\Security::escape($_SESSION['username'] ?? 'Admin'); ?></strong>! Here is what's happening in your store today.
            </p>
        </div>
        <span class="user-role-badge">
            <?= strtoupper(\App\Core\Security::escape($_SESSION['role'] ?? 'Staff')); ?>
        </span>
    </div>

    <!-- Quick Stats Bar -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">System Status</span>
            <span class="stat-value text-success">● Active</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Session User</span>
            <span class="stat-value"><?= \App\Core\Security::escape($_SESSION['username'] ?? 'User'); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Store Location</span>
            <span class="stat-value">Main Branch</span>
        </div>
    </div>

    <!-- Main Workspace Action Cards -->
    <div class="dashboard-grid">
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
</div>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>