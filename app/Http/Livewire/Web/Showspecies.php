<?php

namespace App\Http\Livewire\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

use App\Models\FsBaseSpinfo;
use App\Models\FsTreeCensuses;
use App\Models\Web\Photo;
use App\Models\Web\DisNote;

use App\Models\FsSeedlingData;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;

class Showspecies extends Component
{
    public $user;
    public $spcode;
    public $photoinfo;
    public $desinfo;
    public $speciesinfo;
    public $countInd;
    public $countB;
    public $maxDBH;
    public $countSeeds;
    public $countFlower;
    public $countSeedlings;
    public $leafphoto = 'no';
    public $treeinfo;
    public $groupConditions;
    public $researches = [];
    public $latestTreeCensus = 5;
    public $latestTreeCensusYear = '2024';

    public function mount($spcode)
    {
        $this->treeinfo = '';

        $photoQuery = Photo::where('spcode', $spcode);

        if (! auth()->check() && Schema::connection('mysql_web')->hasColumn('photo', 'is_public')) {
            $photoQuery->where('is_public', true);
        }

        $this->photoinfo = $photoQuery->orderBy('type2')->get()->toArray();
        // dd($photoinfo);
        $desinfo = auth()->check()
            ? DisNote::where('spcode', $spcode)->orderBy('type2')->get()->toArray()
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

        $this->speciesinfo = FsBaseSpinfo::where('spcode', $spcode)->first()->toArray();
        $this->spcode = $spcode;
        $this->researches = $this->speciesResearchFlags($spcode);
        [$this->latestTreeCensus, $this->latestTreeCensusYear] = $this->latestTreeCensusInfo();

        $latestTreeTable = $this->treeCensusTable($this->latestTreeCensus);
        $latestTreeBase = $this->treeCensusQuery($this->latestTreeCensus)
            ->where('base.spcode', $spcode);

        $this->countInd = (clone $latestTreeBase)->where($latestTreeTable . '.branch', '0')->count();
        $this->countB = (clone $latestTreeBase)->where($latestTreeTable . '.branch', '!=', '0')->count();
        $this->maxDBH = (clone $latestTreeBase)->where($latestTreeTable . '.branch', '0')->max($latestTreeTable . '.dbh');
        $this->countSeeds = FsSeedsFulldata::where('sp', $spcode)->sum('seeds');
        $this->countFlower = FsSeedsFulldata::where('sp', $spcode)->where('code', '6')->count();
        $this->countSeedlings = FsSeedlingData::where('csp', $this->speciesinfo['csp'])->whereColumn('tag', 'mtag')->sum('ind');

        // dd($this->treeinfo);
        // $this->showdata($spcode);
        $leafphotoPath = 'FDPfiles/splist/leafphoto/' . $this->speciesinfo['csp'] . '.jpg';

        if (file_exists(public_path($leafphotoPath))) {
            $this->leafphoto = 'yes';
        }
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

    private function speciesResearchFlags(string $spcode): array
    {
        $fallback = [
            'tree' => (int) ($this->speciesinfo['tree'] ?? 0),
            'seed' => (int) ($this->speciesinfo['seed'] ?? 0),
            'seedling' => (int) ($this->speciesinfo['seedling'] ?? 0),
            'mortality' => 0,
        ];

        if (!Schema::connection('mysql4')->hasTable('species_research_links')) {
            return $fallback;
        }

        $linkedCodes = DB::connection('mysql4')
            ->table('species_research_links')
            ->where('spcode', $spcode)
            ->pluck('research_code')
            ->all();

        if ($linkedCodes === []) {
            return $fallback;
        }

        return collect($linkedCodes)
            ->mapWithKeys(fn ($researchCode) => [$researchCode => 1])
            ->union($fallback)
            ->all();
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
                    SUM(CASE WHEN {$table}.status != '0' AND {$table}.status != '-9' AND {$table}.date != '0000-00-00' THEN 1 ELSE 0 END) as alive_count,
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
    public function fig6data()
    {

        $seedlingTraps = [];
        $seedlingSeries = [];

        $seedlingSeries1 = FsSeedlingData::select(DB::raw('SUM(ind) as sum'), 'year', 'month')
            ->where('csp', $this->speciesinfo['csp'])
            ->whereColumn('tag', 'mtag')
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
            ->whereColumn('tag', 'mtag')
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
