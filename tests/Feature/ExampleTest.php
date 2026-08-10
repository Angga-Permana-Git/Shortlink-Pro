<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_shows_login_page_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Masuk');
    }
}
