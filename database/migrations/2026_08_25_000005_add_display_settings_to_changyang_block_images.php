<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);
        if (! $schema->hasColumn('changyang_block_images', 'display_settings')) {
            $schema->table('changyang_block_images', function (Blueprint $table): void {
                $table->json('display_settings')->nullable()->after('link_url');
            });
        }

        $db = DB::connection($this->connection);
        foreach ($this->peopleImageSettings() as $path => $settings) {
            $db->table('changyang_block_images')->where('image_path', $path)->update([
                'display_settings' => json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        if ($schema->hasColumn('changyang_block_images', 'display_settings')) {
            $schema->table('changyang_block_images', function (Blueprint $table): void {
                $table->dropColumn('display_settings');
            });
        }
    }

    private function peopleImageSettings(): array
    {
        $data = json_decode((string) file_get_contents(database_path('data/changyang-content.json')), true, flags: JSON_THROW_ON_ERROR);
        $people = collect($data['pages'])->firstWhere('slug', 'people');
        $settings = [];

        foreach ($people['sections'] ?? [] as $section) {
            foreach ($section['blocks'] as $block) {
                foreach ($block['images'] as $image) {
                    if (isset($image['display_settings'])) {
                        $settings[$image['image_path']] = $image['display_settings'];
                    }
                }
            }
        }

        return $settings;
    }
};
