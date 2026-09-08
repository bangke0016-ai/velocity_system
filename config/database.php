<?php
/**
 * VELOCITY SYSTEM AI - Database Configuration
 * Koneksi PDO ke MySQL Database
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'velocity_system');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan password MySQL Anda

// Use the deployed application directory instead of a hardcoded local folder.
$scriptDirectory = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
$basePath = $scriptDirectory === '/' || $scriptDirectory === '.' ? '/' : rtrim($scriptDirectory, '/') . '/';
define('BASE_URL', $basePath);
$siteScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$siteHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', $siteScheme . '://' . $siteHost . BASE_URL);
define('PLATFORM_FEE_PERCENT', 30);
define('JOKI_FEE_PERCENT', 100 - PLATFORM_FEE_PERCENT);

// Upload directory
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');

// WhatsApp default
define('WA_NUMBER', '6281945864645');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES    => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $shippingColumns = [
                'shipping_required TINYINT(1) NOT NULL DEFAULT 0',
                'shipping_name VARCHAR(100) DEFAULT NULL',
                'shipping_phone VARCHAR(30) DEFAULT NULL',
                'shipping_address TEXT',
                'shipping_city VARCHAR(100) DEFAULT NULL',
                'shipping_postal_code VARCHAR(10) DEFAULT NULL',
                'shipping_courier VARCHAR(50) DEFAULT NULL',
                'shipping_cost INT NOT NULL DEFAULT 0',
                "shipping_status VARCHAR(30) NOT NULL DEFAULT 'menunggu'",
                'shipping_tracking VARCHAR(80) DEFAULT NULL',
                'shipping_receipt VARCHAR(255) DEFAULT NULL',
                'shipping_latitude DECIMAL(10,7) DEFAULT NULL',
                'shipping_longitude DECIMAL(10,7) DEFAULT NULL',
                'revision_count TINYINT UNSIGNED NOT NULL DEFAULT 0',
                'revision_note TEXT',
                'revision_attachment VARCHAR(255) DEFAULT NULL',
                'revision_requested_at DATETIME DEFAULT NULL',
            ];
            foreach ($shippingColumns as $column) {
                try {
                    $this->pdo->exec("ALTER TABLE orders ADD COLUMN $column");
                } catch (PDOException $e) {
                    // Kolom sudah ada atau database belum memiliki tabel orders.
                }
            }
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS joki (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(100) NOT NULL,
                whatsapp VARCHAR(20) NOT NULL,
                keahlian VARCHAR(50) NOT NULL,
                status ENUM('aktif', 'suspend') NOT NULL DEFAULT 'aktif',
                rating DECIMAL(2,1) NOT NULL DEFAULT 0.0,
                total_selesai INT UNSIGNED NOT NULL DEFAULT 0,
                saldo_joki BIGINT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            foreach (['joki_id INT NULL', 'status_alokasi ENUM(\'belum_dialokasikan\', \'diproses_joki\', \'selesai\') NOT NULL DEFAULT \'belum_dialokasikan\'', 'approval_status ENUM(\'pending\', \'diterima\', \'ditolak\') NOT NULL DEFAULT \'pending\'', 'platform_fee BIGINT UNSIGNED NOT NULL DEFAULT 0', 'joki_earning BIGINT UNSIGNED NOT NULL DEFAULT 0', 'payout_processed_at DATETIME NULL'] as $column) {
                try {
                    $this->pdo->exec("ALTER TABLE orders ADD COLUMN $column");
                } catch (PDOException $e) {
                    // Kolom sudah ada.
                }
            }
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS histori_transaksi (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_pesanan INT NULL,
                id_joki INT NULL,
                total_bayar BIGINT UNSIGNED NOT NULL DEFAULT 0,
                bagian_admin BIGINT UNSIGNED NOT NULL DEFAULT 0,
                bagian_joki BIGINT UNSIGNED NOT NULL DEFAULT 0,
                tipe ENUM('masuk', 'tarik') NOT NULL,
                tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (id_pesanan), INDEX (id_joki)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS payout_joki (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_joki INT NOT NULL,
                nominal BIGINT UNSIGNED NOT NULL,
                status ENUM('pending', 'sukses') NOT NULL DEFAULT 'pending',
                tanggal_pengajuan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                tanggal_bayar DATETIME DEFAULT NULL,
                bukti_transfer VARCHAR(255) DEFAULT NULL,
                INDEX (id_joki), INDEX (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS refund_log (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_pesanan INT NOT NULL,
                nominal_refund BIGINT UNSIGNED NOT NULL,
                alasan ENUM('revisi_gagal', 'cancel', 'garansi') NOT NULL,
                tanggal_refund DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                status ENUM('diproses', 'selesai') NOT NULL DEFAULT 'selesai',
                INDEX (id_pesanan), INDEX (tanggal_refund)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            foreach (['withdrawal_requested TINYINT(1) NOT NULL DEFAULT 0', 'withdrawal_amount BIGINT UNSIGNED NOT NULL DEFAULT 0', 'withdrawal_method VARCHAR(20) DEFAULT NULL', 'withdrawal_account_name VARCHAR(100) DEFAULT NULL', 'withdrawal_account_number VARCHAR(100) DEFAULT NULL'] as $column) {
                try {
                    $this->pdo->exec("ALTER TABLE joki ADD COLUMN $column");
                } catch (PDOException $e) {
                    // Kolom sudah ada.
                }
            }
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS joki_payout_links (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                token CHAR(64) NOT NULL UNIQUE,
                joki_id INT NOT NULL,
                order_id INT NOT NULL,
                amount BIGINT UNSIGNED NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (joki_id), INDEX (order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                conversation_token CHAR(64) NOT NULL,
                sender ENUM('visitor', 'admin') NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (conversation_token, id),
                INDEX (sender, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            try {
                $this->pdo->exec("ALTER TABLE chat_messages MODIFY sender ENUM('visitor', 'assistant', 'admin') NOT NULL");
            } catch (PDOException $e) {
                // Tabel chat belum tersedia atau struktur sudah sesuai.
            }
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Prevent cloning
    private function __clone() {}
}
