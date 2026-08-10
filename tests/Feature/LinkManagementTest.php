<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\ShortUrl\ShortUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_creates_link_with_audit_and_generates_slug(): void
    {
        $user = User::factory()->create();

        $link = app(ShortUrlService::class)->create($user, [
            'destination_url' => 'https://laravel.com',
        ]);

        $this->assertInstanceOf(ShortUrl::class, $link);
        $this->assertSame($user->id, $link->owner_id);
        $this->assertNotEmpty($link->slug);
        $this->assertSame(0, $link->total_clicks);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'action' => 'link.create',
            'resource_type' => 'short_url',
            'resource_id' => $link->id,
        ]);
    }

    public function test_service_creates_link_with_custom_slug(): void
    {
        $user = User::factory()->create();

        $link = app(ShortUrlService::class)->create($user, [
            'destination_url' => 'https://example.com',
        ], 'my-custom');

        $this->assertSame('my-custom', $link->slug);
    }

    public function test_service_hashes_password(): void
    {
        $user = User::factory()->create();

        $link = app(ShortUrlService::class)->create($user, [
            'destination_url' => 'https://example.com',
            'password' => 's3cret',
        ]);

        $this->assertTrue($link->hasPassword());
        $this->assertNotSame('s3cret', $link->password_hash);
        $this->assertTrue(password_verify('s3cret', $link->password_hash));
    }

    public function test_service_rejects_invalid_custom_slug(): void
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        app(ShortUrlService::class)->stringToCustomSlug('bad slug!!');
    }

    public function test_audit_logger_records_action(): void
    {
        $user = User::factory()->create();

        $log = AuditLogger::log($user, 'custom.action', 'resource', 42, ['key' => 'value']);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'custom.action',
            'resource_type' => 'resource',
            'resource_id' => 42,
        ]);
    }
}