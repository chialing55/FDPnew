<?php

namespace App\Http\Livewire\Shoushan;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

use App\Models\Ss1haData2015;
use App\Models\Ss1haBase2015;
use App\Models\Ss1haData2024;
use App\Models\Ss1haBase2024;

use App\Models\SsSplist;

class S1haDataviewer extends Component
{

    public $qx='-4';
    public $qy='13';
    public $census='1';
    public $oldnew='old';
    public $error='';
    public $path='';
    public $filePath2='';
    public $map='1';
    public $downloadtable=[];

    public $censusyear=array('','2015','2024');

    public function mount(Request $request){


        for($i=1;$i<2;$i++){

            $downloadtable[$i][0]='舊樹';
            // $downloadtable[$i][1]='新樹';
            $downloadtable[$i][1]='地圖';
        }


        $this->downloadtable=$downloadtable;
        // $this->processFile2($request);
        $this->processFile($request);


    }

    public function change(Request $request)
    {
        $this->processFile($request);
    }

    public $type;

//檢查是否有資料電子檔
    public function processFile(Request $request){

        $downloadtable = [];
        $downloadtable2 = [];

        $fileqx=str_pad($this->qx, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy, 2, '0', STR_PAD_LEFT);
        $plot=$fileqx.$fileqy;

        for($i=1;$i<2;$i++){
            $filecensus='ss_1ha_census'.$i.'_scanfile';
            $filePath = '';
            

            for($j=0;$j<2;$j++){
                if ($j==0){
                    $filePath=$filecensus."/".$fileqx."/old/".$plot."_old.pdf";
                } else if ($j==1) {
                    $filePath=$filecensus."/".$fileqx."/orimap/".$plot.".jpg";
                }

                    


                if ($filePath!=''){
                    $filePath2 = public_path($filePath);

                    if (file_exists($filePath2)) {
                        $path = $filePath;
                        $error = '';
                    } else {
                        $error = '沒有檔案 ' . $filePath;
                        $path = '';
                    }
                } else {
                    $path = '';
                }

                $downloadtable[$i][0] = '舊樹';
                $downloadtable[$i][1] = '地圖';

                 $downloadtable2[$i][$j] = ($path != '')
                ? "<a href='/" . $path . "' target=_blank j=" . $j . ">" . $downloadtable[$i][$j] . "</a>"
                : $downloadtable[$i][$j];
            }
        }
// dd($downloadtable);
        $this->downloadtable=$downloadtable2;

    }


    public $tag='';

    public $branch='';
    public $resultbase;
    public $result;
    public $resultnote='';

    public function submitStemidForm(Request $request)
    {
        $this->serachstemid($request, $this->tag, $this->branch);
    }
//依stemid尋找資料
    public function serachstemid(Request $request, $tag, $branch)
    {

        if ($branch==''){$branch='0';}
        $stemid=$tag.".".$branch;

        // dd($stemid);

        $census1=Ss1haData2015::where('tag', 'like', $tag)->get()->toArray();
        $census2=Ss1haData2024::where('tag', 'like', $tag)->get()->toArray();
        $base2024 = Ss1haBase2024::where('tag', 'like', $tag)
            ->join('splist', '1ha_base_2024.spcode', '=', 'splist.spcode')
            ->select('1ha_base_2024.*', 'splist.index as csp')
            ->get()
            ->toArray();
        $maxb=Ss1haData2024::where('tag', 'like', $tag)->max('branch');
        // dd($maxb);

        // dd($census1);


            if (count($census1) > 0) {

                $table['data'] = $census1;
                $table['data2'] = $census2;
                // $table[0]['census'] = '2014';
                $table['maxb'] = $maxb;
                $this->result = $table;
                $this->resultnote = '';
                $this->baseresult=$base2024[0];

            } else {
                $table['data'] = ['qx' => '', 'qy' => '','sqx' => '', 'sqy' => '', 'csp' => '', 'status' => '', 'dbh' => '', 'height' => '', 'note' => ''];
                $table['maxb'] = '';
                $this->result ='';
                $this->baseresult='';
                $this->resultnote = '查無此樹';
            }

            // dd($table);

    }




    public function render()
    {
        return view('livewire.shoushan.s1ha-dataviewer');
    }
}
