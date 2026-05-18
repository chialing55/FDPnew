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

        $this->copyTable('slrecord2', 'slrecord_' . $suffix);
        $this->copyTable('slcov1', 'slcov_' . $suffix);
        $this->copyTable('slroll1', 'slroll_' . $suffix);
        $this->copyTable('seedling', 'seedling_' . $suffix);
        $this->copyTable('base', 'base_' . $suffix);
        $this->copyTable('seedling_records', 'seedling_records_' . $suffix);
        $this->copyTable('seedling_stems', 'seedling_stems_' . $suffix);
        $this->copyTable('seedling_individuals', 'seedling_individuals_' . $suffix);

        foreach (['slrecord', 'slrecord1', 'slrecord2', 'slcov1', 'slcov2', 'slroll1', 'slroll2'] as $table) {
            if (Schema::connection('mysql3')->hasTable($table)) {
                $this->truncateTable($table);
            }
        }

        $this->slmaxcensus=FsSeedlingRecord::max('census');
        $this->nowcensus=FsSeedlingSlrecord1::max('census');
        $this->cleanupnote = '已完成資料表備份與工作表清空：' . $suffix;
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

    private function syncSeedlingRecord(array $slrecord): void
    {
        $now = date('Y-m-d H:i:s');
        $updatedId = $this->user ?: ($slrecord['updated_id'] ?? null);
        $isSprout = strtoupper((string) ($slrecord['sprout'] ?? '')) === 'TRUE';

        if (!$isSprout) {
            DB::connection('mysql3')->table('seedling_individuals')->updateOrInsert(
                ['mtag' => $slrecord['mtag']],
                [
                    'trap' => $slrecord['trap'],
                    'plot' => $slrecord['plot'],
                    'x' => $slrecord['x'] === '' ? null : $slrecord['x'],
                    'y' => $slrecord['y'] === '' ? null : $slrecord['y'],
                    'csp' => $slrecord['csp'] ?? null,
                    'updated_id' => $updatedId,
                    'updated_at' => $now,
                ]
            );
        }

        DB::connection('mysql3')->table('seedling_stems')->updateOrInsert(
            ['tag' => $slrecord['tag']],
            [
                'mtag' => $slrecord['mtag'],
                'branch' => $this->branchFromTag((string) $slrecord['tag']),
                'ind' => $slrecord['ind'] === '' ? null : $slrecord['ind'],
                'sprout' => $slrecord['sprout'] ?? null,
                'updated_id' => $updatedId,
                'updated_at' => $now,
            ]
        );

        DB::connection('mysql3')->table('seedling_records')->updateOrInsert(
            [
                'census' => $slrecord['census'],
                'tag' => $slrecord['tag'],
            ],
            [
                'year' => $slrecord['year'],
                'month' => $slrecord['month'],
                'date' => $this->dateForRecord($slrecord['date'] ?? null),
                'ht' => $slrecord['ht'] === '' ? null : $slrecord['ht'],
                'cotno' => $slrecord['cotno'] === '' ? null : $slrecord['cotno'],
                'leafno' => $slrecord['leafno'] === '' ? null : $slrecord['leafno'],
                'recruit' => $slrecord['recruit'] ?? null,
                'status' => $slrecord['status'] ?? null,
                'note' => $slrecord['note'] ?? null,
                'updated_id' => $updatedId,
                'updated_at' => $now,
            ]
        );
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
                if (is_null($slcov[$covkey[$i]])){$slcov[$covkey[$i]]='';}
                $add[$covkey[$i]]=$slcov[$covkey[$i]];
            }

           $insert=FsSeedlingCov::insert($add);
        }        


        $this->importnote="資料已匯入完成";
        $this->slmaxcensus=FsSeedlingRecord::max('census');
        $this->nowcensus=FsSeedlingSlrecord1::max('census');


    }

    public function render()
    {
        return view('livewire.fushan.seedling-import');
    }
}
