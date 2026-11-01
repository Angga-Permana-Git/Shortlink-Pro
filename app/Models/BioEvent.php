<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BioEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'bio_profile_id',
        'bio_link_id',
        'jenis',
        'ip_hash',
        'user_agent',
        'referer',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BioProfile::class, 'bio_profile_id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(BioLink::class, 'bio_link_id');
    }
}
