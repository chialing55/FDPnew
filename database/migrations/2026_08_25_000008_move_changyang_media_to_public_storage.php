<?php

use App\Services\ChangYangMediaRelocator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $data = json_decode((string) file_get_contents(database_path('data/changyang-content.json')), true, flags: JSON_THROW_ON_ERROR);
        $manifest = $data['media_manifest'] ?? [];
        app(ChangYangMediaRelocator::class)->publish($manifest);

        $db = DB::connection($this->connection);
        $db->transaction(function () use ($db, $manifest): void {
            foreach ($manifest as $entry) {
                $source = $entry['source'];
                $target = $entry['target'];

                if (str_starts_with($target, 'changyang/heroes/')) {
                    $db->table('changyang_pages')->where('hero_image_path', $source)->update(['hero_image_path' => $target, 'updated_at' => now()]);
                }
                if (str_starts_with($target, 'changyang/content/')) {
                    $db->table('changyang_block_images')->where('image_path', $source)->update(['image_path' => $target, 'updated_at' => now()]);
                }
                if (str_contains($target, '/galleries/') || str_starts_with($target, 'changyang/galleries/')) {
                    $db->table('changyang_galleries')->where('cover_image_path', $source)->update(['cover_image_path' => $target, 'updated_at' => now()]);
                    $db->table('changyang_gallery_items')->where('image_path', $source)->update(['image_path' => $target, 'updated_at' => now()]);
                    $db->table('changyang_gallery_items')->where('thumbnail_path', $source)->update(['thumbnail_path' => $target, 'updated_at' => now()]);
                }

                foreach (['changyang_content_blocks', 'changyang_news'] as $table) {
                    $rows = $db->table($table)->where('content_html', 'like', '%/'.$source.'%')->get(['id', 'content_html']);
                    foreach ($rows as $row) {
                        $db->table($table)->where('id', $row->id)->update([
                            'content_html' => str_replace('/'.$source, '/storage/'.$target, $row->content_html),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Archived source files remain in public/changyang-assets, but uploaded storage media is not deleted on rollback.
    }
};
