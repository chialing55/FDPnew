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

        $schema->create('changyang_person_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $schema->create('changyang_people', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->longText('introduction_html')->nullable();
            $table->longText('contact_html')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('image_alt', 500)->nullable();
            $table->json('display_settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $schema->create('changyang_person_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('person_id')->constrained('changyang_people')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('changyang_person_categories')->cascadeOnDelete();
            $table->string('role_title', 255)->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->boolean('is_current')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['category_id', 'is_current', 'sort_order']);
        });

        $this->importExistingPeople();
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        $schema->dropIfExists('changyang_person_roles');
        $schema->dropIfExists('changyang_people');
        $schema->dropIfExists('changyang_person_categories');
    }

    private function importExistingPeople(): void
    {
        $db = DB::connection($this->connection);
        $page = $db->table('changyang_pages')->where('slug', 'people')->first();

        if ($page === null) {
            return;
        }

        $now = now();
        $sections = $db->table('changyang_page_sections')
            ->where('page_id', $page->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($sections as $section) {
            $blocks = $db->table('changyang_content_blocks')
                ->where('section_id', $section->id)
                ->where('type', 'person')
                ->orderBy('sort_order')
                ->get();

            if ($blocks->isEmpty()) {
                continue;
            }

            $categoryId = $db->table('changyang_person_categories')->insertGetId([
                'slug' => 'legacy-section-'.$section->id,
                'title' => $section->heading ?: 'People',
                'sort_order' => $section->sort_order,
                'is_active' => (bool) $section->is_active,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($blocks as $block) {
                $image = $db->table('changyang_block_images')
                    ->where('content_block_id', $block->id)
                    ->orderBy('sort_order')
                    ->first();
                $personId = $db->table('changyang_people')->insertGetId([
                    'name' => $block->heading ?: '未命名人物',
                    'introduction_html' => $block->content_html,
                    'contact_html' => $block->media_content_html,
                    'image_path' => $image?->image_path,
                    'image_alt' => $image?->alt_text ?: $block->heading,
                    'display_settings' => $image?->display_settings,
                    'sort_order' => $block->sort_order,
                    'is_active' => (bool) $block->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $db->table('changyang_person_roles')->insert([
                    'person_id' => $personId,
                    'category_id' => $categoryId,
                    'is_current' => true,
                    'sort_order' => $block->sort_order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
