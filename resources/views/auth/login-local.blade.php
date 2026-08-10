<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::get('app_name', 'Shortlink Enterprise') }} — Login Darurat</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fbfbfd;
            padding: 1.5rem;
        }
        .card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 2rem 2rem;
            border: 1px solid #ececf0;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(26, 41, 108, 0.08), 0 2px 6px rgba(26, 41, 108, 0.06);
        }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo img { max-height: 64px; max-width: 180px; }
        .logo h1 { font-size: 1.35rem; font-weight: 700; color: #1A296C; margin-top: .5rem; }
        h2 { font-size: 1.1rem; color: #203273; }
        .sub { color: #64748b; font-size: .85rem; margin-bottom: 1.25rem; }
        .warning {
            background: #FEFCE8;
            border: 1px solid #FDE68A;
            color: #92400E;
            padding: .6rem .8rem;
            border-radius: 8px;
            font-size: .8rem;
            margin-bottom: 1.25rem;
        }
        label { display: block; font-size: .8rem; font-weight: 600; color: #334155; margin-bottom: .35rem; }
        input {
            width: 100%;
            padding: .65rem .8rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: .95rem;
            margin-bottom: 1rem;
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus { outline: none; border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
        button {
            width: 100%;
            padding: .7rem;
            background: #2563EB;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, box-shadow .15s;
        }
        button:hover { background: #1d4ed8; box-shadow: 0 6px 16px rgba(37, 99, 235, .35); }
        .error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
            padding: .6rem .8rem;
            border-radius: 8px;
            font-size: .82rem;
            margin-bottom: 1rem;
        }
        .link { margin-top: 1.25rem; text-align: center; font-size: .82rem; }
        .link a { color: #2563EB; text-decoration: none; }
        .footer { text-align: center; margin-top: 1.5rem; color: #94a3b8; font-size: .75rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            @php($logoUrl = app(\App\Services\Branding\BrandingService::class)->loginLogoUrl())
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo">
            @endif
            <h1>{{ \App\Models\Setting::get('app_name', 'Shortlink Enterprise') }}</h1>
        </div>

        <h2>Login Darurat</h2>
        <p class="sub">Khusus administrator sistem.</p>

        <div class="warning">
            Jalur login ini hanya digunakan saat SSO tidak tersedia.<br>
            Semua aktivitas login darurat akan dicatat.
        </div>

        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.local') }}">
            @csrf
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>

            <button type="submit">Login Darurat</button>
        </form>

        <div class="link"><a href="{{ route('login') }}">&larr; Kembali ke halaman login</a></div>
    </div>
</body>
</html>