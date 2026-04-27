<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingBase;
use App\Models\FsSeedlingCov;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;

use App\Jobs\FsSeedlingDataCheck;
use App\Jobs\FsSeedlingRecruitCheck;

use App\Jobs\SeedlingAddButton;

//小苗資料輸入後的所有儲存與刪除

class SeedlingSaveController extends Controller
{
    private function noteTypeFromMessage(string $message, bool $hasError = false): string
    {
        if ($message === '') {
            return '';
        }

        return $hasError ? 'error' : 'success';
    }

    private function noteField(string $name, string $message, bool $hasError = false): array
    {
        return [
            $name => $message,
            $name . '_type' => $this->noteTypeFromMessage($message, $hasError),
        ];
    }

    public function getTableInstance($entry)
    {
        if ($entry == '1') {
            return new FsSeedlingSlrecord1;
        } else {
            return new FsSeedlingSlrecord2;
        }
    }

    public function getTableInstanceCov($entry)
    {
        if ($entry == '1') {
            return new FsSeedlingSlcov1;
        } else {
            return new FsSeedlingSlcov2;
        }
    }

    public function getTableInstanceRoll($entry)
    {
        if ($entry == '1') {
            return new FsSeedlingSlroll1;
        } else {
            return new FsSeedlingSlroll2;
        }
    }

    public function getRedata($entry, $trap)
    {
        //存檔後都需重新產生資料
        $table = $this->getTableInstance($entry);

        $redata = $table::where('trap', 'like', $trap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();


        $ob_redata = new SeedlingAddButton;
        $redata = $ob_redata->addbutton($redata, $entry);

        return $redata;
    }

    //輸入完成後檢查
    public function finishnote(Request $request, $entry)
    {

        $tablecov = $this->getTableInstanceCov($entry);
        $table = $this->getTableInstance($entry);
        $pass = '1';
        $finishnote = '';
        $cov = $tablecov::query()->where('date', 'like', '0000-00-00')->get();
        if (count($cov) != '0') {
            foreach ($cov as $temp) {
                $traplist[] = $temp['trap'];
            }
            $traplist = array_unique($traplist);
            sort($traplist);
            $string = implode(", ", $traplist);
            $finishnote = '有資料未輸入完成 [' . $string . ']';
        } else {


            $data = $table::query()->where('date', 'like', '0000-00-00')->get();
            if (count($data) != '0') {
                foreach ($data as $temp) {
                    $traplist[] = $temp['trap'];
                }
                $traplist = array_unique($traplist);
                sort($traplist);
                $string = implode(", ", $traplist);
                $finishnote = '有資料未輸入完成 [' . $string . ']';
            }
        }

        if ($finishnote == '') {
            $finishnote = '輸入完成';
        }

        // echo $user;
        return [
            'result' => 'ok',
            'pass' => $pass,
            ...$this->noteField('finishnote', $finishnote, $pass !== '1')
        ];
    }
    //地被資料儲存
    public function savecov(Request $request)
    {
        $user = $request->user();

        $data_all = request()->all();
        // print_r($savecov);
        $savecov = $data_all['data'];
        $entry = $data_all['entry'];

        $covsavenote = '';
        $hasCovError = false;

        $tablecov = $this->getTableInstanceCov($entry);

        for ($i = 0; $i < count($savecov); $i++) {

            if ($savecov[$i]['date'] == '') {
                $savecov[$i]['date'] = '0000-00-00';
            }
            //地被資料基本檢查
            if ($savecov[$i]['date'] == '0000-00-00') {
                $covsavenote = '需有日期資料';
                $hasCovError = true;
                break;
            }

            if ($savecov[$i]['canopy'] == '' || $savecov[$i]['date'] == '' || $savecov[$i]['cov'] == '') {
                $covsavenote = '資料有空白值';
                $hasCovError = true;
                break;
            }

            if ($savecov[$i]['cov'] < 0 || $savecov[$i]['cov'] > 100) {
                $covsavenote = '覆蓋度資料有誤';
                $hasCovError = true;
                break;
            } else {


                $tablecov::where('id', $savecov[$i]['id'])->update(['cov' => $savecov[$i]['cov'], 'date' => $savecov[$i]['date'], 'canopy' => $savecov[$i]['canopy'], 'note' => $savecov[$i]['note'], 'updated_id' => $user->name]);
                //重新下載資料


                $covsavenote = '已儲存環境資料';
            }
        }


        return [
            'result' => 'ok',
            // 'covs' => $slcov,
            ...$this->noteField('covsavenote', $covsavenote, $hasCovError),

        ];
    }

    //小苗資料儲存
    public function savedata(Request $request)
    {

        $data_all = request()->all();
        // // print_r($savecov);
        $data = $data_all['data'];
        $entry = $data_all['entry'];
        $user = $data_all['user'];

        // $user=$data[0]['user'];
        // // $temp=[];
        // $list='';
        $datasavenote = '';
        $hasDataError = false;

        $table = $this->getTableInstance($entry);

        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['date'] == '') {
                $data[$i]['date'] = '0000-00-00';
            }

            // $list[]=$data[$i]['tag'];
            $uplist = [];
            //需有資料  
            $datacheck = ['pass' => '1', 'datasavenote' => ''];
            //舊苗檢查
            $check = new FsSeedlingDataCheck;
            $datacheck = $check->check($data[$i], $table);
            $data[$i] = $datacheck['data'];

            //修改tag  //如果是修改新增小苗的號碼，則mtag也要一起修改
            $alterdata = [];
            $slrecord = $table::where('id', 'like', $data[$i]['id'])->first();

            if (!$slrecord) {
                $datasavenote = '找不到要儲存的小苗資料。';
                $hasDataError = true;
                break;
            }

            $slrecord = $slrecord->toArray();

            if ($data[$i]['tag'] != $slrecord['tag']) {
                $data[$i]['tag'] = strtoupper($data[$i]['tag']);
                $mtag = explode('.', trim($data[$i]['tag']));
                $data[$i]['mtag'] = $mtag[0];
            }
            //如果原本的status是N，後來不是N (A, G, D)，新增alternote說明
            //echo 'recruit: '.$data[$i]['recruit'];
            if ($slrecord['recruit'] == 'N' && $data[$i]['status'] != 'N') {

                if ($data[$i]['alternote'] != '') {
                    $alterdata = json_decode($data[$i]['alternote'], true);  //把json轉array
                }
                $alterdata['other'] = '原消失已被找到';

                $data[$i]['alternote'] = json_encode($alterdata, JSON_UNESCAPED_UNICODE);  //把array轉json
            }

            // if ($data[$i]['ht'] !='-2' && $slrecord['ht']!='-2'){
            //     $data[$i]['recruit'] ='S';
            // }

            if ($datacheck['pass'] == 1) {
                // ['year' => date('Y'), 'month' => $month, 'date' => '0000-00-00']
                foreach ($data[$i] as $key => $value) {
                    // dd($key);
                    if (!in_array($key, ['user', 'entry', 'updated_at', 'updated_id', 'alternotetable'])) {
                        if ($slrecord[$key] != $value) {
                            $uplist[$key] = trim($value);
                        }
                    }
                }
                // dd($uplist);
                // $uplist2="['updated_id' => 'test']";
                if ($uplist != []) {  //有資料要存
                    $list = $data[$i]['tag'];
                    $uplist['updated_id'] = $user;

                    $table::where('id', 'like', $data[$i]['id'])->update($uplist);

                    $datasavenote = '資料已儲存';
                }
            } else {
                $datasavenote = $datacheck['datasavenote'];
                $hasDataError = true;
                break;
            }
        } //最外層


        $redata = $this->getRedata($entry, $data[0]['trap']);

        return [
            'result' => 'ok',
            // 'uplist' => $uplist,
            'data' => $redata,
            // 'list' => $list,
            ...$this->noteField('datasavenote', $datasavenote, $hasDataError)

        ];
    }
    //新增苗儲存

    public function saverecruit(Request $request)
    {

        $data = request()->all();
        // print_r($savecov);
        $recruit = $data['data'];
        $entry = $data['entry'];
        $user = $data['user'];
        $pps = (int) ($data['pps'] ?? 20);
        if (!in_array($pps, [20, 40], true)) {
            $pps = 20;
        }
        $recruitsavenote = '';
        $hasRecruitError = false;
        $nonsavelist = [];
        $appendRecruitNote = function (string $message) use (&$recruitsavenote) {
            $recruitsavenote .= ($recruitsavenote === '' ? '' : '<br>') . $message;
        };
        $savedRecruitTag = null;
        $savedRecruitTrap = null;


        $table = $this->getTableInstance($entry);

        // $temp=[[]];

        for ($i = 0; $i < count($recruit); $i++) {
            // $recruitsavenote='';

            if ($recruit[$i]['date'] == '') {
                // $recruitsavenote = '資料不完整';
                $nonsavelist[$i] = $recruit[$i];
                continue;
            }

            if ($recruit[$i]['tag'] == '') {
                $nonsavelist[$i] = $recruit[$i];
                continue;
            } else {
                $recruit[$i]['tag'] = strtoupper($recruit[$i]['tag']); //轉為大寫

                if ($recruit[$i]['plot'] == '' || $recruit[$i]['csp'] == '' || $recruit[$i]['ht'] == '' || $recruit[$i]['leafno'] == '') {
                    $appendRecruitNote('第' . ($i + 1) . '筆資料 資料不完整');
                    $hasRecruitError = true;
                    $nonsavelist[$i] = $recruit[$i];
                    continue;
                }
                if ($recruit[$i]['cotno'] == '') {
                    $recruit[$i]['cotno'] = 0;
                }
                $mtag = explode('.', $recruit[$i]['tag']);
                $recruit[$i]['mtag'] = $mtag[0];

                $datacheck = ['pass' => '1', 'datasavenote' => ''];

                if ($recruit[$i]['tofix'] == '1') {  //勾選為漏資料
                    //找舊資料
                    $seedling = FsSeedlingData::where('tag', 'like', $recruit[$i]['tag'])->orderBy('census', 'DESC')->get();
                    if ($seedling->isEmpty()) {
                        $datacheck['datasavenote'] = ($datacheck['datasavenote'] === '' ? '' : '<br>') . '第' . ($i + 1) . '筆 查無舊資料';
                        $datacheck['pass'] = "0";
                        $hasRecruitError = true;
                    } else {

                        if ($recruit[$i]['x'] == '') {
                            $base = FsSeedlingBase::where('mtag', 'like', $recruit[$i]['mtag'])->get();
                            $recruit[$i]['x'] = $base[0]['x'];
                            $recruit[$i]['y'] = $base[0]['y'];
                        }

                        $recruit[$i]['status'] = 'A';
                        $recruit[$i]['recruit'] = 'O';
                        $recruit[$i]['alternotetable'] = "{\"other\":\"漏資料\"}";

                        $includeKeys = ['trap', 'plot', 'csp', 'sprout'];
                        foreach ($recruit[$i] as $key => $value) {

                            if (in_array($key, $includeKeys)) {
                                if ($seedling[0][$key] != $value) {
                                    $appendRecruitNote($recruit[$i]['tag'] . ' 漏資料，但基本資料 ' . $key . ' 與原始資料不符。以舊資料儲存，如需修改，請填寫特殊修改。');
                                    $hasRecruitError = true;
                                    $recruit[$i][$key] = $seedling[0][$key];
                                }
                            }
                        }
                        //漏資料的舊苗走舊苗的檢查
                        $check = new FsSeedlingDataCheck;
                        $datacheck = $check->check($recruit[$i], $table);
                    }
                } else {
                    //新增苗檢查
                    $check = new FsSeedlingRecruitCheck;
                    $datacheck = $check->check($recruit[$i], $entry, $i);
                }



                // //補上資料庫其他欄位的資料       
                if ($datacheck['pass'] == 1) {

                    $recruit[$i] = $datacheck['data'];

                    $census = $table::first();
                    $recruit[$i]['status'] = 'A';
                    $recruit[$i]['census'] = $census['census'];
                    $recruit[$i]['year'] = $census['year'];
                    $recruit[$i]['month'] = $census['month'];

                    $recruit[$i]['id'] = '0';
                    $recruit[$i]['ind'] = '1';
                    if (!isset($recruit[$i]['note'])) {
                        $recruit[$i]['note'] = '';
                    }
                    if (!isset($recruit[$i]['alternotetable'])) {
                        $recruit[$i]['alternote'] = '';
                    } else {
                        $recruit[$i]['alternote'] = $recruit[$i]['alternotetable'];
                        unset($recruit[$i]['alternotetable']);
                    }
                    unset($recruit[$i]['tofix']);

                    $recruit[$i]['updated_id'] = $user;
                    $recruit[$i]['updated_at'] = date("Y-m-d H:i:s");

                    //存檔
                    $insert2 = [];

                    foreach ($recruit[$i] as $key => $value) {
                        $insert2[$key] = $value;
                        // $insertkey=$insertkey.$key.",";
                        // $insertvalue=$insertvalue."'".trim($value)."',";

                    }
                    //產生空白表
                    $nonsavelist[$i]['date'] = '';
                    $nonsavelist[$i]['trap'] = $recruit[$i]['trap'];
                    $nonsavelist[$i]['recruit'] = 'R';
                    $nonsavelist[$i]['sprout'] = 'FALSE';
                    $nonsavelist[$i]['tag'] = '';
                    $nonsavelist[$i]['csp'] = '';
                    $nonsavelist[$i]['ht'] = '';
                    $nonsavelist[$i]['cotno'] = '';
                    $nonsavelist[$i]['leafno'] = '';
                    $nonsavelist[$i]['x'] = '';
                    $nonsavelist[$i]['y'] = '';
                    $nonsavelist[$i]['note'] = '';
                    $nonsavelist[$i]['tofix'] = '';



                    $table::insert($insert2);

                    $appendRecruitNote('第' . ($i + 1) . '筆資料已儲存');
                    $savedRecruitTag = $recruit[$i]['tag'];
                    $savedRecruitTrap = $recruit[$i]['trap'];
                } else {  // $datacheck['pass']!=1
                    $appendRecruitNote($datacheck['datasavenote']);
                    $hasRecruitError = true;
                    $nonsavelist[$i] = $recruit[$i];
                    // break;

                }
            }  //來自 tag
        } //最外層

        //maxid
        $maxid = FsSeedlingSlrecord::count();

        //重新載入資料
        $thispage = '1';

        $resultTrap = $savedRecruitTrap ?? ($recruit[0]['trap'] ?? null);
        $redata = $resultTrap ? $this->getRedata($entry, $resultTrap) : [];
        if ($savedRecruitTag !== null) {
            foreach ($redata as $key => $value) {
                if ($value['tag'] == $savedRecruitTag) {
                    $thispage = (string) ceil(($key + 1) / $pps);
                    break;
                }
            }
        }


        return [
            'result' => 'ok',
            'data' => $recruit,
            'recruit' => $redata,
            'thispage' => $thispage,
            'maxid' => $maxid,
            'nonsavelist' => $nonsavelist,
            // 'temp' => $temp,
            ...$this->noteField('recruitsavenote', $recruitsavenote, $hasRecruitError)
            // 'insert' => $insert2


        ];
    }

    //刪除新增苗資料

    public function deletedata(Request $request, $tag, $entry, $thispage)
    {

        // $user='chialing';
        $datasavenote = '';

        $table = $this->getTableInstance($entry);

        $trap = $table::where('tag', 'like', $tag)->get();
        $thistrap = $trap[0]['trap'];
        $total = $table::where('trap', 'like', $thistrap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();

        $d_record = $table::where('tag', 'like', $tag)->delete();

        $datasavenote = '已刪除 ' . $tag . ' 新增小苗資料';
        $maxid = FsSeedlingSlrecord::count();

        $redata = $this->getRedata($entry, $thistrap);


        return [
            'result' => 'ok',
            // 'test'=> $test,
            'thispage' => $thispage,
            'recruit' => $redata,
            'maxid' => $maxid,
            ...$this->noteField('datasavenote', $datasavenote)
        ];
    }

    //撿到環儲存
    public function saveslroll(Request $request, $entry, $trap)
    {
        // $test='';
        $user = $request->user();

        $tableroll = $this->getTableInstanceRoll($entry);
        $tablecov = $this->getTableInstanceCov($entry);
        $slrollsavenote = '';
        $hasRollError = false;
        $slrolldata = request()->all();
        $slrollnew = $slrolldata['data'];

        $insert1 = '';
        for ($i = 0; $i < count($slrollnew); $i++) {
            $uplist = [];
            if (empty($slrollnew[$i])) break;


            if ($slrollnew[$i]['date'] == '') {
                if (
                    ($slrollnew[$i]['plot'] ?? '') !== ''
                    || ($slrollnew[$i]['tag'] ?? '') !== ''
                    || ($slrollnew[$i]['note'] ?? '') !== ''
                ) {
                    $slrollsavenote = '撿到環資料需填寫日期。';
                    $hasRollError = true;
                }
                break;
            }

            if ($slrollnew[$i]['trap'] == '' || $slrollnew[$i]['plot'] == '' || $slrollnew[$i]['tag'] == '') {
                $slrollsavenote = '撿到環資料有空白值。';
                $hasRollError = true;
                break;
            }

            if (isset($slrollnew[$i]['id'])) {
                // 比對舊資料

                $olddata = $tableroll::where('id', 'like', $slrollnew[$i]['id'])->get();

                foreach ($slrollnew[$i] as $key => $value) {
                    if ($key != 'updated_id' && $key != 'updated_at' && $key != 'delete') {
                        if ($olddata[0][$key] != $value) {
                            $uplist[$key] = trim($value);
                        }
                    }
                }


                if ($uplist != []) {  //有資料要存
                    // $list=$data[$i]['tag'];
                    $uplist['updated_id'] = $user->name;

                    $tableroll::where('id', 'like', $slrollnew[$i]['id'])->update($uplist);

                    $slrollsavenote = '資料已儲存';
                }
            } else { //新資料
                $insertkey = '';
                $insertvalue = '';
                $insert2 = [];
                $slrollnew[$i]['updated_at'] = date("Y-m-d H:i:s");
                $cov = $tablecov::first();
                if (!$cov) {
                    $slrollsavenote = '找不到本次輸入對應的環境資料，無法儲存撿到環。';
                    $hasRollError = true;
                    break;
                }
                // 存檔
                $slrollnew[$i]['month'] = $cov['month'];
                $slrollnew[$i]['year'] = $cov['year'];
                $slrollnew[$i]['id'] = '0';

                foreach ($slrollnew[$i] as $key => $value) {
                    if ($key != 'delete' && $key != 'updated_id') {
                        $insertkey = $insertkey . $key . ",";
                        $insertvalue = $insertvalue . "'" . trim($value) . "',";
                        $insert2[$key] = $value;
                    }
                }


                $insertkey = $insertkey . 'updated_id';
                $insertvalue = $insertvalue . "'" . $user . "'";
                $insert2['updated_id'] = $user->name;


                $tableroll::insert($insert2);

                $slrollsavenote = '資料已儲存';
            }
        }

        // //重新載入資料

        $slroll = $tableroll::where('trap', 'like', $trap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();




        if (!$slroll->isEmpty()) {
            $slroll = $slroll->toArray();
            for ($m = 0; $m < count($slroll); $m++) {
                $slroll[$m]['delete'] = "<button class='deleteroll' deleteid='" . $slroll[$m]['id'] . "' tag='" . $slroll[$m]['tag'] . "' entry='" . $entry . "' trap='" . $trap . "'>X</button>";
            }
        } else {
            $slroll = [];
        }


        return [
            'result' => 'ok',
            'entry' => $entry,
            'data' => $slroll,
            'text' => $slrollnew,
            'trap' => $trap,
            ...$this->noteField('slrollsavenote', $slrollsavenote, $hasRollError)

        ];
    }
    //刪除撿到環資料

    public function deleteslroll($tag, $id, $entry, $trap)
    {

        $slrollsavenote = '';
        $tableroll = $this->getTableInstanceRoll($entry);

        $tableroll::where('id', 'like', $id)->delete();

        $slrollsavenote = '已刪除 ' . $tag . ' 撿到環資料';

        // 重新載入資料


        $slroll = $tableroll::where('trap', 'like', $trap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();


        if (!$slroll->isEmpty()) {
            $slroll = $slroll->toArray();
            for ($m = 0; $m < count($slroll); $m++) {
                $slroll[$m]['delete'] = "<button class='deleteroll' deleteid='" . $slroll[$m]['id'] . "' tag='" . $slroll[$m]['tag'] . "' entry='" . $entry . "' trap='" . $trap . "'>X</button>";
            }
        } else {
            $slroll = [];
            $slroll[0]['year'] = '';
            $slroll[0]['month'] = '';
        }


        return [
            'result' => 'ok',
            'data' => $slroll,
            'trap' => $trap,
            ...$this->noteField('slrollsavenote', $slrollsavenote)
        ];
    }



    //儲存特殊修改

    public function savealternote(Request $request)
    {

        $data_all = request()->all();

        $data = $data_all['data'][0];
        $entry = $data_all['entry'];
        $thispage = $data_all['thispage'];
        $authUser = $request->user();
        $user = $authUser?->name
            ?? $authUser?->account
            ?? ($authUser?->id ? (string) $authUser->id : null)
            ?? ($data_all['user'] ?? '');
        $datasavenote = '';
        $table = $this->getTableInstance($entry);
        $olddata = $table::where('id', 'like', $data['id'])->first();

        if (!$olddata) {
            Log::warning('seedling.savealternote.not_found', [
                'entry' => $entry,
                'thispage' => $thispage,
                'record_id' => $data['id'] ?? null,
                'payload' => $data,
            ]);

            return response()->json([
                'result' => 'error',
                'datasavenote' => '找不到要儲存特殊修改的小苗資料。',
                'datasavenote_type' => 'error',
            ], 404);
        }

        $data2 = array_filter($data, function ($value, $key) {
            return $key !== 'id' && $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $alterdata = !empty($data2)
            ? json_encode($data2, JSON_UNESCAPED_UNICODE)
            : '';

        $affectedRows = 0;

        if ($olddata->alternote != $alterdata) {
            $uplist = ['alternote' => $alterdata];

            $uplist['updated_id'] = $user !== ''
                ? $user
                : ($olddata->updated_id ?: 'system');

            $affectedRows = $table::where('id', 'like', $data['id'])->update($uplist);
        }

        $datasavenote = '資料已儲存';

        Log::info('seedling.savealternote.result', [
            'entry' => $entry,
            'thispage' => $thispage,
            'record_id' => $data['id'] ?? null,
            'tag' => $olddata->tag ?? null,
            'trap' => $olddata->trap ?? null,
            'user' => $user,
            'affected_rows' => $affectedRows,
            'alterdata' => $alterdata,
        ]);

        //重新載入資料
        $maxid = FsSeedlingSlrecord::count();

        $redata = $this->getRedata($entry, $olddata->trap);


        return [
            'result' => 'ok',
            ...$this->noteField('datasavenote', $datasavenote),
            'data' => $redata,
            'maxid' => $maxid,
            'thispage' => $thispage
            // 'thispage' => $thispage
            // 'inlist'=>$sql
        ];
    }

    //刪除特殊修改
    public function deletealter(Request $request, $tag, $entry, $thispage)
    {
        Log::info('seedling.deletealter.start', [
            'tag' => $tag,
            'entry' => $entry,
            'thispage' => $thispage,
            'method' => $request->method(),
            'user' => $request->user()?->name,
        ]);

        $table = $this->getTableInstance($entry);

        $datasavenote = '';

        $table::where('tag', 'like', $tag)->update(['alternote' => '']);
        // $test='y';

        $datasavenote = '已刪除 ' . $tag . ' 特殊修改資料';


        //重新載入資料
        $olddata = $table::where('tag', 'like', $tag)->get()->toArray();
        if (empty($olddata)) {
            Log::warning('seedling.deletealter.not_found', [
                'tag' => $tag,
                'entry' => $entry,
            ]);

            return response()->json([
                'result' => 'error',
                'datasavenote' => '找不到要刪除特殊修改的小苗資料。',
                'datasavenote_type' => 'error',
            ], 404);
        }
        $maxid = FsSeedlingSlrecord::count();

        // $redata='1';

        $redata = $this->getRedata($entry, $olddata[0]['trap']);



        $realterdata = ['Tag' => '', 'Trap' => '', 'Plot' => '', '原長度' => '', '原葉片數' => '', '狀態' => '', 'id' => $olddata[0]['id']];
        $havedata = 'no';

        return [
            'result' => 'ok',
            // 'test'=> $test,
            'thispage' => $thispage,
            'data' => $redata,
            'maxid' => $maxid,
            'realterdata' => $realterdata,
            'havedata' => $havedata,

            ...$this->noteField('datasavenote', $datasavenote)
        ];
    }
}
