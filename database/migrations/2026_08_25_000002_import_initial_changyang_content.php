<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $data = $this->importData();
        $db = DB::connection($this->connection);

        $db->transaction(function () use ($data, $db): void {
            if ($db->table('changyang_pages')->exists()
                || $db->table('changyang_news')->exists()
                || $db->table('changyang_galleries')->exists()) {
                throw new \RuntimeException('Changyang content tables must be empty before the initial import.');
            }

            $now = now();
            foreach ($data['pages'] as $page) {
                $sections = $page['sections'];
                unset($page['sections']);
                $page['hero_settings'] = $this->json($page['hero_settings']);
                $page['created_at'] = $now;
                $page['updated_at'] = $now;
                $pageId = $db->table('changyang_pages')->insertGetId($page);

                foreach ($sections as $section) {
                    $blocks = $section['blocks'];
                    unset($section['blocks']);
                    $section['page_id'] = $pageId;
                    $section['settings'] = $this->json($section['settings']);
                    $section['created_at'] = $now;
                    $section['updated_at'] = $now;
                    $sectionId = $db->table('changyang_page_sections')->insertGetId($section);

                    foreach ($blocks as $block) {
                        $images = $block['images'];
                        unset($block['images']);
                        $block['section_id'] = $sectionId;
                        $block['settings'] = $this->json($block['settings']);
                        $block['created_at'] = $now;
                        $block['updated_at'] = $now;
                        $blockId = $db->table('changyang_content_blocks')->insertGetId($block);

                        foreach ($images as $image) {
                            $image['content_block_id'] = $blockId;
                            $image['created_at'] = $now;
                            $image['updated_at'] = $now;
                            $db->table('changyang_block_images')->insert($image);
                        }
                    }
                }
            }

            foreach ($data['news'] as $news) {
                $news['created_at'] = $now;
                $news['updated_at'] = $now;
                $db->table('changyang_news')->insert($news);
            }

            foreach ($data['galleries'] as $gallery) {
                $items = $gallery['items'];
                unset($gallery['items']);
                $gallery['created_at'] = $now;
                $gallery['updated_at'] = $now;
                $galleryId = $db->table('changyang_galleries')->insertGetId($gallery);

                foreach ($items as $item) {
                    $item['gallery_id'] = $galleryId;
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;
                    $db->table('changyang_gallery_items')->insert($item);
                }
            }
        });
    }

    public function down(): void
    {
        $data = $this->importData();
        $db = DB::connection($this->connection);

        $db->transaction(function () use ($data, $db): void {
            $db->table('changyang_gallery_items')->delete();
            $db->table('changyang_galleries')->delete();
            $db->table('changyang_news')->delete();
            $db->table('changyang_pages')->whereIn('slug', array_column($data['pages'], 'slug'))->delete();
        });
    }

    private function importData(): array
    {
        $path = database_path('data/changyang-content.json');
        if (! is_file($path)) {
            throw new \RuntimeException('Missing database/data/changyang-content.json.');
        }

        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (($data['version'] ?? null) !== 1
            || ! isset($data['pages'], $data['news'], $data['galleries'])) {
            throw new \RuntimeException('Invalid Changyang import data.');
        }

        return $data;
    }

    private function json(?array $value): ?string
    {
        return $value === null ? null : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
};
