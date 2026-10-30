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
 * Menerima kata sandi tautan yang dilindungi dan mengingatnya di sesi
 * pengunjung begitu cocok.
 */
class UnlockShortUrlController extends Controller
{
    public function __invoke(Request $request, string $slug, LinkAccessService $access): View|RedirectResponse|Response
    {
        $shortUrl = $access->find($slug);

        if (! $shortUrl?->hasPassword()) {
            return redirect()->route('redirect.show', $slug);
        }

        $request->validate(['password' => ['required', 'string']]);

        $password = (string) $request->input('password');

        $status = $access->checkPassword($shortUrl, $password, RequestContextDTO::fromRequest($request));

        if ($status === LinkAccessStatus::WrongPassword) {
            return back()->withErrors(['password' => 'Kata sandi salah. Silakan coba lagi.']);
        }

        if ($status === LinkAccessStatus::Ready) {
            $request->session()->put(LinkAccessService::sessionKey($slug), true);

            return redirect()->away($shortUrl->destination_url)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return response()->view('short.error', ['status' => $status])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
