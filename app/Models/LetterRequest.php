<?php

namespace App\Models;

use App\Enums\LetterStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LetterRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'rt_id',
        'letter_type_id',
        'resident_id',
        'requested_by',
        'purpose',
        'form_data',
        'status',
        'letter_number',
        'qr_token',
        'pdf_path',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LetterStatus::class,
            'form_data' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [LetterStatus::Approved, LetterStatus::Completed], true);
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
