<?php

namespace App\Http\Controllers\Api\V1;

use App\Dto\UpdateLinktreeProfileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLinktreeProfileRequest;
use App\Services\Linktree\LinktreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinktreeApiController extends Controller
{
    public function show(Request $request, LinktreeService $service): JsonResponse
    {
        return response()->json(['data' => $service->profile($request->user())->load('links')]);
    }

    public function update(UpdateLinktreeProfileRequest $request, LinktreeService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->update($request->user(), UpdateLinktreeProfileDTO::fromArray($request->validated())),
        ]);
    }
}
