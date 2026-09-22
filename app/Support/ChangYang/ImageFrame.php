<?php

namespace App\Support\ChangYang;

final class ImageFrame
{
    public const MIN_HEIGHT = 80;

    public const MAX_HEIGHT = 1000;

    public const MAX_SCALE = 3;

    public static function defaults(): array
    {
        return ['frame_height' => 320, 'position_x' => 50, 'position_y' => 50, 'scale' => 1, 'object_fit' => 'cover'];
    }

    public static function normalize(?array $settings): array
    {
        $settings ??= [];

        if (! isset($settings['position_x'], $settings['position_y']) && filled($settings['position'] ?? null)) {
            [$x, $y] = array_pad(preg_split('/\s+/', trim((string) $settings['position'])) ?: [], 2, 'center');
            $settings['position_x'] = self::positionValue($x);
            $settings['position_y'] = self::positionValue($y);
        }

        if (! isset($settings['frame_height']) && isset($settings['height'])) {
            $settings['frame_height'] = $settings['height'];
        }

        $defaults = self::defaults();
        foreach (['frame_height', 'position_x', 'position_y', 'scale'] as $key) {
            $value = $settings[$key] ?? null;
            // Accept historical numeric pixel/percentage settings as well.
            if (is_string($value)) {
                $value = preg_replace('/(?:px|%)$/', '', trim($value));
            }
            $settings[$key] = is_numeric($value) && is_finite((float) $value) ? (float) $value : $defaults[$key];
        }
        $settings['frame_height'] = (int) max(self::MIN_HEIGHT, min(self::MAX_HEIGHT, $settings['frame_height']));
        $settings['position_x'] = max(0, min(100, $settings['position_x']));
        $settings['position_y'] = max(0, min(100, $settings['position_y']));
        $settings['scale'] = max(1, min(self::MAX_SCALE, $settings['scale']));
        $settings['object_fit'] = 'cover';

        return $settings;
    }

    private static function positionValue(string $value): float
    {
        return match (strtolower($value)) {
            'left', 'top' => 0,
            'right', 'bottom' => 100,
            default => is_numeric(rtrim($value, '%')) ? (float) rtrim($value, '%') : 50,
        };
    }
}
