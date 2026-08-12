<?php $pageTitle = "Welcome to StockMaster"; ?>
<?php require_once ROOT_DIR . '/app/Views/layouts/header.php'; ?>

<section class="hero-section">
    <div class="hero-content">
        <span class="badge-pill">Sari-Sari Store Management</span>
        <h1 class="hero-title">Effortless Inventory & POS Operations</h1>
        <p class="hero-subtitle">
            A fast, reliable point-of-sale and stock management system built specifically for daily retail operations.
        </p>
        <div class="cta-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL; ?>/login" class="btn btn-primary btn-lg">Log In to System &rarr;</a>
            <?php else: ?>
                <a href="<?= BASE_URL; ?>/dashboard" class="btn btn-primary btn-lg">Go to Dashboard &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features-grid">
    <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Rapid Checkout</h3>
        <p>Process customer orders quickly with real-time total calculations and simple receipt processing.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">📦</div>
        <h3>Inventory Tracking</h3>
        <p>Monitor stock quantities, track cost/selling margins, and receive low-stock alerts automatically.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3>Secure & Reliable</h3>
        <p>Protected with CSRF token verification, session protection, and role-based access control.</p>
    </div>
</section>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>