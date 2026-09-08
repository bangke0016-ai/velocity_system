<?php
$serviceCatalog = [];
$serviceStmt = Database::getInstance()->getConnection()->query(
  'SELECT s.id, s.title, s.price, s.price_max, s.unit, s.icon, c.name AS category_name, c.slug AS category_slug FROM services s JOIN categories c ON c.id = s.category_id WHERE s.is_active = 1 AND c.is_active = 1 ORDER BY c.sort_order, s.sort_order, s.title'
);
$additionalServices = [];
$activeCategories = Database::getInstance()->getConnection()->query("SELECT c.id, c.slug, c.name, c.icon FROM categories c WHERE c.is_active = 1 AND c.slug <> 'web_dev' ORDER BY c.sort_order, c.name")->fetchAll();
$publicCategorySlugs = ['tulis_tangan', 'ketik', 'desain', 'edit', 'paketan'];
$categoryTabMap = ['tulis_tangan' => 'tulis', 'ketik' => 'ketik', 'desain' => 'desain', 'edit' => 'edit', 'paketan' => 'paketan'];
$newCategories = [];
foreach ($serviceStmt->fetchAll() as $service) {
  $serviceCatalog[$service['category_name']][] = $service;
  if ((int) $service['id'] > 22) {
    $additionalServices[] = $service;
  }
}
foreach ($activeCategories as $category) {
  if (!in_array($category['slug'], $publicCategorySlugs, true)) {
    $newCategories[] = $category;
  }
}
$testimonialDirectory = __DIR__ . '/../assets/testimonials';
$testimonialFiles = is_dir($testimonialDirectory)
  ? glob($testimonialDirectory . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE)
  : [];
$testimonialSlides = array_map(static function ($file) {
  $fileName = basename($file);
  return [
    'src' => 'assets/testimonials/' . rawurlencode($fileName),
    'title' => 'Testimoni pelanggan',
    'subtitle' => 'Bukti kepuasan pelanggan'
  ];
}, $testimonialFiles);
if (!$testimonialSlides) {
  $testimonialSlides = [
    ['src' => 'assets/hero/slide-02.jpg', 'title' => 'Dokumentasi layanan', 'subtitle' => 'Tambahkan foto testimoni ke assets/testimonials'],
    ['src' => 'assets/hero/slide-03.jpg', 'title' => 'Dokumentasi layanan', 'subtitle' => 'Tambahkan foto testimoni ke assets/testimonials'],
    ['src' => 'assets/hero/slide-01.jpg', 'title' => 'Dokumentasi layanan', 'subtitle' => 'Tambahkan foto testimoni ke assets/testimonials']
  ];
}
$siteName = getSetting('site_name', 'VELOCITY SYSTEM AI');
$siteTagline = getSetting('site_tagline', 'Jasa Joki Tugas Profesional & Terpercaya');
$siteDescription = getSetting('site_description', 'Platform jasa tugas akademik profesional dan terpercaya untuk pelajar dan mahasiswa di seluruh Indonesia.');
$whatsappNumber = getSetting('whatsapp', '6281945864645');
$whatsappDisplay = getSetting('whatsapp_display', '0819 - 4586 - 4645');
$contactEmail = getSetting('email', 'info@velocitysystem.ai');
$location = getSetting('location', 'Maiwa, Kab. Enrekang, Sulawesi Selatan');
$copyrightYear = getSetting('copyright_year', '2024');
$paymentMethods = array_values(array_filter(array_map('trim', explode(',', getSetting('payment_methods', 'QRIS, Bank Transfer, E-Wallet, DANA, GoPay, OVO')))));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VELOCITY SYSTEM AI — Jasa Joki Tugas Profesional</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ============================================
   CSS CUSTOM PROPERTIES
   ============================================ */
:root {
  --navy: #0F172A;
  --royal: #1E3A8A;
  --electric: #2563EB;
  --sky: #38BDF8;
  --light-blue: #DBEAFE;
  --soft-gray: #F1F5F9;
  --page-bg: #F8FAFC;
  --white: #FFFFFF;
  --text-dark: #1E293B;
  --text-muted: #64748B;
  --border: #E2E8F0;
  --success: #22C55E;
  --wa-green: #25D366;
  --transition: all 0.3s ease;
  --radius-sm: 10px;
}
* { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
  margin: 0;
  background: var(--page-bg);
  color: var(--text-dark);
  font-family: 'Inter', sans-serif;
}
a { color: inherit; text-decoration: none; }
button, input, select, textarea { font: inherit; }
.container { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
.section-padding { padding: 90px 0; }
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
  background: rgba(15,23,42,0.94);
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}
.header-inner { display: flex; align-items: center; justify-content: space-between; height: 72px; }
.logo { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.2rem; color: var(--white); letter-spacing: 0.5px; }
.logo span { color: var(--sky); font-weight: 500; }
.logo-icon { color: var(--sky); }
.nav-links { display: flex; align-items: center; gap: 28px; color: #CBD5E1; font-size: 0.9rem; font-weight: 600; }
.nav-links a:hover { color: var(--white); }
.mobile-toggle { display: none; border: 0; background: transparent; color: var(--white); cursor: pointer; padding: 10px; font-size: 1.2rem; }
.hero {
  background: linear-gradient(135deg, var(--navy), #0C1E4A 40%, var(--royal));
  min-height: 100vh;
  display: flex;
  align-items: center;
  padding-top: 72px;
  position: relative;
  overflow: hidden;
}
.hero-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; z-index: 2; }
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(37,99,235,0.2);
  border: 1px solid rgba(56,189,248,0.3);
  padding: 6px 16px;
  border-radius: 50px;
  color: var(--sky);
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 24px;
  animation: fadeInUp 0.6s ease;
}
.hero-title {
  font-size: 3.2rem;
  font-weight: 900;
  color: var(--white);
  line-height: 1.15;
  margin-bottom: 20px;
  text-shadow: 0 10px 28px rgba(2,12,40,0.28);
  animation: fadeInUp 0.6s ease 0.1s both;
}
.hero-title .highlight {
  background: linear-gradient(135deg, var(--sky), var(--electric));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-subtitle {
  font-size: 1.1rem;
  color: #94A3B8;
  line-height: 1.7;
  margin-bottom: 36px;
  max-width: 520px;
  animation: fadeInUp 0.6s ease 0.2s both;
}
.hero-buttons {
  display: flex;
  gap: 16px;
  margin-bottom: 40px;
  animation: fadeInUp 0.6s ease 0.3s both;
}
.btn-primary {
  background: linear-gradient(135deg, var(--electric), #3B82F6);
  color: var(--white);
  padding: 14px 32px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: inherit;
  box-shadow: 0 4px 15px rgba(37,99,235,0.4);
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(37,99,235,0.5);
}
.btn-outline {
  background: transparent;
  color: var(--white);
  padding: 14px 32px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  border: 2px solid rgba(255,255,255,0.3);
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: inherit;
}
.btn-outline:hover {
  border-color: var(--white);
  background: rgba(255,255,255,0.05);
}
.hero-trust {
  display: flex;
  gap: 28px;
  animation: fadeInUp 0.6s ease 0.4s both;
}
.trust-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #94A3B8;
  font-size: 0.85rem;
  font-weight: 500;
}
.trust-item i {
  color: var(--success);
  font-size: 0.9rem;
}
.hero-illustration {
  min-height: 440px;
  padding: 24px 0;
  position: relative;
  overflow: hidden;
  border-radius: 26px;
  animation: fadeIn 1s ease 0.5s both;
}
.hero-carousel {
  position: absolute;
  inset: 24px 0;
  overflow: hidden;
  border-radius: 24px;
  box-shadow: 0 24px 55px rgba(2, 12, 40, 0.35);
  transform: rotate(2deg);
  touch-action: pan-y;
  user-select: none;
}
.hero-carousel::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(145deg, rgba(15,23,42,0.02), rgba(15,23,42,0.12));
  pointer-events: none;
}
.hero-slides {
  display: flex;
  height: 100%;
  transition: transform 0.8s cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}
.hero-slide {
  min-width: 100%;
  height: 100%;
  background-position: center;
  background-repeat: no-repeat;
  background-size: contain;
  background-color: #0B327C;
  transform: scale(1.02);
  transition: transform 1.2s ease;
}
.hero-slide.is-active { transform: scale(1); }
.hero-slide:nth-child(1) {
  background-image: url('assets/hero/slide-01.jpg');
}
.hero-slide:nth-child(2) {
  background-image: url('assets/hero/slide-02.jpg');
}
.hero-slide:nth-child(3) {
  background-image: url('assets/hero/slide-03.jpg');
}
.hero-slide:nth-child(4) {
  background-image: url('assets/hero/slide-04.jpg');
}
.hero-slide:nth-child(5) {
  background-image: url('assets/hero/slide-05.jpg');
}
.hero-slide:nth-child(6) {
  background-image: url('assets/hero/slide-06.jpg');
}
.hero-slide:nth-child(7) {
  background-image: url('assets/hero/slide-07.jpg');
}
.hero-slide-caption {
  position: absolute;
  right: 28px;
  bottom: 42px;
  max-width: 190px;
  padding: 12px 14px;
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 12px;
  background: rgba(15,23,42,0.62);
  color: var(--white);
  font-size: 0.78rem;
  font-weight: 600;
  line-height: 1.45;
  backdrop-filter: blur(10px);
  z-index: 1;
}
.hero-carousel-controls {
  position: absolute;
  right: 26px;
  top: 42px;
  z-index: 2;
  display: flex;
  gap: 8px;
}
.carousel-arrow {
  width: 38px;
  height: 38px;
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  background: rgba(15,23,42,0.5);
  color: var(--white);
  cursor: pointer;
  backdrop-filter: blur(8px);
  transition: var(--transition);
}
.carousel-arrow:hover { background: var(--electric); transform: translateY(-2px); }
.hero-carousel-dots {
  position: absolute;
  left: 28px;
  bottom: 47px;
  z-index: 2;
  display: flex;
  gap: 7px;
}
.carousel-dot {
  width: 8px;
  height: 8px;
  padding: 0;
  border: 0;
  border-radius: 20px;
  background: rgba(255,255,255,0.5);
  cursor: pointer;
  transition: width 0.35s ease, background 0.35s ease;
}
.carousel-dot.active { width: 25px; background: var(--sky); }

/* ============================================
   MOTION & SCROLL REVEALS
   ============================================ */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(28px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
@keyframes scrollReveal {
  from {
    opacity: 0;
    transform: perspective(900px) translateY(34px) rotateX(7deg);
    filter: blur(3px);
  }
  to {
    opacity: 1;
    transform: perspective(900px) translateY(0) rotateX(0);
    filter: blur(0);
  }
}
.animate-on-scroll {
  opacity: 0;
  transform: perspective(900px) translateY(34px) rotateX(7deg);
  transform-origin: center bottom;
  will-change: opacity, transform, filter;
}
.animate-on-scroll.visible {
  animation: scrollReveal 0.75s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.services-grid .animate-on-scroll:nth-child(2),
.steps-grid .animate-on-scroll:nth-child(2),
.terms-grid .animate-on-scroll:nth-child(2) { animation-delay: 0.08s; }
.services-grid .animate-on-scroll:nth-child(3),
.steps-grid .animate-on-scroll:nth-child(3),
.terms-grid .animate-on-scroll:nth-child(3) { animation-delay: 0.16s; }
.services-grid .animate-on-scroll:nth-child(4),
.steps-grid .animate-on-scroll:nth-child(4),
.terms-grid .animate-on-scroll:nth-child(4) { animation-delay: 0.24s; }
.terms-grid .animate-on-scroll:nth-child(5) { animation-delay: 0.32s; }
.terms-grid .animate-on-scroll:nth-child(6) { animation-delay: 0.4s; }
.device-gate-card { animation: fadeInUp 0.75s cubic-bezier(0.22, 1, 0.36, 1) both; }
.device-gate-title { animation: fadeInUp 0.65s 0.12s ease both; }
.device-choice { animation: fadeInUp 0.65s 0.24s ease both; }
.device-choice + .device-choice { animation-delay: 0.34s; }
@media (prefers-reduced-motion: reduce) {
  html { scroll-behavior: auto; }
  .animate-on-scroll,
  .animate-on-scroll.visible,
  .hero-badge,
  .hero-title,
  .hero-subtitle,
  .hero-buttons,
  .hero-trust,
  .hero-illustration,
  .device-gate-card,
  .device-gate-title,
  .device-choice {
    animation: none;
    opacity: 1;
    transform: none;
    filter: none;
  }
}

/* ============================================
   SECTION HEADERS
   ============================================ */
.section-header {
  text-align: center;
  margin-bottom: 56px;
}
.section-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--electric);
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-bottom: 12px;
}
.section-title {
  font-size: 2.2rem;
  font-weight: 800;
  color: var(--navy);
  margin-bottom: 14px;
}
.section-subtitle {
  font-size: 1.05rem;
  color: var(--text-muted);
  max-width: 600px;
  margin: 0 auto;
}

/* ============================================
   SERVICES SECTION
   ============================================ */
.services { background: var(--white); }
.services-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}
.service-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 32px 24px;
  text-align: center;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}
.service-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--electric), var(--sky));
  opacity: 0;
  transition: var(--transition);
}
.service-card:hover {
  transform: translateY(-6px);
  box-shadow: var(--shadow-lg);
  border-color: rgba(37,99,235,0.2);
}
.service-card:hover::before { opacity: 1; }
.service-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 1.6rem;
  transition: var(--transition);
}
.service-card:nth-child(1) .service-icon { background: #EFF6FF; color: var(--electric); }
.service-card:nth-child(2) .service-icon { background: #F0FDF4; color: #16A34A; }
.service-card:nth-child(3) .service-icon { background: #FFF7ED; color: #EA580C; }
.service-card:nth-child(4) .service-icon { background: #FAF5FF; color: #9333EA; }
.service-card:hover .service-icon { transform: scale(1.1); }
.service-card h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin-bottom: 10px;
  color: var(--navy);
}
.service-card p {
  font-size: 0.88rem;
  color: var(--text-muted);
  line-height: 1.6;
  margin-bottom: 18px;
}
.service-link {
  color: var(--electric);
  font-size: 0.88rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: var(--transition);
}
.service-link:hover { gap: 10px; }

/* ============================================
   PRICING SECTION
   ============================================ */
.pricing { background: var(--soft-gray); }
.pricing-tabs {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-bottom: 40px;
  flex-wrap: wrap;
  background: var(--white);
  padding: 6px;
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  max-width: 700px;
  margin-left: auto;
  margin-right: auto;
}
.pricing-tab {
  padding: 10px 22px;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text-muted);
  cursor: pointer;
  transition: var(--transition);
  border: none;
  background: transparent;
  font-family: inherit;
  white-space: nowrap;
}
.pricing-tab:hover { color: var(--electric); background: var(--light-blue); }
.pricing-tab.active {
  background: var(--electric);
  color: var(--white);
  box-shadow: 0 2px 8px rgba(37,99,235,0.3);
}
.pricing-panel { display: none; animation: fadeIn 0.4s ease; }
.pricing-panel.active { display: block; }
.pricing-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  max-width: 800px;
  margin: 0 auto;
}
.pricing-card-header {
  background: linear-gradient(135deg, var(--navy), var(--royal));
  padding: 24px 32px;
  color: var(--white);
}
.pricing-card-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}
.pricing-card-header h3 i { color: var(--sky); }
.pricing-card-header p {
  font-size: 0.88rem;
  color: #93C5FD;
  margin-top: 4px;
}
.pricing-table {
  width: 100%;
  border-collapse: collapse;
}
.pricing-table thead th {
  background: var(--soft-gray);
  padding: 14px 24px;
  text-align: left;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text-muted);
  border-bottom: 2px solid var(--border);
}
.pricing-table tbody td {
  padding: 16px 24px;
  border-bottom: 1px solid var(--border);
  font-size: 0.92rem;
  vertical-align: middle;
}
.pricing-table tbody tr { transition: var(--transition); }
.pricing-table tbody tr:hover { background: #F0F7FF; }
.pricing-table tbody tr:last-child td { border-bottom: none; }
.item-name {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 500;
}
.item-name i {
  color: var(--electric);
  font-size: 0.85rem;
  width: 20px;
  text-align: center;
}
.price-badge {
  display: inline-flex;
  align-items: center;
  padding: 5px 14px;
  background: var(--light-blue);
  color: var(--royal);
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.88rem;
  white-space: nowrap;
}
.pricing-card-footer {
  padding: 20px 32px;
  background: var(--soft-gray);
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid var(--border);
}
.pricing-card-footer p {
  font-size: 0.85rem;
  color: var(--text-muted);
}
.pricing-card-footer p i { color: var(--success); margin-right: 6px; }
.btn-order-sm {
  background: var(--electric);
  color: var(--white);
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.88rem;
  border: none;
  cursor: pointer;
  transition: var(--transition);
  font-family: inherit;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-order-sm:hover {
  background: #1D4ED8;
  transform: translateY(-1px);
}
/* Featured paketan tab */
.pricing-card.featured { border: 2px solid var(--electric); }
.pricing-card.featured .pricing-card-header {
  background: linear-gradient(135deg, var(--electric), #3B82F6);
}

/* ============================================
   CARA PESAN SECTION
   ============================================ */
.how-to { background: var(--white); }
.steps-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  position: relative;
}
.steps-grid::before {
  content: '';
  position: absolute;
  top: 48px;
  left: 15%;
  right: 15%;
  height: 2px;
  background: linear-gradient(90deg, var(--border), var(--electric), var(--border));
  z-index: 0;
}
.step-card {
  text-align: center;
  position: relative;
  z-index: 1;
  padding: 0 12px;
}
.step-number {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 18px;
  font-size: 1.3rem;
  font-weight: 800;
  background: var(--white);
  border: 3px solid var(--electric);
  color: var(--electric);
  transition: var(--transition);
}
.step-card:hover .step-number {
  background: var(--electric);
  color: var(--white);
  transform: scale(1.1);
}
.step-icon {
  font-size: 2rem;
  color: var(--electric);
  margin-bottom: 12px;
}
.step-card h4 {
  font-size: 1rem;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 8px;
}
.step-card p {
  font-size: 0.85rem;
  color: var(--text-muted);
  line-height: 1.5;
}

/* ============================================
   KETENTUAN SECTION
   ============================================ */
.terms { background: linear-gradient(180deg, var(--soft-gray), #E8EFF8); }
.terms-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  max-width: 900px;
  margin: 0 auto 32px;
}
.term-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  background: var(--white);
  padding: 18px 20px;
  border-radius: var(--radius-sm);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  transition: var(--transition);
}
.term-item:hover { box-shadow: var(--shadow-md); border-color: rgba(37,99,235,0.2); }
.term-check {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #DCFCE7;
  color: var(--success);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.8rem;
}
.term-item h4 {
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--navy);
  margin-bottom: 3px;
}
.term-item p {
  font-size: 0.82rem;
  color: var(--text-muted);
  line-height: 1.4;
}
.term-warning {
  max-width: 900px;
  margin: 0 auto;
  background: linear-gradient(135deg, #FEF2F2, #FFF1F2);
  border: 2px solid #FECACA;
  border-radius: var(--radius-md);
  padding: 22px 28px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.term-warning-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #FEE2E2;
  color: var(--danger);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.term-warning h4 {
  font-size: 1rem;
  font-weight: 700;
  color: #B91C1C;
  margin-bottom: 3px;
}
.term-warning p {
  font-size: 0.88rem;
  color: #991B1B;
}
.terms {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #eef5fb 0%, #f8fbff 52%, #e9f3f8 100%);
}
.terms::before,
.terms::after {
  content: '';
  position: absolute;
  border: 1px solid rgba(37,99,235,0.08);
  border-radius: 50%;
  pointer-events: none;
}
.terms::before { width: 360px; height: 360px; top: -190px; right: -100px; }
.terms::after { width: 250px; height: 250px; bottom: -150px; left: -110px; }
.terms .container { position: relative; z-index: 1; }
.terms .section-header { margin-bottom: 44px; }
.terms .section-label {
  padding: 7px 13px;
  border: 1px solid rgba(37,99,235,0.16);
  border-radius: 999px;
  background: rgba(255,255,255,0.65);
}
.terms .section-title { letter-spacing: -0.03em; }
.terms-grid { max-width: 980px; gap: 18px; }
.term-item {
  position: relative;
  min-height: 112px;
  padding: 22px 24px;
  border-color: rgba(148,163,184,0.3);
  border-radius: 16px;
  box-shadow: 0 10px 24px rgba(30,58,138,0.06);
  overflow: hidden;
}
.term-item::before {
  content: '';
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: linear-gradient(180deg, var(--electric), var(--sky));
}
.term-item:nth-child(2)::before { background: linear-gradient(180deg, #0f9f88, #55d6b6); }
.term-item:nth-child(3)::before { background: linear-gradient(180deg, #f59e0b, #fbbf24); }
.term-item:nth-child(4)::before { background: linear-gradient(180deg, #8b5cf6, #c084fc); }
.term-item:nth-child(5)::before { background: linear-gradient(180deg, #16a34a, #4ade80); }
.term-item:nth-child(6)::before { background: linear-gradient(180deg, #0891b2, #67e8f9); }
.term-item:nth-child(7)::before { background: linear-gradient(180deg, #e11d48, #fb7185); }
.term-item:hover { transform: translateY(-4px); box-shadow: 0 16px 30px rgba(30,58,138,0.12); }
.term-check {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, #dcfce7, #bbf7d0);
  box-shadow: 0 5px 12px rgba(34,197,94,0.14);
  font-size: 0.9rem;
}
.term-item h4 { font-size: 0.98rem; letter-spacing: -0.01em; }
.term-item p { font-size: 0.84rem; line-height: 1.55; }
.term-warning {
  position: relative;
  max-width: 980px;
  margin-top: 36px;
  padding: 20px 24px;
  border: 1px solid rgba(220,38,38,0.25);
  border-left: 5px solid #ef4444;
  border-radius: 14px;
  background: linear-gradient(110deg, #fff7f7, #fff1f2);
  box-shadow: 0 12px 26px rgba(185,28,28,0.08);
}
.term-warning-icon { border-radius: 14px; box-shadow: 0 6px 14px rgba(239,68,68,0.12); }
.term-warning h4 { letter-spacing: 0.01em; }
@media (max-width: 768px) {
  .terms .section-header { margin-bottom: 32px; }
  .term-item { min-height: 0; padding: 18px 20px; }
  .term-warning { align-items: flex-start; padding: 18px; }
}

/* ============================================
   TESTIMONIALS SECTION
   ============================================ */
.testimonials { background: var(--white); }
.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}
.testimonial-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 28px;
  transition: var(--transition);
}
.testimonial-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }
.testimonial-stars {
  color: #FBBF24;
  font-size: 0.9rem;
  margin-bottom: 14px;
  letter-spacing: 2px;
}
.testimonial-card blockquote {
  font-size: 0.92rem;
  color: var(--text-dark);
  line-height: 1.7;
  margin-bottom: 18px;
  font-style: italic;
  position: relative;
  padding-left: 16px;
  border-left: 3px solid var(--electric);
}
.testimonial-author {
  display: flex;
  align-items: center;
  gap: 12px;
}
.testimonial-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--electric), var(--sky));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-weight: 700;
  font-size: 0.9rem;
}
.testimonial-info h5 {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--navy);
}
.testimonial-info span {
  font-size: 0.78rem;
  color: var(--text-muted);
}
.proof-section {
  margin-top: 72px;
  padding: 42px;
  background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
}
.proof-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 26px;
}
.proof-kicker {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 10px;
  color: var(--electric);
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.proof-kicker::before {
  content: '';
  width: 24px;
  height: 2px;
  border-radius: 2px;
  background: var(--sky);
}
.proof-heading h3 {
  margin: 0 0 8px;
  color: var(--navy);
  font-size: clamp(1.45rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
}
.proof-heading p {
  margin: 0;
  color: var(--text-muted);
  max-width: 600px;
  font-size: 0.92rem;
  line-height: 1.6;
}
.proof-trust {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #15803D;
  font-size: 0.78rem;
  font-weight: 700;
  white-space: nowrap;
  padding: 9px 12px;
  border: 1px solid rgba(21,128,61,0.16);
  border-radius: 999px;
  background: rgba(240,253,244,0.72);
}
.proof-reviews {
  overflow: hidden;
  margin: 0 auto 28px;
  padding: 12px 0;
  border-top: 1px solid rgba(37,99,235,0.12);
  border-bottom: 1px solid rgba(37,99,235,0.12);
  mask-image: linear-gradient(90deg, transparent, #000 7%, #000 93%, transparent);
}
.proof-review-track {
  display: flex;
  width: max-content;
  animation: proofReviews 34s linear infinite;
}
.proof-review {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 0 28px;
  color: var(--text-dark);
  font-size: 0.82rem;
  white-space: nowrap;
}
.proof-review i { color: #F59E0B; font-size: 0.72rem; }
.proof-review strong { color: var(--electric); font-weight: 700; }
@keyframes proofReviews {
  from { transform: translateX(0); }
  to { transform: translateX(-50%); }
}
@media (prefers-reduced-motion: reduce) {
  .proof-review-track { animation: none; }
  .proof-stage.is-changing,
  .proof-stage.is-changing img,
  .proof-caption.is-changing { animation: none; }
}
.proof-slider { position: relative; max-width: 760px; margin: 0 auto; }
.proof-stage {
  position: relative;
  overflow: hidden;
  min-height: 360px;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: #EAF2F7;
  box-shadow: 0 12px 28px rgba(15,23,42,0.08);
  cursor: zoom-in;
  touch-action: pan-y;
}
.proof-stage img { width: 100%; height: 440px; display: block; object-fit: contain; will-change: transform, opacity, filter; }
.proof-stage.is-changing { cursor: progress; }
.proof-stage.is-changing { animation: proofFrameGlow 680ms ease-out both; }
.proof-stage.is-changing img { animation-duration: 680ms; animation-timing-function: cubic-bezier(0.22, 0.61, 0.36, 1); animation-fill-mode: both; }
.proof-stage.is-changing.next img { animation-name: proofSlideInNext; }
.proof-stage.is-changing.prev img { animation-name: proofSlideInPrev; }
@keyframes proofSlideInNext {
  from { opacity: 0; filter: blur(7px); transform: translateX(8%) scale(0.965); }
  55% { opacity: 0.82; filter: blur(1px); }
  to { opacity: 1; filter: blur(0); transform: translateX(0) scale(1); }
}
@keyframes proofSlideInPrev {
  from { opacity: 0; filter: blur(7px); transform: translateX(-8%) scale(0.965); }
  55% { opacity: 0.82; filter: blur(1px); }
  to { opacity: 1; filter: blur(0); transform: translateX(0) scale(1); }
}
@keyframes proofFrameGlow {
  0% { box-shadow: 0 12px 28px rgba(15,23,42,0.08), 0 0 0 rgba(37,99,235,0); }
  45% { box-shadow: 0 18px 38px rgba(15,23,42,0.14), 0 0 22px rgba(56,189,248,0.2); }
  100% { box-shadow: 0 12px 28px rgba(15,23,42,0.08), 0 0 0 rgba(37,99,235,0); }
}
.proof-stage::after {
  content: 'Klik foto untuk memperbesar';
  position: absolute;
  right: 16px;
  bottom: 16px;
  padding: 7px 11px;
  border-radius: 6px;
  background: rgba(15,23,42,0.78);
  color: var(--white);
  font-size: 0.7rem;
  font-weight: 700;
  pointer-events: none;
}
.proof-arrow {
  position: absolute;
  top: 50%;
  z-index: 1;
  width: 42px;
  height: 42px;
  border: 0;
  border-radius: 50%;
  background: var(--white);
  color: var(--navy);
  box-shadow: 0 4px 14px rgba(15,23,42,0.18);
  cursor: pointer;
  transform: translateY(-50%);
}
.proof-arrow:hover { color: var(--electric); }
.proof-arrow.prev { left: 16px; }
.proof-arrow.next { right: 16px; }
.proof-caption { padding: 16px 4px 0; text-align: center; }
.proof-caption.is-changing { animation: proofCaptionIn 420ms ease-out both; }
.proof-caption strong { display: block; color: var(--navy); font-size: 0.9rem; }
.proof-caption span { color: var(--text-muted); font-size: 0.78rem; }
@keyframes proofCaptionIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.proof-dots { display: flex; justify-content: center; gap: 8px; margin-top: 16px; }
.proof-dot { width: 8px; height: 8px; padding: 0; border: 0; border-radius: 50%; background: #CBD5E1; cursor: pointer; }
.proof-dot.active { width: 24px; border-radius: 5px; background: var(--electric); }
.proof-modal .modal { max-width: 430px; }
.proof-modal .modal-body { padding: 16px; background: #EAF2F7; }
.proof-modal img { width: 100%; max-height: 70vh; display: block; object-fit: contain; border-radius: 8px; }

/* ============================================
   CTA SECTION
   ============================================ */
.cta-section {
  background: linear-gradient(135deg, var(--navy) 0%, var(--royal) 100%);
  padding: 70px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.cta-section::before {
  content: '';
  position: absolute;
  top: -50%;
  left: 50%;
  transform: translateX(-50%);
  width: 800px;
  height: 800px;
  background: radial-gradient(circle, rgba(56,189,248,0.08) 0%, transparent 70%);
}
.cta-section h2 {
  font-size: 2rem;
  font-weight: 800;
  color: var(--white);
  margin-bottom: 14px;
  position: relative;
}
.cta-section p {
  font-size: 1.05rem;
  color: #93C5FD;
  margin-bottom: 32px;
  position: relative;
}
.cta-section .btn-primary {
  font-size: 1.05rem;
  padding: 16px 40px;
  position: relative;
}

/* ============================================
   FOOTER
   ============================================ */
.footer {
  background: var(--navy);
  padding: 60px 0 0;
  color: #94A3B8;
}
.footer-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1.3fr;
  gap: 40px;
  padding-bottom: 40px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.footer-brand .logo {
  margin-bottom: 14px;
  font-size: 1.1rem;
}
.footer-brand p {
  font-size: 0.88rem;
  line-height: 1.7;
  color: #64748B;
  margin-bottom: 18px;
}
.footer-social {
  display: flex;
  gap: 10px;
}
.footer-social a {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: rgba(255,255,255,0.06);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94A3B8;
  font-size: 0.95rem;
  transition: var(--transition);
}
.footer-social a:hover {
  background: var(--electric);
  color: var(--white);
  transform: translateY(-2px);
}
.footer-col h4 {
  color: var(--white);
  font-size: 0.95rem;
  font-weight: 700;
  margin-bottom: 18px;
}
.footer-col ul li { margin-bottom: 10px; }
.footer-col ul li a {
  color: #64748B;
  font-size: 0.88rem;
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 6px;
}
.footer-col ul li a:hover { color: var(--sky); padding-left: 4px; }
.footer-contact li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 14px !important;
}
.footer-contact li i {
  color: var(--sky);
  margin-top: 3px;
  width: 16px;
}
.footer-contact li span {
  font-size: 0.88rem;
  color: #94A3B8;
  line-height: 1.5;
}
.footer-payments {
  padding: 24px 0;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
}
.payment-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 6px;
  color: #94A3B8;
  font-size: 0.8rem;
  font-weight: 500;
}
.payment-badge i { color: var(--sky); }
.footer-bottom {
  padding: 20px 0;
  text-align: center;
  font-size: 0.82rem;
  color: #475569;
}

/* ============================================
   FLOATING WHATSAPP BUTTON
   ============================================ */
.wa-float {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 999;
}
.wa-float a {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--wa-green);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-size: 1.8rem;
  box-shadow: 0 4px 20px rgba(37,211,102,0.4);
  animation: pulse 2s infinite;
  transition: var(--transition);
}
.wa-float a:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 30px rgba(37,211,102,0.5);
}
.wa-tooltip {
  position: absolute;
  right: 72px;
  top: 50%;
  transform: translateY(-50%);
  background: var(--white);
  color: var(--text-dark);
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  box-shadow: var(--shadow-md);
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: var(--transition);
}
.wa-float:hover .wa-tooltip { opacity: 1; }
.wa-tooltip::after {
  content: '';
  position: absolute;
  right: -6px;
  top: 50%;
  transform: translateY(-50%);
  border: 6px solid transparent;
  border-left-color: var(--white);
}

/* ============================================
   ORDER MODAL
   ============================================ */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,0.7);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-overlay.active { display: flex; }
.modal {
  background: var(--white);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 540px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideDown 0.3s ease;
  box-shadow: var(--shadow-xl);
}
.modal-header {
  background: linear-gradient(135deg, var(--navy), var(--royal));
  padding: 24px 28px;
  color: var(--white);
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.modal-header h3 {
  font-size: 1.2rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}
.modal-header h3 i { color: var(--sky); }
.modal-close {
  background: rgba(255,255,255,0.15);
  border: none;
  color: var(--white);
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.1rem;
  transition: var(--transition);
}
.modal-close:hover { background: rgba(255,255,255,0.25); }
.modal-body { padding: 28px; }
.shipping-option{margin:22px 0;padding:14px;border:1px solid #cde4e2;border-radius:12px;background:linear-gradient(135deg,#f3fbfa,#f8fcff)}.shipping-toggle{display:flex;align-items:center;gap:12px;margin:0;cursor:pointer}.shipping-toggle input{width:18px;height:18px;accent-color:var(--electric);flex:0 0 auto}.shipping-toggle span{flex:1}.shipping-toggle strong{display:block;color:var(--navy);font-size:.9rem}.shipping-toggle small{display:block;margin-top:4px;color:var(--text-muted);font-size:.76rem;font-weight:400;line-height:1.45}.shipping-toggle>i{color:var(--electric);font-size:1.2rem}.shipping-fields{margin-top:16px;padding-top:16px;border-top:1px solid #d9e9e8}.shipping-fields-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}.shipping-fields .form-group{margin-bottom:14px}.shipping-wide{grid-column:1/-1}@media(max-width:560px){.shipping-fields-grid{grid-template-columns:1fr}.shipping-wide{grid-column:auto}}
.form-group { margin-bottom: 18px; }
.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--navy);
  margin-bottom: 6px;
}
.form-control {
  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-size: 0.92rem;
  font-family: inherit;
  color: var(--text-dark);
  transition: var(--transition);
  background: var(--white);
}
.form-control:focus {
  outline: none;
  border-color: var(--electric);
  box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
select.form-control { cursor: pointer; }
textarea.form-control { resize: vertical; min-height: 80px; }
.deadline-picker { position: relative; }
.deadline-picker .form-control { padding-right: 44px; }
.deadline-picker i {
  position: absolute;
  top: 50%;
  right: 14px;
  color: var(--electric);
  pointer-events: none;
  transform: translateY(-50%);
}
.form-hint {
  display: block;
  margin-top: 6px;
  color: var(--text-muted);
  font-size: 0.76rem;
}
.calc-box {
  background: var(--soft-gray);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 16px;
  margin-bottom: 20px;
}
.calc-row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 0.9rem;
}
.calc-row.total {
  border-top: 2px solid var(--border);
  margin-top: 8px;
  padding-top: 12px;
  font-weight: 700;
  color: var(--navy);
}
.calc-row .dp { color: var(--electric); font-weight: 700; }
.btn-submit {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, var(--wa-green), #20BA5C);
  color: var(--white);
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-family: inherit;
  transition: var(--transition);
}
.btn-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(37,211,102,0.4);
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1024px) {
  .hero-inner { grid-template-columns: 1fr; text-align: center; }
  .hero-subtitle { margin: 0 auto 36px; }
  .hero-buttons { justify-content: center; }
  .hero-trust { justify-content: center; }
  .hero-illustration { display: block; min-height: 390px; max-width: 620px; width: 100%; margin: 0 auto; }
  .services-grid { grid-template-columns: repeat(2, 1fr); }
  .steps-grid { grid-template-columns: repeat(2, 1fr); }
  .steps-grid::before { display: none; }
  .testimonials-grid { grid-template-columns: 1fr; max-width: 500px; margin: 0 auto; }
  .proof-section { padding: 30px 24px; }
  .proof-stage img { height: 340px; }
  .footer-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
  .header-inner { height: 64px; }
  .logo { min-width: 0; font-size: .98rem; gap: 7px; white-space: nowrap; }
  .logo span { font-size: .78rem; }
  .nav-links {
    position: absolute;
    top: 64px;
    left: 0;
    right: 0;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0;
    padding: 8px 20px 14px;
    background: rgba(15,23,42,.98);
    border-top: 1px solid rgba(255,255,255,.1);
    box-shadow: 0 14px 25px rgba(0,0,0,.22);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: opacity .2s ease, visibility .2s ease, transform .2s ease;
  }
  .nav-links.is-open { opacity: 1; visibility: visible; transform: translateY(0); }
  .nav-links a { padding: 13px 4px; border-bottom: 1px solid rgba(255,255,255,.08); }
  .nav-links a:last-child { border-bottom: 0; }
  .mobile-toggle { display: block; }
  .hero-title { font-size: 2.2rem; }
  .services-grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
  .terms-grid { grid-template-columns: 1fr; }
  .steps-grid { grid-template-columns: 1fr; max-width: 300px; margin: 0 auto; }
  .footer-grid { grid-template-columns: 1fr; }
  .pricing-tabs { flex-direction: column; align-items: stretch; }
  .section-padding { padding: 60px 0; }
  .section-title { font-size: 1.7rem; }
  .proof-heading { display: block; }
  .proof-trust { margin-top: 14px; }
  .hero-illustration { min-height: 330px; padding: 12px 0; }
  .hero-carousel { inset: 12px 0; border-radius: 20px; }
  .hero-slide-caption { right: 16px; bottom: 24px; max-width: 165px; padding: 10px 12px; font-size: 0.72rem; }
  .hero-carousel-dots { left: 18px; bottom: 30px; }
  .hero-carousel-controls { right: 16px; top: 26px; }
  .carousel-arrow { width: 34px; height: 34px; }
}
.device-gate {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: grid;
  place-items: center;
  padding: 24px;
  background: linear-gradient(135deg, rgba(15,23,42,.98), rgba(30,58,138,.96));
  transition: opacity .35s ease, visibility .35s ease;
}
.device-gate.is-hidden { opacity: 0; visibility: hidden; pointer-events: none; }
.device-gate-card {
  width: min(680px, 100%);
  padding: clamp(28px, 5vw, 48px);
  border: 1px solid rgba(255,255,255,.18);
  border-radius: 24px;
  background: rgba(255,255,255,.1);
  box-shadow: 0 28px 80px rgba(0,0,0,.28);
  color: var(--white);
  text-align: center;
  backdrop-filter: blur(16px);
}
.device-gate-kicker { margin: 0 0 12px; color: var(--sky); font-size: .78rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
.device-gate-title { margin: 0 0 12px; font-size: clamp(1.8rem, 4vw, 2.65rem); }
.device-gate-copy { margin: 0 auto 28px; max-width: 520px; color: #CBD5E1; line-height: 1.65; }
.device-choice-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.device-choice {
  display: grid;
  gap: 10px;
  min-height: 150px;
  place-items: center;
  padding: 22px 16px;
  border: 1px solid rgba(255,255,255,.2);
  border-radius: 16px;
  background: rgba(255,255,255,.08);
  color: var(--white);
  cursor: pointer;
  transition: transform .25s ease, background .25s ease, border-color .25s ease;
}
.device-choice:hover, .device-choice:focus-visible { transform: translateY(-4px); border-color: var(--sky); background: rgba(56,189,248,.18); outline: none; }
.device-choice i { color: var(--sky); font-size: 2.6rem; }
.device-choice strong { font-size: 1.05rem; }
.device-choice span { color: #CBD5E1; font-size: .82rem; }
body.device-mobile .container { width: min(430px, calc(100% - 28px)); }
body.device-mobile .hero-inner { gap: 28px; }
body.device-mobile .hero-illustration { min-height: 300px; }
body.device-mobile .header-inner { height: 64px; }
body.device-mobile .logo { min-width: 0; font-size: .98rem; gap: 7px; white-space: nowrap; }
body.device-mobile .logo span { font-size: .78rem; }
body.device-mobile .mobile-toggle { display: block; }
body.device-mobile .nav-links { position: absolute; top: 64px; left: 0; right: 0; display: flex; flex-direction: column; align-items: stretch; gap: 0; padding: 8px 20px 14px; background: rgba(15,23,42,.98); border-top: 1px solid rgba(255,255,255,.1); box-shadow: 0 14px 25px rgba(0,0,0,.22); }
body.device-mobile .nav-links:not(.is-open) { opacity: 0; visibility: hidden; transform: translateY(-8px); }
body.device-mobile .nav-links a { padding: 13px 4px; border-bottom: 1px solid rgba(255,255,255,.08); }
body.device-mobile .nav-links a:last-child { border-bottom: 0; }
body.device-mobile .hero-inner { grid-template-columns: 1fr; text-align: center; }
body.device-mobile .hero-subtitle { margin: 0 auto 36px; }
body.device-mobile .hero-buttons { justify-content: center; }
body.device-mobile .hero-trust { justify-content: center; }
body.device-mobile .services-grid, body.device-mobile .steps-grid { grid-template-columns: 1fr; max-width: 400px; margin: 0 auto; }
.mobile-service-picker { display: none; }
body.device-mobile .mobile-service-picker { display: block; }
body.device-mobile #layanan { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
.mobile-service-trigger { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border: 1.5px solid var(--border); border-radius: 8px; background: var(--white); color: var(--text-dark); text-align: left; cursor: pointer; }
.mobile-service-trigger span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mobile-service-trigger i { color: var(--electric); flex: 0 0 auto; }
.mobile-service-list { display: none; max-height: min(52vh, 390px); overflow-y: auto; margin-top: 8px; border: 1px solid var(--border); border-radius: 10px; background: var(--white); box-shadow: 0 12px 28px rgba(15,23,42,.14); }
.mobile-service-list.is-open { display: block; }
.mobile-service-category { padding: 12px 14px 7px; color: var(--electric); font-size: .75rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
.mobile-service-option { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border: 0; border-top: 1px solid #eef2f7; background: var(--white); color: var(--text-dark); text-align: left; cursor: pointer; }
.mobile-service-option:hover, .mobile-service-option.is-selected { background: #eff6ff; color: var(--electric); }
.mobile-service-option span:first-child { min-width: 0; line-height: 1.35; }
.mobile-service-price { flex: 0 0 auto; color: var(--text-muted); font-size: .76rem; white-space: nowrap; }
body.device-mobile .modal { max-height: calc(100vh - 24px); overflow-y: auto; }
body.device-mobile .modal-body { padding: 20px 16px; }
body.device-gate-pending { overflow: hidden; }
@media (max-width: 520px) {
  .device-gate { padding: 16px; }
  .device-gate-card { border-radius: 18px; }
  .device-choice-grid { grid-template-columns: 1fr; }
  .device-choice { min-height: 116px; grid-template-columns: auto 1fr; place-items: center start; text-align: left; }
  .device-choice i { grid-row: span 2; }
}
</style>
<style>
.additional-services{margin:0 0 28px;padding:22px;border:1px solid rgba(8,127,131,.2);border-radius:14px;background:linear-gradient(135deg,#effaf7,#f8fbfc);box-shadow:0 12px 30px rgba(20,69,78,.08)}.additional-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:17px}.additional-heading h3{margin:7px 0 3px;color:var(--navy);font-size:20px}.additional-heading p{margin:0;color:var(--muted);font-size:13px}.additional-icon{padding:12px;border-radius:10px;background:#d9f2ed;color:var(--teal);font-size:18px}.additional-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:10px}.additional-item{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:14px;border:1px solid #dcebea;border-radius:9px;background:#fff}.additional-item strong,.additional-item small{display:block}.additional-item strong{font-size:14px}.additional-item small{margin-top:4px;color:var(--muted);font-size:12px}.additional-item .price-badge{white-space:nowrap;font-size:12px}@media(max-width:560px){.additional-heading{align-items:center}.additional-item{align-items:flex-start;flex-direction:column}.additional-item .price-badge{white-space:normal}}
</style>
</head>
<body class="device-<?= htmlspecialchars($deviceMode ?? 'laptop', ENT_QUOTES, 'UTF-8') ?>">

<!-- ============================================
     HEADER
     ============================================ -->
<header class="header" id="header">
  <div class="container header-inner">
    <a href="#" class="logo">
      <i class="fas fa-bolt logo-icon"></i>
      VELOCITY <span>SYSTEM AI</span>
    </a>
    <nav class="nav-links" id="navLinks">
      <a href="#hero">Beranda</a>
      <a href="#services">Layanan</a>
      <a href="#pricing">Harga</a>
      <a href="#how-to">Cara Pesan</a>
      <a href="#testimonials">Testimoni</a>
      <a href="#footer">Kontak</a>
    </nav>
    <button class="mobile-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false" onclick="toggleNav()">
      <i class="fas fa-bars"></i>
    </button>
  </div>
</header>

<section class="hero" id="hero">
  <div class="container">
    <div class="hero-inner">
      <div class="hero-content">
        <div class="hero-badge"><i class="fas fa-bolt"></i> Platform Terpercaya #1</div>
        <h1 class="hero-title">Tugas Numpuk? Tenang!<br><span class="highlight">Kami Siap Melayani Anda</span></h1>
        <p class="hero-subtitle">Jasa Joki Tugas Profesional & Terpercaya untuk Pelajar dan Mahasiswa. Pengerjaan Cepat, Amanah, & Garansi Diterima.</p>
        <div class="hero-buttons">
          <button class="btn-primary" onclick="openModal()"><i class="fas fa-paper-plane"></i> Pesan Sekarang</button>
          <a href="#pricing" class="btn-outline"><i class="fas fa-tags"></i> Cek Daftar Harga</a>
        </div>
        <div class="hero-trust">
          <div class="trust-item"><i class="fas fa-circle-check"></i> 500+ Tugas Selesai</div>
          <div class="trust-item"><i class="fas fa-circle-check"></i> Garansi 100%</div>
          <div class="trust-item"><i class="fas fa-circle-check"></i> Privasi Aman</div>
        </div>
      </div>
      <div class="hero-illustration" aria-label="Galeri ruang kerja profesional">
        <div class="hero-carousel" id="heroCarousel" tabindex="0">
          <div class="hero-slides">
            <div class="hero-slide is-active" role="img" aria-label="Promosi open order Velocity System AI"></div>
            <div class="hero-slide" role="img" aria-label="Keunggulan Velocity System AI"></div>
            <div class="hero-slide" role="img" aria-label="Informasi layanan Velocity System AI"></div>
            <div class="hero-slide" role="img" aria-label="Promo spesial Ramadan"></div>
            <div class="hero-slide" role="img" aria-label="Layanan tugas tulis tangan"></div>
            <div class="hero-slide" role="img" aria-label="Promo spesial potongan harga"></div>
            <div class="hero-slide" role="img" aria-label="Pengumuman layanan Velocity System AI"></div>
          </div>
          <div class="hero-carousel-controls">
            <button class="carousel-arrow" type="button" data-carousel-prev aria-label="Gambar sebelumnya"><i class="fas fa-arrow-left"></i></button>
            <button class="carousel-arrow" type="button" data-carousel-next aria-label="Gambar berikutnya"><i class="fas fa-arrow-right"></i></button>
          </div>
          <div class="hero-carousel-dots" role="tablist" aria-label="Pilih gambar hero">
            <button class="carousel-dot active" type="button" data-carousel-dot="0" aria-label="Tampilkan gambar 1" aria-selected="true"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="1" aria-label="Tampilkan gambar 2" aria-selected="false"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="2" aria-label="Tampilkan gambar 3" aria-selected="false"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="3" aria-label="Tampilkan gambar 4" aria-selected="false"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="4" aria-label="Tampilkan gambar 5" aria-selected="false"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="5" aria-label="Tampilkan gambar 6" aria-selected="false"></button>
            <button class="carousel-dot" type="button" data-carousel-dot="6" aria-label="Tampilkan gambar 7" aria-selected="false"></button>
          </div>
          <div class="hero-slide-caption" aria-live="polite">Open order Velocity System AI</div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="modal-overlay proof-modal" id="proofModal" onclick="closeProof(event)">
  <div class="modal">
    <div class="modal-header"><h3><i class="fas fa-comments"></i> Detail Feedback</h3><button class="modal-close" onclick="closeProof()"><i class="fas fa-xmark"></i></button></div>
    <div class="modal-body"><img id="proofModalImage" src="" alt="Foto dokumentasi layanan"></div>
  </div>
</div>

<template hidden>
      <div class="hero-illustration" role="img" aria-label="Ruang kerja profesional dengan laptop dan perlengkapan belajar"></div>
          <rect x="186" y="246" width="80" height="5" rx="2.5" fill="#93C5FD" opacity="0.5"/>
          <rect x="186" y="256" width="40" height="5" rx="2.5" fill="#93C5FD" opacity="0.4"/>
          <!-- Laptop base -->
          <path d="M155 280 H345 L355 288 H145 Z" fill="#1E293B"/>
          <!-- Coffee cup -->
          <rect x="360" y="254" width="30" height="26" rx="4" fill="#38BDF8"/>
          <rect x="360" y="250" width="30" height="6" rx="3" fill="#0F172A"/>
          <path d="M390 260 Q400 260 400 270 Q400 280 390 280" stroke="#38BDF8" stroke-width="3" fill="none"/>
          <!-- Steam -->
          <path d="M370 245 Q372 238 375 245" stroke="#93C5FD" stroke-width="1.5" fill="none" opacity="0.5"/>
          <path d="M378 243 Q380 234 383 243" stroke="#93C5FD" stroke-width="1.5" fill="none" opacity="0.4"/>
          <!-- Books stack -->
          <rect x="100" y="248" width="55" height="10" rx="2" fill="#2563EB"/>
          <rect x="103" y="238" width="50" height="10" rx="2" fill="#38BDF8"/>
          <rect x="106" y="228" width="44" height="10" rx="2" fill="#1E3A8A"/>
          <!-- Pencil -->
          <rect x="118" y="260" width="4" height="18" rx="1" fill="#F59E0B" transform="rotate(-20, 120, 270)"/>
          <polygon points="116,280 120,280 118,286" fill="#0F172A" transform="rotate(-20, 118, 283)"/>
          <!-- Person (simplified) -->
          <circle cx="250" cy="150" r="32" fill="#DBEAFE"/>
          <circle cx="250" cy="150" r="28" fill="#93C5FD"/>
          <!-- Hair -->
          <path d="M222 145 Q222 120 250 118 Q278 120 278 145" fill="#0F172A"/>
          <!-- Body -->
          <path d="M220 190 Q220 175 250 175 Q280 175 280 190 L285 230 H215 Z" fill="#2563EB"/>
          <!-- Arms -->
          <path d="M220 195 L185 235 Q183 240 188 240 L210 230" fill="#2563EB"/>
          <path d="M280 195 L315 235 Q317 240 312 240 L290 230" fill="#2563EB"/>
          <!-- Hands on keyboard -->
          <circle cx="210" cy="268" r="8" fill="#DBEAFE"/>
          <circle cx="290" cy="268" r="8" fill="#DBEAFE"/>
          <!-- Floating icons -->
          <rect x="50" y="120" width="36" height="36" rx="8" fill="rgba(37,99,235,0.15)"/>
          <text x="60" y="144" fill="#38BDF8" font-size="18" font-family="Arial">A+</text>
          <rect x="410" y="140" width="36" height="36" rx="8" fill="rgba(37,99,235,0.15)"/>
          <text x="418" y="164" fill="#38BDF8" font-size="16" font-family="Arial">&#x2713;</text>
          <rect x="420" y="90" width="30" height="30" rx="6" fill="rgba(56,189,248,0.12)"/>
          <text x="427" y="111" fill="#93C5FD" font-size="16" font-family="Arial">&#9733;</text>
          <!-- Gear icon -->
          <circle cx="70" y="200" r="14" fill="rgba(37,99,235,0.1)" cx="70" cy="200"/>
          <text x="63" y="206" fill="#38BDF8" font-size="14" font-family="Arial">&#9881;</text>
        </svg>
      </div>
    </div>
  </div>
</section>
</template>

<!-- ============================================
     SERVICES SECTION
     ============================================ -->
<section class="services section-padding" id="services">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-th-large"></i> Layanan</div>
      <h2 class="section-title">Layanan Kami</h2>
      <p class="section-subtitle">Solusi lengkap untuk semua kebutuhan tugas akademik Anda</p>
    </div>
    <div class="services-grid">
      <div class="service-card animate-on-scroll">
        <div class="service-icon"><i class="fas fa-pen-fancy"></i></div>
        <h3>Jasa Tulis Tugas / Catatan</h3>
        <p>Penulisan tugas tangan untuk buku, binder, dan kertas folio dengan tulisan rapi dan terstruktur.</p>
        <a href="#pricing" class="service-link" onclick="showTab('tulis')">Lihat Harga <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="service-card animate-on-scroll">
        <div class="service-icon"><i class="fas fa-file-word"></i></div>
        <h3>Jasa Ketik & Edit Document</h3>
        <p>Pengetikan makalah, laporan, PPT, dan dokumen Word dengan format profesional.</p>
        <a href="#pricing" class="service-link" onclick="showTab('ketik')">Lihat Harga <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="service-card animate-on-scroll">
        <div class="service-icon"><i class="fas fa-palette"></i></div>
        <h3>Jasa Desain</h3>
        <p>Desain poster, ID Card, undangan, banner, brosur, sertifikat dan material grafis lainnya.</p>
        <a href="#pricing" class="service-link" onclick="showTab('desain')">Lihat Harga <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="service-card animate-on-scroll">
        <div class="service-icon"><i class="fas fa-code"></i></div>
        <h3>Web Design & Development</h3>
        <p>Pembuatan website modern, landing page, dan aplikasi web untuk kebutuhan bisnis Anda.</p>
        <a href="consultation.php" class="service-link" aria-label="Isi brief konsultasi Web Design dan Development">Konsultasi <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     PRICING SECTION
     ============================================ -->
<section class="pricing section-padding" id="pricing">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-tags"></i> Harga</div>
      <h2 class="section-title">Daftar Harga Layanan</h2>
      <p class="section-subtitle">Harga terjangkau dengan kualitas terbaik</p>
    </div>

    <div class="pricing-tabs animate-on-scroll">
      <button class="pricing-tab active" onclick="showTab('tulis')">✏️ Tulis Tangan</button>
      <button class="pricing-tab" onclick="showTab('ketik')">📄 Jasa Ketik</button>
      <button class="pricing-tab" onclick="showTab('desain')">🎨 Jasa Desain</button>
      <button class="pricing-tab" onclick="showTab('edit')">📝 Jasa Edit</button>
      <button class="pricing-tab" onclick="showTab('paketan')">⭐ Harga Paketan</button>
      <?php foreach ($newCategories as $category): ?><button class="pricing-tab" data-tab="category-<?= (int) $category['id'] ?>" onclick="showTab('category-<?= (int) $category['id'] ?>')"><i class="<?= htmlspecialchars($category['icon'] ?: 'fas fa-folder', ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></button><?php endforeach; ?>
    </div>

    <!-- TAB 1: Tulis Tangan -->
    <div class="pricing-panel active" id="panel-tulis">
      <div class="pricing-card animate-on-scroll">
        <div class="pricing-card-header">
          <h3><i class="fas fa-pen-nib"></i> Jasa Tulis Tangan</h3>
          <p>Penulisan tugas tangan rapi untuk semua jenis buku & kertas</p>
        </div>
        <table class="pricing-table">
          <thead>
            <tr><th>Jenis Layanan</th><th>Harga</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="item-name"><i class="fas fa-book"></i> Buku Kecil / Binder A5</span></td>
              <td><span class="price-badge">Rp 5.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-book-open"></i> Buku Besar Bigboss / Binder B5</span></td>
              <td><span class="price-badge">Rp 6.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-file-alt"></i> Kertas HVS A4 / F4</span></td>
              <td><span class="price-badge">Rp 5.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-scroll"></i> Kertas Folio / A5</span></td>
              <td><span class="price-badge">Rp 7.000 / halaman</span></td>
            </tr>
          </tbody>
        </table>
        <div class="pricing-card-footer">
          <p><i class="fas fa-check-circle"></i> Tulisan rapi & terstruktur</p>
          <button class="btn-order-sm" onclick="openModal()"><i class="fas fa-shopping-cart"></i> Pesan Layanan Ini</button>
        </div>
      </div>
    </div>

    <!-- TAB 2: Jasa Ketik -->
    <div class="pricing-panel" id="panel-ketik">
      <div class="pricing-card animate-on-scroll">
        <div class="pricing-card-header">
          <h3><i class="fas fa-keyboard"></i> Jasa Ketik</h3>
          <p>Pengetikan dokumen profesional dengan format yang tepat</p>
        </div>
        <table class="pricing-table">
          <thead>
            <tr><th>Jenis Dokumen</th><th>Harga</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="item-name"><i class="fas fa-file-lines"></i> Makalah</span></td>
              <td><span class="price-badge">Rp 3.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-presentation-screen"></i> PPT</span></td>
              <td><span class="price-badge">Rp 2.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-file-word"></i> Word</span></td>
              <td><span class="price-badge">Rp 2.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-clipboard-list"></i> Laporan</span></td>
              <td><span class="price-badge">Rp 3.000 / halaman</span></td>
            </tr>
          </tbody>
        </table>
        <div class="pricing-card-footer">
          <p><i class="fas fa-check-circle"></i> Format rapi sesuai standar</p>
          <button class="btn-order-sm" onclick="openModal()"><i class="fas fa-shopping-cart"></i> Pesan Layanan Ini</button>
        </div>
      </div>
    </div>

    <!-- TAB 3: Jasa Desain -->
    <div class="pricing-panel" id="panel-desain">
      <div class="pricing-card animate-on-scroll">
        <div class="pricing-card-header">
          <h3><i class="fas fa-paint-brush"></i> Jasa Desain</h3>
          <p>Desain grafis profesional untuk berbagai kebutuhan</p>
        </div>
        <table class="pricing-table">
          <thead>
            <tr><th>Kategori Desain</th><th>Harga</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="item-name"><i class="fas fa-image"></i> Poster</span></td>
              <td><span class="price-badge">Rp 10.000 / item</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-id-card"></i> ID Card</span></td>
              <td><span class="price-badge">Rp 10.000 / item</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-envelope-open-text"></i> Undangan</span></td>
              <td><span class="price-badge">Rp 10.000 / item</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-flag"></i> Banner</span></td>
              <td><span class="price-badge">Rp 10.000 / item</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-newspaper"></i> Brosur</span></td>
              <td><span class="price-badge">Rp 10.000 / item</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-certificate"></i> Sertifikat</span></td>
              <td><span class="price-badge">Rp 10.000 / item</span></td>
            </tr>
          </tbody>
        </table>
        <div class="pricing-card-footer">
          <p><i class="fas fa-check-circle"></i> Desain modern & kreatif</p>
          <button class="btn-order-sm" onclick="openModal()"><i class="fas fa-shopping-cart"></i> Pesan Layanan Ini</button>
        </div>
      </div>
    </div>

    <!-- TAB 4: Jasa Edit -->
    <div class="pricing-panel" id="panel-edit">
      <div class="pricing-card animate-on-scroll">
        <div class="pricing-card-header">
          <h3><i class="fas fa-pen-to-square"></i> Jasa Edit</h3>
          <p>Editing, formatting, dan penyusunan dokumen profesional</p>
        </div>
        <table class="pricing-table">
          <thead>
            <tr><th>Layanan Edit</th><th>Harga</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="item-name"><i class="fas fa-wand-magic-sparkles"></i> Desain PPT</span></td>
              <td><span class="price-badge">Rp 3.000 - Rp 5.000 / slide</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-rotate-left"></i> Ketik Ulang Document</span></td>
              <td><span class="price-badge">Rp 2.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-align-left"></i> Rapihin Format</span></td>
              <td><span class="price-badge">Rp 1.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-layer-group"></i> Penyusunan Dokumen</span></td>
              <td><span class="price-badge">Rp 2.000 / halaman</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-list-ol"></i> Penomoran Halaman</span></td>
              <td><span class="price-badge">Rp 2.000 / 10 halaman</span></td>
            </tr>
          </tbody>
        </table>
        <div class="pricing-card-footer">
          <p><i class="fas fa-check-circle"></i> Hasil editing profesional</p>
          <button class="btn-order-sm" onclick="openModal()"><i class="fas fa-shopping-cart"></i> Pesan Layanan Ini</button>
        </div>
      </div>
    </div>

    <!-- TAB 5: Harga Paketan (Featured) -->
    <div class="pricing-panel" id="panel-paketan">
      <div class="pricing-card featured animate-on-scroll">
        <div class="pricing-card-header">
          <h3><i class="fas fa-star"></i> Harga Paketan <span style="background:rgba(255,255,255,0.2);padding:3px 10px;border-radius:20px;font-size:0.75rem;margin-left:8px;">BEST VALUE</span></h3>
          <p>Paket lengkap hemat untuk tugas besar</p>
        </div>
        <table class="pricing-table">
          <thead>
            <tr><th>Paket Layanan</th><th>Harga Paket</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="item-name"><i class="fas fa-graduation-cap"></i> Makalah Tugas Sekolah</span></td>
              <td><span class="price-badge">Rp 30.000 / paket</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-building"></i> Laporan PKL</span></td>
              <td><span class="price-badge">Rp 50.000 / paket</span></td>
            </tr>
            <tr>
              <td><span class="item-name"><i class="fas fa-wand-magic-sparkles"></i> PPT Desain Morph</span></td>
              <td><span class="price-badge">Rp 60.000 / paket</span></td>
            </tr>
          </tbody>
        </table>
        <div class="pricing-card-footer">
          <p><i class="fas fa-check-circle"></i> Sudah termasuk riset & penyusunan</p>
          <button class="btn-order-sm" onclick="openModal()"><i class="fas fa-shopping-cart"></i> Pesan Layanan Ini</button>
        </div>
      </div>
    </div>
  </div>
</section>

<?php foreach ($newCategories as $category): $categoryServices = $serviceCatalog[$category['name']] ?? []; $tabKey = 'category-' . (int) $category['id']; ?>
<section class="pricing-panel" id="panel-<?= $tabKey ?>"><div class="pricing-card animate-on-scroll"><div class="pricing-card-header"><h3><i class="<?= htmlspecialchars($category['icon'] ?: 'fas fa-folder', ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h3><p>Layanan dan harga terbaru</p></div><table class="pricing-table"><thead><tr><th>Jenis Layanan</th><th>Harga</th></tr></thead><tbody><?php foreach ($categoryServices as $service): ?><tr><td><span class="item-name"><i class="<?= htmlspecialchars($service['icon'] ?: 'fas fa-file', ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></span></td><td><span class="price-badge">Rp <?= number_format((int) $service['price'], 0, ',', '.') ?><?= $service['price_max'] ? ' - Rp ' . number_format((int) $service['price_max'], 0, ',', '.') : '' ?> / <?= htmlspecialchars($service['unit'], ENT_QUOTES, 'UTF-8') ?></span></td></tr><?php endforeach; ?></tbody></table><div class="pricing-card-footer"><p><i class="fas fa-check-circle"></i> Layanan tersedia untuk order baru</p><button class="btn-order-sm" onclick="openModal()"><i class="fas fa-shopping-cart"></i> Pesan Layanan Ini</button></div></div></section>
<?php endforeach; ?>

<!-- ============================================
     CARA PESAN SECTION
     ============================================ -->
<section class="how-to section-padding" id="how-to">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-route"></i> Alur</div>
      <h2 class="section-title">Cara Pemesanan</h2>
      <p class="section-subtitle">Proses mudah dan cepat — hanya 4 langkah</p>
    </div>
    <div class="steps-grid">
      <div class="step-card animate-on-scroll">
        <div class="step-number">1</div>
        <div class="step-icon"><i class="fas fa-hand-pointer"></i></div>
        <h4>Pilih Layanan</h4>
        <p>Pilih jenis jasa yang Anda butuhkan dari daftar layanan kami.</p>
      </div>
      <div class="step-card animate-on-scroll">
        <div class="step-number">2</div>
        <div class="step-icon"><i class="fas fa-edit"></i></div>
        <h4>Isi Detail Tugas</h4>
        <p>Lengkapi informasi tugas, deadline, dan lampirkan file pendukung.</p>
      </div>
      <div class="step-card animate-on-scroll">
        <div class="step-number">3</div>
        <div class="step-icon"><i class="fas fa-wallet"></i></div>
        <h4>Bayar DP 50%</h4>
        <p>Lakukan pembayaran uang muka 50% melalui transfer atau e-wallet.</p>
      </div>
      <div class="step-card animate-on-scroll">
        <div class="step-number">4</div>
        <div class="step-icon"><i class="fas fa-circle-check"></i></div>
        <h4>Tugas Selesai</h4>
        <p>Terima tugas yang sudah jadi sesuai deadline, dengan garansi revisi.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     KETENTUAN SECTION
     ============================================ -->
<section class="terms section-padding" id="terms">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-shield-halved"></i> Ketentuan</div>
      <h2 class="section-title">Ketentuan Layanan</h2>
      <p class="section-subtitle">Pastikan Anda membaca dan memahami ketentuan berikut</p>
    </div>
    <div class="terms-grid">
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>DP 50% Sebelum Pengerjaan</h4>
          <p>Menghindari risiko hit & run dari pembeli.</p>
        </div>
      </div>
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>Wajib Mengirim Bukti Payment</h4>
          <p>Pembeli wajib mengunggah/mengirim bukti transfer.</p>
        </div>
      </div>
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>Tidak Boleh Cancel Saat Pengerjaan</h4>
          <p>Pembatalan tidak diizinkan saat proses berlangsung.</p>
        </div>
      </div>
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>Free Revisi Maksimal 3x</h4>
          <p>Garansi perbaikan maksimal 3 kali tanpa biaya tambahan.</p>
        </div>
      </div>
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>Jaminan Uang Kembali 100%</h4>
          <p>Garansi refund penuh jika penjoki tidak menyelesaikan tugas.</p>
        </div>
      </div>
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>Data & Privasi Dijamin Aman 100%</h4>
          <p>Kerahasiaan identitas dan data Anda sepenuhnya terjamin.</p>
        </div>
      </div>
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>Deadline Sesuai Kesepakatan</h4>
          <p>Pengerjaan selesai tepat waktu sesuai yang disepakati.</p>
        </div>
      </div>
      <div class="term-item animate-on-scroll">
        <div class="term-check"><i class="fas fa-check"></i></div>
        <div>
          <h4>Update Pengerjaan Transparan</h4>
          <p>Anda mendapatkan informasi perkembangan tugas selama proses berlangsung.</p>
        </div>
      </div>
    </div>
    <!-- Warning box -->
    <div class="term-warning animate-on-scroll">
      <div class="term-warning-icon"><i class="fas fa-triangle-exclamation"></i></div>
      <div>
        <h4>⚠️ TIDAK MENERIMA JOKI UJIAN</h4>
        <p>Demi integritas akademik, kami secara tegas tidak melayani jasa joki untuk ujian (UTS, UAS, Quiz, maupun ujian live). Kebijakan ini berlaku tanpa pengecualian.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     TESTIMONIALS SECTION
     ============================================ -->
<section class="testimonials section-padding" id="testimonials">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <div class="section-label"><i class="fas fa-star"></i> Testimoni</div>
      <h2 class="section-title">Apa Kata Pelanggan Kami</h2>
      <p class="section-subtitle">Kepuasan pelanggan adalah prioritas utama kami</p>
    </div>
    <div class="proof-section animate-on-scroll">
      <div class="proof-heading">
        <div>
          <div class="proof-kicker"><i class="fas fa-sparkles"></i> Cerita nyata pelanggan kami</div>
          <h3>Hasil yang Membuat Pelanggan Kembali</h3>
          <p>Setiap foto menyimpan cerita tentang proses yang rapi, komunikasi yang cepat, dan hasil yang dikerjakan dengan sepenuh perhatian.</p>
        </div>
        <span class="proof-trust"><i class="fas fa-shield-halved"></i> Dipercaya pelanggan</span>
      </div>
      <div class="proof-reviews" aria-label="Ulasan pelanggan" role="region">
        <div class="proof-review-track">
          <div class="proof-review"><i class="fas fa-star"></i><span>"Hasilnya rapi dan selesai sesuai deadline."</span><strong>Andi S.</strong></div>
          <div class="proof-review"><i class="fas fa-star"></i><span>"Admin cepat merespons, prosesnya jelas dari awal."</span><strong>Nurul R.</strong></div>
          <div class="proof-review"><i class="fas fa-star"></i><span>"Desainnya profesional dan sangat membantu presentasi."</span><strong>Dimas F.</strong></div>
          <div class="proof-review"><i class="fas fa-star"></i><span>"Hasil sesuai brief, revisinya juga ditangani dengan baik."</span><strong>Fajar A.</strong></div>
          <div class="proof-review" aria-hidden="true"><i class="fas fa-star"></i><span>"Hasilnya rapi dan selesai sesuai deadline."</span><strong>Andi S.</strong></div>
          <div class="proof-review" aria-hidden="true"><i class="fas fa-star"></i><span>"Admin cepat merespons, prosesnya jelas dari awal."</span><strong>Nurul R.</strong></div>
          <div class="proof-review" aria-hidden="true"><i class="fas fa-star"></i><span>"Desainnya profesional dan sangat membantu presentasi."</span><strong>Dimas F.</strong></div>
          <div class="proof-review" aria-hidden="true"><i class="fas fa-star"></i><span>"Hasil sesuai brief, revisinya juga ditangani dengan baik."</span><strong>Fajar A.</strong></div>
        </div>
      </div>
      <div class="proof-slider" id="proofSlider">
        <button class="proof-arrow prev" type="button" onclick="changeProof(-1)" aria-label="Foto sebelumnya"><i class="fas fa-chevron-left"></i></button>
        <button class="proof-arrow next" type="button" onclick="changeProof(1)" aria-label="Foto berikutnya"><i class="fas fa-chevron-right"></i></button>
        <div class="proof-stage">
          <img id="proofImage" src="<?= htmlspecialchars($testimonialSlides[0]['src'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($testimonialSlides[0]['title'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="proof-caption"><strong id="proofTitle"><?= htmlspecialchars($testimonialSlides[0]['title'], ENT_QUOTES, 'UTF-8') ?></strong><span id="proofSubtitle"><?= htmlspecialchars($testimonialSlides[0]['subtitle'], ENT_QUOTES, 'UTF-8') ?></span></div>
        <div class="proof-dots" role="tablist" aria-label="Pilih foto testimoni">
          <?php foreach ($testimonialSlides as $index => $slide): ?>
            <button class="proof-dot<?= $index === 0 ? ' active' : '' ?>" type="button" onclick="showProof(<?= $index ?>)" aria-label="Foto testimoni <?= $index + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================
     CTA SECTION
     ============================================ -->
<section class="cta-section">
  <div class="container animate-on-scroll">
    <h2>Siap Menyelesaikan Tugas Anda?</h2>
    <p>Hubungi kami sekarang dan dapatkan pengerjaan tugas yang cepat, rapi, dan terpercaya.</p>
    <button class="btn-primary" onclick="openModal()">
      <i class="fas fa-paper-plane"></i> Pesan Sekarang
    </button>
  </div>
</section>

<!-- ============================================
     FOOTER
     ============================================ -->
<footer class="footer" id="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="logo">
          <i class="fas fa-bolt logo-icon"></i>
          <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <p><?= htmlspecialchars($siteDescription, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="footer-social">
          <a href="<?= htmlspecialchars(getSetting('instagram', '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="<?= htmlspecialchars(getSetting('facebook', '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="<?= htmlspecialchars(getSetting('tiktok', '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
          <a href="<?= htmlspecialchars(getSetting('youtube', '#'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Menu Cepat</h4>
        <ul>
          <li><a href="#hero"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Beranda</a></li>
          <li><a href="#services"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Layanan</a></li>
          <li><a href="#pricing"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Daftar Harga</a></li>
          <li><a href="#how-to"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Cara Pesan</a></li>
          <li><a href="#terms"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Ketentuan</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Layanan Kami</h4>
        <ul>
          <li><a href="#pricing" onclick="showTab('tulis')"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Jasa Tulis Tangan</a></li>
          <li><a href="#pricing" onclick="showTab('ketik')"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Jasa Ketik</a></li>
          <li><a href="#pricing" onclick="showTab('desain')"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Jasa Desain</a></li>
          <li><a href="#pricing" onclick="showTab('edit')"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Jasa Edit</a></li>
          <li><a href="#pricing" onclick="showTab('paketan')"><i class="fas fa-chevron-right" style="font-size:0.65rem"></i> Harga Paketan</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Hubungi Kami</h4>
        <ul class="footer-contact">
          <li>
            <i class="fab fa-whatsapp"></i>
            <span><strong><?= htmlspecialchars($whatsappDisplay, ENT_QUOTES, 'UTF-8') ?></strong><br>Chat via WhatsApp</span>
          </li>
          <li>
            <i class="fas fa-location-dot"></i>
            <span><?= nl2br(htmlspecialchars($location, ENT_QUOTES, 'UTF-8')) ?></span>
          </li>
          <li>
            <i class="fas fa-envelope"></i>
            <span><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?></span>
          </li>
        </ul>
      </div>
    </div>
    <div class="footer-payments">
      <span style="color:#64748B;font-size:0.82rem;font-weight:600;margin-right:8px;">Pembayaran Aman:</span>
      <?php foreach ($paymentMethods as $paymentMethod): ?><span class="payment-badge"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?>
    </div>
    <div class="footer-bottom">
      © <?= htmlspecialchars($copyrightYear, ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>. All Rights Reserved. | <?= htmlspecialchars($siteTagline, ENT_QUOTES, 'UTF-8') ?>
    </div>
  </div>
</footer>

<!-- ============================================
     FLOATING WHATSAPP BUTTON
     ============================================ -->
<div class="wa-float">
  <span class="wa-tooltip">Chat via WhatsApp</span>
  <a href="https://wa.me/<?= htmlspecialchars(formatWhatsAppNumber($whatsappNumber), ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Halo Admin ' . $siteName . ', saya ingin bertanya tentang jasa joki tugas.') ?>" target="_blank" rel="noopener">
    <i class="fab fa-whatsapp"></i>
  </a>
</div>

<!-- ============================================
     ORDER MODAL
     ============================================ -->
<div class="modal-overlay" id="orderModal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-shopping-cart"></i> Form Pemesanan</h3>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" class="form-control" placeholder="Masukkan nama lengkap Anda">
      </div>
      <div class="form-group">
        <label for="whatsapp">Nomor WhatsApp aktif</label>
        <input type="tel" id="whatsapp" class="form-control" placeholder="Contoh: 08123456789">
      </div>
      <div class="form-group">
        <label for="layanan">Pilih Layanan</label>
        <select id="layanan" class="form-control" onchange="calculatePrice()">
          <option value="">-- Pilih Layanan --</option>
          <?php foreach ($serviceCatalog as $categoryName => $categoryServices): ?><optgroup label="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>"><?php foreach ($categoryServices as $service): ?><option value="service_<?= (int) $service['id'] ?>" data-service-id="<?= (int) $service['id'] ?>" data-price="<?= (int) $service['price'] ?>" data-unit="<?= htmlspecialchars($service['unit'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?> — Rp <?= number_format((int) $service['price'], 0, ',', '.') ?><?= $service['price_max'] ? ' - Rp ' . number_format((int) $service['price_max'], 0, ',', '.') : '' ?>/<?= htmlspecialchars($service['unit'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></optgroup><?php endforeach; ?>
        </select>
        <div class="mobile-service-picker">
          <button class="mobile-service-trigger" id="mobileServiceTrigger" type="button" aria-expanded="false">
            <span id="mobileServiceValue">-- Pilih Layanan --</span><i class="fas fa-chevron-down" aria-hidden="true"></i>
          </button>
          <div class="mobile-service-list" id="mobileServiceList">
            <?php foreach ($serviceCatalog as $categoryName => $categoryServices): ?>
              <div class="mobile-service-category"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></div>
              <?php foreach ($categoryServices as $service): ?>
                <button class="mobile-service-option" type="button" data-value="service_<?= (int) $service['id'] ?>">
                  <span><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="mobile-service-price">Rp <?= number_format((int) $service['price'], 0, ',', '.') ?><?= $service['price_max'] ? ' - ' . number_format((int) $service['price_max'], 0, ',', '.') : '' ?>/<?= htmlspecialchars($service['unit'], ENT_QUOTES, 'UTF-8') ?></span>
                </button>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="jumlah">Jumlah</label>
        <input type="number" id="jumlah" class="form-control" value="1" min="1" onchange="calculatePrice()" oninput="calculatePrice()">
      </div>
      <div class="form-group">
        <label for="deadline_at">Tanggal &amp; Jam Deadline</label>
        <div class="deadline-picker">
          <input type="datetime-local" id="deadline_at" class="form-control" required>
          <i class="fas fa-calendar-days"></i>
        </div>
        <small class="form-hint">Pilih tanggal dan jam target penyelesaian tugas.</small>
      </div>
      <div class="form-group">
        <label for="detail">Detail Tugas / Catatan Khusus</label>
        <textarea id="detail" class="form-control" placeholder="Jelaskan detail tugas, deadline, dan catatan lainnya..."></textarea>
      </div>
      <div class="form-group">
        <label>Upload File Pendukung (opsional)</label>
        <input type="file" id="file_attachment" name="file_attachment" class="form-control" style="padding:8px">
      </div>
      <div class="calc-box" id="calcBox" style="display:none;">
        <div class="calc-row">
          <span>Harga per unit</span>
          <span id="calcUnit">Rp 0</span>
        </div>
        <div class="calc-row">
          <span>Jumlah</span>
          <span id="calcQty">0</span>
        </div>
        <div class="calc-row total">
          <span>Total Harga</span>
          <span id="calcTotal">Rp 0</span>
        </div>
        <div class="calc-row">
          <span>Wajib DP (<?= max(0, min(100, (int) getSetting('dp_percentage', '50'))) ?>%)</span>
          <span class="dp" id="calcDp">Rp 0</span>
        </div>
      </div>
      <button class="btn-submit" onclick="submitOrder()">
        <i class="fab fa-whatsapp"></i> Kirim Pesanan via WhatsApp
      </button>
    </div>
  </div>
</div>

<!-- ============================================
     JAVASCRIPT
     ============================================ -->
<script>
// ---- Header scroll effect ----
window.addEventListener('scroll', () => {
  const header = document.getElementById('header');
  header.classList.toggle('scrolled', window.scrollY > 50);
});

// ---- Mobile nav toggle ----
function toggleNav() {
  const nav = document.getElementById('navLinks');
  const toggle = document.querySelector('.mobile-toggle');
  const isOpen = nav.classList.toggle('is-open');
  toggle.setAttribute('aria-expanded', String(isOpen));
}

document.querySelectorAll('#navLinks a').forEach((link) => {
  link.addEventListener('click', () => {
    document.getElementById('navLinks').classList.remove('is-open');
    document.querySelector('.mobile-toggle').setAttribute('aria-expanded', 'false');
  });
});

const mobileServiceTrigger = document.getElementById('mobileServiceTrigger');
const mobileServiceList = document.getElementById('mobileServiceList');
const mobileServiceValue = document.getElementById('mobileServiceValue');
const serviceSelect = document.getElementById('layanan');
if (mobileServiceTrigger && mobileServiceList && serviceSelect) {
  mobileServiceTrigger.addEventListener('click', () => {
    const isOpen = mobileServiceList.classList.toggle('is-open');
    mobileServiceTrigger.setAttribute('aria-expanded', String(isOpen));
  });

  mobileServiceList.querySelectorAll('.mobile-service-option').forEach((optionButton) => {
    optionButton.addEventListener('click', () => {
      serviceSelect.value = optionButton.dataset.value;
      serviceSelect.dispatchEvent(new Event('change'));
      mobileServiceValue.textContent = optionButton.querySelector('span').textContent;
      mobileServiceList.querySelectorAll('.mobile-service-option').forEach((item) => item.classList.remove('is-selected'));
      optionButton.classList.add('is-selected');
      mobileServiceList.classList.remove('is-open');
      mobileServiceTrigger.setAttribute('aria-expanded', 'false');
    });
  });
}

// ---- Pricing tabs ----
function showTab(tab) {
  document.querySelectorAll('.pricing-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.pricing-tab').forEach(t => t.classList.remove('active'));

  const panel = document.getElementById('panel-' + tab);
  if (panel) panel.classList.add('active');

  const tabNames = { tulis: '✏️ Tulis Tangan', ketik: '📄 Jasa Ketik', desain: '🎨 Jasa Desain', edit: '📝 Jasa Edit', paketan: '⭐ Harga Paketan' };
  document.querySelectorAll('.pricing-tab').forEach(t => {
    if (t.dataset.tab === tab || t.textContent.trim() === tabNames[tab]) t.classList.add('active');
  });
}

// ---- Order modal ----
function openModal() {
  document.getElementById('orderModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('orderModal').classList.remove('active');
  document.body.style.overflow = '';
}
const proofSlides = <?= json_encode($testimonialSlides, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
let proofIndex = 0;
let proofSwipeStart = null;
let proofSwipeMoved = false;
let proofChangeTimer;
function showProof(index, direction = 1) {
  proofIndex = (index + proofSlides.length) % proofSlides.length;
  const slide = proofSlides[proofIndex];
  const stage = document.querySelector('.proof-stage');
  const caption = document.querySelector('.proof-caption');
  stage.classList.remove('is-changing', 'next', 'prev');
  caption.classList.remove('is-changing');
  document.getElementById('proofImage').src = slide.src;
  document.getElementById('proofImage').alt = slide.title;
  document.getElementById('proofTitle').textContent = slide.title;
  document.getElementById('proofSubtitle').textContent = slide.subtitle;
  document.querySelectorAll('.proof-dot').forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === proofIndex));
  requestAnimationFrame(() => {
    stage.classList.add('is-changing', direction > 0 ? 'next' : 'prev');
    caption.classList.add('is-changing');
  });
  clearTimeout(proofChangeTimer);
  proofChangeTimer = setTimeout(() => {
    stage.classList.remove('is-changing', 'next', 'prev');
    caption.classList.remove('is-changing');
  }, 720);
}
function changeProof(direction) { showProof(proofIndex + direction, direction); }
const proofStage = document.querySelector('.proof-stage');
proofStage.addEventListener('pointerdown', (event) => {
  proofSwipeStart = { x: event.clientX, y: event.clientY };
  proofSwipeMoved = false;
  proofStage.setPointerCapture(event.pointerId);
});
proofStage.addEventListener('pointermove', (event) => {
  if (!proofSwipeStart) return;
  const distanceX = event.clientX - proofSwipeStart.x;
  const distanceY = event.clientY - proofSwipeStart.y;
  proofSwipeMoved = Math.abs(distanceX) > 12 && Math.abs(distanceX) > Math.abs(distanceY);
});
proofStage.addEventListener('pointerup', (event) => {
  if (!proofSwipeStart) return;
  const distanceX = event.clientX - proofSwipeStart.x;
  if (proofSwipeMoved && Math.abs(distanceX) > 45) {
    changeProof(distanceX < 0 ? 1 : -1);
    event.preventDefault();
  }
  proofSwipeStart = null;
});
proofStage.addEventListener('click', (event) => {
  if (proofSwipeMoved) {
    event.preventDefault();
    proofSwipeMoved = false;
    return;
  }
  openProof();
});
function openProof() {
  const image = document.getElementById('proofImage');
  const modalImage = document.getElementById('proofModalImage');
  modalImage.src = image.src;
  modalImage.alt = image.alt;
  document.getElementById('proofModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeProof(event) {
  if (event && event.target !== event.currentTarget) return;
  document.getElementById('proofModal').classList.remove('active');
  document.body.style.overflow = '';
}
document.getElementById('orderModal').addEventListener('click', (e) => {
  if (e.target === e.currentTarget) closeModal();
});

// ---- Price calculation ----
function calculatePrice() {
  const select = document.getElementById('layanan');
  const qty = parseInt(document.getElementById('jumlah').value) || 0;
  const option = select.options[select.selectedIndex];
  const calcBox = document.getElementById('calcBox');

  if (!option || !option.dataset.price) {
    calcBox.style.display = 'none';
    return;
  }
  const price = parseInt(option.dataset.price);
  const unit = option.dataset.unit;
  const total = price * qty;
  const dp = Math.ceil(total * <?= max(0, min(100, (int) getSetting('dp_percentage', '50'))) ?> / 100);

  document.getElementById('calcUnit').textContent = formatRp(price) + ' / ' + unit;
  document.getElementById('calcQty').textContent = qty + ' ' + unit;
  document.getElementById('calcTotal').textContent = formatRp(total);
  document.getElementById('calcDp').textContent = formatRp(dp);
  calcBox.style.display = 'block';
}
function formatRp(n) {
  return 'Rp ' + n.toLocaleString('id-ID');
}

const deadlineField = document.getElementById('deadline_at');
if (deadlineField) {
  const minimumDeadline = new Date();
  minimumDeadline.setMinutes(minimumDeadline.getMinutes() + 30);
  const localMinimum = new Date(minimumDeadline.getTime() - minimumDeadline.getTimezoneOffset() * 60000);
  deadlineField.min = localMinimum.toISOString().slice(0, 16);
}

// ---- Submit order via WhatsApp ----
async function submitOrder() {
  const nama = document.getElementById('nama').value.trim();
  const wa = document.getElementById('whatsapp').value.trim();
  const select = document.getElementById('layanan');
  const option = select.options[select.selectedIndex];
  const qty = parseInt(document.getElementById('jumlah').value) || 0;
  const deadlineInput = document.getElementById('deadline_at');
  const deadlineValue = deadlineInput.value;
  const detail = document.getElementById('detail').value.trim();
  const fileInput = document.getElementById('file_attachment');

  if (!nama || !wa || !option.value || !option.dataset.serviceId || !deadlineValue || qty < 1) {
    alert('Harap lengkapi Nama, WhatsApp, Layanan, serta tanggal dan jam deadline.');
    return;
  }

  const deadlineDate = new Date(deadlineValue);
  if (Number.isNaN(deadlineDate.getTime()) || deadlineDate <= new Date()) {
    alert('Deadline harus berupa tanggal dan jam yang masih akan datang.');
    deadlineInput.focus();
    return;
  }
  const deadlineFormatted = deadlineDate.toLocaleString('id-ID', {
    dateStyle: 'full',
    timeStyle: 'short'
  });

  const price = parseInt(option.dataset.price) || 0;
  const total = price * qty;
  const dpPercentage = <?= max(0, min(100, (int) getSetting('dp_percentage', '50'))) ?>;
  const dp = Math.ceil(total * dpPercentage / 100);
  const adminWhatsApp = <?= json_encode(formatWhatsAppNumber(getSetting('whatsapp', WA_NUMBER))) ?>;

  const orderData = new FormData();
  orderData.append('nama', nama);
  orderData.append('whatsapp', wa);
  orderData.append('service_id', option.dataset.serviceId);
  orderData.append('layanan', option.text.split(' — ')[0]);
  orderData.append('jumlah', qty);
  orderData.append('deadline_at', deadlineValue);
  orderData.append('detail', detail);
  orderData.append('total_price', total);
  if (fileInput.files[0]) orderData.append('file_attachment', fileInput.files[0]);

  const submitButton = document.querySelector('.btn-submit');
  submitButton.disabled = true;
  submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengunggah pesanan...';

  try {
    const response = await fetch('order.php', { method: 'POST', body: orderData });
    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Pesanan gagal disimpan.');
    }
    let msg = `Halo Admin VELOCITY SYSTEM AI,\n\n`;
    msg += `Saya ingin memesan jasa:\n`;
    msg += `- Kode Order: ${result.order_code}\n`;
    msg += `- Nama: ${nama}\n`;
    msg += `- WhatsApp: ${wa}\n`;
    msg += `- Layanan: ${option.text}\n`;
    msg += `- Jumlah: ${qty}\n`;
    msg += `- Deadline: ${deadlineFormatted}\n`;
    msg += `- Total Harga: ${formatRp(total)}\n`;
    msg += `- Wajib DP (${dpPercentage}%): ${formatRp(dp)}\n\n`;
    if (detail) msg += `Catatan: ${detail}\n\n`;
    msg += `Mohon diproses, terima kasih!`;
    if (result.file_url) {
      msg += `\n\nLampiran tugas: ${result.file_url}`;
    }
    window.open('https://wa.me/' + adminWhatsApp + '?text=' + encodeURIComponent(msg), '_blank');
    closeModal();
  } catch (error) {
    alert(error.message || 'Pesanan gagal dikirim. Silakan coba lagi.');
  } finally {
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fab fa-whatsapp"></i> Kirim Pesanan via WhatsApp';
  }
}

// ---- Hero carousel ----
const heroCarousel = document.getElementById('heroCarousel');
if (heroCarousel) {
  const heroSlides = heroCarousel.querySelector('.hero-slides');
  const heroSlideItems = [...heroCarousel.querySelectorAll('.hero-slide')];
  const heroDots = [...heroCarousel.querySelectorAll('.carousel-dot')];
  const heroCaption = heroCarousel.querySelector('.hero-slide-caption');
  const heroCaptions = [
    'Open order Velocity System AI',
    'Pahami keunggulan layanan kami',
    'Tim kreatif siap membantu kebutuhan Anda',
    'Promo spesial Ramadan untuk waktu terbatas',
    'Jasa tulis tangan secepat kilat',
    'Dapatkan promo dan potongan harga spesial',
    'Pengumuman layanan terbaru kami'
  ];
  let heroIndex = 0;
  let heroTimer;
  let pointerStart = 0;

  const showHeroSlide = (index) => {
    heroIndex = (index + heroSlideItems.length) % heroSlideItems.length;
    heroSlides.style.transform = `translate3d(-${heroIndex * 100}%, 0, 0)`;
    heroSlideItems.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === heroIndex));
    heroDots.forEach((dot, dotIndex) => {
      const active = dotIndex === heroIndex;
      dot.classList.toggle('active', active);
      dot.setAttribute('aria-selected', String(active));
    });
    heroCaption.textContent = heroCaptions[heroIndex];
  };

  const stopHeroAutoplay = () => clearInterval(heroTimer);
  const startHeroAutoplay = () => {
    stopHeroAutoplay();
    heroTimer = setInterval(() => showHeroSlide(heroIndex + 1), 5200);
  };

  heroCarousel.querySelector('[data-carousel-prev]').addEventListener('click', () => {
    showHeroSlide(heroIndex - 1);
    startHeroAutoplay();
  });
  heroCarousel.querySelector('[data-carousel-next]').addEventListener('click', () => {
    showHeroSlide(heroIndex + 1);
    startHeroAutoplay();
  });
  heroDots.forEach((dot) => dot.addEventListener('click', () => {
    showHeroSlide(Number(dot.dataset.carouselDot));
    startHeroAutoplay();
  }));
  heroCarousel.addEventListener('pointerdown', (event) => {
    pointerStart = event.clientX;
    heroCarousel.setPointerCapture(event.pointerId);
    stopHeroAutoplay();
  });
  heroCarousel.addEventListener('pointerup', (event) => {
    const distance = event.clientX - pointerStart;
    if (Math.abs(distance) > 45) showHeroSlide(heroIndex + (distance < 0 ? 1 : -1));
    startHeroAutoplay();
  });
  heroCarousel.addEventListener('pointercancel', startHeroAutoplay);
  heroCarousel.addEventListener('mouseenter', stopHeroAutoplay);
  heroCarousel.addEventListener('mouseleave', startHeroAutoplay);
  heroCarousel.addEventListener('focusin', stopHeroAutoplay);
  heroCarousel.addEventListener('focusout', startHeroAutoplay);
  heroCarousel.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') showHeroSlide(heroIndex - 1);
    if (event.key === 'ArrowRight') showHeroSlide(heroIndex + 1);
  });
  document.addEventListener('visibilitychange', () => document.hidden ? stopHeroAutoplay() : startHeroAutoplay());
  startHeroAutoplay();
}

// ---- Intersection Observer for scroll animations ----
const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry, index) => {
    if (entry.isIntersecting) {
      setTimeout(() => {
        entry.target.classList.add('visible');
      }, index * 80);
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
</script>

<?php include __DIR__ . '/../chat-widget.php'; ?>
</body>
</html>
