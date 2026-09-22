<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql_web')->table('changyang_gallery_items', function (Blueprint $table) {
            $table->boolean('is_cover')->default(false);
        });
        $db = DB::connection('mysql_web');
        foreach ($db->table('changyang_galleries')->get() as $gallery) {
            $items = $db->table('changyang_gallery_items')->where('gallery_id', $gallery->id)->orderBy('sort_order')->orderBy('id')->get();
            $cover = $items->first(fn ($item) => $item->image_path === $gallery->cover_image_path || ($item->thumbnail_path && $item->thumbnail_path === $gallery->cover_image_path)) ?? $items->first();
            if ($cover) {
                $db->table('changyang_gallery_items')->where('id', $cover->id)->update(['is_cover' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::connection('mysql_web')->table('changyang_gallery_items', fn (Blueprint $table) => $table->dropColumn('is_cover'));
    }
};
