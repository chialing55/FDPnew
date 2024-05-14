<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;


use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeEntrycom;
use App\Models\FsTreeCensus5;

use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;
use App\Models\FsTreeFixlog;

use App\Jobs\FsTreeAddButton;
use App\Jobs\FsTreeCensus5Progress;

class TreeMap extends Component
{

    public $user;
    public $site;
    public $data;
    public $qx;
    public $qy;
    public $subqx;
    public $subqy;
    public $sqx;
    public $sqy;
    public $record;

    public $alternotelist=[];
    public $showdata;

    public function mount(){

        $ob_result = new FsTreeCensus5Progress;
        $result=$ob_result->showProgress();

        // Extract only the directory names
        $this->alternotelist = $result['alternotelist'];
        // dd($this->directories);
    }


    public function submitForm(Request $request){
        if ($this->qx!=''){
            $this->searchSite($request, $this->qx, $this->qy, 1, 1);
        }

    }

    public function submitsqxForm(Request $request, $subqx, $subqy){

        $this->searchSite($request, $this->qx, $this->qy, $subqx, $subqy);

    }
    public $result;
    public $tablePlot;

    public $dataR;
    public $datanote;
    public $filePath;

//排序資料，把空白資料放前面
    public function customSort($a, $b) {
        // 检查 $a 是否满足条件
        $aCondition = $a['qudx'] == '0';
        // 检查 $b 是否满足条件
        $bCondition = $b['qudx'] == '0';

        // 如果 $a 满足条件而 $b 不满足条件，将 $a 放在前面
        if ($aCondition && !$bCondition) {
            return -1;
        }
        // 如果 $b 满足条件而 $a 不满足条件，将 $b 放在前面
        elseif (!$aCondition && $bCondition) {
            return 1;
        }
        // 如果都满足条件或都不满足条件，则按照 'tag' 键排序
        else {
            return $a['tag'] <=> $b['tag'];
        }
    }


    public function searchSite(Request $request, $qx, $qy, $subqx, $subqy){
            $R='N';

            $data = FsTreeBase::select('base.*', 'census5.status', 'base.updated_at as update_date')->join('census5', 'census5.tag', '=', 'base.tag')->where('census5.date', '!=', '0000-00-00')->where('census5.branch', '==', '0')->where('base.deleted_at', 'like', '')->where('base.qx', 'like', $qx)->where('base.qy', 'like', $qy)->where('base.subqx','like', $subqx)->where('base.subqy', 'like', $subqy)->orderBy('base.qudx')->orderBy('base.qudy')->orderBy('base.tag')->get()->toArray();

            // $dataR=FsTreeBaseR::where('qx','like', $qx)->where('qy', 'like', $qy)->where('subqx','like', $subqx)->where('subqy', 'like', $subqy)->orderBy('tag')->get()->toArray();
            $dataR = FsTreeBaseR::select('base_r.*', 'census5.status', 'base_r.updated_at as update_date')->join('census5', 'census5.stemid', '=', 'base_r.stemid')->where('census5.date', '!=', '0000-00-00')->where('base_r.deleted_at', 'like', '')->where('base_r.qx', 'like', $qx)->where('base_r.qy', 'like', $qy)->where('base_r.subqx','like', $subqx)->where('base_r.subqy', 'like', $subqy)->orderBy('base_r.qudx')->orderBy('base_r.qudy')->orderBy('base_r.stemid')->get()->toArray();
// dd($data);
            foreach ($dataR as &$datar){   //加上&就可以更改$datar
                $datar['tag']=$datar['stemid'];
                $datar['type']="R";
            }

            foreach ($data as &$datae){
                $datae['type']="all";
            }

            $mergedData = array_merge($data, $dataR);
            $result = array_values($mergedData);//重新給key


// 使用 usort 函数对数组进行排序
            usort($result, array($this, 'customSort'));
            // $tag = array_column($result, 'tag');
            // array_multisort($tag, SORT_ASC, $result);
            // dd($result);

            $d = 0;

            foreach ($result as $item) {
                if ($item['qudx'] == '0' && $item['qudy'] == '0') {
                    $d++;
                }
            }


            $datanote='共 '.count($result).' 筆。其中有 '.$d.' 筆缺值';

  
        //     $data=FsTreeBase::query()->join('census5', 'base.tag', '=', 'census5.tag')->where('base.qx','like', $qx)->where('base.qy', 'like', $qy)->where('base.subqx','like', $subqx)->where('base.subqy', 'like', $subqy)->where('qudx', 'like', '0')->where('census5.branch', '=', '0')->where('census5.status', '=', '-9')->orderBy('base.tag')->get()->toArray();

        
// dd($qx);
// dd($result);
        $this->data=$result;
        $this->datanote=$datanote;
        $this->result=$result;
        $this->qx=$qx;
        $this->qy=$qy;
        $this->subqx=$subqx;
        $this->subqy=$subqy;
        $this->showdata='1';
        $this->tablePlot=$qx.$qy.$subqx.$subqy;

        $this->tag='';
        $this->x='';
        $this->y='';
        $this->showmap();


        $this->dispatchBrowserEvent('initTablesorter', ['tablePlot'=>$this->tablePlot, 'data' => $this->result, 'mapfile'=>$this->filePath[0]]);
        
      //  dd($data);

    }



    
    public $error;

    public function showmap(){

        $fileqx=str_pad($this->qx, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy, 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;
        $filecensus='fs_census5_scanfile';
        $filemap='';

        if ($this->subqx==1 && $this->subqy==1){
            $filemap='01';
        } else if ($this->subqx==1 && $this->subqy==2){
            $filemap='02';
        } else if ($this->subqx==2 && $this->subqy==2){
            $filemap='03';
        } else {
            $filemap='04';
        }
        $filePath=[];
        $filePath[0]=$filecensus."/".$fileqx.'/map/'.$filesqx.$filemap.'.jpg';
        $filePath[1]=$filecensus."/".$fileqx.'/orimap/'.$filesqx.$filemap.'.jpg';
        $filePath[2]=$filecensus."/".$fileqx.'/old/'.$filesqx.'_old.pdf';
        $filePath[3]=$filecensus."/".$fileqx.'/new/'.$filesqx.'_new.pdf';
//確定是否有檔案
        $error=[];

        for($i=0;$i<4;$i++){
            $matchingFiles = glob(public_path($filePath[$i]) . '.', GLOB_BRACE | GLOB_NOCHECK);

            if (!empty($matchingFiles)) {

                $path[$i] = $filePath[$i];

            } else {
                $error[$i] = '沒有檔案 ' . $filePath[$i];
                $path[$i] = '';
            }
        }
        $this->error=$error;
        $this->filePath=$path;


    }



    protected $listeners = ['updateCoordinates' => 'updateCoordinates'];

    public $datasavenote='';

    public function updateCoordinates(Request $request, $data)
    {

        // dd($data);

        $qudx = $data['x'];
        $qudy = $data['y'];
        $tag = $data['tag'];
        $rtype = $data['rtype'];

        if ($rtype=='R'){
            $stemdata=FsTreeBaseR::where('stemid', 'like', $tag)->get()->toArray();
            // if (count($stemdata)==0){
            //     $tag=$tag.'0';
            //     $stemdata=FsTreeBaseR::where('stemid', 'like', $tag)->get()->toArray();
            // }
        } else {
            $stemdata=FsTreeBase::where('tag', 'like', $tag)->get()->toArray();
        }

        $plotx=$stemdata[0]['qx']*20+($stemdata[0]['subqx']-1)*10+$qudx;
        $ploty=$stemdata[0]['qy']*20+($stemdata[0]['subqy']-1)*10+$qudy;

        $uplist=['qudx' =>$qudx, 'qudy' => $qudy, 'plotx'=>$plotx, 'ploty'=>$ploty, 'update_id' =>$this->user];

        $fixlog['type']='update';
        $fixlog['id']='0';
        $fixlog['from']='map';
        
        $fixlog['qx']=$stemdata[0]['qx'];
        $fixlog['stemid']=$tag;
        $fixlog['descript']=json_encode($uplist, JSON_UNESCAPED_UNICODE);
        $fixlog['update_id']=$this->user;
        $fixlog['updated_at']=date("Y-m-d H:i:s");

        if ($rtype=='R'){
            FsTreeBaseR::where('stemid', 'like', $tag)->update($uplist);
            $fixlog['sheet']='base_r';
        } else {
            FsTreeBase::where('tag', 'like', $tag)->update($uplist);
            $fixlog['sheet']='base';
        }
        FsTreeFixlog::insert($fixlog);

        $this->datasavenote='已更新資料';
        $this->searchSite($request, $this->qx, $this->qy, $this->subqx, $this->subqy);

        // dd($uplist);
        // dd($data);

    }


    protected $listeners2 = ['updateSavenote' => 'updateSavenote'];

    public function updateSavenote(){
        $this->datasavenote='';
    }


    public function render()
    {
        return view('livewire.fushan.tree-map');
    }
}
