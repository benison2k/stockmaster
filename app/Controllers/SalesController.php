<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Sale;
use App\Core\Security;

class SalesController extends Controller {

    public function index() {
        // Get filter inputs from query string, default to empty (which triggers present-day default in model)
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $saleModel = new Sale();
        $sales = $saleModel->getSalesFiltered($startDate, $endDate);
        $summary = $saleModel->getSummary($startDate, $endDate);

        $this->view('sales/index', [
            'pageTitle' => 'Sales History & Records - StockMaster',
            'sales' => $sales,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function details() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $saleId = $_GET['id'] ?? 0;
        if (!$saleId) {
            echo json_encode(['success' => false, 'message' => 'Invalid Sale ID']);
            return;
        }

        $saleModel = new Sale();
        $items = $saleModel->getSaleItems($saleId);

        echo json_encode(['success' => true, 'items' => $items]);
    }
}