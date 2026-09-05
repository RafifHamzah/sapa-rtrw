<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Surat — Platform Digital RT/RW</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); padding: 20px; color: #0f172a;
        }
        .card { background: #fff; border-radius: 18px; box-shadow: 0 20px 45px rgba(0,0,0,.25); max-width: 460px; width: 100%; overflow: hidden; }
        .banner { padding: 28px 24px; text-align: center; color: #fff; }
        .banner.ok { background: #059669; }
        .banner.bad { background: #dc2626; }
        .banner .icon { width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,.2); display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 10px; }
        .banner h1 { margin: 6px 0 2px; font-size: 20px; }
        .banner p { margin: 0; opacity: .9; font-size: 13px; }
        .content { padding: 22px 24px 26px; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 11px 0; border-bottom: 1px solid #eef2f7; font-size: 14px; }
        .row:last-child { border-bottom: 0; }
        .row .label { color: #64748b; }
        .row .value { font-weight: 600; text-align: right; color: #0f172a; }
        .badge { display: inline-block; background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .foot { text-align: center; font-size: 12px; color: #94a3b8; padding: 16px; background: #f8fafc; }
        .bad-note { text-align: center; color: #475569; font-size: 14px; line-height: 1.6; padding: 8px 4px 0; }
    </style>
</head>
<body>
    <div class="card">
        @if ($valid)
            <div class="banner ok">
                <div class="icon">✓</div>
                <h1>Surat Terverifikasi</h1>
                <p>Dokumen ini sah dan terdaftar di sistem RT/RW.</p>
            </div>
            <div class="content">
                <div class="row"><span class="label">Nomor Surat</span><span class="value">{{ $request->letter_number }}</span></div>
                <div class="row"><span class="label">Jenis Surat</span><span class="value">{{ $request->letterType->name }}</span></div>
                <div class="row"><span class="label">Atas Nama</span><span class="value">{{ $request->resident->full_name }}</span></div>
                <div class="row"><span class="label">RT / RW</span><span class="value">RT {{ $request->rt->number }} / RW {{ $request->rt->rw_number }}</span></div>
                <div class="row"><span class="label">Tanggal Terbit</span><span class="value">{{ optional($request->processed_at)->translatedFormat('d F Y') }}</span></div>
                <div class="row"><span class="label">Status</span><span class="value"><span class="badge">Sah</span></span></div>
            </div>
        @else
            <div class="banner bad">
                <div class="icon">✕</div>
                <h1>Surat Tidak Ditemukan</h1>
                <p>Token verifikasi tidak valid.</p>
            </div>
            <div class="content">
                <p class="bad-note">
                    Dokumen dengan token ini tidak terdaftar atau belum disahkan.
                    Pastikan Anda memindai QR dari surat asli, atau hubungi pengurus RT.
                </p>
            </div>
        @endif
        <div class="foot">Platform Digital RT/RW · Verifikasi Dokumen</div>
    </div>
</body>
</html>
