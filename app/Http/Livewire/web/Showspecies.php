<?php

namespace App\Http\Livewire\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;

use App\Models\FsBaseSpinfo;
use App\Models\FsWebPhoto;
use App\Models\FsWebDisNote;

use App\Models\FsTreeCensus1;
use App\Models\FsTreeCensus2;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus5;
use App\Models\FsTreeBase;
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
    public $leafphoto='no';

    public function mount($spcode){
        $this->treeinfo='';

        $this->photoinfo=FsWebPhoto::where('spcode', 'like', $spcode)->orderBy('type2')->get()->toArray();
        // dd($photoinfo);
        $desinfo=FsWebDisNote::where('spcode', 'like', $spcode)->orderBy('type2')->get()->toArray();

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

        $this->desinfo=$des;

        $this->speciesinfo=FsBaseSpinfo::where('spcode', 'like', $spcode)->first()->toArray();
        $this->spcode=$spcode;

        $this->countInd=FsTreeCensus4::select('base.*', 'census4.*')->join('base', 'base.tag', '=', 'census4.tag')->where('base.spcode', 'like', $spcode)->where('branch', 'like', '0')->count();
        $this->countB=FsTreeCensus4::select('base.*', 'census4.*')->join('base', 'base.tag', '=', 'census4.tag')->where('base.spcode', 'like', $spcode)->where('branch', 'not like', '0')->count();
        $this->maxDBH=FsTreeCensus4::select('base.*', 'census4.*')->join('base', 'base.tag', '=', 'census4.tag')->where('base.spcode', 'like', $spcode)->where('branch', 'like', '0')->max('dbh');
        $this->countSeeds=FsSeedsFulldata::where('sp','like', $spcode)->sum('seeds');
        $this->countFlower=FsSeedsFulldata::where('sp','like', $spcode)->where('code', 'like','6')->count();
        $this->countSeedlings=FsSeedlingData::where('csp','like', $this->speciesinfo['csp'])->whereColumn('tag', 'mtag')->sum('ind');

        // dd($this->treeinfo);
        // $this->showdata($spcode);
        $leafphotoPath='splist/leafphoto/'.$this->speciesinfo['csp'].'.jpg';

        if (file_exists(public_path($leafphotoPath))){
            $this->leafphoto='yes';
        }
        

    }

    public $censusA;

// 各次調查植株數量圖
    public function fig1data(){
        $spcode=$this->spcode;
//censusA: 活著的樹  censusR:新增的樹  censusD:死掉的樹
        $census1=FsTreeCensus1::select('base.*', 'census1.*')->join('base', 'base.tag', '=', 'census1.tag')->where('base.spcode', 'like', $spcode)->where('branch', 'like', '0')->count();

        $censusA['census1']=$census1;
        $censusR['census1']='';
        $censusD['census1']='';

        for($i=2;$i<5;$i++){
            switch ($i) {
                case '2':$table= new FsTreeCensus2; break;
                case '3':$table= new FsTreeCensus3; break;
                case '4':$table= new FsTreeCensus4; break;
                case '5':$table= new FsTreeCensus5; break;
            }

            $censusA['census'.$i]=$table::select('base.*', 'census'.$i.'.*')->join('base', 'base.tag', '=', 'census'.$i.'.tag')->where('base.spcode', 'like', $spcode)->where('status', 'not like', '0')->where('status', 'not like', '-9')->where('date', 'not like', '0000-00-00')->where('branch', 'like', '0')->count();
            $censusR['census'.$i]=$table::select('base.*', 'census'.$i.'.*')->join('base', 'base.tag', '=', 'census'.$i.'.tag')->where('base.spcode', 'like', $spcode)->where('status', 'like', '-9')->where('branch', 'like', '0')->count();
            $censusD['census'.$i]=$table::select('base.*', 'census'.$i.'.*')->join('base', 'base.tag', '=', 'census'.$i.'.tag')->where('base.spcode', 'like', $spcode)->where('status', 'like', '0')->where('date', 'not like', '0000-00-00')->where('branch', 'like', '0')->count();

        }

        // dd($censusA);

        $this->censusA=$censusA;

    $this->dispatchBrowserEvent('fig1', ['censusA'=>$censusA, 'censusR' => $censusR, 'censusD' => $censusD]);

    }

    public function groupConditions(){
        if ($this->maxDBH >100){
            $groupConditions = [
                '<5' => [0.5, 5],
                '5-10' => [5, 10],
                '10-20' => [10, 20],
                '20-50' => [20, 50],
                '50-100' => [50, 100],
                '>100' => 100
            ];
        } else if ($this->maxDBH >50){
            $groupConditions = [
                '<5' => [0.5, 5],
                '5-10' => [5, 10],
                '10-20' => [10, 20],
                '20-50' => [20, 50],
                '>50' => 50,
                
            ];
        } else if ($this->maxDBH >20){
            $groupConditions = [
                '<5' => [0.5, 5],
                '5-10' => [5, 10],
                '10-20' => [10, 20],
                '>20' => 20,
            ];
        } else if ($this->maxDBH >10){
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

        $this->groupConditions=$groupConditions;
    }
//第四次調查徑級結構
    public function fig2data(){


        $spcode=$this->spcode;
        $census4=FsTreeCensus4::select('base.*', 'census4.*')->join('base', 'base.tag', '=', 'census4.tag')->where('base.spcode', 'like', $spcode)->where('dbh', 'not like', '0')->where('branch', 'like', '0')->get()->toArray();

     // 準備分群條件
        $this->groupConditions();
        $groupConditions=$this->groupConditions;


        // 初始化統計結果陣列
        $groupedCounts = [];

        // 根據每個分群條件進行計算
        foreach ($groupConditions as $groupName => $groupRange) {
            // 使用 where 條件過濾出符合該分群條件的記錄
            $count = count(array_filter($census4, function ($item) use ($groupRange) {
                // 檢查 dbh 是否在指定範圍內
                if (is_array($groupRange)) {
                    return $item['dbh'] >= $groupRange[0] && $item['dbh'] < $groupRange[1];
                } else {
                    return $item['dbh'] > $groupRange;
                }
            }));

            // 將計算結果存入統計結果陣列
            $groupedCounts[$groupName] = $count;
        }


// 現在 $groupedCounts 就包含了每個分群的計數結果


        // dd($groupedCounts);

    $this->dispatchBrowserEvent('fig2', ['groupedCounts'=>$groupedCounts]);

    }
//第四次調查植株位置分布
    public function fig3data(){

        $spcode=$this->spcode;
        $census4A=FsTreeCensus4::select('base.*', 'census4.*')->join('base', 'base.tag', '=', 'census4.tag')->where('base.spcode', 'like', $spcode)->where('dbh', 'not like', '0')->where('branch', 'like', '0')->get()->toArray();
        // $census4D=FsTreeCensus4::select('base.*', 'census4.*')->join('base', 'base.tag', '=', 'census4.tag')->where('base.spcode', 'like', $spcode)->where('dbh', 'like', '0')->where('branch', 'like', '0')->get()->toArray();

     // 準備分群條件
        $this->groupConditions();
        $groupConditions=$this->groupConditions;

        // 初始化統計結果陣列
        $group = [];

        // 根據每個分群條件進行計算
        foreach ($groupConditions as $groupName => $groupRange) {
            // 使用 where 條件過濾出符合該分群條件的記錄
            $filteredData = array_filter($census4A, function ($item) use ($groupRange) {
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

    $this->dispatchBrowserEvent('fig3', ['census4A'=>$census4A, 'group'=>$group]);

    }

    public $timeSeries;
    public $dateSeries;
    public $dateCounts;

//開花量時間變化
//census261 / 2007.09.01 / key=60 開始為 106個網子，之前是87
    public function fig4data(){
        if ($this->timeSeries==[]){
            $this->getTimeSeries();
        }

        $flowerTraps = [];
        $flowerSeries = [];

        foreach ($this->timeSeries['6'] as $item) {
            $date = $item['date1'];
            $count = $item['count'];
            
            // 如果這個 date1 還不存在在 $groupedItems 中，則建立一個空陣列
            if (!isset($flowerTraps[$date])) {
                $flowerTraps[$date] = 0;
            }
            
            // 將 seeds 值相加到對應的 date1 中
            $flowerTraps[$date] += $count;
        }

        foreach ($this->dateSeries as $key=>$item){
            if (isset($flowerTraps[$item])){
                if($key<60){
                    $value=($flowerTraps[$item]/87/$this->dateCounts[$item])*100;
                } else {
                    $value=($flowerTraps[$item]/106/$this->dateCounts[$item])*100;
                }

                $flowerSeries[$item]=$value;
            } else {
                $flowerSeries[$item]='0';
            }
            
        }
// dd($flowerTraps);
        $this->dispatchBrowserEvent('fig4', ['flowerSeries'=>$flowerSeries, 'dateSeries'=>$this->dateSeries]);


    }
//結果量時間變化
    public function fig5data(){
        if ($this->timeSeries==[]){
            $this->getTimeSeries();
        }

        $fruitsTraps = [];
        $fruitsSeries = [];

        foreach ($this->timeSeries['1'] as $item) {
            $date = $item['date1'];
            $count = $item['seeds'];
            
            // 如果這個 date1 還不存在在 $groupedItems 中，則建立一個空陣列
            if (!isset($fruitsTraps[$date])) {
                $fruitsTraps[$date] = 0;
            }
            
            // 將 seeds 值相加到對應的 date1 中
            $fruitsTraps[$date] += $count;
        }

        foreach ($this->timeSeries['2'] as $item) {
            $date = $item['date1'];
            $count = $item['seeds'];
            
            // 如果這個 date1 還不存在在 $groupedItems 中，則建立一個空陣列
            if (!isset($fruitsTraps[$date])) {
                $fruitsTraps[$date] = 0;
            }
            
            // 將 seeds 值相加到對應的 date1 中
            $fruitsTraps[$date] += $count;
        }


        foreach ($this->dateSeries as $key=>$item){
            if (isset($fruitsTraps[$item])){
                if($key<60){
                    $value=($fruitsTraps[$item]/87)*2;
                } else {
                    $value=($fruitsTraps[$item]/106)*2;
                }

                $fruitsSeries[$item]=$value;
            } else {
                $fruitsSeries[$item]='0';
            }
            
        }
 // dd($fruitsTraps);
        $this->dispatchBrowserEvent('fig5', ['fruitsSeries'=>$fruitsSeries, 'dateSeries'=>$this->dateSeries]);

    }
//小苗數量時間變化
    //census30 / 2010-11 / key=29 開始為 106個網子，之前是87
    public function fig6data(){

        $seedlingTraps=[];
        $seedlingSeries=[];

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
        $seedlingDateSeries= FsSeedlingData::select(DB::raw("CONCAT(year, '-', month) as ym"))
            ->where('csp', $this->speciesinfo['csp'])
            ->whereColumn('tag', 'mtag')
            ->where('status', 'A')
            ->groupBy('year', 'month')
            ->pluck('ym')
            ->toArray();


        foreach ($seedlingDateSeries as $key=>$item){
            if (isset($seedlingSeries1[$item])){
                if($key<30){
                    $value=($seedlingSeries1[$item]/87)/3;
                } else {
                    $value=($seedlingSeries1[$item]/106)/3;
                }
                $seedlingSeries[$item]=$value;
            } else {
                $seedlingSeries[$item]='0';
            }
            
        }
// dd($seedlingSeries);

        $this->dispatchBrowserEvent('fig6', ['seedlingSeries'=>$seedlingSeries]);
    }

    public function getTimeSeries(){
        $timeSeries=FsSeedsFulldata::select('fulldata.*', 'dateinfo.*')->join('dateinfo','dateinfo.census','=','fulldata.census')->where('fulldata.sp','like', $this->spcode)->get()->toArray();

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
        $this->timeSeries=$groupedItems;

        $dateSeries = FsSeedsDateinfo::orderBy('census')->pluck('date1')->toArray();
        $dateCounts = collect($dateSeries)->countBy(function ($date) {
            return $date;
        })->toArray();
        $dateSeries = array_values(array_unique($dateSeries));
// dd($dateSeries);
        $this->dateCounts=$dateCounts;
        $this->dateSeries=$dateSeries;

    }

    public function render()
    {
        // $this->showdata($this->spcode);
        return view('livewire.web.showspecies');
    }
}
