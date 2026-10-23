/**
 * Konfigurasi uji E2E (e2e@0.17 / @e2e-dev/web).
 *
 * Lihat uji_e2e.md — bagian 7 PRDapp.md.
 * - Server uji: `php artisan serve` pada port acak (127.0.0.1:0).
 * - APP_URL ditimpa jadi origin yang dialokasikan, agar redirect/QR pakai port yang sama.
 * - data-hook dipakai sebagai test-id supaya getByTestId menargetkan atribut data-hook yang sudah ada.
 */
import type { E2EConfig } from "e2e";
import { web } from "@e2e-dev/web";

// Muat .env agar uji bisa memakai DEMO_ACCOUNT_PASSWORD tanpa menulis
// kredensial di kode. Bila berkasnya tidak ada, uji yang butuh akun demo
// akan di-skip dengan alasan yang jelas.
try {
  process.loadEnvFile(".env");
} catch {
  // .env tidak ada (misalnya di CI) — biarkan, uji demo yang menangani.
}

export default {
  targets: [
    {
      name: "aplikasi",
      engine: web({ browser: "chromium", testIdAttribute: "data-hook" }),
      app: {
        url: "http://127.0.0.1:0",
        command: {
          executable: "php",
          args: ["artisan", "serve", "--host=127.0.0.1", "--port={port}"],
          env: {
            APP_URL: "http://127.0.0.1:{port}",
          },
          startupTimeout: 120_000,
        },
      },
    },
  ],
  workers: 1,
} satisfies E2EConfig;
