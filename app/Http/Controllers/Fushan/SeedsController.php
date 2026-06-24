<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ManagesResearchOutputAssets;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

//分配網址至各頁面

class SeedsController extends Controller
{
    use ManagesResearchOutputAssets;



    public function seeds(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_doc', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name


        ]);
    }


    public function entry(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');



        return view('pages/fushan/seeds_entry', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name

        ]);
    }


    public function import(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_import', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name

        ]);
    }


    public function note(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/seeds_note', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name


        ]);
    }


    public function showdata(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_dataviewer', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name


        ]);
    }

    public function researchOutput(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        $dateOptions = FsSeedsDateinfo::query()
            ->select('census', 'date')
            ->whereNotNull('census')
            ->orderByDesc('census')
            ->get()
            ->map(fn ($row) => [
                'census' => $row->census,
                'date' => $row->date ?? '',
                'label' => 'census ' . $row->census . ' : ' . ($row->date ?? ''),
            ])
            ->values();

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

        $hasAppliedRange = $request->has('start_census') && $request->has('end_census');

        if ($hasAppliedRange) {
            $this->forgetSeedResearchOutputSession($request);
        }

        return view('pages/fushan/seeds_research_output', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name,
            'dateOptions' => $dateOptions,
            'selectedStartCensus' => $selectedStartCensus,
            'selectedEndCensus' => $selectedEndCensus,
            'hasAppliedRange' => $hasAppliedRange,
            'itemUrls' => [
                'composition' => route('admin.fushan.seeds.research-output.item', ['item' => 'composition']),
                'phenology' => route('admin.fushan.seeds.research-output.item', ['item' => 'phenology']),
                'distribution' => route('admin.fushan.seeds.research-output.item', ['item' => 'distribution']),
            ],
            'clearSessionUrl' => route('admin.fushan.seeds.research-output.clear-session'),
        ]);
    }

    public function researchOutputItem(Request $request, string $item)
    {
        $startCensus = $request->input('start_census');
        $endCensus = $request->input('end_census');
        $validated = $this->validSeedResearchRange($startCensus, $endCensus);

        if ($validated === null) {
            return response()->json([
                'error' => '資料範圍不正確，請重新套用篩選。',
            ], 422);
        }

        [$minCensus, $maxCensus] = $validated;
        $cacheKey = $this->seedResearchOutputCacheKey($item, $minCensus, $maxCensus);
        $cached = $request->session()->get($cacheKey);

        if (is_array($cached) && isset($cached['html'])) {
            return response()->json([
                'html' => $cached['html'],
                'cached' => true,
            ]);
        }

        $viewData = [
            'item' => $item,
            'number' => fn ($value) => number_format((float) $value, 0),
            'percent' => fn ($value) => number_format((float) $value, 1),
        ];

        if ($item === 'composition') {
            $compositionSummary = $this->seedCompositionSummary($minCensus, $maxCensus);
            $viewData['compositionSummary'] = $compositionSummary;
            $viewData['compositionFigure'] = $this->renderSeedCompositionFigure($request, $minCensus, $maxCensus, $compositionSummary);
        } elseif ($item === 'phenology') {
            $phenologySummary = $this->seedPhenologySummary($minCensus, $maxCensus);
            $viewData['phenologySummary'] = $phenologySummary;
            $viewData['phenologyFigure'] = $this->renderSeedPhenologyFigure($request, $minCensus, $maxCensus, $phenologySummary);
        } elseif ($item === 'distribution') {
            $distributionSummary = $this->seedSpatialDistributionSummary($minCensus, $maxCensus);
            $viewData['distributionSummary'] = $distributionSummary;
            $viewData['distributionFigure'] = $this->renderSeedSpatialDistributionFigure($request, $minCensus, $maxCensus, $distributionSummary);
            $viewData['distributionSpeciesFigure'] = $this->renderSeedSpatialSpeciesFigure($request, $minCensus, $maxCensus, $distributionSummary);
        } else {
            return response()->json(['error' => '未知的成果項目。'], 404);
        }

        $html = view('pages.fushan.partials.seeds_research_output_item', $viewData)->render();
        $request->session()->put($cacheKey, ['html' => $html]);

        return response()->json([
            'html' => $html,
            'cached' => false,
        ]);
    }

    public function clearResearchOutputSession(Request $request)
    {
        $this->forgetSeedResearchOutputSession($request);

        return response()->noContent();
    }

    public function researchOutputAsset(Request $request, string $token, string $extension)
    {
        return $this->researchOutputAssetFromSession($request, $token, $extension, 'seeds_research_output_assets_');
    }

    private function forgetSeedResearchOutputSession(Request $request): void
    {
        $this->forgetResearchOutputSessionAssets(
            $request,
            'seeds_research_output.',
            'seeds_research_output_assets_',
            $this->seedResearchOutputTemporaryPrefixes()
        );
    }

    private function validSeedResearchRange($startCensus, $endCensus): ?array
    {
        if ($startCensus === null || $endCensus === null) {
            return null;
        }

        $exists = FsSeedsDateinfo::query()
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

    private function seedResearchOutputCacheKey(string $item, int $minCensus, int $maxCensus): string
    {
        return "seeds_research_output.v3.{$minCensus}.{$maxCensus}.{$item}";
    }

    private function seedResearchOutputTemporaryPrefixes(): array
    {
        return [
            sys_get_temp_dir() . '/seeds-research-output-',
        ];
    }

    private function seedCompositionSummary($startCensus, $endCensus): array
    {
        if ($startCensus === null || $endCensus === null) {
            return $this->emptySeedCompositionSummary();
        }

        $minCensus = min((int) $startCensus, (int) $endCensus);
        $maxCensus = max((int) $startCensus, (int) $endCensus);
        $baseDatabase = config('database.connections.mysql4.database') ?: 'fs_base';
        $spinfoTable = '`' . str_replace('`', '``', $baseDatabase) . '`.`spinfo`';

        $dateRows = FsSeedsDateinfo::query()
            ->select('census', 'date')
            ->whereBetween('census', [$minCensus, $maxCensus])
            ->orderBy('census')
            ->get();

        $summary = DB::connection('mysql2')
            ->table('fulldata as f')
            ->leftJoin('splist as sl', 'f.csp', '=', 'sl.csp')
            ->leftJoin(DB::raw($spinfoTable . ' as sp'), 'f.sp', '=', 'sp.spcode')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->selectRaw("COUNT(DISTINCT CASE WHEN COALESCE(sp.apgfamily, '') != '' THEN sp.apgfamily END) as family_count")
            ->selectRaw("COUNT(DISTINCT CASE WHEN COALESCE(sp.genus, '') != '' THEN sp.genus END) as genus_count")
            ->selectRaw("COUNT(DISTINCT CASE WHEN COALESCE(f.sp, '') != '' THEN f.sp ELSE f.csp END) as species_count")
            ->selectRaw("SUM(CASE WHEN f.code = '6' THEN 1 ELSE 0 END) as flower_record_count")
            ->selectRaw("SUM(CASE WHEN f.code = '1' THEN CAST(COALESCE(NULLIF(f.`count`, ''), 0) AS DECIMAL(12,2)) ELSE 0 END) as mature_fruit_count")
            ->selectRaw("SUM(CASE WHEN f.code = '2' THEN CAST(COALESCE(NULLIF(f.seeds, ''), 0) AS DECIMAL(12,2)) ELSE 0 END) as mature_seed_count")
            ->selectRaw("SUM(CASE WHEN f.code = '1' AND COALESCE(sl.size, '') != 'S' THEN CAST(COALESCE(NULLIF(f.seeds, ''), 0) AS DECIMAL(12,2)) ELSE 0 END) as fruit_seed_count")
            ->selectRaw("SUM(CASE WHEN f.code = '1' AND sl.size = 'S' THEN CAST(COALESCE(NULLIF(f.`count`, ''), 0) AS DECIMAL(12,2)) ELSE 0 END) as small_fruit_count")
            ->first();

        $flowerTop = $this->seedCompositionTopSpecies($minCensus, $maxCensus, "f.code = '6'", 'COUNT(*)');
        $fruitTop = $this->seedCompositionTopSpecies($minCensus, $maxCensus, "f.code = '1'", "SUM(CAST(COALESCE(NULLIF(f.`count`, ''), 0) AS DECIMAL(12,2)))");
        $seedTop = $this->seedCompositionTopSpecies($minCensus, $maxCensus, "f.code = '2'", "SUM(CAST(COALESCE(NULLIF(f.seeds, ''), 0) AS DECIMAL(12,2)))");
        $seedOnlySpecies = $this->seedCompositionSpeciesByCodes($minCensus, $maxCensus, ['2'], ['1']);
        $fragmentOnlySpecies = $this->seedCompositionSpeciesByCodes($minCensus, $maxCensus, ['3', '4'], ['1', '2']);

        $matureFruitCount = (float) ($summary->mature_fruit_count ?? 0);
        $matureSeedCount = (float) ($summary->mature_seed_count ?? 0);
        $fruitTopTwoCount = collect($fruitTop)->take(2)->sum('total');
        $seedTopTwoCount = collect($seedTop)->take(2)->sum('total');

        return [
            'start_date_text' => $this->formatSeedDate($dateRows->first()->date ?? ''),
            'end_date_text' => $this->formatSeedDate($dateRows->last()->date ?? ''),
            'survey_count' => $dateRows->count(),
            'family_count' => (int) ($summary->family_count ?? 0),
            'genus_count' => (int) ($summary->genus_count ?? 0),
            'species_count' => (int) ($summary->species_count ?? 0),
            'flower_record_count' => (int) ($summary->flower_record_count ?? 0),
            'mature_fruit_count' => (int) round($matureFruitCount),
            'mature_seed_count' => (int) round($matureSeedCount),
            'seed_total_with_fruits' => (int) round($matureSeedCount + (float) ($summary->fruit_seed_count ?? 0)),
            'small_fruit_count' => (int) round((float) ($summary->small_fruit_count ?? 0)),
            'flower_top' => $flowerTop,
            'fruit_top' => $fruitTop,
            'seed_top' => $seedTop,
            'fruit_top_two_percent' => $matureFruitCount > 0 ? round($fruitTopTwoCount / $matureFruitCount * 100, 1) : 0,
            'seed_top_two_percent' => $matureSeedCount > 0 ? round($seedTopTwoCount / $matureSeedCount * 100, 1) : 0,
            'seed_only_species' => $seedOnlySpecies,
            'fragment_only_species' => $fragmentOnlySpecies,
        ];
    }

    private function emptySeedCompositionSummary(): array
    {
        return [
            'start_date_text' => '',
            'end_date_text' => '',
            'survey_count' => 0,
            'family_count' => 0,
            'genus_count' => 0,
            'species_count' => 0,
            'flower_record_count' => 0,
            'mature_fruit_count' => 0,
            'mature_seed_count' => 0,
            'seed_total_with_fruits' => 0,
            'small_fruit_count' => 0,
            'flower_top' => [],
            'fruit_top' => [],
            'seed_top' => [],
            'fruit_top_two_percent' => 0,
            'seed_top_two_percent' => 0,
            'seed_only_species' => [],
            'fragment_only_species' => [],
        ];
    }

    private function renderSeedCompositionFigure(Request $request, $startCensus, $endCensus, array $compositionSummary): array
    {
        if (($compositionSummary['survey_count'] ?? 0) === 0) {
            return ['png_url' => null, 'pdf_url' => null, 'error' => null];
        }

        $minCensus = min((int) $startCensus, (int) $endCensus);
        $maxCensus = max((int) $startCensus, (int) $endCensus);
        $scriptPath = resource_path('scripts/seeds_composition.R');

        return $this->renderSeedResearchFigure(
            $request,
            'composition',
            $minCensus,
            $maxCensus,
            $scriptPath,
            $this->seedCompositionFigurePayload($minCensus, $maxCensus, $compositionSummary)
        );
    }

    private function seedCompositionFigurePayload(int $minCensus, int $maxCensus, array $summary): array
    {
        $flowerRows = $this->seedCompositionRows($minCensus, $maxCensus, "f.code = '6'", 'COUNT(*)');
        $fruitRows = $this->seedCompositionRows($minCensus, $maxCensus, "f.code = '1'", "SUM(CAST(COALESCE(NULLIF(f.`count`, ''), 0) AS DECIMAL(12,2)))");
        $seedRows = $this->seedCompositionRows($minCensus, $maxCensus, "f.code = '2'", "SUM(CAST(COALESCE(NULLIF(f.seeds, ''), 0) AS DECIMAL(12,2)))");
        $lowerPanelRows = max(count($fruitRows), count($seedRows));

        return [
            'flower' => [
                'title' => '(a) 花',
                'x_label' => '數量',
                'rows' => $flowerRows,
            ],
            'fruit' => [
                'title' => '(b) 成熟果實',
                'x_label' => '數量',
                'rows' => $fruitRows,
                'pad_to' => $lowerPanelRows,
            ],
            'seed' => [
                'title' => '(c) 種子',
                'x_label' => '數量',
                'rows' => $seedRows,
                'pad_to' => $lowerPanelRows,
            ],
            'caption' => '圖. 福山森林動態樣區 ' . ($summary['start_date_text'] ?? '') . '至' . ($summary['end_date_text'] ?? '') . ' 種子雨之主要植物組成。 (a)為各物種所收集到之落花紀錄筆數；(b)為成熟果實之數量，橫軸為對數刻度；(c)則為種子之數量。',
        ];
    }

    private function seedCompositionRows(int $minCensus, int $maxCensus, string $whereRaw, string $totalExpression, int $limit = 10): array
    {
        $rows = DB::connection('mysql2')
            ->table('fulldata as f')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->whereRaw($whereRaw)
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->select('f.csp')
            ->selectRaw($totalExpression . ' as total')
            ->groupBy('f.csp')
            ->havingRaw('total > 0')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->csp,
                'value' => (int) round((float) $row->total),
            ]);

        $topRows = $rows->take($limit)->values();
        $otherTotal = $rows->slice($limit)->sum('value');

        if ($otherTotal > 0) {
            $topRows->push(['label' => '其他物種', 'value' => (int) $otherTotal]);
        }

        return $topRows->all();
    }
    private function seedCompositionTopSpecies(int $minCensus, int $maxCensus, string $whereRaw, string $totalExpression): array
    {
        return DB::connection('mysql2')
            ->table('fulldata as f')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->whereRaw($whereRaw)
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->select('f.csp')
            ->selectRaw($totalExpression . ' as total')
            ->groupBy('f.csp')
            ->havingRaw('total > 0')
            ->orderByDesc('total')
            ->limit(3)
            ->get()
            ->map(fn ($row) => [
                'csp' => $row->csp,
                'total' => (int) round((float) $row->total),
            ])
            ->all();
    }

    private function seedCompositionSpeciesByCodes(int $minCensus, int $maxCensus, array $includeCodes, array $excludeCodes): array
    {
        return DB::connection('mysql2')
            ->table('fulldata as f')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->groupBy('f.csp')
            ->havingRaw("SUM(CASE WHEN f.code IN (" . $this->quotedCodeList($includeCodes) . ") THEN 1 ELSE 0 END) > 0")
            ->havingRaw("SUM(CASE WHEN f.code IN (" . $this->quotedCodeList($excludeCodes) . ") THEN 1 ELSE 0 END) = 0")
            ->orderBy('f.csp')
            ->pluck('f.csp')
            ->all();
    }


    private function seedPhenologySummary($startCensus, $endCensus): array
    {
        if ($startCensus === null || $endCensus === null) {
            return $this->emptySeedPhenologySummary();
        }

        $minCensus = min((int) $startCensus, (int) $endCensus);
        $maxCensus = max((int) $startCensus, (int) $endCensus);

        $dateRows = FsSeedsDateinfo::query()
            ->select('census', 'date')
            ->whereBetween('census', [$minCensus, $maxCensus])
            ->orderBy('census')
            ->get();

        $summary = DB::connection('mysql2')
            ->table('fulldata as f')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->selectRaw("COUNT(DISTINCT CASE WHEN f.code = '6' THEN COALESCE(NULLIF(f.sp, ''), f.csp) END) as flower_species_count")
            ->selectRaw("COUNT(DISTINCT CASE WHEN CAST(f.code AS UNSIGNED) < 6 THEN COALESCE(NULLIF(f.sp, ''), f.csp) END) as fruit_species_count")
            ->first();

        $rows = $this->seedPhenologyRows($minCensus, $maxCensus);
        $flowerPeakRow = collect($rows)->sortByDesc('flower')->first();
        $fruitPeakRow = collect($rows)->sortByDesc('fruit')->first();

        return [
            'start_date_text' => $this->formatSeedDate($dateRows->first()->date ?? ''),
            'end_date_text' => $this->formatSeedDate($dateRows->last()->date ?? ''),
            'start_month_period_text' => $this->formatSeedMonthPeriod($dateRows->first()->date ?? ''),
            'end_month_period_text' => $this->formatSeedMonthPeriod($dateRows->last()->date ?? ''),
            'start_month_text' => $this->formatSeedMonth($dateRows->first()->date ?? ''),
            'end_month_text' => $this->formatSeedMonth($dateRows->last()->date ?? ''),
            'survey_count' => $dateRows->count(),
            'flower_species_count' => (int) ($summary->flower_species_count ?? 0),
            'fruit_species_count' => (int) ($summary->fruit_species_count ?? 0),
            'flower_peak_month_text' => $this->formatSeedMonth($flowerPeakRow['date'] ?? ''),
            'fruit_peak_month_text' => $this->formatSeedMonth($fruitPeakRow['date'] ?? ''),
            'rows' => $rows,
        ];
    }

    private function emptySeedPhenologySummary(): array
    {
        return [
            'start_date_text' => '',
            'end_date_text' => '',
            'start_month_period_text' => '',
            'end_month_period_text' => '',
            'start_month_text' => '',
            'end_month_text' => '',
            'survey_count' => 0,
            'flower_species_count' => 0,
            'fruit_species_count' => 0,
            'flower_peak_month_text' => '',
            'fruit_peak_month_text' => '',
            'rows' => [],
        ];
    }

    private function renderSeedPhenologyFigure(Request $request, $startCensus, $endCensus, array $phenologySummary): array
    {
        if (($phenologySummary['survey_count'] ?? 0) === 0 || count($phenologySummary['rows'] ?? []) === 0) {
            return ['png_url' => null, 'pdf_url' => null, 'error' => null];
        }

        $minCensus = min((int) $startCensus, (int) $endCensus);
        $maxCensus = max((int) $startCensus, (int) $endCensus);
        $scriptPath = resource_path('scripts/seeds_phenology_species.R');

        return $this->renderSeedResearchFigure($request, 'phenology-species', $minCensus, $maxCensus, $scriptPath, [
            'y_label' => '物種數',
            'legend' => [
                'flower' => '花',
                'fruit' => '果',
            ],
            'empty_label' => '無資料',
            'rows' => $phenologySummary['rows'],
        ]);
    }

    private function seedPhenologyRows(int $minCensus, int $maxCensus): array
    {
        return DB::connection('mysql2')
            ->table('fulldata as f')
            ->join('dateinfo as d', 'd.census', '=', 'f.census')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->whereNotNull('d.date')
            ->where('d.date', '!=', '')
            ->select('d.date')
            ->selectRaw("COUNT(DISTINCT CASE WHEN f.code = '6' THEN COALESCE(NULLIF(f.sp, ''), f.csp) END) as flower")
            ->selectRaw("COUNT(DISTINCT CASE WHEN CAST(f.code AS UNSIGNED) < 6 THEN COALESCE(NULLIF(f.sp, ''), f.csp) END) as fruit")
            ->groupBy('d.date')
            ->orderBy('d.date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'flower' => (int) ($row->flower ?? 0),
                'fruit' => (int) ($row->fruit ?? 0),
            ])
            ->all();
    }

    private function renderSeedResearchFigure(Request $request, string $prefix, int $minCensus, int $maxCensus, string $scriptPath, array $payload): array
    {
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ($payloadJson === false) {
            return [
                'png_url' => null,
                'pdf_url' => null,
                'error' => '無法編碼圖表資料。',
            ];
        }

        $hash = substr(sha1($minCensus . '-' . $maxCensus . '-' . $prefix . '-asset-v1-' . filemtime($scriptPath) . '-' . md5((string) $payloadJson)), 0, 12);
        $fileBase = "{$prefix}-{$hash}";
        $pngToken = "{$fileBase}-png";
        $pdfToken = "{$fileBase}-pdf";
        $assetKey = "seeds_research_output_assets_{$minCensus}_{$maxCensus}_{$prefix}";
        $assetRecords = $request->session()->get($assetKey, []);
        $assetRecords = is_array($assetRecords) ? $assetRecords : [];

        $pngPath = $assetRecords[$pngToken]['path'] ?? null;
        $pdfPath = $assetRecords[$pdfToken]['path'] ?? null;
        $missingFigure = ! is_string($pngPath) || ! is_file($pngPath) || ! is_string($pdfPath) || ! is_file($pdfPath);

        if ($missingFigure) {
            $this->deleteResearchOutputAssets($assetRecords, $this->seedResearchOutputTemporaryPrefixes());
            $temporaryDirectory = sys_get_temp_dir() . "/seeds-research-output-{$prefix}-{$hash}-" . uniqid();
            $jsonPath = $temporaryDirectory . "/{$fileBase}.json";
            $pngPath = $temporaryDirectory . "/{$fileBase}.png";
            $pdfPath = $temporaryDirectory . "/{$fileBase}.pdf";

            if (! @mkdir($temporaryDirectory, 0775, true) && ! is_dir($temporaryDirectory)) {
                return [
                    'png_url' => null,
                    'pdf_url' => null,
                    'error' => "無法建立圖表暫存資料夾：{$temporaryDirectory}",
                ];
            }

            $jsonWritten = @file_put_contents($jsonPath, $payloadJson);

            if ($jsonWritten === false) {
                $this->removeResearchOutputTemporaryDirectory($temporaryDirectory);

                return [
                    'png_url' => null,
                    'pdf_url' => null,
                    'error' => "無法寫入圖表資料檔：{$jsonPath}",
                ];
            }

            $process = new Process([
                'Rscript',
                $scriptPath,
                '--input',
                $jsonPath,
                '--pdf',
                $pdfPath,
                '--png',
                $pngPath,
                '--font',
                storage_path('fonts/msjh.ttf'),
                '--times',
                resource_path('fonts/times.ttf'),
            ]);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->removeResearchOutputTemporaryDirectory($temporaryDirectory);

                return [
                    'png_url' => null,
                    'pdf_url' => null,
                    'error' => trim($process->getErrorOutput() ?: $process->getOutput()),
                ];
            }

            if (! is_file($pngPath) || ! is_file($pdfPath)) {
                $this->removeResearchOutputTemporaryDirectory($temporaryDirectory);

                return [
                    'png_url' => null,
                    'pdf_url' => null,
                    'error' => "圖檔沒有成功產生：{$fileBase}",
                ];
            }

            $assetRecords = [
                $pngToken => [
                    'path' => $pngPath,
                    'extension' => 'png',
                    'mime' => 'image/png',
                    'download' => "{$fileBase}.png",
                ],
                $pdfToken => [
                    'path' => $pdfPath,
                    'extension' => 'pdf',
                    'mime' => 'application/pdf',
                    'download' => "{$fileBase}.pdf",
                ],
            ];

            @unlink($jsonPath);
            $request->session()->put($assetKey, $assetRecords);
        }

        $pngPath = $assetRecords[$pngToken]['path'] ?? null;

        return [
            'png_url' => is_string($pngPath) && is_file($pngPath)
                ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($pngPath))
                : null,
            'pdf_url' => route('admin.fushan.seeds.research-output.asset', ['token' => $pdfToken, 'extension' => 'pdf']),
            'error' => null,
        ];
    }


    private function seedSpatialDistributionSummary(int $minCensus, int $maxCensus): array
    {
        $dateRows = FsSeedsDateinfo::query()
            ->select('census', 'date')
            ->whereBetween('census', [$minCensus, $maxCensus])
            ->orderBy('census')
            ->get();

        $trapTotal = 106;
        $flowerTrapRows = $this->seedTrapSpeciesCounts($minCensus, $maxCensus, "f.code = '6'");
        $fruitTrapRows = $this->seedTrapSpeciesCounts($minCensus, $maxCensus, "f.code IN ('1', '2')");
        $flowerSpeciesRows = $this->seedSpeciesTrapCounts($minCensus, $maxCensus, "f.code = '6'");
        $fruitSpeciesRows = $this->seedSpeciesTrapCounts($minCensus, $maxCensus, "f.code IN ('1', '2')");

        $flowerPositive = collect($flowerTrapRows)->where('species_count', '>', 0)->count();
        $flowerAtLeastThree = collect($flowerTrapRows)->where('species_count', '>=', 3)->count();
        $fruitPositive = collect($fruitTrapRows)->where('species_count', '>', 0)->count();
        $fruitAtLeastThree = collect($fruitTrapRows)->where('species_count', '>=', 3)->count();
        $flowerTopSpecies = $flowerSpeciesRows[0] ?? ['csp' => '', 'trap_count' => 0];
        $fruitTopSpecies = $fruitSpeciesRows[0] ?? ['csp' => '', 'trap_count' => 0];
        $flowerSpeciesAtLeastTen = collect($flowerSpeciesRows)->where('trap_count', '>=', 10)->count();
        $fruitSpeciesAtLeastTen = collect($fruitSpeciesRows)->where('trap_count', '>=', 10)->count();
        $speciesTrapRows = $this->seedSpatialSpeciesTrapRows($flowerSpeciesRows, $fruitSpeciesRows);

        return [
            'start_month_period_text' => $this->formatSeedMonthPeriod($dateRows->first()->date ?? ''),
            'end_month_period_text' => $this->formatSeedMonthPeriod($dateRows->last()->date ?? ''),
            'start_month_text' => $this->formatSeedMonth($dateRows->first()->date ?? ''),
            'end_month_text' => $this->formatSeedMonth($dateRows->last()->date ?? ''),
            'trap_total' => $trapTotal,
            'flower_trap_positive_count' => $flowerPositive,
            'flower_trap_positive_percent' => $trapTotal > 0 ? round($flowerPositive / $trapTotal * 100, 1) : 0,
            'flower_trap_at_least_three_count' => $flowerAtLeastThree,
            'flower_trap_at_least_three_percent' => $trapTotal > 0 ? round($flowerAtLeastThree / $trapTotal * 100, 1) : 0,
            'flower_top_species' => $flowerTopSpecies['csp'],
            'flower_top_species_trap_count' => (int) $flowerTopSpecies['trap_count'],
            'flower_species_at_least_ten_count' => $flowerSpeciesAtLeastTen,
            'fruit_trap_positive_count' => $fruitPositive,
            'fruit_trap_positive_percent' => $trapTotal > 0 ? round($fruitPositive / $trapTotal * 100, 1) : 0,
            'fruit_trap_at_least_three_count' => $fruitAtLeastThree,
            'fruit_trap_at_least_three_percent' => $trapTotal > 0 ? round($fruitAtLeastThree / $trapTotal * 100, 1) : 0,
            'fruit_top_species' => $fruitTopSpecies['csp'],
            'fruit_top_species_trap_count' => (int) $fruitTopSpecies['trap_count'],
            'fruit_species_at_least_ten_count' => $fruitSpeciesAtLeastTen,
            'flower_trap_rows' => $flowerTrapRows,
            'fruit_trap_rows' => $fruitTrapRows,
            'species_trap_rows' => $speciesTrapRows,
        ];
    }

    private function renderSeedSpatialDistributionFigure(Request $request, int $minCensus, int $maxCensus, array $summary): array
    {
        if (count($summary['flower_trap_rows'] ?? []) === 0 && count($summary['fruit_trap_rows'] ?? []) === 0) {
            return ['png_url' => null, 'pdf_url' => null, 'error' => null];
        }

        $scriptPath = resource_path('scripts/seeds_spatial_distribution.R');

        return $this->renderSeedResearchFigure($request, 'spatial-distribution', $minCensus, $maxCensus, $scriptPath, [
            'labels' => [
                'y' => '收集網數量',
                'x' => '物種數',
                'flower_title' => '(a) 花',
                'fruit_title' => '(b) 成熟果實及種子',
                'empty' => '無資料',
            ],
            'trap_total' => $summary['trap_total'],
            'flower_traps' => $summary['flower_trap_rows'],
            'fruit_traps' => $summary['fruit_trap_rows'],
        ]);
    }


    private function renderSeedSpatialSpeciesFigure(Request $request, int $minCensus, int $maxCensus, array $summary): array
    {
        if (count($summary['species_trap_rows'] ?? []) === 0) {
            return ['png_url' => null, 'pdf_url' => null, 'error' => null];
        }

        $scriptPath = resource_path('scripts/seeds_spatial_species_traps.R');

        return $this->renderSeedResearchFigure($request, 'spatial-species-traps', $minCensus, $maxCensus, $scriptPath, [
            'labels' => [
                'x' => '收集網數量',
                'fruit' => '成熟果實及種子',
                'flower' => '花',
                'empty' => '無資料',
            ],
            'rows' => $summary['species_trap_rows'],
        ]);
    }

    private function seedSpatialSpeciesTrapRows(array $flowerRows, array $fruitRows): array
    {
        $species = [];

        foreach ($flowerRows as $row) {
            $key = $row['csp'];
            $species[$key] ??= ['csp' => $key, 'fruit' => 0, 'flower' => 0];
            $species[$key]['flower'] = (int) $row['trap_count'];
        }

        foreach ($fruitRows as $row) {
            $key = $row['csp'];
            $species[$key] ??= ['csp' => $key, 'fruit' => 0, 'flower' => 0];
            $species[$key]['fruit'] = (int) $row['trap_count'];
        }

        return collect($species)
            ->filter(fn ($row) => $row['flower'] > 0 || $row['fruit'] > 0)
            ->sortBy([
                ['flower', 'asc'],
                ['fruit', 'asc'],
                ['csp', 'asc'],
            ])
            ->values()
            ->all();
    }

    private function seedTrapSpeciesCounts(int $minCensus, int $maxCensus, string $whereRaw): array
    {
        return DB::connection('mysql2')
            ->table('fulldata as f')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->whereRaw($whereRaw)
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->select('f.trap')
            ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(f.sp, ''), f.csp)) as species_count")
            ->groupBy('f.trap')
            ->orderBy('f.trap')
            ->get()
            ->map(fn ($row) => [
                'trap' => (string) $row->trap,
                'species_count' => (int) ($row->species_count ?? 0),
            ])
            ->all();
    }

    private function seedSpeciesTrapCounts(int $minCensus, int $maxCensus, string $whereRaw): array
    {
        return DB::connection('mysql2')
            ->table('fulldata as f')
            ->whereBetween('f.census', [$minCensus, $maxCensus])
            ->whereRaw($whereRaw)
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->select('f.csp')
            ->selectRaw('COUNT(DISTINCT f.trap) as trap_count')
            ->groupBy('f.csp')
            ->havingRaw('trap_count > 0')
            ->orderByDesc('trap_count')
            ->get()
            ->map(fn ($row) => [
                'csp' => $row->csp,
                'trap_count' => (int) ($row->trap_count ?? 0),
            ])
            ->all();
    }

    private function quotedCodeList(array $codes): string
    {
        return collect($codes)
            ->map(fn ($code) => DB::connection('mysql2')->getPdo()->quote((string) $code))
            ->implode(', ');
    }

    private function formatSeedDate(?string $date): string
    {
        if (! $date) {
            return '';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return $date;
        }

        return date('Y', $timestamp) . ' 年 ' . (int) date('n', $timestamp) . ' 月 ' . (int) date('j', $timestamp) . ' 日';
    }

    private function formatSeedMonth(?string $date): string
    {
        if (! $date) {
            return '';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return $date;
        }

        return date('Y', $timestamp) . ' 年 ' . (int) date('n', $timestamp) . ' 月';
    }

    private function formatSeedMonthPeriod(?string $date): string
    {
        if (! $date) {
            return '';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return $date;
        }

        $day = (int) date('j', $timestamp);
        $period = $day <= 15 ? '初' : ($day <= 25 ? '中' : '底');

        return $this->formatSeedMonth($date) . $period;
    }

    public function download(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        $dateOptions = FsSeedsDateinfo::query()
            ->select('census', 'date')
            ->whereNotNull('census')
            ->orderByDesc('census')
            ->get()
            ->map(fn ($row) => [
                'census' => $row->census,
                'date' => $row->date ?? '',
                'label' => 'census ' . $row->census . ' : ' . ($row->date ?? ''),
            ])
            ->values();

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

        return view('pages/fushan/seeds_download', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name,
            'dateOptions' => $dateOptions,
            'selectedStartCensus' => $selectedStartCensus,
            'selectedEndCensus' => $selectedEndCensus,
        ]);
    }

    public function downloadFulldata(Request $request): StreamedResponse
    {
        $validated = $this->validSeedResearchRange($request->input('start_census'), $request->input('end_census'));

        abort_if($validated === null, 422, '資料範圍不正確，請重新選擇。');

        [$minCensus, $maxCensus] = $validated;
        $excludedFulldataColumns = ['checknote', 'updated_id', 'updated_at'];
        $fulldataColumns = array_values(array_diff(
            Schema::connection('mysql2')->getColumnListing('fulldata'),
            $excludedFulldataColumns
        ));
        $dateinfoColumns = Schema::connection('mysql2')->getColumnListing('dateinfo');
        $joinedDateColumns = array_values(array_intersect(['date', 'dare', 'date1', 'year', 'month', 'period'], $dateinfoColumns));
        $headers = array_merge($fulldataColumns, $joinedDateColumns);
        $filename = 'seeds_fulldata_' . $minCensus . '_' . $maxCensus . '_' . now()->format('Ymd') . '.txt';

        return $this->streamTxt($filename, $headers, function ($handle) use ($fulldataColumns, $joinedDateColumns, $minCensus, $maxCensus) {
            $query = DB::connection('mysql2')
                ->table('fulldata as f')
                ->leftJoin('dateinfo as d', 'd.census', '=', 'f.census')
                ->whereBetween('f.census', [$minCensus, $maxCensus]);

            foreach ($fulldataColumns as $column) {
                $query->addSelect('f.' . $column);
            }

            foreach ($joinedDateColumns as $column) {
                $query->addSelect(DB::raw('d.`' . str_replace('`', '``', $column) . '` as `' . str_replace('`', '``', $column) . '`'));
            }

            foreach (['census', 'trap', 'csp', 'code'] as $column) {
                if (in_array($column, $fulldataColumns, true)) {
                    $query->orderBy('f.' . $column);
                }
            }

            $query->chunk(1000, function ($rows) use ($handle, $fulldataColumns, $joinedDateColumns) {
                foreach ($rows as $row) {
                    $values = [];

                    foreach ($fulldataColumns as $column) {
                        $values[] = $row->{$column} ?? '';
                    }

                    foreach ($joinedDateColumns as $column) {
                        $values[] = $row->{$column} ?? '';
                    }

                    fputcsv($handle, $values, "\t");
                }
            });
        });
    }

    private function streamTxt(string $filename, array $headers, callable $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writeRows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, "\t");
            $writeRows($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function unknown(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_unknown', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name


        ]);
    }

    public function unknownData(Request $request, string $unk)
    {
        abort_unless((bool) $request->user()?->is_admin, 403);

        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/seeds_unknown_data', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name,
            'unk' => $unk,
        ]);
    }

    public function updateBackData(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');
        return view('pages/fushan/seeds_updatebackdata', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->account ?? $user->name

        ]);
    }
}
