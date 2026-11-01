<?php

namespace App\Http\Controllers\Api\V1;

use App\Dto\UpdateBioProfileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBioProfileRequest;
use App\Services\Bio\BioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BioApiController extends Controller
{
    public function show(Request $request, BioService $service): JsonResponse
    {
        return response()->json(['data' => $service->profile($request->user())->load('links')]);
    }

    public function update(UpdateBioProfileRequest $request, BioService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->update($request->user(), UpdateBioProfileDTO::fromArray($request->validated())),
        ]);
    }
}
