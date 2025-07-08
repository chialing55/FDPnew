<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;
use App\Models\FsSeedsFixlog;

use App\Jobs\FsSeedsCheck;
use App\Jobs\SeedsAddButton;


class SeedsSaveController extends Controller
{


    public function getTableInstance($type) {
        if ($type == 'record') {
            return new FsSeedsRecord1;
        } else {
            return new FsSeedsFulldata;
        }
    }

//產生名錄
    public function spinfo(){
        $spinfolist=FsSeedsSplist::query()->get()->toArray();
        foreach ($spinfolist as $spinfo1){
            $spinfo[$spinfo1['csp']]=$spinfo1;
        }

        return $spinfo;
    }

//預設鑑定者
    public $identifier='黃小俊';

//重新載入資料
    public function getRedata(){

        $data1=FsSeedsRecord1::query()->get()->toArray();
        $ob_table = new SeedsAddButton;
        $redata=$ob_table->addbutton($data1, 'record');

        return $redata; 

    }
//重新載入資料2
    public function getRedata2($census){

        $data1=FsSeedsFulldata::where('census', 'like', $census)->orderby('trap')->get()->toArray();
        $ob_table = new SeedsAddButton;
        $redata=$ob_table->addbutton($data1, 'fulldata');

        return $redata; 

    }

//已輸入資料的修改與儲存
    public function savedata(Request $request, $type)
    {
        $table = $this->getTableInstance($type);
        $data_all = $request->all();
        $data = $data_all['data'];
        $user = $data_all['user'];
        $datasavenote = '';

        $spinfo = $this->spinfo();
        $ids = collect($data)->pluck('id')->filter()->unique()->toArray();

        // 一次取得舊資料，用 id 建立 map
        $existingRows = $table::whereIn('id', $ids)->get()->keyBy('id')->toArray();

        // 一次建立所有 checksign（重複比對用）
        $census = $data[0]['census'] ?? '';
        $existingSigns = ($type === 'record')
            ? \App\Models\FsSeedsRecord1::selectRaw("CONCAT(census, trap, csp, code) AS sign")->pluck('sign')->toArray()
            : \App\Models\FsSeedsFulldata::where('census', $census)
                ->selectRaw("CONCAT(census, trap, csp, code) AS sign")->pluck('sign')->toArray();

        $checker = new \App\Jobs\FsSeedsCheck;
        $lastUpdatedId = null;

        foreach ($data as $item) {
            $id = $item['id'];
            if (!isset($existingRows[$id])) continue;

            $original = $existingRows[$id];
            $item['trap'] = str_pad($item['trap'], 3, '0', STR_PAD_LEFT);

            $excludeFields = ['checknote', 'updated_at', 'updated_id'];
            $hasChange = false;
            $updatedes = [];

            foreach ($original as $key => $val) {
                if (in_array($key, $excludeFields)) continue;
                if ((string) $val !== (string) $item[$key]) {
                    $hasChange = true;
                    $updatedes[$key] = $val . "=>" . $item[$key];
                }
            }

            if (!$hasChange) continue;
            foreach ($item as $key => $val) {
                if (is_string($val)) {
                    $item[$key] = trim($val);
                }
            }

            $result = $checker->check($item, $spinfo, $existingSigns);
            $updated = $result['result'];
            $checknote = $result['checknote'];

            $uplist = [];
            foreach ($updated as $key => $val) {
                if ($key === 'd') continue;
                $uplist[$key] = $val ?? '';
            }

            $uplist['checknote'] = $checknote;
            $uplist['updated_id'] = $user;

            $table::where('id', $id)->update($uplist);
            $datasavenote = '已更新資料';
            $lastUpdatedId = $id;

            if ($type === 'fulldata' && !empty($updatedes)) {
                $updatedes['id'] = $id;
                \App\Models\FsSeedsFixlog::insert([
                    'id' => 0,
                    'type' => 'update',
                    'census' => $uplist['census'],
                    'descript' => json_encode($updatedes, JSON_UNESCAPED_UNICODE),
                    'updated_id' => $user,
                    'updated_at' => now()
                ]);
            }
        }

        // 回傳更新後頁面與資料
        if ($type === 'record') {
            $redata = $this->getRedata();
            $thispage = ceil($lastUpdatedId / 29);
        } else {
            $redata = $this->getRedata2($data[0]['census']);
            $k = ($lastUpdatedId - $redata[0]['id']) + 1;
            $thispage = ceil($k / 29);
        }
        if($lastUpdatedId === null) {
            $thispage = 1; // 如果沒有更新，則回到第一頁
        }
        return [
            'result' => 'ok',
            'data' => $redata,
            'thispage' => $thispage,
            'seedssavenote' => $datasavenote
        ];
    }

//空白表單的輸入與儲存
    public function savedata1(Request $request, $type)
    {
        $table = $this->getTableInstance($type);
        $data_all = $request->all();
        $data = $data_all['data'];

        $user = $request->session()->get('user', function () {
            return view('login1', ['check' => 'no']);
        });

        $spinfo = $this->spinfo();

        // 取得該 census 下所有現有資料（避免重複）
        $census = $data[0]['census'];
        if ($type === 'record') {
            $existingSigns = FsSeedsRecord1::selectRaw("CONCAT(census, trap, csp, code) AS sign")->pluck('sign')->toArray();
        } else {
            $existingSigns = FsSeedsFulldata::where('census', $census)
                ->selectRaw("CONCAT(census, trap, csp, code) AS sign")
                ->pluck('sign')
                ->toArray();
        }

        $insertList = [];
        $fixlogList = [];
        $checker = new \App\Jobs\FsSeedsCheck;

        foreach ($data as $row) {
            if (trim($row['trap']) === '') continue;

            // 預設欄位處理
            $row['trap'] = str_pad($row['trap'], 3, '0', STR_PAD_LEFT);
            if ($row['code'] === '') $row['code'] = '0';
            if ($row['count'] === '') $row['count'] = '0';
            foreach ($row as $key => $val) {
                if (is_string($val)) {
                    $row[$key] = trim($val);
                }
            }
            $result = $checker->check($row, $spinfo, $existingSigns);
            $checkedRow = $result['result'];
            $checknote = $result['checknote'];

            // 組合 insert 資料
            $inlist = [];
            foreach ($checkedRow as $key => $value) {
                if ($value === null) $value = '';
                if ($key === 'id') $value = '0';
                $inlist[$key] = $value;
            }

            $inlist['checknote'] = $checknote;
            $inlist['updated_id'] = $user;
            $inlist['updated_at'] = now();

            // 補足物種資訊（fulldata）
            if ($type === 'fulldata') {
                $inlist['id'] = '0';
                if (isset($spinfo[$inlist['csp']])) {
                    $inlist['sp'] = $spinfo[$inlist['csp']]['sp'];
                    $inlist['identified'] = $spinfo[$inlist['csp']]['identified'];
                } else {
                    $inlist['sp'] = '';
                    $inlist['identified'] = 'N';
                }

                // 若有 nothing 資料，清掉
                FsSeedsFulldata::where('trap', $inlist['trap'])
                    ->where('csp', 'nothing')
                    ->where('census', $inlist['census'])
                    ->delete();
            }

            $insertList[] = $inlist;

            // 紀錄 fixlog（fulldata）
            if ($type === 'fulldata') {
                $updatedes = [
                    'trap' => $inlist['trap'],
                    'csp' => $inlist['csp'],
                    'code' => $inlist['code']
                ];
                $fixlogList[] = [
                    'id' => '0',
                    'type' => 'insert',
                    'census' => $inlist['census'],
                    'descript' => json_encode($updatedes, JSON_UNESCAPED_UNICODE),
                    'updated_id' => $user,
                    'updated_at' => now()
                ];
            }
        }

        // 寫入資料
        if (!empty($insertList)) {
            $table::insert($insertList);
        }

        if ($type === 'fulldata' && !empty($fixlogList)) {
            \App\Models\FsSeedsFixlog::insert($fixlogList);
        }

        // 回傳空表格
        $redata = ($type === 'record') ? $this->getRedata() : $this->getRedata2($census);
        $emptytable = [];
        for ($k = 0; $k < 29; $k++) {
            $emptytable[] = [
                'id' => $k + 1,
                'census' => $redata[0]['census'],
                'trap' => '',
                'csp' => '',
                'code' => '',
                'count' => '',
                'seeds' => '',
                'viability' => '',
                'fragments' => '',
                'sex' => '',
                'identifier' => $this->identifier,
                'note' => '',
            ];
        }

        return [
            'result' => 'ok',
            'data' => $redata,
            'emptytable' => $emptytable,
            'seedssavenote' => '已新增資料'
        ];
    }


//刪除已輸入資料
    public function deletedata(Request $request, $id, $info, $thispage, $type)
    {
        $user = $request->session()->get('user', function () {
            return view('login1', ['check' => 'no']);
        });

        $datasavenote = '';
        $table = $this->getTableInstance($type);

        // 找出要刪除的資料
        $record = $table::find($id);

        if (!$record) {
            return [
                'result' => 'error',
                'message' => '指定資料不存在',
            ];
        }

        $census = $record->census;
        $trap = $record->trap;

        // 儲存用於 fixlog 的描述
        $updatedes = [
            'trap' => $record->trap,
            'csp' => $record->csp,
            'code' => $record->code,
        ];

        // 刪除資料
        $record->delete();
        $datasavenote = '已刪除 ' . $info . ' 種子雨資料';

        // 如為 fulldata，要記 log 與檢查是否要補 nothing
        if ($type === 'fulldata') {
            \App\Models\FsSeedsFixlog::insert([
                'id' => 0,
                'type' => 'delete',
                'census' => $census,
                'descript' => json_encode($updatedes, JSON_UNESCAPED_UNICODE),
                'updated_id' => $user,
                'updated_at' => now(),
            ]);

            // 若此 trap 該次普查資料皆被刪除，補一筆 nothing
            $stillHasData = \App\Models\FsSeedsFulldata::where('trap', $trap)
                ->where('census', $census)
                ->exists();

            if (!$stillHasData) {
                \App\Models\FsSeedsFulldata::insert([
                    'id' => 0,
                    'census' => $census,
                    'trap' => $trap,
                    'csp' => 'nothing',
                    'sp' => 'NOTHING',
                    'identified' => 'Y',
                    'code' => '0',
                    'count' => '0',
                    'seeds' => '0',
                    'viability' => '0',
                    'fragments' => '0',
                    'sex' => '',
                    'identifier' => $this->identifier,
                    'note' => '',
                    'checknote' => '',
                    'updated_id' => $user,
                    'updated_at' => now(),
                ]);
            }
        }

        // 重新取得資料與頁碼
        if ($type === 'record') {
            $redata = $this->getRedata();
            $thispage = ceil($id / 29);
        } else {
            $redata = $this->getRedata2($census);
            $k = ($id - $redata[0]['id']) + 1;
            $thispage = ceil($k / 29);
        }

        return [
            'result' => 'ok',
            'data' => $redata,
            'thispage' => 1,
            'seedssavenote' => $datasavenote,
        ];
    }


//輸入完成檢查，並匯入大表
public function finishnote(Request $request)
{
    $user = $request->session()->get('user', function () {
        return view('login1', ['check' => 'no']);
    });

    $finishnote = '';
    $spinfo = $this->spinfo();

    // 檢查是否有未修正錯誤資料
    $hasErrors = FsSeedsRecord1::where('checknote', '!=', '')->exists();
    if ($hasErrors) {
        return [
            'result' => 'ok',
            'finishnote' => '有資料錯誤未更正',
        ];
    }

    // 取得所有 record 資料（用 trap 分組）
    $records = FsSeedsRecord1::all()->groupBy('trap');
    if ($records->isEmpty()) {
        return [
            'result' => 'ok',
            'finishnote' => '無可匯入資料',
        ];
    }

    // 用其中一筆資料的欄位建立預設空值 inlistf
    $datacol = FsSeedsRecord1::first();
    $inlistf = [];
    foreach ($datacol->toArray() as $key => $val) {
        $inlistf[$key] = '';
    }
    $inlistf['id'] = '0';
    $inlistf['census'] = $datacol->census;

    $insertList = [];
    $trapHasData = [];

    // 遍歷每個有資料的 trap
    foreach ($records as $trap => $dataGroup) {
        $trap = str_pad($trap, 3, '0', STR_PAD_LEFT);
        $trapHasData[] = $trap;

        foreach ($dataGroup as $record) {
            $row = $record->toArray();
            $row['id'] = '0';

            foreach ($row as $key => $val) {
                if ($val === null) $val = '';
                $inlist[$key] = $val;
            }

            if (isset($spinfo[$inlist['csp']])) {
                $inlist['sp'] = $spinfo[$inlist['csp']]['sp'];
                $inlist['identified'] = $spinfo[$inlist['csp']]['identified'];
            } else {
                $inlist['sp'] = '';
                $inlist['identified'] = 'N';
            }

            $inlist['updated_id'] = $user;
            $inlist['updated_at'] = now();

            $insertList[] = $inlist;
        }
    }

    // 生成 001~107 的 trap，補缺漏
    for ($j = 1; $j <= 107; $j++) {
        $trap = str_pad($j, 3, '0', STR_PAD_LEFT);
        if (!in_array($trap, $trapHasData)) {
            $inlist = $inlistf;
            $inlist['trap'] = $trap;
            $inlist['csp'] = 'nothing';
            $inlist['sp'] = 'NOTHING';
            $inlist['identified'] = 'Y';
            $inlist['code'] = '0';
            $inlist['count'] = '0';
            $inlist['seeds'] = '0';
            $inlist['viability'] = '0';
            $inlist['fragments'] = '0';
            $inlist['sex'] = '';
            $inlist['identifier'] = $this->identifier;
            $inlist['note'] = '';
            $inlist['checknote'] = '';
            $inlist['updated_id'] = $user;
            $inlist['updated_at'] = now();

            $insertList[] = $inlist;
        }
    }

    // 寫入資料
    if (!empty($insertList)) {
        FsSeedsFulldata::insert($insertList);
    }

    // 清空暫存表
    FsSeedsRecord1::truncate();

    return [
        'result' => 'ok',
        'finishnote' => '',
    ];
}


}