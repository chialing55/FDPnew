<?php

namespace App\Support\DataEntry;

final class SaveFeedback
{
    public static function status(int $saved, int $failed): string
    {
        if ($failed > 0) {
            return $saved > 0 ? 'partial' : 'error';
        }

        return $saved > 0 ? 'success' : 'noop';
    }

    public static function message(string $status, int $saved, int $skipped, int $failed): string
    {
        return match ($status) {
            'success' => "儲存完成：本次新增或更新 {$saved} 筆".self::skippedText($skipped).'。',
            'partial' => "部分儲存完成：本次新增或更新 {$saved} 筆".self::skippedText($skipped)."，錯誤 {$failed} 筆。錯誤資料已保留，請修正後再儲存。",
            'error' => "儲存失敗：錯誤 {$failed} 筆".self::skippedText($skipped).'。輸入資料已保留，請依錯誤提示修正。',
            default => '沒有可儲存資料'.self::skippedText($skipped).'。',
        };
    }

    private static function skippedText(int $skipped): string
    {
        return $skipped > 0 ? "，跳過 {$skipped} 筆尚未輸入資料" : '';
    }
}
