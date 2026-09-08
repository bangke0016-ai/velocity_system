# VELOCITY SYSTEM AI - PHP MVC

## Instalasi XAMPP

1. Salin folder `velocity_system` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka `http://localhost/phpmyadmin`.
4. Import file `database/velocity_system.sql`.
5. Buka `http://localhost/velocity_system/`.

Konfigurasi database berada di `config/database.php`:

- Host: `localhost`
- Database: `velocity_system`
- User: `root`
- Password: kosong secara default pada XAMPP

Halaman asli dipertahankan sebagai `views/home.php`. Endpoint `order.php` menyimpan data order ke tabel `orders`, memvalidasi larangan joki ujian, dan mendukung lampiran file.

## Panel Admin

Buka `http://localhost/velocity_system/admin.php` untuk mengelola order, status pengerjaan, harga layanan, dan pengaturan kontak.

Login awal: username `admin`, password `velocity123`. Segera ganti kredensial di database sebelum website digunakan secara publik.
