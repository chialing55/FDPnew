<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('mysql5')->hasTable('splist')
            || ! Schema::connection('mysql5')->hasTable('splist2')) {
            return;
        }

        $db = DB::connection('mysql5');
        $columns = Schema::connection('mysql5')->getColumnListing('splist');

        $missingSpecies = $db->table('splist2')
            ->whereIn('spcode', ['DERRTR', 'FICUFO'])
            ->get($columns)
            ->map(fn ($row) => (array) $row)
            ->all();

        if ($missingSpecies !== []) {
            $db->table('splist')->upsert($missingSpecies, ['spcode'], $columns);
        }

        $db->table('splist')
            ->where('spcode', 'POGOCR')
            ->update([
                'index' => '金絲草',
                'chname' => '金絲草',
            ]);
    }

    public function down(): void
    {
        // Data consolidation is intentionally retained for later curation.
    }
};
