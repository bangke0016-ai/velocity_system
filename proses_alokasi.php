<?php
/** Endpoint aman untuk mengalokasikan satu order kepada joki yang sesuai. */
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

if (!isset($_SESSION['admin_id'])) { redirect('admin.php'); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals((string) ($_SESSION['admin_csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) {
    http_response_code(419);
    exit('Sesi formulir kedaluwarsa. Silakan kembali dan coba lagi.');
}

$db = Database::getInstance()->getConnection();
$orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
$jokiId = filter_var($_POST['joki_id'] ?? null, FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? 'allocate');
try {
    if (!$orderId || !$jokiId || !in_array($action, ['allocate', 'replace'], true)) { throw new InvalidArgumentException('Order dan joki wajib dipilih.'); }
    $stmt = $db->prepare('SELECT o.*, s.title AS service_title, c.name AS category_name FROM orders o JOIN services s ON s.id = o.service_id JOIN categories c ON c.id = s.category_id WHERE o.id = ? FOR UPDATE');
    $db->beginTransaction();
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    $stmt = $db->prepare("SELECT * FROM joki WHERE id = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$jokiId]);
    $joki = $stmt->fetch();
    if (!$order || !$joki) { throw new RuntimeException('Order atau joki tidak ditemukan.'); }
    if ($order['status'] === 'selesai' || $order['status_alokasi'] === 'selesai') { throw new RuntimeException('Order yang sudah selesai tidak dapat dialokasikan.'); }
    if ($action === 'replace' && (int) $order['joki_id'] === $jokiId) { throw new RuntimeException('Pilih penjoki pengganti yang berbeda.'); }
    $stmt = $db->prepare("UPDATE orders SET joki_id = ?, status_alokasi = 'diproses_joki' WHERE id = ? AND status_alokasi <> 'selesai'");
    $stmt->execute([$jokiId, $orderId]);
    $db->commit();

    $earning = (int) floor((int) $order['total_price'] * 0.70);
    $deadline = $order['deadline_at'] ? date('d M Y, H:i', strtotime($order['deadline_at'])) : 'Belum ditentukan';
    $clientWhatsapp = formatWhatsAppNumber($order['whatsapp']);
    $clientChatLink = 'https://wa.me/' . $clientWhatsapp;
    $opening = $action === 'replace' ? 'Anda ditunjuk sebagai penjoki pengganti untuk order berikut.' : 'Anda mendapatkan tugas baru dari Velocity System.';
    $message = "Halo {$joki['nama']},\n\n{$opening}\n\nDATA ORDER\n- ID Order: {$order['order_code']}\n- Judul Tugas: {$order['service_title']}\n- Deadline: {$deadline}\n- Bagian Anda (70%): " . formatRupiah($earning) . "\n\nDATA CLIENT\n- Nama: {$order['customer_name']}\n- WhatsApp: {$order['whatsapp']}\n- Link chat client: {$clientChatLink}\n\nSilakan hubungi client untuk konsultasi kebutuhan tugas dan konfirmasi penerimaan tugas ini.";
    $_SESSION['allocation_whatsapp'] = 'https://wa.me/' . formatWhatsAppNumber($joki['whatsapp']) . '?text=' . rawurlencode($message);
    setFlash('success', $action === 'replace' ? "Penjoki order {$order['order_code']} berhasil diganti ke {$joki['nama']}." : "Order {$order['order_code']} berhasil dialokasikan kepada {$joki['nama']}.");
} catch (Throwable $exception) {
    if ($db->inTransaction()) { $db->rollBack(); }
    setFlash('error', $exception->getMessage());
}
redirect('admin.php?tab=alokasi');
