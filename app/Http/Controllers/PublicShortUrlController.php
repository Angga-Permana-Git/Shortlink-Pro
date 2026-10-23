<?php

namespace App\Http\Controllers;

use App\Dto\CreateShortUrlDTO;
use App\Http\Requests\CreatePublicShortUrlRequest;
use App\Services\ShortUrl\ShortUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PublicShortUrlController extends Controller
{
    public function store(CreatePublicShortUrlRequest $request, ShortUrlService $service): JsonResponse|RedirectResponse
    {
        $shortUrl = $service->create(null, CreateShortUrlDTO::fromArray($request->validated()));

        if (! $request->expectsJson()) {
            return back()->with('short_url', $shortUrl->shortUrl());
        }

        return response()->json([
            'data' => [
                'slug' => $shortUrl->slug,
                'url' => $shortUrl->shortUrl(),
                'qr' => route('qr.show', $shortUrl->slug),
                'protected' => $shortUrl->hasPassword(),
                'expires_at' => $shortUrl->expires_at?->toIso8601String(),
            ],
        ], 201);
    }
}
