<?php

namespace App\Helpers;

class PageTitleHelper
{
    /**
     * 取得導航標題用的翻譯 key
     */
    public static function getNavTitleKey(): string
    {
        $segment1 = request()->segment(1);
        $segment2 = request()->segment(2);

        if (!empty($segment2)) {
            return "web.nav_{$segment1}_{$segment2}";
        }

        return "web.nav_{$segment1}";
    }

    /**
     * 回傳語系後的實際 title
     */
    public static function getTranslatedTitle(): string
    {
        $key = self::getNavTitleKey();
        return __($key);
    }

    /**
     * 產生麵包屑
     * 回傳格式：
     * [
     *   ['label' => '背景介紹', 'url' => '/background'],
     *   ['label' => '研究動機', 'url' => '/background/motivation']
     * ]
     */
    public static function breadcrumbs(): array
    {
        $segment1 = request()->segment(1);
        $segment2 = request()->segment(2);

        $breadcrumbs[] = ['label' => __('web.nav_home'), 'url' => '/'];


        if (!empty($segment1)) {
            $key1 = "web.nav_{$segment1}";
            $breadcrumbs[] = [
                'label' => __($key1),
                'url' => '',
            ];
        }

        if (!empty($segment2)) {
            $key2 = "web.nav_{$segment1}_{$segment2}";
            $breadcrumbs[] = [
                'label' => __($key2),
                'url' => '',
            ];
        }

        return $breadcrumbs;
    }
}
