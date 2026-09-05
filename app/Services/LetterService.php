<?php

namespace App\Services;

use App\Enums\LetterStatus;
use App\Models\LetterRequest;
use App\Models\Rt;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LetterService
{
    /**
     * Setujui permohonan surat: buat nomor surat, qr_token, render PDF ber-QR.
     * Dibungkus DB transaction + kunci baris RT agar penomoran aman dari
     * race condition (nomor urut per RT per tahun tidak dobel).
     */
    public function approve(LetterRequest $request, User $processor): LetterRequest
    {
        return DB::transaction(function () use ($request, $processor): LetterRequest {
            // Serialize per-RT: dua approval bersamaan pada RT sama akan antre.
            $rt = Rt::whereKey($request->rt_id)->lockForUpdate()->firstOrFail();

            $request->forceFill([
                'status' => LetterStatus::Approved,
                'letter_number' => $this->generateLetterNumber($request, $rt),
                'qr_token' => $this->generateQrToken(),
                'processed_by' => $processor->id,
                'processed_at' => now(),
                'notes' => null,
            ])->save();

            $path = $this->renderAndStorePdf($request->fresh(['resident.family', 'letterType', 'rt', 'processor']));
            $request->update(['pdf_path' => $path]);

            $this->notifyRequester($request);

            return $request;
        });
    }

    public function reject(LetterRequest $request, User $processor, string $reason): LetterRequest
    {
        $request->update([
            'status' => LetterStatus::Rejected,
            'notes' => $reason,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ]);

        $this->notifyRequester($request);

        return $request;
    }

    /**
     * Kabari warga pemohon lewat notifikasi in-app (lonceng). Dibungkus rescue
     * agar kegagalan pengiriman notifikasi tak membatalkan proses surat.
     */
    private function notifyRequester(LetterRequest $request): void
    {
        $requester = $request->requester;

        if ($requester) {
            rescue(fn () => $requester->notify(new \App\Notifications\LetterStatusNotification($request)), report: false);
        }
    }

    /**
     * Nomor surat: 001/DOM/RT05/2026 — urut berjalan per RT per tahun.
     * Harus dipanggil di dalam transaksi yang sudah mengunci baris RT.
     */
    public function generateLetterNumber(LetterRequest $request, Rt $rt): string
    {
        $year = now()->year;

        $sequence = LetterRequest::query()
            ->where('rt_id', $rt->id)
            ->whereNotNull('letter_number')
            ->whereYear('processed_at', $year)
            ->count() + 1;

        return sprintf(
            '%03d/%s/RT%s/%d',
            $sequence,
            strtoupper($request->letterType->code),
            $rt->number,
            $year,
        );
    }

    public function generateQrToken(): string
    {
        // 40 karakter heksadesimal (20 byte / 160-bit acak) — praktis mustahil
        // ditebak, sekaligus menjaga QR tetap ringkas.
        return bin2hex(random_bytes(20));
    }

    /**
     * Ganti placeholder template dengan data resident + form_data pemohon.
     */
    public function fillTemplate(LetterRequest $request): string
    {
        $resident = $request->resident;

        $replacements = [
            '{nama}' => (string) $resident->full_name,
            '{nik}' => (string) $resident->nik,
            '{alamat}' => (string) ($resident->family?->address ?? '-'),
            '{keperluan}' => (string) $request->purpose,
            '{tempat_lahir}' => (string) ($resident->birth_place ?? '-'),
            '{pekerjaan}' => (string) ($resident->occupation ?? '-'),
        ];

        foreach ((array) $request->form_data as $key => $value) {
            $replacements['{' . $key . '}'] = is_array($value) ? implode(', ', $value) : (string) $value;
        }

        return strtr((string) $request->letterType->template_body, $replacements);
    }

    /**
     * Render PDF surat (kop + body + QR verifikasi + ruang tanda tangan) dan
     * simpan ke disk public. Mengembalikan path relatif.
     */
    public function renderAndStorePdf(LetterRequest $request): string
    {
        $verificationUrl = route('letters.verify', ['qr_token' => $request->qr_token]);

        // QR sebagai SVG inline (tanpa perlu ekstensi imagick).
        $qrSvg = QrCode::format('svg')->size(140)->margin(0)->errorCorrection('L')->generate($verificationUrl);

        $pdf = Pdf::loadView('letters.pdf', [
            'request' => $request,
            'body' => $this->fillTemplate($request),
            'qrSvg' => (string) $qrSvg,
            'verificationUrl' => $verificationUrl,
        ])->setPaper('a4');

        $path = 'letters/' . $request->qr_token . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
