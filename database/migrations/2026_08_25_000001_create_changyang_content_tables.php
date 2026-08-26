<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->create('changyang_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title');
            $table->string('navigation_label', 100)->nullable();
            $table->string('template', 50)->default('default');
            $table->string('hero_image_path', 500)->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->json('hero_settings')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->boolean('show_in_navigation')->default(false);
            $table->unsignedInteger('navigation_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['show_in_navigation', 'navigation_order']);
            $table->index('is_active');
        });

        $schema->create('changyang_page_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('changyang_pages')->cascadeOnDelete();
            $table->string('heading')->nullable();
            $table->text('subheading')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['page_id', 'is_active', 'sort_order']);
        });

        $schema->create('changyang_content_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('section_id')->constrained('changyang_page_sections')->cascadeOnDelete();
            $table->string('type', 50)->default('rich_text');
            $table->string('layout', 50)->default('text_only');
            $table->string('heading')->nullable();
            $table->longText('media_content_html')->nullable();
            $table->longText('content_html')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['section_id', 'is_active', 'sort_order']);
        });

        $schema->create('changyang_block_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_block_id')->constrained('changyang_content_blocks')->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->string('link_url', 1000)->nullable();
            $table->json('display_settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['content_block_id', 'sort_order']);
        });

        $schema->create('changyang_news', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('category_year');
            $table->unsignedTinyInteger('category_month');
            $table->longText('content_html');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_year', 'category_month', 'sort_order']);
            $table->index('is_active');
        });

        $schema->create('changyang_galleries', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image_path', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $schema->create('changyang_gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_id')->constrained('changyang_galleries')->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->string('thumbnail_path', 500)->nullable();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['gallery_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->dropIfExists('changyang_gallery_items');
        $schema->dropIfExists('changyang_galleries');
        $schema->dropIfExists('changyang_news');
        $schema->dropIfExists('changyang_block_images');
        $schema->dropIfExists('changyang_content_blocks');
        $schema->dropIfExists('changyang_page_sections');
        $schema->dropIfExists('changyang_pages');
    }
};
