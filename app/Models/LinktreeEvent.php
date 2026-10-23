<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinktreeEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'linktree_profile_id',
        'linktree_link_id',
        'jenis',
        'ip_hash',
        'user_agent',
        'referer',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(LinktreeProfile::class, 'linktree_profile_id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(LinktreeLink::class, 'linktree_link_id');
    }
}
