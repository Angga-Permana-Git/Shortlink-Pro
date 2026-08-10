<?php

namespace App\Services\System;

use App\Models\Setting;

class TimezoneService
{
    public const SETTING_KEY = 'timezone';

    public function get(): string
    {
        $tz = Setting::get(self::SETTING_KEY);

        if (! $tz || ! in_array($tz, timezone_identifiers_list(), true)) {
            return config('app.timezone', 'UTC');
        }

        return $tz;
    }

    public function set(string $timezone, int $updatedBy): bool
    {
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            return false;
        }

        Setting::set(self::SETTING_KEY, $timezone);
        Setting::set('updated_by', $updatedBy);

        return true;
    }

    public static function apply(): void
    {
        try {
            $tz = Setting::get(self::SETTING_KEY);
        } catch (\Throwable) {
            return;
        }

        if ($tz && in_array($tz, timezone_identifiers_list(), true)) {
            date_default_timezone_set($tz);
            app('config')->set('app.timezone', $tz);
        }
    }
}