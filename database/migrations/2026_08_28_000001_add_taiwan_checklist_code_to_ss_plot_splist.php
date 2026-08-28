<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('mysql5');

        if (! $schema->hasColumn('splist', 'code')) {
            $schema->table('splist', function (Blueprint $table): void {
                $table->string('code', 50)->nullable()->after('spcode')->index();
            });
        }

        $checklist = DB::connection('plant_catalog')
            ->table('taiwan_checklist')
            ->get(['spcode', 'spcode_current', 'canonical_name', 'chname']);

        $byScientificName = $checklist
            ->filter(fn ($row) => trim((string) $row->canonical_name) !== '')
            ->groupBy(fn ($row) => trim((string) $row->canonical_name));
        $byChineseName = $checklist
            ->filter(fn ($row) => trim((string) $row->chname) !== '')
            ->groupBy(fn ($row) => trim((string) $row->chname));

        $species = DB::connection('mysql5')
            ->table('splist')
            ->where(function ($query): void {
                $query->whereNull('code')->orWhere('code', '');
            })
            ->get(['spcode', 'index', 'spname']);

        foreach ($species as $row) {
            $currentCodes = $this->currentCodes(
                $byScientificName->get(trim((string) $row->spname), collect())
            );

            if ($currentCodes->count() !== 1) {
                $currentCodes = $this->currentCodes(
                    $byChineseName->get(trim((string) $row->index), collect())
                );
            }

            if ($currentCodes->count() !== 1) {
                continue;
            }

            DB::connection('mysql5')
                ->table('splist')
                ->where('spcode', $row->spcode)
                ->where(function ($query): void {
                    $query->whereNull('code')->orWhere('code', '');
                })
                ->update(['code' => $currentCodes->first()]);
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('mysql5');

        if ($schema->hasColumn('splist', 'code')) {
            $schema->table('splist', function (Blueprint $table): void {
                $table->dropColumn('code');
            });
        }
    }

    private function currentCodes($rows)
    {
        return collect($rows)
            ->map(fn ($row) => trim((string) ($row->spcode_current ?: $row->spcode)))
            ->filter()
            ->unique()
            ->values();
    }
};
