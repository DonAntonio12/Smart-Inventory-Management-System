<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set or update a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value]
        );
    }

    /**
     * Retrieve multiple settings as an associative array with defaults.
     */
    public static function getMany(array $keysWithDefaults): array
    {
        $keys = array_keys($keysWithDefaults);
        $settings = static::query()->whereIn('key', $keys)->pluck('value', 'key');

        $result = [];
        foreach ($keysWithDefaults as $key => $default) {
            $result[$key] = $settings->has($key) && $settings[$key] !== null ? $settings[$key] : $default;
        }

        return $result;
    }
}
