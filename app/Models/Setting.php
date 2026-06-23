<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return string|null
     */
    public static function get(string $key, $default = null): ?string
    {
        $setting = self::find($key);
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param string|null $value
     * @return void
     */
    public static function set(string $key, ?string $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
