<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

use App\Models\FsSeedlingCov;
use App\Models\FsSeedlingRecord;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;

//將資料匯入小苗大表

class SeedlingImport extends Component
{
    public $slmaxcensus;
    public $nowcensus;
    public $importnote;
    public $cleanupnote;
    public $user;
    public $site;

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function truncateTable(string $table): void
    {
        DB::connection('mysql3')->statement('TRUNCATE TABLE ' . $this->quoteIdentifier($table));
    }

    private function activeRowsWhereClause(string $table, ?string $alias = null): ?string
    {
        if (!Schema::connection('mysql3')->hasColumn($table, 'deleted_at')) {
            return null;
        }

        $prefix = $alias !== null ? $alias . '.' : '';

        return $prefix . $this->quoteIdentifier('deleted_at') . ' IS NULL';
    }

    private function copyTable(string $sourceTable, string $targetTable): void
    {
        if (!Schema::connection('mysql3')->hasTable($sourceTable)) {
            return;
        }

        if (!Schema::connection('mysql3')->hasTable($targetTable)) {
            DB::connection('mysql3')->statement(
                'CREATE TABLE ' . $this->quoteIdentifier($targetTable) . ' LIKE ' . $this->quoteIdentifier($sourceTable)
            );
        } else {
            $this->truncateTable($targetTable);
        }

        DB::connection('mysql3')->statement(
            'INSERT INTO ' . $this->quoteIdentifier($targetTable) . ' SELECT * FROM ' . $this->quoteIdentifier($sourceTable)
        );
    }

    private function rebuildSeedlingAnalysisTable(): void
    {
        foreach (['seedling', 'seedling_records', 'seedling_stems', 'seedling_individuals'] as $table) {
            if (!Schema::connection('mysql3')->hasTable($table)) {
                throw new \RuntimeException('找不到資料表：' . $table);
            }
        }

        $seedlingColumns = Schema::connection('mysql3')->getColumnListing('seedling');
        $recordColumns = Schema::connection('mysql3')->getColumnListing('seedling_records');
        $stemColumns = Schema::connection('mysql3')->getColumnListing('seedling_stems');
        $individualColumns = Schema::connection('mysql3')->getColumnListing('seedling_individuals');

        $columnSources = [];
        foreach ($recordColumns as $column) {
            $columnSources[$column] = 'r';
        }
        foreach ($stemColumns as $column) {
            $columnSources[$column] ??= 'st';
        }
        foreach ($individualColumns as $column) {
            $columnSources[$column] ??= 'i';
        }

        $insertColumns = [];
        $selectColumns = [];
        foreach ($seedlingColumns as $column) {
            if ($column === 'id') {
                continue;
            }

            $source = $columnSources[$column] ?? null;
            if (!$source) {
                continue;
            }

            $quotedColumn = $this->quoteIdentifier($column);
            $insertColumns[] = $quotedColumn;
            $selectColumns[] = $source . '.' . $quotedColumn;
        }

        if (empty($insertColumns)) {
            throw new \RuntimeException('seedling 分析表沒有可重建的共同欄位。');
        }

        $whereClauses = array_filter([
            $this->activeRowsWhereClause('seedling_records', 'r'),
            $this->activeRowsWhereClause('seedling_stems', 'st'),
            $this->activeRowsWhereClause('seedling_individuals', 'i'),
        ]);
        $whereSql = $whereClauses === [] ? '' : 'WHERE ' . implode(' AND ', $whereClauses) . ' ';

        $this->truncateTable('seedling');

        DB::connection('mysql3')->statement(
            'INSERT INTO ' . $this->quoteIdentifier('seedling') . ' (' . implode(', ', $insertColumns) . ') ' .
            'SELECT ' . implode(', ', $selectColumns) . ' ' .
            'FROM ' . $this->quoteIdentifier('seedling_records') . ' r ' .
            'JOIN ' . $this->quoteIdentifier('seedling_stems') . ' st ON r.' . $this->quoteIdentifier('tag') . ' = st.' . $this->quoteIdentifier('tag') . ' ' .
            'JOIN ' . $this->quoteIdentifier('seedling_individuals') . ' i ON st.' . $this->quoteIdentifier('mtag') . ' = i.' . $this->quoteIdentifier('mtag') . ' ' .
            $whereSql .
            'ORDER BY r.' . $this->quoteIdentifier('census') . ', i.' . $this->quoteIdentifier('trap') . ', i.' . $this->quoteIdentifier('plot') . ', st.' . $this->quoteIdentifier('tag')
        );
    }

    public function mount($user = null, $site = null){
        abort_unless(Auth::user()?->is_admin, 403);

        $this->user = $user;
        $this->site = $site;

        $this->slmaxcensus=FsSeedlingRecord::max('census');
        $this->nowcensus=FsSeedlingSlrecord1::max('census');
        

    }

    public function cleanupWorkTables()
    {
        $record = FsSeedlingSlrecord1::first() ?: FsSeedlingSlrecord2::first();

        if (!$record) {
            $this->cleanupnote = '目前沒有可整理的調查工作表資料。';
            return;
        }

        $year = (string) ($record->year ?: date('Y'));
        $month = str_pad((string) ($record->month ?: date('m')), 2, '0', STR_PAD_LEFT);
        $suffix = $year . $month;

        try {
            DB::connection('mysql3')->transaction(function () use ($suffix) {
                $this->copyTable('slrecord2', 'slrecord_' . $suffix);
                $this->copyTable('slcov1', 'slcov_' . $suffix);
                $this->copyTable('slroll1', 'slroll_' . $suffix);
                $this->rebuildSeedlingAnalysisTable();
                $this->copyTable('seedling', 'seedling_' . $suffix);

                foreach (['slrecord', 'slrecord1', 'slrecord2', 'slcov1', 'slcov2', 'slroll1', 'slroll2'] as $table) {
                    if (Schema::connection('mysql3')->hasTable($table)) {
                        $this->truncateTable($table);
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->cleanupnote = '資料表整理失敗：' . $e->getMessage();
            return;
        }

        $this->slmaxcensus=FsSeedlingRecord::max('census');
        $this->nowcensus=FsSeedlingSlrecord1::max('census');
        $this->cleanupnote = '已重建 seedling 分析表、完成 seedling_' . $suffix . ' 備份，並清空工作表。';
    }

    private function branchFromTag(string $tag): int
    {
        $parts = explode('.', $tag, 2);

        return isset($parts[1]) ? (int) $parts[1] : 0;
    }

    private function dateForRecord(?string $date): ?string
    {
        $date = trim((string) $date);

        return $date === '' || $date === '0000-00-00' ? null : $date;
    }

    private function nullableValue(array $row, string $key)
    {
        $value = $row[$key] ?? null;

        return $value === '' ? null : $value;
    }

    private function syncSeedlingRecord(array $slrecord): void
    {
        $now = date('Y-m-d H:i:s');
        $updatedId = $this->user ?: ($slrecord['updated_id'] ?? null);

        $individual = DB::connection('mysql3')
            ->table('seedling_individuals')
            ->where('mtag', $slrecord['mtag'])
            ->first();

        if ($individual) {
            $individualUpdate = [];
            foreach (['x', 'y'] as $field) {
                $value = $this->nullableValue($slrecord, $field);
                if ((string) ($individual->{$field} ?? '') !== (string) ($value ?? '')) {
                    $individualUpdate[$field] = $value;
                }
            }

            if (!empty($individualUpdate)) {
                $individualUpdate['updated_id'] = $updatedId;
                $individualUpdate['updated_at'] = $now;

                DB::connection('mysql3')
                    ->table('seedling_individuals')
                    ->where('mtag', $slrecord['mtag'])
                    ->update($individualUpdate);
            }
        } else {
            DB::connection('mysql3')
                ->table('seedling_individuals')
                ->insert([
                    'mtag' => $slrecord['mtag'],
                    'trap' => $slrecord['trap'],
                    'plot' => $slrecord['plot'],
                    'x' => $this->nullableValue($slrecord, 'x'),
                    'y' => $this->nullableValue($slrecord, 'y'),
                    'csp' => $slrecord['csp'] ?? null,
                    'updated_id' => $updatedId,
                    'created_at' => $now,
                ]);
        }

        $hasStem = DB::connection('mysql3')
            ->table('seedling_stems')
            ->where('tag', $slrecord['tag'])
            ->exists();

        if (!$hasStem) {
            DB::connection('mysql3')
                ->table('seedling_stems')
                ->insert([
                    'tag' => $slrecord['tag'],
                    'mtag' => $slrecord['mtag'],
                    'branch' => $this->branchFromTag((string) $slrecord['tag']),
                    'ind' => $this->nullableValue($slrecord, 'ind'),
                    'sprout' => $slrecord['sprout'] ?? null,
                    'updated_id' => $updatedId,
                    'created_at' => $now,
                ]);
        }

        $hasRecord = DB::connection('mysql3')
            ->table('seedling_records')
            ->where('census', $slrecord['census'])
            ->where('tag', $slrecord['tag'])
            ->exists();

        if (!$hasRecord) {
            DB::connection('mysql3')
                ->table('seedling_records')
                ->insert([
                    'census' => $slrecord['census'],
                    'tag' => $slrecord['tag'],
                    'year' => $slrecord['year'],
                    'month' => $slrecord['month'],
                    'date' => $this->dateForRecord($slrecord['date'] ?? null),
                    'ht' => $this->nullableValue($slrecord, 'ht'),
                    'cotno' => $this->nullableValue($slrecord, 'cotno'),
                    'leafno' => $this->nullableValue($slrecord, 'leafno'),
                    'recruit' => $slrecord['recruit'] ?? null,
                    'status' => $slrecord['status'] ?? null,
                    'note' => $slrecord['note'] ?? null,
                    'created_at' => $now,
                    'updated_id' => $updatedId,
                ]);
        }
    }


    public function import(){
        if (empty($this->nowcensus)) {
            $this->importnote = '目前沒有可匯入的大表資料。';
            return;
        }

        if ((string) $this->slmaxcensus === (string) $this->nowcensus) {
            $this->importnote = '第 ' . $this->nowcensus . ' 次調查資料已匯入，未重複匯入。';
            return;
        }

        $s_slrecord=FsSeedlingSlrecord1::all()->toArray();
        if (empty($s_slrecord)) {
            $this->importnote = 'slrecord1 目前沒有可匯入資料。';
            return;
        }

        $censusY=$s_slrecord[0]['year'];
        $censusM=str_pad($s_slrecord[0]['month'], 2, '0', STR_PAD_LEFT);

        DB::connection('mysql3')->transaction(function () use ($s_slrecord) {
            foreach($s_slrecord as $slrecord){
                $this->syncSeedlingRecord($slrecord);
            }

//cov
            $covkey=Schema::connection('mysql3')->getColumnListing('seedling_cov');
            $s_slcov=FsSeedlingSlcov1::all()->toArray();

            foreach($s_slcov as $slcov){
                $add=[];
                $slcov['id']='0';
                $slcov['ht']='0';

                for($i=0;$i<count($covkey);$i++){
                    if (is_null($slcov[$covkey[$i]] ?? null)){$slcov[$covkey[$i]]='';}
                    $add[$covkey[$i]]=$slcov[$covkey[$i]];
                }

               FsSeedlingCov::insert($add);
            }
        });


        $this->importnote="資料已匯入完成：小苗資料已寫入 seedling_individuals、seedling_stems、seedling_records；覆蓋度資料已寫入 seedling_cov。";
        $this->slmaxcensus=FsSeedlingRecord::max('census');
        $this->nowcensus=FsSeedlingSlrecord1::max('census');


    }

    public function render()
    {
        return view('livewire.fushan.seedling-import');
    }
}
