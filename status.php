<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$orderCode = strtoupper(trim((string) ($_GET['order'] ?? '')));
$order = null;
if (preg_match('/^VEL-[A-Z0-9-]+$/', $orderCode)) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT o.*, s.title AS service_title FROM orders o JOIN services s ON s.id = o.service_id WHERE o.order_code = ? LIMIT 1');
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();
}
$statusLabels = ['pending_dp' => 'Menunggu DP', 'dp_paid' => 'DP Dibayar', 'pengerjaan' => 'Sedang Dikerjakan', 'revisi' => 'Tahap Revisi', 'selesai' => 'Selesai', 'cancelled' => 'Dibatalkan'];
$statusSteps = ['pending_dp', 'dp_paid', 'pengerjaan', 'revisi', 'selesai'];
$currentStatus = $order['status'] ?? '';
$currentStep = array_search($currentStatus, $statusSteps, true);
$currentStep = $currentStep === false ? -1 : $currentStep;
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cek Status Pesanan | Velocity System AI</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--navy:#0F172A;--teal:#007c83;--teal-dark:#07555d;--blue:#2563EB;--muted:#64748B;--line:#E2E8F0;--paper:#F5F8FA;--white:#fff;--green:#16A34A;--amber:#D97706;--red:#DC2626}*{box-sizing:border-box}body{margin:0;background:linear-gradient(135deg,#eff8f7,#f5f8fc);color:var(--navy);font:15px 'DM Sans',sans-serif}h1,h2{font-family:'Space Grotesk',sans-serif}.shell{width:min(720px,calc(100% - 32px));margin:0 auto;padding:38px 0 60px}.brand{text-align:center;color:var(--teal-dark);font:700 19px 'Space Grotesk';letter-spacing:.04em;margin-bottom:28px}.brand i{color:#38BDF8;margin-right:8px}.card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 18px 45px rgba(15,23,42,.08)}.hero{text-align:center}.hero h1{font-size:30px;margin:0 0 9px}.hero p{margin:0;color:var(--muted);line-height:1.6}.search{display:flex;gap:10px;margin-top:24px}.search input{min-width:0;flex:1;padding:13px 14px;border:1px solid var(--line);border-radius:9px;font:inherit;text-transform:uppercase}.btn{border:0;border-radius:9px;padding:13px 17px;background:var(--teal);color:#fff;font:700 14px 'DM Sans';cursor:pointer}.btn:hover{background:var(--teal-dark)}.error{margin-top:18px;padding:14px;border-radius:9px;background:#fff1f2;color:var(--red)}.result{margin-top:24px}.result-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;border-bottom:1px solid var(--line);padding-bottom:20px}.result-head h2{margin:0 0 7px;font-size:22px}.result-head p{margin:0;color:var(--muted)}.status{padding:8px 11px;border-radius:999px;background:#fff3d6;color:var(--amber);font-size:12px;font-weight:700;white-space:nowrap}.status.done{background:#dcfce7;color:var(--green)}.status.cancelled{background:#fee2e2;color:var(--red)}.progress{display:grid;grid-template-columns:repeat(5,1fr);gap:7px;margin:25px 0 28px}.step{text-align:center;color:#94A3B8;font-size:11px;font-weight:600}.step-icon{display:grid;place-items:center;width:30px;height:30px;margin:0 auto 8px;border:2px solid #CBD5E1;border-radius:50%;background:#fff}.step.active{color:var(--teal)}.step.active .step-icon{border-color:var(--teal);background:var(--teal);color:#fff}.details{display:grid;grid-template-columns:1fr 1fr;gap:14px}.detail{padding:14px;background:#F8FAFC;border-radius:9px}.detail span{display:block;margin-bottom:5px;color:var(--muted);font-size:12px}.detail strong{font-size:14px}.foot{text-align:center;margin-top:20px;color:var(--muted);font-size:13px;line-height:1.6}.foot a{color:var(--teal);font-weight:700}@media(max-width:560px){.card{padding:22px}.search{display:grid}.result-head{display:block}.status{display:inline-block;margin-top:15px}.details{grid-template-columns:1fr}.progress{gap:2px}.step{font-size:9px}}
</style>
<style>
.shipping-tracking{margin-top:26px;padding-top:24px;border-top:1px solid var(--line)}.shipping-tracking h3{display:flex;align-items:center;gap:9px;margin:0 0 16px;font:700 17px 'Space Grotesk'}.shipping-tracking h3 i{color:var(--teal)}.shipping-meta{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}.shipping-meta span{padding:7px 10px;border-radius:8px;background:#f0f8f7;color:var(--teal-dark);font-size:12px;font-weight:700}.shipping-track{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.shipping-step{position:relative;text-align:center;color:#94a3b8;font-size:10px;font-weight:700}.shipping-step::before{content:'';position:absolute;top:13px;left:50%;width:100%;height:2px;background:#e2e8f0;z-index:0}.shipping-step:last-child::before{display:none}.shipping-step-icon{position:relative;z-index:1;display:grid;place-items:center;width:28px;height:28px;margin:0 auto 8px;border-radius:50%;background:#e2e8f0;color:#94a3b8}.shipping-step.active{color:var(--teal)}.shipping-step.active .shipping-step-icon{background:var(--teal);color:#fff;box-shadow:0 0 0 5px #e1f4f1}.shipping-resi{margin-top:16px;padding:12px 14px;border-radius:9px;background:#f8fafc;color:var(--muted);font-size:12px}.shipping-resi strong{color:var(--navy)}@media(max-width:560px){.shipping-track{gap:2px}.shipping-step{font-size:9px}.shipping-step-icon{width:25px;height:25px}.shipping-step::before{top:12px}}
</style>
<style>.revision-link{display:flex;align-items:center;gap:12px;margin-top:18px;padding:14px 15px;border:1px solid #cbdcfb;border-radius:11px;background:linear-gradient(135deg,#eff6ff,#f8fbff);color:#1d4ed8;transition:transform .2s ease,box-shadow .2s ease}.revision-link:hover{transform:translateY(-2px);box-shadow:0 8px 18px rgba(37,99,235,.12)}.revision-link>i:first-child{font-size:18px}.revision-link span{flex:1}.revision-link strong,.revision-link small{display:block}.revision-link strong{font-size:13px}.revision-link small{margin-top:3px;color:#64748b;font-size:11px}</style>
</head>
<body><main class="shell"><div class="brand"><i class="fa-solid fa-bolt"></i> VELOCITY SYSTEM AI</div><section class="card hero"><h1>Cek Status Pesanan Anda</h1><p>Masukkan kode order untuk melihat progres pengerjaan secara praktis dan transparan.</p><form class="search" method="get"><input name="order" value="<?= $e($orderCode) ?>" placeholder="Contoh: VEL-20260821137" required><button class="btn"><i class="fa-solid fa-magnifying-glass"></i> Cek Status</button></form></section><?php if ($orderCode !== '' && !$order): ?><div class="error"><i class="fa-solid fa-circle-exclamation"></i> Kode order tidak ditemukan. Periksa kembali kode yang Anda terima.</div><?php elseif ($order): ?><section class="card result"><div class="result-head"><div><h2><?= $e($order['order_code']) ?></h2><p>Halo, <?= $e($order['customer_name']) ?>. Berikut informasi pesanan Anda.</p></div><span class="status <?= $currentStatus === 'selesai' ? 'done' : ($currentStatus === 'cancelled' ? 'cancelled' : '') ?>"><?= $e($statusLabels[$currentStatus] ?? $currentStatus) ?></span></div><div class="progress"><?php foreach ($statusSteps as $index => $step): ?><div class="step <?= $index <= $currentStep ? 'active' : '' ?>"><div class="step-icon"><i class="fa-solid <?= $index <= $currentStep ? 'fa-check' : 'fa-circle' ?>"></i></div><?= $e($statusLabels[$step]) ?></div><?php endforeach; ?></div><div class="details"><div class="detail"><span>Layanan</span><strong><?= $e($order['service_title']) ?></strong></div><div class="detail"><span>Jumlah</span><strong><?= (int) $order['quantity'] ?> unit</strong></div><div class="detail"><span>Deadline</span><strong><?= $order['deadline_at'] ? date('d M Y, H:i', strtotime($order['deadline_at'])) : '-' ?></strong></div><div class="detail"><span>Total pesanan</span><strong><?= formatRupiah($order['total_price']) ?></strong></div></div></section><?php endif; ?><p class="foot">Simpan kode order Anda untuk pengecekan berikutnya.<br>Butuh bantuan? <a href="<?= $e(SITE_URL) ?>#footer">Hubungi admin Velocity System AI</a></p></main></body>
<?php if ($order && $currentStatus === 'selesai' && empty($order['shipping_required'])): ?><div style="max-width:720px;margin:-42px auto 40px;padding:0 30px;text-align:center"><a class="btn" href="shipping.php?order=<?= $e($order['order_code']) ?>"><i class="fa-solid fa-truck-fast"></i> Isi Form Pengiriman Paket Fisik</a></div><?php endif; ?><script>
const orderResult = document.querySelector('.result');
const shippingReady = <?= json_encode(!empty($order['shipping_required'])) ?>;
const shippingStatus = <?= json_encode($order['shipping_status'] ?? 'menunggu') ?>;
const shippingCourier = <?= json_encode($order['shipping_courier'] ?? '') ?>;
const shippingTracking = <?= json_encode($order['shipping_tracking'] ?? '') ?>;
const shippingReceipt = <?= json_encode($order['shipping_receipt'] ?? '') ?>;
const orderStatus = <?= json_encode($currentStatus) ?>;
const revisionCount = <?= (int) ($order['revision_count'] ?? 0) ?>;
const revisionLimit = <?= (int) getSetting('max_revisions', '3') ?>;
if (orderResult && ['selesai', 'revisi'].includes(orderStatus) && revisionCount < revisionLimit) {
    const revisionLink = document.createElement('a');
    revisionLink.className = 'revision-link';
    revisionLink.href = `revision.php?order=<?= $e($orderCode) ?>`;
    revisionLink.innerHTML = `<i class="fa-solid fa-pen-to-square"></i><span><strong>Perlu perbaikan?</strong><small>Ajukan revisi untuk order ini. Sisa ${revisionLimit - revisionCount} kali.</small></span><i class="fa-solid fa-arrow-right"></i>`;
    orderResult.appendChild(revisionLink);
}
if (orderResult && shippingReady) {
    const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character]));
    const statuses = ['menunggu', 'diproses', 'dikirim', 'selesai'];
    const labels = ['Menunggu diproses', 'Sedang diproses', 'Sudah dikirim', 'Pengiriman selesai'];
    const icons = ['fa-clipboard-check', 'fa-box', 'fa-truck-fast', 'fa-circle-check'];
    const current = Math.max(0, statuses.indexOf(shippingStatus));
    const tracking = document.createElement('div');
    tracking.className = 'shipping-tracking';
    const receiptLink = shippingReceipt ? `<a href="<?= $e(SITE_URL) ?>public/uploads/${encodeURIComponent(shippingReceipt)}" target="_blank" rel="noopener">Lihat file resi pengiriman</a>` : 'File resi akan ditampilkan setelah admin mengunggahnya.';
    tracking.innerHTML = `<h3><i class="fa-solid fa-truck-fast"></i> Status pengiriman paket</h3><div class="shipping-meta"><span><i class="fa-solid fa-building"></i> Kurir: ${escapeHtml(shippingCourier || 'Belum dipilih')}</span><span><i class="fa-solid fa-location-dot"></i> Data alamat sudah diterima</span></div><div class="shipping-track">${labels.map((label, index) => `<div class="shipping-step ${index <= current ? 'active' : ''}"><div class="shipping-step-icon"><i class="fa-solid ${icons[index]}"></i></div>${label}</div>`).join('')}</div><div class="shipping-resi"><i class="fa-solid fa-file-arrow-down"></i> ${shippingTracking ? `Nomor resi: <strong>${escapeHtml(shippingTracking)}</strong>` : ''} ${receiptLink}</div>`;
    orderResult.appendChild(tracking);
}
</script></html>
