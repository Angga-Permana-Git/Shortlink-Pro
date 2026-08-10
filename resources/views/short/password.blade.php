<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Link Dilindungi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1A296C 0%, #3399FF 100%); }
        .card { background: #FFFFFF; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,.15); padding: 48px; max-width: 420px; width: 100%; }
        .lock { width: 56px; height: 56px; border-radius: 14px; background: #EEF2FF; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        h1 { font-size: 20px; color: #111827; margin-bottom: 6px; }
        .sub { color: #6B7280; font-size: 14px; margin-bottom: 24px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 14px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,.2); }
        .error { color: #B91C1C; font-size: 13px; margin-top: 8px; }
        button { width: 100%; margin-top: 20px; padding: 12px; background: #2563EB; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        button:hover { background: #1D4ED8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="lock">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
        <h1>Link Dilindungi</h1>
        <p class="sub">Link ini dilindungi kata sandi. Masukkan kata sandi untuk melanjutkan.</p>
        <form method="POST" action="{{ route('redirect.unlock', $shortUrl->slug) }}">
            @csrf
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" autofocus required>
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
            <button type="submit">Lanjutkan</button>
        </form>
    </div>
</body>
</html>