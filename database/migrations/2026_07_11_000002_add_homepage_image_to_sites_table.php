<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::connection('mysql_web')->table('sites', fn (Blueprint $table) => $table->string('homepage_image')->nullable()->after('description_en'));
        DB::connection('mysql_web')->table('sites')->get()->each(function ($site): void {
            $slug = DB::connection('mysql_web')->table('pages')->where('id', $site->page_id)->value('slug');
            $name = basename((string) $slug) . '_thumb.jpg';
            if (is_file(public_path('images/plots/' . $name))) DB::connection('mysql_web')->table('sites')->where('id', $site->id)->update(['homepage_image' => $name]);
        });
    }
    public function down(): void
    {
        Schema::connection('mysql_web')->table('sites', fn (Blueprint $table) => $table->dropColumn('homepage_image'));
    }
};
