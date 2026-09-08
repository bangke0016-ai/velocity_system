<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/app/Models/Service.php';
require_once __DIR__ . '/app/Models/Order.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$name = trim((string) ($_POST['nama'] ?? ''));
$whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
$serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
$serviceTitle = trim((string) ($_POST['layanan'] ?? ''));
$quantity = filter_var($_POST['jumlah'] ?? null, FILTER_VALIDATE_INT);
$deadlineInput = trim((string) ($_POST['deadline_at'] ?? ''));
$notes = trim((string) ($_POST['detail'] ?? ''));
$shippingRequired = ($_POST['shipping_required'] ?? '') === '1';
$shippingName = trim((string) ($_POST['shipping_name'] ?? ''));
$shippingPhone = trim((string) ($_POST['shipping_phone'] ?? ''));
$shippingAddress = trim((string) ($_POST['shipping_address'] ?? ''));
$shippingCity = trim((string) ($_POST['shipping_city'] ?? ''));
$shippingPostalCode = trim((string) ($_POST['shipping_postal_code'] ?? ''));
$shippingCourier = trim((string) ($_POST['shipping_courier'] ?? ''));
$totalPrice = 0;
$dpAmount = 0;

if ($name === '' || mb_strlen($name) > 100 || $whatsapp === '' || mb_strlen($whatsapp) > 20 || !$serviceId || $quantity === false || $quantity < 1 || $quantity > 1000 || $deadlineInput === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Data pemesanan belum lengkap.']);
    exit;
}
if ($shippingRequired && ($shippingName === '' || $shippingPhone === '' || $shippingAddress === '' || $shippingCity === '' || $shippingPostalCode === '' || $shippingCourier === '')) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Lengkapi data alamat pengiriman fisik terlebih dahulu.']);
    exit;
}

$deadlineDate = DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i', $deadlineInput);
$dateErrors = DateTimeImmutable::getLastErrors();
if (!$deadlineDate || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) || $deadlineDate <= new DateTimeImmutable()) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Tanggal dan jam deadline tidak valid.']);
    exit;
}
$deadlineAt = $deadlineDate->format('Y-m-d H:i:s');

if (preg_match('/(ujian|uts|uas|quiz_live|quiz live)/i', $notes)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Kami tidak menerima joki ujian.']);
    exit;
}

$fileAttachment = null;
try {
    $db = Database::getInstance()->getConnection();
    $serviceStmt = $db->prepare(
        'SELECT s.id, s.title, s.price, s.price_max FROM services s JOIN categories c ON c.id = s.category_id WHERE s.id = ? AND s.is_active = 1 AND c.is_active = 1 LIMIT 1'
    );
    $serviceStmt->execute([$serviceId]);
    $service = $serviceStmt->fetch();

    if (!$service) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Layanan tidak tersedia saat ini.']);
        exit;
    }

    // Harga dihitung ulang dari database agar tidak bisa diubah dari browser.
    $totalPrice = (int) $service['price'] * $quantity;
    $dpPercentage = max(0, min(100, (int) getSetting('dp_percentage', '50')));
    $dpAmount = (int) ceil($totalPrice * $dpPercentage / 100);

    $orderCode = generateOrderCode();
    $fileUrl = null;
    if (!empty($_FILES['file_attachment']['name'])) {
        if ($_FILES['file_attachment']['error'] !== UPLOAD_ERR_OK || (int) $_FILES['file_attachment']['size'] > 10 * 1024 * 1024 || !is_uploaded_file($_FILES['file_attachment']['tmp_name'])) {
            throw new RuntimeException('Upload file gagal.');
        }
        $extension = strtolower(pathinfo($_FILES['file_attachment']['name'], PATHINFO_EXTENSION));
        $allowedMimeTypes = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/zip' => 'zip',
        ];
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['file_attachment']['tmp_name']);
        if (!isset($allowedMimeTypes[$mimeType]) || ($extension === 'jpeg' ? 'jpg' : $extension) !== $allowedMimeTypes[$mimeType]) {
            throw new RuntimeException('Format file tidak didukung.');
        }
        $extension = $allowedMimeTypes[$mimeType];
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        $fileAttachment = $orderCode . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        if (!move_uploaded_file($_FILES['file_attachment']['tmp_name'], UPLOAD_DIR . $fileAttachment)) {
            throw new RuntimeException('File tidak dapat disimpan.');
        }
        $fileUrl = SITE_URL . 'public/uploads/' . rawurlencode($fileAttachment);
    }

    (new Order())->create([
        'order_code' => $orderCode,
        'customer_name' => $name,
        'whatsapp' => $whatsapp,
        'service_id' => $service['id'],
        'quantity' => $quantity,
        'deadline_at' => $deadlineAt,
        'total_price' => $totalPrice,
        'dp_amount' => $dpAmount,
        'notes' => $notes,
        'file_attachment' => $fileAttachment,
        'shipping_required' => $shippingRequired ? 1 : 0,
        'shipping_name' => $shippingRequired ? $shippingName : null,
        'shipping_phone' => $shippingRequired ? $shippingPhone : null,
        'shipping_address' => $shippingRequired ? $shippingAddress : null,
        'shipping_city' => $shippingRequired ? $shippingCity : null,
        'shipping_postal_code' => $shippingRequired ? $shippingPostalCode : null,
        'shipping_courier' => $shippingRequired ? $shippingCourier : null,
        'shipping_cost' => 0,
    ]);

    echo json_encode(['success' => true, 'order_code' => $orderCode, 'file_url' => $fileUrl]);
} catch (RuntimeException $exception) {
    if ($fileAttachment !== null && is_file(UPLOAD_DIR . $fileAttachment)) {
        unlink(UPLOAD_DIR . $fileAttachment);
    }
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
} catch (Throwable $exception) {
    if ($fileAttachment !== null && is_file(UPLOAD_DIR . $fileAttachment)) {
        unlink(UPLOAD_DIR . $fileAttachment);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Pesanan belum dapat disimpan. Periksa koneksi database XAMPP.']);
}
