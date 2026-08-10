1. Ringkasan Eksekutif
Aplikasi shortlink internal menggunakan Keycloak sebagai pintu login utama. Keycloak terhubung dengan LDAP internal sebagai sumber data user.
Namun, karena LDAP tidak memiliki group atau role khusus aplikasi, aplikasi shortlink tetap harus menentukan sendiri siapa yang menjadi user biasa dan siapa yang menjadi admin.
Dengan kata lain:
text
12
Keycloak = AuthenticationAplikasi = Authorization
Keycloak hanya memastikan bahwa user yang login adalah user yang valid. Setelah login sukses, aplikasi shortlink akan melihat database lokal untuk menentukan role user tersebut di dalam aplikasi.
Untuk kebutuhan darurat, aplikasi juga memiliki satu mekanisme login lokal bernama local super admin, yang dapat digunakan ketika Keycloak atau LDAP sedang bermasalah.
2. Keputusan Desain Utama
Ini adalah keputusan inti yang wajib dipahami oleh seluruh tim.
2.1 Keycloak hanya untuk autentikasi
Keycloak bertanggung jawab untuk:
text
123
Siapa yang login?Apakah username/password valid?Apakah user aktif di sistem identitas kantor?
Keycloak tidak dijadikan sumber role utama untuk aplikasi shortlink pada fase ini.
2.2 Aplikasi shortlink memegang otorisasi
Aplikasi shortlink bertanggung jawab untuk:
text
1234
Apakah user ini admin?Apakah user ini user biasa?Siapa yang boleh mengakses halaman admin?Siapa yang boleh mengubah role user lain?
Role aplikasi disimpan di database MySQL milik aplikasi shortlink.
2.3 Role default adalah user
Setiap user yang pertama kali login melalui Keycloak akan dibuat otomatis di database aplikasi dengan role:
text
1
user
User tidak otomatis menjadi admin.
2.4 Admin ditentukan dari dalam aplikasi
Admin dapat diangkat oleh admin lain atau oleh super admin melalui halaman manajemen user di aplikasi.
Contoh:
text
1234567
User biasa login via Keycloak↓Admin membuka halaman Admin > Users↓Admin memilih user tersebut↓Admin mengubah role menjadi admin
2.5 Local super admin adalah jalur darurat
Aplikasi tetap memiliki akun lokal yang disimpan di database aplikasi.
Akun ini digunakan untuk:
text
123
Bootstrap awalPemulihan saat Keycloak bermasalahPemulihan saat admin LDAP tidak bisa login
Dalam kondisi normal, super admin tetap disarankan login melalui Keycloak.
3. Latar Belakang
Server internal sudah memiliki Keycloak sebagai SSO. LDAP juga sudah tersedia sebagai sumber user internal. Namun LDAP tidak memiliki group, role, atau atribut khusus yang bisa langsung digunakan untuk membedakan admin dan user biasa pada aplikasi shortlink.
Jika aplikasi langsung membaca LDAP, aplikasi tidak memiliki cara alami untuk menentukan siapa admin. Karena itu, aplikasi membutuhkan lapisan otorisasi sendiri.
Dengan adanya Keycloak, aplikasi tidak perlu lagi berbicara langsung ke LDAP. Aplikasi cukup menggunakan Keycloak sebagai identity provider, lalu menyimpan role aplikasi di database lokal.
4. Tujuan
Menggunakan Keycloak sebagai metode login utama.
Tetap menggunakan LDAP sebagai sumber identitas internal melalui Keycloak.
Menyediakan mekanisme role sederhana: user dan admin.
Menyediakan halaman manajemen role untuk admin.
Menyediakan akun lokal darurat untuk recovery.
Mencatat setiap perubahan role untuk kebutuhan audit.
Membuat aplikasi tetap dapat diakses saat Keycloak bermasalah melalui local admin.
5. Non-Goals
Hal-hal berikut tidak termasuk dalam ruang lingkup fase ini:
Tidak mengubah struktur LDAP kantor.
Tidak membangun role management global di Keycloak.
Tidak menggunakan role LDAP sebagai sumber otorisasi utama.
Tidak membuat multi-role granular seperti editor, auditor, atau viewer.
Tidak membangun fitur user provisioning otomatis dua arah ke Keycloak.
Tidak membangun 2FA pada fase ini, kecuali sudah disediakan oleh Keycloak.
Tidak membuat aplikasi shortlink sebagai identity provider.
6. Asumsi dan Prasyarat
Sebelum fitur ini diimplementasikan, prasyarat berikut dianggap sudah tersedia:
Keycloak sudah berjalan dan dapat diakses oleh aplikasi.
Keycloak sudah terhubung dengan LDAP internal.
Aplikasi shortlink dapat didaftarkan sebagai client di Keycloak.
Aplikasi menggunakan HTTPS.
Laravel memiliki akses ke database MySQL.
Tim infrastruktur dapat membuat client Keycloak dengan tipe confidential.
Claim minimal dari Keycloak tersedia:
text
1234
subpreferred_usernameemailname
7. Istilah
Istilah
Arti
Authentication
Proses membuktikan identitas user
Authorization
Proses menentukan hak akses user
SSO
Single Sign-On
Keycloak
Identity provider internal
LDAP
Direktori user internal
OIDC
OpenID Connect
Role
Hak akses di aplikasi: user atau admin
Local admin
Akun darurat yang disimpan di database aplikasi
Protected account
Akun penting yang dilindungi dari perubahan sembarangan
Break-glass account
Akun darurat untuk pemulihan sistem
8. Arsitektur Sistem
8.1 Arsitektur Umum
text
123456789
User ↓Browser ↓Aplikasi Shortlink Laravel ↓Keycloak ↓LDAP Internal
Untuk login normal:
text
1234567891011121314151617
User membuka aplikasi↓Aplikasi mengarahkan user ke Keycloak↓User login di Keycloak↓Keycloak memverifikasi user ke LDAP jika diperlukan↓Keycloak mengembalikan token OIDC ke aplikasi↓Aplikasi memvalidasi token↓Aplikasi membuat atau memperbarui data user di MySQL↓Aplikasi menentukan role user dari MySQL↓User masuk ke aplikasi
8.2 Pembagian Tanggung Jawab
Komponen
Tanggung Jawab
LDAP
Sumber data user internal
Keycloak
Autentikasi user, issuer token OIDC
Laravel
Validasi token, provisioning user, role management, audit
MySQL
Menyimpan role aplikasi, akun lokal, log perubahan role
9. Persona
9.1 User Biasa
User internal yang login menggunakan akun LDAP melalui Keycloak.
Hak akses:
text
123
LoginMenggunakan fitur shortlink sesuai kebutuhan dasarMelihat data miliknya sendiri jika relevan
9.2 Admin Aplikasi
User yang login melalui Keycloak dan diberi role admin di aplikasi.
Hak akses:
text
12345
Semua hak user biasaMengakses halaman adminMengelola role userMelihat daftar user yang pernah loginMelihat audit log jika diberikan akses
9.3 Local Super Admin
Akun lokal yang disimpan di database aplikasi.
Digunakan untuk:
text
1234
Pemulihan sistemKeycloak downAdmin Keycloak/LDAP tidak tersediaBootstrap admin pertama
Hak akses:
text
12
Sama dengan admin aplikasiMemiliki proteksi khusus sebagai akun darurat
10. User Stories
User Biasa
text
1
Sebagai user internal, saya ingin login menggunakan akun kantor melalui SSO agar tidak perlu mendaftar akun baru.
text
1
Sebagai user biasa, saya ingin tetap memiliki role user secara default agar tidak semua orang bisa mengakses halaman admin.
Admin Aplikasi
text
1
Sebagai admin, saya ingin melihat daftar user yang pernah login agar saya bisa menentukan siapa yang perlu menjadi admin.
text
1
Sebagai admin, saya ingin mengubah role user menjadi admin atau user biasa tanpa harus mengakses database secara manual.
Super Admin / System Owner
text
1
Sebagai pemilik sistem, saya ingin memiliki akun lokal darurat agar aplikasi tetap bisa dikelola saat Keycloak bermasalah.
text
1
Sebagai auditor, saya ingin setiap perubahan role tercatat agar bisa ditelusuri siapa yang mengubah dan kapan.
11. Kebutuhan Fungsional
FR-01 — Login Utama Menggunakan Keycloak
Sistem harus menyediakan login utama melalui Keycloak menggunakan protokol OpenID Connect.
Flow:
text
123456789101112131415
User membuka aplikasi↓User belum login↓Aplikasi redirect ke halaman login Keycloak↓User melakukan autentikasi↓Keycloak redirect kembali ke aplikasi dengan authorization code↓Aplikasi menukar code menjadi token↓Aplikasi memvalidasi token↓Aplikasi login user ke sesi Laravel
Ketentuan:
Aplikasi tidak menyimpan password user LDAP.
Aplikasi tidak melakukan bind langsung ke LDAP.
Aplikasi hanya menerima user yang sudah diverifikasi oleh Keycloak.
Aplikasi wajib memvalidasi token dari Keycloak.
Validasi token minimal:
text
12345
Signature token validIssuer sesuaiAudience sesuaiToken belum expiredState parameter valid untuk mencegah CSRF
FR-02 — Provisioning User Otomatis
Setiap user yang pertama kali login melalui Keycloak harus dibuatkan record di database aplikasi.
Data yang disimpan minimal:
text
1234567
keycloak_subusernamenameemailauth_provider = keycloakrole = userlast_login_at
Jika user sudah ada, aplikasi melakukan update:
text
1234
nameemailusernamelast_login_at
Ketentuan:
keycloak_sub menjadi identifier utama yang stabil.
Username boleh berubah, tetapi keycloak_sub tidak boleh berubah.
User baru default role adalah user.
Username disimpan dalam format lowercase.
FR-03 — Role Management di Aplikasi
Sistem harus menyediakan dua role aplikasi:
text
12
useradmin
Role ditentukan oleh aplikasi, bukan oleh Keycloak.
Ketentuan:
User yang login pertama kali tidak otomatis menjadi admin.
Role admin diberikan manual melalui halaman admin.
Perubahan role harus disimpan di database aplikasi.
Perubahan role harus langsung berlaku pada request berikutnya.
Middleware admin harus membaca role dari database, bukan hanya dari session.
FR-04 — Halaman Manajemen User
Sistem harus menyediakan halaman admin untuk mengelola user.
Menu:
text
12
Admin└── Users
Fitur:
Melihat daftar user yang pernah login.
Melihat username.
Melihat nama.
Melihat email.
Melihat sumber akun: Keycloak atau local.
Melihat role saat ini.
Melihat waktu login terakhir.
Mengubah role user.
Mencari user berdasarkan username, nama, atau email.
Filter berdasarkan role atau sumber akun.
Aksi:
text
12
Jadikan AdminJadikan User Biasa
FR-05 — Local Emergency Login
Sistem harus menyediakan login lokal darurat.
Login lokal ini tidak menggunakan Keycloak.
Tujuan:
text
123
Recovery saat Keycloak downRecovery saat LDAP bermasalahBootstrap admin pertama
Ketentuan:
Login lokal hanya untuk akun dengan auth_provider = local.
Password lokal harus di-hash menggunakan Argon2id atau bcrypt.
Login lokal harus dilindungi rate limiting.
Login lokal sebaiknya tidak dipromosikan sebagai login utama.
Hanya akun lokal yang aktif yang dapat login.
Login lokal wajib dicatat dalam audit log.
Rekomendasi URL:
text
1
/login/local
Form login utama tetap优先 menggunakan Keycloak.
FR-06 — Bootstrap Super Admin
Sistem harus menyediakan cara untuk membuat super admin pertama.
Metode yang direkomendasikan:
bash
1
php artisan app:create-local-admin
Command akan meminta:
text
1234
UsernameNamaPasswordKonfirmasi password
Lalu menyimpan:
text
12345
auth_provider = localrole = adminis_protected = trueis_active = truepassword = hashed
Ketentuan:
Command tidak menampilkan password di console.
Password minimal 12 karakter.
Username local admin tidak boleh sama dengan user Keycloak yang sudah ada.
Jika username sudah dipakai, proses dibatalkan.
FR-07 — Proteksi Akun Penting
Akun lokal darurat harus diberi tanda:
text
1
is_protected = true
Ketentuan:
Admin yang login melalui Keycloak tidak boleh mengubah role akun protected.
Admin yang login melalui Keycloak tidak boleh menonaktifkan akun protected.
Admin yang login melalui Keycloak tidak boleh menghapus akun protected.
Perubahan terhadap akun protected hanya boleh dilakukan oleh akun lokal.
Sistem harus mencegah penghapusan semua local admin aktif.
FR-08 — Audit Log Perubahan Role
Setiap perubahan role wajib dicatat.
Data audit minimal:
text
1234567
AktorTarget userRole lamaRole baruAlamat IPUser agentWaktu perubahan
Audit log harus:
text
123
Read-only dari UITidak dapat dihapus oleh admin biasaDisimpan di database
FR-09 — Logout
Sistem harus menyediakan logout dari aplikasi.
Saat logout:
text
123
Sesi Laravel dihancurkanCSRF token di-resetUser tidak lagi memiliki akses ke halaman terproteksi
Opsional untuk fase lanjutan:
text
1
Logout juga mengarahkan user ke endpoint logout Keycloak
12. Kebutuhan Non-Fungsional
12.1 Keamanan
Semua endpoint admin wajib melewati middleware autentikasi dan admin.
Password lokal wajib di-hash.
Tidak boleh menyimpan password LDAP atau Keycloak di database aplikasi.
Token Keycloak tidak boleh dicatat di log aplikasi.
Login lokal wajib menggunakan rate limiting.
Semua form POST/PATCH wajib menggunakan CSRF protection.
Perubahan role wajib menggunakan metode PATCH atau POST, bukan GET.
Session harus menggunakan driver yang aman, direkomendasikan database atau redis.
Cookie session harus menggunakan secure dan httpOnly.
12.2 Ketersediaan
Aplikasi tetap harus bisa diakses oleh local admin saat Keycloak down.
Jika Keycloak down, user SSO tidak dapat login, tetapi aplikasi tidak boleh crash.
Aplikasi harus menampilkan pesan yang jelas jika Keycloak tidak dapat dihubungi.
12.3 Kinerja
Validasi role harus cepat karena diambil dari database aplikasi.
Panggilan ke Keycloak hanya terjadi saat proses login, bukan pada setiap request.
Query daftar user harus mendukung pagination.
12.4 Auditability
Setiap perubahan role harus dapat ditelusuri.
Audit log harus menampilkan waktu dalam zona waktu yang disepakati.
Audit log tidak boleh diubah dari UI.
12.5 Maintainability
Logika Keycloak harus berada dalam service terpisah.
Controller tidak boleh berisi logika validasi token secara manual.
Role check harus terpusat di middleware/policy.
Konfigurasi Keycloak disimpan di environment variable.
13. Desain Database MySQL
13.1 Tabel users
sql
123456789101112131415
CREATE TABLE users (    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,    keycloak_sub VARCHAR(255) NULL UNIQUE,    username VARCHAR(100) NOT NULL UNIQUE,    name VARCHAR(150) NULL,    email VARCHAR(150) NULL,    password VARCHAR(255) NULL,    auth_provider ENUM('keycloak', 'local') NOT NULL DEFAULT 'keycloak',    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',    is_protected BOOLEAN NOT NULL DEFAULT FALSE,    is_active BOOLEAN NOT NULL DEFAULT TRUE,    last_login_at TIMESTAMP NULL,    created_at TIMESTAMP NULL,    updated_at TIMESTAMP NULL);
Penjelasan:
Kolom
Fungsi
keycloak_sub
ID unik dari Keycloak, stabil dan tidak berubah
username
Username unik untuk tampilan dan pencarian
password
Hanya digunakan oleh akun lokal
auth_provider
Membedakan akun Keycloak dan akun lokal
role
Role aplikasi: user atau admin
is_protected
Menandai akun penting yang dilindungi
is_active
Menonaktifkan akun jika diperlukan
Catatan penting:
text
1234567
Akun Keycloak:- keycloak_sub wajib diisi- password NULLAkun lokal:- keycloak_sub NULL- password wajib diisi dan di-hash
13.2 Tabel role_change_logs
sql
123456789101112
CREATE TABLE role_change_logs (    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,    actor_user_id BIGINT UNSIGNED NULL,    target_user_id BIGINT UNSIGNED NOT NULL,    old_role ENUM('user', 'admin') NOT NULL,    new_role ENUM('user', 'admin') NOT NULL,    ip_address VARCHAR(45) NULL,    user_agent VARCHAR(255) NULL,    created_at TIMESTAMP NULL,    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL,    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE);
13.3 Tabel login_audit_logs opsional
Jika ingin audit login lebih lengkap:
sql
1234567891011
CREATE TABLE login_audit_logs (    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,    user_id BIGINT UNSIGNED NULL,    username VARCHAR(100) NULL,    auth_provider ENUM('keycloak', 'local') NULL,    status ENUM('success', 'failed') NOT NULL,    ip_address VARCHAR(45) NULL,    user_agent VARCHAR(255) NULL,    created_at TIMESTAMP NULL,    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL);
14. Desain Teknis Laravel
14.1 Struktur Login
Aplikasi memiliki dua jalur login:
text
12
1. Login SSO Keycloak2. Login lokal darurat
Login utama:
text
12
GET /auth/keycloak/redirectGET /auth/keycloak/callback
Login lokal darurat:
text
12
GET  /login/localPOST /login/local
14.2 Routes
php
12345678910111213141516171819202122232425
use App\Http\Controllers\Auth\KeycloakAuthController;use App\Http\Controllers\Auth\LocalAuthController;use App\Http\Controllers\Admin\UserController;use App\Http\Controllers\Admin\RoleChangeLogController;Route::get('/', fn () => view('welcome'));Route::middleware('guest')->group(function () {    Route::get('/auth/keycloak/redirect', [KeycloakAuthController::class, 'redirect']);    Route::get('/auth/keycloak/callback', [KeycloakAuthController::class, 'callback']);    Route::get('/login/local', [LocalAuthController::class, 'showLoginForm']);    Route::post('/login/local', [LocalAuthController::class, 'login'])        ->middleware('throttle:5,1');});Route::middleware('auth')->group(function () {    Route::post('/logout', [KeycloakAuthController::class, 'logout'])->name('logout');});Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {    Route::get('/users', [UserController::class, 'index'])->name('users.index');    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');    Route::get('/role-logs', [RoleChangeLogController::class, 'index'])->name('role-logs.index');});
14.3 Middleware Admin
php
12345678910111213141516171819202122
namespace App\Http\Middleware;use Closure;use Illuminate\Http\Request;class EnsureUserIsAdmin{    public function handle(Request $request, Closure $next)    {        $user = $request->user();        if (! $user) {            abort(403, 'Akses ditolak.');        }        if ($user->role !== 'admin') {            abort(403, 'Akses hanya untuk administrator.');        }        return $next($request);    }}
Registrasi alias middleware:
php
1
'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
14.4 Logika Callback Keycloak
Gambaran logika:
php
12345678910111213
public function callback(Request $request){    // 1. Validasi state.    // 2. Tukar authorization code dengan token.    // 3. Validasi token.    // 4. Ambil claim: sub, preferred_username, name, email.    // 5. Cari user berdasarkan keycloak_sub.    // 6. Jika tidak ada, buat user baru dengan role user.    // 7. Jika ada, update username, name, email.    // 8. Update last_login_at.    // 9. Login user ke Laravel Auth.    // 10. Redirect ke dashboard.}
Pseudocode inti:
php
123456789101112131415161718192021
$keycloakSub = $claims['sub'];$username = strtolower($claims['preferred_username']);$user = User::updateOrCreate(    [        'keycloak_sub' => $keycloakSub,    ],    [        'username' => $username,        'name' => $claims['name'] ?? $username,        'email' => $claims['email'] ?? null,        'auth_provider' => 'keycloak',        'is_active' => true,    ]);$user->update([    'last_login_at' => now(),]);Auth::login($user);
Catatan penting:
text
12
Jangan gunakan username sebagai kunci utama provisioning.Gunakan keycloak_sub sebagai identitas yang stabil.
14.5 Logika Login Lokal
php
12345678910111213141516171819202122232425262728
public function login(Request $request){    $credentials = $request->validate([        'username' => ['required', 'string'],        'password' => ['required', 'string'],    ]);    $username = strtolower($credentials['username']);    $user = User::where('username', $username)        ->where('auth_provider', 'local')        ->where('is_active', true)        ->first();    if (! $user || ! Hash::check($credentials['password'], $user->password)) {        return back()->withErrors([            'username' => 'Kredensial tidak valid.',        ]);    }    $user->update([        'last_login_at' => now(),    ]);    Auth::login($user);    return redirect()->intended('/admin/users');}
14.6 Logika Update Role
php
12345678910111213141516171819202122232425262728293031323334
public function updateRole(Request $request, User $user){    $validated = $request->validate([        'role' => ['required', 'in:user,admin'],    ]);    $actor = $request->user();    if ($user->is_protected && $actor->auth_provider !== 'local') {        abort(403, 'Akun terlindungi tidak dapat diubah oleh admin non-lokal.');    }    if ($user->id === $actor->id) {        abort(400, 'Anda tidak dapat mengubah role akun sendiri.');    }    $oldRole = $user->role;    $newRole = $validated['role'];    if ($oldRole !== $newRole) {        $user->update(['role' => $newRole]);        RoleChangeLog::create([            'actor_user_id' => $actor->id,            'target_user_id' => $user->id,            'old_role' => $oldRole,            'new_role' => $newRole,            'ip_address' => $request->ip(),            'user_agent' => substr($request->userAgent(), 0, 255),        ]);    }    return back()->with('success', 'Role berhasil diperbarui.');}
14.7 Environment Configuration
Contoh .env:
env
123456
KEYCLOAK_BASE_URL=https://keycloak.internal.localKEYCLOAK_REALM=internalKEYCLOAK_CLIENT_ID=shortlink-appKEYCLOAK_CLIENT_SECRET=change-this-secretKEYCLOAK_REDIRECT_URI=https://shortlink.internal.local/auth/keycloak/callbackKEYCLOAK_LOGOUT_REDIRECT_URI=https://shortlink.internal.local
Jangan menyimpan secret di dalam source code.
14.8 Rekomendasi Package
Pilih salah satu pendekatan berikut:
Opsi 1 — Socialite + Keycloak Provider
Cocok jika ingin implementasi OAuth/OIDC yang umum di Laravel.
Contoh package:
text
12
laravel/socialitesocialiteproviders/keycloak
Opsi 2 — Package Keycloak Laravel
Cocok jika ingin integrasi Keycloak yang lebih spesifik.
Contoh package:
text
1
robsontenorio/laravel-keycloak
Rekomendasi:
text
12
Gunakan package yang sudah mature.Hindari memvalidasi JWT secara manual kecuali ada kebutuhan khusus dan tim benar-benar memahami OIDC.
15. Desain UI/UX
15.1 Halaman Login Utama
Halaman login utama cukup sederhana.
Isi:
text
1234
Logo aplikasiJudul aplikasiTombol: Login dengan SSOLink kecil: Login darurat
Contoh:
text
12345
Aplikasi Shortlink Internal[ Login dengan SSO ]Login darurat hanya untuk administrator sistem.
15.2 Halaman Login Lokal
Halaman login lokal hanya untuk kondisi darurat.
Isi:
text
1234
UsernamePasswordTombol LoginPeringatan bahwa jalur ini hanya untuk emergency
Contoh peringatan:
text
12
Jalur login ini hanya digunakan saat SSO tidak tersedia.Semua aktivitas login darurat akan dicatat.
15.3 Halaman Admin Users
Kolom tabel:
text
1234567
UsernameNamaEmailSumber AkunRoleLogin TerakhirAksi
Aksi:
text
12
Jadikan AdminJadikan User
Untuk akun protected:
text
12
Tampilkan badge "Protected"Nonaktifkan tombol perubahan role jika actor bukan local admin
15.4 Halaman Audit Log
Kolom:
text
123456
WaktuAktorTargetRole LamaRole BaruIP Address
Fitur:
text
1234
Filter berdasarkan tanggalFilter berdasarkan aktorFilter berdasarkan targetPagination
16. Keamanan
16.1 Validasi Token Keycloak
Aplikasi wajib memastikan token berasal dari Keycloak yang sah.
Validasi minimal:
text
12345
iss sesuai dengan issuer Keycloakaud sesuai dengan client ID aplikasiexp belum lewatsignature validstate valid
16.2 Session
Gunakan session driver database atau redis.
Session harus timeout setelah periode tertentu.
Setelah logout, session harus benar-benar dihancurkan.
Setelah perubahan role, perubahan harus langsung terbaca karena Laravel membaca user dari database pada request berikutnya.
16.3 Rate Limiting
Login lokal:
text
1
Maksimal 5 percobaan per menit per IP atau username
Callback Keycloak:
text
1
Tidak perlu rate limit agresif, tetapi wajib validasi state
16.4 Audit
Setiap kejadian penting dicatat:
text
1234
Login lokal suksesLogin lokal gagalPerubahan roleUpaya mengubah akun protected
16.5 Proteksi Akun
Akun local admin tidak boleh diubah oleh admin Keycloak.
Akun local admin tidak boleh dihapus jika merupakan satu-satunya local admin aktif.
Username local admin harus di-reserve agar tidak digunakan oleh user Keycloak.
Username seperti root, admin, superadmin disarankan tidak digunakan oleh user Keycloak.
17. Edge Cases dan Penanganan
17.1 Keycloak Down
Kondisi:
text
1
User tidak bisa login via SSO
Penanganan:
text
12
Tampilkan pesan bahwa SSO sedang tidak tersediaLocal admin tetap dapat login melalui /login/local
17.2 LDAP Down
Kondisi:
text
1
Keycloak mungkin masih hidup, tetapi tidak dapat memverifikasi user ke LDAP
Penanganan:
text
12
Ikuti kebijakan Keycloak internalLocal admin tetap menjadi jalur darurat aplikasi
17.3 User Baru Pertama Kali Login
Kondisi:
text
1
User valid di Keycloak tetapi belum ada di database aplikasi
Penanganan:
text
1234
Buat user baruauth_provider = keycloakrole = useris_active = true
17.4 Username Berubah di Keycloak
Kondisi:
text
1
Username user berubah di LDAP/Keycloak
Penanganan:
text
12
Aplikasi tidak kehilangan user karena menggunakan keycloak_subUsername di database diperbarui saat login berikutnya
17.5 User Dinonaktifkan di Keycloak
Kondisi:
text
1
User dinonaktifkan di LDAP/Keycloak setelah sempat login
Penanganan fase awal:
text
12
User tidak bisa login baruSession lama akan habis sesuai lifetime session aplikasi
Penanganan lanjutan opsional:
text
123
Gunakan session lifetime pendekLakukan validasi ulang ke Keycloak pada interval tertentuGunakan token introspection jika dibutuhkan
17.6 Admin Menurunkan Role Diri Sendiri
Kondisi:
text
1
Admin mencoba mengubah role dirinya sendiri
Penanganan:
text
12
Tolak aksi tersebutTampilkan pesan bahwa admin tidak dapat mengubah role akun sendiri
17.7 Admin Keycloak Mencoba Mengubah Local Admin
Kondisi:
text
1
Admin yang login via Keycloak mencoba mengubah local protected admin
Penanganan:
text
12
TolakTampilkan pesan bahwa akun protected hanya dapat diubah oleh local admin
17.8 Semua Local Admin Terhapus
Kondisi:
text
1
Tidak ada local admin aktif tersisa
Penanganan:
text
1
Sistem menolak penghapusan atau penonaktifan local admin aktif terakhir
18. Acceptance Criteria
18.1 Login SSO
User dapat login menggunakan Keycloak.
Aplikasi menerima token OIDC dari Keycloak.
Aplikasi memvalidasi token sebelum login.
User baru otomatis dibuat dengan role user.
User lama diperbarui data profil dan last_login_at.
User tidak perlu mengisi form username/password di aplikasi untuk login normal.
18.2 Role
User baru default role adalah user.
Admin dapat mengubah role user menjadi admin.
Admin dapat menurunkan role admin menjadi user.
Perubahan role langsung berlaku pada request berikutnya.
User biasa tidak dapat mengakses halaman admin.
18.3 Local Admin
Local admin dapat dibuat melalui artisan command.
Local admin dapat login melalui /login/local.
Password local admin tidak disimpan dalam bentuk plain text.
Login local admin dibatasi dengan rate limiting.
Local admin tetap bisa login saat Keycloak tidak tersedia.
18.4 Proteksi
Admin Keycloak tidak dapat mengubah role local protected admin.
Admin tidak dapat mengubah role dirinya sendiri.
Sistem mencegah penghapusan local admin aktif terakhir.
Username reserved tidak dapat digunakan oleh user Keycloak.
18.5 Audit
Setiap perubahan role tercatat di role_change_logs.
Audit log menampilkan aktor, target, role lama, role baru, dan waktu.
Audit log tidak dapat diubah dari UI.
19. Milestone Implementasi
Milestone 1 — Fondasi Database & Authentication Keycloak
Durasi estimasi: 2–3 hari kerja.
Scope:
text
123456
Migration usersMigration role_change_logsKonfigurasi Keycloak clientImplementasi login KeycloakProvisioning user otomatisLogin/logout dasar
Output:
text
12
User bisa login melalui KeycloakUser tersimpan di database dengan role default user
Milestone 2 — Local Emergency Admin
Durasi estimasi: 1 hari kerja.
Scope:
text
12345
Login lokalArtisan command create local adminHash passwordRate limitingProteksi akun local
Output:
text
1
Super admin lokal dapat login saat Keycloak down
Milestone 3 — Manajemen Role Admin
Durasi estimasi: 2 hari kerja.
Scope:
text
12345
Halaman admin usersUbah role userMiddleware adminProteksi route adminValidasi perubahan role
Output:
text
1
Admin dapat mengangkat dan menurunkan role user dari UI
Milestone 4 — Audit & Hardening
Durasi estimasi: 1–2 hari kerja.
Scope:
text
123456
Audit log roleAudit login lokal opsionalTesting edge casePerbaikan sessionRate limitingError handling Keycloak
Output:
text
1
Sistem lebih aman, audit siap, dan siap dipakai internal
20. Risiko dan Mitigasi
Risiko
Dampak
Mitigasi
Keycloak down
User SSO tidak bisa login
Sediakan local admin emergency
LDAP down
Autentikasi Keycloak terganggu
Local admin emergency + monitoring Keycloak
Token tidak tervalidasi dengan benar
Risiko keamanan
Gunakan package OIDC yang matang dan konfigurasi issuer/audience dengan benar
Username bentrok
User Keycloak tidak bisa login
Gunakan keycloak_sub sebagai identitas utama
Session lama tetap aktif setelah user dinonaktifkan
Risiko akses tidak sah
Session lifetime terbatas dan future enhancement token introspection
Admin menyalahgunakan role
Perubahan role tidak terkontrol
Audit log dan proteksi akun penting
Local admin menjadi celah
Risiko backdoor
Hash kuat, rate limit, audit, gunakan hanya saat darurat
21. Konfigurasi Keycloak yang Dibutuhkan
Tim aplikasi membutuhkan client Keycloak dengan konfigurasi minimal:
text
1234567891011
Client ID: shortlink-appClient type: OpenID ConnectClient authentication: ONValid redirect URI:https://shortlink.internal.local/auth/keycloak/callbackValid post logout redirect URI:https://shortlink.internal.localScopes:openidprofileemail
Claim yang dibutuhkan aplikasi:
text
1234
subpreferred_usernamenameemail
22. Batasan yang Perlu Dipahami Bersama
Aplikasi tidak mengambil role dari LDAP.
Aplikasi tidak mengambil role dari Keycloak pada fase ini.
Role aplikasi berasal dari database aplikasi shortlink.
Admin aplikasi tidak otomatis dibuat dari LDAP.
User LDAP yang valid hanya berarti user tersebut boleh login, bukan berarti otomatis admin.
Local admin bukan akun LDAP, tetapi tetap memiliki hak admin di aplikasi.
Keycloak adalah sumber autentikasi utama untuk user internal.
23. Kesimpulan Final
Arsitektur akhir yang digunakan adalah:
text
1234567891011121314
User ↓Login melalui Keycloak SSO ↓Keycloak memverifikasi identitas, termasuk user LDAP ↓Aplikasi shortlink menerima token valid ↓Aplikasi membuat atau memperbarui data user ↓Aplikasi mengecek role di database lokal ↓Jika role admin → akses adminJika role user → akses user biasa
Untuk kondisi darurat:
text
12345
Local admin login langsung ke aplikasi↓Aplikasi memverifikasi password lokal↓Local admin mendapatkan akses admin
Dengan desain ini:
text
1234
Autentikasi terpusat di KeycloakOtorisasi aplikasi tetap fleksibel di LaravelAdmin tetap bisa dikelola dari aplikasiSistem tetap punya jalur pemulihan saat SSO bermasalah