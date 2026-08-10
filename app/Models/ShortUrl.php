<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'slug',
        'destination_url',
        'is_active',
        'starts_at',
        'expires_at',
        'password_hash',
        'total_clicks',
        'last_clicked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_clicked_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(ClickEvent::class);
    }

    public function hasPassword(): bool
    {
        return ! empty($this->password_hash);
    }

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return now()->gte($this->expires_at);
    }

    public function notStarted(): bool
    {
        if (! $this->starts_at) {
            return false;
        }

        return now()->lt($this->starts_at);
    }

    public function shortUrl(): string
    {
        return route('redirect.show', $this->slug);
    }
}