<?php
/** Form publik ber-token untuk pengisian rekening dan pengajuan penarikan joki. */
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$db = Database::getInstance()->getConnection();
$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$error = '';
$linkData = null;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = $db->prepare('SELECT l.*, j.nama, j.saldo_joki, j.withdrawal_requested FROM joki_payout_links l JOIN joki j ON j.id = l.joki_id WHERE l.token = ? AND l.used_at IS NULL AND l.expires_at >= NOW() LIMIT 1');
    $stmt->execute([$token]);
    $linkData = $stmt->fetch() ?: null;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $linkData) {
    $method = (string) ($_POST['method'] ?? '');
    $accountName = trim((string) ($_POST['account_name'] ?? ''));
    $accountNumber = trim((string) ($_POST['account_number'] ?? ''));
    if (!in_array($method, ['bank', 'e-wallet'], true) || $accountName === '' || mb_strlen($accountName) > 100 || !preg_match('/^[a-zA-Z0-9 .+()_-]{4,100}$/', $accountNumber)) {
        $error = 'Metode dan data rekening/e-wallet wajib diisi dengan benar.';
    } elseif ((int) $linkData['withdrawal_requested'] === 1) {
        $error = 'Permintaan penarikan Anda sedang menunggu konfirmasi admin.';
    } else {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare('SELECT id, saldo_joki, withdrawal_requested FROM joki WHERE id = ? FOR UPDATE');
            $stmt->execute([(int) $linkData['joki_id']]);
            $joki = $stmt->fetch();
            if (!$joki || (int) $joki['withdrawal_requested'] === 1 || (int) $linkData['amount'] > (int) $joki['saldo_joki']) {
                throw new RuntimeException('Saldo tidak tersedia atau permintaan sedang diproses.');
            }
            $stmt = $db->prepare('UPDATE joki SET withdrawal_requested = 1, withdrawal_amount = ?, withdrawal_method = ?, withdrawal_account_name = ?, withdrawal_account_number = ? WHERE id = ?');
            $stmt->execute([(int) $linkData['amount'], $method, $accountName, $accountNumber, (int) $linkData['joki_id']]);
            $stmt = $db->prepare("INSERT INTO payout_joki (id_joki, nominal, status) VALUES (?, ?, 'pending')");
            $stmt->execute([(int) $linkData['joki_id'], (int) $linkData['amount']]);
            $stmt = $db->prepare('UPDATE joki_payout_links SET used_at = NOW() WHERE id = ? AND used_at IS NULL');
            $stmt->execute([(int) $linkData['id']]);
            $db->commit();
            $success = true;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) { $db->rollBack(); }
            $error = $exception->getMessage();
        }
    }
}
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Form Penarikan | Velocity</title><style>body{margin:0;background:#f5f7f8;color:#17232e;font:15px Arial,sans-serif}.box{width:min(520px,calc(100% - 32px));margin:8vh auto;padding:30px;background:#fff;border:1px solid #dce5e9;border-radius:14px;box-shadow:0 16px 38px rgba(25,53,62,.1)}h1{margin:0 0 8px;font-size:25px}p{color:#71808c;line-height:1.5}.amount{padding:14px;margin:20px 0;background:#e7f6f2;border-radius:8px;color:#07555d;font-weight:bold}label{display:block;margin:16px 0 7px;font-weight:bold}input,select{width:100%;padding:12px;border:1px solid #dce5e9;border-radius:8px;box-sizing:border-box;font:inherit}button{width:100%;margin-top:22px;padding:13px;border:0;border-radius:8px;background:#007c83;color:#fff;font-weight:bold;font-size:15px;cursor:pointer}.error{padding:12px;background:#fff0f0;color:#b33b3b;border-radius:8px}.success{text-align:center}</style></head><body><main class="box"><?php if (!empty($success)): ?><div class="success"><h1>Permintaan berhasil dikirim</h1><p>Data rekening Anda sudah diterima. Admin akan memverifikasi transfer dan memperbarui status pembayaran.</p></div><?php elseif (!$linkData): ?><h1>Link tidak valid</h1><p>Link sudah digunakan, kedaluwarsa, atau tidak ditemukan. Silakan hubungi admin.</p><?php else: ?><h1>Form penarikan saldo</h1><p>Halo <?= $e($linkData['nama']) ?>, isi data rekening atau e-wallet untuk pencairan saldo tugas.</p><div class="amount">Nominal yang diajukan: <?= formatRupiah($linkData['amount']) ?></div><?php if ($error): ?><div class="error"><?= $e($error) ?></div><?php endif; ?><form method="post"><input type="hidden" name="token" value="<?= $e($token) ?>"><label>Metode pencairan</label><select name="method" required><option value="">Pilih metode</option><option value="bank">Bank</option><option value="e-wallet">E-Wallet</option></select><label>Nama pemilik rekening</label><input name="account_name" maxlength="100" required><label>Nomor rekening / nomor e-wallet</label><input name="account_number" maxlength="100" required><button type="submit">Kirim permintaan penarikan</button></form><?php endif; ?></main></body></html>
