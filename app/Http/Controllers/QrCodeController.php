<?php

namespace App\Http\Controllers;

use App\Services\QrCode\QrCodeService;
use Illuminate\Http\Response;

class QrCodeController extends Controller
{
    public function show(string $slug, QrCodeService $qrCode): Response
    {
        return response($qrCode->svgUntukSlug($slug))
            ->header('Content-Type', 'image/svg+xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age='.config('pemendek.qr_cache_seconds'));
    }
}
