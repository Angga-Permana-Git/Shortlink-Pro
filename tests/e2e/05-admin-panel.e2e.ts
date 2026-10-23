/**
 * Uji dashboard admin: masuk sebagai admin, lalu periksa bahwa seluruh widget
 * dashboard dan halaman pengaturan benar-benar dirender di browser.
 *
 * Widget yang gagal memuat (misalnya galat query) membuat halaman menampilkan
 * pesan galat atau widget hilang, sehingga uji ini menangkap regresi yang tidak
 * terlihat oleh uji HTTP biasa. Widget dirender bersama halaman (tidak lazy),
 * jadi teksnya harus sudah ada sejak respons pertama.
 *
 * Judul dashboard memakai "Dasbor" karena panel memakai locale id.
 */
import { test } from "@e2e-dev/web";
import { expect } from "e2e";

const PASSWORD = process.env.DEMO_ACCOUNT_PASSWORD ?? "";
const ADMIN = "admin@company.local";

test.describe("panel admin — dashboard dan halaman pengaturan", () => {
  test("admin melihat seluruh widget dashboard dan halaman pengaturan", async ({
    app,
    screen,
    browser,
  }) => {
    test.skip(PASSWORD === "", "DEMO_ACCOUNT_PASSWORD tidak tersedia di .env");

    await app.open("/login/local");
    await screen.getByLabel("Email atau Nama Pengguna").fill(ADMIN);
    await screen.getByLabel("Kata Sandi").fill(PASSWORD);
    await screen.getByRole("button", { name: /^masuk$/i }).tap();

    // ---- Dashboard ----
    await expect(screen.getByRole("heading", { name: "Dasbor" })).toBeVisible();

    const labelStat = browser.locator(".fi-wi-stats-overview-stat-label-ctn");
    for (const label of ["Pengguna Aktif", "Total Tautan", "Total Klik", "Klik Hari Ini"]) {
      await expect(labelStat.getByText(label)).toBeVisible();
    }
    for (const heading of [
      "Klik Sukses · 14 Hari",
      "Pertumbuhan · 30 Hari",
      "Tautan Terpopuler",
      "Pengguna Paling Aktif",
    ]) {
      await expect(screen.getByRole("heading", { name: heading })).toBeVisible();
    }
    await expect(labelStat.getByText("Perlu Perhatian")).toBeVisible();

    // ---- Halaman pengaturan ----
    await app.open("/admin/branding-settings");
    await expect(screen.getByRole("heading", { name: "Branding" })).toBeVisible();

    await app.open("/admin/timezone-settings");
    await expect(screen.getByRole("heading", { name: "Pengaturan Waktu" })).toBeVisible();

    // ---- Resource daftar ----
    await app.open("/admin/short-urls");
    await expect(screen.getByRole("heading", { name: /^tautan pendek$/i })).toBeVisible();

    await app.open("/admin/users");
    await expect(screen.getByRole("heading", { name: /^pengguna$/i })).toBeVisible();

    // ---- Halaman log (hanya baca) ----
    const halamanLog: Array<[string, RegExp]> = [
      ["/admin/audit-logs", /^log audit$/i],
      ["/admin/click-events", /^log klik$/i],
      ["/admin/role-change-logs", /^log perubahan peran$/i],
      ["/admin/login-audit-logs", /^log masuk$/i],
    ];
    for (const [jalur, judul] of halamanLog) {
      await app.open(jalur);
      await expect(screen.getByRole("heading", { name: judul })).toBeVisible();
    }
  });
});
