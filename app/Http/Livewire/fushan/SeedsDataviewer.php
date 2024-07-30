<?php

namespace App\Http\Livewire\Fushan;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;

//種子資料檢視
class SeedsDataviewer extends Component
{

    public $data=[];

    public $year='all';
    public $month='all';
    public $trap='all';
    public $species='';
    public $code='all';

    public $traps;

    public function mount(Request $request){

        $unkParam = $request->session()->get('unk', function () {
            return 'no';
        });

        if ($unkParam!='no'){
            $this->species=$unkParam;
            $this->trap='each';
            $this->search('all', 'all', 'each', $unkParam, 'all');
            $request->session()->forget('unk');
        }

        $this->traps=FsSeedsFulldata::select('trap')->groupBy('trap')->get()->toArray();
    }

    public function search($year, $month, $trap, $species, $code){
        $year2=$year;
        $month2=$month;
        $trap2=$trap;
        if($year=='all' || $year=='each'){ $year='%';}
        if($month=='all' || $month=='each'){ $month='%';}
        if($trap=='all' || $trap=='each'){ $trap='%';} else {
            $trap = str_pad($trap, 3, '0', STR_PAD_LEFT);
        }
        if($species=='' ){$species='%';}
        if($code=='all'){ $code='%';}


        $alldata = FsSeedsFulldata::join('dateinfo', 'fulldata.census', '=', 'dateinfo.census')
            ->select('fulldata.census', 'fulldata.trap', 'fulldata.sp', 'fulldata.csp', 'fulldata.code','fulldata.identified', 'dateinfo.year', 'dateinfo.month')
            ->where('year', 'like', $year)
            ->where('csp', 'like', '%'.$species.'%')
            ->where('trap', 'like', $trap)
            ->where('month', 'like', $month)
            ->where(function ($query) use ($code) {
        // If $code is '%', retrieve all codes, else retrieve the specific code
                    $query->where('code', 'like', $code);
                })
            ->where('csp', 'not like', 'nothing')
            ->get()
            ->toArray();

       // dd($alldata);
         

         $comb=[];
         $comb1=[];
         $datacomb=[];
         foreach($alldata as $data){
            if ($year2=='all'){$data['year']='-';}
            if ($month2=='all'){$data['month']='-';}
            if ($trap2=='all'){$data['trap']='-';}

            $comb_1=$data['year'].$data['month'].$data['trap'].$data['csp'].$data['code'];
            $comb_2=$data['year'].$data['month'].$data['trap'].$data['csp'];
            if (in_array($comb_1, $comb)){
                continue;
            } else {



                $comb[]=$comb_1;

                if (!in_array($comb_2, $comb1)){
                    $comb1[]=$comb_2;
                    $datacomb[$comb_2]=$data;
                } 
                $datacomb[$comb_2]['codecomb'][]=$data['code'];
                
            }

         }

        usort($datacomb, function ($a, $b) {
            return strcmp($a['csp'], $b['csp']);
        });

        usort($datacomb, function ($a, $b) {
            return strcmp($a['trap'], $b['trap']);
        });

        usort($datacomb, function ($a, $b) {
            return strcmp($a['year'], $b['year']);
        });

         $this->data=$datacomb;


    }

    public function openUnknown(Request $request, $url, $unk){
        $request->session()->put('unk', $unk);

        return redirect()->to($url);
    }


    public function render()
    {
        return view('livewire.fushan.seeds-dataviewer');
    }
}
