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

        $schema->table('photo', function (Blueprint $table): void {
            if (! Schema::connection('mysql_web')->hasColumn('photo', 'code')) {
                $table->string('code', 50)->nullable()->after('spcode')->index();
            }

            if (! Schema::connection('mysql_web')->hasColumn('photo', 'site')) {
                $table->string('site', 50)->nullable()->after('code')->index();
            }
        });

        $fushanCodes = DB::connection('plant_catalog')
            ->table('site_species')
            ->where('site', 'fushan')
            ->whereNotNull('code')
            ->where('code', '<>', '')
            ->pluck('code', 'spcode');

        foreach ($fushanCodes as $spcode => $code) {
            DB::connection('mysql_web')
                ->table('photo')
                ->where('spcode', $spcode)
                ->where(function ($query): void {
                    $query->whereNull('code')->orWhere('code', '');
                })
                ->update([
                    'code' => $code,
                    'site' => 'fushan',
                ]);
        }
    }

    public function down(): void
    {
        Schema::connection('mysql_web')->table('photo', function (Blueprint $table): void {
            if (Schema::connection('mysql_web')->hasColumn('photo', 'site')) {
                $table->dropColumn('site');
            }

            if (Schema::connection('mysql_web')->hasColumn('photo', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
