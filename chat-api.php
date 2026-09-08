<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();
$token = (string) ($_SESSION['chat_token'] ?? '');
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['chat_token'] = $token;
}

function chatResponse(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function assistantReply(PDO $db, string $message): string {
    $normalized = trim((string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', mb_strtolower($message)));
    $words = array_values(array_filter(preg_split('/\s+/', $normalized)));
    $has = static function (array $terms) use ($normalized, $words): bool {
        foreach ($terms as $term) {
            if (str_contains($normalized, $term)) return true;
        }
        return false;
    };

    if ($has(['halo', 'hai', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'assalam'])) {
        return 'Halo! Saya Velocity Assistant. Saya bisa mencari informasi layanan, harga, keamanan data, revisi, dan deadline dari data website. Apa yang ingin Anda tanyakan?';
    }
    if ($has(['aman', 'privasi', 'rahasia', 'data pribadi', 'keamanan'])) {
        return 'Identitas dan data pribadi Anda kami jaga dengan aman dan rahasia. Data digunakan hanya untuk kebutuhan pemesanan dan komunikasi layanan.';
    }
    if ($has(['revisi', 'nilai jelek', 'tidak sesuai', 'garansi', 'brief'])) {
        return 'Kami memberikan garansi revisi gratis hingga hasilnya sesuai brief awal yang disepakati. Sertakan brief yang jelas agar tim dapat mengerjakannya sesuai kebutuhan.';
    }
    if ($has(['cepat', 'kilat', 'deadline', 'darurat', 'selesai kapan', 'berapa lama'])) {
        return 'Layanan kilat tersedia untuk deadline darurat. Estimasi bergantung pada jenis, jumlah, dan tingkat kesulitan tugas. Kirim jenis tugas serta deadline Anda agar admin dapat mengecek ketersediaannya.';
    }
    if ($has(['cara pesan', 'cara order', 'memesan', 'booking', 'pesan'])) {
        return 'Cara pesan: pilih layanan, isi detail tugas dan deadline, unggah lampiran bila ada, lalu kirim pesanan. Admin akan memprosesnya setelah detail diterima.';
    }

    $services = $db->query("SELECT s.title, s.price, s.price_max, s.unit, c.name AS category_name FROM services s JOIN categories c ON c.id = s.category_id WHERE s.is_active = 1 AND c.is_active = 1 ORDER BY c.sort_order, s.sort_order, s.title")->fetchAll();
    $matches = [];
    foreach ($services as $service) {
        $serviceText = mb_strtolower($service['title'] . ' ' . $service['category_name']);
        $score = 0;
        foreach ($words as $word) {
            if (mb_strlen($word) >= 3 && str_contains($serviceText, $word)) $score++;
        }
        if ($score > 0) $matches[] = ['score' => $score, 'service' => $service];
    }
    usort($matches, static fn(array $left, array $right): int => $right['score'] <=> $left['score']);

    if ($matches && $has(['harga', 'biaya', 'tarif', 'berapa', 'bayar', 'rp', 'rupiah'])) {
        $items = [];
        foreach (array_slice($matches, 0, 3) as $match) {
            $service = $match['service'];
            $price = 'Rp ' . number_format((int) $service['price'], 0, ',', '.');
            if (!empty($service['price_max'])) $price .= ' - Rp ' . number_format((int) $service['price_max'], 0, ',', '.');
            $items[] = $service['title'] . ': ' . $price . ' / ' . $service['unit'];
        }
        return "Saya menemukan layanan yang mungkin sesuai dari katalog website:\n- " . implode("\n- ", $items) . "\n\nHarga dapat menyesuaikan detail tugas. Admin dapat membantu mengonfirmasi estimasi final.";
    }
    if ($matches) {
        $names = array_map(static fn(array $match): string => $match['service']['title'], array_slice($matches, 0, 3));
        return 'Dari katalog website, layanan yang paling mendekati pertanyaan Anda adalah: ' . implode(', ', $names) . '. Apakah Anda ingin mengetahui harga atau cara pemesanannya?';
    }
    if ($has(['harga', 'biaya', 'tarif', 'berapa', 'bayar', 'rp', 'rupiah'])) {
        return 'Harga mengikuti jenis dan tingkat kesulitan layanan. Sebutkan nama layanan atau jenis tugas Anda agar saya dapat mencari data yang paling sesuai dari katalog.';
    }
    return 'Saya belum menemukan sumber jawaban yang cukup tepat dari data website. Tolong tuliskan jenis tugas, deadline, atau layanan yang dimaksud. Jika masih belum terjawab, admin akan melanjutkan percakapan ini.';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $after = max(0, (int) ($_GET['after'] ?? 0));
    $stmt = $db->prepare('SELECT id, sender, message, created_at FROM chat_messages WHERE conversation_token = ? AND id > ? ORDER BY id ASC LIMIT 100');
    $stmt->execute([$token, $after]);
    chatResponse(['success' => true, 'token' => $token, 'messages' => $stmt->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatResponse(['success' => false, 'message' => 'Metode tidak didukung.'], 405);
}

$message = trim((string) ($_POST['message'] ?? ''));
if ($message === '' || mb_strlen($message) > 2000) {
    chatResponse(['success' => false, 'message' => 'Pesan wajib diisi dan maksimal 2.000 karakter.'], 422);
}

$stmt = $db->prepare("INSERT INTO chat_messages (conversation_token, sender, message) VALUES (?, 'visitor', ?)");
$stmt->execute([$token, $message]);
$assistantMessage = assistantReply($db, $message);
$stmt = $db->prepare("INSERT INTO chat_messages (conversation_token, sender, message) VALUES (?, 'assistant', ?)");
$stmt->execute([$token, $assistantMessage]);
chatResponse(['success' => true, 'token' => $token, 'message_id' => (int) $db->lastInsertId(), 'assistant_reply' => true]);