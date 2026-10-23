# =============================================================================
# Shortlink Pro - contoh environment untuk docker-compose.app.yaml
# =============================================================================
# Dipakai bila MySQL dikelola di luar compose ini.
#   cp .env.example.app .env
#
# Isi DB_* sesuai server database Anda. Container app hanya punya akses
# keluar (network egress), jadi pastikan DB_PORT terbuka untuk host Docker.
# =============================================================================

# --- Compose ----------------------------------------------------------------
APP_PORT=9000
APP_IMAGE_TAG=latest
# Jangan diturunkan di bawah 8.3.36 (patch PHP2Shell).
# PHP_VERSION=8.3.36

APP_NAME="Shortlink Pro"
APP_ENV=production
APP_DEBUG=false
# Buat dengan: php -r "echo 'base64:'.base64_encode(random_bytes(32));"
APP_KEY=
APP_URL=https://shortlink.example.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stderr
LOG_LEVEL=warning

# --- Basis data eksternal ---------------------------------------------------
DB_CONNECTION=mysql
DB_HOST=db.contoh.internal
DB_PORT=3306
DB_DATABASE=shortlink_pro
DB_USERNAME=shortlink
# Hindari karakter "$" di sini: compose akan menganggapnya variabel.
DB_PASSWORD=ubah-kata-sandi-kuat

# --- Sesi & cache -----------------------------------------------------------
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# --- SSO Keycloak (kosongkan untuk menonaktifkan) ---------------------------
KEYCLOAK_BASE_URL=
KEYCLOAK_REALM=
KEYCLOAK_CLIENT_ID=
KEYCLOAK_CLIENT_SECRET=
KEYCLOAK_REDIRECT_URI=https://shortlink.example.com/auth/keycloak/callback
KEYCLOAK_LOGOUT_REDIRECT_URI=https://shortlink.example.com

# --- Masuk dengan Google (kosongkan untuk menonaktifkan) --------------------
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://shortlink.example.com/auth/google/callback

# --- Akun demo (WAJIB kosong di produksi) -----------------------------------
DEMO_ACCOUNT_PASSWORD=

# --- Pemendek tautan --------------------------------------------------------
PEMENDEK_QR_CACHE_SECONDS=86400
PEMENDEK_PASSWORD_MAX_ATTEMPTS=5
PEMENDEK_PASSWORD_LOCK_SECONDS=60
PEMENDEK_SAFE_BROWSING=true
PEMENDEK_SAFE_BROWSING_KEY=
PEMENDEK_SAFE_BROWSING_TIMEOUT_SECONDS=3

# --- Email ------------------------------------------------------------------
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="no-reply@shortlink.example.com"
MAIL_FROM_NAME="Shortlink Pro"
