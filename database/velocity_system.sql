-- ============================================
-- VELOCITY SYSTEM AI - Database Schema & Seed
-- Import file ini ke MySQL / phpMyAdmin
-- ============================================

-- Buat Database
CREATE DATABASE IF NOT EXISTS `velocity_system` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `velocity_system`;

-- ============================================
-- TABEL: services (Daftar Layanan & Harga)
-- ============================================
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `joki`;
DROP TABLE IF EXISTS `histori_transaksi`;
DROP TABLE IF EXISTS `testimonials`;
DROP TABLE IF EXISTS `terms`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `chat_messages`;

CREATE TABLE `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(80) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `chat_messages` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_token` CHAR(64) NOT NULL,
  `sender` ENUM('visitor', 'assistant', 'admin') NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`conversation_token`, `id`),
  INDEX (`sender`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(100) NOT NULL DEFAULT 'fas fa-folder',
  `description` TEXT,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `price` INT NOT NULL,
  `price_max` INT DEFAULT NULL COMMENT 'Untuk range harga (misal 3000-5000)',
  `unit` VARCHAR(50) NOT NULL DEFAULT 'halaman',
  `icon` VARCHAR(100) NOT NULL DEFAULT 'fas fa-file',
  `description` TEXT,
  `is_package` TINYINT(1) NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `joki` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `whatsapp` VARCHAR(20) NOT NULL,
  `keahlian` VARCHAR(50) NOT NULL,
  `status` ENUM('aktif', 'suspend') NOT NULL DEFAULT 'aktif',
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  `total_selesai` INT UNSIGNED NOT NULL DEFAULT 0,
  `saldo_joki` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `withdrawal_requested` TINYINT(1) NOT NULL DEFAULT 0,
  `withdrawal_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `withdrawal_method` VARCHAR(20) DEFAULT NULL,
  `withdrawal_account_name` VARCHAR(100) DEFAULT NULL,
  `withdrawal_account_number` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `joki_payout_links` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `token` CHAR(64) NOT NULL UNIQUE,
  `joki_id` INT NOT NULL,
  `order_id` INT NOT NULL,
  `amount` BIGINT UNSIGNED NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`joki_id`), INDEX (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `histori_transaksi` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_pesanan` INT DEFAULT NULL,
  `id_joki` INT DEFAULT NULL,
  `total_bayar` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `bagian_admin` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `bagian_joki` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `tipe` ENUM('masuk', 'tarik') NOT NULL,
  `tanggal` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`id_pesanan`), INDEX (`id_joki`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payout_joki` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_joki` INT NOT NULL,
  `nominal` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('pending', 'sukses') NOT NULL DEFAULT 'pending',
  `tanggal_pengajuan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_bayar` DATETIME DEFAULT NULL,
  `bukti_transfer` VARCHAR(255) DEFAULT NULL,
  INDEX (`id_joki`), INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `refund_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_pesanan` INT NOT NULL,
  `nominal_refund` BIGINT UNSIGNED NOT NULL,
  `alasan` ENUM('revisi_gagal', 'cancel', 'garansi') NOT NULL,
  `tanggal_refund` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('diproses', 'selesai') NOT NULL DEFAULT 'selesai',
  INDEX (`id_pesanan`), INDEX (`tanggal_refund`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: orders (Pesanan)
-- ============================================
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(30) NOT NULL UNIQUE,
  `customer_name` VARCHAR(100) NOT NULL,
  `whatsapp` VARCHAR(20) NOT NULL,
  `service_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `deadline_at` DATETIME DEFAULT NULL,
  `total_price` INT NOT NULL,
  `dp_amount` INT NOT NULL,
  `notes` TEXT,
  `file_attachment` VARCHAR(255) DEFAULT NULL,
  `shipping_required` TINYINT(1) NOT NULL DEFAULT 0,
  `shipping_name` VARCHAR(100) DEFAULT NULL,
  `shipping_phone` VARCHAR(30) DEFAULT NULL,
  `shipping_address` TEXT,
  `shipping_city` VARCHAR(100) DEFAULT NULL,
  `shipping_postal_code` VARCHAR(10) DEFAULT NULL,
  `shipping_courier` VARCHAR(50) DEFAULT NULL,
  `shipping_cost` INT NOT NULL DEFAULT 0,
  `shipping_status` VARCHAR(30) NOT NULL DEFAULT 'menunggu',
  `shipping_tracking` VARCHAR(80) DEFAULT NULL,
  `shipping_receipt` VARCHAR(255) DEFAULT NULL,
  `shipping_latitude` DECIMAL(10,7) DEFAULT NULL,
  `shipping_longitude` DECIMAL(10,7) DEFAULT NULL,
  `joki_id` INT DEFAULT NULL,
  `status_alokasi` ENUM('belum_dialokasikan', 'diproses_joki', 'selesai') NOT NULL DEFAULT 'belum_dialokasikan',
  `approval_status` ENUM('pending', 'diterima', 'ditolak') NOT NULL DEFAULT 'pending',
  `platform_fee` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `joki_earning` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `payout_processed_at` DATETIME DEFAULT NULL,
  `revision_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `revision_note` TEXT,
  `revision_attachment` VARCHAR(255) DEFAULT NULL,
  `revision_requested_at` DATETIME DEFAULT NULL,
  `status` ENUM('pending_dp', 'dp_paid', 'pengerjaan', 'revisi', 'selesai', 'cancelled') NOT NULL DEFAULT 'pending_dp',
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`joki_id`) REFERENCES `joki`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: testimonials (Testimoni)
-- ============================================
CREATE TABLE `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `initials` VARCHAR(5) NOT NULL,
  `role` VARCHAR(100) NOT NULL,
  `service_used` VARCHAR(100) NOT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `content` TEXT NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: terms (Ketentuan Layanan)
-- ============================================
CREATE TABLE `terms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'fas fa-check',
  `type` ENUM('normal', 'warning') NOT NULL DEFAULT 'normal',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: settings (Pengaturan Website)
-- ============================================
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- SEED DATA: Categories
-- ============================================
INSERT INTO `categories` (`slug`, `name`, `icon`, `description`, `sort_order`) VALUES
('tulis_tangan', 'Jasa Tulis Tugas / Catatan', 'fas fa-pen-fancy', 'Penulisan tugas tangan untuk buku, binder, dan kertas folio dengan tulisan rapi dan terstruktur.', 1),
('ketik', 'Jasa Ketik & Edit Document', 'fas fa-file-word', 'Pengetikan makalah, laporan, PPT, dan dokumen Word dengan format profesional.', 2),
('desain', 'Jasa Desain', 'fas fa-palette', 'Desain poster, ID Card, undangan, banner, brosur, sertifikat dan material grafis lainnya.', 3),
('edit', 'Jasa Edit', 'fas fa-pen-to-square', 'Editing, formatting, dan penyusunan dokumen profesional.', 4),
('paketan', 'Harga Paketan', 'fas fa-star', 'Paket lengkap hemat untuk tugas besar.', 5),
('web_dev', 'Web Design & Development', 'fas fa-code', 'Pembuatan website modern, landing page, dan aplikasi web untuk kebutuhan bisnis Anda.', 6);

-- ============================================
-- SEED DATA: Services (Verbatim dari Gambar)
-- ============================================

-- Jasa Tulis Tangan
INSERT INTO `services` (`category_id`, `title`, `price`, `unit`, `icon`, `sort_order`) VALUES
(1, 'Buku Kecil / Binder A5', 5000, 'halaman', 'fas fa-book', 1),
(1, 'Buku Besar Bigboss / Binder B5', 6000, 'halaman', 'fas fa-book-open', 2),
(1, 'Kertas HVS A4 / F4', 5000, 'halaman', 'fas fa-file-alt', 3),
(1, 'Kertas Folio / A5', 7000, 'halaman', 'fas fa-scroll', 4);

-- Jasa Ketik
INSERT INTO `services` (`category_id`, `title`, `price`, `unit`, `icon`, `sort_order`) VALUES
(2, 'Makalah', 3000, 'halaman', 'fas fa-file-lines', 1),
(2, 'PPT', 2000, 'halaman', 'fas fa-file-powerpoint', 2),
(2, 'Word', 2000, 'halaman', 'fas fa-file-word', 3),
(2, 'Laporan', 3000, 'halaman', 'fas fa-clipboard-list', 4);

-- Jasa Desain
INSERT INTO `services` (`category_id`, `title`, `price`, `unit`, `icon`, `sort_order`) VALUES
(3, 'Poster', 10000, 'item', 'fas fa-image', 1),
(3, 'ID Card', 10000, 'item', 'fas fa-id-card', 2),
(3, 'Undangan', 10000, 'item', 'fas fa-envelope-open-text', 3),
(3, 'Banner', 10000, 'item', 'fas fa-flag', 4),
(3, 'Brosur', 10000, 'item', 'fas fa-newspaper', 5),
(3, 'Sertifikat', 10000, 'item', 'fas fa-certificate', 6);

-- Jasa Edit
INSERT INTO `services` (`category_id`, `title`, `price`, `price_max`, `unit`, `icon`, `sort_order`) VALUES
(4, 'Desain PPT', 3000, 5000, 'slide', 'fas fa-wand-magic-sparkles', 1),
(4, 'Ketik Ulang Document', 2000, NULL, 'halaman', 'fas fa-rotate-left', 2),
(4, 'Rapihin Format', 1000, NULL, 'halaman', 'fas fa-align-left', 3),
(4, 'Penyusunan Dokumen', 2000, NULL, 'halaman', 'fas fa-layer-group', 4),
(4, 'Penomoran Halaman', 2000, NULL, '10 halaman', 'fas fa-list-ol', 5);

-- Harga Paketan
INSERT INTO `services` (`category_id`, `title`, `price`, `unit`, `icon`, `is_package`, `is_featured`, `sort_order`) VALUES
(5, 'Makalah Tugas Sekolah', 30000, 'paket', 'fas fa-graduation-cap', 1, 1, 1),
(5, 'Laporan PKL', 50000, 'paket', 'fas fa-building', 1, 1, 2),
(5, 'PPT Desain Morph', 60000, 'paket', 'fas fa-wand-magic-sparkles', 1, 1, 3);

-- ============================================
-- SEED DATA: Testimonials
-- ============================================
INSERT INTO `testimonials` (`name`, `initials`, `role`, `service_used`, `rating`, `content`) VALUES
('Andi S.', 'AS', 'Mahasiswa', 'Jasa Ketik Makalah', 5, 'Makalah selesai dalam 2 hari, formatnya rapi banget dan sesuai pedoman kampus. Admin sangat responsif di WhatsApp. Pasti order lagi!'),
('Nurul R.', 'NR', 'Pelajar SMA', 'Jasa Tulis Tangan', 5, 'Tulisan tangannya rapi sekali, guruku sampai memuji. Harga terjangkau dan prosesnya cepat. Sangat recommended buat anak sekolah!'),
('Dimas F.', 'DF', 'Mahasiswa', 'Paket PPT Desain Morph', 5, 'PPT presentasi morph-nya keren banget! Teman-teman sekelas pada kagum. Desainnya profesional dan modern. Worth every penny!');

-- ============================================
-- SEED DATA: Ketentuan Layanan
-- ============================================
INSERT INTO `terms` (`title`, `description`, `icon`, `type`, `sort_order`) VALUES
('DP 50% Sebelum Pengerjaan', 'Menghindari risiko hit & run dari pembeli.', 'fas fa-check', 'normal', 1),
('Wajib Mengirim Bukti Payment', 'Pembeli wajib mengunggah/mengirim bukti transfer.', 'fas fa-check', 'normal', 2),
('Tidak Boleh Cancel Saat Pengerjaan', 'Pembatalan tidak diizinkan saat proses berlangsung.', 'fas fa-check', 'normal', 3),
('Free Revisi Maksimal 3x', 'Garansi perbaikan maksimal 3 kali tanpa biaya tambahan.', 'fas fa-check', 'normal', 4),
('Jaminan Uang Kembali 100%', 'Garansi refund penuh jika penjoki tidak menyelesaikan tugas.', 'fas fa-check', 'normal', 5),
('Data & Privasi Dijamin Aman 100%', 'Kerahasiaan identitas dan data Anda sepenuhnya terjamin.', 'fas fa-check', 'normal', 6),
('Deadline Sesuai Kesepakatan', 'Pengerjaan selesai tepat waktu sesuai yang disepakati.', 'fas fa-check', 'normal', 7),
('TIDAK MENERIMA JOKI UJIAN', 'Demi integritas akademik, kami secara tegas tidak melayani jasa joki untuk ujian (UTS, UAS, Quiz, maupun ujian live). Kebijakan ini berlaku tanpa pengecualian.', 'fas fa-triangle-exclamation', 'warning', 8);

-- ============================================
-- SEED DATA: Settings
-- ============================================
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_name', 'VELOCITY SYSTEM AI', 'Nama website'),
('site_tagline', 'Jasa Joki Tugas Profesional & Terpercaya', 'Tagline website'),
('site_description', 'Platform jasa tugas akademik profesional dan terpercaya untuk pelajar dan mahasiswa di seluruh Indonesia.', 'Deskripsi website'),
('whatsapp', '6281945864645', 'Nomor WhatsApp admin (format internasional)'),
('whatsapp_display', '0819 - 4586 - 4645', 'Nomor WhatsApp tampilan'),
('email', 'info@velocitysystem.ai', 'Email kontak'),
('location', 'Maiwa, Kab. Enrekang, Sulawesi Selatan', 'Alamat lokasi'),
('copyright_year', '2024', 'Tahun copyright'),
('dp_percentage', '50', 'Persentase DP (%)'),
('shipping_fee', '15000', 'Biaya tetap pengiriman paket fisik'),
('max_revisions', '3', 'Maksimal revisi gratis'),
('instagram', 'https://www.instagram.com/velocity_system.id/', 'Link Instagram'),
('instagram_name', 'velocity_system.id', 'Nama akun Instagram untuk invoice'),
('facebook', '#', 'Link Facebook'),
('tiktok', 'https://www.tiktok.com/@exam_shop', 'Link TikTok'),
('tiktok_name', 'exam_shop', 'Nama akun TikTok untuk invoice'),
('youtube', '#', 'Link YouTube'),
('payment_methods', 'QRIS, Bank Transfer, E-Wallet, DANA, GoPay, OVO', 'Metode pembayaran dipisahkan dengan koma');
