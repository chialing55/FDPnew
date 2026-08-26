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
        if (! $schema->hasColumn('changyang_content_blocks', 'media_content_html')) {
            $schema->table('changyang_content_blocks', function (Blueprint $table): void {
                $table->longText('media_content_html')->nullable()->after('heading');
            });
        }

        $db = DB::connection($this->connection);
        $blocks = $db->table('changyang_content_blocks')
            ->where('content_html', 'like', '%class="person-meta"%')
            ->get(['id', 'content_html']);

        foreach ($blocks as $block) {
            if (! preg_match('#^\s*<div class="person-meta">(.*?)</div>#is', $block->content_html, $match)) {
                continue;
            }

            $mediaContent = preg_replace('#<(?:strong|p)>\s*(?:<br\s*/?>|&nbsp;)*\s*</(?:strong|p)>#is', '', trim($match[1])) ?? trim($match[1]);
            $mediaContent = preg_replace('#(?:<br\s*/?>\s*)+</p>#i', '</p>', $mediaContent) ?? $mediaContent;
            $mediaContent = preg_replace('#(?:<br\s*/?>\s*)+$#i', '', $mediaContent) ?? $mediaContent;

            $db->table('changyang_content_blocks')->where('id', $block->id)->update([
                'media_content_html' => $mediaContent,
                'content_html' => trim(substr($block->content_html, strlen($match[0]))),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        if ($schema->hasColumn('changyang_content_blocks', 'media_content_html')) {
            $schema->table('changyang_content_blocks', function (Blueprint $table): void {
                $table->dropColumn('media_content_html');
            });
        }
    }
};
