<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql_web');

        if (! $schema->hasColumn('sites', 'homepage_image_settings')) {
            $schema->table('sites', function (Blueprint $table): void {
                $table->json('homepage_image_settings')->nullable()->after('homepage_image_position');
            });
        }

        DB::connection('mysql_web')->table('sites')
            ->whereNull('homepage_image_settings')
            ->orderBy('id')
            ->eachById(function (object $site): void {
                DB::connection('mysql_web')->table('sites')->where('id', $site->id)->update([
                    'homepage_image_settings' => json_encode([
                        'frame_height' => 320,
                        'position_x' => 50,
                        'position_y' => max(1, min(100, (int) ($site->homepage_image_position ?? 50))),
                        'scale' => 1,
                        'object_fit' => 'cover',
                    ]),
                ]);
            });
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql_web');

        if ($schema->hasColumn('sites', 'homepage_image_settings')) {
            $schema->table('sites', fn (Blueprint $table) => $table->dropColumn('homepage_image_settings'));
        }
    }
};
