<?php
declare(strict_types=1);

session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/app/Models/Joki.php';

$requestedAdminDevice = (string) ($_GET['device'] ?? '');
if (in_array($requestedAdminDevice, ['mobile', 'laptop'], true)) {
    $_SESSION['admin_device'] = $requestedAdminDevice;
}
$adminDevice = (string) ($_SESSION['admin_device'] ?? '');
if (!in_array($adminDevice, ['mobile', 'laptop'], true)) {
    require __DIR__ . '/views/admin-device-choice.php';
    exit;
}
$adminViewFile = $adminDevice === 'mobile'
    ? __DIR__ . '/views/admin-mobile.php'
    : __DIR__ . '/views/admin-laptop.php';

$db = Database::getInstance()->getConnection();
$iconOptions = [
    'fas fa-file-lines' => 'Dokumen', 'fas fa-file-word' => 'Word', 'fas fa-file-powerpoint' => 'Presentasi',
    'fas fa-pen-fancy' => 'Tulis', 'fas fa-pen-to-square' => 'Edit', 'fas fa-keyboard' => 'Ketik',
    'fas fa-palette' => 'Desain', 'fas fa-image' => 'Gambar', 'fas fa-id-card' => 'ID Card',
    'fas fa-code' => 'Website', 'fas fa-laptop-code' => 'Coding', 'fas fa-cart-shopping' => 'Toko Online',
    'fas fa-calendar-check' => 'Booking', 'fas fa-credit-card' => 'Pembayaran', 'fas fa-chart-line' => 'Dashboard',
    'fas fa-database' => 'Database', 'fas fa-gears' => 'Teknis', 'fas fa-graduation-cap' => 'Akademik',
    'fas fa-book' => 'Buku', 'fas fa-layer-group' => 'Paket', 'fas fa-star' => 'Unggulan',
    'fas fa-folder' => 'Folder', 'fas fa-plus-circle' => 'Lainnya',
];
$db->exec("CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$adminExists = (int) $db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
if ($adminExists === 0) {
    $seed = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $seed->execute(['admin', password_hash('velocity123', PASSWORD_DEFAULT)]);
}

function adminCsrf(): string {
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['admin_csrf'];
}

function requireAdminCsrf(): void {
    if (!hash_equals((string) ($_SESSION['admin_csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa. Silakan kembali dan coba lagi.');
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    redirect('admin.php');
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $stmt = $db->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([trim((string) ($_POST['username'] ?? ''))]);
    $admin = $stmt->fetch();
    if ($admin && password_verify((string) ($_POST['password'] ?? ''), $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect('admin.php');
    }
    $loginError = 'Username atau password tidak sesuai.';
}

$isLoggedIn = isset($_SESSION['admin_id']);
$flash = null;
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdminCsrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'confirm_withdrawal') {
        try {
            $proof = null;
            if (!empty($_FILES['bukti_transfer']['name'])) {
                $file = $_FILES['bukti_transfer'];
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
                if ($file['error'] !== UPLOAD_ERR_OK || (int) $file['size'] > 5 * 1024 * 1024 || !isset($allowed[$mime])) { throw new InvalidArgumentException('Bukti transfer harus JPG, PNG, WEBP, atau PDF maksimal 5 MB.'); }
                if (!is_dir(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }
                $proof = 'payout-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $proof)) { throw new RuntimeException('Bukti transfer gagal disimpan.'); }
            }
            (new Joki($db))->confirmWithdrawal((int) ($_POST['joki_id'] ?? 0), $proof);
            setFlash('success', 'Penarikan saldo berhasil dikonfirmasi dan dicatat.');
        } catch (Throwable $exception) { setFlash('error', $exception->getMessage()); }
        redirect('admin.php?tab=keuangan');
    }
    if ($action === 'create_refund') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $amount = max(0, (int) ($_POST['nominal_refund'] ?? 0));
        $reason = (string) ($_POST['alasan'] ?? '');
        if ($orderId < 1 || $amount < 1 || !in_array($reason, ['revisi_gagal', 'cancel', 'garansi'], true)) { setFlash('error', 'Data refund tidak valid.'); }
        else {
            $stmt = $db->prepare("SELECT o.total_price, COALESCE(SUM(r.nominal_refund), 0) AS refunded FROM orders o LEFT JOIN refund_log r ON r.id_pesanan = o.id AND r.status = 'selesai' WHERE o.id = ? GROUP BY o.id"); $stmt->execute([$orderId]);
            $refundOrder = $stmt->fetch();
            if (!$refundOrder) { setFlash('error', 'Order refund tidak ditemukan.'); }
            elseif ($amount > ((int) $refundOrder['total_price'] - (int) $refundOrder['refunded'])) { setFlash('error', 'Nominal refund melebihi sisa nilai order.'); }
            else { $stmt = $db->prepare('INSERT INTO refund_log (id_pesanan, nominal_refund, alasan, status) VALUES (?, ?, ?, "selesai")'); $stmt->execute([$orderId, $amount, $reason]); setFlash('success', 'Refund berhasil dicatat.'); }
        }
        redirect('admin.php?tab=keuangan');
    }
    if ($action === 'approve_order' || $action === 'reject_order') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $approvalStatus = $action === 'approve_order' ? 'diterima' : 'ditolak';
        $stmt = $db->prepare("UPDATE orders SET approval_status = ?, status = CASE WHEN ? = 'ditolak' THEN 'cancelled' ELSE status END WHERE id = ? AND approval_status = 'pending'");
        $stmt->execute([$approvalStatus, $approvalStatus, $orderId]);
        $success = $stmt->rowCount() === 1;
        setFlash($success ? 'success' : 'error', $success ? ($approvalStatus === 'diterima' ? 'Pesanan berhasil di-ACC.' : 'Pesanan berhasil ditolak.') : 'Pesanan sudah diproses atau tidak ditemukan.');
        redirect('admin.php?tab=orders');
    }
    if (in_array($action, ['create_joki', 'update_joki', 'toggle_joki'], true)) {
        $jokiModel = new Joki($db);
        $jokiOptions = ['IT', 'Akuntansi', 'Umum'];
        $jokiId = (int) ($_POST['joki_id'] ?? 0);
        try {
            if ($action === 'toggle_joki' && $jokiId > 0) {
                $jokiModel->toggleStatus($jokiId);
                setFlash('success', 'Status joki berhasil diubah.');
            } else {
                $nama = trim((string) ($_POST['nama'] ?? ''));
                $whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
                $keahlian = (string) ($_POST['keahlian'] ?? '');
                $rating = max(0, min(5, (float) ($_POST['rating'] ?? 0)));
                if ($nama === '' || mb_strlen($nama) > 100 || !preg_match('/^[0-9+() .-]{8,20}$/', $whatsapp) || $keahlian === '' || mb_strlen($keahlian) > 50) {
                    throw new InvalidArgumentException('Nama, WhatsApp, dan keahlian wajib diisi dengan benar.');
                }
                if ($action === 'create_joki') { $jokiModel->create($nama, $whatsapp, $keahlian, $rating); setFlash('success', 'Joki berhasil ditambahkan.'); }
                else { $jokiModel->update($jokiId, $nama, $whatsapp, $keahlian, $rating); setFlash('success', 'Data joki berhasil diperbarui.'); }
            }
        } catch (Throwable $exception) { setFlash('error', $exception->getMessage()); }
        redirect('admin.php?tab=joki');
    }
    if ($action === 'update_status') {
        $allowed = ['pending_dp', 'dp_paid', 'pengerjaan', 'revisi', 'selesai', 'cancelled'];
        $status = (string) ($_POST['status'] ?? '');
        $orderId = (int) ($_POST['order_id'] ?? 0);
        if (in_array($status, $allowed, true) && $orderId > 0) {
            try {
                if ($status === 'selesai') {
                    $payoutToken = (new Joki($db))->completeOrder($orderId);
                    if ($payoutToken) {
                        $stmt = $db->prepare('SELECT o.order_code, o.customer_name, o.total_price, o.joki_earning, o.deadline_at, s.title AS service_title, j.nama AS joki_name, j.whatsapp AS joki_whatsapp FROM orders o JOIN services s ON s.id = o.service_id JOIN joki j ON j.id = o.joki_id WHERE o.id = ? LIMIT 1');
                        $stmt->execute([$orderId]);
                        $completedOrder = $stmt->fetch();
                        if ($completedOrder) {
                            $payoutFormUrl = SITE_URL . 'form_penarikan.php?token=' . rawurlencode($payoutToken);
                            $payoutMessage = "Halo {$completedOrder['joki_name']}, order {$completedOrder['order_code']} sudah selesai. Silakan isi data rekening bank atau e-wallet untuk proses pencairan bagian Anda (" . formatRupiah($completedOrder['joki_earning']) . ") melalui link berikut:\n\n{$payoutFormUrl}";
                            $_SESSION['payout_whatsapp'] = 'https://wa.me/' . formatWhatsAppNumber($completedOrder['joki_whatsapp']) . '?text=' . rawurlencode($payoutMessage);
                        }
                    }
                    setFlash('success', 'Order selesai. Komisi 30% dipotong dan 70% masuk ke saldo joki.');
                } else {
                    $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
                    $stmt->execute([$status, $orderId]);
                    setFlash('success', 'Status order berhasil diperbarui.');
                }
            } catch (Throwable $exception) {
                setFlash('error', $exception->getMessage());
            }
        }
        redirect('admin.php?tab=orders');
    }
    if ($action === 'update_shipping') {
        $shippingStatuses = ['menunggu', 'diproses', 'dikirim', 'selesai'];
        $shippingStatus = (string) ($_POST['shipping_status'] ?? 'menunggu');
        $tracking = trim((string) ($_POST['shipping_tracking'] ?? ''));
        $orderId = (int) ($_POST['order_id'] ?? 0);
        if (in_array($shippingStatus, $shippingStatuses, true) && $orderId > 0 && mb_strlen($tracking) <= 80) {
            $receipt = null;
            if (!empty($_FILES['shipping_receipt']['name'])) {
                $file = $_FILES['shipping_receipt'];
                $allowed = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                if ($file['error'] !== UPLOAD_ERR_OK || (int) $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name']) || !isset($allowed[$mime])) {
                    setFlash('error', 'File resi harus berupa PDF, JPG, PNG, atau WEBP maksimal 5 MB.');
                    redirect('admin.php?tab=shipping');
                }
                if (!is_dir(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }
                $receipt = 'resi-' . $orderId . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $receipt)) {
                    setFlash('error', 'File resi gagal disimpan.');
                    redirect('admin.php?tab=shipping');
                }
            }
            $query = $receipt ? 'UPDATE orders SET shipping_status = ?, shipping_tracking = ?, shipping_receipt = ? WHERE id = ? AND shipping_required = 1' : 'UPDATE orders SET shipping_status = ?, shipping_tracking = ? WHERE id = ? AND shipping_required = 1';
            $stmt = $db->prepare($query);
            $stmt->execute($receipt ? [$shippingStatus, $tracking ?: null, $receipt, $orderId] : [$shippingStatus, $tracking ?: null, $orderId]);
            setFlash('success', 'Status pengiriman dan file resi berhasil diperbarui.');
        }
        redirect('admin.php?tab=shipping');
    }
    if ($action === 'admin_chat_reply') {
        $conversationToken = (string) ($_POST['conversation_token'] ?? '');
        $message = trim((string) ($_POST['message'] ?? ''));
        if (preg_match('/^[a-f0-9]{64}$/', $conversationToken) && $message !== '' && mb_strlen($message) <= 2000) {
            $stmt = $db->prepare("INSERT INTO chat_messages (conversation_token, sender, message) VALUES (?, 'admin', ?)");
            $stmt->execute([$conversationToken, $message]);
            setFlash('success', 'Balasan berhasil dikirim.');
        } else {
            setFlash('error', 'Balasan tidak valid.');
        }
        redirect('admin.php?tab=chat&conversation=' . rawurlencode($conversationToken));
    }
    if ($action === 'update_service') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        if ($title === '' || !$categoryId) {
            $currentStmt = $db->prepare('SELECT title, category_id FROM services WHERE id = ? LIMIT 1');
            $currentStmt->execute([$serviceId]);
            $current = $currentStmt->fetch();
            $title = $title ?: (string) ($current['title'] ?? '');
            $categoryId = $categoryId ?: (int) ($current['category_id'] ?? 0);
        }
        $price = max(0, (int) ($_POST['price'] ?? 0));
        $priceMaxInput = trim((string) ($_POST['price_max'] ?? ''));
        $priceMax = $priceMaxInput === '' ? null : max($price, (int) $priceMaxInput);
        $unit = trim((string) ($_POST['unit'] ?? 'halaman'));
        $icon = array_key_exists((string) ($_POST['icon'] ?? ''), $iconOptions) ? (string) $_POST['icon'] : 'fas fa-file';
        if ($title === '' || mb_strlen($title) > 150 || !$categoryId || $unit === '' || mb_strlen($unit) > 50) {
            setFlash('error', 'Nama layanan, kategori, dan satuan wajib diisi dengan benar.');
        } else {
            $categoryStmt = $db->prepare('SELECT id FROM categories WHERE id = ? LIMIT 1');
            $categoryStmt->execute([$categoryId]);
            if (!$categoryStmt->fetch()) {
                setFlash('error', 'Kategori layanan tidak valid.');
            } else {
                $stmt = $db->prepare('UPDATE services SET title = ?, category_id = ?, price = ?, price_max = ?, unit = ?, icon = ?, is_active = ? WHERE id = ?');
                $stmt->execute([$title, $categoryId, $price, $priceMax, $unit, $icon, isset($_POST['is_active']) ? 1 : 0, $serviceId]);
                setFlash('success', 'Layanan berhasil diperbarui.');
            }
        }
        redirect('admin.php?tab=services');
    }
    if ($action === 'create_service') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
        $price = max(0, (int) ($_POST['price'] ?? 0));
        $priceMaxInput = trim((string) ($_POST['price_max'] ?? ''));
        $priceMax = $priceMaxInput === '' ? null : max($price, (int) $priceMaxInput);
        $unit = trim((string) ($_POST['unit'] ?? 'halaman'));
        $icon = array_key_exists((string) ($_POST['icon'] ?? ''), $iconOptions) ? (string) $_POST['icon'] : 'fas fa-file';
        if ($title === '' || mb_strlen($title) > 150 || !$categoryId || $unit === '' || mb_strlen($unit) > 50) {
            setFlash('error', 'Nama layanan, kategori, dan satuan wajib diisi dengan benar.');
        } else {
            $categoryStmt = $db->prepare('SELECT id FROM categories WHERE id = ? LIMIT 1');
            $categoryStmt->execute([$categoryId]);
            if (!$categoryStmt->fetch()) {
                setFlash('error', 'Kategori layanan tidak valid.');
            } else {
                $sortStmt = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM services WHERE category_id = ?');
                $sortStmt->execute([$categoryId]);
                $stmt = $db->prepare('INSERT INTO services (category_id, title, price, price_max, unit, icon, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$categoryId, $title, $price, $priceMax, $unit, $icon, isset($_POST['is_active']) ? 1 : 0, (int) $sortStmt->fetchColumn()]);
                setFlash('success', 'Layanan baru berhasil ditambahkan.');
            }
        }
        redirect('admin.php?tab=services');
    }
    if ($action === 'delete_service') {
        $serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
        if ($serviceId) {
            $stmt = $db->prepare('UPDATE services SET is_active = 0 WHERE id = ?');
            $stmt->execute([$serviceId]);
            setFlash('success', 'Layanan berhasil dihapus dari website. Riwayat order tetap aman.');
        } else {
            setFlash('error', 'Layanan tidak valid.');
        }
        redirect('admin.php?tab=services');
    }
    if ($action === 'update_category') {
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $icon = trim((string) ($_POST['icon'] ?? 'fas fa-folder'));
        $icon = array_key_exists($icon, $iconOptions) ? $icon : 'fas fa-folder';
        $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));
        if ($categoryId < 1 || $name === '' || mb_strlen($name) > 100 || $icon === '' || mb_strlen($icon) > 100) {
            setFlash('error', 'Nama kategori dan ikon wajib diisi dengan benar.');
        } else {
            $stmt = $db->prepare('UPDATE categories SET name = ?, icon = ?, sort_order = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $icon, $sortOrder, isset($_POST['is_active']) ? 1 : 0, $categoryId]);
            setFlash('success', 'Menu kategori berhasil diperbarui.');
        }
        redirect('admin.php?tab=services&categories=1');
    }
    if ($action === 'create_category') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $icon = trim((string) ($_POST['icon'] ?? 'fas fa-folder'));
        $icon = array_key_exists($icon, $iconOptions) ? $icon : 'fas fa-folder';
        $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug ?: $name);
        $slug = trim((string) $slug, '_');
        if ($name === '' || mb_strlen($name) > 100 || $slug === '' || mb_strlen($slug) > 50 || $icon === '') {
            setFlash('error', 'Nama, slug, dan ikon kategori wajib diisi dengan benar.');
        } else {
            try {
                $stmt = $db->prepare('INSERT INTO categories (slug, name, icon, sort_order, is_active) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$slug, $name, $icon, $sortOrder, isset($_POST['is_active']) ? 1 : 0]);
                setFlash('success', 'Menu kategori baru berhasil ditambahkan.');
            } catch (PDOException $exception) {
                setFlash('error', 'Slug kategori sudah digunakan. Gunakan slug lain.');
            }
        }
        redirect('admin.php?tab=services&categories=1');
    }
    if ($action === 'update_settings') {
        $settingsToUpdate = [
            'site_name' => trim((string) ($_POST['site_name'] ?? '')),
            'site_tagline' => trim((string) ($_POST['site_tagline'] ?? '')),
            'site_description' => trim((string) ($_POST['site_description'] ?? '')),
            'whatsapp' => trim((string) ($_POST['whatsapp'] ?? '')),
            'whatsapp_display' => trim((string) ($_POST['whatsapp_display'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'location' => trim((string) ($_POST['location'] ?? '')),
            'copyright_year' => trim((string) ($_POST['copyright_year'] ?? date('Y'))),
            'dp_percentage' => (string) max(0, min(100, (int) ($_POST['dp_percentage'] ?? 50))),
            'max_revisions' => (string) max(0, min(20, (int) ($_POST['max_revisions'] ?? 3))),
            'instagram' => trim((string) ($_POST['instagram'] ?? '#')),
            'instagram_name' => trim((string) ($_POST['instagram_name'] ?? '')),
            'facebook' => trim((string) ($_POST['facebook'] ?? '#')),
            'tiktok' => trim((string) ($_POST['tiktok'] ?? '#')),
            'tiktok_name' => trim((string) ($_POST['tiktok_name'] ?? '')),
            'youtube' => trim((string) ($_POST['youtube'] ?? '#')),
            'payment_methods' => trim((string) ($_POST['payment_methods'] ?? 'QRIS, Bank Transfer, E-Wallet, DANA, GoPay, OVO')),
        ];
        $stmt = $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($settingsToUpdate as $key => $value) { $stmt->execute([$key, $value]); }
        setFlash('success', 'Seluruh pengaturan website berhasil disimpan.');
        redirect('admin.php?tab=settings');
    }
    if ($action === 'upload_testimonials') {
        $directory = __DIR__ . '/assets/testimonials';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $uploaded = 0;
        $allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        foreach ($_FILES['testimonial_images']['tmp_name'] ?? [] as $index => $temporaryFile) {
            $error = $_FILES['testimonial_images']['error'][$index] ?? UPLOAD_ERR_NO_FILE;
            $size = (int) ($_FILES['testimonial_images']['size'][$index] ?? 0);
            if ($error !== UPLOAD_ERR_OK || $size > 8 * 1024 * 1024 || !is_uploaded_file($temporaryFile)) {
                continue;
            }
            $imageInfo = @getimagesize($temporaryFile);
            $mimeType = $imageInfo['mime'] ?? '';
            if (!$imageInfo || !isset($allowedMimeTypes[$mimeType])) {
                continue;
            }
            $fileName = 'testimoni-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimeTypes[$mimeType];
            if (move_uploaded_file($temporaryFile, $directory . '/' . $fileName)) {
                $uploaded++;
            }
        }
        setFlash($uploaded > 0 ? 'success' : 'error', $uploaded > 0 ? "$uploaded foto testimoni berhasil diunggah." : 'Tidak ada foto yang berhasil diunggah. Gunakan JPG, PNG, WEBP, atau GIF maksimal 8 MB per foto.');
        redirect('admin.php?tab=testimonials');
    }
    if ($action === 'delete_testimonial') {
        $fileName = basename((string) ($_POST['file_name'] ?? ''));
        $filePath = __DIR__ . '/assets/testimonials/' . $fileName;
        if (is_file($filePath) && preg_match('/^testimoni-[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp|gif)$/', $fileName)) {
            unlink($filePath);
            setFlash('success', 'Foto testimoni berhasil dihapus.');
        }
        redirect('admin.php?tab=testimonials');
    }
}

$flash = $isLoggedIn ? getFlash() : null;
$allocationWhatsapp = $_SESSION['allocation_whatsapp'] ?? null;
unset($_SESSION['allocation_whatsapp']);
$tab = $_GET['tab'] ?? 'overview';
$statusLabels = [
    'pending_dp' => 'Menunggu DP', 'dp_paid' => 'DP Dibayar', 'pengerjaan' => 'Pengerjaan',
    'revisi' => 'Revisi', 'selesai' => 'Selesai', 'cancelled' => 'Dibatalkan',
];

if ($isLoggedIn) {
    $stats = [
        'total' => (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
        'pending' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending_dp', 'dp_paid')")->fetchColumn(),
        'active' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status IN ('pengerjaan', 'revisi')")->fetchColumn(),
        'revenue' => (int) $db->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = 'selesai'")->fetchColumn(),
    ];
    $search = trim((string) ($_GET['q'] ?? ''));
    $filter = (string) ($_GET['status'] ?? '');
    $orderDate = (string) ($_GET['date'] ?? '');
    $sql = "SELECT o.*, CASE WHEN o.status = 'pending_dp' THEN o.approval_status ELSE 'diterima' END AS approval_status, s.title AS service_title, j.nama AS joki_name, j.status AS joki_status FROM orders o JOIN services s ON s.id = o.service_id LEFT JOIN joki j ON j.id = o.joki_id WHERE 1=1";
    $params = [];
    if ($search !== '') {
        $sql .= ' AND (o.order_code LIKE ? OR o.customer_name LIKE ? OR o.whatsapp LIKE ?)';
        $params = ["%$search%", "%$search%", "%$search%"];
    }
    if (isset($statusLabels[$filter])) { $sql .= ' AND o.status = ?'; $params[] = $filter; }
    $selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $orderDate);
    if ($selectedDate && $selectedDate->format('Y-m-d') === $orderDate) {
        $nextDate = $selectedDate->modify('+1 day');
        $sql .= ' AND o.created_at >= ? AND o.created_at < ?';
        $params[] = $selectedDate->format('Y-m-d H:i:s');
        $params[] = $nextDate->format('Y-m-d H:i:s');
    } else {
        $orderDate = '';
    }
    $sql .= ' ORDER BY o.created_at DESC LIMIT 100';
    $stmt = $db->prepare($sql); $stmt->execute($params); $orders = $stmt->fetchAll();
    foreach ($orders as &$order) {
        $order['whatsapp'] = formatWhatsAppNumber($order['whatsapp']);
    }
    unset($order);
    $services = $db->query('SELECT s.*, c.name AS category_name FROM services s JOIN categories c ON c.id = s.category_id ORDER BY c.sort_order, s.sort_order')->fetchAll();
    $jokiOptions = array_column($db->query('SELECT DISTINCT keahlian FROM joki WHERE keahlian <> "" ORDER BY keahlian')->fetchAll(), 'keahlian');
    $jokiSearch = trim((string) ($_GET['q'] ?? ''));
    $jokiFilter = (string) ($_GET['keahlian'] ?? '');
    $jokiRows = (new Joki($db))->all($jokiSearch, in_array($jokiFilter, $jokiOptions, true) ? $jokiFilter : '');
    $allocationOrders = $db->query("SELECT o.id, o.order_code, o.customer_name, o.deadline_at, o.total_price, o.status_alokasi, s.title AS service_title, c.name AS category_name FROM orders o JOIN services s ON s.id = o.service_id JOIN categories c ON c.id = s.category_id WHERE o.status_alokasi = 'belum_dialokasikan' AND o.status NOT IN ('cancelled', 'selesai') ORDER BY o.created_at ASC")->fetchAll();
    $assignedOrders = $db->query("SELECT o.id, o.order_code, o.customer_name, o.deadline_at, o.total_price, o.status_alokasi, o.joki_id, s.title AS service_title, c.name AS category_name, j.nama AS joki_name FROM orders o JOIN services s ON s.id = o.service_id JOIN categories c ON c.id = s.category_id JOIN joki j ON j.id = o.joki_id WHERE o.status_alokasi = 'diproses_joki' AND o.status NOT IN ('cancelled', 'selesai') ORDER BY o.updated_at DESC")->fetchAll();
    // Gunakan orders sebagai sumber omset agar order lama tetap ikut terhitung.
    $completedFinance = $db->query("SELECT COALESCE(SUM(total_price), 0) AS omzet, COALESCE(SUM(CASE WHEN platform_fee > 0 THEN platform_fee ELSE FLOOR(total_price * " . PLATFORM_FEE_PERCENT . " / 100) END), 0) AS admin FROM orders WHERE status = 'selesai'")->fetch();
    $totalRefund = (int) $db->query("SELECT COALESCE(SUM(nominal_refund), 0) FROM refund_log WHERE status = 'selesai'")->fetchColumn();
    $financeStats = [
        'omzet' => (int) ($completedFinance['omzet'] ?? 0),
        'admin' => max(0, (int) ($completedFinance['admin'] ?? 0) - $totalRefund),
        'joki' => (int) $db->query("SELECT COALESCE(SUM(bagian_joki), 0) FROM histori_transaksi WHERE tipe = 'tarik'")->fetchColumn(),
    ];
    $financeTransactions = $db->query('SELECT h.*, o.order_code, j.nama AS joki_name FROM histori_transaksi h LEFT JOIN orders o ON o.id = h.id_pesanan LEFT JOIN joki j ON j.id = h.id_joki ORDER BY h.tanggal DESC, h.id DESC LIMIT 100')->fetchAll();
    $withdrawalRequests = $db->query('SELECT id, nama, whatsapp, saldo_joki, withdrawal_amount, withdrawal_method, withdrawal_account_name, withdrawal_account_number FROM joki WHERE withdrawal_requested = 1 AND withdrawal_amount > 0 ORDER BY updated_at ASC')->fetchAll();
    $refundLogs = $db->query('SELECT r.*, o.order_code FROM refund_log r LEFT JOIN orders o ON o.id = r.id_pesanan ORDER BY r.tanggal_refund DESC, r.id DESC LIMIT 100')->fetchAll();
    $shippingOrders = $db->query('SELECT o.*, s.title AS service_title FROM orders o JOIN services s ON s.id = o.service_id WHERE o.shipping_required = 1 ORDER BY o.created_at DESC')->fetchAll();
    foreach ($shippingOrders as &$shippingOrder) {
        if ($shippingOrder['shipping_latitude'] !== null && $shippingOrder['shipping_longitude'] !== null) {
            $shippingOrder['shipping_courier'] .= "\nLokasi: " . $shippingOrder['shipping_latitude'] . ', ' . $shippingOrder['shipping_longitude'];
        }
    }
    unset($shippingOrder);
    $categories = $db->query('SELECT id, slug, name, icon, sort_order, is_active FROM categories ORDER BY sort_order, name')->fetchAll();
    $settings = [];
    foreach ($db->query('SELECT setting_key, setting_value FROM settings') as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    $chatConversations = $db->query("SELECT conversation_token, MAX(id) AS last_id, MAX(created_at) AS last_message_at, SUBSTRING_INDEX(GROUP_CONCAT(message ORDER BY id DESC SEPARATOR '\\n'), '\\n', 1) AS last_message, SUM(sender = 'visitor') AS visitor_messages FROM chat_messages GROUP BY conversation_token ORDER BY last_id DESC LIMIT 100")->fetchAll();
    $selectedConversation = (string) ($_GET['conversation'] ?? '');
    $chatMessages = [];
    if (preg_match('/^[a-f0-9]{64}$/', $selectedConversation)) {
        $stmt = $db->prepare('SELECT id, sender, message, created_at FROM chat_messages WHERE conversation_token = ? ORDER BY id ASC LIMIT 200');
        $stmt->execute([$selectedConversation]);
        $chatMessages = $stmt->fetchAll();
    } else {
        $selectedConversation = '';
    }
    $testimonialDirectory = __DIR__ . '/assets/testimonials';
    $testimonialFiles = is_dir($testimonialDirectory)
        ? glob($testimonialDirectory . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE)
        : [];
}

if ($isLoggedIn && $tab === 'services' && isset($_GET['edit'])) {
    require __DIR__ . '/views/admin-services-edit.php';
    exit;
}

if ($isLoggedIn && $tab === 'services' && isset($_GET['categories'])) {
    require __DIR__ . '/views/admin-categories-edit.php';
    exit;
}

if ($isLoggedIn && $tab === 'testimonials') {
    require __DIR__ . '/views/admin-testimonials.php';
    exit;
}

if ($isLoggedIn && $tab === 'shipping') {
    require __DIR__ . '/views/admin-shipping.php';
    exit;
}
if ($isLoggedIn && $tab === 'penarikan') {
    redirect('admin.php?tab=keuangan');
}

$adminView = $adminViewFile;
ob_start();
require $adminView;
$adminHtml = ob_get_clean();
$payoutWhatsapp = $_SESSION['payout_whatsapp'] ?? null;
unset($_SESSION['payout_whatsapp']);
if ($payoutWhatsapp) {
    $payoutNotice = '<div class="alert"><i class="fa-solid fa-wallet"></i> Form rekening penjoki siap dikirim. <a class="btn small" target="_blank" rel="noopener" href="' . htmlspecialchars($payoutWhatsapp, ENT_QUOTES, 'UTF-8') . '">Kirim form ke WhatsApp <i class="fa-brands fa-whatsapp"></i></a></div>';
    $adminHtml = preg_replace('~(<section class="content">)~', '$1' . $payoutNotice, $adminHtml, 1);
}
if ($tab === 'joki') {
    ob_start();
    require __DIR__ . '/views/admin-joki.php';
    $jokiHtml = ob_get_clean();
    $adminHtml = preg_replace('~<section class="content">.*?</section>~s', '<section class="content">' . $jokiHtml . '</section>', $adminHtml, 1);
    $adminHtml = preg_replace('~<header class="top"><h1>.*?</h1>~s', '<header class="top"><h1>Manajemen joki</h1>', $adminHtml, 1);
}
if ($tab === 'alokasi') {
    ob_start();
    require __DIR__ . '/views/admin-alokasi.php';
    $allocationHtml = ob_get_clean();
    $adminHtml = preg_replace('~<section class="content">.*?</section>~s', '<section class="content">' . $allocationHtml . '</section>', $adminHtml, 1);
    $adminHtml = preg_replace('~<header class="top"><h1>.*?</h1>~s', '<header class="top"><h1>Alokasi tugas</h1>', $adminHtml, 1);
}
if ($tab === 'keuangan') {
    ob_start();
    require __DIR__ . '/views/admin-keuangan.php';
    $financeHtml = ob_get_clean();
    $adminHtml = preg_replace('~<section class="content">.*?</section>~s', '<section class="content">' . $financeHtml . '</section>', $adminHtml, 1);
    $adminHtml = preg_replace('~<header class="top"><h1>.*?</h1>~s', '<header class="top"><h1>Laporan keuangan</h1>', $adminHtml, 1);
}
if ($tab === 'chat') {
    ob_start();
    require __DIR__ . '/views/admin-chat.php';
    $chatHtml = ob_get_clean();
    $adminHtml = preg_replace('~<section class="content">.*?</section>~s', '<section class="content">' . $chatHtml . '</section>', $adminHtml, 1);
    $adminHtml = preg_replace('~<header class="top"><h1>.*?</h1>~s', '<header class="top"><h1>Live Chat</h1>', $adminHtml, 1);
}
$categoryNav = '<a class="' . ($tab === 'chat' ? 'active' : '') . '" href="admin.php?tab=chat"><i class="fa-solid fa-comments"></i><span>Live Chat</span></a><a class="' . ($tab === 'joki' ? 'active' : '') . '" href="admin.php?tab=joki"><i class="fa-solid fa-users"></i><span>Manajemen Joki</span></a><a class="' . ($tab === 'alokasi' ? 'active' : '') . '" href="admin.php?tab=alokasi"><i class="fa-solid fa-share-nodes"></i><span>Alokasi Tugas</span></a><a class="' . ($tab === 'keuangan' ? 'active' : '') . '" href="admin.php?tab=keuangan"><i class="fa-solid fa-chart-line"></i><span>Laporan Keuangan</span></a><a class="' . ($tab === 'testimonials' ? 'active' : '') . '" href="admin.php?tab=testimonials"><i class="fa-solid fa-images"></i><span>Testimoni</span></a><a class="' . ($tab === 'shipping' ? 'active' : '') . '" href="admin.php?tab=shipping"><i class="fa-solid fa-truck-fast"></i><span>Pengiriman Paket</span></a><a class="' . ($tab === 'services' && isset($_GET['categories']) ? 'active' : '') . '" href="admin.php?tab=services&amp;categories=1"><i class="fa-solid fa-layer-group"></i><span>Kategori Layanan</span></a>';
echo str_replace('</nav>', $categoryNav . '</nav>', $adminHtml);