<?php

namespace App\Support\Web;

class RelatedTagStyle
{
    public static function for(string $type, int $id): string
    {
        $colors = [
            'site' => [
                ['#f1f5f9', '#dbe4ee'], ['#eff6ff', '#dbeafe'], ['#eef2ff', '#dfe4ff'],
                ['#ecfeff', '#cffafe'], ['#f0fdfa', '#ccfbf1'], ['#ecfdf5', '#d1fae5'],
            ],
            'subject' => [
                ['#fffbeb', '#fef3c7'], ['#fff7ed', '#ffedd5'], ['#fff1f2', '#ffe4e6'],
                ['#fdf4ff', '#fae8ff'], ['#f5f3ff', '#ede9fe'], ['#f7fee7', '#ecfccb'],
                ['#ecfeff', '#cffafe'], ['#ecfdf5', '#d1fae5'], ['#f0f9ff', '#e0f2fe'],
                ['#fef2f2', '#fee2e2'],
            ],
        ];
        $list = $colors[$type] ?? [['#f3f4f6', '#e5e7eb']];
        [$background, $hoverBackground] = $list[$id % count($list)];

        return '--related-tag-bg: '.$background.'; --related-tag-hover-bg: '.$hoverBackground.';';
    }

    public static function classes(bool $elevated = false): string
    {
        return 'related-tag'.($elevated ? ' relative z-10' : '');
    }
}
