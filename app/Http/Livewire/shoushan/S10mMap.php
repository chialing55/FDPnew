<?php

namespace App\Http\Livewire\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

use App\Models\Ss10mTreeData2015;
use App\Models\Ss10mTreeData2024;
use App\Models\Ss10mTreeBase2015;
use App\Models\Ss10mTreeBase2024;
use App\Models\Ss10mTreeBaseR2024;
use App\Models\SsFixlog;


//10m樣區樹位置圖輸入
class S10mMap extends Component
{


    public $user;
    public $site;
    public $data;
    public $selectPlot;  //
    public $record;
    public $plots = array('A1', 'A2', 'A3', 'B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38'); 

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

//選擇樣區資料
    public function searchSite(Request $request, $selectPlot){
            $plot=$this->plots[$selectPlot];

            $R='N';

            $data = Ss10mTreeBase2024::select('10m_tree_base_2024.*', '10m_tree_data_2024.status', '10m_tree_base_2024.updated_at as update_date')->join('10m_tree_data_2024', '10m_tree_data_2024.tagid', '=', '10m_tree_base_2024.tagid')->where('10m_tree_data_2024.date', '!=', '0000-00-00')->where('10m_tree_data_2024.branch', '==', '0')->where('10m_tree_base_2024.deleted_at', 'like', '')->where('10m_tree_base_2024.plot', 'like', $plot)->orderBy('10m_tree_base_2024.qudx')->orderBy('10m_tree_base_2024.qudy')->orderBy('10m_tree_base_2024.tag')->get()->toArray();
// dd($data);
            // $dataR=Ss10mTreeBaseR2024::where('qx','like', $qx)->where('qy', 'like', $qy)->where('subqx','like', $sqx)->where('subqy', 'like', $sqy)->orderBy('tag')->get()->toArray();
            $dataR = Ss10mTreeBaseR2024::select('10m_tree_base_r_2024.*', '10m_tree_data_2024.status', '10m_tree_base_r_2024.updated_at as update_date')->join('10m_tree_data_2024', '10m_tree_data_2024.stemid', '=', '10m_tree_base_r_2024.stemid')->where('10m_tree_data_2024.date', '!=', '0000-00-00')->where('10m_tree_base_r_2024.deleted_at', 'like', '')->where('10m_tree_base_r_2024.plot', 'like', $plot)->orderBy('10m_tree_base_r_2024.qudx')->orderBy('10m_tree_base_r_2024.qudy')->orderBy('10m_tree_base_r_2024.stemid')->get()->toArray();
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
            // foreach($result as &$res){
            //     $res['tag']=$res['tagid'];
            //     $res['tag1']=$res['tag'];
            // }
// dd($result);

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



        
// dd($qx);
// dd($result);
        $this->data=$result;
        $this->datanote=$datanote;
        $this->result=$result;
        $this->selectPlot=$selectPlot;
        $this->showdata='1';
        $this->tablePlot=$this->plots[$selectPlot];

        $this->tag='';
        $this->x='';
        $this->y='';
        $this->showmap();


        $this->dispatchBrowserEvent('initTablesorter', ['plot' => $this->tablePlot, 'tablePlot'=>$this->tablePlot, 'data' => $this->result, 'mapfile'=>$this->filePath[0]]);
        
      //  dd($data);

    }



    
    public $error;
//顯示地圖檔案
    public function showmap(){
        $plot=$this->plots[$this->selectPlot];
        $filesqx=$plot;
        $filecensus='ss_10m_census3_scanfile';

        $filePath=[];
        $filePath[0]=$filecensus.'/map/'.$filesqx.'.png';
        $filePath[1]=$filecensus.'/orimap/'.$filesqx.'.png';
        $filePath[2]=$filecensus.'/old/'.$filesqx.'_old.pdf';
        $filePath[3]=$filecensus.'/new/'.$filesqx.'_new.pdf';
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
//儲存點圖資料
    public function updateCoordinates(Request $request, $data)
    {

        // dd($data);
        $plot=$this->plots[$this->selectPlot];
        // dd($plot);
        $qudx = $data['x'];
        $qudy = $data['y'];
        $tag = $data['tag'];
        $rtype = $data['rtype'];
        $tagid=$plot."-".$data['tag'];

        if ($rtype=='R'){
            $stemdata=Ss10mTreeBaseR2024::where('stemid', 'like', $tag)->get()->toArray();
            // if (count($stemdata)==0){
            //     $tag=$tag.'0';
            //     $stemdata=Ss10mTreeBaseR2024::where('stemid', 'like', $tag)->get()->toArray();
            // }
        } else {
            $stemdata=Ss10mTreeBase2024::where('tagid', 'like', $tagid)->get()->toArray();
        }


        $sqx=intval(ceil($qudx/5));
        $sqy=intval(ceil($qudy/5));

        $uplist=['qudx' =>$qudx, 'qudy' => $qudy, 'updated_id' =>$this->user];

        $fixlog['type']='update';
        $fixlog['id']='0';
        $fixlog['from']='map';
        
        $fixlog['qx']=$stemdata[0]['plot'];
        $fixlog['stemid']=$tagid;
        $fixlog['descript']=json_encode($uplist, JSON_UNESCAPED_UNICODE);
        $fixlog['updated_id']=$this->user;
        $fixlog['updated_at']=date("Y-m-d H:i:s");

        if ($rtype=='R'){
            Ss10mTreeBaseR2024::where('stemid', 'like', $tag)->update($uplist);
            $fixlog['sheet']='10m_tree_base_r_2024';
        } else {
            Ss10mTreeBase2024::where('tagid', 'like', $tagid)->update($uplist);
            $fixlog['sheet']='10m_tree_base_2024';
        }

        if($stemdata[0]['qudx']!='0' && $stemdata[0]['qudy']!='0'){
            SsFixlog::insert($fixlog);
        }


        $datasavenote='已更新資料';
        if ($sqx!=$stemdata[0]['sqx'] || $sqy!=$stemdata[0]['sqy']){
            $datasavenote.='。但('.$tag.')小區不符，請確認。';
        }

        $this->datasavenote=$datasavenote;
        $this->searchSite($request, $this->selectPlot);

        // dd($uplist);
        // dd($data);

    }


    protected $listeners2 = ['updateSavenote' => 'updateSavenote'];

    public function updateSavenote(){
        $this->datasavenote='';
    }


    public function render()
    {
        return view('livewire.shoushan.s10m-map');
    }
}
