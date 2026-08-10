<?php

namespace App\Policies;

use App\Models\ShortUrl;
use App\Models\User;

class ShortUrlPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ShortUrl $shortUrl): bool
    {
        return $user->isAdmin() || $shortUrl->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ShortUrl $shortUrl): bool
    {
        return $user->isAdmin() || $shortUrl->owner_id === $user->id;
    }

    public function delete(User $user, ShortUrl $shortUrl): bool
    {
        return $user->isAdmin();
    }

    public function toggle(User $user, ShortUrl $shortUrl): bool
    {
        return $user->isAdmin() || $shortUrl->owner_id === $user->id;
    }
}