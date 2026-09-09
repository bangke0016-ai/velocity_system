<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/config/database.php';

function adminChatResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    adminChatResponse(['success' => false, 'message' => 'Sesi admin tidak valid.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    adminChatResponse(['success' => false, 'message' => 'Metode tidak didukung.'], 405);
}

$conversationToken = (string) ($_GET['conversation'] ?? '');
$after = max(0, (int) ($_GET['after'] ?? 0));
if ($conversationToken === '') {
    try {
        $db = Database::getInstance()->getConnection();
        $latest = $db->query('SELECT conversation_token, MAX(id) AS last_id FROM chat_messages GROUP BY conversation_token ORDER BY last_id DESC LIMIT 100')->fetchAll();
        adminChatResponse(['success' => true, 'conversations' => $latest]);
    } catch (Throwable $exception) {
        adminChatResponse(['success' => false, 'message' => 'Percakapan belum dapat dimuat.'], 500);
    }
}
if (!preg_match('/^[a-f0-9]{64}$/', $conversationToken)) {
    adminChatResponse(['success' => false, 'message' => 'Percakapan tidak valid.'], 422);
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT id, sender, message, created_at FROM chat_messages WHERE conversation_token = ? AND id > ? ORDER BY id ASC LIMIT 100');
    $stmt->execute([$conversationToken, $after]);
    adminChatResponse(['success' => true, 'messages' => $stmt->fetchAll()]);
} catch (Throwable $exception) {
    adminChatResponse(['success' => false, 'message' => 'Pesan belum dapat dimuat.'], 500);
}
