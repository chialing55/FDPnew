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

        $unkParam = $request->hasSession()
            ? $request->session()->get('unk', 'no')
            : 'no';

        if ($unkParam!='no'){
            $this->species=$unkParam;
            $this->trap='each';
            $this->search('all', 'all', 'each', $unkParam, 'all');
            $request->session()->forget('unk');
        }

        $this->traps=FsSeedsFulldata::select('trap')->groupBy('trap')->orderBy('trap')->get()->toArray();
    }

    public function search($year = null, $month = null, $trap = null, $species = null, $code = null){
        $year = $year ?? $this->year;
        $month = $month ?? $this->month;
        $trap = $trap ?? $this->trap;
        $species = $species ?? $this->species;
        $code = $code ?? $this->code;

        $this->year = $year;
        $this->month = $month;
        $this->trap = $trap;
        $this->species = $species;
        $this->code = $code;

        $year2 = $year;
        $month2 = $month;
        $trap2 = $trap;
        $trapValue = ($trap === 'all' || $trap === 'each') ? null : str_pad($trap, 3, '0', STR_PAD_LEFT);
        $speciesValue = trim($species);
        $codeValue = $code === 'all' ? null : $code;

        $alldata = FsSeedsFulldata::join('dateinfo', 'fulldata.census', '=', 'dateinfo.census')
            ->select('fulldata.census', 'fulldata.trap', 'fulldata.sp', 'fulldata.csp', 'fulldata.code','fulldata.identified', 'dateinfo.year', 'dateinfo.month')
            ->when($year !== 'all' && $year !== 'each', fn ($query) => $query->where('dateinfo.year', $year))
            ->when($month !== 'all' && $month !== 'each', fn ($query) => $query->where('dateinfo.month', $month))
            ->when($trapValue !== null, fn ($query) => $query->where('fulldata.trap', $trapValue))
            ->when($speciesValue !== '', fn ($query) => $query->where('fulldata.csp', 'like', '%'.$speciesValue.'%'))
            ->when($codeValue !== null, fn ($query) => $query->where('fulldata.code', $codeValue))
            ->where('csp', 'not like', 'nothing')
            ->get()
            ->toArray();

         $comb = [];
         $comb1 = [];
         $datacomb = [];
         foreach($alldata as $data){
            if ($year2 == 'all') {$data['year'] = '-';}
            if ($month2 == 'all') {$data['month'] = '-';}
            if ($trap2 == 'all') {$data['trap'] = '-';}

            $comb_1 = $data['year'].$data['month'].$data['trap'].$data['csp'].$data['code'];
            $comb_2 = $data['year'].$data['month'].$data['trap'].$data['csp'];
            if (in_array($comb_1, $comb, true)) {
                continue;
            }

            $comb[] = $comb_1;

            if (!in_array($comb_2, $comb1, true)) {
                $comb1[] = $comb_2;
                $datacomb[$comb_2] = $data;
                $datacomb[$comb_2]['codecomb'] = [];
            }

            $datacomb[$comb_2]['codecomb'][] = $data['code'];
         }

        $datacomb = array_values($datacomb);
        usort($datacomb, function ($a, $b) {
            return [$a['year'], $a['month'], $a['trap'], $a['csp']]
                <=> [$b['year'], $b['month'], $b['trap'], $b['csp']];
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
