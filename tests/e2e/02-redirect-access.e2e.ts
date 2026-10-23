/**
 * Uji alur akses tautan pendek /{slug} (redirect publik).
 *
 * Membuat tautan via API, lalu memverifikasi:
 *  - akses langsung → redirect ke tujuan
 *  - /qr/{slug} → SVG
 *  - slug tidak ditemukan → halaman not-found
 * Data dibersihkan di .e2e/cleanup.js.
 */
import { test } from "@e2e-dev/web";
import { expect } from "e2e";

function uniq(prefix: string): string {
  return `${prefix}-${Math.random().toString(36).slice(2, 8)}`;
}

test.describe("akses tautan — alur redirect publik", () => {
  test("akses /{slug} langsung mengarahkan ke tujuan", async ({
    app,
    browser,
  }) => {
    const res = await fetch(new URL("/api/v1/links", app.baseUrl), {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify({ destination_url: "https://example.com/kebijakan" }),
    });
    const { data } = await res.json();
    const slug = data.slug;

    await app.open(`/${slug}`);
    await expect(browser).toHaveURL("https://example.com/kebijakan");
  });

  test("/qr/{slug} mengembalikan SVG", async ({ app, browser }) => {
    const res = await fetch(new URL("/api/v1/links", app.baseUrl), {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify({ destination_url: `https://example.com/qr` }),
    });
    const { data } = await res.json();
    await browser.goto(new URL(`/qr/${data.slug}`, app.baseUrl).toString());
    // Dokumen SVG tidak punya <body>, jadi baca elemen akar dokumen.
    const markup = await browser.evaluate(
      () => document.documentElement.outerHTML ?? "",
    );
    expect(markup).toContain("<svg");
  });

  test("slug tak terdaftar menampilkan halaman not-found", async ({
    app,
    screen,
  }) => {
    const missing = `tidak-ada-${uniq("x")}`;
    await app.open(`/${missing}`);
    await expect(
      screen.getByRole("heading", { name: /tidak ditemukan/i }),
    ).toBeVisible();
    await expect(
      screen.getByRole("link", { name: /kembali ke beranda/i }),
    ).toBeVisible();
  });
});
