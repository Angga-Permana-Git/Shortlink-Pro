#!/usr/bin/env bash
# =============================================================================
# Pemasangan reverse proxy nginx di host (tanpa SSL)
# =============================================================================
# Contoh:
#   sudo ./install.sh --domain short.example.com
#   sudo ./install.sh --domain short.example.com --port 9000
#   sudo ./install.sh --domain short.example.com --listen-all   # hanya bila
#       cloudflared berjalan di mesin/network lain
# =============================================================================
set -euo pipefail

DOMAIN=""
UPSTREAM_PORT="9000"
LISTEN_ALL="false"
SITE_NAME="shortlink-pro"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_CONF="${SCRIPT_DIR}/nginx/shortlink-pro.conf"

usage() {
    cat <<'TEKS'
Pemakaian: sudo ./install.sh --domain <domain> [opsi]

  --domain <domain>   Domain publik yang dipakai Cloudflare Tunnel (wajib).
  --port <port>       Port aplikasi di host (default 9000).
  --listen-all        Dengarkan port 80 di semua antarmuka (default loopback).
  -h, --help          Tampilkan bantuan ini.
TEKS
}

while [ "$#" -gt 0 ]; do
    case "$1" in
        --domain)
            if [ "$#" -lt 2 ]; then
                echo "--domain butuh nilai." >&2
                exit 1
            fi
            DOMAIN="$2"
            shift 2
            ;;
        --port)
            if [ "$#" -lt 2 ]; then
                echo "--port butuh nilai." >&2
                exit 1
            fi
            UPSTREAM_PORT="$2"
            shift 2
            ;;
        --listen-all)
            LISTEN_ALL="true"
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "Opsi tidak dikenal: $1" >&2
            usage
            exit 1
            ;;
    esac
done

if [ -z "$DOMAIN" ]; then
    echo "--domain wajib diisi." >&2
    usage
    exit 1
fi

if ! printf '%s' "$DOMAIN" | grep -Eq '^[A-Za-z0-9.-]+$'; then
    echo "Domain hanya boleh berisi huruf, angka, titik, dan tanda hubung." >&2
    exit 1
fi

if ! printf '%s' "$UPSTREAM_PORT" | grep -Eq '^[0-9]+$'; then
    echo "Port harus berupa angka." >&2
    exit 1
fi

if [ "$UPSTREAM_PORT" -lt 1 ] || [ "$UPSTREAM_PORT" -gt 65535 ]; then
    echo "Port harus di antara 1 dan 65535." >&2
    exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
    echo "Jalankan dengan sudo." >&2
    exit 1
fi

if ! command -v nginx >/dev/null 2>&1; then
    echo "nginx belum terpasang. Pasang dulu (mis. apt install nginx), lalu ulangi." >&2
    exit 1
fi

if [ ! -f "$SOURCE_CONF" ]; then
    echo "Berkas konfigurasi tidak ditemukan: $SOURCE_CONF" >&2
    exit 1
fi

TMP_CONF="$(mktemp)"
trap 'rm -f "$TMP_CONF"' EXIT

sed \
    -e "s/__DOMAIN__/${DOMAIN}/g" \
    -e "s#http://127.0.0.1:9000#http://127.0.0.1:${UPSTREAM_PORT}#g" \
    "$SOURCE_CONF" > "$TMP_CONF"

if [ "$LISTEN_ALL" = "true" ]; then
    sed -i 's/listen 127.0.0.1:80;/listen 80;/' "$TMP_CONF"
    echo "PERINGATAN: origin HTTP terbuka di semua antarmuka. Pastikan firewall" >&2
    echo "hanya mengizinkan Cloudflare/cloudflared, atau batalkan --listen-all." >&2
fi

if [ -e /etc/nginx/sites-enabled/default ]; then
    echo "PERINGATAN: site 'default' nginx masih aktif dan bisa membuka port 80" >&2
    echo "ke publik. Bila tidak dipakai: rm /etc/nginx/sites-enabled/default" >&2
fi

install -m 0644 "$TMP_CONF" "/etc/nginx/sites-available/${SITE_NAME}.conf"
ln -sfn "/etc/nginx/sites-available/${SITE_NAME}.conf" \
    "/etc/nginx/sites-enabled/${SITE_NAME}.conf"

nginx -t

if systemctl is-active --quiet nginx 2>/dev/null; then
    systemctl reload nginx
else
    nginx -s reload
fi

echo "Konfigurasi terpasang: /etc/nginx/sites-available/${SITE_NAME}.conf"
echo "Domain            : ${DOMAIN}"
echo "Upstream          : 127.0.0.1:${UPSTREAM_PORT}"

if command -v curl >/dev/null 2>&1; then
    if curl -fsS -m 5 "http://127.0.0.1:${UPSTREAM_PORT}/up" >/dev/null; then
        echo "Status aplikasi   : sehat (/up merespons)"
    else
        echo "Status aplikasi   : BELUM sehat. Cek 'docker compose ... ps' dan log container." >&2
    fi

    if curl -fsS -m 5 -H "Host: ${DOMAIN}" "http://127.0.0.1/up" >/dev/null; then
        echo "Status nginx      : sehat (Host ${DOMAIN} diteruskan ke aplikasi)"
    else
        echo "Status nginx      : BELUM sehat. Cek 'nginx -t' dan pastikan site aktif." >&2
    fi
fi

cat <<TEKS

Langkah berikutnya:
1. Pastikan container berjalan:
     cd ${SCRIPT_DIR} && docker compose -f docker-compose.fulldb.yaml ps
2. Arahkan Cloudflare Tunnel ke origin ini, misalnya:
     cloudflared tunnel --url http://127.0.0.1:80
   atau pada config.yml tunnel bernama:
     ingress:
       - service: http://127.0.0.1:80
3. Uji dari host:
     curl -I -H "Host: ${DOMAIN}" http://127.0.0.1/
TEKS
