<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $source = public_path('images/plots/fushan_thumb.jpg');
        $target = 'plot-cards/fushan_thumb.jpg';

        if (! Storage::disk('public')->exists($target) && is_file($source)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        if (Storage::disk('public')->exists($target)) {
            $pageId = DB::connection('mysql_web')->table('pages')
                ->where('slug', 'sites/fushan')
                ->value('id');

            if ($pageId) {
                DB::connection('mysql_web')->table('sites')
                    ->where('page_id', $pageId)
                    ->update(['homepage_image' => $target]);
            }
        }
    }

    public function down(): void
    {
        // 保留圖片與有效路徑，避免 rollback 造成前台斷圖。
    }
};
