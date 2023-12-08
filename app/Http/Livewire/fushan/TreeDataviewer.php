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





    public function processFile2(Request $request){

        $fileqx=str_pad($this->qx1, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy1, 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;
        $temp=['old', 'new', 'map', 'map', 'map', 'map'];

        for($i=1;$i<5;$i++){
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

    public function submitStemidForm(Request $request)
    {
        $this->serachstemid($request, $this->tag, $this->branch);
    }

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
        $base=FsTreeBase::where('tag', 'like', $tag)->get()->toArray();
        $census4_2=FsTreeCensus4::where('tag', 'like', $tag)->max('branch');

        // dd($request);
        if (count($base)>0){
            $this->basedata=['stemid'=> $stemid, 'qx'=>$base[0]['qx'], 'qy'=>$base[0]['qy'], 'sqx'=>$base[0]['sqx'], 'sqy'=>$base[0]['sqy'], 'csp'=>$splist[$base[0]['spcode']], 'tag'=>$tag, 'b' => $branch, 'bs'=>$census4_2];

            if (count($census1)>0){
                if ($tag[0]=='G'){
                    $census1[0]['dbh']=$census1[0]['h']-$census1[0]['pom'];
                }
                $table[0]=['census' => 'census1', 'status' => '', 'code'=>'', 'dbh'=>$census1[0]['dbh'], 'pom' => $census1[0]['pom'], 'note' => $census1[0]['note'], 'confirm' =>''];
            } else {
                $table[0]=['census' => 'census1', 'status' => '', 'code'=>'', 'dbh'=>'', 'pom' => '', 'note' => '', 'confirm' =>''];
            }
            if (count($census2)>0){
                if ($tag[0]=='G'){
                    $census2[0]['dbh']=$census2[0]['h2'];
                    // $census2[0]['pom']=$census2[0]['h1'];
                }
                $table[1]=['census' => 'census2', 'status' => $census2[0]['status'], 'code'=>$census2[0]['code'], 'dbh'=>$census2[0]['dbh'], 'pom' => $census2[0]['pom'], 'note' => $census2[0]['note'], 'confirm' =>''];
            } else {
                $table[1]=['census' => 'census2', 'status' => '', 'code'=>'', 'dbh'=>'', 'pom' => '', 'note' => '', 'confirm' =>''];
            }

            if (count($census3)>0){
                if ($tag[0]=='G'){
                    $census3[0]['dbh']=$census3[0]['h2'];
                    // $census3[0]['pom']=$census3[0]['h1'];
                }
                $table[2]=['census' => 'census3', 'status' => $census3[0]['status'], 'code'=>$census3[0]['code'], 'dbh'=>$census3[0]['dbh'], 'pom' => $census3[0]['pom'], 'note' => $census3[0]['note'], 'confirm' =>$census3[0]['confirm']];
            } else {
                $table[2]=['census' => 'census3', 'status' => '', 'code'=>'', 'dbh'=>'', 'pom' => '', 'note' => '', 'confirm' =>''];
            }

            if (count($census4)>0){
                if ($tag[0]=='G'){
                    $census4[0]['dbh']=$census4[0]['h2'];
                    // $census4[0]['pom']=$census4[0]['h1'];
                }
                $table[3]=['census' => 'census4', 'status' => $census4[0]['status'], 'code'=>$census4[0]['code'], 'dbh'=>$census4[0]['dbh'], 'pom' => $census4[0]['pom'], 'note' => $census4[0]['note'], 'confirm' =>$census4[0]['confirm']];
            } else {
                $table[3]=['census' => 'census4', 'status' => '', 'code'=>'', 'dbh'=>'', 'pom' => '', 'note' => '', 'confirm' =>''];
            }

            

            $this->result=$table;
            $this->resultnote='';

        } else {
            $this->resultnote='查無此樹';
            $this->result='';
        }
    }

    public function render()
    {
        return view('livewire.fushan.tree-dataviewer');
    }
}
