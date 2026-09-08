<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pilih Tampilan | Velocity System AI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--navy:#0f172a;--blue:#2563eb;--sky:#38bdf8;--white:#fff;--muted:#cbd5e1}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:linear-gradient(135deg,var(--navy),#1e3a8a);color:var(--white);font:15px 'DM Sans',sans-serif}.choice-page{width:min(700px,100%);padding:clamp(28px,6vw,52px);border:1px solid rgba(255,255,255,.18);border-radius:24px;background:rgba(255,255,255,.1);box-shadow:0 28px 80px rgba(0,0,0,.28);text-align:center;backdrop-filter:blur(16px)}.kicker{margin:0 0 12px;color:var(--sky);font:700 .78rem 'Space Grotesk';letter-spacing:.14em;text-transform:uppercase}.title{margin:0 0 12px;font:700 clamp(1.8rem,4vw,2.65rem) 'Space Grotesk'}.copy{max-width:520px;margin:0 auto 28px;color:var(--muted);line-height:1.65}.choices{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.choice{display:grid;gap:10px;min-height:150px;place-items:center;padding:22px 16px;border:1px solid rgba(255,255,255,.2);border-radius:16px;background:rgba(255,255,255,.08);color:var(--white);text-decoration:none;transition:transform .2s ease,background .2s ease,border-color .2s ease}.choice:hover,.choice:focus-visible{transform:translateY(-4px);border-color:var(--sky);background:rgba(56,189,248,.18);outline:0}.choice i{color:var(--sky);font-size:2.6rem}.choice strong{font-size:1.05rem}.choice span{color:var(--muted);font-size:.82rem}@media(max-width:520px){.choice-page{border-radius:18px}.choices{grid-template-columns:1fr}.choice{min-height:116px;grid-template-columns:auto 1fr;place-items:center start;text-align:left}.choice i{grid-row:span 2}}
</style>
</head>
<body>
<main class="choice-page">
  <p class="kicker">Velocity System AI</p>
  <h1 class="title">Buka website dengan tampilan apa?</h1>
  <p class="copy">Pilih perangkat yang ingin digunakan agar halaman tampil lebih rapi dan nyaman.</p>
  <div class="choices">
    <a class="choice" href="index.php?device=mobile"><i class="fas fa-mobile-screen-button" aria-hidden="true"></i><strong>HP / Mobile</strong><span>Tampilan khusus layar kecil</span></a>
    <a class="choice" href="index.php?device=laptop"><i class="fas fa-laptop" aria-hidden="true"></i><strong>Laptop / Computer</strong><span>Tampilan khusus layar besar</span></a>
  </div>
</main>
</body>
</html>
