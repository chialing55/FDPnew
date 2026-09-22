<?php

namespace App\Services\Web;

class PublicationTextNormalizer
{
    /** 移除連字號與破折號前後的匯入空白。 */
    public static function text(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtr($value, [
            '‐' => '-', // U+2010 HYPHEN: some fonts render it with visual side space.
            '‑' => '-', // U+2011 NON-BREAKING HYPHEN
        ]);

        return preg_replace('/\\s*([\\-‒–—])\\s*/u', '$1', trim($value));
    }

    public static function metadata(array $data): array
    {
        foreach ($data as $field => $value) {
            if (is_string($value)) {
                $data[$field] = static::text($value);
            }
        }

        return $data;
    }
}
