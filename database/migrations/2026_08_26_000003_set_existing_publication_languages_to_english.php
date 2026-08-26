<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        DB::connection($this->connection)
            ->table('publications')
            ->update(['language' => 'en']);
    }

    public function down(): void
    {
        // The previous language cannot be inferred safely after normalization.
    }
};
