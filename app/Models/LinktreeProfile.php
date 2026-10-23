<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinktreeProfile extends Model
{
    protected $fillable = ['user_id', 'public_slug', 'display_name', 'bio', 'avatar_path', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(LinktreeLink::class, 'profile_id')->orderBy('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LinktreeEvent::class, 'linktree_profile_id');
    }
}
