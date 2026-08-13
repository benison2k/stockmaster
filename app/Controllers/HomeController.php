<?php

namespace App\Controllers;

use App\Models\Analytics;

class HomeController {

    public function landing() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        require_once ROOT_DIR . '/app/Views/home/landing.php';
    }

    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $analyticsModel = new Analytics();

        $today = date('Y-m-d');
        
        // Fetch real-time data for today
        $todaySummary = $analyticsModel->getSummary($today, $today);
        $recentSales = array_slice($analyticsModel->getSalesFiltered($today, $today), 0, 5);
        $lowStockItems = $analyticsModel->getLowStockItems(10);

        require_once ROOT_DIR . '/app/Views/home/dashboard.php';
    }
}