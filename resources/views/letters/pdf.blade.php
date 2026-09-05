<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        /* Helvetica = font bawaan dompdf (tidak di-embed) → PDF kecil & hemat memori. */
        * { font-family: Helvetica, sans-serif; }
        body { font-size: 12px; color: #111; margin: 0; }
        .kop { text-align: center; border-bottom: 3px double #111; padding-bottom: 8px; margin-bottom: 4px; }
        .kop h1 { font-size: 18px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .kop h2 { font-size: 15px; margin: 2px 0; text-transform: uppercase; }
        .kop p { margin: 1px 0; font-size: 11px; }
        .title { text-align: center; margin: 22px 0 4px; }
        .title h3 { margin: 0; font-size: 14px; text-decoration: underline; text-transform: uppercase; }
        .title .number { font-size: 12px; margin-top: 2px; }
        .body { margin: 18px 6px; line-height: 1.7; text-align: justify; }
        .signature { margin-top: 28px; width: 100%; }
        .signature td { vertical-align: top; font-size: 12px; }
        .sign-space { height: 70px; }
        .qr-box { text-align: center; font-size: 9px; color: #444; }
        .qr-box svg { width: 110px; height: 110px; }
        .footnote { margin-top: 24px; border-top: 1px solid #999; padding-top: 6px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    @php($rt = $request->rt)
    <div class="kop">
        <h1>Rukun Tetangga {{ $rt->number }} / RW {{ $rt->rw_number }}</h1>
        @if ($rt->name)
            <h2>{{ $rt->name }}</h2>
        @endif
        <p>Kelurahan {{ $rt->village }}, Kecamatan {{ $rt->district }}</p>
        <p>{{ $rt->city }}, {{ $rt->province }} {{ $rt->postal_code }}</p>
    </div>

    <div class="title">
        <h3>{{ $request->letterType->name }}</h3>
        <div class="number">Nomor: {{ $request->letter_number }}</div>
    </div>

    <div class="body">
        {!! nl2br(e($body)) !!}
    </div>

    <table class="signature">
        <tr>
            <td style="width: 60%;">&nbsp;</td>
            <td style="width: 40%; text-align: center;">
                {{ $rt->city }}, {{ optional($request->processed_at)->translatedFormat('d F Y') }}<br>
                Ketua RT {{ $rt->number }}
                <div class="sign-space"></div>
                <strong>{{ $rt->chairman_name ?? '(..............................)' }}</strong>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 130px;" class="qr-box">
                {!! $qrSvg !!}
                <div>Pindai untuk verifikasi</div>
            </td>
            <td style="vertical-align: bottom; font-size: 10px; color: #444;">
                Surat ini sah tanpa tanda tangan basah bila terverifikasi melalui:<br>
                <span style="font-size: 9px;">{{ $verificationUrl }}</span>
            </td>
        </tr>
    </table>

    <div class="footnote">
        Dokumen diterbitkan secara digital oleh Platform Digital RT/RW pada
        {{ optional($request->processed_at)->translatedFormat('d F Y H:i') }} WIB.
    </div>
</body>
</html>
