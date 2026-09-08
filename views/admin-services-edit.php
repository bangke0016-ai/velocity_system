<?php $e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Layanan | Velocity Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<style>
:root{--ink:#17232e;--muted:#71808c;--paper:#f5f7f8;--line:#dce5e9;--teal:#007c83;--teal-dark:#07555d;--mint:#d9f2ed;--white:#fff;--red:#c94b4b}*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:14px 'DM Sans',sans-serif}h1,h2{font-family:'Space Grotesk',sans-serif}.page{width:min(1180px,calc(100% - 32px));margin:auto;padding:30px 0 55px}.top{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:22px}.top h1{margin:0;font-size:28px}.top p{margin:6px 0 0;color:var(--muted)}.actions{display:flex;gap:10px}.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;padding:11px 15px;background:var(--teal);color:#fff;font:600 14px 'DM Sans';text-decoration:none;cursor:pointer}.btn:hover{background:var(--teal-dark)}.btn.light{background:var(--mint);color:var(--teal-dark)}.panel{overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 14px 35px rgba(25,53,62,.08)}.notice{padding:13px 18px;background:var(--mint);color:var(--teal-dark);border-bottom:1px solid var(--line)}.edit-row{display:grid;grid-template-columns:1.4fr 1.2fr .75fr .75fr .75fr auto auto;align-items:end;gap:14px;padding:18px;border-bottom:1px solid var(--line)}.edit-row:last-child{border-bottom:0}.field{min-width:0}label{display:block;margin-bottom:7px;font-size:12px;font-weight:700}input,select{width:100%;height:40px;padding:9px 10px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font:inherit}.check{display:flex;align-items:center;gap:7px;height:40px;white-space:nowrap;color:var(--muted);font-weight:600}.check input{width:16px;height:16px;accent-color:var(--teal)}.row-button{height:40px;white-space:nowrap}.inactive{opacity:.62;background:#fcfcfc}@media(max-width:950px){.edit-row{grid-template-columns:repeat(3,1fr)}.row-button{width:100%}}@media(max-width:600px){.page{width:min(100% - 20px,1180px)}.top{align-items:stretch;flex-direction:column}.actions{flex-direction:column}.actions .btn{width:100%}.edit-row{grid-template-columns:1fr 1fr}.edit-row .field:first-child{grid-column:1/-1}.edit-row .check{grid-column:1/-1}.row-button{grid-column:1/-1}}
</style>
<style>
body.admin-mobile .page{width:min(100% - 20px,1180px);padding:20px 0 40px}body.admin-mobile .top{align-items:stretch;flex-direction:column}body.admin-mobile .actions{flex-direction:column}body.admin-mobile .actions .btn{width:100%}body.admin-mobile .edit-row{grid-template-columns:1fr}.edit-row .field:first-child,body.admin-mobile .edit-row .check,body.admin-mobile .row-button{grid-column:auto}body.admin-mobile .row-button{width:100%}
</style>
</head>
<body class="admin-<?= $e($adminDevice ?? 'laptop') ?>">
<main class="page">
  <header class="top"><div><h1>Edit layanan</h1><p>Ubah nama, kategori, harga, satuan, atau status tampil dengan mudah.</p></div><div class="actions"><a class="btn light" href="admin.php?tab=services">Kembali</a><a class="btn light" href="admin.php?tab=services&amp;categories=1">Kategori layanan</a><a class="btn" href="admin.php?tab=services">Tambah layanan</a></div></header>
  <section class="panel"><div class="notice">Perubahan layanan langsung memengaruhi halaman utama dan form pemesanan. Menonaktifkan layanan tidak menghapus riwayat order.</div>
  <?php foreach ($services as $service): ?><form class="edit-row <?= !$service['is_active'] ? 'inactive' : '' ?>" method="post" action="admin.php?tab=services"><input type="hidden" name="action" value="update_service"><input type="hidden" name="csrf" value="<?= $e(adminCsrf()) ?>"><input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>"><div class="field"><label>Nama layanan</label><input name="title" value="<?= $e($service['title']) ?>" maxlength="150" required></div><div class="field"><label>Kategori</label><select name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $service['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= $e($category['name']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Harga mulai</label><input type="number" name="price" value="<?= (int) $service['price'] ?>" min="0" required></div><div class="field"><label>Harga maksimal</label><input type="number" name="price_max" value="<?= $e($service['price_max']) ?>" min="0" placeholder="Opsional"></div><div class="field"><label>Satuan</label><input name="unit" value="<?= $e($service['unit']) ?>" maxlength="50" required></div><label class="check"><input type="checkbox" name="is_active" <?= $service['is_active'] ? 'checked' : '' ?>> Aktif</label><button class="btn row-button" type="submit">Simpan</button></form><?php endforeach; ?>
  </section>
</main>
<script>
const serviceIcons = <?= json_encode($iconOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
document.querySelectorAll('input[name="icon"]').forEach((input) => {
  const select = document.createElement('select');
  select.name = input.name;
  select.required = input.required;
  Object.entries(serviceIcons).forEach(([icon, label]) => {
    const option = document.createElement('option');
    option.value = icon;
    option.textContent = `${label} (${icon})`;
    option.selected = icon === input.value;
    select.appendChild(option);
  });
  input.replaceWith(select);
});
</script>
</body>
</html>
