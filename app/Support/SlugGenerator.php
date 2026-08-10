<?php

namespace App\Support;

use App\Models\ShortUrl;
use Illuminate\Support\Str;

class SlugGenerator
{
    private const SLUG_LENGTH = 7;

    public static function generate(int $length = self::SLUG_LENGTH): string
    {
        do {
            $slug = static::random($length);
        } while (ShortUrl::query()->where('slug', $slug)->exists());

        return $slug;
    }

    public static function random(int $length = self::SLUG_LENGTH): string
    {
        return Str::random($length);
    }

    public static function isCustomSlugValid(string $slug): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-_]*$/', $slug);
    }
}