/**
 * Uji alur publik di beranda (/).
 *
 * Cakupan: formulir pendekkan, hasil yang ditampilkan (tautan + salin + QR),
 * paket kustom, dan catatan bersandi. Data dihasilkan unik per putaran dan
 * dibersihkan oleh .e2e/cleanup.js setelah rangkaian selesai.
 */
import { test } from "@e2e-dev/web";
import { expect } from "e2e";

function uniq(prefix: string): string {
  return `${prefix}-${Math.random().toString(36).slice(2, 8)}`;
}

const DEST = `https://contoh.e2e/${uniq("d")}`; // unik per load modul

test.describe("beranda — alur pendekkan publik", () => {
  test("membuka / dan menampilkan formulir pendekkan", async ({ app, screen }) => {
    await app.open("/");
    await expect(screen.getByTestId("formulir")).toBeVisible();
    await expect(screen.getByLabel("Tautan panjang")).toBeVisible();
  });

  test("pendekkan cepat menampilkan hasil + tautan + QR + tombol salin", async ({
    app,
    screen,
    browser,
  }) => {
    await app.open("/");

    await screen.getByLabel("Tautan panjang").fill(DEST);
    await screen.getByRole("checkbox", { name: "QR Code" }).check();
    await screen.getByTestId("tombol-pendek").tap();

    const link = screen.getByTestId("hasil-tautan");
    await expect(link).toBeVisible();
    const href = await link.getAttribute("href");
    const match = href?.match(/\/([^/?#]+)$/);
    expect(match).not.toBeNull();
    const slug = match![1];

    await expect(link).toHaveText(new RegExp(`^https?://[^/]+/${slug}$`));
    await expect(screen.getByTestId("gambar-qr")).toBeVisible();
    await expect(screen.getByTestId("unduh-qr")).toHaveAttribute(
      "href",
      new RegExp(`/qr/${slug}$`),
    );

    // Headless Chromium tanpa clipboard permissions menolak writeText, jadi
    // kami hanya memverifikasi tombol salin ada dan dapat diklik (handler bernama
    // 'Salin'). Aset ini tidak menyentuh logika aplikasi asli clipboard.
    await screen.getByTestId("tombol-salin").tap();
    await expect(screen.getByTestId("label-salin")).toHaveText("Salin");
    await browser.reload();
    await expect(screen.getByLabel("Tautan panjang")).toBeVisible();
  });

  test("pendekkan kustom valid menampungkan slug pilihan", async ({
    app,
    screen,
  }) => {
    const slug = uniq("qa");
    await app.open("/");
    await screen.getByLabel("Tautan panjang").fill(DEST);
    await screen.getByRole("checkbox", { name: "Kustom" }).check();
    await screen.getByLabel("Nama pendek kustom").fill(slug);
    await screen.getByTestId("tombol-pendek").tap();

    await expect(screen.getByTestId("hasil-tautan")).toHaveText(
      new RegExp(`^https?://[^/]+/${slug}$`),
    );
  });

  test("pendekkan dengan kata sandi menampilkan catatan bersandi", async ({
    app,
    screen,
  }) => {
    await app.open("/");
    await screen.getByLabel("Tautan panjang").fill(DEST);
    await screen.getByRole("checkbox", { name: "Kata sandi" }).check();
    await Promise.all([
      screen.getByLabel("Kata sandi tautan").fill("rahasia123"),
      screen.getByRole("checkbox", { name: "QR Code" }).check(),
    ]);
    await screen.getByTestId("tombol-pendek").tap();

    await expect(screen.getByTestId("hasil-tautan")).toBeVisible();
    await expect(screen.getByTestId("catatan-hasil")).toBeVisible();
    await expect(screen.getByTestId("catatan-hasil")).toHaveText(/kata sandi/i);
  });
});
