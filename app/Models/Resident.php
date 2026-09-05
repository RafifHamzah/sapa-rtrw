<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\ResidentRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    /** @use HasFactory<\Database\Factories\ResidentFactory> */
    use HasFactory;

    protected $fillable = [
        'family_id',
        'user_id',
        'nik',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'relationship',
        'religion',
        'marital_status',
        'occupation',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            // NIK dienkripsi at rest — transparan saat dibaca/ditulis via Eloquent.
            'nik' => 'encrypted',
            'gender' => Gender::class,
            'relationship' => ResidentRelationship::class,
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
