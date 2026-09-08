<?php $e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Menu Kategori | Velocity Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<style>
:root{--ink:#17232e;--muted:#71808c;--paper:#f5f7f8;--line:#dce5e9;--teal:#007c83;--teal-dark:#07555d;--mint:#d9f2ed;--white:#fff}*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:14px 'DM Sans',sans-serif}h1,h2{font-family:'Space Grotesk',sans-serif}.page{width:min(1050px,calc(100% - 32px));margin:auto;padding:30px 0 55px}.top{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:22px}.top h1{margin:0;font-size:28px}.top p{margin:6px 0 0;color:var(--muted)}.actions{display:flex;gap:10px}.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;padding:11px 15px;background:var(--teal);color:#fff;font:600 14px 'DM Sans';text-decoration:none;cursor:pointer}.btn:hover{background:var(--teal-dark)}.btn.light{background:var(--mint);color:var(--teal-dark)}.panel{overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 14px 35px rgba(25,53,62,.08)}.notice{padding:13px 18px;background:var(--mint);color:var(--teal-dark);border-bottom:1px solid var(--line)}.category-row{display:grid;grid-template-columns:1.2fr 1fr .9fr .65fr auto auto;align-items:end;gap:14px;padding:18px;border-bottom:1px solid var(--line)}.category-row:last-child{border-bottom:0}.field{min-width:0}label{display:block;margin-bottom:7px;font-size:12px;font-weight:700}input,select{width:100%;height:40px;padding:9px 10px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font:inherit}.check{display:flex;align-items:center;gap:7px;height:40px;white-space:nowrap;color:var(--muted);font-weight:600}.check input{width:16px;height:16px;accent-color:var(--teal)}.inactive{opacity:.6;background:#fcfcfc}.new-category{padding:20px;border-bottom:1px solid var(--line)}.new-category h2{margin:0 0 16px;font-size:19px}.new-grid{display:grid;grid-template-columns:1.2fr 1fr 1fr .65fr auto;align-items:end;gap:14px}@media(max-width:850px){.category-row,.new-grid{grid-template-columns:repeat(3,1fr)}.category-row .check{grid-column:1/-1}.category-row .row-button{width:100%}}@media(max-width:560px){.page{width:min(100% - 20px,1050px)}.top{align-items:stretch;flex-direction:column}.actions{flex-direction:column}.actions .btn{width:100%}.category-row,.new-grid{grid-template-columns:1fr}.row-button{width:100%}}
.icon-picker{display:grid;grid-template-columns:repeat(auto-fill,minmax(132px,1fr));gap:10px;max-height:228px;overflow-y:auto;padding:6px;border:1px solid var(--line);border-radius:12px;background:linear-gradient(135deg,#f8fbfb,#f1f7f7);scrollbar-color:#8bc5c0 transparent;scrollbar-width:thin}.icon-choice{display:flex;min-height:58px;flex-direction:column;align-items:center;justify-content:center;gap:7px;padding:9px 7px;border:1px solid #d9e7e9;border-radius:10px;background:#fff;color:var(--muted);font:600 11px 'DM Sans';cursor:pointer;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease,background .2s ease}.icon-choice i{color:var(--teal);font-size:21px;line-height:1}.icon-choice span{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.icon-choice:hover{border-color:#74bdb5;background:#f4fffd;transform:translateY(-2px);box-shadow:0 7px 14px rgba(0,124,131,.1)}.icon-choice.selected{border-color:var(--teal);background:var(--mint);box-shadow:0 0 0 3px rgba(0,124,131,.12),0 7px 14px rgba(0,124,131,.1);color:var(--teal-dark)}.icon-choice.selected i{color:var(--teal-dark)}.icon-input{display:none}.icon-field{grid-column:span 2}.new-category .icon-field{min-width:0}@media(max-width:850px){.icon-field{grid-column:1/-1}}@media(max-width:560px){.icon-picker{grid-template-columns:repeat(3,1fr);gap:8px}.icon-choice{min-height:56px}}
<style>
.icon-field{position:relative}.new-grid .icon-field{grid-column:span 1}.icon-control{position:relative}.icon-trigger{display:flex;align-items:center;justify-content:space-between;gap:9px;width:100%;height:40px;padding:8px 10px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--ink);font:600 13px 'DM Sans';cursor:pointer;text-align:left}.icon-trigger:hover,.icon-control.is-open .icon-trigger{border-color:#74bdb5;box-shadow:0 0 0 3px rgba(0,124,131,.1)}.icon-trigger-value{display:flex;align-items:center;gap:9px;min-width:0}.icon-trigger-value i{width:18px;color:var(--teal);font-size:16px;text-align:center}.icon-trigger-value span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.icon-trigger>i{color:var(--muted);font-size:11px;transition:transform .2s ease}.icon-control.is-open .icon-trigger>i{transform:rotate(180deg)}.icon-picker{display:none;position:absolute;top:calc(100% + 8px);left:0;right:0;z-index:20;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;max-height:210px;padding:8px;overflow-y:auto;border:1px solid var(--line);border-radius:10px;background:#fff;box-shadow:0 14px 28px rgba(25,53,62,.16)}.icon-control.is-open .icon-picker{display:grid}.icon-choice{min-width:0;min-height:48px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:6px 4px;border:1px solid #d9e7e9;border-radius:8px;background:#fbfdfd;color:var(--muted);font:600 10px 'DM Sans';cursor:pointer;transition:.2s}.icon-choice i{color:var(--teal);font-size:17px;line-height:1}.icon-choice span{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.icon-choice:hover,.icon-choice.selected{border-color:var(--teal);background:var(--mint);color:var(--teal-dark)}.icon-choice.selected i{color:var(--teal-dark)}
body.admin-mobile .page{width:min(100% - 20px,1050px);padding:20px 0 40px}body.admin-mobile .top{align-items:stretch;flex-direction:column}body.admin-mobile .actions{flex-direction:column}body.admin-mobile .actions .btn{width:100%}body.admin-mobile .category-row,body.admin-mobile .new-grid{grid-template-columns:1fr}body.admin-mobile .category-row .check,body.admin-mobile .category-row .row-button{grid-column:auto}body.admin-mobile .row-button{width:100%}
</style>
<style>
body{background:radial-gradient(circle at 8% 0%,rgba(125,211,202,.18),transparent 30%),radial-gradient(circle at 96% 12%,rgba(0,124,131,.1),transparent 24%),var(--paper)}
.page{position:relative}.top{position:relative}.top h1{letter-spacing:-.03em}.top h1::after{content:'';display:block;width:42px;height:4px;margin-top:10px;border-radius:4px;background:linear-gradient(90deg,var(--teal),#7bd1c8)}.top p{font-size:14px}.panel{position:relative;border-color:#d4e2e5;box-shadow:0 18px 45px rgba(25,53,62,.11)}.panel::before{content:'';position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,var(--teal),#78cec5,#d9f2ed);z-index:1}.new-category{background:linear-gradient(135deg,#fff 0%,#f4fbfa 100%);padding-top:26px}.new-category h2{color:var(--teal-dark);letter-spacing:-.02em}.notice{font-size:13px;line-height:1.55}.category-row{transition:background .2s ease,box-shadow .2s ease}.category-row:hover{background:#f7fcfc;box-shadow:inset 3px 0 0 var(--teal)}input:hover,select:hover{border-color:#a9ccce}input:focus,select:focus{outline:0;border-color:var(--teal);box-shadow:0 0 0 3px rgba(0,124,131,.1)}.btn{box-shadow:0 6px 14px rgba(0,124,131,.14);transition:background .2s ease,transform .2s ease,box-shadow .2s ease}.btn:hover{transform:translateY(-1px);box-shadow:0 9px 18px rgba(0,124,131,.2)}
</style>
</head>
<body class="admin-<?= $e($adminDevice ?? 'laptop') ?>">
<main class="page">
<header class="top"><div><h1>Edit menu kategori</h1><p>Atur menu tab harga yang tampil di halaman utama website.</p></div><div class="actions"><a class="btn light" href="admin.php?tab=services">Kembali</a><a class="btn" href="admin.php?tab=services&edit=1">Edit layanan</a></div></header>
<section class="panel"><div class="new-category"><h2>Tambah menu kategori</h2><form class="new-grid" method="post"><input type="hidden" name="action" value="create_category"><input type="hidden" name="csrf" value="<?= $e(adminCsrf()) ?>"><div class="field"><label>Nama menu</label><input name="name" placeholder="Contoh: Paket Bisnis" maxlength="100" required></div><div class="field"><label>Slug</label><input name="slug" placeholder="paket_bisnis" maxlength="50"></div><div class="field"><label>Ikon Font Awesome</label><input name="icon" value="fas fa-folder" maxlength="100" required></div><div class="field"><label>Urutan</label><input type="number" name="sort_order" value="10" min="0"></div><label class="check"><input type="checkbox" name="is_active" checked> Tampilkan</label><button class="btn" type="submit">Tambah menu</button></form></div><div class="notice">Menu kategori aktif akan menjadi tab harga. Kategori yang dinonaktifkan tidak tampil di website, tetapi data layanan dan order tetap aman.</div><?php foreach ($categories as $category): ?><form class="category-row <?= !$category['is_active'] ? 'inactive' : '' ?>" method="post"><input type="hidden" name="action" value="update_category"><input type="hidden" name="csrf" value="<?= $e(adminCsrf()) ?>"><input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>"><div class="field"><label>Nama menu</label><input name="name" value="<?= $e($category['name']) ?>" maxlength="100" required></div><div class="field"><label>Slug</label><input value="<?= $e($category['slug']) ?>" disabled></div><div class="field"><label>Ikon</label><input name="icon" value="<?= $e($category['icon']) ?>" maxlength="100" required></div><div class="field"><label>Urutan</label><input type="number" name="sort_order" value="<?= (int) $category['sort_order'] ?>" min="0"></div><label class="check"><input type="checkbox" name="is_active" <?= $category['is_active'] ? 'checked' : '' ?>> Aktif</label><button class="btn row-button" type="submit">Simpan</button></form><?php endforeach; ?></section>
</main>
<script>
const categoryIcons = <?= json_encode($iconOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
document.querySelectorAll('input[name="icon"]').forEach((input) => {
	const field = input.parentElement;
	field.classList.add('icon-field');
	const control = document.createElement('div');
	control.className = 'icon-control';
	const picker = document.createElement('div');
	const hidden = document.createElement('input');
	const trigger = document.createElement('button');
	const triggerValue = document.createElement('span');
	const triggerIcon = document.createElement('i');
	const triggerArrow = document.createElement('i');
	trigger.type = 'button';
	trigger.className = 'icon-trigger';
	trigger.setAttribute('aria-haspopup', 'listbox');
	triggerValue.className = 'icon-trigger-value';
	triggerIcon.setAttribute('aria-hidden', 'true');
	triggerArrow.className = 'fas fa-chevron-down';
	triggerArrow.setAttribute('aria-hidden', 'true');
	triggerValue.append(triggerIcon, document.createElement('span'));
	trigger.append(triggerValue, triggerArrow);
	picker.className = 'icon-picker';
	picker.setAttribute('role', 'listbox');
	hidden.type = 'hidden';
	hidden.name = input.name;
	hidden.value = input.value;
	hidden.required = input.required;
	hidden.className = 'icon-input';
	const updateTrigger = (icon, label) => {
		triggerIcon.className = icon;
		triggerValue.querySelector('span').textContent = label;
		trigger.setAttribute('aria-label', `Ikon terpilih: ${label}`);
	};
	updateTrigger(input.value, categoryIcons[input.value] || 'Pilih ikon');
	Object.entries(categoryIcons).forEach(([icon, label]) => {
		const choice = document.createElement('button');
		choice.type = 'button';
		choice.setAttribute('role', 'option');
		choice.className = `icon-choice${icon === input.value ? ' selected' : ''}`;
		choice.title = label;
		choice.innerHTML = `<i class="${icon}" aria-hidden="true"></i><span>${label}</span>`;
		choice.addEventListener('click', () => {
			hidden.value = icon;
			picker.querySelectorAll('.icon-choice').forEach((item) => item.classList.remove('selected'));
			choice.classList.add('selected');
			updateTrigger(icon, label);
			control.classList.remove('is-open');
		});
		picker.appendChild(choice);
	});
	trigger.addEventListener('click', () => control.classList.toggle('is-open'));
	control.append(hidden, trigger, picker);
	input.replaceWith(control);
});
document.addEventListener('click', (event) => {
	document.querySelectorAll('.icon-control.is-open').forEach((control) => {
		if (!control.contains(event.target)) control.classList.remove('is-open');
	});
});
</script>
</body>
</html>
