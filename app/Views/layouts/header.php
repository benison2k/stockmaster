<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? \App\Core\Security::escape($pageTitle) : 'StockMaster'; ?></title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/public/css/main.css">
    <!-- Google Material Icons CDN -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
            transform: translateX(0);
            max-width: 600px;
            overflow: hidden;
            white-space: nowrap;
        }

        .nav-links.nav-hidden {
            opacity: 0;
            transform: translateX(20px);
            max-width: 0;
            margin: 0;
            padding: 0;
            pointer-events: none;
        }

        .nav-toggle-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .nav-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .nav-toggle-btn .material-icons {
            font-size: 20px;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="brand-wrapper">
                <a href="<?= BASE_URL; ?>/dashboard" class="brand">StockMaster</a>
            </div>
            
            <div class="navbar-right">
                <div class="nav-links" id="navLinksContainer">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL; ?>/dashboard">Dashboard</a>
                        <a href="<?= BASE_URL; ?>/pos">POS</a>
                        <a href="<?= BASE_URL; ?>/inventory">Inventory</a>
                        <a href="<?= BASE_URL; ?>/sales">Sales</a>
                        <a href="<?= BASE_URL; ?>/analytics">Analytics</a>
                        <a href="<?= BASE_URL; ?>/logout" class="btn-logout">Logout</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL; ?>/login" class="btn-login">Login</a>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button" id="toggleNavBtn" class="nav-toggle-btn" title="Toggle Navigation Menu">
                        <span class="material-icons" id="toggleIcon">close</span>
                    </button>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="container">

    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.getElementById('navLinksContainer');
        const toggleBtn = document.getElementById('toggleNavBtn');
        const toggleIcon = document.getElementById('toggleIcon');

        if (toggleBtn && navLinks) {
            const isHidden = localStorage.getItem('stockmaster_nav_hidden') === 'true';

            if (isHidden) {
                navLinks.classList.add('nav-hidden');
                toggleIcon.textContent = 'menu'; // Burger menu when hidden
            } else {
                toggleIcon.textContent = 'close'; // X icon when visible
            }

            toggleBtn.addEventListener('click', function () {
                navLinks.classList.toggle('nav-hidden');
                const hidden = navLinks.classList.contains('nav-hidden');
                
                localStorage.setItem('stockmaster_nav_hidden', hidden);

                if (hidden) {
                    toggleIcon.textContent = 'menu'; // Switch to burger menu when hidden
                } else {
                    toggleIcon.textContent = 'close'; // Switch to X icon when visible
                }
            });
        }
    });
    </script>
    <?php endif; ?>