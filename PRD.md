PRD FINAL: Enterprise URL Shortener
Versi: 1.0
Status: Final untuk development MVP
Pemilik produk: Angga Permana
Pendekatan: Praktisi internal tool, MVP dulu, tidak over-engineering.
1. Ringkasan Produk
Aplikasi URL shortener internal untuk perusahaan dengan fitur:
1.	Login terpusat melalui SSO/LDAP.
2.	Setiap user hanya dapat melihat dan mengelola (buat dan edit) short URL miliknya. Penghapusan (delete) HANYA dapat dilakukan oleh Admin.
3.	Link dapat diberi expiration.
4.	Link dapat diproteksi password.
5.	Dashboard untuk melihat rekap akses.
6.	Admin panel untuk manajemen user, link, settings, audit, dan branding.
7.	Admin dapat mengganti logo pada halaman login.
Fokus utama produk:
Membuat short URL yang aman, terkontrol, memiliki pemilik jelas, mudah diaudit, dan mudah dikelola oleh admin.
2. Keputusan Final yang Sudah Dikunci
•	Backend: Laravel
•	Admin/Dashboard: Filament
•	Database: MySQL 8
•	Cache/Queue: Redis optional
•	Auth tahap dev: Login lokal
•	Auth production: LDAP/SSO melalui AUTH_MODE
•	Struktur kode: Modular per area (Admin, User, Services, Actions, Policies)
•	Deployment target: VPS/container dengan Nginx/PHP-FPM atau tool seperti Coolify/Forge
•	MVP: Tidak langsung microservices, mulai dari monolith modular
3. Tujuan Produk
8.	Menyediakan layanan pemendek URL internal yang aman.
9.	Memastikan setiap link memiliki owner yang jelas.
10.	Membatasi akses link dengan expiration dan password.
11.	Memberikan statistik akses kepada pemilik link.
12.	Memberikan kontrol penuh kepada admin untuk user, link, dan branding.
13.	Memudahkan audit aktivitas user dan admin.
14.	Mempermudah handover development karena struktur kode modular.
4. Non-Goals
Untuk MVP, sistem belum mencakup:
15.	Pendaftaran user publik.
16.	Monetisasi atau subscription.
17.	Multi-tenant SaaS.
18.	Custom domain kompleks.
19.	A/B testing link.
20.	Fraud detection lanjutan.
21.	Geo analytics mendalam.
22.	Approval workflow kompleks.
23.	QR code generator.
24.	Bulk import/export besar-besaran.
5. Persona dan Role
•	User internal: Pegawai yang membuat short URL. Hak Utama: Login, membuat link, mengedit link miliknya, melihat statistik link miliknya. (HAK HAPUS DIHILANGKAN).
•	Admin: Pengelola sistem. Hak Utama: Mengelola user, melihat semua link, menghapus link, mengelola settings/branding, melihat audit.
•	Visitor: Orang yang mengakses short URL. Hak Utama: Mengakses link jika link aktif, belum expired, dan password benar bila diproteksi.
•	System: Proses internal. Hak Utama: Validasi expiration, mencatat click, cleanup, audit, enforce security.
6. Ruang Lingkup MVP
Masuk MVP
25.	Login lokal untuk development.
26.	Struktur AUTH_MODE untuk local/LDAP/SSO.
27.	User management dasar.
28.	CRUD short URL (Hapus khusus Admin).
29.	Ownership enforcement.
30.	Expiration link.
31.	Password protected link.
32.	Public redirect endpoint.
33.	Click analytics dasar.
34.	Dashboard user.
35.	Admin panel.
36.	Admin dapat mengganti logo halaman login.
37.	Audit log dasar.
38.	Struktur folder modular untuk admin/user/services/actions/policies.
Belum Masuk MVP
39.	Integrasi LDAP/SSO penuh jika belum tersedia environment IdP.
40.	QR code.
41.	Team/workspace.
42.	API key publik.
43.	Bulk import.
44.	Custom domain.
45.	Advanced analytics.
7. Kebutuhan Fungsional
7.1 Authentication
•	AUTH-01: Sistem mendukung login lokal untuk dev (Must)
•	AUTH-02: Sistem mendukung LDAP/SSO untuk production (Must)
•	AUTH-03: Mode auth diatur lewat environment (Must)
•	AUTH-04: User baru dibuat otomatis saat login LDAP/SSO valid (Should)
•	AUTH-05: Role default user baru adalah user biasa (Must)
•	AUTH-06: Admin lokal tetap tersedia sebagai fallback (Should)
•	AUTH-07: User nonaktif tidak bisa login (Must)
7.2 Authorization dan Ownership
•	AUTHZ-01: Setiap short URL wajib memiliki owner (Must)
•	AUTHZ-02: User hanya dapat melihat link miliknya (Must)
•	AUTHZ-03: User hanya dapat mengubah (edit) link miliknya (Must)
•	AUTHZ-04: HANYA Admin yang dapat menghapus link (Must)
•	AUTHZ-05: Admin dapat melihat semua link (Must)
•	AUTHZ-06: Ownership wajib divalidasi di backend (Must)
•	AUTHZ-07: Semua aksi sensitif dicatat di audit log (Should)
Aturan:
User biasa: Lihat link milik sendiri, Create link, Edit link milik sendiri
Admin: Lihat semua link, Nonaktifkan link, Hapus link (termasuk milik user lain), Ubah ownership bila diperlukan, Kelola user dan settings
7.3 Short URL Management
•	LINK-01: User dapat membuat short URL (Must)
•	LINK-02: User dapat memakai slug acak atau custom (Must)
•	LINK-03: Sistem memvalidasi destination URL (Must)
•	LINK-04: User dapat mengubah destination URL (Must)
•	LINK-05: User dapat menonaktifkan link (Must)
•	LINK-06: Admin dapat menghapus link (Must)
•	LINK-07: Sistem menampilkan status link (Must)
•	LINK-08: Slug unik dan tidak mudah ditebak (Must)
7.4 Password Protected Link
•	PASS-01: User dapat mengaktifkan password pada link (Must)
•	PASS-02: Visitor wajib memasukkan password sebelum redirect (Must)
•	PASS-03: Password disimpan sebagai hash (Must)
•	PASS-04: Sistem membatasi percobaan password (Must)
•	PASS-05: Error message bersifat generik (Must)
•	PASS-06: Password tidak pernah ditampilkan kembali (Must)
7.5 Expiration
•	EXP-01: User dapat mengatur tanggal kedaluwarsa (Must)
•	EXP-02: Link otomatis tidak aktif setelah waktu habis (Must)
•	EXP-03: Sistem mendukung link permanen (Should)
•	EXP-04: Waktu disimpan dalam UTC (Must)
•	EXP-05: Opsional start time (Should)
7.6 Public Redirect
•	RED-01: Visitor dapat mengakses short URL tanpa login (Must)
•	RED-02: Sistem mengecek active, expiration, dan password (Must)
•	RED-03: Sistem mencatat click event (Must)
•	RED-04: Halaman error ditampilkan untuk kondisi tertentu (Must)
•	RED-05: Redirect harus cepat (Must)
7.7 Dashboard User
•	DASH-01: User dapat melihat daftar link miliknya (Must)
•	DASH-02: User dapat mencari link (Should)
•	DASH-03: User dapat memfilter status link (Should)
•	DASH-04: User dapat melihat total click (Must)
•	DASH-05: User dapat melihat detail link (Must)
•	DASH-06: User dapat membuat link baru (Must)
•	DASH-07: User dapat mengubah (edit) link miliknya (Must)
7.8 Analytics
•	ANA-01: Sistem mencatat setiap akses link (Must)
•	ANA-02: Sistem mencatat status akses (Must)
•	ANA-03: Sistem menghitung total click (Must)
•	ANA-04: IP disimpan sebagai hash (Should)
•	ANA-05: Data analytics punya retention (Should)
•	ANA-06: Analytics tidak memperlambat redirect (Should)
7.9 Admin Panel
•	ADM-01: Admin dapat melihat dashboard ringkasan (Must)
•	ADM-02: Admin dapat mengelola user (Must)
•	ADM-03: Admin dapat melihat semua link (Must)
•	ADM-04: Admin dapat menonaktifkan link (Must)
•	ADM-05: Admin dapat menghapus link (Must)
•	ADM-06: Admin dapat melihat audit log (Should)
•	ADM-07: Admin dapat mengganti logo login (Must)
•	ADM-08: Admin dapat mengatur nama aplikasi/favicon (Should)
•	ADM-09: Admin dapat mengubah ownership link (Should)
7.10 Audit Log
•	AUD-01: Sistem mencatat login berhasil/gagal (Should)
•	AUD-02: Sistem mencatat create/update/delete link (Should)
•	AUD-03: Sistem mencatat aksi admin (Should)
•	AUD-04: Sistem mencatat perubahan branding (Should)
•	AUD-05: Audit log tidak dapat diubah user biasa (Should)
8. Kebutuhan Non-Fungsional
8.1 Security
46.	HTTPS wajib di production.
47.	Password user dan password link wajib di-hash.
48.	Session cookie menggunakan flag secure.
49.	Rate limiting pada login dan form password link.
50.	Validasi input untuk URL, slug, dan file upload.
51.	Authorization wajib dicek di backend.
52.	Slug acak tidak mudah ditebak.
53.	Error publik tidak membocorkan informasi sensitif.
8.2 Performance
54.	Redirect harus ringan.
55.	Pencatatan analytics diusahakan async bila memungkinkan.
56.	Lookup slug dapat menggunakan cache.
57.	Index database pada kolom penting.
8.3 Availability
58.	Layanan redirect harus tersedia tinggi.
59.	Gangguan dashboard tidak boleh membuat redirect gagal total.
60.	Queue dan scheduler berjalan sebagai service.
8.4 Privacy
61.	IP visitor disimpan sebagai hash.
62.	Data analytics memiliki masa simpan.
63.	Password tidak pernah muncul di log atau API response.
8.5 Maintainability
64.	Kode dipisahkan per modul.
65.	Ada pemisahan Admin/User.
66.	Logika bisnis berada di Service/Action.
67.	Ownership dan keamanan diatur lewat Policy.
68.	Mudah dipahami oleh developer pengganti.
9. Tech Stack Final
•	Backend: Laravel
•	Admin/Dashboard: Filament
•	Database: MySQL 8
•	Cache/Queue: Redis optional
•	Storage: Local storage atau S3-compatible
•	Web Server: Nginx atau Apache
•	PHP: 8.2 atau lebih baru
•	Deployment: VPS, Docker, Coolify, atau Forge
10. Struktur Database Final
•	users: id, name, email, username, password, auth_provider, ldap_dn, sso_subject, role, is_active, last_login_at
•	short_urls: id, owner_id, slug, destination_url, is_active, starts_at, expires_at, password_hash, total_clicks, last_clicked_at
•	click_events: id, short_url_id, status, ip_hash, user_agent, referer, created_at
•	settings: id, key, value, updated_by, created_at, updated_at
•	audit_logs: id, actor_id, actor_type, action, resource_type, resource_id, metadata, ip_address, created_at
11. Struktur Folder Modular
Tujuan: Mudah dipahami, tidak mencampur kode admin dan user, memisahkan logika bisnis dari UI.
•	app/Actions/ShortUrl/
•	app/Filament/Admin/
•	app/Filament/User/
•	app/Http/Controllers/
•	app/Models/
•	app/Policies/
•	app/Services/Auth/
•	app/Services/ShortUrl/
•	app/Services/Analytics/
•	app/Services/Branding/
•	app/Support/Enums/
12. Rekomendasi Warna & UI/UX (Berdasarkan Referensi Ekosistem)
Berdasarkan analisis visual dari referensi aplikasi Portal Smart PMK dan Srikandi yang dilampirkan, berikut adalah panduan palet warna dan styling UI/UX agar URL Shortener ini senada dengan corporate identity yang sudah ada:
69.	Primary Navy Blue (#1A296C atau #203273)
Gunakan untuk Header utama aplikasi, Sidebar navigation, dan teks heading utama. Warna ini mencerminkan sisi formal, enterprise, dan keamanan tinggi, serta selaras dengan header Portal Kemenko PMK.
70.	Accent Light Blue / Cyan (#42C2FF atau #3399FF)
Gunakan untuk background halaman login (mengadaptasi estetika login Srikandi), highlight menu aktif di sidebar, atau icon informatif. Warna ini memberikan kesan modern, bersih, dan segar.
71.	Action Button Blue (#2563EB atau #3B82F6)
Gunakan secara eksklusif untuk tombol utama (Call-To-Action) seperti tombol "Masuk", "Create Link", atau "Simpan". Warna biru cerah ini memiliki tingkat kontras yang tinggi terhadap background terang.
72.	Alert / Warning Red (#D32F2F atau #B71C1C)
Gunakan untuk tombol tindakan destruktif khusus Admin (seperti "Hapus Link"), badge peringatan, atau notifikasi jika link sudah expired. Warna merah ini terinspirasi dari grafis peringatan pada bagian inspektorat.
73.	Background & Surface (White & Light Gray)
Gunakan Putih (#FFFFFF) murni untuk card/container konten utama, form input, dan area tabel. Gunakan Abu-abu Terang (#F3F4F6 atau #F8FAFC) untuk warna latar belakang dashboard agar layout lebih rapi dan mata tidak mudah lelah.
74.	Tipografi & Layout Styling
Pilih tipografi sans-serif yang modern dan bersih (seperti Inter atau Poppins). Desain elemen antarmuka (seperti card dan modal) dengan shadow yang tipis (subtle shadow) dan radius sudut (border-radius) yang sedikit membulat untuk menyesuaikan dengan tren desain UI saat ini.
13. Kesimpulan
PRD ini menjadi acuan utama untuk membangun URL shortener internal dengan pendekatan:
MVP dulu, aman dulu, rapi dulu, mudah dihandover.
Keputusan Final Hak Akses:
Telah diimplementasikan bahwa user biasa hanya memiliki hak untuk membuat (create) dan mengubah (edit) link. Fitur hapus (delete) telah dicabut dari user biasa dan dikunci sebagai hak eksklusif Admin untuk mencegah kehilangan riwayat data dan memastikan akuntabilitas aktivitas URL internal.
