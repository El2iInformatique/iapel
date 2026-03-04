<?php

namespace App\Services;

use App\Models\RuntimeSecret;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SecretStore
{
    private const CACHE_PREFIX = 'runtime_secret:';

    public function get(string $key, ?string $default = null): ?string
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, now()->addMinutes(1), function () use ($key, $default) {
            $secret = RuntimeSecret::where('key', $key)->first();

            if ($secret) {
                try {
                    return Crypt::decryptString($secret->value);
                } catch (\Throwable $e) {
                    return $default;
                }
            }

            return config('secrets.' . $key, $default);
        });
    }

    public function set(string $key, string $value, ?string $updatedBy = null): void
    {
        RuntimeSecret::updateOrCreate(
            ['key' => $key],
            [
                'value' => Crypt::encryptString($value),
                'updated_by' => $updatedBy,
            ]
        );

        Cache::forget(self::CACHE_PREFIX . $key);
    }

    public function forget(string $key): void
    {
        RuntimeSecret::where('key', $key)->delete();
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    public function isAuthorized(string $providedToken, ?string $clientKey = null): bool
    {
        $clientToken = $clientKey ? $this->get($clientKey) : null;
        $adminToken = $this->get('admin');

        return $this->safeEquals($providedToken, $clientToken)
            || $this->safeEquals($providedToken, $adminToken);
    }

    private function safeEquals(string $providedToken, ?string $expectedToken): bool
    {
        if (!is_string($expectedToken) || $expectedToken === '') {
            return false;
        }

        return hash_equals($providedToken, $expectedToken);
    }
}
