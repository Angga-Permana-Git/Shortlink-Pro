/**
 * Uji API v1 yang memerlukan login (session cookie Laravel).
 *
 * Regresi untuk bug "GET/PUT /api/v1/linktree selalu 401": grup middleware
 * `api` sempat tidak memuat cookie + session sehingga `auth` tidak pernah
 * mengenali pengguna yang sudah masuk (lihat bootstrap/app.php).
 */
import { test } from "@e2e-dev/web";
import { expect } from "e2e";

function uniq(prefix: string): string {
  return `${prefix}-${Math.random().toString(36).slice(2, 8)}`;
}

const EMAIL = `e2e.${uniq("api")}@e2e.test`;
const USERNAME = `e2e${uniq("api")}`;
const PASSWORD = "rahasia_jarang123";
const ALAMAT = uniq("api");

async function daftar(screen: any): Promise<void> {
  await screen.getByLabel("Nama lengkap").fill("E2E API");
  await screen.getByLabel("Nama pengguna").fill(USERNAME);
  await screen.getByLabel("Email").fill(EMAIL);
  await screen.getByLabel("Kata sandi").fill(PASSWORD);
  await screen.getByLabel("Ulangi kata sandi").fill(PASSWORD);
  await screen.getByRole("button", { name: /^daftar$/i }).tap();
}

test.describe("API v1 — endpoint berautentikasi", () => {
  test("session login bisa membaca dan mengubah profil Linktree sendiri", async ({
    app,
    screen,
    browser,
  }) => {
    await app.open("/register");
    await daftar(screen);
    await screen.getByRole("heading", { name: /^dashboard$/i }).waitFor();

    const cookies = await browser.cookies();
    const cookieHeader = cookies.map((c) => `${c.name}=${c.value}`).join("; ");

    const baca = await fetch(new URL("/api/v1/linktree", app.baseUrl), {
      headers: { Accept: "application/json", cookie: cookieHeader },
    });
    expect(baca.status).toBe(200);
    const { data } = await baca.json();
    expect(data.public_slug).toBeNull();

    const ubah = await fetch(new URL("/api/v1/linktree", app.baseUrl), {
      method: "PUT",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        cookie: cookieHeader,
      },
      body: JSON.stringify({ public_slug: ALAMAT, display_name: "E2E API" }),
    });
    expect(ubah.status).toBe(200);

    const publik = await fetch(new URL(`/u/${ALAMAT}`, app.baseUrl));
    expect(publik.status).toBe(200);
    expect(await publik.text()).toContain("E2E API");

    const tamu = await fetch(new URL("/api/v1/linktree", app.baseUrl), {
      headers: { Accept: "application/json" },
    });
    expect(tamu.status).toBe(401);
  });
});
