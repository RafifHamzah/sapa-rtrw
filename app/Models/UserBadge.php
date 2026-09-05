<?php

namespace App\Models;

use App\Enums\Badge;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    /** @use HasFactory<\Database\Factories\UserBadgeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge',
        'awarded_at',
    ];

    protected function casts(): array
    {
        return [
            'badge' => Badge::class,
            'awarded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
