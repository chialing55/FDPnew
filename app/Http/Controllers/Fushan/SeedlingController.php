<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Models\FsSeedlingCov;
use App\Models\FsSeedlingRecord;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;
use Symfony\Component\HttpFoundation\StreamedResponse;

//產生紀錄紙資料表
//分配網址到各個頁面

class SeedlingController extends Controller
{
    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function truncateTable(string $table): void
    {
        DB::connection('mysql3')->statement('TRUNCATE TABLE ' . $this->quoteIdentifier($table));
    }

    private function nextSurveyYearMonth(int $maxCensus): array
    {
        $latest = FsSeedlingRecord::query()
            ->where('census', $maxCensus)
            ->select('year', 'month')
            ->first();

        $latestYear = (int) ($latest->year ?? 0);
        $latestMonth = (int) ($latest->month ?? 0);

        if ($latestYear > 0 && $latestMonth === 2) {
            return [$latestYear, 8];
        }

        if ($latestYear > 0 && $latestMonth === 8) {
            return [$latestYear + 1, 2];
        }

        $nextMonth = (($maxCensus + 1) % 2 === 0) ? 2 : 8;

        return [(int) date('Y'), $nextMonth];
    }

    private function insertTableFromTable(string $targetTable, string $sourceTable): void
    {
        $targetColumns = Schema::connection('mysql3')->getColumnListing($targetTable);
        $sourceColumns = Schema::connection('mysql3')->getColumnListing($sourceTable);
        $sourceColumnMap = array_flip($sourceColumns);

        $insertColumns = [];
        $selectColumns = [];

        foreach ($targetColumns as $column) {
            $insertColumns[] = $this->quoteIdentifier($column);

            if (isset($sourceColumnMap[$column])) {
                $selectColumns[] = $this->quoteIdentifier($sourceTable) . '.' . $this->quoteIdentifier($column);
            } else {
                $selectColumns[] = "''";
            }
        }

        DB::connection('mysql3')->statement(
            'INSERT INTO ' . $this->quoteIdentifier($targetTable) .
            ' (' . implode(', ', $insertColumns) . ') SELECT ' . implode(', ', $selectColumns) .
            ' FROM ' . $this->quoteIdentifier($sourceTable)
        );
    }

    private function seedlingWorkSelectSql(?array $columns = null): string
    {
        $columns ??= [
            'id',
            'census',
            'year',
            'month',
            'date',
            'trap',
            'plot',
            'tag',
            'mtag',
            'csp',
            'ht',
            'cotno',
            'leafno',
            'ind',
            'note',
            'recruit',
            'status',
            'sprout',
            'x',
            'y',
        ];

        $columnMap = [
            'id' => 'r.id',
            'census' => 'r.census',
            'year' => 'r.year',
            'month' => 'r.month',
            'date' => "COALESCE(DATE_FORMAT(r.date, '%Y-%m-%d'), '0000-00-00')",
            'trap' => 'i.trap',
            'plot' => 'i.plot',
            'tag' => 'st.tag',
            'mtag' => 'st.mtag',
            'csp' => 'i.csp',
            'ht' => 'r.ht',
            'cotno' => 'r.cotno',
            'leafno' => 'r.leafno',
            'ind' => 'st.ind',
            'note' => "COALESCE(r.note, '')",
            'recruit' => "COALESCE(r.recruit, '')",
            'status' => "COALESCE(r.status, '')",
            'sprout' => "COALESCE(st.sprout, '')",
            'x' => 'i.x',
            'y' => 'i.y',
            'updated_at' => "''",
            'updated_id' => "COALESCE(r.updated_id, '')",
            'alternote' => "''",
        ];

        $selectColumns = array_map(function ($column) use ($columnMap) {
            $expression = $columnMap[$column] ?? "''";

            return $expression . ' AS ' . $this->quoteIdentifier($column);
        }, $columns);

        return 'SELECT ' . implode(', ', $selectColumns) .
            ' FROM seedling_records r' .
            ' JOIN seedling_stems st ON r.tag = st.tag' .
            ' JOIN seedling_individuals i ON st.mtag = i.mtag' .
            " WHERE r.census LIKE ? AND (r.status LIKE 'A' OR r.status LIKE 'N')" .
            ' ORDER BY i.trap, i.plot, st.tag';
    }

    private function prepareSeedlingWorkTables(int $maxCensus): void
    {
        if (!Schema::connection('mysql3')->hasTable('slrecord')) {
            DB::connection('mysql3')->statement(
                'CREATE TABLE slrecord ENGINE = MyISAM AS ' . $this->seedlingWorkSelectSql(),
                [$maxCensus]
            );
            Schema::connection('mysql3')->table('slrecord', function ($table) {
                $table->string('updated_at');
            });
        } else {
            $this->truncateTable('slrecord');

            if (!Schema::connection('mysql3')->hasColumn('slrecord', 'updated_at')) {
                Schema::connection('mysql3')->table('slrecord', function ($table) {
                    $table->string('updated_at');
                });
            }

            $insertColumns = Schema::connection('mysql3')->getColumnListing('slrecord');

            DB::connection('mysql3')->statement(
                'INSERT INTO slrecord (' . implode(', ', array_map([$this, 'quoteIdentifier'], $insertColumns)) . ') ' .
                $this->seedlingWorkSelectSql($insertColumns),
                [$maxCensus]
            );
        }

        if (!Schema::connection('mysql3')->hasColumn('slrecord', 'updated_at')) {
            Schema::connection('mysql3')->table('slrecord', function ($table) {
                $table->string('updated_at');
            });
        }

        DB::connection('mysql3')->statement("DELETE FROM slrecord WHERE ht = '-7' AND sprout = 'True'");
        DB::connection('mysql3')->statement("DELETE FROM slrecord WHERE ht = '-2' AND sprout = 'True'");

        FsSeedlingSlrecord::query()->update(['id' => '0', 'census' => $maxCensus + 1, 'year' => '0', 'month' => '0', 'date' => '0000-00-00']);
        FsSeedlingSlrecord::where('recruit', 'R')->update(['recruit' => 'O']);
        FsSeedlingSlrecord::where('status', 'N')->update(['recruit' => 'N']);

        if (!Schema::connection('mysql3')->hasTable('slrecord1')) {
            DB::connection('mysql3')->statement("CREATE  TABLE  `fs_seedling`.`slrecord1` (  `id` int( 11  )  NOT  NULL AUTO_INCREMENT,  `census` int( 3  )  NOT  NULL ,  `year` int( 4  )  NOT  NULL ,  `month` int( 2  )  NOT  NULL ,  `date` char( 10  )  NOT  NULL ,  `trap` int( 3  )  NOT  NULL ,  `plot` int( 1  )  NOT  NULL ,  `tag` char( 12  )  NOT  NULL ,  `mtag` char( 12  )  NOT  NULL ,  `csp` char( 20  )  NOT  NULL ,    `ht` float ,  `cotno` int( 2  )  , `leafno` int( 2  )   ,  `ind` int( 3  )  NOT  NULL default  '1',  `note` varchar( 255  )  NOT  NULL ,`recruit` char( 2  )  NOT  NULL ,`status` char( 2  )  NOT  NULL ,`sprout` char( 5  )  NOT  NULL , `x` int( 3  )  NOT  NULL , `y` int( 3  )  NOT  NULL , `updated_at` varchar(255) , `alternote` VARCHAR( 255 ) NOT NULL, `updated_id` char(20) not null, PRIMARY KEY (  `id` ) , index(  trap  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8");
        } else {
            $this->truncateTable('slrecord1');
        }

        if (!Schema::connection('mysql3')->hasColumn('slrecord1', 'alternote')) {
            DB::connection('mysql3')->statement("ALTER TABLE `slrecord1` ADD `alternote` VARCHAR(255) NOT NULL");
        }

        if (!Schema::connection('mysql3')->hasColumn('slrecord1', 'updated_id')) {
            DB::connection('mysql3')->statement("ALTER TABLE `slrecord1` ADD `updated_id` char(20) NOT NULL");
        }

        $this->insertTableFromTable('slrecord1', 'slrecord');

        [$year, $month] = $this->nextSurveyYearMonth($maxCensus);

        FsSeedlingSlrecord1::query()->update(['year' => $year, 'month' => $month]);
        FsSeedlingSlrecord1::where('ht', '>=', '-1')->update(['ht' => NULL, 'cotno' => NULL, 'leafno' => NULL]);
        FsSeedlingSlrecord1::query()->update(['updated_at' => '', 'alternote' => '', 'updated_id' => '']);

        if (!Schema::connection('mysql3')->hasTable('slrecord2')) {
            DB::connection('mysql3')->statement("CREATE TABLE slrecord2 LIKE slrecord1");
        } else {
            $this->truncateTable('slrecord2');
        }

        $this->insertTableFromTable('slrecord2', 'slrecord1');

        if (!Schema::connection('mysql3')->hasTable('slcov1')) {
            DB::connection('mysql3')->statement("CREATE TABLE `fs_seedling`.`slcov1` ( `id` int (11) NOT  NULL AUTO_INCREMENT, `year` int( 4  ) ,  `month` int( 2  ) ,  `date` char( 10  ) ,  `trap` int( 3  ),  `plot` int( 1  ) , `cov` float,`canopy` char (2) ,  `note` varchar( 255  ), `updated_at` varchar(255), `updated_id` char(20), PRIMARY KEY (  `id` ) , index(  trap  ) )ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8");
        } else {
            $this->truncateTable('slcov1');
        }

        for ($x = 1; $x < 108; $x++) {
            if ($x != 42) {
                for ($y = 1; $y < 4; $y++) {
                    DB::connection('mysql3')->statement("INSERT INTO fs_seedling.slcov1 (trap, plot) values (?, ?)", [$x, $y]);
                }
            }
        }

        DB::connection('mysql3')->statement("DELETE FROM fs_seedling.slcov1 WHERE trap = 33 AND plot = 3");
        FsSeedlingSlcov1::query()->update(['year' => $year, 'month' => $month, 'date' => '0000-00-00', 'updated_at' => '']);

        if (!Schema::connection('mysql3')->hasTable('slcov2')) {
            DB::connection('mysql3')->statement("CREATE TABLE slcov2 LIKE slcov1");
        } else {
            $this->truncateTable('slcov2');
        }

        $this->insertTableFromTable('slcov2', 'slcov1');

        if (!Schema::connection('mysql3')->hasTable('slroll1')) {
            DB::connection('mysql3')->statement("CREATE TABLE `fs_seedling`.`slroll1` ( `id` int (11) NOT  NULL AUTO_INCREMENT,  `year` int( 4  )  NOT  NULL ,  `month` int( 2  )  NOT  NULL , `date` char( 10  )  NOT  NULL ,  `trap` int( 3  )  NOT  NULL ,  `plot` int( 1  )  NOT  NULL , `tag` char(12) not null,  `note` varchar( 255  ), `updated_at` varchar(255) not null, `updated_id` char(20) not null,PRIMARY KEY (  `id` ) , index(  trap  ) )ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8");
        } else {
            $this->truncateTable('slroll1');
        }

        if (!Schema::connection('mysql3')->hasTable('slroll2')) {
            DB::connection('mysql3')->statement("CREATE TABLE slroll2 LIKE slroll1");
        } else {
            $this->truncateTable('slroll2');
        }
    }

    private function shouldPrepareSeedlingWorkTables(int $maxCensus): bool
    {
        foreach (['slrecord', 'slrecord1', 'slrecord2', 'slcov1', 'slcov2', 'slroll1', 'slroll2'] as $table) {
            if (!Schema::connection('mysql3')->hasTable($table)) {
                return true;
            }
        }

        $slrecordMaxCensus = FsSeedlingSlrecord::max('census');

        if ($slrecordMaxCensus === null) {
            return true;
        }

        return (int) $slrecordMaxCensus < $maxCensus;
    }


    public function seedling(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');
        //最近一次調查
        $maxCensus = FsSeedlingRecord::max('census');


        if ($this->shouldPrepareSeedlingWorkTables((int) $maxCensus)) {
            $this->prepareSeedlingWorkTables((int) $maxCensus);
        }

        // $slrecord=FsSeedlingSlrecord::all();
        // for($i=0; $i<count($slrecord);$i++){
        //     $plot=$slrecord[$i]['trap'];
        //     $slrecorddata[$plot][]=$slrecord[$i];
        // }
        // echo count($slrecord)/500;
        // print_r($slrecord[0])

        $slrecord = FsSeedlingSlrecord::first();
        // $year=$slrecord[0]['year'];
        $census = $slrecord['census'];
        // print_r($census);


        return view('pages/fushan/seedling_doc', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            // 'maxcensus' => $maxCensus,
            'census' => $census,
            // 'entry1note' => $entry1note,
            // 'entry2note' => $entry2note
            // 'slrecord' => $slrecord,
            // 'slrecord1' => $slrecord1,
            // 'slrecord2' => $slrecord2
            // 'slcov1' => $slcov1,
            // 'slcov2' => $slcov2,
            // 'slroll1' => $slroll1,
            // 'slroll2' => $slroll2
        ]);
    }


    public function entry(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');
        $entry = $request->route('entry');

        $slrecord = FsSeedlingSlrecord::first();
        // $year=$slrecord[0]['year'];
        $census = $slrecord['census'];

        return view('pages/fushan/seedling_entry', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'entry' => $entry,
            'census' => $census,

        ]);
    }

    public function compare(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        $slrecord = FsSeedlingSlrecord::first();
        // $year=$slrecord[0]['year'];
        $census = $slrecord['census'];

        return view('pages/fushan/seedling_compare', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'census' => $census,

        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $site = $request->route("site");

        $slrecord = FsSeedlingSlrecord::first();
        $census = $slrecord["census"];

        return view("pages/fushan/seedling_update", [
            "site" => $site,
            "project" => "小苗",
            "user" => $user->account ?? $user->name,
            "census" => $census,
        ]);
    }

    public function import(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');

        $slrecord = FsSeedlingSlrecord::first();
        // $year=$slrecord[0]['year'];
        $census = $slrecord['census'];

        return view('pages/fushan/seedling_import', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'census' => $census,

        ]);
    }


    public function note(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/seedling_note', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,

        ]);
    }

    public function dataviewer(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/seedling_dataviewer', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,

        ]);
    }

    public function download(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');
        $latestSeedling = DB::connection('mysql3')
            ->table('seedling')
            ->select('year', 'month')
            ->orderByDesc('census')
            ->first();

        $latestSeedlingYm = $latestSeedling
            ? sprintf('%04d-%02d', (int) $latestSeedling->year, (int) $latestSeedling->month)
            : '尚無資料';

        return view('pages/fushan/seedling_download', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'latestSeedlingYm' => $latestSeedlingYm,
        ]);
    }

    public function downloadSeedling(): StreamedResponse
    {
        $columns = Schema::connection('mysql3')->getColumnListing('seedling');
        $filename = 'seedling_latest_' . now()->format('Ymd') . '.txt';

        return $this->streamTxt($filename, $columns, function ($handle) use ($columns) {
            $query = DB::connection('mysql3')->table('seedling')->select($columns);

            foreach (['census', 'trap', 'plot', 'tag'] as $column) {
                if (in_array($column, $columns, true)) {
                    $query->orderBy($column);
                }
            }

            $query->chunk(1000, function ($rows) use ($handle, $columns) {
                foreach ($rows as $row) {
                    fputcsv($handle, array_map(fn ($column) => $row->{$column}, $columns), "	");
                }
            });
        });
    }

    private function streamTxt(string $filename, array $headers, callable $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writeRows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, "	");
            $writeRows($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
