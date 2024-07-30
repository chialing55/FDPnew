<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeComplete;

use App\Jobs\TreeCompareCheck;

//每木資料比對

class TreeCompare extends Component
{

    public $comnote;

    public $entrylist=[];
    public $comparelist=[];
    public $qx;
    public $user;

    public function mount(){
// 確認是否輸入完資料
        $comnote='';
        $entrylist=[];
        $comparelist=[];

        $complete=FsTreeComplete::select('qx',  DB::raw('SUM(entry1) as sum1'), DB::raw('SUM(entry2) as sum2'))->groupBy('qx')->get()->toArray();
        $compareDone=FsTreeComplete::select('qx',  'compareDone')->where('compareDone', '!=', '')->groupBy('qx', 'compareDone')->get()->toArray();

        foreach ($complete as $entry){
            if ($entry['sum1']==25 && $entry['sum2']==25){
                $entrylist[]=$entry['qx'];
            }
        }


        foreach ($compareDone as $entry){

                $comparelist[]=$entry['qx'];

        }
        // dd($compareDone);

        $this->entrylist=$entrylist;
        $this->comparelist=$comparelist;

    }

    public function compare(Request $request){
        $comnote='';

        $qx=$this->qx;
            
        $pass='1';

            $record1=FsTreeRecord1::query()->where('qx', 'like', $qx)->get()->keyBy('stemid')->toArray();
            $record2=FsTreeRecord2::query()->where('qx', 'like', $qx)->get()->keyBy('stemid')->toArray();

            $record1Stemid = array_keys($record1);
            $record2Stemid = array_keys($record2);

            $allStemid = array_unique(array_merge($record1Stemid, $record2Stemid));
            sort($allStemid);

 // dd($pass);
            $plotSize='20';
            $plotType='fsTree';

            $check = new TreeCompareCheck;
            $comnote=$check->check($request, $record1, $record2, $allStemid, $plotSize, $plotType);


        // dd($comnote);

            if ($comnote==''){
                    $comnote='資料皆相符。恭喜比對完成。';
                    $user = $request->session()->get('user', function () {
                        return 'no';
                    });

                    $uplist['compareDone']=$user;
                    $uplist['compareDone_at']=date("Y-m-d H:i:s");

                    FsTreeComplete::where('qx', 'like', $record1[$allStemid[0]]['qx'])->update($uplist);

            }


        $this->comnote=$comnote;

        $request->session()->put('comnote', $comnote);
        $this->mount();

        // dd('q');
    }


    public function render()
    {
        return view('livewire.fushan.tree-compare');
    }
}
