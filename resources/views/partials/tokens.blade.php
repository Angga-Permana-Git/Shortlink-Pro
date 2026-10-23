{{--
    Token desain terpusat sesuai PRDapp.md bagian 6 (Konvensi Pengembangan).

    Sumber tunggal nilai warna, jarak, ukuran teks, radius, dan bayangan untuk
    semua halaman di luar panel Filament. Komponen hanya boleh memakai token di
    sini, bukan menulis ulang nilai mentahnya.

    Warna aksi utama mengikuti Branding Settings lewat BrandingComposer, yang
    mengirim variabel $branding ke tampilan pemilik partial ini.
--}}
<style>
    :root {
        /* Warna */
        --warna-utama: {{ $branding['warna'] }};
        --warna-utama-hover: #1D4ED8;
        --warna-navy: #1A296C;
        --warna-navy-teks: #203273;
        --warna-aksen: #3399FF;
        --warna-bahaya: #B91C1C;
        --warna-sukses: #065F46;
        --warna-sukses-latar: #D1FAE5;
        --warna-bahaya-latar: #FEE2E2;
        --warna-smart-navy: #0A2A66;
        --warna-smart-navy-gelap: #071E4A;
        --warna-smart-biru: #E6F0FA;
        --warna-smart-kartu: #FFFFFF;
        --warna-latar: #FBFBFD;
        --warna-kartu: #FFFFFF;
        --warna-teks: #1F2937;
        --warna-teks-judul: #111827;
        --warna-teks-lembut: #6B7280;
        --warna-garis: #ECECF0;
        --warna-garis-input: #D1D5DB;

        /* Gradien dan bayangan */
        --gradien-smart: linear-gradient(135deg, #E0F2FE 0%, #F4F7FC 100%);
        --gradien-latar: linear-gradient(135deg, var(--warna-navy) 0%, var(--warna-aksen) 100%);
        --bayang-smart: 0 10px 30px rgba(10, 42, 102, 0.08);
        --bayang-fokus: 0 0 0 3px rgba(10, 42, 102, 0.14);
        --bayang-kartu: 0 8px 24px rgba(26, 41, 108, 0.08), 0 2px 6px rgba(26, 41, 108, 0.06);
        --bayang-kartu-besar: 0 10px 40px rgba(0, 0, 0, 0.15);

        /* Jarak */
        --jarak-1: 0.25rem;
        --jarak-2: 0.5rem;
        --jarak-3: 0.75rem;
        --jarak-4: 1rem;
        --jarak-5: 1.5rem;
        --jarak-6: 2rem;
        --jarak-7: 3rem;

        /* Ukuran teks */
        --teks-xs: 0.75rem;
        --teks-sm: 0.85rem;
        --teks-md: 1rem;
        --teks-lg: 1.15rem;
        --teks-xl: 1.4rem;
        --teks-judul: clamp(1.75rem, 5vw, 2.25rem);

        /* Ukuran layout dan garis */
        --lebar-halaman: 56.25rem;
        --lebar-formulir: 25rem;
        --lebar-qr: 8rem;
        --lebar-garis: 1px;

        /* Radius */
        --radius-kontrol: 0.5rem;
        --radius-kartu: 0.875rem;
        --radius-kartu-besar: 1rem;
        --radius-pil: 999px;

        /* Tipografi */
        --font-dasar: 'Segoe UI', system-ui, -apple-system, sans-serif;

        /* Transisi: dipakai untuk umpan balik interaksi, bukan hiasan. */
        --transisi-cepat: 0.15s ease;

        {{--
            Breakpoint dipakai lewat nilai literal di media query, karena CSS
            tidak mengizinkan var() di dalam @media. Nilai 40rem di bawah adalah
            rujukan tunggalnya.
        --}}
        --breakpoint-sedang: 40rem;
    }
</style>
