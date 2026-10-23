/**
 * Uji API publik v1 (/api/v1/links).
 *
 * Cakupan: permintaan valid (201 + data.slug/url), validasi (422), dan
 * header Content-Type SVG dari /qr/{slug}. Semua tautan yang dibuat
 * dibersihkan oleh .e2e/cleanup.js.
 */
import { test } from "@e2e-dev/web";
import { expect } from "e2e";

function uniq(prefix: string): string {
  return `${prefix}-${Math.random().toString(36).slice(2, 8)}`;
}

const DEST = `https://contoh-api.e2e/${uniq("d")}`;

test.describe("API v1 — endpoint publik", () => {
  async function postLink(payload: object, baseUrl: string) {
    return fetch(new URL("/api/v1/links", baseUrl), {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify(payload),
    });
  }

  test("POST /api/v1/links mengembalikan 201 + data.slug + data.url", async ({
    app,
  }) => {
    const res = await postLink({ destination_url: DEST }, app.baseUrl);
    expect(res.status).toBe(201);
    const { data } = await res.json();
    expect(data).toBeDefined();
    expect(data.slug).toMatch(/^[a-zA-Z0-9][a-zA-Z0-9-_]*$/);
    expect(data.url).toMatch(/^https?:\/\//);
  });

  test("POST /api/v1/links tanpa destination_url gagal 422", async ({ app }) => {
    const res = await postLink({}, app.baseUrl);
    expect(res.status).toBe(422);
  });

  test("GET /qr/{slug} mengembalikan SVG", async ({ app, browser }) => {
    const res = await postLink({ destination_url: DEST }, app.baseUrl);
    const { data } = await res.json();
    const qrUrl = new URL(`/qr/${data.slug}`, app.baseUrl).toString();

    const qrRes = await fetch(qrUrl);
    expect(qrRes.status).toBe(200);
    expect(qrRes.headers.get("Content-Type")).toContain("image/svg+xml");
    const body = await qrRes.text();
    expect(body).toContain("<svg");
  });
});

test.describe("API v1 — autentikasi", () => {
  test("GET /api/v1/linktree memerlukan autentikasi (401)", async ({ app }) => {
    const res = await fetch(new URL("/api/v1/linktree", app.baseUrl), {
      headers: { Accept: "application/json" },
    });
    expect(res.status).toBe(401);
  });
});
