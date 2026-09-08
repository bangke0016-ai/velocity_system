<?php
/**
 * VELOCITY SYSTEM AI - Order Model
 * Simpan & kelola data pesanan
 */

class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Buat pesanan baru
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO orders (order_code, customer_name, whatsapp, service_id, quantity, deadline_at, total_price, dp_amount, notes, file_attachment, shipping_required, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_postal_code, shipping_courier, shipping_cost, shipping_latitude, shipping_longitude)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['order_code'],
            $data['customer_name'],
            $data['whatsapp'],
            $data['service_id'],
            $data['quantity'],
            $data['deadline_at'] ?? null,
            $data['total_price'],
            $data['dp_amount'],
            $data['notes'] ?? null,
            $data['file_attachment'] ?? null,
            $data['shipping_required'] ?? 0,
            $data['shipping_name'] ?? null,
            $data['shipping_phone'] ?? null,
            $data['shipping_address'] ?? null,
            $data['shipping_city'] ?? null,
            $data['shipping_postal_code'] ?? null,
            $data['shipping_courier'] ?? null,
            $data['shipping_cost'] ?? 0,
            $data['shipping_latitude'] ?? null,
            $data['shipping_longitude'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Cari pesanan berdasarkan kode
     */
    public function findByCode($code) {
        $stmt = $this->db->prepare(
            "SELECT o.*, s.title as service_title, s.unit as service_unit, c.name as category_name
             FROM orders o 
             JOIN services s ON o.service_id = s.id 
             JOIN categories c ON s.category_id = c.id
             WHERE o.order_code = ?"
        );
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    /**
     * Ambil semua pesanan (admin)
     */
    public function getAll($limit = 50) {
        $stmt = $this->db->prepare(
            "SELECT o.*, s.title as service_title, c.name as category_name
             FROM orders o 
             JOIN services s ON o.service_id = s.id 
             JOIN categories c ON s.category_id = c.id
             ORDER BY o.created_at DESC 
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Update status pesanan
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }

    /**
     * Upload bukti pembayaran
     */
    public function uploadPaymentProof($id, $filename) {
        $stmt = $this->db->prepare(
            "UPDATE orders SET payment_proof = ?, status = 'dp_paid' WHERE id = ?"
        );
        return $stmt->execute([$filename, $id]);
    }

    /**
     * Hitung total pesanan
     */
    public function countOrders() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM orders");
        return $stmt->fetch()['total'];
    }

    /**
     * Hitung pesanan selesai
     */
    public function countCompleted() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'selesai'");
        return $stmt->fetch()['total'];
    }
}
