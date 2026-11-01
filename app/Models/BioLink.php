<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BioLink extends Model
{
    protected $fillable = ['profile_id', 'label', 'url', 'icon', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(BioProfile::class, 'profile_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BioEvent::class, 'bio_link_id');
    }
}
