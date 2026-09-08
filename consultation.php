<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$websiteTypes = [
    'Landing page',
    'Website bisnis / company profile',
    'Toko online',
    'Aplikasi web / dashboard',
    'Website portfolio',
    'Lainnya',
];
$featureOptions = [
    'Form kontak / WhatsApp',
    'Login dan register pengguna',
    'Dashboard admin',
    'Katalog atau manajemen produk',
    'Pembayaran online',
    'Booking atau pemesanan',
    'Integrasi API',
    'SEO dan Google Analytics',
];
$submitted = [];
$whatsappUrl = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'whatsapp' => trim((string) ($_POST['whatsapp'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'website_type' => trim((string) ($_POST['website_type'] ?? '')),
        'purpose' => trim((string) ($_POST['purpose'] ?? '')),
        'pages' => trim((string) ($_POST['pages'] ?? '')),
        'design' => trim((string) ($_POST['design'] ?? '')),
        'content' => trim((string) ($_POST['content'] ?? '')),
        'domain_hosting' => trim((string) ($_POST['domain_hosting'] ?? '')),
        'timeline' => trim((string) ($_POST['timeline'] ?? '')),
        'budget' => trim((string) ($_POST['budget'] ?? '')),
        'reference' => trim((string) ($_POST['reference'] ?? '')),
        'notes' => trim((string) ($_POST['notes'] ?? '')),
    ];
    $features = array_values(array_intersect($featureOptions, (array) ($_POST['features'] ?? [])));
    $submitted['features'] = $features;

    if ($submitted['name'] === '' || $submitted['whatsapp'] === '' || $submitted['website_type'] === '' || $submitted['purpose'] === '') {
        $error = 'Nama, WhatsApp, tipe website, dan tujuan website wajib diisi.';
    } elseif (!in_array($submitted['website_type'], $websiteTypes, true)) {
        $error = 'Tipe website tidak valid.';
    } elseif ($submitted['email'] !== '' && !filter_var($submitted['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (mb_strlen($submitted['name']) > 100 || mb_strlen($submitted['whatsapp']) > 30 || mb_strlen($submitted['purpose']) > 1000) {
        $error = 'Beberapa isian terlalu panjang. Mohon ringkas kembali.';
    }

    if ($error === '') {
        $featureText = $features ? implode(', ', $features) : 'Belum ditentukan';
        $message = implode("\n", [
            'Halo Admin Velocity System AI,',
            '',
          'Saya ingin mengajukan konsultasi pembuatan website.',
            '',
          'BRIEF PEMBUATAN WEBSITE',
          '========================',
          '',
          'DATA KONTAK',
          '- Nama: ' . $submitted['name'],
          '- WhatsApp: ' . $submitted['whatsapp'],
          '- Email: ' . ($submitted['email'] ?: 'Belum diisi'),
            '',
          'DETAIL WEBSITE',
          '- Tipe: ' . $submitted['website_type'],
          '- Tujuan: ' . $submitted['purpose'],
          '- Halaman: ' . ($submitted['pages'] ?: 'Belum ditentukan'),
          '- Fitur: ' . $featureText,
          '',
          'DESAIN DAN TEKNIS',
          '- Gaya desain: ' . ($submitted['design'] ?: 'Belum ditentukan'),
          '- Kesiapan konten: ' . ($submitted['content'] ?: 'Belum ditentukan'),
          '- Domain/hosting: ' . ($submitted['domain_hosting'] ?: 'Belum ditentukan'),
          '- Referensi: ' . ($submitted['reference'] ?: 'Tidak ada'),
          '',
          'WAKTU DAN ANGGARAN',
          '- Target selesai: ' . ($submitted['timeline'] ?: 'Belum ditentukan'),
          '- Budget: ' . ($submitted['budget'] ?: 'Belum ditentukan'),
          '- Catatan: ' . ($submitted['notes'] ?: 'Tidak ada'),
            '',
            'Mohon dibantu dengan rekomendasi teknologi, estimasi waktu, dan penawaran harga. Terima kasih.',
        ]);
        $whatsappUrl = 'https://wa.me/' . formatWhatsAppNumber(getSetting('whatsapp', WA_NUMBER)) . '?text=' . rawurlencode($message);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Konsultasi Pembuatan Website | Velocity System AI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<style>
:root{--ink:#10212b;--muted:#657782;--teal:#007c83;--teal-dark:#07555d;--line:#dce7e9;--paper:#f4f8f7;--white:#fff;--danger:#b42318;--soft:#e7f5f1}*{box-sizing:border-box}body{margin:0;background:linear-gradient(135deg,#edf8f5,#f7f9fb);color:var(--ink);font:15px 'DM Sans',sans-serif}h1,h2,h3{font-family:'Space Grotesk',sans-serif}.page{width:min(920px,calc(100% - 32px));margin:auto;padding:34px 0 60px}.brand{text-align:center;color:var(--teal-dark);font:700 18px 'Space Grotesk';letter-spacing:.04em;margin-bottom:26px}.card{background:var(--white);border:1px solid var(--line);border-radius:14px;padding:30px;box-shadow:0 18px 45px rgba(16,33,43,.08)}.intro{text-align:center;margin-bottom:26px}.intro h1{margin:0 0 10px;font-size:30px}.intro p{max-width:650px;margin:0 auto;color:var(--muted);line-height:1.6}.section{padding-top:24px;margin-top:24px;border-top:1px solid var(--line)}.section h2{font-size:19px;margin:0 0 17px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.field{display:flex;flex-direction:column;gap:7px}.field.full{grid-column:1/-1}label{font-weight:700;font-size:13px}input,select,textarea{width:100%;border:1px solid var(--line);border-radius:8px;padding:12px 13px;color:var(--ink);font:inherit;background:#fff}textarea{min-height:92px;resize:vertical}input:focus,select:focus,textarea:focus{outline:2px solid #b9e4dc;border-color:var(--teal)}.hint{font-size:12px;color:var(--muted);font-weight:400}.checks{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}.check{display:flex;align-items:center;gap:9px;padding:11px 12px;border:1px solid var(--line);border-radius:8px;font-weight:500}.check input{width:auto;accent-color:var(--teal)}.actions{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-top:28px}.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;padding:13px 18px;background:var(--teal);color:#fff;font:700 14px 'DM Sans';text-decoration:none;cursor:pointer}.btn:hover{background:var(--teal-dark)}.btn.secondary{background:var(--soft);color:var(--teal-dark)}.alert{padding:13px 15px;border-radius:8px;background:#fff1f0;color:var(--danger);margin-bottom:18px}.success{background:#f0faf7}.summary{background:#f8fbfa;border:1px solid var(--line);border-radius:10px;padding:20px;line-height:1.7;white-space:pre-line}.summary h2{margin:0 0 12px;font-size:20px}.foot{text-align:center;color:var(--muted);font-size:13px;margin-top:20px}.foot a{color:var(--teal);font-weight:700}@media(max-width:620px){.page{width:min(100% - 22px,920px);padding-top:20px}.card{padding:21px}.grid,.checks{grid-template-columns:1fr}.field.full{grid-column:auto}.actions{align-items:stretch;flex-direction:column-reverse}.btn{width:100%}.intro h1{font-size:25px}}
</style>
<style>
:root{--ink:#102b3a;--muted:#607985;--teal:#087f83;--teal-dark:#07545d;--line:#d9e8e8;--paper:#edf8f6;--white:#fff;--soft:#e7f6f1;--glow:rgba(8,127,131,.16)}
html{scroll-behavior:smooth}
body{position:relative;min-height:100vh;overflow-x:hidden;background:#eaf7f4;background-image:radial-gradient(circle at 8% 8%,rgba(112,218,199,.3),transparent 27rem),radial-gradient(circle at 92% 12%,rgba(76,145,219,.18),transparent 25rem),linear-gradient(135deg,#eefbf8 0%,#f7fafc 52%,#eaf4f7 100%)}
body:before,body:after{content:"";position:fixed;z-index:-1;border:1px solid rgba(8,127,131,.12);border-radius:50%;pointer-events:none}
body:before{width:420px;height:420px;left:-250px;bottom:-170px;box-shadow:0 0 0 32px rgba(8,127,131,.035),0 0 0 64px rgba(8,127,131,.025)}
body:after{width:300px;height:300px;right:-180px;top:170px;box-shadow:0 0 0 24px rgba(76,145,219,.04),0 0 0 48px rgba(76,145,219,.025)}
.page{position:relative;padding:42px 0 72px}
.brand{display:inline-flex;position:relative;left:50%;transform:translateX(-50%);align-items:center;gap:10px;padding:9px 16px;margin-bottom:25px;border:1px solid rgba(8,127,131,.16);border-radius:999px;background:rgba(255,255,255,.58);box-shadow:0 8px 24px rgba(8,127,131,.08);animation:rise .65s ease both}
.brand:before{content:"";width:7px;height:7px;border-radius:50%;background:#38bda9;box-shadow:0 0 0 5px rgba(56,189,169,.14)}
.intro{animation:rise .7s .08s ease both}
.intro h1{letter-spacing:-.03em;color:var(--ink);font-size:clamp(27px,4vw,39px)}
.intro p{font-size:16px;max-width:700px}
.card{position:relative;overflow:hidden;border:1px solid rgba(174,211,211,.65);border-radius:20px;background:rgba(255,255,255,.86);box-shadow:0 25px 70px rgba(20,69,78,.1);backdrop-filter:blur(12px);animation:cardIn .75s .16s ease both}
.card:before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:linear-gradient(90deg,#087f83,#56cbb5,#5b9ddd,#087f83);background-size:220% 100%;animation:gradientMove 8s linear infinite}
.section{position:relative;padding:27px 0 0;margin-top:27px}
.section:before{content:"";position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,rgba(8,127,131,.28),rgba(8,127,131,.04),transparent)}
.section h2{display:flex;align-items:center;gap:10px;color:var(--teal-dark);font-size:20px;margin-bottom:19px}
.section h2:before{content:"";width:9px;height:9px;border-radius:3px;background:#56cbb5;box-shadow:0 0 0 5px rgba(86,203,181,.13);transform:rotate(45deg)}
.field{animation:fieldIn .55s ease both}
.field:nth-child(2){animation-delay:.06s}.field:nth-child(3){animation-delay:.12s}.field:nth-child(4){animation-delay:.18s}
label{color:#173746}
input,select,textarea{border-color:#d5e5e6;border-radius:10px;background:rgba(255,255,255,.82);transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease}
input:hover,select:hover,textarea:hover{border-color:#9bcac7}
input:focus,select:focus,textarea:focus{transform:translateY(-1px);box-shadow:0 7px 20px rgba(8,127,131,.12)}
.checks{gap:11px}.check{border-color:#d7e7e7;background:rgba(248,252,251,.72);transition:transform .2s ease,border-color .2s ease,background .2s ease,box-shadow .2s ease}.check:hover{transform:translateY(-2px);border-color:#83c3bb;background:#fff;box-shadow:0 8px 18px rgba(8,127,131,.1)}
.actions{padding-top:4px}.btn{border-radius:10px;box-shadow:0 8px 18px rgba(8,127,131,.18);transition:transform .2s ease,box-shadow .2s ease,background .2s ease}.btn:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(8,127,131,.24)}.btn.secondary{box-shadow:none}
.alert{border:1px solid rgba(180,35,24,.12);box-shadow:0 8px 20px rgba(180,35,24,.06)}.alert.success{border-color:rgba(8,127,131,.16);background:#effaf7;color:var(--teal-dark)}
.summary{border-color:#d5e8e5;background:linear-gradient(135deg,#f8fcfb,#eef8f5);box-shadow:inset 4px 0 0 #56cbb5;line-height:1.8}
.summary h2{color:var(--teal-dark)}
.foot{animation:rise .8s .3s ease both}
@keyframes rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
@keyframes cardIn{from{opacity:0;transform:translateY(24px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes fieldIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
@keyframes gradientMove{to{background-position:220% 0}}
@media(max-width:620px){.page{padding:25px 0 48px}.card{border-radius:16px}.section h2{font-size:18px}.intro p{font-size:14px}.brand{font-size:15px}}
@media(prefers-reduced-motion:reduce){*,*:before,*:after{animation-duration:.01ms!important;animation-iteration-count:1!important;scroll-behavior:auto!important;transition-duration:.01ms!important}}
/* Stronger selection controls keep the required choices visually obvious. */
.field:has(select){position:relative}
.field:has(select) label:after{content:'PILIHAN';display:inline-block;margin-left:8px;padding:3px 7px;border-radius:999px;background:#dff5ef;color:#087f83;font-size:9px;letter-spacing:.08em;vertical-align:middle}
select{appearance:none;min-height:49px;padding-right:48px;border:2px solid #a8d8d1;background-color:#f7fcfb;background-image:linear-gradient(45deg,transparent 50%,#087f83 50%),linear-gradient(135deg,#087f83 50%,transparent 50%);background-position:calc(100% - 21px) 21px,calc(100% - 15px) 21px;background-size:6px 6px,6px 6px;background-repeat:no-repeat;font-weight:600;cursor:pointer}
select:required:invalid{color:#71858d;background-color:#f4fbf9}
select:focus{border-color:#087f83;background-color:#fff;box-shadow:0 0 0 4px rgba(8,127,131,.13),0 10px 24px rgba(8,127,131,.12)}
select option{color:var(--ink);background:#fff;font-weight:500}
.field:has(select):after{content:"";position:absolute;left:0;right:0;bottom:-5px;height:2px;border-radius:2px;background:linear-gradient(90deg,#56cbb5,transparent);opacity:.55;pointer-events:none}
.check:has(input:checked){border-color:#42ae9f;background:linear-gradient(135deg,#effbf8,#e2f5f0);color:#07545d;box-shadow:0 8px 20px rgba(8,127,131,.12);transform:translateY(-2px)}
.check input{width:17px;height:17px}
.actions .btn:not(.secondary){background:linear-gradient(110deg,#087f83,#0a9b91,#087f83);background-size:200% 100%;animation:buttonShimmer 5s ease-in-out infinite}
@keyframes buttonShimmer{0%,100%{background-position:0 0}50%{background-position:100% 0}}
@media(prefers-reduced-motion:reduce){.actions .btn:not(.secondary){animation:none}}
body{background-image:linear-gradient(rgba(8,127,131,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(8,127,131,.035) 1px,transparent 1px),radial-gradient(circle at 8% 8%,rgba(112,218,199,.3),transparent 27rem),radial-gradient(circle at 92% 12%,rgba(76,145,219,.18),transparent 25rem),linear-gradient(135deg,#eefbf8 0%,#f7fafc 52%,#eaf4f7 100%);background-size:34px 34px,34px 34px,auto,auto,auto}
.card{isolation:isolate}
.card:after{content:"";position:absolute;inset:4px;border:1px solid rgba(255,255,255,.7);border-radius:16px;pointer-events:none;z-index:-1}
.card:hover{box-shadow:0 30px 80px rgba(20,69,78,.15)}
.intro h1{background:linear-gradient(100deg,#102b3a 12%,#087f83 48%,#102b3a 86%);background-size:180% auto;background-clip:text;-webkit-background-clip:text;color:transparent;animation:titleShift 7s ease-in-out infinite}
.intro p{letter-spacing:.01em}
.card>section{animation:sectionReveal .65s ease both}
.card>section:nth-child(2){animation-delay:.12s}.card>section:nth-child(3){animation-delay:.2s}.card>section:nth-child(4){animation-delay:.28s}.card>section:nth-child(5){animation-delay:.36s}
.section h2{position:relative}
.section h2:after{content:"";width:42px;height:2px;margin-left:auto;border-radius:2px;background:linear-gradient(90deg,#56cbb5,transparent);opacity:.7}
.field:has(select) select{box-shadow:0 5px 0 rgba(8,127,131,.06)}
.field:has(select) select:hover{transform:translateY(-2px);box-shadow:0 8px 0 rgba(8,127,131,.08)}
.field:has(select) select:focus{transform:translateY(-2px)}
.check{position:relative;overflow:hidden}.check:after{content:"";position:absolute;width:70px;height:70px;right:-35px;top:-35px;border-radius:50%;background:rgba(86,203,181,.1);transition:transform .3s ease}.check:hover:after,.check:has(input:checked):after{transform:scale(1.5)}
@keyframes titleShift{0%,100%{background-position:0% center}50%{background-position:100% center}}
@keyframes sectionReveal{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@media(prefers-reduced-motion:reduce){.intro h1{animation:none;background-position:0 center}.card>section{animation:none}}
</style>
</head>
<body>
<main class="page">
  <div class="intro"><h1>Brief Pembuatan Website</h1><p>Isi kebutuhan website Anda sedetail mungkin agar tim kami dapat menyiapkan rekomendasi fitur, teknologi, estimasi waktu, dan harga yang sesuai.</p></div>
  <?php if ($error): ?><div class="alert"><?= $e($error) ?></div><?php endif; ?>
  <?php if ($whatsappUrl): ?>
    <section class="card">
      <div class="alert success">Brief berhasil dibuat. Periksa kembali ringkasannya sebelum dikirim kepada admin.</div>
      <div class="summary"><h2>Ringkasan Konsultasi</h2><?php
        echo $e("DATA KONTAK\nNama: {$submitted['name']}\nWhatsApp: {$submitted['whatsapp']}\nEmail: " . ($submitted['email'] ?: 'Belum diisi') . "\n\nDETAIL WEBSITE\nTipe: {$submitted['website_type']}\nTujuan: {$submitted['purpose']}\nHalaman: " . ($submitted['pages'] ?: 'Belum ditentukan') . "\nFitur: " . ($submitted['features'] ? implode(', ', $submitted['features']) : 'Belum ditentukan') . "\n\nDESAIN DAN TEKNIS\nGaya desain: " . ($submitted['design'] ?: 'Belum ditentukan') . "\nKesiapan konten: " . ($submitted['content'] ?: 'Belum ditentukan') . "\nDomain/hosting: " . ($submitted['domain_hosting'] ?: 'Belum ditentukan') . "\nReferensi: " . ($submitted['reference'] ?: 'Tidak ada') . "\n\nWAKTU DAN ANGGARAN\nTarget selesai: " . ($submitted['timeline'] ?: 'Belum ditentukan') . "\nBudget: " . ($submitted['budget'] ?: 'Belum ditentukan') . "\nCatatan: " . ($submitted['notes'] ?: 'Tidak ada'));
      ?></div>
      <div class="actions"><a class="btn secondary" href="consultation.php">Isi ulang</a><a class="btn" href="<?= $e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer">Kirim Brief ke WhatsApp</a></div>
    </section>
  <?php else: ?>
    <form class="card" method="post">
      <section><h2>1. Data Kontak</h2><div class="grid"><div class="field"><label for="name">Nama lengkap *</label><input id="name" name="name" required value="<?= $e($submitted['name'] ?? '') ?>"></div><div class="field"><label for="whatsapp">Nomor WhatsApp *</label><input id="whatsapp" name="whatsapp" type="tel" required placeholder="Contoh: 08123456789" value="<?= $e($submitted['whatsapp'] ?? '') ?>"></div><div class="field full"><label for="email">Email <span class="hint">(opsional)</span></label><input id="email" name="email" type="email" value="<?= $e($submitted['email'] ?? '') ?>"></div></div></section>
      <section class="section"><h2>2. Konsep Website</h2><div class="grid"><div class="field"><label for="website_type">Tipe website *</label><select id="website_type" name="website_type" required><option value="">Pilih tipe website</option><?php foreach ($websiteTypes as $type): ?><option value="<?= $e($type) ?>" <?= ($submitted['website_type'] ?? '') === $type ? 'selected' : '' ?>><?= $e($type) ?></option><?php endforeach; ?></select></div><div class="field"><label for="pages">Halaman yang dibutuhkan</label><input id="pages" name="pages" placeholder="Contoh: Beranda, Tentang, Layanan, Kontak" value="<?= $e($submitted['pages'] ?? '') ?>"></div><div class="field full"><label for="purpose">Tujuan utama website *</label><textarea id="purpose" name="purpose" required placeholder="Contoh: memperkenalkan bisnis dan menerima calon pelanggan..."><?= $e($submitted['purpose'] ?? '') ?></textarea></div></div></section>
      <section class="section"><h2>3. Fitur Website</h2><div class="checks"><?php foreach ($featureOptions as $feature): ?><label class="check"><input type="checkbox" name="features[]" value="<?= $e($feature) ?>" <?= in_array($feature, $submitted['features'] ?? [], true) ? 'checked' : '' ?>> <?= $e($feature) ?></label><?php endforeach; ?></div></section>
      <section class="section"><h2>4. Desain dan Kebutuhan Teknis</h2><div class="grid"><div class="field"><label for="design">Gaya desain</label><input id="design" name="design" placeholder="Contoh: modern, minimalis, profesional" value="<?= $e($submitted['design'] ?? '') ?>"></div><div class="field"><label for="content">Kesiapan konten</label><select id="content" name="content"><option value="">Pilih status konten</option><?php foreach (['Konten sudah siap','Sebagian sudah siap','Belum siap, perlu dibantu'] as $content): ?><option value="<?= $e($content) ?>" <?= ($submitted['content'] ?? '') === $content ? 'selected' : '' ?>><?= $e($content) ?></option><?php endforeach; ?></select></div><div class="field"><label for="domain_hosting">Domain dan hosting</label><select id="domain_hosting" name="domain_hosting"><option value="">Pilih kebutuhan</option><?php foreach (['Sudah tersedia','Belum tersedia, perlu rekomendasi','Belum memahami kebutuhan'] as $hosting): ?><option value="<?= $e($hosting) ?>" <?= ($submitted['domain_hosting'] ?? '') === $hosting ? 'selected' : '' ?>><?= $e($hosting) ?></option><?php endforeach; ?></select></div><div class="field"><label for="reference">Referensi website</label><input id="reference" name="reference" placeholder="Link atau deskripsi website referensi" value="<?= $e($submitted['reference'] ?? '') ?>"></div></div></section>
      <section class="section"><h2>5. Waktu dan Anggaran</h2><div class="grid"><div class="field"><label for="timeline">Target selesai</label><input id="timeline" name="timeline" placeholder="Contoh: 30 September 2026" value="<?= $e($submitted['timeline'] ?? '') ?>"></div><div class="field"><label for="budget">Perkiraan budget</label><input id="budget" name="budget" placeholder="Contoh: Rp 3.000.000 - Rp 5.000.000" value="<?= $e($submitted['budget'] ?? '') ?>"></div><div class="field full"><label for="notes">Catatan tambahan</label><textarea id="notes" name="notes" placeholder="Tuliskan kebutuhan lain, integrasi, atau pertanyaan Anda..."><?= $e($submitted['notes'] ?? '') ?></textarea></div></div></section>
      <div class="actions"><a class="btn secondary" href="index.php">Kembali ke website</a><button class="btn" type="submit">Buat Ringkasan Konsultasi</button></div>
    </form>
  <?php endif; ?>
  <p class="foot">Data digunakan untuk memahami kebutuhan website Anda.<br><a href="index.php">Kembali ke Velocity System AI</a></p>
</main>
<script>
const timelineField = document.querySelector('input[name="timeline"]');
if (timelineField) {
  timelineField.type = 'date';
  timelineField.min = new Date().toISOString().split('T')[0];
  timelineField.title = 'Pilih tanggal target selesai dari kalender';
}
</script>
</body>
</html>
