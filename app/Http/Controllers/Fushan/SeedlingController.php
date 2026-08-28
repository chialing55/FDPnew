<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ManagesResearchOutputAssets;

use App\Models\FsSeedlingCov;
use App\Models\FsSeedlingRecord;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;
use App\Models\PlantCatalog\SiteSpecies;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

//產生紀錄紙資料表
//分配網址到各個頁面

class SeedlingController extends Controller
{
    use ManagesResearchOutputAssets;

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
            ' WHERE r.deleted_at IS NULL' .
            ' AND st.deleted_at IS NULL' .
            ' AND i.deleted_at IS NULL' .
            " AND r.census LIKE ? AND (r.status LIKE 'A' OR r.status LIKE 'N')" .
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
        $census = $slrecord["census"];
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


    public function researchOutput(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        $dateOptions = $this->seedlingCensusOptions();
        $censusValues = $dateOptions->pluck('census')->map(fn ($census) => (string) $census)->all();
        $defaultStartCensus = $dateOptions->first()['census'] ?? null;
        $defaultEndCensus = $dateOptions->last()['census'] ?? null;
        $selectedStartCensus = $request->input('start_census', $defaultStartCensus);
        $selectedEndCensus = $request->input('end_census', $defaultEndCensus);

        if (! in_array((string) $selectedStartCensus, $censusValues, true)) {
            $selectedStartCensus = $defaultStartCensus;
        }

        if (! in_array((string) $selectedEndCensus, $censusValues, true)) {
            $selectedEndCensus = $defaultEndCensus;
        }

        if ($request->has('start_census') && $request->has('end_census')) {
            $this->forgetSeedlingResearchOutputSession($request);
        }

        return view('pages/fushan/seedling_research_output', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'dateOptions' => $dateOptions,
            'selectedStartCensus' => $selectedStartCensus,
            'selectedEndCensus' => $selectedEndCensus,
            'hasAppliedRange' => $request->has('start_census') && $request->has('end_census'),
            'itemUrls' => [
                'composition' => route('admin.fushan.seedling.research-output.item', ['item' => 'composition']),
                'survival-growth' => route('admin.fushan.seedling.research-output.item', ['item' => 'survival-growth']),
            ],
            'compositionDocxUrl' => route('admin.fushan.seedling.research-output.composition-docx'),
            'survivalGrowthDocxUrl' => route('admin.fushan.seedling.research-output.survival-growth-docx'),
            'clearSessionUrl' => route('admin.fushan.seedling.research-output.clear-session'),
        ]);
    }

    public function researchOutputItem(Request $request, string $item)
    {
        $startCensus = $request->input('start_census');
        $endCensus = $request->input('end_census');
        $validated = $this->validSeedlingResearchRange($startCensus, $endCensus);

        if ($validated === null) {
            return response()->json([
                'error' => '資料範圍不正確，請重新套用篩選。',
            ], 422);
        }

        [$minCensus, $maxCensus] = $validated;
        $cacheKey = $this->seedlingResearchOutputCacheKey($item, $minCensus, $maxCensus);
        $cached = $request->session()->get($cacheKey);

        if (is_array($cached) && isset($cached['html'])
            && $this->cachedResearchOutputHtmlHasAssets($request, (string) $cached['html'], 'seedling_research_output_assets_')) {
            return response()->json([
                'html' => $cached['html'],
                'cached' => true,
            ]);
        }

        if (is_array($cached) && isset($cached['html'])) {
            $request->session()->forget($cacheKey);
        }

        $viewData = [
            'item' => $item,
            'number' => fn ($value) => number_format((float) $value, 0),
            'decimal' => fn ($value, $precision = 1) => number_format((float) $value, $precision),
            'percent' => fn ($value) => number_format((float) $value, 1),
        ];

        if ($item === 'composition') {
            $compositionSummary = $this->seedlingCompositionSummary($minCensus, $maxCensus);
            $viewData['compositionSummary'] = $compositionSummary;
            $viewData['compositionAssets'] = $this->renderSeedlingCompositionAssets($request, $minCensus, $maxCensus, $compositionSummary);
        } elseif ($item === 'survival-growth') {
            $viewData['survivalGrowthSummary'] = $this->seedlingSurvivalGrowthSummary($minCensus, $maxCensus);
            $viewData['growthHistogramAssets'] = $this->renderSeedlingGrowthHistogramAssets($request, $minCensus, $maxCensus, $viewData['survivalGrowthSummary']);
            $viewData['survivalGrowthDocxUrl'] = route('admin.fushan.seedling.research-output.survival-growth-docx', ['start_census' => $minCensus, 'end_census' => $maxCensus]);
        } else {
            return response()->json(['error' => '未知的成果項目。'], 404);
        }

        $html = view('pages.fushan.partials.seedling_research_output_item', $viewData)->render();
        $request->session()->put($cacheKey, ['html' => $html]);

        return response()->json([
            'html' => $html,
            'cached' => false,
        ]);
    }

    public function downloadResearchOutputCompositionDocx(Request $request): StreamedResponse
    {
        $validated = $this->validSeedlingResearchRange(
            $request->input('start_census'),
            $request->input('end_census')
        );

        if ($validated === null) {
            abort(422, '資料範圍不正確，請重新套用篩選。');
        }

        [$minCensus, $maxCensus] = $validated;
        $summary = $this->seedlingCompositionSummary($minCensus, $maxCensus);
        $content = $this->buildSeedlingCompositionDocxContent($summary);
        $filename = "seedling-composition-{$minCensus}-{$maxCensus}.docx";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function downloadResearchOutputSurvivalGrowthDocx(Request $request): StreamedResponse
    {
        $validated = $this->validSeedlingResearchRange(
            $request->input('start_census'),
            $request->input('end_census')
        );

        if ($validated === null) {
            abort(422, '資料範圍不正確，請重新套用篩選。');
        }

        [$minCensus, $maxCensus] = $validated;
        $summary = $this->seedlingSurvivalGrowthSummary($minCensus, $maxCensus);
        $content = $this->buildSeedlingSurvivalGrowthDocxContent($summary);
        $filename = "seedling-survival-growth-{$minCensus}-{$maxCensus}.docx";

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function clearResearchOutputSession(Request $request)
    {
        $this->forgetSeedlingResearchOutputSession($request);

        return response()->noContent();
    }

    public function researchOutputAsset(Request $request, string $token, string $extension)
    {
        return $this->researchOutputAssetFromSession($request, $token, $extension, 'seedling_research_output_assets_');
    }

    private function forgetSeedlingResearchOutputSession(Request $request): void
    {
        $this->forgetResearchOutputSessionAssets(
            $request,
            'seedling_research_output.',
            'seedling_research_output_assets_',
            $this->seedlingResearchOutputTemporaryPrefixes(),
            ['seedling_research_output']
        );
    }

    private function validSeedlingResearchRange($startCensus, $endCensus): ?array
    {
        if ($startCensus === null || $endCensus === null) {
            return null;
        }

        $exists = FsSeedlingRecord::query()
            ->whereIn('census', [$startCensus, $endCensus])
            ->distinct()
            ->count('census');

        if ((string) $startCensus === (string) $endCensus) {
            return $exists === 1 ? [(int) $startCensus, (int) $endCensus] : null;
        }

        if ($exists < 2) {
            return null;
        }

        return [min((int) $startCensus, (int) $endCensus), max((int) $startCensus, (int) $endCensus)];
    }

    private function seedlingCensusOptions()
    {
        return FsSeedlingRecord::query()
            ->select('census', 'year', 'month')
            ->whereNotNull('census')
            ->whereNotNull('year')
            ->whereNotNull('month')
            ->groupBy('census', 'year', 'month')
            ->orderByDesc('census')
            ->get()
            ->map(function ($row) {
                $year = (int) $row->year;
                $month = (int) $row->month;

                return [
                    'census' => $row->census,
                    'label' => sprintf('census %s: %04d-%02d', $row->census, $year, $month),
                ];
            })
            ->values();
    }

    private function seedlingResearchOutputCacheKey(string $item, int $minCensus, int $maxCensus): string
    {
        return "seedling_research_output.v14.{$this->researchChartRuntimeVersion($item)}.{$minCensus}.{$maxCensus}.{$item}";
    }

    private function researchChartStyleVersion(): int
    {
        $stylePath = resource_path('scripts/research_chart_style.R');

        return file_exists($stylePath) ? filemtime($stylePath) : 0;
    }

    private function researchChartRuntimeVersion(string $item): string
    {
        $paths = [
            resource_path('scripts/research_chart_style.R'),
            resource_path('scripts/research_chart_runtime.R'),
        ];

        $paths = array_merge($paths, match ($item) {
            'composition' => [resource_path('scripts/seedling_composition.R')],
            'survival-growth' => [resource_path('scripts/seedling_growth_histograms.R')],
            default => [],
        });

        return substr(sha1(implode('|', array_map(
            fn ($path) => file_exists($path) ? $path . ':' . filemtime($path) : $path . ':missing',
            $paths
        ))), 0, 12);
    }

    private function seedlingResearchOutputTemporaryPrefixes(): array
    {
        return [
            sys_get_temp_dir() . '/seedling-composition-',
            sys_get_temp_dir() . '/seedling-growth-histogram-',
        ];
    }

    private function seedlingCompositionSummary(int $minCensus, int $maxCensus): array
    {
        $censusRows = FsSeedlingRecord::query()
            ->select('census', 'year', 'month')
            ->selectRaw('AVG(TO_DAYS(date)) as mean_day')
            ->whereBetween('census', [$minCensus, $maxCensus])
            ->whereNotNull('census')
            ->whereNotNull('year')
            ->whereNotNull('month')
            ->groupBy('census', 'year', 'month')
            ->orderBy('census')
            ->get();

        $surveys = [];

        foreach ($censusRows as $index => $censusRow) {
            $census = (int) $censusRow->census;
            $previousCensusRow = $this->previousSeedlingCensusRow($census);
            $aliveRows = $this->seedlingSpeciesCounts($census, "r.recruit IN ('O', 'R') AND r.status IN ('A', 'T', 'S')");
            $newRows = $this->seedlingSpeciesCounts($census, "r.recruit = 'R' AND r.status IN ('A', 'T', 'S')");
            $deadRows = $this->seedlingSpeciesCounts($census, "r.recruit IN ('O', 'R') AND r.status IN ('G', 'D', 'N')");
            $aliveTotal = collect($aliveRows)->sum('count');
            $newTotal = collect($newRows)->sum('count');
            $deadTotal = collect($deadRows)->sum('count');
            $speciesNames = collect($aliveRows)->pluck('csp')->filter()->values()->all();
            $taxonomy = $this->seedlingTaxonomyCounts($speciesNames);
            $topAlive = $this->topSeedlingSpeciesRows($aliveRows, 10);
            $topNewFive = collect($newRows)->take(5)->values()->all();
            $topDeadFive = collect($deadRows)->take(5)->values()->all();
            $topAliveTotal = collect($aliveRows)->take(10)->sum('count');
            $previousAliveRows = $previousCensusRow
                ? $this->seedlingSpeciesCounts((int) $previousCensusRow->census, "r.recruit IN ('O', 'R') AND r.status IN ('A', 'T', 'S')")
                : [];
            $speciesChange = $this->seedlingSpeciesChange($previousAliveRows, $aliveRows);

            $surveys[] = [
                'sequence' => $index + 1,
                'census' => $census,
                'year' => (int) $censusRow->year,
                'month' => (int) $censusRow->month,
                'survey_date_text' => $this->formatSeedlingSurveyMonth((int) $censusRow->year, (int) $censusRow->month),
                'survey_month_text' => $this->formatSeedlingMonth((int) $censusRow->year, (int) $censusRow->month),
                'previous_census' => $previousCensusRow?->census,
                'previous_month_text' => $previousCensusRow ? $this->formatSeedlingMonth((int) $previousCensusRow->year, (int) $previousCensusRow->month) : null,
                'family_count' => $taxonomy['family_count'],
                'genus_count' => $taxonomy['genus_count'],
                'species_count' => count($speciesNames),
                'alive_total' => $aliveTotal,
                'new_total' => $newTotal,
                'dead_total' => $deadTotal,
                'density' => $aliveTotal / 324,
                'top_alive' => $topAlive,
                'top_new_five' => $topNewFive,
                'top_dead_five' => $topDeadFive,
                'top_alive_percent' => $aliveTotal > 0 ? round($topAliveTotal / $aliveTotal * 100, 1) : 0,
                'new_top_percent' => $newTotal > 0 && isset($topNewFive[0]) ? round($topNewFive[0]['count'] / $newTotal * 100, 1) : 0,
                'species_change' => $speciesChange,
                'figure_rows' => $this->seedlingCompositionFigureRows($aliveRows, $newRows, $deadRows),
                'table' => [
                    'alive' => $topAlive,
                    'new' => $this->topSeedlingSpeciesRows($newRows, 10),
                    'dead' => $this->topSeedlingSpeciesRows($deadRows, 10),
                    'alive_total' => $aliveTotal,
                    'new_total' => $newTotal,
                    'dead_total' => $deadTotal,
                ],
            ];
        }

        return ['surveys' => $surveys];
    }

    private function previousSeedlingCensusRow(int $census): ?object
    {
        return FsSeedlingRecord::query()
            ->select('census', 'year', 'month')
            ->selectRaw('AVG(TO_DAYS(date)) as mean_day')
            ->where('census', '<', $census)
            ->whereNotNull('year')
            ->whereNotNull('month')
            ->groupBy('census', 'year', 'month')
            ->orderByDesc('census')
            ->first();
    }

    private function seedlingSpeciesCounts(int $census, string $whereRaw): array
    {
        return DB::connection('mysql3')
            ->table('seedling_records as r')
            ->join('seedling_stems as st', 'r.tag', '=', 'st.tag')
            ->join('seedling_individuals as i', 'st.mtag', '=', 'i.mtag')
            ->where('r.census', $census)
            ->whereRaw($whereRaw)
            ->whereNull('r.deleted_at')
            ->whereNull('st.deleted_at')
            ->whereNull('i.deleted_at')
            ->whereRaw("UPPER(COALESCE(st.sprout, '')) = 'FALSE'")
            ->whereNotNull('i.csp')
            ->where('i.csp', '!=', '')
            ->where('i.csp', '!=', 'UNK')
            ->where('i.csp', '!=', 'unk')
            ->select('i.csp')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('i.csp')
            ->havingRaw('total > 0')
            ->orderByDesc('total')
            ->orderBy('i.csp')
            ->get()
            ->map(fn ($row) => [
                'csp' => $row->csp,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    private function seedlingTaxonomyCounts(array $speciesNames): array
    {
        if (count($speciesNames) === 0) {
            return ['family_count' => 0, 'genus_count' => 0];
        }

        $rows = SiteSpecies::query()
            ->fushan()
            ->withChecklistTaxonomy()
            ->whereIn('csp', $speciesNames)
            ->select('checklist.family as apgfamily', 'checklist.genus')
            ->get();

        return [
            'family_count' => $rows->pluck('apgfamily')->filter()->unique()->count(),
            'genus_count' => $rows->pluck('genus')->filter()->unique()->count(),
        ];
    }

    private function topSeedlingSpeciesRows(array $rows, int $limit): array
    {
        $topRows = collect($rows)->take($limit)->values();
        $otherTotal = collect($rows)->slice($limit)->sum('count');

        if ($otherTotal > 0) {
            $topRows->push(['csp' => '其他物種', 'count' => (int) $otherTotal]);
        }

        return $topRows->all();
    }

    private function seedlingSpeciesChange(array $previousRows, array $currentRows): array
    {
        $previous = collect($previousRows)->pluck('csp')->filter()->values();
        $current = collect($currentRows)->pluck('csp')->filter()->values();
        $gained = $current->diff($previous)->values()->all();
        $lost = $previous->diff($current)->values()->all();

        return [
            'delta' => $current->count() - $previous->count(),
            'gained' => $gained,
            'lost' => $lost,
            'has_previous' => $previous->count() > 0,
        ];
    }


    private function seedlingSurvivalGrowthSummary(int $minCensus, int $maxCensus): array
    {
        $censusRows = FsSeedlingRecord::query()
            ->select('census', 'year', 'month')
            ->selectRaw('AVG(TO_DAYS(date)) as mean_day')
            ->whereBetween('census', [$minCensus, $maxCensus])
            ->whereNotNull('census')
            ->whereNotNull('year')
            ->whereNotNull('month')
            ->groupBy('census', 'year', 'month')
            ->orderBy('census')
            ->get();

        $intervals = [];

        foreach ($censusRows as $censusRow) {
            $currentCensus = (int) $censusRow->census;
            $previousCensusRow = $this->previousSeedlingCensusRow($currentCensus);

            if (! $previousCensusRow) {
                continue;
            }

            $previousCensus = (int) $previousCensusRow->census;
            $years = $this->seedlingIntervalYears($previousCensusRow, $censusRow);

            if ($years <= 0) {
                continue;
            }

            $rows = $this->seedlingSurvivalGrowthRows($previousCensus, $currentCensus, $years);
            $survivalRows = collect($rows)->sortBy([['survival_rate', 'desc'], ['previous_alive_total', 'desc'], ['csp', 'asc']])->values()->all();

            if (count($rows) === 0) {
                continue;
            }

            $topSurvival = collect($survivalRows)->filter(fn ($row) => abs($row['survival_rate'] - $survivalRows[0]['survival_rate']) < 0.0005)->values()->all();
            $remaining = collect($survivalRows)->slice(count($topSurvival))->values();
            $nextSurvival = $remaining->take(2)->values()->all();
            $rangeRows = $remaining->slice(count($nextSurvival))->values();
            $topRecruit = collect($rows)->sortByDesc('previous_recruit_total')->first();
            $topGrowth = collect($rows)->filter(fn ($row) => $row['growth_count'] > 0 && $row['growth_cm_rate'] !== null)->sortByDesc('growth_cm_rate')->take(3)->values()->all();

            $intervals[] = [
                'previous_census' => $previousCensus,
                'current_census' => $currentCensus,
                'previous_month_text' => $this->formatSeedlingMonth((int) $previousCensusRow->year, (int) $previousCensusRow->month),
                'current_month_text' => $this->formatSeedlingMonth((int) $censusRow->year, (int) $censusRow->month),
                'years' => $years,
                'rows' => collect($rows)->sortByDesc('previous_alive_total')->values()->all(),
                'top_survival' => $topSurvival,
                'next_survival' => $nextSurvival,
                'range_min' => $rangeRows->min('survival_rate'),
                'range_max' => $rangeRows->max('survival_rate'),
                'top_recruit' => $topRecruit,
                'top_growth' => $topGrowth,
            ];
        }

        return ['intervals' => $intervals];
    }

    private function seedlingSurvivalGrowthRows(int $previousCensus, int $currentCensus, float $years): array
    {
        $records = DB::connection('mysql3')
            ->table('seedling_records as prev')
            ->join('seedling_stems as st', 'prev.tag', '=', 'st.tag')
            ->join('seedling_individuals as i', 'st.mtag', '=', 'i.mtag')
            ->leftJoin('seedling_records as curr', function ($join) use ($currentCensus) {
                $join->on('curr.tag', '=', 'prev.tag')
                    ->where('curr.census', '=', $currentCensus)
                    ->whereNull('curr.deleted_at');
            })
            ->where('prev.census', $previousCensus)
            ->whereIn('prev.recruit', ['O', 'R'])
            ->whereIn('prev.status', ['A', 'T', 'S'])
            ->whereNull('prev.deleted_at')
            ->whereNull('st.deleted_at')
            ->whereNull('i.deleted_at')
            ->whereRaw("UPPER(COALESCE(st.sprout, '')) = 'FALSE'")
            ->whereNotNull('i.csp')
            ->where('i.csp', '!=', '')
            ->where('i.csp', '!=', 'UNK')
            ->where('i.csp', '!=', 'unk')
            ->select('i.csp', 'prev.tag', 'prev.recruit', 'prev.ht as previous_height', 'curr.ht as current_height', 'curr.recruit as current_recruit', 'curr.status as current_status')
            ->get();

        return $records
            ->groupBy('csp')
            ->map(function ($rows, $csp) use ($years) {
                $previousAlive = $rows->count();

                if ($previousAlive < 10) {
                    return null;
                }

                $survivedRows = $rows->filter(fn ($row) => in_array($row->current_recruit, ['O', 'R'], true) && in_array($row->current_status, ['A', 'T', 'S'], true));
                $survived = $survivedRows->count();
                $survivalRate = $previousAlive > 0 ? pow($survived / $previousAlive, 1 / $years) : 0;
                $growthValues = $survivedRows
                    ->map(function ($row) use ($years) {
                        if (! $this->hasValidSeedlingHeight($row->previous_height) || ! $this->hasValidSeedlingHeight($row->current_height)) {
                            return null;
                        }

                        return ((float) $row->current_height - (float) $row->previous_height) / $years;
                    })
                    ->filter(fn ($value) => $value !== null)
                    ->values()
                    ->all();
                $logGrowthValues = $survivedRows
                    ->map(function ($row) use ($years) {
                        if (! $this->hasValidSeedlingHeight($row->previous_height) || ! $this->hasValidSeedlingHeight($row->current_height)) {
                            return null;
                        }

                        return (log((float) $row->current_height) - log((float) $row->previous_height)) / $years;
                    })
                    ->filter(fn ($value) => $value !== null)
                    ->values()
                    ->all();

                return [
                    'csp' => (string) $csp,
                    'previous_alive_total' => $previousAlive,
                    'previous_recruit_total' => $rows->filter(fn ($row) => $row->recruit === 'R')->count(),
                    'survived_total' => $survived,
                    'survival_rate' => round($survivalRate, 3),
                    'growth_rate' => count($logGrowthValues) === 0 ? null : round($this->median($logGrowthValues), 3),
                    'growth_cm_rate' => count($growthValues) === 0 ? null : round($this->median($growthValues), 3),
                    'growth_count' => count($growthValues),
                    'growth_values' => array_map(fn ($value) => round((float) $value, 3), $growthValues),
                ];
            })
            ->filter()
            ->sortBy([
                ['previous_alive_total', 'desc'],
                ['csp', 'asc'],
            ])
            ->values()
            ->all();
    }

    private function hasValidSeedlingHeight($height): bool
    {
        if ($height === null || $height === '') {
            return false;
        }

        $height = (float) $height;

        return $height > 0 && $height < 999;
    }

    private function median(array $values): float
    {
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
    }

    private function seedlingIntervalYears(object $previousCensusRow, object $currentCensusRow): float
    {
        if (isset($previousCensusRow->mean_day, $currentCensusRow->mean_day) && $previousCensusRow->mean_day !== null && $currentCensusRow->mean_day !== null) {
            return max(0, ((float) $currentCensusRow->mean_day - (float) $previousCensusRow->mean_day) / 365.25);
        }

        $previousMonthIndex = ((int) $previousCensusRow->year * 12) + (int) $previousCensusRow->month;
        $currentMonthIndex = ((int) $currentCensusRow->year * 12) + (int) $currentCensusRow->month;

        return max(0, ($currentMonthIndex - $previousMonthIndex) / 12);
    }

    private function renderSeedlingGrowthHistogramAssets(Request $request, int $minCensus, int $maxCensus, array $summary): array
    {
        $plots = [];
        $figures = [];
        $scriptPath = resource_path('scripts/seedling_growth_histograms.R');
        $hash = substr(sha1($minCensus . '-' . $maxCensus . '-style-v1-' . filemtime($scriptPath) . '-' . $this->researchChartStyleVersion() . '-' . md5(json_encode($summary, JSON_UNESCAPED_UNICODE))), 0, 12);
        $assetKey = "seedling_research_output_assets_{$minCensus}_{$maxCensus}_growth_histograms";
        $assetRecords = $request->session()->get($assetKey, []);
        $assetRecords = is_array($assetRecords) ? $assetRecords : [];

        foreach ($summary['intervals'] ?? [] as $interval) {
            $panels = collect($interval['rows'])
                ->filter(fn ($row) => ($row['growth_count'] ?? 0) >= 10 && count($row['growth_values'] ?? []) >= 10)
                ->map(fn ($row) => [
                    'sp' => $row['csp'],
                    'csp' => $row['csp'],
                    'values' => $row['growth_values'],
                ])
                ->values()
                ->all();

            foreach (array_chunk($panels, 16) as $pageIndex => $chunk) {
                $fileBase = "growth-histogram-{$hash}-census-{$interval['previous_census']}-{$interval['current_census']}-page-" . ($pageIndex + 1);
                $pngToken = "{$fileBase}-png";
                $pdfToken = "{$fileBase}-pdf";
                $plots[] = [
                    'file_base' => $fileBase,
                    'png_token' => $pngToken,
                    'pdf_token' => $pdfToken,
                    'panels' => $chunk,
                    'x_label' => '生長率 (cm/year)',
                    'y_label' => '個體數',
                    'x_min' => -60,
                    'x_max' => 80,
                    'x_tick_min' => -60,
                    'x_tick_by' => 20,
                    'break_by' => 5,
                ];
                $figures[] = [
                    'previous_month_text' => $interval['previous_month_text'],
                    'current_month_text' => $interval['current_month_text'],
                    'png_token' => $pngToken,
                    'pdf_url' => route('admin.fushan.seedling.research-output.asset', ['token' => $pdfToken, 'extension' => 'pdf']),
                    'png_url' => null,
                ];
            }
        }

        if (count($plots) === 0) {
            return ['figures' => [], 'error' => null];
        }

        $missingFigure = collect($plots)->contains(function ($plot) use ($assetRecords) {
            $png = $assetRecords[$plot['png_token']]['path'] ?? null;
            $pdf = $assetRecords[$plot['pdf_token']]['path'] ?? null;

            return ! is_string($png) || ! is_file($png) || ! is_string($pdf) || ! is_file($pdf);
        });

        if ($missingFigure) {
            $this->deleteSeedlingResearchAssets($assetRecords);
            $assetRecords = [];
            $temporaryDirectory = sys_get_temp_dir() . '/seedling-growth-histogram-' . $hash . '-' . uniqid();

            if (! @mkdir($temporaryDirectory, 0775, true) && ! is_dir($temporaryDirectory)) {
                return ['figures' => $figures, 'error' => "無法建立圖表暫存資料夾：{$temporaryDirectory}"];
            }

            $jsonPath = $temporaryDirectory . "/growth-histogram-{$hash}.json";
            $jsonWritten = @file_put_contents($jsonPath, json_encode(['plots' => $plots], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            if ($jsonWritten === false) {
                $this->removeSeedlingTemporaryDirectory($temporaryDirectory);
                return ['figures' => $figures, 'error' => "無法寫入圖表資料檔：{$jsonPath}"];
            }

            $process = new Process([
                'Rscript',
                $scriptPath,
                '--input',
                $jsonPath,
                '--outdir',
                $temporaryDirectory,
                '--font',
                storage_path('fonts/msjh.ttf'),
                '--times',
                resource_path('fonts/times.ttf'),
            ]);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->removeSeedlingTemporaryDirectory($temporaryDirectory);
                return ['figures' => $figures, 'error' => trim($process->getErrorOutput() ?: $process->getOutput())];
            }

            foreach ($plots as $plot) {
                foreach (['png', 'pdf'] as $extension) {
                    $token = $plot[$extension . '_token'];
                    $path = $temporaryDirectory . '/' . $plot['file_base'] . '.' . $extension;

                    if (! is_file($path)) {
                        $this->removeSeedlingTemporaryDirectory($temporaryDirectory);
                        return ['figures' => $figures, 'error' => "圖檔沒有成功產生：{$path}"];
                    }

                    $assetRecords[$token] = $this->researchOutputAssetRecord($path, $extension, $plot['file_base'] . '.' . $extension);
                }
            }

            @unlink($jsonPath);
            $request->session()->put($assetKey, $assetRecords);
        }

        foreach ($figures as &$figure) {
            $pngPath = $assetRecords[$figure['png_token']]['path'] ?? null;

            $figure['png_url'] = $this->inlineResearchOutputImage($pngPath);
        }
        unset($figure);

        return ['figures' => $figures, 'error' => null];
    }

    private function renderSeedlingCompositionAssets(Request $request, int $minCensus, int $maxCensus, array $summary): array
    {
        if (count($summary['surveys'] ?? []) === 0) {
            return ['docx_url' => null, 'figures' => [], 'error' => null];
        }

        $scriptPath = resource_path('scripts/seedling_composition.R');
        $hash = substr(sha1($minCensus . '-' . $maxCensus . '-composition-layout-v4-' . filemtime($scriptPath) . '-' . $this->researchChartStyleVersion() . '-' . md5(json_encode($summary, JSON_UNESCAPED_UNICODE))), 0, 12);
        $assetKey = "seedling_research_output_assets_{$minCensus}_{$maxCensus}_composition";
        $sessionAssets = $request->session()->get($assetKey, []);
        $figures = [];
        $plots = [];
        $assetRecords = is_array($sessionAssets) ? $sessionAssets : [];

        $isSingleCompositionFigure = count($summary['surveys']) === 1;

        foreach ($summary['surveys'] as $survey) {
            $fileBase = "composition-{$hash}-census-{$survey['census']}";
            $pngToken = "{$fileBase}-png";
            $pdfToken = "{$fileBase}-pdf";
            $plots[] = [
                'file_base' => $fileBase,
                'png_token' => $pngToken,
                'pdf_token' => $pdfToken,
                'panel_label' => $isSingleCompositionFigure ? '' : '(' . chr(96 + max(1, min(26, (int) $survey['sequence']))) . ')',
                'layout' => $isSingleCompositionFigure ? 'long' : 'focus-standard',
                'focus_species' => '大明橘',
                'x_label' => '小苗個體數',
                'legend' => [
                    'survive' => '存活舊苗',
                    'recruit' => '新增苗',
                    'dead' => '死亡苗',
                ],
                'rows' => $survey['figure_rows'],
            ];
            $figures[$survey['census']] = [
                'png_url' => null,
                'pdf_url' => route('admin.fushan.seedling.research-output.asset', ['token' => $pdfToken, 'extension' => 'pdf']),
            ];
        }

        $missingFigure = collect($plots)->contains(function ($plot) use ($assetRecords) {
            $png = $assetRecords[$plot['png_token']]['path'] ?? null;
            $pdf = $assetRecords[$plot['pdf_token']]['path'] ?? null;

            return ! is_string($png) || ! is_file($png) || ! is_string($pdf) || ! is_file($pdf);
        });

        if ($missingFigure) {
            $this->deleteSeedlingResearchAssets($assetRecords);
            $assetRecords = [];
            $temporaryDirectory = sys_get_temp_dir() . '/seedling-composition-' . $hash . '-' . uniqid();

            if (! @mkdir($temporaryDirectory, 0775, true) && ! is_dir($temporaryDirectory)) {
                return [
                    'docx_url' => route('admin.fushan.seedling.research-output.composition-docx', ['start_census' => $minCensus, 'end_census' => $maxCensus]),
                    'figures' => $figures,
                    'error' => "無法建立圖表暫存資料夾：{$temporaryDirectory}",
                ];
            }

            $jsonPath = $temporaryDirectory . "/composition-{$hash}.json";
            $jsonWritten = @file_put_contents($jsonPath, json_encode(['plots' => $plots], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            if ($jsonWritten === false) {
                $this->removeSeedlingTemporaryDirectory($temporaryDirectory);

                return [
                    'docx_url' => route('admin.fushan.seedling.research-output.composition-docx', ['start_census' => $minCensus, 'end_census' => $maxCensus]),
                    'figures' => $figures,
                    'error' => "無法寫入圖表資料檔：{$jsonPath}",
                ];
            }

            $process = new Process([
                'Rscript',
                $scriptPath,
                '--input',
                $jsonPath,
                '--outdir',
                $temporaryDirectory,
                '--font',
                storage_path('fonts/msjh.ttf'),
                '--times',
                resource_path('fonts/times.ttf'),
            ]);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->removeSeedlingTemporaryDirectory($temporaryDirectory);

                return [
                    'docx_url' => route('admin.fushan.seedling.research-output.composition-docx', ['start_census' => $minCensus, 'end_census' => $maxCensus]),
                    'figures' => $figures,
                    'error' => trim($process->getErrorOutput() ?: $process->getOutput()),
                ];
            }

            foreach ($plots as $plot) {
                foreach (['png', 'pdf'] as $extension) {
                    $token = $plot[$extension . '_token'];
                    $path = $temporaryDirectory . '/' . $plot['file_base'] . '.' . $extension;

                    if (! is_file($path)) {
                        $this->removeSeedlingTemporaryDirectory($temporaryDirectory);

                        return [
                            'docx_url' => route('admin.fushan.seedling.research-output.composition-docx', ['start_census' => $minCensus, 'end_census' => $maxCensus]),
                            'figures' => $figures,
                            'error' => "圖檔沒有成功產生：{$path}",
                        ];
                    }

                    $assetRecords[$token] = $this->researchOutputAssetRecord($path, $extension, $plot['file_base'] . '.' . $extension);
                }
            }

            @unlink($jsonPath);
            $request->session()->put($assetKey, $assetRecords);
        }

        foreach ($plots as $plot) {
            $pngPath = $assetRecords[$plot['png_token']]['path'] ?? null;

            if (is_string($pngPath) && is_file($pngPath)) {
                foreach ($summary['surveys'] as $survey) {
                    if ($plot['file_base'] === "composition-{$hash}-census-{$survey['census']}") {
                        $figures[$survey['census']]['png_url'] = $this->inlineResearchOutputImage($pngPath);
                        break;
                    }
                }
            }
        }

        return [
            'docx_url' => route('admin.fushan.seedling.research-output.composition-docx', ['start_census' => $minCensus, 'end_census' => $maxCensus]),
            'figures' => $figures,
            'error' => null,
        ];
    }

    private function deleteSeedlingResearchAssets(array $assets): void
    {
        $this->deleteResearchOutputAssets($assets, $this->seedlingResearchOutputTemporaryPrefixes());
    }

    private function removeSeedlingTemporaryDirectory(string $directory): void
    {
        $this->removeResearchOutputTemporaryDirectory($directory);
    }

    private function seedlingCompositionFigureRows(array $aliveRows, array $newRows, array $deadRows): array
    {
        $aliveBySpecies = collect($aliveRows)->keyBy('csp');
        $newBySpecies = collect($newRows)->keyBy('csp');
        $deadBySpecies = collect($deadRows)->keyBy('csp');
        $species = $aliveBySpecies->keys()
            ->merge($newBySpecies->keys())
            ->merge($deadBySpecies->keys())
            ->unique()
            ->values();

        return $species
            ->map(function ($csp) use ($aliveBySpecies, $newBySpecies, $deadBySpecies) {
                $alive = (int) ($aliveBySpecies[$csp]['count'] ?? 0);
                $recruit = (int) ($newBySpecies[$csp]['count'] ?? 0);
                $dead = (int) ($deadBySpecies[$csp]['count'] ?? 0);

                return [
                    'csp' => $csp,
                    'alive' => $alive,
                    'survive' => max(0, $alive - $recruit),
                    'recruit' => $recruit,
                    'dead' => $dead,
                ];
            })
            ->sortBy([
                ['alive', 'asc'],
                ['dead', 'asc'],
                ['csp', 'asc'],
            ])
            ->values()
            ->all();
    }

    private function buildSeedlingSurvivalGrowthDocxContent(array $summary): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'seedling-survival-growth-');

        if ($temporaryPath === false) {
            throw new \RuntimeException('無法建立 Word 暫存檔。');
        }

        $zip = new \ZipArchive();

        if ($zip->open($temporaryPath, \ZipArchive::OVERWRITE) !== true) {
            @unlink($temporaryPath);
            throw new \RuntimeException('無法建立 Word 檔。');
        }

        $zip->addFromString('[Content_Types].xml', $this->seedlingDocxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->seedlingDocxRelsXml());
        $zip->addFromString('word/styles.xml', $this->seedlingDocxStylesXml());
        $zip->addFromString('word/document.xml', $this->seedlingSurvivalGrowthDocumentXml($summary));

        if (! $zip->close()) {
            @unlink($temporaryPath);
            throw new \RuntimeException('Word 檔暫存寫入失敗。');
        }

        $content = file_get_contents($temporaryPath);
        @unlink($temporaryPath);

        if ($content === false) {
            throw new \RuntimeException('Word 檔讀取失敗。');
        }

        return $content;
    }

    private function seedlingSurvivalGrowthDocumentXml(array $summary): string
    {
        $body = '';

        foreach ($summary['intervals'] as $interval) {
            $title = '表 福山森林動態樣區喬木樹種小苗之存活率及生長率';
            $caption = '挑選' . $interval['previous_month_text'] . '之調查中小苗數量 ≥ 10 株的樹種進行計算，N 為' . $interval['previous_month_text'] . '所調查到之小苗數量，S 為' . $interval['current_month_text'] . '存活之舊苗數量。';
            $body .= $this->seedlingDocxParagraph($title);
            $body .= $this->seedlingDocxParagraph($caption);
            $body .= $this->seedlingSurvivalGrowthDocxTable($interval['rows']);
            $body .= '<w:p/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>'
            . $body
            . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/></w:sectPr>'
            . '</w:body></w:document>';
    }

    private function seedlingSurvivalGrowthDocxTable(array $rows): string
    {
        $widths = [2300, 900, 900, 1300, 1500];
        $xml = '<w:tbl><w:tblPr><w:tblW w:w="6900" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '</w:tblBorders></w:tblPr><w:tblGrid>';

        foreach ($widths as $width) {
            $xml .= '<w:gridCol w:w="' . $width . '"/>';
        }

        $xml .= '</w:tblGrid>';
        $xml .= '<w:tr>'
            . $this->seedlingDocxCell('樹種', 2300)
            . $this->seedlingDocxCell('N', 900, 1, 'center')
            . $this->seedlingDocxCell('S', 900, 1, 'center')
            . $this->seedlingDocxCell('年存活率', 1300, 1, 'center')
            . $this->seedlingDocxCell('年生長率' . "\n" . '(cm/year)', 1500, 1, 'center')
            . '</w:tr>';

        foreach ($rows as $row) {
            $xml .= '<w:tr>'
                . $this->seedlingDocxCell($row['csp'], 2300)
                . $this->seedlingDocxCell(number_format($row['previous_alive_total']), 900, 1, 'right')
                . $this->seedlingDocxCell(number_format($row['survived_total']), 900, 1, 'right')
                . $this->seedlingDocxCell(number_format($row['survival_rate'], 3), 1300, 1, 'right')
                . $this->seedlingDocxCell($row['growth_cm_rate'] === null ? '' : number_format($row['growth_cm_rate'], 3), 1500, 1, 'right')
                . '</w:tr>';
        }

        return $xml . '</w:tbl>';
    }

    private function buildSeedlingCompositionDocxContent(array $summary): string
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'seedling-composition-');

        if ($temporaryPath === false) {
            throw new \RuntimeException('無法建立 Word 暫存檔。');
        }

        $zip = new \ZipArchive();

        if ($zip->open($temporaryPath, \ZipArchive::OVERWRITE) !== true) {
            @unlink($temporaryPath);
            throw new \RuntimeException('無法建立 Word 檔。');
        }

        $zip->addFromString('[Content_Types].xml', $this->seedlingDocxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->seedlingDocxRelsXml());
        $zip->addFromString('word/styles.xml', $this->seedlingDocxStylesXml());
        $zip->addFromString('word/document.xml', $this->seedlingCompositionDocumentXml($summary));

        if (! $zip->close()) {
            @unlink($temporaryPath);
            throw new \RuntimeException('Word 檔暫存寫入失敗。');
        }

        $content = file_get_contents($temporaryPath);
        @unlink($temporaryPath);

        if ($content === false) {
            throw new \RuntimeException('Word 檔讀取失敗。');
        }

        return $content;
    }

    private function seedlingCompositionDocumentXml(array $summary): string
    {
        $body = '';

        foreach ($summary['surveys'] as $survey) {
            $title = $survey['previous_month_text']
                ? "福山森林動態樣區 {$survey['previous_month_text']} 至 {$survey['survey_month_text']} 喬木小苗之動態變化"
                : "福山森林動態樣區 {$survey['survey_month_text']} 喬木小苗之植物組成";
            $body .= $this->seedlingDocxParagraph($title);

            if ($survey['previous_month_text']) {
                $body .= $this->seedlingDocxParagraph("其中存活小苗數量包含{$survey['previous_month_text']}調查存活之舊苗與{$survey['survey_month_text']}調查新增之小苗。");
            }

            $body .= $this->seedlingDocxTable($survey['table']);
            $body .= '<w:p/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>'
            . $body
            . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/></w:sectPr>'
            . '</w:body></w:document>';
    }

    private function seedlingDocxTable(array $table): string
    {
        $widths = [1800, 650, 295, 1800, 650, 295, 1800, 650];
        $rowCount = max(count($table['alive']), count($table['new']), count($table['dead']));
        $xml = '<w:tbl><w:tblPr><w:tblW w:w="7940" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '</w:tblBorders></w:tblPr><w:tblGrid>';

        foreach ($widths as $width) {
            $xml .= '<w:gridCol w:w="' . $width . '"/>';
        }

        $xml .= '</w:tblGrid>';
        $xml .= '<w:tr>'
            . $this->seedlingDocxCell('存活小苗數量', 2450, 2)
            . $this->seedlingDocxCell('', 295)
            . $this->seedlingDocxCell('新增小苗數量', 2450, 2)
            . $this->seedlingDocxCell('', 295)
            . $this->seedlingDocxCell('死亡小苗數量', 2450, 2)
            . '</w:tr>';
        $xml .= '<w:tr>'
            . $this->seedlingDocxCell('物種', 1800)
            . $this->seedlingDocxCell('株數', 650)
            . $this->seedlingDocxCell('', 295)
            . $this->seedlingDocxCell('物種', 1800)
            . $this->seedlingDocxCell('株數', 650)
            . $this->seedlingDocxCell('', 295)
            . $this->seedlingDocxCell('物種', 1800)
            . $this->seedlingDocxCell('株數', 650)
            . '</w:tr>';

        for ($i = 0; $i < $rowCount; $i++) {
            $alive = $table['alive'][$i] ?? null;
            $new = $table['new'][$i] ?? null;
            $dead = $table['dead'][$i] ?? null;
            $xml .= '<w:tr>'
                . $this->seedlingDocxCell($alive['csp'] ?? '', 1800)
                . $this->seedlingDocxCell(isset($alive) ? number_format($alive['count']) : '', 650, 1, 'right')
                . $this->seedlingDocxCell('', 295)
                . $this->seedlingDocxCell($new['csp'] ?? '', 1800)
                . $this->seedlingDocxCell(isset($new) ? number_format($new['count']) : '', 650, 1, 'right')
                . $this->seedlingDocxCell('', 295)
                . $this->seedlingDocxCell($dead['csp'] ?? '', 1800)
                . $this->seedlingDocxCell(isset($dead) ? number_format($dead['count']) : '', 650, 1, 'right')
                . '</w:tr>';
        }

        $xml .= '<w:tr>'
            . $this->seedlingDocxCell('小苗總數', 1800)
            . $this->seedlingDocxCell(number_format($table['alive_total']), 650, 1, 'right')
            . $this->seedlingDocxCell('', 295)
            . $this->seedlingDocxCell('', 1800)
            . $this->seedlingDocxCell(number_format($table['new_total']), 650, 1, 'right')
            . $this->seedlingDocxCell('', 295)
            . $this->seedlingDocxCell('', 1800)
            . $this->seedlingDocxCell(number_format($table['dead_total']), 650, 1, 'right')
            . '</w:tr>';

        return $xml . '</w:tbl>';
    }

    private function seedlingDocxCell(string $text, int $width, int $gridSpan = 1, string $align = 'left'): string
    {
        $props = '<w:tcPr><w:tcW w:w="' . $width . '" w:type="dxa"/>';

        if ($gridSpan > 1) {
            $props .= '<w:gridSpan w:val="' . $gridSpan . '"/>';
        }

        $props .= '</w:tcPr>';

        return '<w:tc>' . $props . $this->seedlingDocxParagraph($text, $align) . '</w:tc>';
    }

    private function seedlingDocxParagraph(string $text, string $align = 'left'): string
    {
        return '<w:p><w:pPr><w:jc w:val="' . $align . '"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="標楷體" w:cs="Times New Roman"/><w:sz w:val="22"/></w:rPr><w:t xml:space="preserve">'
            . $this->seedlingDocxEscape($text)
            . '</w:t></w:r></w:p>';
    }

    private function seedlingDocxEscape(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function seedlingDocxStylesXml(): string
    {
        $fontXml = '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="標楷體" w:cs="Times New Roman"/><w:sz w:val="22"/><w:szCs w:val="22"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:docDefaults><w:rPrDefault><w:rPr>' . $fontXml . '</w:rPr></w:rPrDefault></w:docDefaults>'
            . '<w:style w:type="paragraph" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr>' . $fontXml . '</w:rPr></w:style>'
            . '</w:styles>';
    }

    private function seedlingDocxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';
    }

    private function seedlingDocxRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';
    }

    private function formatSeedlingMonth(int $year, int $month): string
    {
        return sprintf('%d 年 %d 月', $year, $month);
    }

    private function formatSeedlingSurveyMonth(int $year, int $month): string
    {
        $period = match ($month) {
            2 => '初',
            8 => '底',
            default => '',
        };

        return sprintf('%d 年 %d 月%s', $year, $month, $period);
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

        $slrecord = FsSeedlingSlrecord::first() ?: FsSeedlingSlrecord1::first() ?: FsSeedlingSlrecord2::first();
        $census = $slrecord->census ?? FsSeedlingRecord::max("census");

        return view('pages/fushan/seedling_import', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'census' => $census,

        ]);
    }


    public function copybook(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/seedling_copybook', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'iansuiFontVersion' => file_exists(public_path('fonts/iansui.ttf')) ? filemtime(public_path('fonts/iansui.ttf')) : time(),
            'chenYuluoyanFontVersion' => file_exists(public_path('fonts/ChenYuluoyan-Thin-Monospaced.ttf'))
                ? filemtime(public_path('fonts/ChenYuluoyan-Thin-Monospaced.ttf'))
                : time(),
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

        $dateOptions = $this->seedlingCensusOptions();
        $censusValues = $dateOptions->pluck('census')->map(fn ($census) => (string) $census)->all();
        $defaultStartCensus = $dateOptions->first()['census'] ?? null;
        $defaultEndCensus = $dateOptions->last()['census'] ?? null;
        $selectedStartCensus = $request->input('start_census', $defaultStartCensus);
        $selectedEndCensus = $request->input('end_census', $defaultEndCensus);

        if (! in_array((string) $selectedStartCensus, $censusValues, true)) {
            $selectedStartCensus = $defaultStartCensus;
        }

        if (! in_array((string) $selectedEndCensus, $censusValues, true)) {
            $selectedEndCensus = $defaultEndCensus;
        }

        return view('pages/fushan/seedling_download', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->account ?? $user->name,
            'dateOptions' => $dateOptions,
            'selectedStartCensus' => $selectedStartCensus,
            'selectedEndCensus' => $selectedEndCensus,
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

    public function downloadAllSeedling(Request $request): StreamedResponse
    {
        $validated = $this->validSeedlingResearchRange($request->input('start_census'), $request->input('end_census'));

        abort_if($validated === null, 422, '資料範圍不正確，請重新選擇。');

        [$minCensus, $maxCensus] = $validated;
        $columns = [
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
            'updated_id',
        ];
        $filename = 'seedling_all_data_' . $minCensus . '_' . $maxCensus . '_' . now()->format('Ymd') . '.txt';

        return $this->streamTxt($filename, $columns, function ($handle) use ($columns, $minCensus, $maxCensus) {
            DB::connection('mysql3')
                ->table('seedling_records as r')
                ->join('seedling_stems as st', 'r.tag', '=', 'st.tag')
                ->join('seedling_individuals as i', 'st.mtag', '=', 'i.mtag')
                ->whereBetween('r.census', [$minCensus, $maxCensus])
                ->whereNull('r.deleted_at')
                ->whereNull('st.deleted_at')
                ->whereNull('i.deleted_at')
                ->select([
                    'r.id as id',
                    'r.census as census',
                    'r.year as year',
                    'r.month as month',
                    DB::raw("COALESCE(DATE_FORMAT(r.date, '%Y-%m-%d'), '0000-00-00') as date"),
                    'i.trap as trap',
                    'i.plot as plot',
                    'st.tag as tag',
                    'st.mtag as mtag',
                    'i.csp as csp',
                    'r.ht as ht',
                    'r.cotno as cotno',
                    'r.leafno as leafno',
                    'st.ind as ind',
                    DB::raw("COALESCE(r.note, '') as note"),
                    DB::raw("COALESCE(r.recruit, '') as recruit"),
                    DB::raw("COALESCE(r.status, '') as status"),
                    DB::raw("COALESCE(st.sprout, '') as sprout"),
                    'i.x as x',
                    'i.y as y',
                    DB::raw("COALESCE(r.updated_id, '') as updated_id"),
                ])
                ->orderBy('r.census')
                ->orderBy('i.trap')
                ->orderBy('i.plot')
                ->orderBy('st.tag')
                ->chunk(1000, function ($rows) use ($handle, $columns) {
                    foreach ($rows as $row) {
                        fputcsv($handle, array_map(fn ($column) => $row->{$column}, $columns), "\t");
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
