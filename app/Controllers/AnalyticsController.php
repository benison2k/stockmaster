<?php

namespace App\Controllers;

use App\Models\Analytics;
use App\Core\Security;

class AnalyticsController {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Default to the last 30 days if not filtered
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $analyticsModel = new Analytics();
        $topProducts = $analyticsModel->getTopProducts($startDate, $endDate);
        $categoryPerformance = $analyticsModel->getCategoryPerformance($startDate, $endDate);
        $dailyTrend = $analyticsModel->getDailyTrend($startDate, $endDate);
        $lowStockItems = $analyticsModel->getLowStockItems(10);

        require_once ROOT_DIR . '/app/Views/analytics/index.php';
    }
}