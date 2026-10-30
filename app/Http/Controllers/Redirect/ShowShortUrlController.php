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
            return response()->view('short.not_found', [], 404)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        $alreadyUnlocked = (bool) $request->session()->get(LinkAccessService::sessionKey($slug));

        $status = $access->open($shortUrl, $alreadyUnlocked, RequestContextDTO::fromRequest($request));

        if ($status === LinkAccessStatus::Ready) {
            return redirect()->away($shortUrl->destination_url)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        if ($status === LinkAccessStatus::PasswordRequired) {
            return response()->view('short.password', ['shortUrl' => $shortUrl])
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return response()->view('short.error', ['status' => $status])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
