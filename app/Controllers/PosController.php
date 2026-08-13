<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;
use App\Models\Database;
use App\Core\Security;
use Exception;

class PosController extends Controller {
    
    public function index() {
        $productModel = new Product();
        $products = $productModel->all();
        $this->view('pos', ['products' => $products]);
    }

    public function checkout() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        if (!Security::verifyCSRFToken($input['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }

        $items = $input['items'] ?? [];
        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            return;
        }

        $db = null;
        try {
            // Instantiate Database object directly to get the PDO connection
            $databaseModel = new Database();
            $db = $databaseModel->getConnection();

            if (!$db) {
                throw new Exception("Database connection failed.");
            }

            $db->beginTransaction();

            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += ($item['price'] * $item['quantity']);
            }

            $stmt = $db->prepare("INSERT INTO sales (total_amount) VALUES (?)");
            $stmt->execute([$totalAmount]);
            $saleId = $db->lastInsertId();

            foreach ($items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                
                $itemStmt = $db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity_sold, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $itemStmt->execute([$saleId, $item['id'], $item['quantity'], $item['price'], $subtotal]);

                $stockStmt = $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                $stockStmt->execute([$item['quantity'], $item['id'], $item['quantity']]);
                
                if ($stockStmt->rowCount() === 0) {
                    throw new Exception("Insufficient stock or product not found for ID: " . $item['id']);
                }
            }

            $db->commit();
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            if ($db && method_exists($db, 'inTransaction') && $db->inTransaction()) {
                $db->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}