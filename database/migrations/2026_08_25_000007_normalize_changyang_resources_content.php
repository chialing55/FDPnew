<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $db = DB::connection($this->connection);
        $page = $db->table('changyang_pages')->where('slug', 'resources')->first();
        if ($page === null) {
            return;
        }

        $resources = collect($this->importData()['pages'])->firstWhere('slug', 'resources');
        if ($resources === null) {
            throw new RuntimeException('Missing Resources page in Changyang import data.');
        }

        $db->transaction(function () use ($db, $page, $resources): void {
            $db->table('changyang_page_sections')->where('page_id', $page->id)->delete();
            $now = now();

            foreach ($resources['sections'] as $section) {
                $blocks = $section['blocks'];
                unset($section['blocks']);
                $section['page_id'] = $page->id;
                $section['settings'] = $this->json($section['settings']);
                $section['created_at'] = $now;
                $section['updated_at'] = $now;
                $sectionId = $db->table('changyang_page_sections')->insertGetId($section);

                foreach ($blocks as $block) {
                    unset($block['images']);
                    $block['section_id'] = $sectionId;
                    $block['settings'] = $this->json($block['settings']);
                    $block['created_at'] = $now;
                    $block['updated_at'] = $now;
                    $db->table('changyang_content_blocks')->insert($block);
                }
            }
        });
    }

    public function down(): void
    {
        // The normalized blocks intentionally replace obsolete Weebly list wrappers.
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
