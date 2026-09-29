# 🕐 Absen PSTORE

Sistem absensi dan manajemen SDM karyawan berbasis web untuk operasional multi-cabang — presensi dengan verifikasi lokasi & foto, pengajuan izin/cuti, penggajian, insentif, inventaris cabang, dan pelaporan.

Dibangun dengan Laravel dan dipakai untuk kebutuhan operasional harian tim.

> **Catatan:** proyek ini berisi data dan alur bisnis internal. Sebagian konfigurasi dan kredensial sengaja tidak disertakan dalam repositori.

---

## ✨ Fitur

**Presensi & kehadiran**
- **Absensi masuk/pulang** — dengan verifikasi lokasi (geotag) dan foto sebagai bukti.
- **Jadwal kerja** — pengaturan shift dan jam kerja per divisi/cabang.
- **Notifikasi keterlambatan** — pengingat otomatis saat karyawan terlambat.
- **Koreksi absensi** — pengajuan perbaikan data presensi beserta persetujuan atasan.
- **Riwayat & rekap** — riwayat kehadiran pribadi, rekap harian/bulanan, dan ringkasan per divisi.

**Pengajuan & administrasi**
- **Izin & cuti** — pengajuan, persetujuan berjenjang, dan pelacakan status.
- **Kasbon (cash advance)** — pengajuan, rencana cicilan, dan riwayat angsuran.
- **Riwayat kepegawaian** — mutasi, jabatan, dan evaluasi karyawan.
- **Sertifikat** — penerbitan sertifikat kepegawaian.

**Penggajian & insentif**
- **Penggajian** — perhitungan gaji per karyawan dan rekap per cabang.
- **Bonus & target kerja** — penetapan target, pencapaian, dan bonus.
- **Papan peringkat cabang** — leaderboard performa antar cabang.

**Operasional cabang**
- **Manajemen cabang & divisi** — struktur organisasi bertingkat.
- **Inventaris cabang** — pencatatan barang, pemakaian, dan pengembalian.
- **Broadcast & pesan cabang** — pengumuman ke seluruh atau sebagian cabang.
- **Push notification** — notifikasi ke perangkat karyawan (Firebase Cloud Messaging + Web Push).

**Verifikasi & audit**
- **Autentikasi sidik jari** — dukungan verifikasi biometrik perangkat.
- **Pemindaian KTP** — pendataan identitas karyawan.
- **Audit & monitoring** — jejak aktivitas dan pemantauan sistem.
- **Analitik Dzikir** — modul kampanye & pencatatan amalan, terintegrasi dengan keseharian tim.

## 🧰 Teknologi

| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 9, PHP 8.1+ |
| Autentikasi | Laravel Sanctum |
| Notifikasi | Firebase Cloud Messaging (`kreait/laravel-firebase`), Web Push (`minishlink/web-push`) |
| Laporan | DomPDF (PDF), Maatwebsite Excel (Excel) |
| Gambar | Intervention Image |
| Frontend | Blade, Vite/Laravel Mix |

## 🚀 Cara Menjalankan Lokal

```bash
# 1. Clone
git clone https://github.com/fabian-syah/absen-pstore.git
cd absen-pstore

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan lingkungan
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi basis data di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absen_pstore
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 4. Migrasi basis data
php artisan migrate

# 5. Jalankan
npm run dev
php artisan serve
```

Buka [http://localhost:8000](http://localhost:8000).

### Catatan konfigurasi notifikasi

Fitur push notification memerlukan kredensial Firebase:

1. Unduh service account dari Firebase Console → **Project Settings → Service Accounts**.
2. Simpan sebagai `storage/app/firebase/service-account.json` (jangan di-commit).
3. Isi variabel Firebase di `.env` sesuai `.env.example`.
4. Untuk Web Push, bangkitkan pasangan kunci VAPID melalui skrip `gen_vapid.php`.

## 📁 Struktur Proyek

| Lokasi | Isi |
|---|---|
| `app/Http/Controllers/` | Controller per modul (Attendance, LeaveRequest, Salary, Branch, Inventory, Audit, dll.) |
| `app/Models/` | Model Eloquent (Attendance, Branch, Division, EmployeeSalary, CashAdvance, Inventory, dll.) |
| `app/Exports/` | Kelas ekspor Excel |
| `app/Jobs/` | Pekerjaan latar (notifikasi, pengingat) |
| `app/Policies/` | Kebijakan otorisasi per peran |
| `database/migrations/` | Skema basis data lengkap |
| `routes/api.php` | Endpoint API untuk aplikasi klien |
| `firebase-messaging-sw.js` | Service worker untuk push notification |

## 🔐 Keamanan & Privasi

Proyek ini menangani **data karyawan** (identitas, gaji, kasbon, foto absensi) sehingga perlu penanganan ketat:

- Jangan commit `.env` maupun service account Firebase — keduanya memuat kredensial sensitif.
- Terapkan `app/Policies/` untuk memastikan karyawan hanya dapat mengakses data miliknya sendiri.
- Foto absensi dan berkas KTP sebaiknya disimpan di penyimpanan privat, bukan folder `public`.
- Batasi akses modul penggajian dan audit hanya untuk peran admin/HR yang berwenang.
- Aktifkan pemantauan `app/Http/Middleware/` untuk mencatat akses ke data sensitif.

## 🗺️ Rencana Pengembangan

- [ ] Uji otomatis untuk alur presensi dan pengajuan cuti
- [ ] Dasbor analitik kehadiran per periode
- [ ] Mode offline untuk pencatatan absensi di cabang dengan jaringan terbatas
- [ ] Ekspor laporan kehadiran terjadwal

## 📄 Lisensi

MIT
