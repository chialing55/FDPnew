<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('is_active');
        });

        DB::connection('mysql_web')->table('sites')
            ->join('pages', 'pages.id', '=', 'sites.page_id')
            ->select('sites.id', 'pages.nav_order')
            ->orderBy('pages.nav_order')
            ->orderBy('sites.id')
            ->get()
            ->values()
            ->each(function ($site, int $index): void {
                DB::connection('mysql_web')->table('sites')->where('id', $site->id)->update([
                    'sort_order' => $site->nav_order ?: $index + 1,
                ]);
            });
    }

    public function down(): void
    {
        Schema::connection('mysql_web')->table('sites', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
    }
};
