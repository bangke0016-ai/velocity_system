<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$orderCode = strtoupper(trim((string) ($_GET['order'] ?? $_POST['order'] ?? '')));
$message = '';
$error = '';
$db = Database::getInstance()->getConnection();

if (!preg_match('/^VEL-[A-Z0-9-]+$/', $orderCode)) {
    $error = 'Kode order tidak valid.';
}

$order = null;
if (!$error) {
    $stmt = $db->prepare('SELECT o.*, s.title AS service_title FROM orders o JOIN services s ON s.id = o.service_id WHERE o.order_code = ? LIMIT 1');
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();
    if (!$order) {
        $error = 'Order tidak ditemukan. Periksa kembali kode order Anda.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order && !$error) {
    $shippingName = trim((string) ($_POST['shipping_name'] ?? ''));
    $shippingPhone = trim((string) ($_POST['shipping_phone'] ?? ''));
    $shippingAddress = trim((string) ($_POST['shipping_address'] ?? ''));
    $shippingCity = trim((string) ($_POST['shipping_city'] ?? ''));
    $shippingPostalCode = trim((string) ($_POST['shipping_postal_code'] ?? ''));
    $shippingCourier = trim((string) ($_POST['shipping_courier'] ?? ''));
    $shippingLatitude = filter_var($_POST['shipping_latitude'] ?? null, FILTER_VALIDATE_FLOAT);
    $shippingLongitude = filter_var($_POST['shipping_longitude'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($order['status'] !== 'selesai') {
        $error = 'Form pengiriman tersedia setelah order berstatus selesai.';
    } elseif ($shippingName === '' || $shippingPhone === '' || $shippingAddress === '' || $shippingCity === '' || $shippingPostalCode === '' || $shippingCourier === '') {
        $error = 'Lengkapi seluruh data penerima dan alamat.';
    } elseif ($shippingLatitude === false || $shippingLongitude === false || $shippingLatitude < -90 || $shippingLatitude > 90 || $shippingLongitude < -180 || $shippingLongitude > 180) {
        $error = 'Tentukan titik lokasi penerima pada peta.';
    } else {
        $stmt = $db->prepare('UPDATE orders SET shipping_required = 1, shipping_name = ?, shipping_phone = ?, shipping_address = ?, shipping_city = ?, shipping_postal_code = ?, shipping_courier = ?, shipping_cost = 0, shipping_latitude = ?, shipping_longitude = ? WHERE id = ?');
        $dpPercentage = max(0, min(100, (int) getSetting('dp_percentage', '50')));
        $stmt->execute([$shippingName, $shippingPhone, $shippingAddress, $shippingCity, $shippingPostalCode, $shippingCourier, $shippingLatitude, $shippingLongitude, $order['id']]);
        $message = 'Alamat pengiriman berhasil disimpan. Admin akan memproses pengiriman paket Anda.';
        $order['shipping_required'] = 1;
    }
}
$shippingCost = 0;
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Form Pengiriman Paket | Velocity System AI</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
:root{--navy:#0f172a;--teal:#007c83;--teal-dark:#07555d;--blue:#2563eb;--muted:#64748b;--line:#dce5e9;--paper:#f4f8fa;--white:#fff;--green:#15803d;--red:#b91c1c}*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 8% 0%,#d8f2ed,transparent 30%),linear-gradient(135deg,#f4f8fa,#edf5fb);color:var(--navy);font:15px 'DM Sans',sans-serif}h1,h2{font-family:'Space Grotesk',sans-serif}.shell{width:min(680px,calc(100% - 32px));margin:auto;padding:38px 0 60px}.brand{text-align:center;color:var(--teal-dark);font:700 19px 'Space Grotesk';letter-spacing:.04em;margin-bottom:24px}.brand i{color:#38bdf8;margin-right:8px}.card{background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:0 18px 45px rgba(15,23,42,.09);overflow:hidden}.card-head{padding:26px 30px;background:linear-gradient(135deg,var(--teal-dark),var(--teal));color:#fff}.card-head h1{margin:0 0 7px;font-size:26px}.card-head p{margin:0;color:#c9eeee;line-height:1.55}.form{padding:28px 30px}.order-pill{display:inline-flex;gap:8px;align-items:center;margin-bottom:20px;padding:8px 12px;border-radius:999px;background:#e8f7f4;color:var(--teal-dark);font-weight:700;font-size:13px}.order-pill i{color:var(--teal)}.field{margin-bottom:17px}.field label{display:block;margin-bottom:7px;font-weight:700;font-size:13px}.field input,.field textarea,.field select{width:100%;padding:12px 13px;border:1px solid var(--line);border-radius:9px;background:#fbfdfd;color:var(--navy);font:inherit}.field textarea{resize:vertical}.field input:focus,.field textarea:focus,.field select:focus{outline:0;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,124,131,.1)}.grid{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}.wide{grid-column:1/-1}.shipping-note{display:flex;gap:11px;align-items:flex-start;margin:4px 0 22px;padding:13px 14px;border-radius:10px;background:#f0fdf4;color:#166534;font-size:13px;line-height:1.5}.shipping-note i{margin-top:2px;color:#16a34a}.btn{width:100%;border:0;border-radius:9px;padding:13px 16px;background:var(--teal);color:#fff;font:700 14px 'DM Sans';cursor:pointer}.btn:hover{background:var(--teal-dark)}.alert{margin:18px 30px 0;padding:13px 14px;border-radius:9px;line-height:1.5}.success{background:#f0fdf4;color:var(--green)}.error{background:#fff1f2;color:var(--red)}.back{display:block;margin-top:18px;text-align:center;color:var(--teal-dark);font-weight:600;text-decoration:none}@media(max-width:560px){.shell{padding-top:22px}.card-head,.form{padding-left:20px;padding-right:20px}.grid{grid-template-columns:1fr}.wide{grid-column:auto}}
</style>
<style>.map-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:-2px 0 9px}.map-button{border:1px solid #b9dedd;border-radius:8px;padding:8px 10px;background:#effaf8;color:#07555d;font:600 12px 'DM Sans';cursor:pointer}.map-button:hover{background:#d9f2ed}.map-toolbar span{color:#64748b;font-size:11px;text-align:right}.shipping-map{height:260px;margin-bottom:7px;border:1px solid #cfe1e3;border-radius:12px;overflow:hidden;z-index:0}.map-hint{display:block;margin:0 0 17px;color:#64748b;font-size:12px;line-height:1.4}@media(max-width:560px){.map-toolbar{align-items:flex-start;flex-direction:column}.map-toolbar span{text-align:left}.shipping-map{height:230px}}</style>
<style>
.shell{width:min(820px,calc(100% - 32px));padding-top:48px}.brand{animation:formReveal .7s ease both}.card{border:1px solid rgba(0,124,131,.16);box-shadow:0 24px 65px rgba(15,23,42,.13)}.card-head{position:relative;padding:34px 38px;overflow:hidden}.card-head::after{content:'';position:absolute;width:220px;height:220px;right:-70px;top:-120px;border:1px solid rgba(255,255,255,.2);border-radius:50%;box-shadow:0 0 0 24px rgba(255,255,255,.05),0 0 0 48px rgba(255,255,255,.04)}.card-head h1{font-size:30px;letter-spacing:-.03em}.card-head p{max-width:560px}.form{padding:30px 38px 36px}.order-pill{margin-bottom:22px;background:#edf9f7;border:1px solid #cce9e5}.shipping-note{margin:0 0 25px;padding:14px 16px;background:linear-gradient(135deg,#effcf6,#f2fbff);border:1px solid #cde9df;color:#166534;box-shadow:0 8px 18px rgba(22,101,52,.06)}.shipping-note span{font-size:0}.shipping-note span::after{content:'Biaya pengiriman ditentukan langsung melalui aplikasi pihak ketiga.';font-size:13px}.shipping-note strong{display:none}.grid{gap:0 18px}.field{margin-bottom:19px}.field label{font-size:12px;letter-spacing:.01em}.field input,.field textarea,.field select{border-color:#d4e2e5;border-radius:10px;min-height:45px;transition:border-color .2s ease,box-shadow .2s ease,background .2s ease}.field textarea{min-height:90px}.field input:hover,.field textarea:hover,.field select:hover{background:#fff;border-color:#9fc9c7}.shipping-section-title{display:flex;align-items:center;gap:10px;margin:27px 0 16px;padding-top:23px;border-top:1px solid #e5eeee;color:var(--teal-dark);font:700 16px 'Space Grotesk'}.shipping-section-title i{width:31px;height:31px;display:grid;place-items:center;border-radius:9px;background:#dff4ef;color:var(--teal);font-size:13px}.form-progress{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:-4px 0 26px}.progress-step{display:flex;align-items:center;gap:8px;color:#84939d;font-size:11px;font-weight:700}.progress-step span{display:grid;place-items:center;width:25px;height:25px;border-radius:50%;background:#e4f4f1;color:var(--teal);font-size:11px}.progress-step.active{color:var(--teal-dark)}.progress-step:not(:last-child)::after{content:'';height:1px;flex:1;background:#d9e9e8}.btn{box-shadow:0 9px 18px rgba(0,124,131,.18);transition:transform .2s ease,box-shadow .2s ease,background .2s ease}.btn:hover{transform:translateY(-2px);box-shadow:0 13px 24px rgba(0,124,131,.24)}.form>.field,.form>.shipping-note,.form>.grid,.form>.order-pill{animation:formReveal .6s cubic-bezier(.22,1,.36,1) both}.map-toolbar{padding-top:2px}.shipping-map{box-shadow:inset 0 0 0 1px rgba(0,124,131,.06),0 10px 20px rgba(15,23,42,.07)}.map-hint{margin-bottom:0}.back{transition:color .2s ease}.back:hover{color:var(--blue)}@keyframes formReveal{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}@media(max-width:560px){.shell{padding-top:24px}.card-head,.form{padding-left:20px;padding-right:20px}.card-head h1{font-size:25px}.form-progress{gap:5px}.progress-step{font-size:10px;gap:5px}.progress-step:not(:last-child)::after{display:none}}
</style>
</head>
<body><main class="shell"><div class="brand"><i class="fa-solid fa-bolt"></i> VELOCITY SYSTEM AI</div><section class="card"><div class="card-head"><h1>Form Pengiriman Paket</h1><p>Lengkapi alamat agar tugas tulis yang sudah selesai dapat dikirim ke tujuan Anda.</p></div><?php if ($message): ?><div class="alert success"><i class="fa-solid fa-circle-check"></i> <?= $e($message) ?></div><?php endif; ?><?php if ($error): ?><div class="alert error"><i class="fa-solid fa-circle-exclamation"></i> <?= $e($error) ?></div><?php endif; ?><?php if ($order && !$message): ?><form class="form" method="post"><input type="hidden" name="order" value="<?= $e($orderCode) ?>"><div class="order-pill"><i class="fa-solid fa-box"></i> <?= $e($order['order_code']) ?> · <?= $e($order['service_title']) ?></div><div class="shipping-note"><i class="fa-solid fa-truck-fast"></i><span>Ongkir pengiriman tetap <strong><?= formatRupiah($shippingCost) ?></strong> dan akan ditambahkan ke total order.</span></div><div class="grid"><div class="field"><label for="shipping_name">Nama penerima</label><input id="shipping_name" name="shipping_name" required value="<?= $e($order['shipping_name'] ?? '') ?>"></div><div class="field"><label for="shipping_phone">Nomor penerima</label><input id="shipping_phone" name="shipping_phone" type="tel" required value="<?= $e($order['shipping_phone'] ?? '') ?>"></div><div class="field wide"><label for="shipping_address">Alamat lengkap</label><textarea id="shipping_address" name="shipping_address" rows="3" required><?= $e($order['shipping_address'] ?? '') ?></textarea></div><div class="field"><label for="shipping_city">Kota / Kabupaten</label><input id="shipping_city" name="shipping_city" required value="<?= $e($order['shipping_city'] ?? '') ?>"></div><div class="field"><label for="shipping_postal_code">Kode pos</label><input id="shipping_postal_code" name="shipping_postal_code" required value="<?= $e($order['shipping_postal_code'] ?? '') ?>"></div><div class="field wide"><label for="shipping_courier">Kurir pilihan</label><select id="shipping_courier" name="shipping_courier" required><option value="">Pilih kurir</option><?php foreach (['JNE','J&T','SiCepat','Pos Indonesia','Kurir lainnya'] as $courier): ?><option <?= ($order['shipping_courier'] ?? '') === $courier ? 'selected' : '' ?>><?= $courier ?></option><?php endforeach; ?></select></div></div><button class="btn"><i class="fa-solid fa-paper-plane"></i> Simpan alamat pengiriman</button></form><?php elseif ($order && $message): ?><div class="form"><a class="btn" href="status.php?order=<?= $e($orderCode) ?>">Kembali ke status order</a></div><?php endif; ?></section><a class="back" href="status.php?order=<?= $e($orderCode) ?>"><i class="fa-solid fa-arrow-left"></i> Kembali ke status pesanan</a></main></body></html>
<script>
const shippingForm = document.querySelector('.form');
if (shippingForm) {
    const orderPill = shippingForm.querySelector('.order-pill');
    const progress = document.createElement('div');
    progress.className = 'form-progress';
    progress.innerHTML = '<div class="progress-step active"><span>1</span> Data penerima</div><div class="progress-step active"><span>2</span> Alamat lengkap</div><div class="progress-step active"><span>3</span> Konfirmasi lokasi</div>';
    orderPill?.after(progress);
    const grid = shippingForm.querySelector('.grid');
    const detailsTitle = document.createElement('div');
    detailsTitle.className = 'shipping-section-title';
    detailsTitle.innerHTML = '<i class="fa-solid fa-address-card"></i> Detail penerima dan alamat';
    grid?.before(detailsTitle);
}
const addressField = document.getElementById('shipping_address');
if (addressField) {
    const mapField = addressField.closest('.field');
    const mapControls = document.createElement('div');
    mapControls.className = 'field wide map-field';
    mapControls.innerHTML = '<div class="shipping-section-title"><i class="fa-solid fa-map-location-dot"></i> Pastikan titik lokasi</div><label>Titik lokasi pengiriman</label><div class="map-toolbar"><button type="button" id="locateButton" class="map-button"><i class="fa-solid fa-location-crosshairs"></i> Gunakan lokasi saya</button><span id="coordinatesHint">Klik peta atau geser pin ke lokasi rumah.</span></div><div id="shippingMap" class="shipping-map"></div><small class="map-hint">Titik lokasi membantu kurir menemukan alamat dengan lebih akurat.</small>';
    const latitude = document.createElement('input');
    const longitude = document.createElement('input');
    latitude.type = longitude.type = 'hidden';
    latitude.name = 'shipping_latitude';
    longitude.name = 'shipping_longitude';
    latitude.id = 'shipping_latitude';
    longitude.id = 'shipping_longitude';
    latitude.value = '<?= $e($order['shipping_latitude'] ?? '') ?>';
    longitude.value = '<?= $e($order['shipping_longitude'] ?? '') ?>';
    mapControls.append(latitude, longitude);
    addressField.parentElement.after(mapControls);
    const leafletScript = document.createElement('script');
    leafletScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    leafletScript.onload = () => {
        const initial = latitude.value && longitude.value ? [Number(latitude.value), Number(longitude.value)] : [-2.5489, 118.0149];
        const map = L.map('shippingMap').setView(initial, latitude.value ? 16 : 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        const marker = L.marker(initial, { draggable: true }).addTo(map);
        const updateCoordinates = (position) => {
            const point = position.latlng || position;
            latitude.value = point.lat.toFixed(7);
            longitude.value = point.lng.toFixed(7);
            document.getElementById('coordinatesHint').textContent = `Lokasi dipilih: ${latitude.value}, ${longitude.value}`;
        };
        if (latitude.value) updateCoordinates({ lat: Number(latitude.value), lng: Number(longitude.value) });
        map.on('click', (event) => { marker.setLatLng(event.latlng); updateCoordinates(event); });
        marker.on('dragend', updateCoordinates);
        document.getElementById('locateButton').addEventListener('click', () => map.locate({ setView: true, maxZoom: 17 }));
        map.on('locationfound', (event) => { marker.setLatLng(event.latlng); updateCoordinates(event); });
        map.on('locationerror', () => { document.getElementById('coordinatesHint').textContent = 'Lokasi perangkat tidak tersedia. Pilih titik langsung di peta.'; });
        setTimeout(() => map.invalidateSize(), 150);
    };
    document.body.appendChild(leafletScript);
}
const phoneField = document.getElementById('shipping_phone');
if (phoneField) {
    phoneField.placeholder = 'Contoh: 08123456789';
    phoneField.inputMode = 'tel';
    phoneField.autocomplete = 'tel';
    const phoneLabel = document.querySelector('label[for="shipping_phone"]');
    if (phoneLabel) phoneLabel.textContent = 'Nomor WhatsApp penerima';
    const hint = document.createElement('small');
    hint.textContent = 'Gunakan nomor WhatsApp yang aktif untuk dihubungi kurir.';
    hint.style.cssText = 'display:block;margin-top:6px;color:#64748b;font-size:12px;line-height:1.4';
    phoneField.after(hint);
}
</script>
