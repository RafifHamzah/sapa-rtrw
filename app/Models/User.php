<?php

namespace App\Models;

use App\Enums\Badge;
use App\Enums\UserStatus;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'rt_id',
        'name',
        'email',
        'google_id',
        'password',
        'phone',
        'status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'xp',
    ];

    /** XP yang dibutuhkan untuk naik satu level. */
    public const XP_PER_LEVEL = 100;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Hanya pengurus & super_admin yang boleh masuk panel Filament (/admin).
     * Warga ditolak — mereka memakai aplikasi warga (Breeze).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'pengurus']);
    }

    // --- Status helpers -----------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    /** Terverifikasi = status aktif (hasil dari aksi "Verifikasi" pengurus). */
    public function isVerified(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isRejected(): bool
    {
        return $this->status === UserStatus::Rejected;
    }

    // --- Gamifikasi ---------------------------------------------------------

    public function level(): int
    {
        return intdiv((int) $this->xp, self::XP_PER_LEVEL) + 1;
    }

    /** XP yang sudah terkumpul di dalam level saat ini (0..XP_PER_LEVEL). */
    public function xpIntoLevel(): int
    {
        return ((int) $this->xp) % self::XP_PER_LEVEL;
    }

    /** Persentase progres menuju level berikutnya. */
    public function levelProgress(): int
    {
        return (int) round($this->xpIntoLevel() / self::XP_PER_LEVEL * 100);
    }

    public function xpToNextLevel(): int
    {
        return self::XP_PER_LEVEL - $this->xpIntoLevel();
    }

    public function hasBadge(Badge $badge): bool
    {
        return $this->badges->contains('badge', $badge);
    }

    public function xpLogs(): HasMany
    {
        return $this->hasMany(XpLog::class)->latest();
    }

    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class)->orderBy('awarded_at');
    }

    // --- Relations ----------------------------------------------------------

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    /**
     * A user account may be linked to a single resident profile.
     */
    public function resident(): HasOne
    {
        return $this->hasOne(Resident::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function recordedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }
}
