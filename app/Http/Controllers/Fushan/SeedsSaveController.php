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
use App\Models\FsSeedsFixlog;
use App\Services\PlantCatalog\FushanSeedSpeciesService;

use App\Jobs\FsSeedsCheck;
use App\Jobs\SeedsAddButton;
use App\Support\ResolvesActorAccount;


class SeedsSaveController extends Controller
{
    use ResolvesActorAccount;

    protected function resolveSeedsPageById(array $rows, $targetId, int $perPage = 29): int
    {
        if (!$targetId || empty($rows)) {
            return 1;
        }

        foreach ($rows as $index => $row) {
            if (($row['id'] ?? null) == $targetId) {
                return (int) floor($index / $perPage) + 1;
            }
        }

        return 1;
    }

    protected function resolveSeedsCensus(string $type, array $row, ?string $fallbackCensus = null): string
    {
        if (($row['census'] ?? '') !== '') {
            return $row['census'];
        }

        if ($fallbackCensus === '__require_census__') {
            return '';
        }

        if (($fallbackCensus ?? '') !== '') {
            return $fallbackCensus;
        }

        if ($type === 'record') {
            return (string) (FsSeedsRecord1::query()->value('census') ?? '');
        }

        return (string) (FsSeedsFulldata::query()->value('census') ?? '');
    }

    protected function prepareInsertRow(array $row, array $spinfo, array $existingSigns, string $type, string $updatedBy, ?string $fallbackCensus = null): array
    {
        $row['census'] = $this->resolveSeedsCensus($type, $row, $fallbackCensus);
        $row['trap'] = str_pad($row['trap'] ?? '', 3, '0', STR_PAD_LEFT);
        if (($row['code'] ?? '') === '') {
            $row['code'] = '0';
        }
        if (($row['count'] ?? '') === '') {
            $row['count'] = '0';
        }

        unset($row['d']);

        foreach ($row as $key => $val) {
            if (is_string($val)) {
                $row[$key] = trim($val);
            }
        }

        $checker = new \App\Jobs\FsSeedsCheck;
        $result = $checker->check($row, $spinfo, $existingSigns);
        $checkedRow = $result['result'];
        $checknote = $result['checknote'];

        $inlist = [];
        foreach ($checkedRow as $key => $value) {
            if ($key === 'd') {
                continue;
            }
            if ($value === null) {
                $value = '';
            }
            if ($key === 'id') {
                $value = '0';
            }
            $inlist[$key] = $value;
        }

        $inlist['checknote'] = $checknote;
        $inlist['updated_id'] = $updatedBy;
        $inlist['updated_at'] = now();

        if ($type === 'fulldata') {
            $inlist['id'] = '0';
            if (isset($spinfo[$inlist['csp']])) {
                $inlist['sp'] = $spinfo[$inlist['csp']]['sp'];
                $inlist['identified'] = $spinfo[$inlist['csp']]['identified'];
            } else {
                $inlist['sp'] = '';
                $inlist['identified'] = 'N';
            }

            FsSeedsFulldata::where('trap', $inlist['trap'])
                ->where('csp', 'nothing')
                ->where('census', $inlist['census'])
                ->delete();
        }

        return $inlist;
    }


    public function getTableInstance($type)
    {
        if ($type == 'record') {
            return new FsSeedsRecord1;
        } else {
            return new FsSeedsFulldata;
        }
    }

    //產生名錄
    public function spinfo()
    {
        return (new FushanSeedSpeciesService)->keyedByCsp();
    }

    //預設鑑定者
    public $identifier = '黃小俊';

    //重新載入資料
    public function getRedata()
    {

        $data1 = FsSeedsRecord1::query()->orderBy('id')->get()->toArray();
        $ob_table = new SeedsAddButton;
        $redata = $ob_table->addbutton($data1, 'record');

        return $redata;
    }
    //重新載入資料2
    public function getRedata2($census)
    {

        $data1 = FsSeedsFulldata::where('census', 'like', $census)->orderBy('id')->get()->toArray();
        $ob_table = new SeedsAddButton;
        $redata = $ob_table->addbutton($data1, 'fulldata');

        return $redata;
    }

    public function getUnknownRedata(string $unk): array
    {
        $data = FsSeedsFulldata::query()
            ->where(function ($query) use ($unk) {
                $query->where('csp', $unk)
                    ->orWhere('sp', $unk);
            })
            ->orderBy('census')
            ->orderBy('trap')
            ->orderBy('code')
            ->orderBy('id')
            ->get()
            ->toArray();

        return (new SeedsAddButton)->addbutton($data, 'fulldata');
    }

    //已輸入資料的修改與儲存
    public function savedata(Request $request, $type)
    {
        $table = $this->getTableInstance($type);
        $data_all = $request->all();
        $data = $data_all['data'];
        $user = $this->actorAccount($request);
        $datasavenote = '';

        $spinfo = $this->spinfo();
        $requestCensus = (string) ($request->input('currentCensus') ?? '');
        $ids = collect($data)->pluck('id')->filter()->unique()->toArray();

        // 一次取得舊資料，用 id 建立 map
        $existingRows = $table::whereIn('id', $ids)->get()->keyBy('id')->toArray();

        // 一次建立所有 checksign（重複比對用）
        $census = $data[0]['census'] ?? '';
        $requestCensuses = collect($data)
            ->pluck('census')
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->unique()
            ->values()
            ->toArray();
        $existingSigns = array_count_values(
            ($type === 'record')
                ? \App\Models\FsSeedsRecord1::selectRaw("CONCAT(census, trap, csp, code) AS sign")->pluck('sign')->toArray()
                : \App\Models\FsSeedsFulldata::query()
                    ->when($requestCensuses !== [], fn ($query) => $query->whereIn('census', $requestCensuses))
                    ->when($requestCensuses === [], fn ($query) => $query->where('census', $census))
                    ->selectRaw("CONCAT(census, trap, csp, code) AS sign")->pluck('sign')->toArray()
        );

        $checker = new \App\Jobs\FsSeedsCheck;
        $lastUpdatedId = null;
        $hasUpdatedRows = false;
        $hasInsertedRows = false;
        $insertedIds = [];

        foreach ($data as $item) {
            $id = $item['id'];
            if (!isset($existingRows[$id])) {
                if (trim((string)($item['trap'] ?? '')) === '') {
                    continue;
                }

                $inlist = $this->prepareInsertRow($item, $spinfo, $existingSigns, $type, $user, $requestCensus);
                $newId = $table::insertGetId($inlist);
                $lastUpdatedId = $newId;
                $hasInsertedRows = true;
                $insertedIds[] = $newId;

                if ($type === 'fulldata') {
                    $updatedes = [
                        'trap' => $inlist['trap'],
                        'csp' => $inlist['csp'],
                        'code' => $inlist['code']
                    ];
                    \App\Models\FsSeedsFixlog::insert([
                        'id' => 0,
                        'type' => 'insert',
                        'census' => $inlist['census'],
                        'descript' => json_encode($updatedes, JSON_UNESCAPED_UNICODE),
                        'updated_id' => $user,
                        'updated_at' => now()
                    ]);
                }
                continue;
            }

            $original = $existingRows[$id];
            $item['trap'] = str_pad($item['trap'], 3, '0', STR_PAD_LEFT);
            $originalSign = ($original['census'] ?? '') . ($original['trap'] ?? '') . ($original['csp'] ?? '') . ($original['code'] ?? '');

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

            $result = $checker->check($item, $spinfo, $existingSigns, $originalSign);
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
            $hasUpdatedRows = true;
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

        if ($hasUpdatedRows && $hasInsertedRows) {
            $datasavenote = '已更新並新增資料';
        } elseif ($hasInsertedRows) {
            $datasavenote = '已新增資料';
        } elseif ($hasUpdatedRows) {
            $datasavenote = '已更新資料';
        }

        // 回傳更新後頁面與資料
        if ($type === 'record') {
            $redata = $this->getRedata();
            $thispage = $this->resolveSeedsPageById($redata, $lastUpdatedId);
        } else {
            $redata = $this->getRedata2($data[0]['census']);
            $thispage = $this->resolveSeedsPageById($redata, $lastUpdatedId);
        }
        if ($lastUpdatedId === null) {
            $thispage = 1; // 如果沒有更新，則回到第一頁
        }
        return [
            'result' => 'ok',
            'data' => $redata,
            'thispage' => $thispage,
            'seedssavenote' => $datasavenote,
            'seedssavenote_type' => $datasavenote !== '' ? 'success' : '',
            'inserted_ids' => $insertedIds,
        ];
    }

    //空白表單的輸入與儲存
    public function savedata1(Request $request, $type)
    {
        $table = $this->getTableInstance($type);
        $data_all = $request->all();
        $data = $data_all['data'];
        $requestCensus = (string) ($request->input('currentCensus') ?? '');

        $user = $this->actorAccount($request);


        $spinfo = $this->spinfo();

        // 取得該 census 下所有現有資料（避免重複）
        $census = $data[0]['census'];
        if ($type === 'record') {
            $existingSigns = array_count_values(
                FsSeedsRecord1::selectRaw("CONCAT(census, trap, csp, code) AS sign")->pluck('sign')->toArray()
            );
        } else {
            $requestCensuses = collect($data)
                ->pluck('census')
                ->filter(fn ($value) => trim((string) $value) !== '')
                ->unique()
                ->values()
                ->toArray();
            $existingSigns = array_count_values(
                FsSeedsFulldata::query()
                    ->when($requestCensuses !== [], fn ($query) => $query->whereIn('census', $requestCensuses))
                    ->when($requestCensuses === [], fn ($query) => $query->where('census', $census))
                    ->selectRaw("CONCAT(census, trap, csp, code) AS sign")
                    ->pluck('sign')
                    ->toArray()
            );
        }

        $insertedIds = [];
        $fixlogList = [];

        foreach ($data as $row) {
            if (trim($row['trap']) === '') continue;
            $inlist = $this->prepareInsertRow($row, $spinfo, $existingSigns, $type, $user, $requestCensus);

            $newId = $table::insertGetId($inlist);
            $insertedIds[] = $newId;

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
            'seedssavenote' => '已新增資料',
            'seedssavenote_type' => 'success',
            'inserted_ids' => $insertedIds,
        ];
    }


    //刪除已輸入資料
    public function deletedata(Request $request, $id, $info, $thispage, $type)
    {
        $user = $this->actorAccount($request);


        $datasavenote = '';
        $table = $this->getTableInstance($type);

        // 找出要刪除的資料
        $record = $table::find($id);

        if (!$record) {
            return [
                'result' => 'error',
                'message' => '指定資料不存在',
                'seedssavenote_type' => 'error',
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
            'thispage' => $thispage,
            'seedssavenote' => $datasavenote,
            'seedssavenote_type' => 'success',
        ];
    }

    public function saveUnknownData(Request $request, string $unk, string $type)
    {
        abort_unless((bool) $request->user()?->is_admin, 403);
        if ($errorResponse = $this->rejectUnknownRowsWithoutCensus($request)) {
            return $errorResponse;
        }
        $request->merge(['currentCensus' => '__require_census__']);

        $response = $this->savedata($request, $type);

        return $this->hydrateUnknownSaveResponse($response, $unk);
    }

    public function saveNewUnknownData(Request $request, string $unk, string $type)
    {
        abort_unless((bool) $request->user()?->is_admin, 403);
        if ($errorResponse = $this->rejectUnknownRowsWithoutCensus($request)) {
            return $errorResponse;
        }
        $request->merge(['currentCensus' => '__require_census__']);

        $response = $this->savedata1($request, $type);

        return $this->hydrateUnknownSaveResponse($response, $unk);
    }

    public function deleteUnknownData(Request $request, string $unk, $id, $info, $thispage, $type)
    {
        abort_unless((bool) $request->user()?->is_admin, 403);

        $response = $this->deletedata($request, $id, $info, $thispage, $type);

        return $this->hydrateUnknownSaveResponse($response, $unk);
    }

    private function rejectUnknownRowsWithoutCensus(Request $request): ?array
    {
        foreach ((array) $request->input('data', []) as $row) {
            $hasContent = trim((string) ($row['trap'] ?? '')) !== ''
                || trim((string) ($row['code'] ?? '')) !== ''
                || trim((string) ($row['count'] ?? '')) !== ''
                || trim((string) ($row['note'] ?? '')) !== '';

            if ($hasContent && trim((string) ($row['census'] ?? '')) === '') {
                return [
                    'result' => 'error',
                    'message' => '新增 UNKNOWN 資料時，census 不得為空白。',
                    'seedssavenote' => '新增 UNKNOWN 資料時，census 不得為空白。',
                    'seedssavenote_type' => 'error',
                ];
            }
        }

        return null;
    }

    private function hydrateUnknownSaveResponse($response, string $unk)
    {
        if (!is_array($response) || !isset($response['data'])) {
            return $response;
        }

        $response['data'] = $this->getUnknownRedata($unk);

        return $response;
    }


    //輸入完成檢查，並匯入大表
    public function finishnote(Request $request)
    {
        $user = $this->actorAccount($request);


        $finishnote = '';
        $spinfo = $this->spinfo();

        // 檢查是否有未修正錯誤資料
        $hasErrors = FsSeedsRecord1::where('checknote', '!=', '')->exists();
        if ($hasErrors) {
            return [
                'result' => 'ok',
                'finishnote' => '有資料錯誤未更正',
                'finishnote_type' => 'error',
            ];
        }

        // 取得所有 record 資料（用 trap 分組）
        $records = FsSeedsRecord1::all()->groupBy('trap');
        if ($records->isEmpty()) {
            return [
                'result' => 'ok',
                'finishnote' => '無可匯入資料',
                'finishnote_type' => 'error',
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
            'finishnote_type' => '',
        ];
    }
}
