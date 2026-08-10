<?php

namespace App\Services\Branding;

use App\Models\Setting;
use App\Models\User;

class BrandingService
{
    public function appName(): string
    {
        return (string) (Setting::get('app_name') ?: config('app.name'));
    }

    public function brandColor(): string
    {
        return (string) (Setting::get('brand_color') ?: '#2563EB');
    }

    public function loginLogoUrl(): ?string
    {
        $path = Setting::get('login_logo');

        if ($path) {
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                return $path;
            }

            return asset('storage/' . ltrim($path, '/'));
        }

        if (file_exists(public_path('logo.png'))) {
            return asset('logo.png');
        }

        if (file_exists(public_path('images/logo.png'))) {
            return asset('images/logo.png');
        }

        return null;
    }

    public function loginLogoPath(): ?string
    {
        return Setting::get('login_logo');
    }

    public function faviconPath(): ?string
    {
        $path = Setting::get('favicon');

        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    public function updateBranding(User $actor, array $data): void
    {
        if (array_key_exists('app_name', $data) && $data['app_name'] !== null) {
            Setting::set('app_name', $data['app_name']);
        }

        if (array_key_exists('brand_color', $data) && $data['brand_color'] !== null) {
            Setting::set('brand_color', $data['brand_color']);
        }

        if (array_key_exists('login_logo', $data) && !empty($data['login_logo'])) {
            Setting::set('login_logo', $data['login_logo']);
        }

        if (array_key_exists('favicon', $data) && !empty($data['favicon'])) {
            Setting::set('favicon', $data['favicon']);
        }

        \App\Services\Audit\AuditLogger::log($actor, 'branding.update', 'setting');
    }
}