<?php

namespace App\Http\Controllers\Api\V1;

use App\Dto\CreateShortUrlDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePublicShortUrlRequest;
use App\Services\ShortUrl\ShortUrlService;
use Illuminate\Http\JsonResponse;

class PublicShortUrlApiController extends Controller
{
    public function store(CreatePublicShortUrlRequest $request, ShortUrlService $service): JsonResponse
    {
        $url = $service->create(null, CreateShortUrlDTO::fromArray($request->validated()));

        return response()->json(['data' => ['slug' => $url->slug, 'url' => $url->shortUrl()]], 201);
    }
}
