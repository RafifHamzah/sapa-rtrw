<?php

namespace App\Models;

use App\Enums\DuesStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dues extends Model
{
    /** @use HasFactory<\Database\Factories\DuesFactory> */
    use HasFactory, SoftDeletes;

    // "dues" adalah bentuk jamak; pastikan tabelnya tetap 'dues'.
    protected $table = 'dues';

    protected $fillable = [
        'rt_id',
        'family_id',
        'period_month',
        'period_year',
        'amount',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'amount' => 'integer',
            'status' => DuesStatus::class,
            'due_date' => 'date',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DuesPayment::class);
    }
}
