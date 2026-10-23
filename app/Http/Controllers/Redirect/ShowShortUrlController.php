<?php

namespace App\Http\Controllers\Redirect;

use App\Dto\RequestContextDTO;
use App\Http\Controllers\Controller;
use App\Services\Redirect\LinkAccessService;
use App\Support\Enums\LinkAccessStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membuka tautan pendek: mengarahkan pengunjung ke tujuan, atau menampilkan
 * halaman kata sandi dan halaman status bila belum bisa dibuka.
 */
class ShowShortUrlController extends Controller
{
    public function __invoke(Request $request, string $slug, LinkAccessService $access): View|RedirectResponse|Response
    {
        $shortUrl = $access->find($slug);

        if (! $shortUrl) {
            return response()->view('short.not_found', [], 404);
        }

        $alreadyUnlocked = (bool) $request->session()->get(LinkAccessService::sessionKey($slug));

        $status = $access->open($shortUrl, $alreadyUnlocked, RequestContextDTO::fromRequest($request));

        return match ($status) {
            LinkAccessStatus::Ready => redirect()->away($shortUrl->destination_url),
            LinkAccessStatus::PasswordRequired => view('short.password', ['shortUrl' => $shortUrl]),
            default => view('short.error', ['status' => $status]),
        };
    }
}
