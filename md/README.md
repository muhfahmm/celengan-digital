# 💰 Celengan Digital

Aplikasi web untuk mengelola tabungan digital dengan antarmuka modern bergaya glassmorphism yang terinspirasi dari iOS.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-purple.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 📋 Deskripsi

Celengan Digital adalah aplikasi manajemen keuangan pribadi yang memungkinkan pengguna untuk:
- Membuat multiple celengan dengan target tertentu
- Mencatat transaksi masuk dan keluar
- Memantau progress tabungan secara visual
- Menganalisis pola keuangan dengan grafik interaktif
- Menyematkan hingga 3 celengan favorit

## ✨ Fitur Utama

### 🎨 Desain Modern
- **Glassmorphism UI** - Antarmuka dengan efek kaca blur yang elegan
- **Dark Mode** - Mode gelap yang nyaman untuk mata
- **Responsive Design** - Tampilan optimal di semua perangkat
- **Smooth Animations** - Transisi dan animasi yang halus

### 💼 Manajemen Celengan
- **Multiple Celengan** - Buat celengan sebanyak yang diinginkan
- **Target & Progress** - Set target dan pantau progress real-time
- **Pin Celengan** - Sematkan hingga 3 celengan favorit (selalu di atas)
- **Sorting** - Urutkan berdasarkan terbaru, progress, saldo, atau target
- **Frekuensi Pengisian** - Pilih harian, mingguan, atau bulanan

### 📊 Tracking Transaksi
- **Transaksi Masuk/Keluar** - Catat semua pemasukan dan pengeluaran
- **Keterangan** - Tambahkan catatan untuk setiap transaksi
- **Edit & Delete** - Ubah atau hapus transaksi kapan saja
- **Auto Calculate** - Saldo otomatis terupdate

### 📈 Visualisasi Data
- **Chart.js Integration** - Grafik interaktif untuk analisis
- **Progress Bar** - Visual progress untuk setiap celengan
- **Summary Cards** - Ringkasan total tabungan dan target
- **Zoom & Pan** - Fitur zoom pada grafik untuk detail lebih baik

### 🔐 Keamanan
- **User Authentication** - Sistem login dan registrasi yang aman
- **Password Hashing** - Password di-hash dengan bcrypt
- **Session Management** - Pengelolaan sesi yang aman
- **Auto Logout** - Sesi otomatis dihapus saat logout
- **Protected Routes** - Halaman dilindungi dengan auth check

## 🛠️ Teknologi yang Digunakan

### Backend
- **PHP 8.2** - Server-side scripting
- **MySQL/MariaDB** - Database management
- **PDO** - Database abstraction layer

### Frontend
- **HTML5** - Struktur halaman
- **CSS3** - Styling dengan glassmorphism
- **JavaScript (ES6+)** - Interaktivitas
- **Chart.js** - Visualisasi data
- **Bootstrap Icons** - Icon library
- **Google Fonts (Inter)** - Typography

### Libraries & Plugins
- **Chart.js** v4.4.1 - Grafik dan chart
- **chartjs-plugin-zoom** v2.0.1 - Zoom functionality
- **Hammer.js** v2.0.8 - Touch gestures
- **Bootstrap Icons** v1.11.3 - Icon set

## 📁 Struktur Folder

```
celengan-digital/
├── web/                       # Aplikasi Web Utama
│   ├── auth/                  # Autentikasi
│   ├── config/                # Konfigurasi
│   ├── dashboard/             # Dashboard utama
│   ├── data-celengan/         # CRUD Celengan
│   ├── transaksi/             # CRUD Transaksi
│   ├── database/              # SQL Scripts
│   ├── assets/                # Static files
│   └── index.php              # Entry point aplikasi web
│
├── mobile/                    # Aplikasi Mobile (Coming Soon)
├── md/                        # Dokumentasi Proyek
└── index.php                  # Root redirector
```

## 🚀 Instalasi

### Prasyarat
- XAMPP/WAMP/LAMP (PHP 8.0+, MySQL 5.7+)
- Web browser modern (Chrome, Firefox, Safari, Edge)

### Langkah Instalasi

1. **Clone atau Download Project**
   ```bash
   git clone https://github.com/username/celengan-digital.git
   # atau download ZIP dan extract ke htdocs
   ```

2. **Pindahkan ke htdocs**
   ```
   C:\xampp\htdocs\celengan digital\
   ```

3. **Import Database**
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Buat database baru: `db_celengan`
   - Import file SQL yang ada di folder `database/`
   - Jalankan script `add_pin_feature.sql` untuk fitur pin

4. **Konfigurasi Database**
   
   Edit file `config/db.php`:
   ```php
   $host = 'localhost';
   $dbname = 'db_celengan';
   $username = 'root';
   $password = ''; // sesuaikan dengan password MySQL Anda
   ```

5. **Jalankan Aplikasi**
   
   Buka browser dan akses:
   ```
   http://localhost/celengan digital/
   ```

6. **Register & Login**
   - Buat akun baru di halaman register
   - Login dengan akun yang telah dibuat

## 📊 Database Schema

### Tabel: `users`
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- username (VARCHAR 100)
- email (VARCHAR 150, UNIQUE)
- password (VARCHAR 255, HASHED)
- created_at (TIMESTAMP)
```

### Tabel: `celengan`
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY -> users.id)
- nama_celengan (VARCHAR 150)
- target (INT)
- pengisian (ENUM: harian, mingguan, bulanan)
- total (INT)
- created_at (TIMESTAMP)
- is_pinned (TINYINT 1, DEFAULT 0)
```

### Tabel: `transaksi`
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- celengan_id (INT, FOREIGN KEY -> celengan.id)
- nominal (INT)
- tipe (ENUM: masuk, keluar)
- keterangan (VARCHAR 255)
- tanggal (DATE)
```

### Trigger: `transaksi_after_delete`
Otomatis menghitung ulang total celengan saat transaksi dihapus.

## 🎯 Cara Penggunaan

### 1. Membuat Celengan Baru
- Klik tombol **"Buat Celengan Baru"**
- Isi nama celengan, target, dan frekuensi pengisian
- Klik **"Buat Celengan"**

### 2. Menambah Transaksi
- Buka detail celengan
- Klik **"Tambah Transaksi"**
- Pilih tipe (Masuk/Keluar), masukkan nominal dan keterangan
- Klik **"Simpan Transaksi"**

### 3. Menyematkan Celengan
- Di dashboard, klik icon **pin (📌)** pada celengan
- Maksimal 3 celengan dapat disematkan
- Celengan yang disematkan akan selalu muncul di atas

### 4. Melihat Grafik
- Buka detail celengan
- Scroll ke bawah untuk melihat grafik transaksi
- Gunakan zoom untuk melihat detail periode tertentu

### 5. Mengedit/Menghapus
- Klik icon **pensil** untuk edit
- Klik icon **trash** untuk hapus (akan muncul konfirmasi)

## 🎨 Fitur UI/UX

### Glassmorphism Design
- Background blur dengan saturasi tinggi
- Transparansi yang elegan
- Border semi-transparan
- Shadow yang lembut
- Gradient yang vibrant

### Dark Mode
- Toggle di pojok kanan atas
- Preference tersimpan di localStorage
- Smooth transition antar mode
- Optimized contrast untuk readability

### Responsive
- Mobile-first approach
- Breakpoints untuk tablet dan desktop
- Touch-friendly pada mobile
- Adaptive layout

### Animations
- Smooth transitions (cubic-bezier)
- Hover effects pada semua interactive elements
- Loading states
- Modal animations (fade in, slide up)

## 🔒 Keamanan

### Authentication
- Password hashing dengan bcrypt (cost 10)
- Session-based authentication
- Protected routes dengan `auth_check.php`
- Auto redirect ke login jika tidak authenticated

### Input Validation
- Server-side validation
- SQL injection prevention dengan PDO prepared statements
- XSS prevention dengan `htmlspecialchars()`
- CSRF protection (session-based)

### Session Management
- Secure session handling
- Session destroy pada logout
- Cookie cleanup
- Auto-logout pada unauthorized access

## 🐛 Troubleshooting

### Database Connection Error
```
Solusi:
1. Pastikan MySQL/MariaDB sudah running
2. Cek kredensial di config/db.php
3. Pastikan database 'db_celengan' sudah dibuat
```

### Halaman Blank/Error 500
```
Solusi:
1. Enable error reporting di php.ini
2. Cek PHP error log
3. Pastikan semua file PHP syntax-nya benar
```

### Session Tidak Tersimpan
```
Solusi:
1. Cek permission folder session PHP
2. Pastikan session_start() dipanggil
3. Clear browser cookies
```

### Grafik Tidak Muncul
```
Solusi:
1. Pastikan koneksi internet (untuk CDN Chart.js)
2. Cek console browser untuk error
3. Pastikan ada data transaksi
```

## 📝 Changelog

### Version 1.0.0 (2026-01-10)
- ✨ Initial release
- 🎨 Glassmorphism UI design
- 🌙 Dark mode support
- 📊 Chart.js integration
- 📌 Pin celengan feature (max 3)
- 🔐 Secure authentication system
- 📱 Responsive design
- 🎭 Modal notifications
- 🔄 Auto-calculate balance
- 📈 Interactive charts with zoom

## 🤝 Kontribusi

Kontribusi selalu diterima! Jika Anda ingin berkontribusi:

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 License

Project ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detail lebih lanjut.

## 👨‍💻 Developer

**Muhammad Fahim**
- Email: fahimfahim0407@gmail.com
- GitHub: [@muhfahmm](https://github.com/muhfahmm)

## 🙏 Acknowledgments

- Chart.js untuk library grafik yang powerful
- Bootstrap Icons untuk icon set yang lengkap
- Google Fonts untuk typography yang indah
- Komunitas PHP & MySQL untuk dokumentasi yang excellent

## 📞 Support

Jika Anda menemukan bug atau memiliki saran, silakan:
- Buat issue di GitHub
- Email ke: fahimfahim0407@gmail.com

---

**⭐ Jika project ini membantu, jangan lupa beri star di GitHub!**

Made with ❤️ by Muhammad Fahim
