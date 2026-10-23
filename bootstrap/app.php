<?php

use App\Exceptions\AppException;
use App\Http\Middleware\AssignTraceId;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Endpoint API yang butuh login memakai session cookie Laravel
        // (PRDapp.md bagian 3.7 dan 5), jadi grup api perlu cookie + session.
        $middleware->api(prepend: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ]);

        // Bahasa antarmuka ditentukan per pengguna/sesi setelah sesi tersedia.
        $middleware->web(append: [SetLocale::class]);

        $middleware->append(AssignTraceId::class);
        $middleware->append(SecurityHeaders::class);

        // Produksi berjalan di balik nginx host + Cloudflare Tunnel. Header
        // X-Forwarded-* hanya diterima dari proxy loopback/Docker; port
        // aplikasi sendiri dipublikasikan ke 127.0.0.1. Tanpa ini Laravel
        // menghasilkan URL http dan secure cookie tidak terkirim.
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '::1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Format galat seragam untuk permintaan JSON: `error_code` + `message` +
        // `trace_id` (disiplin_struktur.md bagian 5). Stack trace tidak pernah
        // dikirim ke klien.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            // Galat validasi mempertahankan kunci `errors` (dibaca JS) namun tetap
            // dibungkus format seragam `error_code` + `message` + `trace_id`.
            if ($e instanceof ValidationException) {
                $traceIdValidasi = (string) $request->attributes->get('trace_id', '');

                return response()->json([
                    'error_code' => 'VALIDATION_ERROR',
                    'message' => 'Data yang dikirim tidak valid.',
                    'trace_id' => $traceIdValidasi,
                    'errors' => $e->errors(),
                ], $e->status, [AssignTraceId::HEADER => $traceIdValidasi]);
            }

            $traceId = (string) $request->attributes->get('trace_id', '');

            $status = $e instanceof AppException
                ? $e->statusCode()
                : ($e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500);

            $errorCode = $e instanceof AppException
                ? $e->errorCode()
                : 'HTTP_'.$status;

            $message = $e->getMessage() !== ''
                ? $e->getMessage()
                : 'Terjadi kesalahan pada server.';

            return response()->json([
                'error_code' => $errorCode,
                'message' => $message,
                'trace_id' => $traceId,
            ], $status, [AssignTraceId::HEADER => $traceId]);
        });
    })->create();
