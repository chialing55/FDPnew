<?php

namespace App\Support;

class ScientificNameFormatter
{
    private const NON_ITALIC_TOKENS = [
        'var.',
        'subsp.',
        'ssp.',
        'f.',
        'forma',
        'cf.',
        'aff.',
        'sp.',
        'spp.',
        'agg.',
        '×',
        'x',
    ];

    public static function segments(string $name): array
    {
        $tokens = preg_split('/(\s+)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);

        return array_map(function (string $token) {
            return [
                'text' => $token,
                'italic' => !preg_match('/^\s+$/u', $token) && !self::isNonItalicToken($token),
            ];
        }, $tokens ?: []);
    }

    private static function isNonItalicToken(string $token): bool
    {
        return in_array(strtolower(trim($token)), self::NON_ITALIC_TOKENS, true);
    }
}
