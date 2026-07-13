<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        DB::connection('mysql_web')->table('sites')->whereNotNull('homepage_image')->orderBy('id')->eachById(function ($site): void {
            if (! str_contains($site->homepage_image, '/')) {
                DB::connection('mysql_web')->table('sites')->where('id', $site->id)->update(['homepage_image' => 'plot-cards/' . $site->homepage_image]);
            }
        });
    }
    public function down(): void
    {
        DB::connection('mysql_web')->table('sites')->whereNotNull('homepage_image')->orderBy('id')->eachById(function ($site): void {
            DB::connection('mysql_web')->table('sites')->where('id', $site->id)->update(['homepage_image' => basename($site->homepage_image)]);
        });
    }
};
