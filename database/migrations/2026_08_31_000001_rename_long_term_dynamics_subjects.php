<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $webConnection = 'mysql_web';

    public function up(): void
    {
        $db = DB::connection($this->webConnection);

        $db->transaction(function (): void {
            $this->renameSubject(
                ['subjects/tree'],
                'subjects/long-term-tree-dynamics',
                '樹木長期動態',
                'Long-term Tree Dynamics'
            );
            $this->renameSubject(
                ['subjects/seedling', 'subjects/forest-regeneration'],
                'subjects/long-term-seedling-dynamics',
                '幼苗長期動態',
                'Long-term Seedling Dynamics'
            );
        });
    }

    public function down(): void
    {
        $db = DB::connection($this->webConnection);

        $db->transaction(function (): void {
            $this->renameSubject(
                ['subjects/long-term-tree-dynamics'],
                'subjects/tree',
                '樹木動態',
                'Tree Dynamics'
            );
            $this->renameSubject(
                ['subjects/long-term-seedling-dynamics'],
                'subjects/forest-regeneration',
                '森林更新',
                'Forest Regeneration'
            );
        });
    }

    /** @param array<int, string> $oldSlugs */
    private function renameSubject(array $oldSlugs, string $newSlug, string $nameZh, string $nameEn): void
    {
        $db = DB::connection($this->webConnection);
        $page = $db->table('pages')
            ->whereIn('slug', [...$oldSlugs, $newSlug])
            ->first();

        if ($page === null) {
            throw new \RuntimeException("找不到研究主題頁面：{$newSlug}");
        }

        $db->table('pages')->where('id', $page->id)->update([
            'slug' => $newSlug,
            'title_zh_tw' => $nameZh,
            'title_en' => $nameEn,
            'updated_at' => now(),
        ]);
        $db->table('subjects')->where('page_id', $page->id)->update([
            'name_zh_tw' => $nameZh,
            'name_en' => $nameEn,
            'short_name_zh_tw' => $nameZh,
            'short_name_en' => $nameEn,
            'updated_at' => now(),
        ]);
    }
};
