<?php

namespace Database\Factories;

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShortUrl>
 */
class ShortUrlFactory extends Factory
{
    protected $model = ShortUrl::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'slug' => Str::random(7),
            'destination_url' => $this->faker->url(),
            'is_active' => true,
            'starts_at' => null,
            'expires_at' => null,
            'password_hash' => null,
            'total_clicks' => 0,
            'last_clicked_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function protected(): static
    {
        return $this->state(fn (array $attributes) => [
            'password_hash' => bcrypt('secret'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}