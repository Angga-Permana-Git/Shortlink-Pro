<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClickEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'short_url_id',
        'status',
        'ip_hash',
        'user_agent',
        'referer',
    ];

    public function shortUrl(): BelongsTo
    {
        return $this->belongsTo(ShortUrl::class);
    }
}