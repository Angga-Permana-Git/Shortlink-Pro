/**
 * Uji alur pengguna: kartu 02 (buat tautan) → kartu 03 (QR/salin/hapus)
 * → kartu 04 (profil Linktree: isi alamat publik) → buka /u/{alamat}.
 *
 * Pendekatan sederhana (karpathy-guidelines §2): gunakan slug otomatis dan
 * identifikasi baris lewat destination URL, bukan mengandalkan panel "Kustom"
 * Livewire yang dapat re-render. Data uji dibuat unik per putaran dan
 * dibersihkan oleh .e2e/cleanup.js setelah rangkaian selesai.
 */
import { test } from "@e2e-dev/web";
import { expect } from "e2e";

function uniq(prefix: string): string {
  return `${prefix}-${Math.random().toString(36).slice(2, 8)}`;
}

const EMAIL = `e2e.${uniq("u")}@e2e.test`;
const USERNAME = `e2e${uniq("u")}`;
const NAME = "E2E User";
const PASSWORD = "rahasia_jarang123";
const LINKTREE_ADDR = uniq("addr");
const TUJUAN = `https://contoh.e2e/${uniq("tujuan")}`;

async function openCard(browser: any, title: string): Promise<void> {
  const card = browser.locator(".kartu").filter({ hasText: title });
  const fold = await card.getByRole("button", { name: /tampilkan|sembunyikan/i }).getAttribute("aria-expanded");
  if (fold !== "true") {
    await card.getByRole("button", { name: /tampilkan/i }).tap();
  }
}

const tunggu = (ms: number) => new Promise<void>((resolve) => setTimeout(resolve, ms));

/**
 * Tunggu Livewire selesai boot sebelum berinteraksi.
 *
 * Tanpa ini, klik bisa jatuh sebelum handler `wire:submit` terpasang sehingga
 * formulir terkirim sebagai submit HTML biasa (halaman muat ulang, hasil hilang).
 */
async function tungguLivewire(browser: any): Promise<void> {
  for (let i = 0; i < 60; i += 1) {
    const siap = await browser
      .evaluate(() => Boolean((window as unknown as Record<string, unknown>).Livewire))
      .catch(() => false);
    if (siap) {
      return;
    }
    await tunggu(250);
  }
  throw new Error("Livewire belum siap setelah 15 detik");
}

/** Isi formulir pendaftaran lalu kirim. */
async function daftar(screen: any): Promise<void> {
  await screen.getByLabel("Nama lengkap").fill(NAME);
  await screen.getByLabel("Nama pengguna").fill(USERNAME);
  await screen.getByLabel("Email").fill(EMAIL);
  await screen.getByLabel("Kata sandi").fill(PASSWORD);
  await screen.getByLabel("Ulangi kata sandi").fill(PASSWORD);
  await screen.getByRole("button", { name: /^daftar$/i }).tap();
}

test.describe("panel pengguna — alur lengkap", () => {
  test("buat tautan → QR/hapus → profil /u/{alamat}", async ({
    app,
    screen,
    browser,
  }) => {
    // ---- Daftar (otentikasi otomatis,arah ke /user) ----
    await app.open("/register");
    await daftar(screen);

    // Pendaftaran dibatasi throttle 5 percobaan/menit per IP. Bila jendela itu
    // sedang penuh (misalnya beberapa putaran uji berturut-turut), tunggu
    // jendelanya berlalu lalu kirim sekali lagi.
    await tunggu(1_500);
    if ((await screen.getByRole("heading", { name: "429" }).count()) > 0) {
      await tunggu(61_000);
      await app.open("/register");
      await daftar(screen);
    }

    await expect(screen.getByText(/Selamat datang kembali/)).toBeVisible();

    // ---- Kartu 02: buat tautan pendek (auto slug) ----
    await tungguLivewire(browser);
    await screen.getByLabel("Tautan panjang").fill(TUJUAN);
    await expect(screen.getByLabel("Tautan panjang")).toHaveValue(TUJUAN);
    await screen.getByRole("button", { name: /^pendekkan tautan$/i }).tap();

    const hasilLink = screen.getByTestId("hasil-tautan");
    await expect(hasilLink).toBeVisible();
    const href = await hasilLink.getAttribute("href");
    expect(href).toMatch(new RegExp(`^https?://[^/]+/[^/]+$`));

    // ---- Kartu 03: panel Link Saya — buka, lalu hapus tautan ----
    await openCard(browser, "Link Saya");

    // Baris tautan dikenali lewat tujuan uniknya (bukan slug acak).
    const baris = browser.locator(".daftar-tautan__item").filter({ hasText: TUJUAN });
    await expect(baris).toBeVisible();

    // hapus tautan lewat konfirmasi dialog
    browser.onDialog((dlg) => dlg.accept());
    await baris.getByRole("button", { name: /^hapus$/i }).tap();
    await expect(screen.getByText("Tautan dihapus.")).toBeVisible();

    // ---- Kartu 04: isi alamat publik Linktree ----
    await openCard(browser, "Profil Linktree");
    await screen.getByLabel("Alamat publik").fill(LINKTREE_ADDR);
    await browser
      .locator(".kartu")
      .filter({ hasText: "Profil Linktree" })
      .getByRole("button", { name: /^simpan profil$/i })
      .tap();

    await expect(screen.getByTestId("alamat-linktree")).toBeVisible();
    const addr = await screen.getByTestId("alamat-linktree").getAttribute("href");
    expect(addr).toMatch(new RegExp(`/${LINKTREE_ADDR}$`));

    // buka /u/{alamat}
    await browser.goto(addr!);
    await expect(
      screen.getByRole("heading", { name: new RegExp(NAME, "i") }),
    ).toBeVisible();
  });
});
