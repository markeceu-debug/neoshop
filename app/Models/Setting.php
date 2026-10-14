<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Pega um valor de setting com cast automático.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) return $default;

        return match ($setting->type) {
            'int'     => (int) $setting->value,
            'decimal' => (float) $setting->value,
            'bool'    => (bool) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string'): self
    {
        if ($type === 'json') {
            $value = json_encode($value);
        }

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );
    }
}