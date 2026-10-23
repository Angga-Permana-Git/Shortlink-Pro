<?php

namespace Tests\Feature;

use App\Exceptions\AppException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Kontrak galat dan penelusuran (disiplin_struktur.md bagian 5 dan 6):
 * setiap respons membawa header `X-Trace-Id`, dan galat pada permintaan JSON
 * berbentuk { error_code, message, trace_id } tanpa stack trace.
 */
class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_carries_trace_header(): void
    {
        $this->get('/login/local')
            ->assertOk()
            ->assertHeader('X-Trace-Id');
    }

    public function test_json_error_uses_uniform_format(): void
    {
        $response = $this->renderJson(new AppException('Data tidak ditemukan.', 'SHORT_URL_NOT_FOUND', 404));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertNotNull($response->getData(true)['trace_id'] ?? null);
        $this->assertSame([
            'error_code' => 'SHORT_URL_NOT_FOUND',
            'message' => 'Data tidak ditemukan.',
            'trace_id' => $response->getData(true)['trace_id'],
        ], $response->getData(true));
    }

    public function test_json_error_never_leaks_stack_trace(): void
    {
        $response = $this->renderJson(new \RuntimeException('Kesalahan internal.'));

        $this->assertSame(500, $response->getStatusCode());

        $body = $response->getContent();

        $this->assertIsString($body);
        $this->assertStringNotContainsString('RuntimeException', $body);
        $this->assertStringNotContainsString('vendor/', $body);
        $this->assertStringNotContainsString('.php', $body);
    }

    public function test_json_validation_error_keeps_errors_key(): void
    {
        $request = Request::create('/api/v1/links', 'POST');
        $request->headers->set('Accept', 'application/json');
        $request->attributes->set('trace_id', 'uji-trace-422');

        $exception = ValidationException::withMessages(['destination_url' => ['Tautan panjang wajib diisi.']]);

        $response = app(ExceptionHandler::class)->render($request, $exception);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('VALIDATION_ERROR', $response->getData(true)['error_code']);
        $this->assertSame('uji-trace-422', $response->getData(true)['trace_id']);
        $this->assertSame(
            ['Tautan panjang wajib diisi.'],
            $response->getData(true)['errors']['destination_url']
        );
    }

    private function renderJson(\Throwable $exception): JsonResponse
    {
        $request = Request::create('/uji-galat', 'GET');
        $request->headers->set('Accept', 'application/json');
        $request->attributes->set('trace_id', 'uji-trace-123');

        $response = app(ExceptionHandler::class)->render($request, $exception);

        $this->assertInstanceOf(JsonResponse::class, $response);

        return $response;
    }
}
