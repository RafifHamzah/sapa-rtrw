<?php

namespace App\Http\Controllers;

use App\Enums\LetterStatus;
use App\Models\LetterRequest;
use App\Models\LetterType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LetterController extends Controller
{
    /**
     * Riwayat permohonan surat milik warga (data warga tertaut akun).
     */
    public function index(Request $request): View
    {
        $resident = $request->user()->resident;

        $letters = $resident
            ? LetterRequest::with('letterType')
                ->where('resident_id', $resident->id)
                ->latest()
                ->get()
            : collect();

        return view('letters.index', [
            'letters' => $letters,
            'types' => LetterType::where('is_active', true)->get(),
        ]);
    }

    /**
     * Warga mengajukan surat: pilih jenis, isi keperluan + required_fields.
     */
    public function store(Request $request): RedirectResponse
    {
        $resident = $request->user()->resident;
        abort_unless($resident !== null, 403, 'Akun Anda belum tertaut ke data warga.');

        $base = $request->validate([
            'letter_type_id' => ['required', Rule::exists('letter_types', 'id')->where('is_active', true)],
            'purpose' => ['required', 'string', 'max:1000'],
        ]);

        $type = LetterType::findOrFail($base['letter_type_id']);
        $formData = $this->validateRequiredFields($request, $type);

        $letter = LetterRequest::create([
            'rt_id' => $resident->family?->rt_id ?? $request->user()->rt_id,
            'letter_type_id' => $type->id,
            'resident_id' => $resident->id,
            'requested_by' => $request->user()->id,
            'purpose' => $base['purpose'],
            'form_data' => $formData,
            'status' => LetterStatus::Pending,
        ]);

        // Gamifikasi: XP mengajukan surat + kabari bila ada badge baru.
        $newBadges = app(\App\Services\GamificationService::class)
            ->recordActivity($request->user(), 20, 'Ajukan surat', 'letter:' . $letter->id);

        $message = 'Permohonan surat berhasil dikirim dan menunggu persetujuan pengurus.';
        if ($newBadges !== []) {
            $message .= ' 🏅 Badge baru: ' . collect($newBadges)->map->getLabel()->join(', ') . '!';
        }

        return back()->with('status', $message)->with('celebrate', true);
    }

    /**
     * Unduh PDF surat yang sudah disetujui (hanya milik warga ybs).
     */
    public function download(Request $request, LetterRequest $letter): StreamedResponse
    {
        $resident = $request->user()->resident;
        abort_unless($resident !== null && $letter->resident_id === $resident->id, 403);
        abort_unless($letter->isApproved() && $letter->pdf_path && Storage::disk('public')->exists($letter->pdf_path), 404);

        // Tandai selesai setelah diunduh (opsional).
        if ($letter->status === LetterStatus::Approved) {
            $letter->update(['status' => LetterStatus::Completed]);
        }

        $filename = 'surat-' . str_replace(['/', '\\'], '-', (string) $letter->letter_number) . '.pdf';

        return Storage::disk('public')->download($letter->pdf_path, $filename);
    }

    /**
     * Validasi dinamis field tambahan sesuai definisi required_fields.
     *
     * @return array<string, mixed>
     */
    private function validateRequiredFields(Request $request, LetterType $type): array
    {
        $rules = [];
        $attributes = [];

        foreach ($type->required_fields ?? [] as $field) {
            $name = $field['name'] ?? null;
            if (! $name) {
                continue;
            }

            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];
            $fieldRules[] = match ($field['type'] ?? 'text') {
                'number' => 'numeric',
                'date' => 'date',
                default => 'string',
            };

            $rules["form_data.{$name}"] = $fieldRules;
            $attributes["form_data.{$name}"] = $field['label'] ?? $name;
        }

        if ($rules === []) {
            return [];
        }

        return Validator::make($request->all(), $rules, [], $attributes)
            ->validate()['form_data'] ?? [];
    }
}
