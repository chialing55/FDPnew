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
        Schema::connection($this->connection)->create('content_block_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_block_id')->constrained('content_blocks')->cascadeOnDelete();
            $table->string('type', 30)->default('text');
            $table->longText('body_zh_tw')->nullable();
            $table->longText('body_en')->nullable();
            $table->string('component')->nullable();
            $table->json('params')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        $db = DB::connection($this->connection);
        $now = now();

        $db->table('content_blocks')
            ->where(fn ($query) => $query->whereNotNull('body_zh_tw')->orWhereNotNull('body_en'))
            ->orderBy('id')
            ->eachById(function ($block) use ($db, $now): void {
                if (! filled($block->body_zh_tw) && ! filled($block->body_en)) {
                    return;
                }

                $db->table('content_block_items')->insert([
                    'content_block_id' => $block->id,
                    'type' => 'text',
                    'body_zh_tw' => $block->body_zh_tw,
                    'body_en' => $block->body_en,
                    'sort_order' => 0,
                    'is_public' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });

        Schema::connection($this->connection)->table('content_blocks', function (Blueprint $table): void {
            $table->dropColumn(['body_zh_tw', 'body_en']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('content_blocks', function (Blueprint $table): void {
            $table->longText('body_zh_tw')->nullable()->after('title_en');
            $table->longText('body_en')->nullable()->after('body_zh_tw');
        });

        $db = DB::connection($this->connection);
        $db->table('content_block_items')->where('type', 'text')->orderBy('sort_order')->orderBy('id')->get()
            ->groupBy('content_block_id')->each(function ($items, int $blockId) use ($db): void {
                $db->table('content_blocks')->where('id', $blockId)->update([
                    'body_zh_tw' => $items->pluck('body_zh_tw')->filter()->implode("\n"),
                    'body_en' => $items->pluck('body_en')->filter()->implode("\n"),
                ]);
            });

        Schema::connection($this->connection)->dropIfExists('content_block_items');
    }
};
