<?php

namespace App\Actions\ShortUrl;

use App\Models\ShortUrl;

class DeleteShortUrl
{
    public function handle(ShortUrl $shortUrl): void
    {
        $shortUrl->delete();
    }
}