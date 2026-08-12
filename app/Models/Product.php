<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Product extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateQuantity(int $id, int $quantity): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET quantity = :quantity, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            ':quantity' => $quantity,
            ':id'       => $id
        ]);
    }

    public function add(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO products (name, barcode, category, quantity, cost_price, selling_price, created_at, updated_at)
            VALUES (:name, :barcode, :category, :quantity, :cost_price, :selling_price, NOW(), NOW())
        ");

        return $stmt->execute([
            ':name'          => $data['name'],
            ':barcode'       => $data['barcode'] ?: null,
            ':category'      => $data['category'],
            ':quantity'      => $data['quantity'],
            ':cost_price'    => $data['cost_price'],
            ':selling_price' => $data['selling_price'],
        ]);
    }

    public function update(array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET name = :name,
                barcode = :barcode,
                category = :category,
                quantity = :quantity,
                cost_price = :cost_price,
                selling_price = :selling_price,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'            => $data['id'],
            ':name'          => $data['name'],
            ':barcode'       => $data['barcode'] ?: null,
            ':category'      => $data['category'],
            ':quantity'      => $data['quantity'],
            ':cost_price'    => $data['cost_price'],
            ':selling_price' => $data['selling_price'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}