<?php

namespace App\Http\Livewire\Fushan;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;
use App\Services\SeedsDateinfoSyncService;

use App\Jobs\SeedsAddButton;

class SeedsShowentry extends Component
{

    public $user;
    public $date;
    public $census;
    public $census2;
    public $census2date=[];
    public $entry;
    public $thiscensus;
    public $dateinfo;
    public $isAdmin = false;
    public $skipnote = '';

    public function mount(){
        $this->isAdmin = (bool) (Auth::user()?->is_admin ?? false);
        $this->refreshCensusState();
    }

    private function actorAccount(): string
    {
        $user = Auth::user();

        return (string) ($this->user ?: ($user?->account ?? $user?->name ?? 'system'));
    }

    private function refreshCensusState(): void
    {
        $maxDateCensus = (int) (FsSeedsDateinfo::query()->max('census') ?? 0);
        $this->census = $maxDateCensus + 1;

        $maxImportedCensus = (int) (FsSeedsFulldata::query()->max('census') ?? 0);
        $pendingDate = FsSeedsDateinfo::query()
            ->where('census', '>', $maxImportedCensus)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'skipped');
            })
            ->orderBy('census')
            ->first();

        if ($pendingDate) {
            $this->census2 = (int) $pendingDate->census;
            $this->census2date = $pendingDate->toArray();
        } else {
            $this->census2 = $this->census;
            $this->census2date = ['date' => ''];
        }

        $this->dateinfo = FsSeedsDateinfo::query()->orderBy('census', 'desc')->take(5)->get()->toArray();

        $entrytable = FsSeedsRecord1::query()->get()->toArray();
        if (count($entrytable) > 0) {
            $this->entry = 'y';
            $this->thiscensus = $entrytable[0]['census'];
        } else {
            $this->entry = '';
            $this->thiscensus = null;
        }
    }

    public $note='';
    public $person1='';
    public $person2='';
    public $person3='';
    public $submitformnote='';

//輸入要輸入資料的周次資訊
    public function submitForm(Request $request){
        $user = $this->actorAccount();
        $pass='yes';

        if ($this->date!=''){

            //check
            $date1=explode('-', $this->date);
            $year=$date1[0];
            $thisyear=date('Y');
            if ($year!=$thisyear && $year!=($thisyear-1)){
                $pass='no';
            }

            $pervdate=FsSeedsDateinfo::query()->where('census', 'like', ($this->census-1))->get()->toArray();
            $pdate=Carbon::parse($pervdate[0]['date']);
            $ndate=Carbon::parse($this->date);
            $interval = $pdate->diffInDays($ndate);
            if ($interval<5 || $interval>10){
                $pass='no';
            }            

            if ($pass=='no'){
                $this->submitformnote='日期輸入錯誤';

            } else {
                $datecheck=$this->dateinfo($this->census, $this->date, $this->person1, $this->person2, $this->person3);

                $this->census2date['date']=$this->date;

                $inlist['year']=$datecheck['year'];
                $inlist['month']=$datecheck['month'];
                $inlist['date1']=$datecheck['date1'];
                $inlist['period']=$datecheck['period'];
                $inlist['workers']=$datecheck['workers'];

                $additionalData=['date'=>$this->date, 'census'=>$this->census,  'updated_id' => $user,'note'=>$this->note, 'status' => '', 'updated_at' => date("Y-m-d H:i:s")];
                $inlist = array_merge($inlist, $additionalData);
                FsSeedsDateinfo::insert($inlist);
                SeedsDateinfoSyncService::sync();

                $this->refreshCensusState();
                $this->createTable($this->census2);
            }

        }
    }

    public function skipCurrentCensus()
    {
        abort_unless((bool) (Auth::user()?->is_admin ?? false), 403);

        if ($this->entry !== '' || !isset($this->census2date['census'])) {
            $this->skipnote = '目前沒有可略過的調查資料。';
            return;
        }

        $census = (int) $this->census2date['census'];
        $hasImportedData = FsSeedsFulldata::query()->where('census', $census)->exists();
        $hasWorkData = FsSeedsRecord1::query()->exists();

        if ($hasImportedData || $hasWorkData) {
            $this->skipnote = 'record1 表已有資料，不能略過此周輸入。';
            return;
        }

        FsSeedsDateinfo::query()
            ->where('census', $census)
            ->update([
                'status' => 'skipped',
                'updated_id' => $this->actorAccount(),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->skipnote = '已略過第 ' . $census . ' 次調查。';
        $this->refreshCensusState();
    }

//處理日期欄位
    public function dateinfo($census, $date, $person1, $person2, $person3){
            $date1=explode('-', $date);
            $year=$date1[0];
            $month=$date1[1];
            $day=$date1[2];

//種子雨收集日期間隔(date1)判斷
//如果這次的收集月份與上次不同，取得上次日期資料，與這次的日期資料相減，獲得$interval
//如果本次日期($day)<$interval/2，則將本次資料歸為前一個月
//若($day)=$interval/2，則需判斷前次月份和前前次月份是否皆已達五次(最多為五次)，若否，則歸入前月，若是，則維持為本月
//若($day)>$interval/2，則屬於本月
//
//


            $pcensus=FsSeedsDateinfo::query()->where('census', 'like', $census-1)->get()->toArray();
            if ($pcensus[0]['month']!=$month){
                $pdate=Carbon::parse($pcensus[0]['date']);
                $ndate=Carbon::parse($date);
                $interval = $pdate->diffInDays($ndate);
                if ($day<($interval/2)){
                    $month=$month-1;
                    if ($month==0){
                        $month='12';
                        $year=$year-1;
                    }
                } elseif($day==($interval/2)){
                    $p1=FsSeedsDateinfo::query()->where('month', 'like', $pcensus[0]['month']-1)->count();
                    $p2=FsSeedsDateinfo::query()->where('month', 'like', $pcensus[0]['month']-2)->count();
                    if ($p1=='5' || $p2=='5'){
                        //$month不變
                    } else {
                        $month=$month-1;
                    }
                }
            }
//period判斷
//以九月為切分，過九月後，即為新的一個period
//
            $month=str_pad($month, 2, '0', STR_PAD_LEFT);

            if ($month>='09'){
                $period=$year-2001;
            } else {
                $period=$year-2002;
            }

            $workers1=[];
            if ($person1!=''){
                $workers1[]=$person1;
            }

            if ($person2!=''){
                $workers1[]=$person2;
            }

            if ($person3!=''){
                $workers1[]=$person3;
            }

            $workers=implode(', ', $workers1);

            return [
                'year'=>$year,
                'month'=>$month,
                'date1'=>$year."-".$month."-01",
                'period'=>$period,
                'workers'=>$workers

            ];


    }



    public function direntry($census2){
        $this->createTable($census2);
    }

    public $entrytable;
    public $identifier='黃小俊';  //預設鑑定者
    
    public function createTable($census){

        $entrytable1=FsSeedsRecord1::query()->orderBy('id', 'asc')->get()->toArray();

        $ob_table = new SeedsAddButton;
        $entrytable=$ob_table->addbutton($entrytable1, 'record');



        $fsscsplist1 = FsSeedsFulldata::select('csp', DB::raw('count(trap) as count2'))->where('csp', 'not like', 'nothing')->groupBy('csp')->orderByDesc('count2')->get()->toArray();
        $fsscsplist2 = FsSeedsSplist::select('csp')->get()->toArray();

        for($i=0;$i<count($fsscsplist1);$i++){

            $fsscsplist[]=$fsscsplist1[$i]['csp'];
        }

        for($i=0;$i<count($fsscsplist2);$i++){

            if (!in_array($fsscsplist2[$i]['csp'], $fsscsplist)){
                $fsscsplist[]=$fsscsplist2[$i]['csp'];
            }

            
        }

        for($k=0;$k<29;$k++){
            $emptytable[$k]['id']=$k+1;
            $emptytable[$k]['census']=$census;
            $emptytable[$k]['trap']='';
            $emptytable[$k]['csp']='';
            $emptytable[$k]['code']='';
            $emptytable[$k]['count']='';
            $emptytable[$k]['seeds']='';
            $emptytable[$k]['viability']='';
            $emptytable[$k]['fragments']='';
            $emptytable[$k]['sex']='';
            $emptytable[$k]['identifier']=$this->identifier;
            $emptytable[$k]['note']='';
        }

        // dd($inlist);
        $this->entry='y';
        $this->thiscensus=$census;

        // $this->dispatchBrowserEvent('data', [ 'census' => $census, 'record' => $entrytable, 'emptytable' => $emptytable, 'csplist' => $fsscsplist]);
        $this->dispatch(
            'data',
            census: $census,
            record: $entrytable,
            emptytable: $emptytable,
            csplist: $fsscsplist
        );


    }

    public $census3;
    public $date3;
    public $person31;
    public $person32;
    public $person33;
    public $note3='';
    public $note2;
    public $chcensus;

//檢視調查日期資訊前五筆
    public function deleteForm(Request $request)
    {

        // 更新date資料
        FsSeedsDateinfo::where('census', 'like', $this->chcensus)->delete();
        SeedsDateinfoSyncService::sync();
        $this->note2='已刪除 census'.$this->chcensus.' 資料';

        $this->dateinfo=FsSeedsDateinfo::query()->orderBy('census', 'desc')->take(5)->get()->toArray(); //取前五筆檢視
        $this->chcensus='';

        $this->resetExcept(['user']);
        $this->mount();
    }
//更新調查日期資訊
    public function submitForm3(Request $request){
        $user = $this->actorAccount();


        if ($this->date3!=''){

            $datecheck=$this->dateinfo($this->census3, $this->date3, $this->person31, $this->person32, $this->person33);

            if (is_null($this->note3)){$this->note3='';}

            // dd($datecheck);

            $inlist['year']=$datecheck['year'];
            $inlist['month']=$datecheck['month'];
            $inlist['date1']=$datecheck['date1'];
            $inlist['period']=$datecheck['period'];
            $inlist['workers']=$datecheck['workers'];

            $additionalData=['date'=>$this->date3, 'census'=>$this->census3,  'updated_id' => $user,'note'=>$this->note3 ,'updated_at' => date("Y-m-d H:i:s")];

            $inlist = array_merge($inlist, $additionalData);
            // dd($inlist);
            FsSeedsDateinfo::where('census', 'like', $this->census3)->update($inlist);
            SeedsDateinfoSyncService::sync();

            // $this->createTable($this->census);
            $this->resetExcept(['user']);
            $this->mount();
        }        
    }

    public $finishnote;


    public function render(Request $request)
    {
        return view('livewire.fushan.seeds-showentry');
    }
}
