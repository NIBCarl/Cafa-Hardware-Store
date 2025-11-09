<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return json_decode($setting->value, true) ?? $default;
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value)]
        );
    }

    /**
     * Get all settings as an associative array
     */
    public static function getAll(): array
    {
        $settings = static::all();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = json_decode($setting->value, true);
        }

        return $result;
    }
}
