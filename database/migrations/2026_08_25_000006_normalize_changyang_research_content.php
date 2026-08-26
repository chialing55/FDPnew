<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $db = DB::connection($this->connection);
        $page = $db->table('changyang_pages')->where('slug', 'research')->first();
        if ($page === null) {
            return;
        }

        $research = collect($this->importData()['pages'])->firstWhere('slug', 'research');
        if ($research === null) {
            throw new RuntimeException('Missing Research page in Changyang import data.');
        }

        $db->transaction(function () use ($db, $page, $research): void {
            $db->table('changyang_page_sections')->where('page_id', $page->id)->delete();
            $now = now();

            foreach ($research['sections'] as $section) {
                $blocks = $section['blocks'];
                unset($section['blocks']);
                $section['page_id'] = $page->id;
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
                        $image['display_settings'] = $this->json($image['display_settings']);
                        $image['content_block_id'] = $blockId;
                        $image['created_at'] = $now;
                        $image['updated_at'] = $now;
                        $db->table('changyang_block_images')->insert($image);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // The normalized structure intentionally replaces obsolete Weebly table markup.
    }

    private function importData(): array
    {
        return json_decode((string) file_get_contents(database_path('data/changyang-content.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    private function json(?array $value): ?string
    {
        return $value === null ? null : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
};
