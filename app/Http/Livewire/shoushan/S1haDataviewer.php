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
// use App\Models\Ss10mTree2014;
// use App\Models\Ss10mTree2015;
// use App\Models\Ss10mTreeEnviR1;
// use App\Models\Ss10mTreeEnviR2;
// use App\Models\Ss10mTreeCovR1;
// use App\Models\Ss10mTreeCovR2;
// use App\Models\Ss10mTreeRecord1;
// use App\Models\Ss10mTreeRecord2;
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

        // for($i=1;$i<4;$i++){
        //     if($i<3){
        //         $downloadtable[$i][0]='調查資料';
        //         $downloadtable[$i][1]='地圖';
        //         $downloadtable[$i][2]='';
        //         $downloadtable[$i][3]='';
        //     } else {
        //         $downloadtable[$i][0]='舊樹';
        //         $downloadtable[$i][1]='新樹';
        //         $downloadtable[$i][2]='地被';
        //         $downloadtable[$i][3]='地圖';
        //     }
        // }

                // if ($path!=''){
                //     $downloadtable2[$i][$j]="<a href='/".$path."' target=_blank j=".$j.">".$downloadtable[$i][$j]."</a>";
                // } else { $downloadtable2[$i][$j]=$downloadtable[$i][$j];}
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

    public function serachstemid(Request $request, $tag, $branch)
    {



        if ($branch==''){$branch='0';}


        
        $stemid=$tag.".".$branch;

        // dd($stemid);

        $census1=Ss1haData2015::where('tag', 'like', $tag)->get()->toArray();

        $base2015=Ss1haBase2015::where('tag', 'like', $tag)->get()->toArray();
        $maxb=Ss1haData2015::where('tag', 'like', $tag)->max('branch');
        // dd($maxb);

        // dd($census1);


            if (count($census1) > 0) {

                $table['data'] = $census1;
                // $table[0]['census'] = '2014';
                $table['maxb'] = $maxb;
                $this->result = $table;
                $this->resultnote = '';
                $this->baseresult=$base2015[0];

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
