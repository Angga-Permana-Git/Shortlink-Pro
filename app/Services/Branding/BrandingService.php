<?php

namespace App\Services\Branding;

use App\Dto\UpdateBrandingDTO;
use App\Models\Setting;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\Storage;

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

            return asset('storage/'.ltrim($path, '/'));
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

        return $path ? asset('storage/'.ltrim($path, '/')) : null;
    }

    public function updateBranding(User $actor, UpdateBrandingDTO $data): void
    {
        Setting::set('updated_by', $actor->getKey());

        if ($data->appName !== null) {
            Setting::set('app_name', $data->appName);
        }

        if ($data->brandColor !== null) {
            Setting::set('brand_color', $data->brandColor);
        }

        if ($data->loginLogo !== null) {
            Setting::set('login_logo', $data->loginLogo);
        }

        if ($data->favicon !== null) {
            Setting::set('favicon', $data->favicon);
        }

        AuditLogger::log($actor, 'branding.update', 'setting');
    }

    /**
     * Hapus logo khusus beserta berkasnya, lalu catat jejaknya.
     */
    public function deleteLogo(User $actor): void
    {
        $path = $this->loginLogoPath();

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        Setting::set('login_logo', null);
        Setting::set('updated_by', $actor->getKey());

        AuditLogger::log($actor, 'branding.logo_delete', 'setting');
    }
}
