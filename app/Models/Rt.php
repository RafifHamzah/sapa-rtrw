<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Rt extends Model
{
    /** @use HasFactory<\Database\Factories\RtFactory> */
    use HasFactory;

    protected $table = 'rts';

    protected $fillable = [
        'number',
        'rw_number',
        'name',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'address',
        'chairman_name',
        'phone',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    public function residents(): HasManyThrough
    {
        return $this->hasManyThrough(Resident::class, Family::class);
    }

    public function transactionCategories(): HasMany
    {
        return $this->hasMany(TransactionCategory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function dues(): HasMany
    {
        return $this->hasMany(Dues::class);
    }

    public function letterTypes(): HasMany
    {
        return $this->hasMany(LetterType::class);
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
