<?php

namespace App\Http\Livewire\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

use App\Models\Ss1haData2015;
use App\Models\Ss1haData2024;
use App\Models\Ss1haBase2015;
use App\Models\Ss1haBase2024;
use App\Models\Ss1haBaseR2024;
use App\Models\SsFixlog;


//1ha樹位置圖輸入
class S1haMap extends Component
{


    public $user;
    public $site;
    public $data;
    public $qx;
    public $qy;
    public $record;


    public $showdata;

    public function mount(){


    }


    public function submitForm(Request $request){
        if ($this->qx!=''){
            $this->searchSite($request, $this->qx, $this->qy);
        }
        $this->datasavenote='';
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


    public function searchSite(Request $request, $qx, $qy){
            $R='N';

            $data = Ss1haBase2024::select('1ha_base_2024.*', '1ha_data_2024.status', '1ha_base_2024.updated_at as update_date')->join('1ha_data_2024', '1ha_data_2024.tag', '=', '1ha_base_2024.tag')->where('1ha_data_2024.date', '!=', '0000-00-00')->where('1ha_data_2024.branch', '==', '0')->where('1ha_base_2024.deleted_at', 'like', '')->where('1ha_base_2024.qx', 'like', $qx)->where('1ha_base_2024.qy', 'like', $qy)->orderBy('1ha_base_2024.qudx')->orderBy('1ha_base_2024.qudy')->orderBy('1ha_base_2024.tag')->get()->toArray();

            // $dataR=Ss1haBaseR2024::where('qx','like', $qx)->where('qy', 'like', $qy)->where('subqx','like', $sqx)->where('subqy', 'like', $sqy)->orderBy('tag')->get()->toArray();
            $dataR = Ss1haBaseR2024::select('1ha_base_r_2024.*', '1ha_data_2024.status', '1ha_base_r_2024.updated_at as update_date')->join('1ha_data_2024', '1ha_data_2024.stemid', '=', '1ha_base_r_2024.stemid')->where('1ha_data_2024.date', '!=', '0000-00-00')->where('1ha_base_r_2024.deleted_at', 'like', '')->where('1ha_base_r_2024.qx', 'like', $qx)->where('1ha_base_r_2024.qy', 'like', $qy)->orderBy('1ha_base_r_2024.qudx')->orderBy('1ha_base_r_2024.qudy')->orderBy('1ha_base_r_2024.stemid')->get()->toArray();
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

  
        //     $data=Ss1haBase2024::query()->join('1ha_data_2024', '1ha_base_2024.tag', '=', '1ha_data_2024.tag')->where('1ha_base_2024.qx','like', $qx)->where('1ha_base_2024.qy', 'like', $qy)->where('1ha_base_2024.subqx','like', $sqx)->where('1ha_base_2024.subqy', 'like', $sqy)->where('qudx', 'like', '0')->where('1ha_data_2024.branch', '=', '0')->where('1ha_data_2024.status', '=', '-9')->orderBy('1ha_base_2024.tag')->get()->toArray();

        
// dd($qx);
// dd($result);
        $this->data=$result;
        $this->datanote=$datanote;
        $this->result=$result;
        $this->qx=$qx;
        $this->qy=$qy;
        $this->showdata='1';
        $this->tablePlot=$qx.$qy;

        $this->tag='';
        $this->x='';
        $this->y='';
        $this->showmap();


        $this->dispatchBrowserEvent('initTablesorter', ['plot' => $this->tablePlot, 'tablePlot'=>$this->tablePlot, 'data' => $this->result, 'mapfile'=>$this->filePath[0]]);
        
      //  dd($data);

    }



    
    public $error;

    public function showmap(){

        $fileqx=str_pad($this->qx, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy, 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;
        $filecensus='ss_1ha_census2_scanfile';

        $filePath=[];
        $filePath[0]=$filecensus."/".$fileqx.'/map/'.$filesqx.'.png';
        $filePath[1]=$filecensus."/".$fileqx.'/orimap/'.$filesqx.'.png';
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
            $stemdata=Ss1haBaseR2024::where('stemid', 'like', $tag)->get()->toArray();
            // if (count($stemdata)==0){
            //     $tag=$tag.'0';
            //     $stemdata=Ss1haBaseR2024::where('stemid', 'like', $tag)->get()->toArray();
            // }
        } else {
            $stemdata=Ss1haBase2024::where('tag', 'like', $tag)->get()->toArray();
        }

        $plotx=$stemdata[0]['qx']*10+$qudx;
        $ploty=$stemdata[0]['qy']*10+$qudy;

        $sqx=intval(ceil($qudx/5));
        $sqy=intval(ceil($qudy/5));

        $uplist=['qudx' =>$qudx, 'qudy' => $qudy, 'plotx'=>$plotx, 'ploty'=>$ploty, 'updated_id' =>$this->user];

        $fixlog['type']='update';
        $fixlog['id']='0';
        $fixlog['from']='map';
        
        $fixlog['qx']=$stemdata[0]['qx'];
        $fixlog['stemid']=$tag;
        $fixlog['descript']=json_encode($uplist, JSON_UNESCAPED_UNICODE);
        $fixlog['updated_id']=$this->user;
        $fixlog['updated_at']=date("Y-m-d H:i:s");

        if ($rtype=='R'){
            Ss1haBaseR2024::where('stemid', 'like', $tag)->update($uplist);
            $fixlog['sheet']='1ha_base_r_2024';
        } else {
            Ss1haBase2024::where('tag', 'like', $tag)->update($uplist);
            $fixlog['sheet']='1ha_base_2024';
        }
        SsFixlog::insert($fixlog);

        $datasavenote='已更新資料';
        if ($sqx!=$stemdata[0]['sqx'] || $sqy!=$stemdata[0]['sqy']){
            $datasavenote.='。但('.$tag.')小區不符，請確認。';
        }

        $this->datasavenote=$datasavenote;
        $this->searchSite($request, $this->qx, $this->qy);

        // dd($uplist);
        // dd($data);

    }


    protected $listeners2 = ['updateSavenote' => 'updateSavenote'];

    public function updateSavenote(){
        $this->datasavenote='';
    }


    public function render()
    {
        return view('livewire.shoushan.s1ha-map');
    }
}
