<?php $pageTitle = "Login - StockMaster"; ?>
<?php require_once ROOT_DIR . '/app/Views/layouts/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access your store workspace.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= \App\Core\Security::escape($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL; ?>/login" method="POST" class="auth-form">
            <!-- Anti-CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Security::generateCSRFToken(); ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. admin" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
    </div>
</div>

<?php require_once ROOT_DIR . '/app/Views/layouts/footer.php'; ?>