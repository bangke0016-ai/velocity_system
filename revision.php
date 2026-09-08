<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$orderCode = strtoupper(trim((string) ($_GET['order'] ?? $_POST['order'] ?? '')));
$error = '';
$message = '';
$db = Database::getInstance()->getConnection();
$order = null;
if (!preg_match('/^VEL-[A-Z0-9-]+$/', $orderCode)) {
    $error = 'Kode order tidak valid.';
} else {
    $stmt = $db->prepare('SELECT o.*, s.title AS service_title FROM orders o JOIN services s ON s.id = o.service_id WHERE o.order_code = ? LIMIT 1');
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();
    if (!$order) $error = 'Order tidak ditemukan.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order && !$error) {
    $note = trim((string) ($_POST['revision_note'] ?? ''));
    $revisionAttachment = null;
    $revisionLimit = max(0, (int) getSetting('max_revisions', '3'));
    if (!in_array($order['status'], ['selesai', 'revisi'], true)) {
        $error = 'Revisi dapat diajukan setelah order selesai.';
    } elseif ($note === '' || mb_strlen($note) > 2000) {
        $error = 'Jelaskan bagian yang perlu direvisi (maksimal 2.000 karakter).';
    } elseif ((int) $order['revision_count'] >= $revisionLimit) {
        $error = 'Batas revisi gratis untuk order ini sudah tercapai.';
    } else {
        if (!empty($_FILES['revision_attachment']['name'])) {
            $file = $_FILES['revision_attachment'];
            $allowed = ['application/pdf' => 'pdf', 'application/msword' => 'doc', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx', 'application/vnd.ms-powerpoint' => 'ppt', 'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'application/zip' => 'zip'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if ($file['error'] !== UPLOAD_ERR_OK || (int) $file['size'] > 10 * 1024 * 1024 || !is_uploaded_file($file['tmp_name']) || !isset($allowed[$mime])) {
                $error = 'File revisi harus berupa PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, atau ZIP maksimal 10 MB.';
            } else {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                $revisionAttachment = 'revisi-' . $order['id'] . '-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $revisionAttachment)) {
                    $error = 'File revisi gagal disimpan.';
                    $revisionAttachment = null;
                }
            }
        }
        if (!$error) {
            $stmt = $db->prepare("UPDATE orders SET status = 'revisi', revision_count = revision_count + 1, revision_note = ?, revision_attachment = ?, revision_requested_at = NOW() WHERE id = ?");
            $stmt->execute([$note, $revisionAttachment, $order['id']]);
        }
        if (!$error) {
        $message = 'Permintaan revisi berhasil dikirim. Admin akan segera meninjaunya.';
        $order['revision_count']++;
        }
    }
}
$revisionLimit = max(0, (int) getSetting('max_revisions', '3'));
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ajukan Revisi | Velocity System AI</title><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"><style>:root{--navy:#0f172a;--teal:#007c83;--teal-dark:#07555d;--muted:#64748b;--line:#dce5e9;--green:#15803d;--red:#b91c1c}*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 10% 0%,#d9f2ed,transparent 32%),linear-gradient(135deg,#f4f8fa,#edf5fb);color:var(--navy);font:15px 'DM Sans',sans-serif}h1,h2{font-family:'Space Grotesk',sans-serif}.shell{width:min(620px,calc(100% - 32px));margin:auto;padding:42px 0}.brand{text-align:center;color:var(--teal-dark);font:700 19px 'Space Grotesk';margin-bottom:24px}.brand i{color:#38bdf8;margin-right:8px}.card{overflow:hidden;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 22px 55px #0f172a18}.head{padding:30px;background:linear-gradient(135deg,var(--teal-dark),var(--teal));color:#fff}.head h1{margin:0 0 8px;font-size:28px}.head p{margin:0;color:#c9eeee;line-height:1.55}.body{padding:30px}.pill{display:inline-flex;gap:8px;margin-bottom:22px;padding:8px 12px;border-radius:999px;background:#e8f7f4;color:var(--teal-dark);font-weight:700;font-size:13px}.info{display:flex;gap:11px;margin-bottom:22px;padding:13px 14px;border-radius:10px;background:#f0fdf4;color:#166534;font-size:13px;line-height:1.5}.field{margin-bottom:20px}.field label{display:block;margin-bottom:8px;font-weight:700;font-size:13px}.field textarea{width:100%;min-height:150px;padding:13px;border:1px solid var(--line);border-radius:10px;resize:vertical;font:inherit}.field textarea:focus{outline:0;border-color:var(--teal);box-shadow:0 0 0 3px #007c831a}.btn{width:100%;padding:13px;border:0;border-radius:9px;background:var(--teal);color:#fff;font:700 14px 'DM Sans';cursor:pointer}.btn:hover{background:var(--teal-dark)}.alert{margin-bottom:20px;padding:13px;border-radius:9px}.success{background:#f0fdf4;color:var(--green)}.error{background:#fff1f2;color:var(--red)}.back{display:block;margin-top:18px;text-align:center;color:var(--teal-dark);font-weight:700}@media(max-width:560px){.shell{padding:24px 0}.head,.body{padding:22px}.head h1{font-size:24px}}</style></head><body><main class="shell"><div class="brand"><i class="fa-solid fa-bolt"></i> VELOCITY SYSTEM AI</div><section class="card"><div class="head"><h1>Ajukan Revisi</h1><p>Sampaikan bagian yang perlu diperbaiki agar admin dapat menindaklanjutinya dengan tepat.</p></div><?php if ($message): ?><div class="body"><div class="alert success"><i class="fa-solid fa-circle-check"></i> <?= $e($message) ?></div><a class="btn" href="status.php?order=<?= $e($orderCode) ?>">Lihat status pesanan</a></div><?php elseif ($error): ?><div class="body"><div class="alert error"><i class="fa-solid fa-circle-exclamation"></i> <?= $e($error) ?></div><?php endif; ?><?php if ($order && !$message): ?><form class="body" method="post"><input type="hidden" name="order" value="<?= $e($orderCode) ?>"><div class="pill"><i class="fa-solid fa-receipt"></i> <?= $e($order['order_code']) ?> · <?= $e($order['service_title']) ?></div><div class="info"><i class="fa-solid fa-shield-check"></i><span>Revisi gratis tersisa <strong><?= max(0, $revisionLimit - (int) $order['revision_count']) ?> kali</strong> dari <?= $revisionLimit ?> kali.</span></div><div class="field"><label for="revision_note">Bagian yang perlu direvisi</label><textarea id="revision_note" name="revision_note" required placeholder="Contoh: Tolong perbaiki halaman 3, bagian paragraf kedua..."><?= $e($order['revision_note'] ?? '') ?></textarea></div><button class="btn"><i class="fa-solid fa-paper-plane"></i> Kirim Permintaan Revisi</button></form><?php endif; ?></section><a class="back" href="status.php?order=<?= $e($orderCode) ?>"><i class="fa-solid fa-arrow-left"></i> Kembali ke status pesanan</a></main></body></html>
