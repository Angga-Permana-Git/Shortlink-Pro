<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memberi setiap permintaan satu `trace_id` untuk penelusuran.
 *
 * `trace_id` disimpan di konteks log (bersama `user_id`) dan dikirim pada header
 * `X-Trace-Id`, sehingga galat yang tampil ke pengguna dapat ditelusuri ke
 * catatan server (disiplin_struktur.md bagian 5 dan 6).
 */
class AssignTraceId
{
    public const HEADER = 'X-Trace-Id';

    public function handle(Request $request, Closure $next): Response
    {
        $traceId = (string) Str::uuid();

        $request->attributes->set('trace_id', $traceId);

        Log::withContext([
            'request_id' => $traceId,
            'user_id' => $request->user()?->getAuthIdentifier(),
        ]);

        $response = $next($request);

        $response->headers->set(self::HEADER, $traceId);

        return $response;
    }
}
