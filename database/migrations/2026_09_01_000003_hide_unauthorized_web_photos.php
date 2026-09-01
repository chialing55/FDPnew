<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('mysql_web')
            ->table('photo')
            ->whereRaw('TRIM(photoby) = ?', ['未授權'])
            ->update(['is_public' => 0]);
    }

    public function down(): void
    {
        // Do not automatically publish photographs whose authorization status
        // has not changed merely because this migration is rolled back.
    }
};
