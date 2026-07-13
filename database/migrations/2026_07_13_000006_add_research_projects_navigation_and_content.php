<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $db = DB::connection($this->connection);
        $db->table('pages')->where('slug', 'results')->update(['nav_order' => 1]);

        $projectPage = $db->table('pages')->where('slug', 'projects')->first();
        $values = [
            'title_zh_tw' => '研究計畫', 'title_en' => 'Research Projects',
            'nav_group' => 'results', 'nav_order' => 2, 'updated_at' => now(),
        ];
        if ($projectPage) {
            $db->table('pages')->where('id', $projectPage->id)->update($values);
        } else {
            $db->table('pages')->insert($values + [
                'slug' => 'projects', 'view' => null, 'description' => null,
                'hero_image' => null, 'created_at' => now(),
            ]);
        }

        $db->table('pages')->where('slug', 'publications')->update(['nav_order' => 3]);
        $db->table('pages')->where('slug', 'plants')->update(['nav_order' => 4]);

        $projects = $db->table('projects')->where(function ($query): void {
            $query->where(function ($q): void { $q->whereNotNull('summary_zh_tw')->where('summary_zh_tw', '!=', ''); })
                ->orWhere(function ($q): void { $q->whereNotNull('summary_en')->where('summary_en', '!=', ''); });
        })->get();

        foreach ($projects as $project) {
            $exists = $db->table('content_blocks')->where('owner_type', 'projects')->where('owner_id', $project->id)->exists();
            if (! $exists) {
                $db->table('content_blocks')->insert([
                    'owner_type' => 'projects', 'owner_id' => $project->id, 'block_type' => 'intro',
                    'title_zh_tw' => '計畫摘要', 'title_en' => 'Project Summary',
                    'body_zh_tw' => $project->summary_zh_tw, 'body_en' => $project->summary_en,
                    'view' => null, 'params' => json_encode([]), 'sort_order' => 0, 'is_public' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $db = DB::connection($this->connection);
        $db->table('pages')->where('slug', 'projects')->delete();
        $db->table('pages')->where('slug', 'publications')->update(['nav_order' => 2]);
        $db->table('pages')->where('slug', 'plants')->update(['nav_order' => 3]);
    }
};
