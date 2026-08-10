Cara pakai:
# Mode 1 - gabungan app + db
docker compose up -d --build          # http://localhost:8080

# Mode 2 - terpisah (urutan penting)
docker compose -f docker-compose.db.yaml up -d
docker compose -f docker-compose.app.yaml up -d --build
Yang wajib disesuaikan (sudah diberi contoh komentar di tiap file):
- APP_URL (default http://localhost:8080) & port APP_PORT
- DB_PASSWORD, DB_ROOT_PASSWORD — wajib ganti untuk produksi
- APP_KEY — set SAMA di app & queue agar session/queue konsisten
- KEYCLOAK_BASE_URL/REALM/SECRET — isi bila mau aktifkan SSO; kosongkan jika hanya login lokal
- APP_SEED=true bila ingin auto-seed demo data saat pertama kali
Catatan: package-lock.json tidak ada di repo ini, jadi Dockerfile memakai npm install (bukan npm ci). Sebaiknya generate package-lock.json (npm install sekali di lokal) lalu ubah ke npm ci agar build reproducible.