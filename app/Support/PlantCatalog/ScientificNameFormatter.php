<?php

namespace App\Support\PlantCatalog;

use Illuminate\Support\HtmlString;

class ScientificNameFormatter
{
    public static function format(?string $name): HtmlString
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $rankTokens = ['var.', 'subsp.', 'ssp.', 'f.', 'forma'];
        $italicIndexes = [];

        if (isset($parts[0])) {
            $italicIndexes[] = 0;
        }
        if (isset($parts[1]) && ! in_array(strtolower($parts[1]), $rankTokens, true)) {
            $italicIndexes[] = 1;
        }

        foreach ($parts as $index => $part) {
            if (in_array(strtolower($part), $rankTokens, true) && isset($parts[$index + 1])) {
                $italicIndexes[] = $index + 1;
            }
        }

        return new HtmlString(collect($parts)->map(function (string $part, int $index) use ($italicIndexes): string {
            $escaped = e($part);

            return in_array($index, $italicIndexes, true) ? '<em>'.$escaped.'</em>' : $escaped;
        })->implode(' '));
    }
}
