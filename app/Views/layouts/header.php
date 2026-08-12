<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? \App\Core\Security::escape($pageTitle) : 'StockMaster'; ?></title>
    <link rel="stylesheet" href="/stockmaster/public/css/main.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="/stockmaster/" class="brand">StockMaster</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/stockmaster/dashboard">Dashboard</a>
                    <a href="/stockmaster/inventory">Inventory</a>
                    <a href="/stockmaster/logout" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="/stockmaster/login" class="btn-login">Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="container">