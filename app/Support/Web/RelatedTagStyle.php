<?php

namespace App\Support\Web;

class RelatedTagStyle
{
    public static function for(string $type, int $id): string
    {
        $colors = [
            'site' => [
                // Sites use cool colors: slate-blue, blue, indigo, cyan,
                // teal and mint.
                ['#dce9f8', '#b9d3ef'], ['#cde7ff', '#8fc9f5'], ['#d8d8ff', '#aaaaf0'],
                ['#c8f0f5', '#83dce7'], ['#c8eee6', '#83d2c2'], ['#d3efdc', '#98d4aa'],
            ],
            'subject' => [
                // Research subjects use warm colors only. The sequence keeps
                // neighboring palette entries visually distinct.
                ['#fff3a3', '#ffe066'], ['#ffe0a3', '#ffc766'], ['#ffd0a8', '#ffad70'],
                ['#ffc2a8', '#ff916f'], ['#f7c0aa', '#e98f70'], ['#ffc1c7', '#f28b98'],
                ['#f7bfd9', '#e887b6'], ['#eec5df', '#d995c2'], ['#e8c3b2', '#cc9278'],
                ['#f3d2a2', '#dda85f'],
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
