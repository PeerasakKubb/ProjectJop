<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteContent
{
    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            return Cache::remember("site_setting.{$key}", 3600, fn () => SiteSetting::get($key, $default));
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function all(): array
    {
        try {
            return Cache::remember('site_settings.all', 3600, function () {
                return SiteSetting::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable) {
            return [];
        }
    }

    public static function forget(): void
    {
        Cache::forget('site_settings.all');
        foreach (SiteSetting::query()->pluck('key') as $key) {
            Cache::forget("site_setting.{$key}");
        }
    }
}
