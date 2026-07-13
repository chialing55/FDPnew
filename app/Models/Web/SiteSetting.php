<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'site_settings';
    protected $connection = 'mysql_web';

    protected $fillable = ['key', 'value'];

    /** @var array<string, string|null> */
    protected static array $resolvedValues = [];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::$resolvedValues[$key]
            ??= static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        static::$resolvedValues[$key] = $value;
    }
}
