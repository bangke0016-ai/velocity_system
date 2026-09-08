<?php
/** Invoice order. Pasang library FPDF di lib/fpdf.php untuk output PDF native. */
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
if (!isset($_SESSION['admin_id'])) { redirect('admin.php'); }
$orderId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$orderId) { http_response_code(400); exit('Order tidak valid.'); }
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT o.*, s.title AS service_title FROM orders o JOIN services s ON s.id = o.service_id WHERE o.id = ? LIMIT 1');
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); exit('Order tidak ditemukan.'); }
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$completedDate = $order['payout_processed_at'] ?: $order['updated_at'];
$invoiceNumber = 'INV-' . $order['order_code'];
$siteName = getSetting('site_name', 'VELOCITY SYSTEM AI');
$siteEmail = getSetting('email', 'info@velocitysystem.ai');
$siteWhatsapp = getSetting('whatsapp_display', getSetting('whatsapp', ''));
$siteInstagram = getSetting('instagram', '');
$siteTiktok = getSetting('tiktok', '');
$siteInstagramName = getSetting('instagram_name', 'velocity_system.id');
$siteTiktokName = getSetting('tiktok_name', 'exam_shop');
$socialHandle = static function (string $value): string {
    $value = trim($value);
    if ($value === '' || $value === '#') { return '@velocitysystem'; }
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        $path = trim((string) parse_url($value, PHP_URL_PATH), '/');
        $value = basename($path) ?: $value;
    }
    return str_starts_with($value, '@') ? $value : '@' . ltrim($value, '/');
};
$siteInstagram = $socialHandle($siteInstagram);
$siteTiktok = $socialHandle($siteTiktok);
$siteInstagramName = $socialHandle($siteInstagramName);
$siteTiktokName = $socialHandle($siteTiktokName);
$siteLocation = 'Instagram: ' . $siteInstagramName . ' | TikTok: ' . $siteTiktokName;
$socialContacts = 'Instagram: ' . $siteInstagramName . ' | TikTok: ' . $siteTiktokName . ' | WA: ' . $siteWhatsapp;
$paymentMethods = getSetting('payment_methods', 'Transfer Bank / E-Wallet');
$remainingPayment = max(0, (int) $order['total_price'] - (int) $order['dp_amount']);
$logoPath = __DIR__ . '/assets/logo.png';
$logoUrl = SITE_URL . 'assets/logo.png';
$fpdf = __DIR__ . '/lib/fpdf.php';
if (is_file($fpdf)) {
    require_once $fpdf;
    $pdf = new FPDF();
    $pdf->SetTitle($invoiceNumber);
    $pdf->AddPage();
    $pdf->SetMargins(18, 16, 18);
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->SetFillColor(0, 124, 131);
    $pdf->Rect(0, 0, 210, 7, 'F');
    $pdf->SetTextColor(23, 35, 46);
    if (is_file($logoPath)) {
        $pdf->Image($logoPath, 18, 14, 48, 0, 'PNG');
        $pdf->SetX(72);
    }
    $pdf->SetFont('Arial', 'B', 19);
    $pdf->Cell(is_file($logoPath) ? 58 : 112, 12, is_file($logoPath) ? '' : strtoupper($siteName), 0, 0);
    $pdf->SetTextColor(0, 124, 131);
    $pdf->SetFont('Arial', 'B', 22);
    $pdf->Cell(62, 12, 'INVOICE', 0, 1, 'R');
    $pdf->SetTextColor(113, 128, 140);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(112, 6, $socialContacts, 0, 0);
    $pdf->Cell(62, 6, $invoiceNumber, 0, 1, 'R');
    $pdf->Cell(112, 6, $siteEmail, 0, 0);
    $pdf->Cell(62, 6, 'Tanggal: ' . date('d M Y', strtotime($completedDate)), 0, 1, 'R');
    $pdf->SetDrawColor(220, 229, 233);
    $pdf->Line(18, 48, 192, 48);
    $pdf->Ln(14);
    $pdf->SetTextColor(113, 128, 140);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(87, 6, 'BILL TO', 0, 0);
    $pdf->Cell(87, 6, 'ORDER INFORMATION', 0, 1);
    $pdf->SetTextColor(23, 35, 46);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(87, 7, $order['customer_name'], 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(87, 7, 'Order: ' . $order['order_code'], 0, 1);
    $pdf->SetTextColor(113, 128, 140);
    $pdf->Cell(87, 6, 'Customer / Client', 0, 0);
    $pdf->Cell(87, 6, 'Status: Selesai', 0, 1);
    $pdf->Ln(12);
    $pdf->SetFillColor(7, 85, 93);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(104, 10, 'DESKRIPSI LAYANAN', 0, 0, 'L', true);
    $pdf->Cell(20, 10, 'QTY', 0, 0, 'C', true);
    $pdf->Cell(50, 10, 'TOTAL', 0, 1, 'R', true);
    $pdf->SetTextColor(23, 35, 46);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(104, 12, $order['service_title'], 'B', 0);
    $pdf->Cell(20, 12, (string) $order['quantity'], 'B', 0, 'C');
    $pdf->Cell(50, 12, formatRupiah($order['total_price']), 'B', 1, 'R');
    $pdf->Ln(12);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(113, 128, 140);
    $pdf->Cell(124, 8, 'Tanggal selesai', 0, 0);
    $pdf->SetTextColor(23, 35, 46);
    $pdf->Cell(50, 8, date('d M Y, H:i', strtotime($completedDate)), 0, 1, 'R');
    $pdf->SetDrawColor(220, 229, 233);
    $pdf->Line(18, $pdf->GetY() + 2, 192, $pdf->GetY() + 2);
    $pdf->Ln(8);
    $pdf->SetFillColor(231, 246, 242);
    $pdf->SetTextColor(7, 85, 93);
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(124, 14, 'TOTAL PEMBAYARAN', 0, 0, 'L', true);
    $pdf->Cell(50, 14, formatRupiah($order['total_price']), 0, 1, 'R', true);
    $pdf->Ln(8);
    $pdf->SetTextColor(113, 128, 140);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(124, 8, 'DP / pembayaran awal', 0, 0);
    $pdf->SetTextColor(23, 35, 46);
    $pdf->Cell(50, 8, formatRupiah($order['dp_amount']), 0, 1, 'R');
    $pdf->SetTextColor(113, 128, 140);
    $pdf->Cell(124, 8, 'Sisa pembayaran', 0, 0);
    $pdf->SetTextColor(23, 35, 46);
    $pdf->Cell(50, 8, formatRupiah($remainingPayment), 0, 1, 'R');
    $pdf->Ln(8);
    $pdf->SetTextColor(113, 128, 140);
    $pdf->Cell(87, 6, 'METODE PEMBAYARAN', 0, 0);
    $pdf->Cell(87, 6, 'DIPERIKSA OLEH', 0, 1);
    $pdf->SetTextColor(23, 35, 46);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(87, 8, $paymentMethods, 0, 0);
    $pdf->Cell(87, 8, 'Admin Velocity System', 0, 1);
    $pdf->Ln(12);
    $pdf->SetTextColor(113, 128, 140);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(174, 5, 'Terima kasih telah menggunakan layanan ' . $siteName . '. Invoice ini diterbitkan sebagai bukti pembayaran resmi dan dibuat secara elektronik.', 0, 'L');
    $pdf->SetY(270);
    $pdf->SetTextColor(0, 124, 131);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(174, 6, 'THANK YOU FOR YOUR BUSINESS', 0, 1, 'C');
    $pdf->Output('D', 'invoice-' . $order['order_code'] . '.pdf'); exit;
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $e($invoiceNumber) ?> | <?= $e($siteName) ?></title><style>@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap');:root{--ink:#17232e;--muted:#71808c;--line:#dce5e9;--teal:#007c83;--dark:#07555d;--mint:#e7f6f2}*{box-sizing:border-box}body{margin:0;background:#eef3f4;color:var(--ink);font:14px 'DM Sans',sans-serif}.invoice{width:min(820px,calc(100% - 32px));margin:36px auto;background:#fff;padding:0 58px 52px;box-shadow:0 20px 55px rgba(25,53,62,.12)}.invoice-head{display:flex;justify-content:space-between;gap:30px;margin:0 -58px;padding:34px 58px 28px;background:linear-gradient(120deg,#52d3d6 0%,#22b8bb 55%,#07979c 100%);border-bottom:7px solid var(--teal)}.brand{font:700 21px 'Space Grotesk';letter-spacing:.03em}.brand img{display:block;width:190px;max-height:76px;object-fit:contain;object-position:left center}.company{margin-top:8px;color:#07555d;line-height:1.6}.invoice-title{text-align:right}.invoice-title h1{margin:0;color:#17232e;font:700 34px 'Space Grotesk';letter-spacing:.04em}.invoice-title p{margin:8px 0 0;color:#07555d;font-weight:600}.meta{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin:32px 0}.meta h3{margin:0 0 10px;color:var(--muted);font-size:11px;letter-spacing:.12em}.meta strong{font:700 16px 'Space Grotesk'}.meta p{margin:5px 0;color:var(--muted)}table{width:100%;border-collapse:collapse;margin-top:10px}th{padding:13px 14px;background:#51d0d3;color:#07555d;text-align:left;font-size:11px;letter-spacing:.08em}td{padding:19px 14px;border-bottom:1px solid var(--line)}th:nth-child(2),td:nth-child(2){text-align:center;width:80px}th:last-child,td:last-child{text-align:right}.summary{display:flex;justify-content:flex-end;margin-top:26px}.total{display:grid;grid-template-columns:190px 170px;align-items:center;padding:17px 20px;background:var(--mint);color:var(--dark);font:700 18px 'Space Grotesk'}.total strong{text-align:right;font-size:20px}.payment-grid{display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-top:28px;padding-top:18px;border-top:1px solid var(--line);color:var(--muted)}.payment-grid strong{display:block;margin-top:7px;color:var(--ink);font-size:14px}.completion{display:flex;justify-content:space-between;margin-top:22px;padding-top:15px;border-top:1px solid var(--line);color:var(--muted)}.footer{margin-top:52px;padding-top:22px;border-top:1px solid var(--line);text-align:center;color:var(--muted);font-size:12px;line-height:1.7}.print{display:block;margin:22px auto 0;padding:11px 18px;border:0;border-radius:8px;background:var(--teal);color:#fff;font:600 14px 'DM Sans';cursor:pointer}@media(max-width:600px){.invoice{padding:0 22px 32px;margin:16px auto}.invoice-head{display:block;margin:0 -22px;padding:28px 22px}.invoice-title{text-align:left;margin-top:24px}.meta,.payment-grid{display:block}.meta>div+div,.payment-grid>div+div{margin-top:22px}.total{grid-template-columns:1fr 1fr;font-size:14px}.total strong{font-size:16px}.completion{display:block}.completion span{display:block;margin-top:6px}}@media print{body{background:#fff}.invoice{width:100%;margin:0;padding:0 28px 28px;box-shadow:none}.invoice-head{margin:0 -28px;padding-left:28px;padding-right:28px}.print{display:none}}</style></head><body><main class="invoice"><header class="invoice-head"><div><div class="brand"><?php if (is_file($logoPath)): ?><img src="<?= $e($logoUrl) ?>" alt="<?= $e($siteName) ?> logo"><?php else: ?><?= $e(strtoupper($siteName)) ?><?php endif; ?></div><div class="company"><?= $e($siteLocation) ?><br><?= $e($siteEmail) ?><?= $siteWhatsapp ? ' · ' . $e($siteWhatsapp) : '' ?></div></div><div class="invoice-title"><h1>INVOICE</h1><p><?= $e($invoiceNumber) ?><br><?= date('d M Y', strtotime($completedDate)) ?></p></div></header><section class="meta"><div><h3>KEPADA / BILL TO</h3><strong><?= $e($order['customer_name']) ?></strong><p>Customer / Client</p></div><div><h3>INFORMASI ORDER</h3><strong><?= $e($order['order_code']) ?></strong><p>Status: Selesai</p></div></section><table><thead><tr><th>DESKRIPSI LAYANAN</th><th>QTY</th><th>TOTAL</th></tr></thead><tbody><tr><td><?= $e($order['service_title']) ?></td><td><?= (int) $order['quantity'] ?></td><td><?= formatRupiah($order['total_price']) ?></td></tr></tbody></table><div class="summary"><div class="total"><span>TOTAL PEMBAYARAN</span><strong><?= formatRupiah($order['total_price']) ?></strong></div></div><div class="payment-grid"><div><span>METODE PEMBAYARAN</span><strong><?= $e($paymentMethods) ?></strong></div><div><span>RINCIAN PEMBAYARAN</span><strong>DP: <?= formatRupiah($order['dp_amount']) ?><br>Sisa: <?= formatRupiah($remainingPayment) ?></strong></div></div><div class="completion"><span>Tanggal selesai</span><strong><?= date('d M Y, H:i', strtotime($completedDate)) ?></strong></div><footer class="footer">Terima kasih telah menggunakan layanan <?= $e($siteName) ?>.<br>Invoice ini diterbitkan sebagai bukti pembayaran resmi dan dibuat secara elektronik.</footer><button class="print" onclick="window.print()">Cetak / Simpan PDF</button></main></body></html>
