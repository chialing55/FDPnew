<?php

namespace App\Http\Livewire\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

use App\Models\Ss10mBase2015;
use App\Models\Ss10mTree2014;
use App\Models\Ss10mTree2015;

use App\Models\SsSplist;

class S10mDataviewer extends Component
{

    public $user;
    public $site;
    public $downloadtable=[];

    public $plots = array('B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38'); 


    public $selectPlot='0';

    public function mount(Request $request)
    {
        

        for($i=1;$i<4;$i++){
            if($i<3){
                $downloadtable[$i][0]='調查資料';
                $downloadtable[$i][1]='';
                $downloadtable[$i][2]='';
                $downloadtable[$i][3]='地圖';
            } else {
                $downloadtable[$i][0]='舊樹';
                $downloadtable[$i][1]='新樹';
                $downloadtable[$i][2]='地被';
                $downloadtable[$i][3]='地圖';
            }
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
    public $census='1';

    public function processFile(Request $request){

        $downloadtable = [];
        $downloadtable2 = [];

        $plot=$this->plots[$this->selectPlot];

        for($i=1;$i<4;$i++){
            $filecensus='ss_10m_census'.$i.'_scanfile';
            $filePath = '';

            for($j=0;$j<4;$j++){


                if($i<3){
                    if ($j=='0'){
                        $filePath=$filecensus."/data/".$plot.".pdf";
                    } else if($j=='3'){
                        $filePath=$filecensus."/orimap/".$plot.".jpg";
                    } else {
                        $filePath='';
                    }
                } else {
                    if ($j=='0'){
                        $filePath=$filecensus."/data/".$plot."_old.pdf";
                    } else if($j=='1'){
                        $filePath=$filecensus."/data/".$plot."_new.pdf";
                    } else if($j=='2'){
                        $filePath=$filecensus."/data/".$plot."_under.pdf";
                    } else if($j=='3'){
                        $filePath=$filecensus."/orimap/".$plot.".jpg";
                    }                     
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

                $downloadtable[$i][0] = ($i < 3) ? '調查資料' : '舊樹';
                $downloadtable[$i][1] = ($i < 3) ? '' : '新樹';
                $downloadtable[$i][2] = ($i == 3) ? '地被' : '';
                $downloadtable[$i][3] = '地圖';

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
    public $stemidplot='';
    public $branch='';
    public $resultbase;
    public $result;
    public $resultnote='';

    public function submitStemidForm(Request $request)
    {
        $this->serachstemid($request, $this->tag, $this->branch, $this->stemidplot);
    }

    public function serachstemid(Request $request, $tag, $branch, $stemidplot)
    {

        if ($branch==''){$branch='0';}


        
        $stemid=$this->plots[$stemidplot]."-".$tag.".".$branch;
        $tagid=$this->plots[$stemidplot]."-".$tag;
        // dd($stemid);

        $census1=Ss10mTree2014::where('stemid', 'like', $stemid)->get()->toArray();
        $census2=Ss10mTree2015::where('stemid', 'like', $stemid)->get()->toArray();
        $base2015=Ss10mBase2015::where('tagid', 'like', $tagid)->get()->toArray();
        $maxb=Ss10mTree2015::where('plot', 'like', $this->plots[$stemidplot])->where('tag', 'like', $tag)->max('branch');
        // dd($maxb);

        // dd($census1);


            if (count($census1) > 0) {

                $table[0] = $census1[0];
                $table[0]['census'] = '2014';
                $table[0]['maxb'] = '';
            } else {
                $table[0] = ['census' => '2014', 'sqx' => '', 'sqy' => '', 'csp' => '', 'status' => '', 'dbh' => '',  'note' => '',  'maxb' =>''];
            }

            if (count($census2) > 0) {
                $table[1] = $census2[0];
                $table[1]['census'] = '2015';
                $table[1]['maxb'] = $maxb;
            } else {
                $table[1] = ['census' => '2015', 'sqx' => '', 'sqy' => '', 'csp' => '', 'status' => '', 'dbh' => '', 'pom' => '', 'note' => '', 'confirm' => '', 'maxb' =>''];
            }

            if (count($census1) > 0 || count($census2) > 0) {
                $this->result = $table;
                $this->resultnote = '';

                $this->baseresult=$base2015[0];

            } else {
                $this->result ='';
                $this->baseresult='';
                $this->resultnote = '查無此樹';
            }

            // dd($table);

    }


    public function render()
    {
        return view('livewire.shoushan.s10m-dataviewer');
    }
}
