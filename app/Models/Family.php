<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    /** @use HasFactory<\Database\Factories\FamilyFactory> */
    use HasFactory;

    protected $fillable = [
        'rt_id',
        'kk_number',
        'head_resident_id',
        'address',
        'house_number',
        'rt_status',
    ];

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    /**
     * Kepala keluarga. Tanpa FK di DB (relasi circular dgn residents),
     * jadi cukup didefinisikan di level Eloquent.
     */
    public function headResident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'head_resident_id');
    }

    public function dues(): HasMany
    {
        return $this->hasMany(Dues::class);
    }
}
