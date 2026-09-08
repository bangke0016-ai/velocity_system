<?php
/**
 * VELOCITY SYSTEM AI - Service Model
 * Query data layanan & harga dari database
 */

class Service {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Ambil semua kategori aktif
     */
    public function getCategories() {
        $stmt = $this->db->query(
            "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Ambil semua layanan aktif beserta kategorinya
     */
    public function getAllServices() {
        $stmt = $this->db->query(
            "SELECT s.*, c.slug as category_slug, c.name as category_name 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.is_active = 1 AND c.is_active = 1 
             ORDER BY c.sort_order ASC, s.sort_order ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Ambil layanan berdasarkan kategori slug
     */
    public function getByCategory($slug) {
        $stmt = $this->db->prepare(
            "SELECT s.*, c.name as category_name, c.icon as category_icon 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE c.slug = ? AND s.is_active = 1 AND c.is_active = 1 
             ORDER BY s.sort_order ASC"
        );
        $stmt->execute([$slug]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil layanan berdasarkan ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare(
            "SELECT s.*, c.slug as category_slug, c.name as category_name 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Ambil layanan yang dikelompokkan per kategori
     */
    public function getGroupedByCategory() {
        $categories = $this->getCategories();
        $grouped = [];
        foreach ($categories as $cat) {
            $services = $this->getByCategory($cat['slug']);
            if (!empty($services)) {
                $grouped[] = [
                    'category' => $cat,
                    'services' => $services
                ];
            }
        }
        return $grouped;
    }

    /**
     * Ambil 4 kategori utama untuk service cards (bukan edit/paketan)
     */
    public function getMainCategories() {
        $stmt = $this->db->query(
            "SELECT * FROM categories 
             WHERE is_active = 1 AND slug IN ('tulis_tangan', 'ketik', 'desain', 'web_dev') 
             ORDER BY sort_order ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Ambil kategori pricing (semua kecuali web_dev)
     */
    public function getPricingCategories() {
        $stmt = $this->db->query(
            "SELECT * FROM categories 
             WHERE is_active = 1 AND slug != 'web_dev' 
             ORDER BY sort_order ASC"
        );
        return $stmt->fetchAll();
    }
}
