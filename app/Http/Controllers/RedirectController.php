<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Services\Analytics\AnalyticsService;
use App\Support\Enums\ClickStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function show(string $slug, AnalyticsService $analytics): \Illuminate\Contracts\View\View|RedirectResponse
    {
        $shortUrl = ShortUrl::query()->where('slug', $slug)->first();

        if (! $shortUrl) {
            return view('short.not_found');
        }

        if (! $shortUrl->is_active) {
            $analytics->recordClick($shortUrl, ClickStatus::Inactive);

            return view('short.error', ['title' => 'Link tidak aktif', 'message' => 'Link ini telah dinonaktifkan.']);
        }

        if ($shortUrl->isExpired()) {
            $analytics->recordClick($shortUrl, ClickStatus::Expired);

            return view('short.error', ['title' => 'Link kedaluwarsa', 'message' => 'Masa berlaku link ini telah berakhir. Silakan hubungi pemilik link.']);
        }

        if ($shortUrl->notStarted()) {
            $analytics->recordClick($shortUrl, ClickStatus::Scheduled);

            return view('short.error', ['title' => 'Link belum aktif', 'message' => 'Link ini akan aktif pada tanggal yang telah ditentukan.']);
        }

        if ($shortUrl->hasPassword()) {
            $key = 'shortlink-password:'.$shortUrl->id;

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $analytics->recordClick($shortUrl, ClickStatus::Protected);

                return view('short.error', ['title' => 'Terlalu banyak percobaan', 'message' => 'Terlalu banyak percobaan password. Silakan coba lagi nanti.']);
            }

            if (! session()->get('shortlink_unlocked_'.$shortUrl->id)) {
                $analytics->recordClick($shortUrl, ClickStatus::Protected);

                return view('short.password', ['shortUrl' => $shortUrl]);
            }
        }

        $analytics->recordClick($shortUrl, ClickStatus::Success);

        return redirect()->away($shortUrl->destination_url);
    }

    public function unlock(Request $request, string $slug, AnalyticsService $analytics): RedirectResponse|\Illuminate\Contracts\View\View
    {
        $shortUrl = ShortUrl::query()->where('slug', $slug)->first();

        if (! $shortUrl?->hasPassword()) {
            return redirect()->route('redirect.show', $slug);
        }

        $key = 'shortlink-password:'.$shortUrl->id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return view('short.error', [
                'title' => 'Terlalu banyak percobaan',
                'message' => 'Terlalu banyak percobaan password. Silakan coba lagi nanti.',
            ]);
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! \Illuminate\Support\Facades\Hash::check($request->input('password'), $shortUrl->password_hash)) {
            RateLimiter::hit($key, 60);

            $analytics->recordClick($shortUrl, ClickStatus::WrongPassword);

            return back()->withErrors(['password' => 'Kata sandi salah. Silakan coba lagi.']);
        }

        RateLimiter::clear($key);

        session()->put('shortlink_unlocked_'.$shortUrl->id, true);

        $analytics->recordClick($shortUrl, ClickStatus::Success);

        return redirect()->away($shortUrl->destination_url);
    }
}