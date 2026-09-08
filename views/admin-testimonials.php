<?php $e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Testimoni | Velocity Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--ink:#17232e;--muted:#71808c;--paper:#f5f7f8;--line:#dce5e9;--teal:#007c83;--teal-dark:#07555d;--mint:#d9f2ed;--red:#c94b4b;--white:#fff;--shadow:0 14px 35px rgba(25,53,62,.09)}*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:14px 'DM Sans',sans-serif}h1,h2{font-family:'Space Grotesk',sans-serif;margin:0}a{color:inherit;text-decoration:none}.shell{display:flex;min-height:100vh}.side{width:244px;background:var(--teal-dark);color:#d7e7e7;padding:27px 15px;flex-shrink:0}.brand{color:#fff;font:700 20px 'Space Grotesk';letter-spacing:.04em;margin-bottom:38px;padding:0 12px}.brand i{margin-right:8px}.side nav a,.side-bottom a{display:flex;gap:12px;align-items:center;padding:12px;border-radius:8px;margin:4px 0;color:#c3d9da}.side nav a:hover,.side nav a.active,.side-bottom a:hover{background:rgba(255,255,255,.12);color:#fff}.side nav i{width:18px;text-align:center}.side-bottom{margin-top:calc(100vh - 360px);padding:20px 12px 0;border-top:1px solid rgba(255,255,255,.14)}.main{flex:1;min-width:0}.top{display:flex;align-items:center;justify-content:space-between;padding:27px 34px;background:#fff;border-bottom:1px solid var(--line)}.top h1{font-size:25px}.user{display:flex;align-items:center;gap:12px;color:var(--muted)}.avatar{display:grid;place-items:center;width:36px;height:36px;border-radius:50%;background:var(--mint);color:var(--teal);font-weight:700}.content{padding:34px}.panel{padding:26px;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow)}.panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:24px}.panel-head h2{font-size:21px}.panel-head p{margin:7px 0 0;color:var(--muted)}.btn{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:8px;padding:11px 16px;background:var(--teal);color:#fff;font:600 14px 'DM Sans';cursor:pointer}.btn:hover{background:var(--teal-dark)}.btn.light{background:#eef7f5;color:var(--teal-dark)}.btn.danger{background:#fff0f0;color:var(--red);padding:7px 10px}.alert{padding:12px 14px;border-radius:8px;margin-bottom:18px;background:var(--mint);color:var(--teal-dark)}.upload{display:grid;gap:13px;margin-bottom:30px}.upload input[type=file]{width:100%;padding:16px;border:1px dashed var(--teal);border-radius:8px;background:#f7fffd;font:inherit}.note{color:var(--muted);font-size:13px}.gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:18px}.photo{overflow:hidden;border:1px solid var(--line);border-radius:10px;background:#fff}.photo img{display:block;width:100%;height:210px;object-fit:cover}.photo footer{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:11px}.photo small{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--muted)}form.inline{margin:0}@media(max-width:760px){.side{width:70px;padding:20px 10px}.side .brand span,.side nav span,.side-bottom span{display:none}.side .brand{padding:0;text-align:center}.side nav a,.side-bottom a{justify-content:center}.content{padding:20px}.top{padding:20px}.user span{display:none}.panel-head{display:block}.panel-head .btn{margin-top:15px}}
</style>
<style>
body.admin-mobile .shell{display:block}body.admin-mobile .side{width:100%;padding:12px 14px;display:flex;align-items:center;gap:12px;overflow-x:auto}body.admin-mobile .side .brand{flex:0 0 auto;margin:0;padding:0 4px;font-size:0}body.admin-mobile .side .brand i{font-size:20px;margin:0}body.admin-mobile .side nav{display:flex;gap:6px;min-width:max-content}body.admin-mobile .side nav a{margin:0;padding:9px 11px;gap:7px;white-space:nowrap}body.admin-mobile .side nav span,body.admin-mobile .side-bottom span{display:inline}body.admin-mobile .side-bottom{margin:0;padding:0;border:0;flex:0 0 auto}body.admin-mobile .side-bottom a{margin:0;padding:9px}body.admin-mobile .top{padding:14px 16px}body.admin-mobile .top h1{font-size:18px}body.admin-mobile .user span{display:none}body.admin-mobile .content{padding:18px 14px}body.admin-mobile .panel{padding:18px}.gallery{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
</style>
</head>
<body class="admin-<?= $e($adminDevice ?? 'laptop') ?>">
<div class="shell">
  <aside class="side">
    <div class="brand"><i class="fa-solid fa-bolt"></i><span>VELOCITY ADMIN</span></div>
    <nav>
      <a href="admin.php"><i class="fa-solid fa-chart-pie"></i><span>Ringkasan</span></a>
      <a href="admin.php?tab=orders"><i class="fa-solid fa-inbox"></i><span>Pesanan</span></a>
      <a href="admin.php?tab=services"><i class="fa-solid fa-tags"></i><span>Layanan & Harga</span></a>
      <a href="admin.php?tab=services&amp;categories=1"><i class="fa-solid fa-layer-group"></i><span>Kategori Layanan</span></a>
      <a class="active" href="admin.php?tab=testimonials"><i class="fa-solid fa-images"></i><span>Testimoni</span></a>
      <a href="admin.php?tab=settings"><i class="fa-solid fa-sliders"></i><span>Pengaturan</span></a>
    </nav>
    <div class="side-bottom"><a href="admin.php?logout=1"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Keluar</span></a></div>
  </aside>
  <main class="main">
    <header class="top"><h1>Foto testimoni</h1><div class="user"><span>Halo, <?= $e($_SESSION['admin_username']) ?></span><div class="avatar">A</div></div></header>
    <section class="content">
      <?php if ($flash): ?><div class="alert"><i class="fa-solid fa-circle-check"></i> <?= $e($flash['message']) ?></div><?php endif; ?>
      <div class="panel">
        <div class="panel-head"><div><h2>Kelola foto kepuasan pelanggan</h2><p>Tambahkan foto baru, dan foto tersebut otomatis tampil di slider testimoni website.</p></div><a class="btn light" href="index.php#testimonials" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat website</a></div>
        <form class="upload" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload_testimonials"><input type="hidden" name="csrf" value="<?= $e(adminCsrf()) ?>"><input type="file" name="testimonial_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required><span class="note">Bisa memilih beberapa foto sekaligus. Format JPG, PNG, WEBP, atau GIF, maksimal 8 MB per foto.</span><button class="btn" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Upload foto</button></form>
        <div class="gallery">
          <?php if (!$testimonialFiles): ?><p class="note">Belum ada foto testimoni. Silakan upload foto pertama.</p><?php endif; ?>
          <?php foreach ($testimonialFiles as $file): $fileName = basename($file); ?>
            <article class="photo"><img src="assets/testimonials/<?= rawurlencode($fileName) ?>" alt="Foto testimoni pelanggan" loading="lazy"><footer><small title="<?= $e($fileName) ?>"><?= $e($fileName) ?></small><form class="inline" method="post" onsubmit="return confirm('Hapus foto ini?')"><input type="hidden" name="action" value="delete_testimonial"><input type="hidden" name="csrf" value="<?= $e(adminCsrf()) ?>"><input type="hidden" name="file_name" value="<?= $e($fileName) ?>"><button class="btn danger" type="submit" title="Hapus foto"><i class="fa-solid fa-trash"></i></button></form></footer></article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>
</div>
</body>
</html>
