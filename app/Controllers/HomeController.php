<?php
namespace App\Controllers;

class HomeController {
    public function landing(): void {
        require_once ROOT_DIR . '/app/Views/home/landing.php';
    }

    public function dashboard(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        require_once ROOT_DIR . '/app/Views/home/dashboard.php';
    }
}