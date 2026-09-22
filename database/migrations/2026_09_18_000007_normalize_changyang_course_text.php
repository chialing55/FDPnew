<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $db = DB::connection($this->connection);
        $pageId = $db->table('changyang_pages')->where('slug', 'courses')->value('id');
        $changes = [];
        foreach ($db->table('changyang_content_blocks')->where('page_id', $pageId)->get() as $block) {
            if (! str_contains($block->content_html ?? '', 'wsite-multicol-table')) {
                continue;
            }
            $document = new DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            try {
                $document->loadHTML('<?xml encoding="UTF-8">'.$block->content_html, LIBXML_NONET);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
            $xpath = new DOMXPath($document);
            $paragraphs = [];
            foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " paragraph ")][not(ancestor::div[contains(concat(" ", normalize-space(@class), " "), " paragraph ")])]') as $paragraph) {
                if (preg_replace('/[\s\x{00A0}\x{200B}]+/u', '', $paragraph->textContent) === '') {
                    continue;
                }
                $html = '';
                foreach ($paragraph->childNodes as $child) {
                    $html .= $document->saveHTML($child);
                }
                if (preg_match('/<(img|table)\b/i', $html)) {
                    throw new RuntimeException('課程說明含有額外圖片或表格，需先確認：'.$block->id);
                }
                $paragraphs[] = '<div>'.trim($html).'</div>';
            }
            if ($paragraphs === []) {
                throw new RuntimeException('找不到課程說明：'.$block->id);
            }
            // Only remove embedded photos when they already exist in the image fields.
            $images = $db->table('changyang_block_images')->where('content_block_id', $block->id)->pluck('image_path');
            foreach ($xpath->query('//img') as $image) {
                $path = ltrim(parse_url($image->getAttribute('src'), PHP_URL_PATH) ?? '', '/');
                $path = preg_replace('#^storage/#', '', $path);
                if (! $images->contains($path)) {
                    throw new RuntimeException('課程圖片尚未獨立儲存：'.$block->id);
                }
            }
            $changes[$block->id] = ['before' => $block->content_html, 'after' => implode("\n", $paragraphs)];
        }
        if ($changes === []) {
            return;
        }
        $directory = storage_path('app/migration-backups');
        File::ensureDirectoryExists($directory, 0700);
        $path = $directory.'/changyang-course-text-'.now()->format('Ymd-His').'-'.uniqid().'.json';
        if (File::put($path, json_encode($changes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
            throw new RuntimeException('無法備份課程內容。');
        }
        chmod($path, 0600);
        $db->transaction(function () use ($db, $changes): void {
            foreach ($changes as $id => $change) {
                $db->table('changyang_content_blocks')->where('id', $id)->update(['content_html' => $change['after']]);
            }
        });
    }

    public function down(): void
    {
        // Keep normalized text; original markup is in the migration backup.
    }
};
