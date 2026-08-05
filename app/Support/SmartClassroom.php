<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class SmartClassroom
{
    public static function layers(): array
    {
        return config('smart_classroom.layers', []);
    }

    public static function modules(): array
    {
        return config('smart_classroom.modules', []);
    }

    public static function module(string $key): ?array
    {
        $module = self::modules()[$key] ?? null;

        if (! $module) {
            return null;
        }

        return array_merge($module, [
            'key' => $key,
            'layer_meta' => self::layers()[$module['layer']] ?? [],
        ]);
    }

    public static function modulesForUser(?User $user): array
    {
        return collect(self::modules())
            ->filter(fn (array $module) => self::userCanAccess($user, $module))
            ->map(fn (array $module, string $key) => array_merge($module, [
                'key' => $key,
                'layer_meta' => self::layers()[$module['layer']] ?? [],
                'active' => self::isModuleActive($module),
                'url' => Route::has($module['route']) ? route($module['route']) : '#',
            ]))
            ->all();
    }

    public static function modulesByLayer(?User $user): array
    {
        $grouped = [];

        foreach (self::layers() as $layerKey => $layer) {
            $grouped[$layerKey] = [
                'meta' => $layer,
                'modules' => [],
            ];
        }

        foreach (self::modulesForUser($user) as $key => $module) {
            $grouped[$module['layer']]['modules'][$key] = $module;
        }

        return array_filter($grouped, fn ($group) => count($group['modules']) > 0);
    }

    public static function isModuleActive(array|string $module): bool
    {
        $patterns = is_string($module)
            ? (self::module($module)['patterns'] ?? [])
            : ($module['patterns'] ?? []);

        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    public static function currentModule(): ?array
    {
        foreach (self::modules() as $key => $module) {
            if (self::isModuleActive($module)) {
                return self::module($key);
            }
        }

        return null;
    }

    public static function userCanAccess(?User $user, array $module): bool
    {
        if (empty($module['roles'])) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return in_array($user->role, $module['roles'], true);
    }
}
