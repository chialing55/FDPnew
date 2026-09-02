<?php

namespace App\Http\Livewire\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Renderless;
use Illuminate\Support\Facades\Schema;

use App\Models\PlantCatalog\SiteSpecies;
use App\Models\FsTreeCensuses;
use App\Models\Web\Photo;
use App\Models\Web\DisNote;
use App\Models\Web\Page;
use App\Support\Web\RelatedTagStyle;

use App\Models\FsSeedlingData;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;

class Showspecies extends Component
{
    public $user;
    public $spcode;
    public $catalogCode;
    public $photoinfo;
    public $desinfo;
    public $speciesinfo;
    public $countInd;
    public $countB;
    public $maxDBH;
    public $countSeeds;
    public $countFlower;
    public $countSeedlings;
    public $countNjsSeedlings = 0;
    public $countSsInd = 0;
    public $countSsB = 0;
    public $maxSsDBH;
    public $leafphoto = 'no';
    public $featuredPhoto;
    public $treeinfo;
    public $groupConditions;
    public $researches = [];
    public $researchSites = [];
    public $researchesBySite = [];
    public $relatedTagIds = [];
    public $latestTreeCensus = 5;
    public $latestTreeCensusYear = '2024';

    public function mount($spcode)
    {
        $this->treeinfo = '';

        // The public URL uses taiwan_checklist.code.  Legacy Fushan detail
        // tables, photos and charts still use that site's local spcode.
        $catalogSpecies = SiteSpecies::query()
            ->withChecklistTaxonomy()
            ->where(function ($query) use ($spcode): void {
                $query->where('site_species.code', $spcode)
                    ->orWhere('site_species.spcode', $spcode);
            })
            ->orderByRaw("site_species.site = 'fushan' DESC")
            ->firstOrFail();
        $fushanSpecies = SiteSpecies::query()
            ->fushan()
            ->withChecklistTaxonomy()
            ->where('site_species.code', $catalogSpecies->code)
            ->first();
        $detailSpecies = $fushanSpecies ?? $catalogSpecies;
        $detailSpcode = $fushanSpecies?->spcode ?? $catalogSpecies->spcode;
        $this->speciesinfo = $detailSpecies->toArray();
        $this->spcode = $detailSpcode;
        $this->catalogCode = $catalogSpecies->code;

        // Photos are linked to the shared TAI2/checklist code. The legacy
        // site spcode is retained on each row only for its physical path.
        $photoQuery = Photo::where(function ($query) use ($detailSpcode): void {
            $query->where('code', $this->catalogCode)
                // Keep unmatched legacy rows visible until they are curated.
                ->orWhere(function ($legacy) use ($detailSpcode): void {
                    $legacy->where('spcode', $detailSpcode)
                        ->where(function ($missingCode): void {
                            $missingCode->whereNull('code')->orWhere('code', '');
                        });
                });
        });

        if (! auth()->check() && Schema::connection('mysql_web')->hasColumn('photo', 'is_public')) {
            $photoQuery->where('is_public', true);
        }

        $this->photoinfo = $photoQuery->orderByDesc('is_featured')->orderBy('type2')->get()->toArray();
        $this->featuredPhoto = collect($this->photoinfo)
            ->first(fn (array $photo): bool => (int) ($photo['is_featured'] ?? 0) === 1);
        $this->leafphoto = $this->featuredPhoto ? 'yes' : 'no';
        $this->photoinfo = collect($this->photoinfo)
            ->reject(fn (array $photo): bool => (int) ($photo['is_featured'] ?? 0) === 1)
            ->values()
            ->all();
        // dd($photoinfo);
        $desinfo = auth()->check()
            ? DisNote::where('spcode', $detailSpcode)->orderBy('type2')->get()->toArray()
            : [];

        $des = [];

        foreach ($desinfo as $data) {
            if (!isset($des[$data['type']])) {
                $des[$data['type']] = [];
            }

            $des[$data['type']][] = $data['note'];
        }

        // 將 $des 轉換為索引式陣列
        // $des = array_values($des);

        // dd($this->photoinfo);

        $this->desinfo = $des;

        [$this->researches, $this->researchSites, $this->researchesBySite] = $this->speciesResearchLabels(
            $catalogSpecies->code,
            $detailSpcode
        );
        $this->relatedTagIds = $this->loadRelatedTagIds();
        [$this->latestTreeCensus, $this->latestTreeCensusYear] = $this->latestTreeCensusInfo();

        $latestTreeTable = $this->treeCensusTable($this->latestTreeCensus);
        $latestTreeBase = $this->treeCensusQuery($this->latestTreeCensus)
            ->where('base.spcode', $detailSpcode);

        $this->countInd = (clone $latestTreeBase)->where($latestTreeTable.'.branch', '0')->count();
        $this->countB = (clone $latestTreeBase)->where($latestTreeTable.'.branch', '!=', '0')->count();
        $this->maxDBH = (clone $latestTreeBase)->where($latestTreeTable.'.branch', '0')->max($latestTreeTable.'.dbh');
        $this->countSeeds = FsSeedsFulldata::where('sp', $detailSpcode)->sum('seeds');
        $this->countFlower = FsSeedsFulldata::where('sp', $detailSpcode)->where('code', '6')->count();
        $this->countSeedlings = FsSeedlingData::where('csp', $this->speciesinfo['csp'])
            ->where('sprout', 'FALSE')
            ->whereNotNull('mtag')
            ->where('mtag', '!=', '')
            ->distinct()
            ->count('mtag');
        $this->countNjsSeedlings = $this->nanjenshanSeedlingCount();
        $this->loadShoushanTreeSummary();

        // dd($this->treeinfo);
        // $this->showdata($spcode);
    }

    private function treeCensusYears(): array
    {
        if (Schema::connection('mysql1')->hasTable('censuses')) {
            $yearColumn = Schema::connection('mysql1')->hasColumn('censuses', 'year') ? 'year' : 'survey_year';

            return FsTreeCensuses::query()
                ->orderBy('census')
                ->pluck($yearColumn, 'census')
                ->map(fn ($year) => (string) $year)
                ->all();
        }

        return [
            1 => '2004',
            2 => '2009',
            3 => '2014',
            4 => '2019',
            5 => '2024',
        ];
    }

    private function latestTreeCensusInfo(): array
    {
        $censuses = $this->treeCensusYears();

        krsort($censuses);

        foreach ($censuses as $census => $year) {
            if (Schema::connection('mysql1')->hasTable($this->treeCensusTable((int) $census))) {
                return [(int) $census, (string) $year];
            }
        }

        return [5, '2024'];
    }

    private function treeCensusTable(int $census): string
    {
        return 'census' . $census;
    }

    private function treeCensusQuery(int $census)
    {
        $table = $this->treeCensusTable($census);

        return DB::connection('mysql1')
            ->table($table)
            ->join('base', 'base.tag', '=', $table . '.tag');
    }

    private function speciesResearchLabels(string $catalogCode, string $fallbackSpcode): array
    {
        $fallback = [
            'tree' => (int) ($this->speciesinfo['tree'] ?? 0),
            'seed' => (int) ($this->speciesinfo['seed'] ?? 0),
            'seedling' => (int) ($this->speciesinfo['seedling'] ?? 0),
            'mortality' => 0,
        ];

        if (!Schema::connection('plant_catalog')->hasTable('species_research_links')) {
            return [$fallback, ['fushan'], ['fushan' => $fallback]];
        }

        $links = DB::connection('plant_catalog')
            ->table('site_species as species')
            ->join('species_research_links as research', function ($join): void {
                $join->on('research.site', '=', 'species.site')
                    ->on('research.spcode', '=', 'species.spcode');
            })
            ->where('species.code', $catalogCode)
            ->whereIn('species.site', ['fushan', 'nanjenshan', 'shoushan'])
            ->distinct()
            ->get(['species.site', 'research.research_code']);

        if ($links->isEmpty()) {
            $sites = $fallbackSpcode !== '' ? ['fushan'] : [];

            return [$fallback, $sites, $sites !== [] ? ['fushan' => $fallback] : []];
        }

        $researches = $links->pluck('research_code')
            ->mapWithKeys(fn ($researchCode) => [$researchCode => 1])
            ->union($fallback)
            ->all();

        $siteOrder = array_flip(['fushan', 'nanjenshan', 'shoushan']);
        $sites = $links->pluck('site')->unique()
            ->sortBy(fn (string $site): int => $siteOrder[$site] ?? 99)
            ->values()->all();

        $researchesBySite = $links->groupBy('site')
            ->map(fn ($siteLinks): array => $siteLinks->pluck('research_code')
                ->mapWithKeys(fn ($researchCode): array => [$researchCode => 1])
                ->all())
            ->all();

        return [$researches, $sites, $researchesBySite];
    }

    private function loadRelatedTagIds(): array
    {
        return Page::query()
            ->with(['site:id,page_id', 'subject:id,page_id'])
            ->whereIn('slug', [
                'sites/fushan',
                'sites/nanjenshan',
                'sites/shoushan',
                'subjects/long-term-tree-dynamics',
                'subjects/long-term-seedling-dynamics',
                'subjects/plant-reproduction-phenology',
            ])
            ->get()
            ->mapWithKeys(fn (Page $page): array => [
                $page->slug => $page->site?->id ?? $page->subject?->id,
            ])
            ->filter()
            ->all();
    }

    private function nanjenshanSeedlingCount(): int
    {
        $nanjenshanSpcode = DB::connection('plant_catalog')
            ->table('site_species')
            ->where('site', 'nanjenshan')
            ->where('code', $this->catalogCode)
            ->value('spcode');

        if (!$nanjenshanSpcode) {
            return 0;
        }

        return DB::connection('njs_seedling')
            ->table('seedling_individuals as individuals')
            ->join('seedling_records as records', 'records.tag', '=', 'individuals.tag')
            ->where('individuals.spcode', $nanjenshanSpcode)
            ->whereNotNull('individuals.tag')
            ->where('individuals.tag', '!=', '')
            ->distinct()
            ->count('individuals.tag');
    }

    private function shoushanSpcode(): ?string
    {
        return DB::connection('plant_catalog')
            ->table('site_species')
            ->where('site', 'shoushan')
            ->where('code', $this->catalogCode)
            ->value('spcode');
    }

    private function shoushanTreeQuery(string $table)
    {
        return DB::connection('mysql5')
            ->table($table.' as census')
            ->join('1ha_base_2024 as base', 'base.tag', '=', 'census.tag')
            ->where('base.spcode', $this->shoushanSpcode())
            ->where('base.deleted_at', '');
    }

    private function loadShoushanTreeSummary(): void
    {
        if (!$this->shoushanSpcode()) {
            return;
        }

        $latest = $this->shoushanTreeQuery('1ha_data_2024')
            ->where('census.deleted_at', '')
            ->where('census.date', '!=', '0000-00-00');

        $this->countSsInd = (clone $latest)
            ->where('census.branch', 0)
            ->whereIn('census.status', ['', '-9', '-2', '-3'])
            ->count();
        $this->countSsB = (clone $latest)
            ->where('census.branch', '!=', 0)
            ->whereIn('census.status', ['', '-9'])
            ->count();
        $this->maxSsDBH = (clone $latest)
            ->where('census.branch', 0)
            ->whereIn('census.status', ['', '-9', '-2', '-3'])
            ->max('census.dbh') ?? 0;
    }

    public function tagStyle(string $type, string $slug, int $fallbackId): string
    {
        return RelatedTagStyle::for($type, (int) ($this->relatedTagIds[$slug] ?? $fallbackId));
    }

    public function tagClasses(): string
    {
        return RelatedTagStyle::classes();
    }

    public $censusA;

    public function loadChartData()
    {
        if ($this->countInd > 0) {
            $this->fig1data();
            $this->fig2data();
            $this->fig3data();
        }

        if ($this->countFlower > 0) {
            $this->fig4data();
        }

        if ($this->countSeeds > 0) {
            $this->fig5data();
        }

        if ($this->countSeedlings > 0) {
            $this->fig6data();
        }
    }

    // 各次調查植株數量圖
    #[Renderless]
    public function fig1data()
    {
        $spcode = $this->spcode;
        $censusA = [];
        $censusR = [];
        $censusD = [];

        //censusA: 活著的樹  censusR:新增的樹  censusD:死掉的樹
        foreach ($this->treeCensusYears() as $i => $year) {
            $table = $this->treeCensusTable((int) $i);

            if (!Schema::connection('mysql1')->hasTable($table)) {
                continue;
            }

            $censusQuery = $this->treeCensusQuery((int) $i)
                ->where('base.spcode', $spcode)
                ->where($table . '.branch', '0');

            if ((int) $i === 1) {
                $censusA[$year] = (clone $censusQuery)->count();
                $censusR[$year] = '';
                $censusD[$year] = '';
                continue;
            }

            $counts = $censusQuery
                ->selectRaw("
                    SUM(CASE
                        WHEN {$table}.status != '0'
                            AND {$table}.status != '-9'
                            AND {$table}.date != '0000-00-00'
                        THEN 1 ELSE 0
                    END) as alive_count,
                    SUM(CASE WHEN {$table}.status = '-9' THEN 1 ELSE 0 END) as recruit_count,
                    SUM(CASE WHEN {$table}.status = '0' AND {$table}.date != '0000-00-00' THEN 1 ELSE 0 END) as dead_count
                ")
                ->first();

            $censusA[$year] = (int) ($counts->alive_count ?? 0);
            $censusR[$year] = (int) ($counts->recruit_count ?? 0);
            $censusD[$year] = (int) ($counts->dead_count ?? 0);
        }

        // dd($censusA);

        $this->censusA = $censusA;

        //$this->dispatchBrowserEvent('fig1', ['censusA'=>$censusA, 'censusR' => $censusR, 'censusD' => $censusD]);
        $this->dispatch(
            'fig1',
            censusA: $censusA,
            censusR: $censusR,
            censusD: $censusD
        );
    }

    public function groupConditions()
    {
        if ($this->maxDBH > 100) {
            $groupConditions = [
                '<5' => [0.5, 5],
                '5-10' => [5, 10],
                '10-20' => [10, 20],
                '20-50' => [20, 50],
                '50-100' => [50, 100],
                '>100' => 100
            ];
        } else if ($this->maxDBH > 50) {
            $groupConditions = [
                '<5' => [0.5, 5],
                '5-10' => [5, 10],
                '10-20' => [10, 20],
                '20-50' => [20, 50],
                '>50' => 50,

            ];
        } else if ($this->maxDBH > 20) {
            $groupConditions = [
                '<5' => [0.5, 5],
                '5-10' => [5, 10],
                '10-20' => [10, 20],
                '>20' => 20,
            ];
        } else if ($this->maxDBH > 10) {
            $groupConditions = [
                '<2' => [0.5, 2],
                '2-5' => [2, 5],
                '5-10' => [5, 10],
                '>10' => 10,
            ];
        } else {
            $groupConditions = [
                '<2' => [0.5, 2],
                '2-5' => [2, 5],
                '>5' => 5,
            ];
        }

        $this->groupConditions = $groupConditions;
    }
    // 最新一次調查徑級結構
    #[Renderless]
    public function fig2data()
    {


        $spcode = $this->spcode;
        // 準備分群條件
        $this->groupConditions();
        $groupConditions = $this->groupConditions;

        $latestTreeTable = $this->treeCensusTable($this->latestTreeCensus);
        $latestCensusQuery = $this->treeCensusQuery($this->latestTreeCensus)
            ->where('base.spcode', $spcode)
            ->where($latestTreeTable . '.dbh', '!=', '0')
            ->where($latestTreeTable . '.branch', '0');

        // 初始化統計結果陣列
        $groupedCounts = [];

        // 根據每個分群條件進行計算
        foreach ($groupConditions as $groupName => $groupRange) {
            $groupedCounts[$groupName] = $this->applyDbhGroup(clone $latestCensusQuery, $groupRange, $latestTreeTable . '.dbh')->count();
        }


        // 現在 $groupedCounts 就包含了每個分群的計數結果


        // dd($groupedCounts);

        // $this->dispatchBrowserEvent('fig2', ['groupedCounts'=>$groupedCounts]);
        $this->dispatch(
            'fig2',
            groupedCounts: $groupedCounts
        );
    }
    // 最新一次調查植株位置分布
    #[Renderless]
    public function fig3data()
    {

        $spcode = $this->spcode;
        $latestTreeTable = $this->treeCensusTable($this->latestTreeCensus);
        $latestCensusA = $this->treeCensusQuery($this->latestTreeCensus)
            ->select(
                $latestTreeTable . '.tag',
                $latestTreeTable . '.dbh',
                'base.plotx',
                'base.ploty',
                'base.qx',
                'base.qy',
                'base.sqx',
                'base.sqy'
            )
            ->where('base.spcode', $spcode)
            ->where($latestTreeTable . '.dbh', '!=', '0')
            ->where($latestTreeTable . '.branch', '0')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toArray();

        // 準備分群條件
        $this->groupConditions();
        $groupConditions = $this->groupConditions;

        // 初始化統計結果陣列
        $group = [];

        // 根據每個分群條件進行計算
        foreach ($groupConditions as $groupName => $groupRange) {
            // 使用 where 條件過濾出符合該分群條件的記錄
            $filteredData = array_filter($latestCensusA, function ($item) use ($groupRange) {
                // 檢查 dbh 是否在指定範圍內
                if (is_array($groupRange)) {
                    return $item['dbh'] >= $groupRange[0] && $item['dbh'] < $groupRange[1];
                } else {
                    return $item['dbh'] > $groupRange;
                }
            });

            // 將過濾後的記錄附加到統計結果陣列中
            $group[$groupName] = array_values($filteredData);
        }
        // dd($group);

        // 現在 $group 就包含了每個分群的記錄

        // 將結果傳遞給前端
        $this->dispatch(
            'fig3',
            group: $group
        );
    }

    public $timeSeries;
    public $dateSeries;
    public $dateCounts;

    //開花量時間變化
    //census261 / 2007.09.01 / key=60 開始為 106個網子，之前是87
    #[Renderless]
    public function fig4data()
    {
        if (empty($this->timeSeries)) {
            $this->getTimeSeries();
        }

        $flowerTraps = [];
        $flowerSeries = [];

        foreach (($this->timeSeries['6'] ?? []) as $item) {
            $date = $item['date1'];
            $count = $item['count'];

            // 如果這個 date1 還不存在在 $groupedItems 中，則建立一個空陣列
            if (!isset($flowerTraps[$date])) {
                $flowerTraps[$date] = 0;
            }

            // 將 seeds 值相加到對應的 date1 中
            $flowerTraps[$date] += $count;
        }

        foreach ($this->dateSeries as $key => $item) {
            if (isset($flowerTraps[$item])) {
                if ($key < 60) {
                    $value = ($flowerTraps[$item] / 87 / $this->dateCounts[$item]) * 100;
                } else {
                    $value = ($flowerTraps[$item] / 106 / $this->dateCounts[$item]) * 100;
                }

                $flowerSeries[$item] = $value;
            } else {
                $flowerSeries[$item] = '0';
            }
        }
        // dd($flowerTraps);
        // /$this->dispatchBrowserEvent('fig4', ['flowerSeries' => $flowerSeries, 'dateSeries' => $this->dateSeries]);
        $this->dispatch(
            'fig4',
            flowerSeries: $flowerSeries
        );
    }
    //結果量時間變化
    #[Renderless]
    public function fig5data()
    {
        if (empty($this->timeSeries)) {
            $this->getTimeSeries();
        }

        $fruitsTraps = [];
        $fruitsSeries = [];

        foreach (($this->timeSeries['1'] ?? []) as $item) {
            $date = $item['date1'];
            $count = $item['seeds'];

            // 如果這個 date1 還不存在在 $groupedItems 中，則建立一個空陣列
            if (!isset($fruitsTraps[$date])) {
                $fruitsTraps[$date] = 0;
            }

            // 將 seeds 值相加到對應的 date1 中
            $fruitsTraps[$date] += $count;
        }

        foreach (($this->timeSeries['2'] ?? []) as $item) {
            $date = $item['date1'];
            $count = $item['seeds'];

            // 如果這個 date1 還不存在在 $groupedItems 中，則建立一個空陣列
            if (!isset($fruitsTraps[$date])) {
                $fruitsTraps[$date] = 0;
            }

            // 將 seeds 值相加到對應的 date1 中
            $fruitsTraps[$date] += $count;
        }


        foreach ($this->dateSeries as $key => $item) {
            if (isset($fruitsTraps[$item])) {
                if ($key < 60) {
                    $value = ($fruitsTraps[$item] / 87) * 2;
                } else {
                    $value = ($fruitsTraps[$item] / 106) * 2;
                }

                $fruitsSeries[$item] = $value;
            } else {
                $fruitsSeries[$item] = '0';
            }
        }
        // dd($fruitsTraps);
        //  $this->dispatchBrowserEvent('fig5', ['fruitsSeries' => $fruitsSeries, 'dateSeries' => $this->dateSeries]);
        $this->dispatch(
            'fig5',
            fruitsSeries: $fruitsSeries
        );
    }
    //小苗數量時間變化
    //census30 / 2010-11 / key=29 開始為 106個網子，之前是87
    #[Renderless]
    public function fig6data()
    {

        $seedlingTraps = [];
        $seedlingSeries = [];

        $seedlingSeries1 = FsSeedlingData::select(DB::raw('SUM(ind) as sum'), 'year', 'month')
            ->where('csp', $this->speciesinfo['csp'])
            ->where('sprout', 'FALSE')
            ->where('status', 'A')
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(function ($item) {
                // 合并 year 和 month 字段为一个字段 ym
                $ym = $item->year . '-' . $item->month;
                // 以 ym 为键，sum 为值构建新数组
                return [$ym => $item->sum];
            })
            ->toArray();
        $seedlingDateSeries = FsSeedlingData::select(DB::raw("CONCAT(year, '-', month) as ym"))
            ->where('csp', $this->speciesinfo['csp'])
            ->where('sprout', 'FALSE')
            ->where('status', 'A')
            ->groupBy('year', 'month')
            ->pluck('ym')
            ->toArray();


        foreach ($seedlingDateSeries as $key => $item) {
            if (isset($seedlingSeries1[$item])) {
                if ($key < 30) {
                    $value = ($seedlingSeries1[$item] / 87) / 3;
                } else {
                    $value = ($seedlingSeries1[$item] / 106) / 3;
                }
                $seedlingSeries[$item] = $value;
            } else {
                $seedlingSeries[$item] = '0';
            }
        }
        // dd($seedlingSeries);

        $this->dispatch(
            'fig6',
            seedlingSeries: $seedlingSeries
        );
    }

    #[Renderless]
    public function fig7data(): void
    {
        $nanjenshanSpcode = DB::connection('plant_catalog')
            ->table('site_species')
            ->where('site', 'nanjenshan')
            ->where('code', $this->catalogCode)
            ->value('spcode');

        if (!$nanjenshanSpcode) {
            $this->dispatch('fig7', seedlingSeries: []);

            return;
        }

        $quadratArea = DB::connection('njs_seedling')->table('quadrats')->count();

        $seedlingSeries = DB::connection('njs_seedling')
            ->table('seedling_records as records')
            ->join('seedling_individuals as individuals', 'individuals.tag', '=', 'records.tag')
            ->join('censuses', 'censuses.census', '=', 'records.census')
            ->where('individuals.spcode', $nanjenshanSpcode)
            ->whereIn('records.status', ['A', 'R'])
            ->groupBy('censuses.census', 'censuses.ym')
            ->orderBy('censuses.census')
            ->selectRaw('censuses.ym, COUNT(DISTINCT records.tag) as total')
            ->get()
            ->mapWithKeys(fn ($row): array => [
                str_replace('/', '-', $row->ym) => $quadratArea > 0
                    ? round((int) $row->total / $quadratArea, 4)
                    : 0,
            ])
            ->all();

        $this->dispatch('fig7', seedlingSeries: $seedlingSeries);
    }

    #[Renderless]
    public function fig8data(): void
    {
        $count2015 = $this->shoushanTreeQuery('1ha_data_2015')
            ->where('census.deleted_at', '')
            ->where('census.branch', 0)
            ->whereIn('census.status', ['', '-2', '-3'])
            ->count();
        $latest = $this->shoushanTreeQuery('1ha_data_2024')
            ->where('census.deleted_at', '')
            ->where('census.branch', 0);

        $this->dispatch('fig8',
            censusA: [
                '2015' => $count2015,
                '2024' => (clone $latest)
                    ->whereIn('census.status', ['', '-2', '-3'])
                    ->where('census.date', '!=', '0000-00-00')
                    ->count(),
            ],
            censusR: ['2015' => 0, '2024' => (clone $latest)
                ->where('census.status', '-9')
                ->where('census.date', '!=', '0000-00-00')
                ->count()],
            censusD: ['2015' => 0, '2024' => (clone $latest)
                ->where('census.status', '0')
                ->where('census.date', '!=', '0000-00-00')
                ->count()],
        );
    }

    #[Renderless]
    public function fig9data(): void
    {
        $rows = $this->shoushanTreeQuery('1ha_data_2024')
            ->where('census.deleted_at', '')
            ->where('census.branch', 0)
            ->whereIn('census.status', ['', '-9', '-2', '-3'])
            ->where('census.date', '!=', '0000-00-00')
            ->where('census.dbh', '>', 0)
            ->selectRaw("CASE
                WHEN census.dbh < 5 THEN '<5'
                WHEN census.dbh < 10 THEN '5-10'
                WHEN census.dbh < 20 THEN '10-20'
                ELSE '>20'
            END AS dbh_class, COUNT(*) AS total")
            ->groupBy('dbh_class')
            ->get()
            ->pluck('total', 'dbh_class');

        $groupedCounts = collect(['<5', '5-10', '10-20', '>20'])
            ->mapWithKeys(fn (string $class): array => [$class => (int) ($rows[$class] ?? 0)])
            ->all();

        $this->dispatch('fig9', groupedCounts: $groupedCounts);
    }

    #[Renderless]
    public function fig10data(): void
    {
        $points = $this->shoushanTreeQuery('1ha_data_2024')
            ->where('census.deleted_at', '')
            ->where('census.branch', 0)
            ->whereIn('census.status', ['', '-9', '-2', '-3'])
            ->where('census.date', '!=', '0000-00-00')
            ->orderBy('base.tag')
            ->get(['base.tag', 'base.plotx', 'base.ploty', 'census.dbh'])
            ->map(fn ($row): array => [
                'x' => (float) $row->plotx,
                'y' => (float) $row->ploty,
                'tag' => trim((string) $row->tag),
                'dbh' => (float) $row->dbh,
            ])
            ->all();

        $this->dispatch('fig10', points: $points);
    }

    public function getTimeSeries()
    {
        $timeSeries = FsSeedsFulldata::select(
                'fulldata.code',
                'dateinfo.date1',
                DB::raw('SUM(fulldata.seeds) as seeds'),
                DB::raw('COUNT(*) as count'),
                DB::raw('MIN(dateinfo.census) as first_census')
            )
            ->join('dateinfo', 'dateinfo.census', '=', 'fulldata.census')
            ->where('fulldata.sp', $this->spcode)
            ->whereIn('fulldata.code', ['1', '2', '6'])
            ->groupBy('fulldata.code', 'dateinfo.date1')
            ->orderBy('first_census')
            ->get()
            ->toArray();

        $groupedItems = [];

        foreach ($timeSeries as $item) {
            $code = $item['code'];
            // 如果這個 code 還不存在在 $groupedItems 中，則建立一個空陣列
            if (!isset($groupedItems[$code])) {
                $groupedItems[$code] = [];
            }
            // 將項目添加到對應的 code 中
            $groupedItems[$code][] = $item;
        }
        // dd($groupedItems);
        $this->timeSeries = $groupedItems;

        $dateSeries = FsSeedsDateinfo::orderBy('census')->pluck('date1')->toArray();
        $dateCounts = collect($dateSeries)->countBy(function ($date) {
            return $date;
        })->toArray();
        $dateSeries = array_values(array_unique($dateSeries));
        // dd($dateSeries);
        $this->dateCounts = $dateCounts;
        $this->dateSeries = $dateSeries;
    }

    private function applyDbhGroup($query, $groupRange, string $dbhColumn = 'dbh')
    {
        if (is_array($groupRange)) {
            return $query->where($dbhColumn, '>=', $groupRange[0])->where($dbhColumn, '<', $groupRange[1]);
        }

        return $query->where($dbhColumn, '>', $groupRange);
    }

    public function render()
    {
        // $this->showdata($this->spcode);
        return view('livewire.web.showspecies');
    }
}
