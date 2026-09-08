<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pilih Tampilan Admin | Velocity System AI</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--ink:#17232e;--muted:#71808c;--paper:#f4f8f7;--teal:#007c83;--teal-dark:#07555d;--mint:#d9f2ed;--white:#fff}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:linear-gradient(135deg,#f4f8f7,#dcebed);color:var(--ink);font:15px 'DM Sans',sans-serif}.choice-page{width:min(700px,100%);padding:clamp(28px,6vw,52px);border:1px solid #cfe1e1;border-radius:22px;background:rgba(255,255,255,.9);box-shadow:0 25px 65px rgba(25,53,62,.13);text-align:center}.brand{margin:0 0 12px;color:var(--teal);font:700 .82rem 'Space Grotesk';letter-spacing:.12em;text-transform:uppercase}.title{margin:0 0 12px;font:700 clamp(1.8rem,4vw,2.6rem) 'Space Grotesk'}.copy{max-width:520px;margin:0 auto 28px;color:var(--muted);line-height:1.65}.choices{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.choice{display:grid;gap:10px;min-height:150px;place-items:center;padding:22px 16px;border:1px solid #cfe1e1;border-radius:14px;background:#f8fcfb;color:var(--ink);text-decoration:none;transition:transform .2s ease,background .2s ease,border-color .2s ease}.choice:hover,.choice:focus-visible{transform:translateY(-4px);border-color:var(--teal);background:var(--mint);outline:0}.choice i{color:var(--teal);font-size:2.5rem}.choice strong{font-size:1.05rem}.choice span{color:var(--muted);font-size:.82rem}@media(max-width:520px){.choice-page{border-radius:16px}.choices{grid-template-columns:1fr}.choice{min-height:112px;grid-template-columns:auto 1fr;place-items:center start;text-align:left}.choice i{grid-row:span 2}}
</style>
</head>
<body>
<main class="choice-page">
  <p class="brand"><i class="fa-solid fa-bolt"></i> Velocity Admin</p>
  <h1 class="title">Pilih tampilan panel admin</h1>
  <p class="copy">Gunakan tampilan yang sesuai dengan perangkat untuk mengelola website dengan lebih nyaman.</p>
  <div class="choices">
    <a class="choice" href="admin.php?device=mobile"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i><strong>HP / Mobile</strong><span>Menu dan data dibuat ringkas</span></a>
    <a class="choice" href="admin.php?device=laptop"><i class="fa-solid fa-laptop" aria-hidden="true"></i><strong>Laptop / Computer</strong><span>Ruang kerja admin lebih luas</span></a>
  </div>
</main>
</body>
</html>
