<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $db = DB::connection('mysql_web');
        $schema = Schema::connection('mysql_web');
        $sections = $db->table('changyang_page_sections')->orderBy('sort_order')->orderBy('id')->get();
        $blocks = $db->table('changyang_content_blocks')->get()->groupBy('section_id');
        $peoplePageId = $db->table('changyang_pages')->where('slug', 'people')->value('id');

        // Validate before MySQL DDL (which cannot be rolled back).
        foreach ($sections as $section) {
            $children = $blocks->get($section->id, collect());
            if ($section->page_id == $peoplePageId) {
                foreach ($children->where('type', 'person') as $block) {
                    if (! $db->table('changyang_people')->where('name', $block->heading)->exists()) {
                        throw new RuntimeException('People 尚未完整轉移：'.$block->heading);
                    }
                }

                continue;
            }
            if ($children->count() !== 1 || filled($section->subheading) || filled($section->settings)) {
                throw new RuntimeException('需先確認段落資料再簡化：'.$section->id);
            }
            $block = $children->first();
            if (filled($block->settings) || (filled($block->heading) && filled($section->heading) && $block->heading !== $section->heading)) {
                throw new RuntimeException('需先確認區塊標題或設定：'.$block->id);
            }
        }

        $backup = [];
        foreach (['changyang_page_sections', 'changyang_content_blocks', 'changyang_block_images'] as $table) {
            $backup[$table] = $db->table($table)->get()->all();
        }
        $directory = storage_path('app/migration-backups');
        File::ensureDirectoryExists($directory, 0700);
        $path = $directory.'/changyang-content-before-flatten-'.now()->format('Ymd-His').'-'.uniqid().'.json';
        if (File::put($path, json_encode($backup, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
            throw new RuntimeException('無法備份原始內容資料。');
        }
        chmod($path, 0600);

        $schema->table('changyang_content_blocks', function (Blueprint $table): void {
            $table->foreignId('page_id')->nullable()->constrained('changyang_pages')->cascadeOnDelete();
        });

        $db->transaction(function () use ($db, $sections, $blocks, $peoplePageId): void {
            $positions = [];
            foreach ($sections as $section) {
                if ($section->page_id == $peoplePageId) {
                    // Deletes only legacy rows and image references, never image files or new people data.
                    $db->table('changyang_content_blocks')->where('section_id', $section->id)->delete();

                    continue;
                }
                $block = $blocks[$section->id]->first();
                $positions[$section->page_id] = ($positions[$section->page_id] ?? 0) + 1;
                $heading = $section->heading ?: $block->heading;
                $db->table('changyang_content_blocks')->where('id', $block->id)->update([
                    'page_id' => $section->page_id,
                    'heading' => $heading === null ? null : preg_replace('#<br\s*/?>#i', "\n", $heading),
                    'is_active' => $section->is_active && $block->is_active,
                    'sort_order' => $positions[$section->page_id],
                ]);
            }
        });

        $schema->table('changyang_content_blocks', function (Blueprint $table): void {
            $table->dropForeign(['section_id']);
            $table->dropIndex(['section_id', 'is_active', 'sort_order']);
            $table->dropColumn(['section_id', 'type', 'settings']);
            $table->unsignedBigInteger('page_id')->nullable(false)->change();
            $table->index(['page_id', 'is_active', 'sort_order']);
        });
        $schema->drop('changyang_page_sections');
    }

    public function down(): void
    {
        throw new RuntimeException('此資料結構簡化需搭配舊版程式與 storage/app/migration-backups 的備份手動還原。');
    }
};
