<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus5;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeCensus2;
use App\Models\FsTreeCensus1;
use App\Models\FsTreeBase;

class TreeDataviewer extends Component
{

    public $qx='0';
    public $qy='0';
    public $census='1';
    public $oldnew='old';
    public $error='';
    public $path='';
    public $filePath2='';
    public $map='1';
    public $downloadtable=[];

    public $qx1='0';
    public $qy1='0';



    public function mount(Request $request)
    {
        

        for($i=1;$i<5;$i++){
            $downloadtable[$i][0]='舊樹';
            $downloadtable[$i][1]='新樹';
            if ($i==1){
                $downloadtable[$i][1]='';
            }
            $downloadtable[$i][2]='地圖1';
            $downloadtable[$i][3]='地圖2';
            $downloadtable[$i][4]='地圖3';
            $downloadtable[$i][5]='地圖4';

        }
        $this->downloadtable=$downloadtable;
        $this->processFile2($request);

    }



    public function change2(Request $request)
    {
        $this->processFile2($request);
    }



//檢查是否有原始資料電子檔

    public function processFile2(Request $request){

        $fileqx=str_pad($this->qx1, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy1, 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;
        $temp=['old', 'new', 'map', 'map', 'map', 'map'];

        for($i=1;$i<6;$i++){
            $filecensus='fs_census'.$i.'_scanfile';

            for($j=0;$j<count($temp);$j++){
                $filePath=$filecensus."/".$fileqx.'/'.$temp[$j].'/'.$filesqx.'_'.$temp[$j].'';
                $filemap='';
                if ($i==1 && $temp[$j]=='new') {
                    $downloadtable[$i][$j]="";
                    continue;
                }

                if ($temp[$j]=='map'){
                    $filemap=$j-1;
                    if ($i==1 || $i==2){
                        if ($filemap==3){$filemap=4;}
                        else if ($filemap==4){$filemap=3;}
                    }
                    $filemap=str_pad($filemap, 2, '0', STR_PAD_LEFT);
                    $filePath=$filecensus."/".$fileqx.'/'.$temp[$j].'/'.$filesqx.$filemap.'';
                }

                $matchingFiles = glob(public_path($filePath) . '.*', GLOB_BRACE | GLOB_NOCHECK);

                $filePath2=$matchingFiles;


                if (!empty($matchingFiles)) {

                    foreach ($matchingFiles as $matchingFile) {
                        $info = pathinfo($matchingFile);
                        
                        if ($info['extension'] === 'pdf') {
                            // 這是 PDF 檔案
                            $path = $filePath.".pdf";

                            $error = '';
                            break;
                        } elseif ($info['extension'] === 'PDF'){
                            $path = $filePath.".PDF";
                            $error = '';
                            break;
                        } elseif ($info['extension'] === 'jpg'){
                            $path = $filePath.".jpg";
                            $error = '';
                            break;
                        }else {
                            $error = '沒有檔案 ' . $filePath;
                            $path='';
                        }
                    }
                } else {
                    $error = '沒有檔案 ' . $filePath;
                    $path='';
                }
                $temp2=['舊樹','新樹','地圖1','地圖2','地圖3','地圖4'];
                if ($path!=''){
                    $downloadtable[$i][$j]="<a href='/".$path."' target=_blank j=".$j.">".$temp2[$j]."</a>";
                } else { $downloadtable[$i][$j]=$temp2[$j]."";}

            }
        }
// dd($downloadtable);
        $this->downloadtable=$downloadtable;

    }


    public $tag='';
    public $branch='';
    public $result;
    public $basedata;
    public $resultnote='';
    public $deleteNote='';

    public function submitStemidForm(Request $request)
    {
        $this->serachstemid($request, $this->tag, $this->branch);
    }
//依據stemid尋找資料
    public function serachstemid(Request $request, $tag, $branch)
    {

        // $user = $request->session()->get('user', function () {
        //     return 'no';
        // });

        $splist=$request->session()->get('splist', function () {
            return 'no';
        });

        if ($branch==''){$branch='0';}
        $stemid=$tag.".".$branch;

        $census1=FsTreeCensus1::where('stemid', 'like', $stemid)->get()->toArray();
        $census2=FsTreeCensus2::where('stemid', 'like', $stemid)->get()->toArray();
        $census3=FsTreeCensus3::where('stemid', 'like', $stemid)->get()->toArray();
        $census4=FsTreeCensus4::where('stemid', 'like', $stemid)->get()->toArray();
        $census5=FsTreeCensus5::where('stemid', 'like', $stemid)->get()->toArray();
        $base=FsTreeBase::where('tag', 'like', $tag)->get()->toArray();
        $maxb1=FsTreeRecord1::where('tag', 'like', $tag)->max('branch');
        $maxb2=FsTreeRecord1::where('tag', 'like', $tag)->max('branch');


        $census5_1=FsTreeRecord1::where('stemid', 'like', $stemid)->where('date', 'not like', '0000-00-00')->get()->toArray();
        $census5_2=FsTreeRecord2::where('stemid', 'like', $stemid)->where('date', 'not like', '0000-00-00')->get()->toArray();

        $census5_3=FsTreeRecord1::where('alternote', 'like', '%'.$tag.'%')->orderby('dbh', 'desc')->get()->toArray();
        $census5_4=FsTreeRecord2::where('alternote', 'like', '%'.$tag.'%')->orderby('dbh', 'desc')->get()->toArray();

        // dd($request);
        if (count($census4)>0){
            //舊樹資料

            $this->basedata=['stemid'=> $stemid, 'qx'=>$base[0]['qx'], 'qy'=>$base[0]['qy'], 'sqx'=>$base[0]['sqx'], 'sqy'=>$base[0]['sqy'], 'csp'=>$splist[$base[0]['spcode']], 'tag'=>$tag, 'b' => $branch, 'bs'=>max($maxb1, $maxb2)];
            $this->deleteNote=$base[0]['deleted_at'];


            $censusTables = ['census1', 'census2', 'census3', 'census4', 'census5'];

            foreach ($censusTables as $index => $censusTable) {
                if (count($$censusTable) > 0) {
                    if ($tag[0] == 'G') {
                        if (isset($$censusTable[0]['h2'])){
                        $$censusTable[0]['dbh'] = $$censusTable[0]['h2'];
                        } else {
                            $$censusTable[0]['dbh'] = $$censusTable[0]['h']-$$censusTable[0]['pom'];
                        }
                    }
                    if ($censusTable=='census1'){
                        $$censusTable[0]['status']='';
                        $$censusTable[0]['code']='';
                        $$censusTable[0]['confirm']='';
                    }
                    if ($censusTable=='census2'){
                        $$censusTable[0]['confirm']='';
                    }
                    $table[$index] = [
                        'census' => $censusTable,
                        'date' => $$censusTable[0]['date'],
                        'status' => $$censusTable[0]['status'],
                        'code' => $$censusTable[0]['code'],
                        'dbh' => $$censusTable[0]['dbh'],
                        'pom' => $$censusTable[0]['pom'],
                        'note' => $$censusTable[0]['note'],
                        'confirm' => $$censusTable[0]['confirm'],
                    ]; 

                    if ($index=='4'){
                        $table[$index]['note'] .= ' ' . $$censusTable[0]['alternote'];                        
                    }           
                } else {
                    $table[$index] = [
                        'census' => $censusTable,
                        'date' => '',
                        'status' => '',
                        'code' => '',
                        'dbh' => '',
                        'pom' => '',
                        'note' => '',
                        'confirm' => '',
                    ];
                }
            }

            if (count($census5)=='0'){

                $census5Table = count($census5_1) > 0 ? $census5_1 : $census5_2;

                if (count($census5Table)>0){
                    if ($tag[0]=='G'){
                        $census5Table[0]['dbh']=$census5Table[0]['h2'];
                        // $$census5Table[0]['pom']=$$census5Table[0]['h1'];
                    }
                    $table[4]=['census' => 'census5 (record)', 'status' => $census5Table[0]['status'], 'code'=>$census5Table[0]['code'], 'dbh'=>$census5Table[0]['dbh'], 'pom' => $census5Table[0]['pom'], 'note' => $census5Table[0]['note'].' '.$census5Table[0]['alternote'], 'confirm' =>$census5Table[0]['confirm']];

                } else {

                    $table[4]=['census' => 'census5','date'=>'', 'status' => '', 'code'=>'', 'dbh'=>'', 'pom' => '', 'note' => '', 'confirm' =>''];
                }
            } 


            $this->result=$table;
            $this->resultnote='';

        } else {


            //這次新增的樹 or 特殊修改換號碼

            //如果已匯入大表

            if (count($census5)>0){
                $census5Table=$census5;
                $temp='';
            } else {
                $census5Table = count($census5_1) > 0 ? $census5_1 : $census5_2;
                $temp='(record)';
            }

            if (count($census5Table) > 0) {
                $table = [];

                for ($i = 1; $i <= 4; $i++) {
                    $table[$i - 1] = ['census' => 'census'.$i, 'date'=>'','status' => '', 'code' => '', 'dbh' => '', 'pom' => '', 'note' => '',  'confirm' => '',
                    ];
                }

            if (count($base)> 0){
                $this->basedata=['stemid'=> $stemid, 'qx'=>$base[0]['qx'], 'qy'=>$base[0]['qy'], 'sqx'=>$base[0]['sqx'], 'sqy'=>$base[0]['sqy'], 'csp'=>$splist[$base[0]['spcode']], 'tag'=>$tag, 'b' => $branch, 'bs'=>max($maxb1, $maxb2)];
            } else {

                $this->basedata=['stemid'=> $stemid, 'qx'=>$census5Table[0]['qx'], 'qy'=>$census5Table[0]['qy'], 'sqx'=>$census5Table[0]['sqx'], 'sqy'=>$census5Table[0]['sqy'], 'csp'=>$splist[$census5Table[0]['spcode']], 'tag'=>$tag, 'b' => $branch, 'bs'=>max($maxb1, $maxb2)];
            }

                if ($tag[0] == 'G') {
                    $census5Table[0]['dbh'] = $census5Table[0]['h2'];
                }
                $table[4] = [
                    'census' => 'census5'.$temp,
                    'date' => $census5Table[0]['date'],
                    'status' => $census5Table[0]['status'],
                    'code' => $census5Table[0]['code'],
                    'dbh' => $census5Table[0]['dbh'],
                    'pom' => $census5Table[0]['pom'],
                    'note' => $census5Table[0]['note'].' '.$census5Table[0]['alternote'],
                    'confirm' => $census5Table[0]['confirm'],
                ];
                $this->result = $table;
                $this->resultnote = '';
            } else {

                //號碼在特殊修改中出現

                $census5alter = count($census5_3) > 0 ? $census5_3 : $census5_4;
                if (count($census5alter) > 0){

                    for ($i = 1; $i <= 4; $i++) {
                        $table[$i - 1] = ['census' => 'census'.$i, 'date'=>'','status' => '', 'code' => '', 'dbh' => '', 'pom' => '', 'note' => '',  'confirm' => '',
                        ];
                    }

                    $this->basedata=['stemid'=> $stemid, 'qx'=>$census5alter[0]['qx'], 'qy'=>$census5alter[0]['qy'], 'sqx'=>$census5alter[0]['sqx'], 'sqy'=>$census5alter[0]['sqy'], 'csp'=>$splist[$census5alter[0]['spcode']], 'tag'=>$census5alter[0]['tag'], 'b' => $census5alter[0]['branch'], 'bs'=>max($maxb1, $maxb2)];

                    
                    if ($tag[0] == 'G') {
                        $census5alter[0]['dbh'] = $census5alter[0]['h2'];
                    }
                    $table[4] = [
                        'census' => 'census5 (record)',
                        'date' => $census5alter[0]['date'],
                        'status' => $census5alter[0]['status'],
                        'code' => $census5alter[0]['code'],
                        'dbh' => $census5alter[0]['dbh'],
                        'pom' => $census5alter[0]['pom'],
                        'note' => $census5alter[0]['note'].' '.$census5alter[0]['alternote'],
                        'confirm' => $census5alter[0]['confirm'],
                    ];
                    $this->result = $table;
                    $this->resultnote = ''; 
                } else {
                    $this->resultnote = '查無此樹';
                    $this->result = '';
                }                          


                
            }
        }
    }

    public function render()
    {
        return view('livewire.fushan.tree-dataviewer');
    }
}
