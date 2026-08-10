<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Link Tidak Ditemukan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1A296C 0%, #3399FF 100%); color: #1F2937; }
        .card { background: #FFFFFF; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,.15); padding: 48px; max-width: 420px; width: 100%; text-align: center; }
        .code { font-size: 72px; font-weight: 800; color: #1A296C; line-height: 1; }
        h1 { margin: 16px 0 8px; font-size: 22px; color: #111827; }
        p { color: #6B7280; font-size: 14px; line-height: 1.6; }
        a { display: inline-block; margin-top: 24px; background: #2563EB; color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; }
        a:hover { background: #1D4ED8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">404</div>
        <h1>Link Tidak Ditemukan</h1>
        <p>Short link yang Anda akses tidak ditemukan atau telah dihapus oleh Admin.</p>
        <a href="/">Kembali ke Beranda</a>
    </div>
</body>
</html>