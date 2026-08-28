<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::get('app_name', 'Shortlink Pro') }} — Masuk</title>
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
        h2 { font-size: 1.1rem; color: #203273; margin-bottom: 1.5rem; text-align: center; }
        .sso-btn {
            width: 100%;
            padding: .8rem;
            background: #1A296C;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            transition: background .15s, box-shadow .15s;
            text-decoration: none;
        }
        .sso-btn:hover { background: #203273; box-shadow: 0 6px 16px rgba(26, 41, 108, .35); }
        .sso-btn svg { width: 20px; height: 20px; flex-shrink: 0; }
        .error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
            padding: .6rem .8rem;
            border-radius: 8px;
            font-size: .82rem;
            margin-bottom: 1rem;
        }
        .emergency { margin-top: 1.5rem; text-align: center; font-size: .78rem; color: #94a3b8; }
        .emergency a { color: #64748b; text-decoration: underline; }
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
            <h1>{{ \App\Models\Setting::get('app_name', 'Shortlink Pro') }}</h1>
        </div>

        <h2>SSO</h2>

        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if(app(\App\Services\Auth\KeycloakAuthService::class)->isConfigured())
            <a class="sso-btn" href="{{ route('keycloak.redirect') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6"/>
                    <path d="M12 18v-6"/>
                    <path d="m9 15 3 3 3-3"/>
                </svg>
                Login dengan SSO
            </a>
        @else
            <div class="error">
                SSO belum dikonfigurasi. Gunakan <a href="{{ route('login.local') }}">login darurat</a> untuk masuk.
            </div>
        @endif

        <div class="emergency"><a href="{{ route('login.local') }}">administrator</a></div>

        <div class="footer">&copy; {{ date('Y') }} {{ \App\Models\Setting::get('app_name', 'Shortlink Pro') }}</div>
    </div>
</body>
</html>