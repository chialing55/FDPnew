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
        $db = DB::connection($this->connection);

        // view 功能已改由 PageDefault 固定加入，不再由管理者維護。
        $db->table('content_blocks')->whereNotNull('view')->where('view', '!=', '')->delete();

        Schema::connection($this->connection)->table('content_blocks', function (Blueprint $table): void {
            $table->dropColumn(['block_type', 'view', 'params']);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        $db = DB::connection($this->connection);

        $schema->table('content_blocks', function (Blueprint $table): void {
            $table->string('block_type', 50)->default('content')->after('owner_id');
            $table->string('view')->nullable()->after('body_en');
            $table->json('params')->nullable()->after('view');
        });

        $now = now();
        $teamPageId = $db->table('pages')->where('slug', 'about/team')->value('id');
        $resultsPageId = $db->table('pages')->where('slug', 'results')->value('id');
        $blocks = [];

        if ($teamPageId) {
            $blocks[] = [
                'owner_type' => 'pages', 'owner_id' => $teamPageId, 'block_type' => 'view',
                'title_zh_tw' => '樣區負責人', 'title_en' => 'Site Manager',
                'view' => 'web.site-teams-block', 'params' => json_encode(['currentRole' => 'plot_manager']),
                'sort_order' => 1, 'is_public' => true, 'created_at' => $now, 'updated_at' => $now,
            ];
            $blocks[] = [
                'owner_type' => 'pages', 'owner_id' => $teamPageId, 'block_type' => 'view',
                'title_zh_tw' => '合作單位', 'title_en' => 'Cooperative unit',
                'view' => 'web.site-teams-block', 'params' => json_encode(['currentRole' => 'team_partner']),
                'sort_order' => 2, 'is_public' => true, 'created_at' => $now, 'updated_at' => $now,
            ];
        }

        if ($resultsPageId) {
            $blocks[] = [
                'owner_type' => 'pages', 'owner_id' => $resultsPageId, 'block_type' => 'view',
                'title_zh_tw' => '成果列表', 'title_en' => 'Results List',
                'view' => 'web.research-output-list', 'params' => json_encode([]),
                'sort_order' => 0, 'is_public' => true, 'created_at' => $now, 'updated_at' => $now,
            ];
        }

        if ($blocks !== []) {
            $db->table('content_blocks')->insert($blocks);
        }
    }
};
