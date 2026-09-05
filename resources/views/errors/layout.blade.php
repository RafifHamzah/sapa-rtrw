<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · SAPA</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 60%); color: #0f172a; padding: 20px;
        }
        .card { text-align: center; max-width: 460px; width: 100%; }
        .mark {
            width: 60px; height: 60px; border-radius: 18px; margin: 0 auto 20px;
            background: linear-gradient(135deg, #10b981, #047857); color: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 30px -8px rgba(4,120,87,.5);
        }
        .code { font-size: 96px; font-weight: 800; line-height: 1; color: #047857; letter-spacing: -2px; }
        h1 { font-size: 22px; margin: 12px 0 6px; color: #0f172a; }
        p { color: #475569; font-size: 15px; line-height: 1.6; margin: 0 auto; max-width: 380px; }
        .actions { margin-top: 28px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        a.btn {
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            border-radius: 12px; padding: 12px 22px; font-weight: 600; font-size: 14px; transition: .15s;
        }
        a.primary { background: #059669; color: #fff; }
        a.primary:hover { background: #047857; }
        a.ghost { color: #475569; }
        a.ghost:hover { background: #f1f5f9; }
        .brand { margin-top: 34px; font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="mark">
            <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11.5 12 4l9 7.5" /><path d="M5 10.5V20h14v-9.5" /><path d="M9.5 20v-4.5a2.5 2.5 0 0 1 5 0V20" />
            </svg>
        </div>
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a class="btn primary" href="{{ url('/') }}">Kembali ke Beranda</a>
            @hasSection('secondary')@yield('secondary')@endif
        </div>
        <p class="brand">SAPA · Sistem Administrasi dan Pelayanan Antarwarga</p>
    </div>
</body>
</html>
