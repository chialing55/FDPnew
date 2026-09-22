<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $db = DB::connection($this->connection);

        // Use the original date-based order as the initial manual ordering.
        $db->table('changyang_news')
            ->orderByDesc('category_year')
            ->orderByDesc('category_month')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->pluck('id')
            ->each(fn (int $id, int $index) => $db->table('changyang_news')
                ->where('id', $id)
                ->update(['sort_order' => $index + 1]));
    }

    public function down(): void
    {
        // The editorial order should be retained if this migration is rolled back.
    }
};
