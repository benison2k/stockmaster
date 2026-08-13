<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Sale extends Model {

    public function getSalesFiltered(?string $startDate, ?string $endDate): array {
        // Default to present day if no dates are given
        $startDate = $startDate ?: date('Y-m-d');
        $endDate = $endDate ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT s.*, 
                   COUNT(si.id) as total_items
            FROM sales s
            LEFT JOIN sale_items si ON s.id = si.sale_id
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
            GROUP BY s.id
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getSummary(?string $startDate, ?string $endDate): array {
        $startDate = $startDate ?: date('Y-m-d');
        $endDate = $endDate ?: date('Y-m-d');

        // 1. Get correct revenue, transaction count, and average order value from sales table directly
        $stmtSales = $this->db->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COUNT(id) as total_transactions,
                COALESCE(SUM(total_amount) / NULLIF(COUNT(id), 0), 0) as avg_order_value
            FROM sales
            WHERE DATE(created_at) BETWEEN :start_date AND :end_date
        ");
        $stmtSales->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        $summary = $stmtSales->fetch(PDO::FETCH_ASSOC);

        // 2. Get total gross profit safely via item-level calculations
        $stmtProfit = $this->db->prepare("
            SELECT 
                COALESCE(SUM((si.unit_price - p.cost_price) * si.quantity_sold), 0) as total_profit
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
        ");
        $stmtProfit->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        $profitData = $stmtProfit->fetch(PDO::FETCH_ASSOC);

        return [
            'total_revenue'      => $summary['total_revenue'],
            'total_transactions' => $summary['total_transactions'],
            'avg_order_value'    => $summary['avg_order_value'],
            'total_profit'       => $profitData['total_profit']
        ];
    }

    public function getSaleItems(int $saleId): array {
        $stmt = $this->db->prepare("
            SELECT si.*, p.name as product_name, p.category
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = :sale_id
        ");
        $stmt->execute([':sale_id' => $saleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}