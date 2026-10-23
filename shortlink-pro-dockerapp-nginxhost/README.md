# Shortlink Pro - Deploy Produksi (Docker + nginx host + Cloudflare Tunnel)

Folder ini berisi berkas deploy untuk **satu server**: aplikasi berjalan di
Docker, nginx di host menjadi reverse proxy, dan Cloudflare Tunnel menjadi
satu-satunya pintu masuk publik. **Tidak ada TLS di server ini** - sertifikat
dan HTTPS ditangani Cloudflare.

## 1. Arsitektur

```text
Pengunjung (HTTPS)
      |
      v
Cloudflare (TLS, WAF, DNS)
      |
      v
cloudflared di host  --HTTP-->  nginx 127.0.0.1:80
                                     |
                                     v
                          container app 127.0.0.1:9000
                                     |
                                     v
                          container db (MySQL, network internal)
```

Port yang terbuka ke host hanya loopback:

- `127.0.0.1:9000` - container aplikasi
- `127.0.0.1:80` - nginx (hanya cloudflared yang menghubungi)

Database tidak pernah membuka port ke host dan berada di network
`backend` dengan `internal: true` (tanpa akses internet).

## 2. Isi folder

| Berkas | Fungsi |
| --- | --- |
| `Dockerfile` | Build image produksi 4 stage: `base` -> `vendor` -> `assets` -> `runtime` |
| `php.ini` | Hardening PHP (`expose_php=Off`, upload 8M, OPcache immutable, upload progress Off) |
| `apache/shortlink-pro.conf` | Virtual host Apache di container (port 9000, log ke stdout) |
| `entrypoint.sh` | Tunggu DB, migrasi, cache Laravel, lalu jalankan Apache |
| `docker-compose.fulldb.yaml` | App + MySQL + scheduler (semua di Docker) |
| `docker-compose.app.yaml` | App + scheduler saja, MySQL eksternal |
| `nginx/shortlink-pro.conf` | Reverse proxy host, HTTP saja, listen loopback |
| `install.sh` | Memasang konfigurasi nginx di host |
| `.env.example.fulldb` / `.env.example.app` | Template environment |

Folder ini **harus tetap berada di dalam root repo** karena konteks build
compose adalah `..` (root repo). Jangan dipindah ke luar.

## 3. Prasyarat

- Docker Engine + plugin Compose v2
- nginx di host (Debian/Ubuntu: `apt install nginx`)
- `cloudflared` di host
- Domain yang sudah aktif di Cloudflare

## 4. Deploy pertama (app + db)

```bash
# 1. Environment
cd shortlink-pro-dockerapp-nginxhost
cp .env.example.fulldb .env
#    - isi APP_URL dengan domain tunnel
#    - ganti juga shortlink.example.com pada KEYCLOAK_*/GOOGLE_*/MAIL_FROM_ADDRESS
#      bila fitur tersebut dipakai
#    - isi DB_PASSWORD yang kuat
#    - isi APP_KEY:
php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"

# 2. Kata sandi root MySQL (tidak pernah masuk environment aplikasi)
mkdir -p secrets
openssl rand -base64 24 > secrets/db_root_password.txt
chmod 600 secrets/db_root_password.txt

# 3. Build + jalankan (dari root repo)
docker compose -f shortlink-pro-dockerapp-nginxhost/docker-compose.fulldb.yaml up -d --build

# 4. Cek
docker compose -f shortlink-pro-dockerapp-nginxhost/docker-compose.fulldb.yaml ps
curl -fsS http://127.0.0.1:9000/up && echo " OK"
```

Saat start, `entrypoint.sh` menunggu MySQL, menjalankan `migrate --force`,
lalu `php artisan optimize`. Service `scheduler` memakai image yang sama
dengan `RUN_MIGRATIONS=false` agar migrasi tidak berjalan ganda.

## 5. Pasang reverse proxy nginx di host

```bash
cd shortlink-pro-dockerapp-nginxhost
sudo ./install.sh --domain short.example.com
```

Skrip akan:

1. mengganti `__DOMAIN__` pada `nginx/shortlink-pro.conf`,
2. memasangnya ke `/etc/nginx/sites-available/shortlink-pro.conf`,
3. mengaktifkan symlink, `nginx -t`, lalu reload,
4. mengecek `http://127.0.0.1:9000/up`.

Default `listen 127.0.0.1:80` - hanya proses lokal yang bisa menghubungi
origin. Gunakan `--listen-all` **hanya** bila cloudflared berjalan di mesin
atau network lain, dan batasi dengan firewall. Jika paket nginx membawa site
`default` yang mendengarkan port 80, hapus symlink-nya
(`rm /etc/nginx/sites-enabled/default`) supaya port 80 tidak terbuka ke publik.

## 6. Cloudflare Tunnel

```bash
# Cepat (tanpa akun tunnel permanen)
cloudflared tunnel --url http://127.0.0.1:80
```

Untuk tunnel permanen, arahkan ingress ke origin yang sama:

```yaml
tunnel: <UUID>
credentials-file: /root/.cloudflared/<UUID>.json
ingress:
  - hostname: short.example.com
    service: http://127.0.0.1:80
  - service: http_status:404
```

Lalu buat DNS CNAME `short.example.com` -> `<UUID>.cfargotunnel.com`
(atau `cloudflared tunnel route dns <nama> short.example.com`).

Catatan penting:

- `APP_URL` **harus** sama dengan domain tunnel dan memakai `https://`.
- nginx selalu mengirim `X-Forwarded-Proto: https`; aplikasi sudah
  mempercayai proxy loopback/Docker sehingga URL dan secure cookie benar.
- Jangan membuka port 80 server ke internet. Origin HTTP tanpa TLS hanya
  boleh dijangkau cloudflared.

## 7. Hardening yang sudah diterapkan

| Lapisan | Tindakan |
| --- | --- |
| Image | PHP 8.3.36 (patch PHP2Shell), 4 stage, tanpa compiler/composer/node di runtime |
| Container app | `read_only`, `cap_drop: ALL` + hanya CHOWN/SETUID/SETGID/KILL, `no-new-privileges`, tmpfs untuk `/tmp`, `/run`, `bootstrap/cache`; perintah artisan/scheduler berjalan sebagai `www-data` |
| Jaringan | app hanya publish ke `127.0.0.1:9000`; db di network `internal` tanpa port host |
| Apache | port 9000, `AllowOverride None`, `-Indexes`, `ServerTokens Prod`, `TraceEnable Off`, log ke stdout |
| PHP | `expose_php=Off`, `display_errors=Off`, upload 8M/post 10M, `session.upload_progress` Off, cookie HttpOnly+Secure+SameSite=Lax |
| Rahasia | `APP_KEY` dan DB password dari env; root MySQL dari Docker secret; `.env` tidak masuk image |
| nginx host | `server_tokens off`, listen loopback, tidak ada direktori SSL |

Yang **tidak** dilakukan otomatis dan tetap tanggung jawab operator:

- firewall host (hanya 22/80-loopback/443-cloudflared),
- pembaruan image dasar (`docker compose build --pull`),
- backup volume `db-data` dan `app-storage`,
- rotasi `APP_KEY`/kata sandi,
- pembatasan akses ke panel admin.

## 8. Operasi harian

```bash
# Log aplikasi (LOG_CHANNEL=stderr)
docker compose -f shortlink-pro-dockerapp-nginxhost/docker-compose.fulldb.yaml logs -f app

# Migrasi manual
docker compose -f shortlink-pro-dockerapp-nginxhost/docker-compose.fulldb.yaml exec app php artisan migrate --force

# Backup database
docker compose -f shortlink-pro-dockerapp-nginxhost/docker-compose.fulldb.yaml exec -T db \
  sh -c 'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > backup-$(date +%F).sql

# Backup berkas unggahan/QR
docker run --rm -v shortlink-pro_app-storage:/data -v "$PWD":/backup alpine \
  tar czf /backup/storage-$(date +%F).tar.gz -C /data .

# Update kode
git pull
docker compose -f shortlink-pro-dockerapp-nginxhost/docker-compose.fulldb.yaml up -d --build
```

`entrypoint.sh` menjalankan `php artisan optimize` setiap start, jadi cache
konfigurasi/rute/view selalu dibuat ulang dari image terbaru.

## 9. Troubleshooting

| Gejala | Penyebab umum | Tindakan |
| --- | --- | --- |
| Container app restart terus | `APP_KEY` kosong | Isi `APP_KEY` di `.env` |
| `SQLSTATE[HY000] [2002]` | DB belum siap / kredensial salah | Cek `docker compose logs db`, cocokkan `DB_*` |
| 502 dari nginx | app belum healthy | `docker compose ps`, cek `/up` di 9000 |
| 500 setelah login | cookie secure diakses via HTTP | Uji lewat domain tunnel (HTTPS), bukan `http://ip` |
| Aset/CSS 404 | `public/build` tidak ada | Build ulang image (`--build`, jangan cache `assets`) |
| Upload > 8 MB gagal | batas PHP | Naikkan `upload_max_filesize`/`post_max_size` dan `client_max_body_size` |
| Perubahan kode tidak terlihat | OPcache `validate_timestamps=0` | Build ulang image, bukan sekadar restart |
| `DB_PASSWORD` salah / DB gagal init | karakter `$` di `.env` dianggap variabel oleh compose | Ganti karakter `$` atau tulis `$$` |

## 10. Checklist sebelum go-live

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` terisi
- [ ] `APP_URL` = domain tunnel (`https://...`)
- [ ] `DEMO_ACCOUNT_PASSWORD` kosong
- [ ] `PEMENDEK_SAFE_BROWSING_KEY` terisi (fitur pemindaian aktif)
- [ ] `secrets/db_root_password.txt` ada dan `chmod 600`
- [ ] `.env` tidak ikut ter-commit (`git status` bersih)
- [ ] `docker compose config` valid
- [ ] `curl -I https://<domain>/up` -> 200
- [ ] Login, buat tautan, buka tautan pendek, dan buka `/storage/...` berhasil
- [ ] `docker compose logs app` tanpa error berulang
