<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\Product;

class InventoryController extends Controller
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /**
     * Display main inventory list
     */
    public function index()
    {
        $products = $this->productModel->getAll();

        // Render main view file directly
        require_once ROOT_DIR . '/app/Views/inventory/index.php';
    }

    /**
     * Quick Stock Update Endpoint (AJAX)
     */
    public function updateStock()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $csrfToken = $_POST['csrf_token'] ?? '';

            if (!Security::verifyCSRFToken($csrfToken) || $id === false || $quantity === false || $quantity < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid payload or security token']);
                return;
            }

            $updated = $this->productModel->updateQuantity($id, $quantity);

            if ($updated) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update database']);
            }
            return;
        }

        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }

    /**
     * Handle Add Product
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Security::verifyCSRFToken($csrfToken)) {
                header('Location: ' . BASE_URL . '/inventory?error=invalid_token');
                exit;
            }

            $category = $_POST['category'] === 'custom' ? trim($_POST['custom_category'] ?? '') : trim($_POST['category'] ?? 'General');

            $data = [
                'name'          => trim($_POST['name'] ?? ''),
                'barcode'       => trim($_POST['barcode'] ?? ''),
                'category'      => $category ?: 'General',
                'quantity'      => (int)($_POST['quantity'] ?? 0),
                'cost_price'    => (float)($_POST['cost_price'] ?? 0.00),
                'selling_price' => (float)($_POST['selling_price'] ?? 0.00),
            ];

            // If your Product model has an add/create method, call it here:
            if (method_exists($this->productModel, 'add')) {
                $this->productModel->add($data);
            }

            header('Location: ' . BASE_URL . '/inventory');
            exit;
        }
    }

    /**
     * Handle Update Product
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Security::verifyCSRFToken($csrfToken)) {
                header('Location: ' . BASE_URL . '/inventory?error=invalid_token');
                exit;
            }

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $category = $_POST['category'] === 'custom' ? trim($_POST['custom_category'] ?? '') : trim($_POST['category'] ?? 'General');

            $data = [
                'id'            => $id,
                'name'          => trim($_POST['name'] ?? ''),
                'barcode'       => trim($_POST['barcode'] ?? ''),
                'category'      => $category ?: 'General',
                'quantity'      => (int)($_POST['quantity'] ?? 0),
                'cost_price'    => (float)($_POST['cost_price'] ?? 0.00),
                'selling_price' => (float)($_POST['selling_price'] ?? 0.00),
            ];

            if ($id && method_exists($this->productModel, 'update')) {
                $this->productModel->update($data);
            }

            header('Location: ' . BASE_URL . '/inventory');
            exit;
        }
    }

    /**
     * Handle Delete Product
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Security::verifyCSRFToken($csrfToken)) {
                header('Location: ' . BASE_URL . '/inventory?error=invalid_token');
                exit;
            }

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            if ($id && method_exists($this->productModel, 'delete')) {
                $this->productModel->delete($id);
            }

            header('Location: ' . BASE_URL . '/inventory');
            exit;
        }
    }
}