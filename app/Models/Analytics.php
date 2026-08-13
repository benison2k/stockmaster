<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Analytics extends Model {

    public function getTopProducts(?string $startDate, ?string $endDate, int $limit = 5): array {
        $startDate = $startDate ?: date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT p.name, p.category, 
                   SUM(si.quantity_sold) as total_qty, 
                   SUM(si.subtotal) as total_revenue,
                   SUM((si.unit_price - p.cost_price) * si.quantity_sold) as total_profit
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN products p ON si.product_id = p.id
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
            GROUP BY p.id
            ORDER BY total_qty DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCategoryPerformance(?string $startDate, ?string $endDate): array {
        $startDate = $startDate ?: date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT p.category, 
                   SUM(si.quantity_sold) as total_qty, 
                   SUM(si.subtotal) as total_revenue, 
                   SUM((si.unit_price - p.cost_price) * si.quantity_sold) as total_profit
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN products p ON si.product_id = p.id
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
            GROUP BY p.category
            ORDER BY total_revenue DESC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getDailyTrend(?string $startDate, ?string $endDate): array {
        $startDate = $startDate ?: date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT DATE(s.created_at) as sale_date,
                   COUNT(DISTINCT s.id) as total_transactions,
                   SUM(si.quantity_sold) as total_items,
                   SUM(si.subtotal) as total_revenue,
                   SUM((si.unit_price - p.cost_price) * si.quantity_sold) as total_profit
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
            GROUP BY DATE(s.created_at)
            ORDER BY sale_date DESC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getLowStockItems(int $threshold = 10): array {
        $stmt = $this->db->prepare("
            SELECT * FROM products 
            WHERE quantity <= :threshold 
            ORDER BY quantity ASC 
            LIMIT 5
        ");
        $stmt->execute([':threshold' => $threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getSummary(?string $startDate, ?string $endDate): array {
        $startDate = $startDate ?: date('Y-m-d');
        $endDate = $endDate ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT s.id) as total_transactions,
                   SUM(si.quantity_sold) as total_items,
                   SUM(si.subtotal) as total_revenue,
                   SUM((si.unit_price - p.cost_price) * si.quantity_sold) as total_profit
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_transactions' => 0, 
            'total_items' => 0, 
            'total_revenue' => 0, 
            'total_profit' => 0
        ];
    }

    public function getSalesFiltered(?string $startDate, ?string $endDate): array {
        $startDate = $startDate ?: date('Y-m-d');
        $endDate = $endDate ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT s.id, s.created_at, 
                   SUM(si.quantity_sold) as total_items,
                   SUM(si.subtotal) as total_amount
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            WHERE DATE(s.created_at) BETWEEN :start_date AND :end_date
            GROUP BY s.id, s.created_at
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date'   => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}