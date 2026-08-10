<?php

namespace Database\Factories;

use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Support\Enums\ClickStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClickEvent>
 */
class ClickEventFactory extends Factory
{
    protected $model = ClickEvent::class;

    public function definition(): array
    {
        return [
            'short_url_id' => ShortUrl::factory(),
            'status' => ClickStatus::Success->value,
            'ip_hash' => hash('sha256', $this->faker->ipv4()),
            'user_agent' => $this->faker->userAgent(),
            'referer' => $this->faker->optional()->randomElement([$this->faker->url(), null]),
        ];
    }
}