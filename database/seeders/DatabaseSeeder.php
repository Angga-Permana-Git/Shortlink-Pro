<?php

namespace Database\Seeders;

use App\Models\ClickEvent;
use App\Models\ShortUrl;
use App\Models\User;
use App\Support\Enums\ClickStatus;
use App\Support\Enums\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.local'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'password',
                'role' => Role::Admin->value,
                'is_active' => true,
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'user@company.local'],
            [
                'name' => 'User Biasa',
                'username' => 'user',
                'password' => 'password',
                'role' => Role::User->value,
                'is_active' => true,
            ]
        );

        if (ShortUrl::query()->count() === 0) {
            $link1 = ShortUrl::factory()->create([
                'owner_id' => $user->id,
                'slug' => 'demo-active',
                'destination_url' => 'https://laravel.com',
            ]);

            $link2 = ShortUrl::factory()->protected()->create([
                'owner_id' => $user->id,
                'slug' => 'demo-protected',
                'destination_url' => 'https://filamentphp.com',
            ]);

            $link3 = ShortUrl::factory()->expired()->create([
                'owner_id' => $user->id,
                'slug' => 'demo-expired',
                'destination_url' => 'https://example.com',
            ]);

            foreach ([$link1, $link2, $link3] as $link) {
                ClickEvent::factory()->count(5)->create([
                    'short_url_id' => $link->id,
                    'status' => ClickStatus::Success->value,
                ]);
            }

            $link1->total_clicks = $link1->clicks()->where('status', ClickStatus::Success->value)->count();
            $link1->save();
        }
    }
}