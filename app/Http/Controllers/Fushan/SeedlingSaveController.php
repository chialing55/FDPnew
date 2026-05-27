<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

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

    private function actorAccount(Request $request): string
    {
        $user = $request->user();

        return (string) ($user?->account ?? $user?->name ?? '');
    }

    private function previousSeedlingRows(string $tag)
    {
        return DB::connection('mysql3')
            ->table('seedling_records as r')
            ->join('seedling_stems as st', 'r.tag', '=', 'st.tag')
            ->join('seedling_individuals as i', 'st.mtag', '=', 'i.mtag')
            ->where('st.tag', 'like', $tag)
            ->orderBy('r.census', 'DESC')
            ->select([
                'r.id',
                'r.census',
                'r.year',
                'r.month',
                DB::raw("COALESCE(DATE_FORMAT(r.date, '%Y-%m-%d'), '0000-00-00') as date"),
                'i.trap',
                'i.plot',
                'st.tag',
                'st.mtag',
                'i.csp',
                'r.ht',
                'r.cotno',
                'r.leafno',
                'st.ind',
                DB::raw("COALESCE(r.note, '') as note"),
                'r.recruit',
                'r.status',
                'st.sprout',
                'i.x',
                'i.y',
            ])
            ->get()
            ->map(fn ($value) => (array) $value);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return "`" . str_replace("`", "``", $identifier) . "`";
    }

    private function activeRowsWhereClause(string $table, string $alias): ?string
    {
        if (!Schema::connection("mysql3")->hasColumn($table, "deleted_at")) {
            return null;
        }

        return $alias . "." . $this->quoteIdentifier("deleted_at") . " IS NULL";
    }

    private function collectAffectedSeedlingTags(array ...$rowGroups): array
    {
        $tags = collect();
        $mtags = collect();

        foreach ($rowGroups as $rows) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                foreach (["tag", "original_tag"] as $key) {
                    $value = strtoupper(trim((string) ($row[$key] ?? "")));
                    if ($value !== "") {
                        $tags->push($value);
                    }
                }

                foreach (["mtag", "original_mtag"] as $key) {
                    $value = trim((string) ($row[$key] ?? ""));
                    if ($value !== "") {
                        $mtags->push($value);
                    }
                }
            }
        }

        $mtagValues = $mtags->unique()->values()->all();
        if ($mtagValues !== []) {
            DB::connection("mysql3")
                ->table("seedling_stems")
                ->whereIn("mtag", $mtagValues)
                ->pluck("tag")
                ->each(fn ($tag) => $tags->push(strtoupper(trim((string) $tag))));
        }

        return $tags
            ->map(fn ($tag) => strtoupper(trim((string) $tag)))
            ->filter(fn ($tag) => $tag !== "")
            ->unique()
            ->values()
            ->all();
    }

    private function syncSeedlingAnalysisRows(array $tags): void
    {
        $tags = collect($tags)
            ->map(fn ($tag) => strtoupper(trim((string) $tag)))
            ->filter(fn ($tag) => $tag !== '')
            ->unique()
            ->values()
            ->all();

        if ($tags === [] || !Schema::connection('mysql3')->hasTable('seedling')) {
            return;
        }

        foreach (['seedling_records', 'seedling_stems', 'seedling_individuals'] as $table) {
            if (!Schema::connection('mysql3')->hasTable($table)) {
                return;
            }
        }

        $seedlingColumns = Schema::connection('mysql3')->getColumnListing('seedling');
        $recordColumns = Schema::connection('mysql3')->getColumnListing('seedling_records');
        $stemColumns = Schema::connection('mysql3')->getColumnListing('seedling_stems');
        $individualColumns = Schema::connection('mysql3')->getColumnListing('seedling_individuals');

        $columnSources = [];
        foreach ($recordColumns as $column) {
            $columnSources[$column] = 'r';
        }
        foreach ($stemColumns as $column) {
            $columnSources[$column] ??= 'st';
        }
        foreach ($individualColumns as $column) {
            $columnSources[$column] ??= 'i';
        }

        $syncColumns = [];
        $selectColumns = [];
        foreach ($seedlingColumns as $column) {
            if ($column === 'id') {
                continue;
            }

            $source = $columnSources[$column] ?? null;
            if (!$source) {
                continue;
            }

            $quotedColumn = $this->quoteIdentifier($column);
            $syncColumns[] = $column;
            $expression = $column === 'note'
                ? "COALESCE(" . $source . "." . $quotedColumn . ", '')"
                : $source . "." . $quotedColumn;
            $selectColumns[] = DB::raw($expression . ' AS ' . $quotedColumn);
        }

        if ($syncColumns === [] || !in_array('tag', $syncColumns, true) || !in_array('census', $syncColumns, true)) {
            return;
        }

        $rebuiltRows = DB::connection('mysql3')
            ->table('seedling_records as r')
            ->join('seedling_stems as st', 'r.tag', '=', 'st.tag')
            ->join('seedling_individuals as i', 'st.mtag', '=', 'i.mtag')
            ->select($selectColumns)
            ->whereIn('r.tag', $tags)
            ->when(Schema::connection('mysql3')->hasColumn('seedling_records', 'deleted_at'), fn ($query) => $query->whereNull('r.deleted_at'))
            ->when(Schema::connection('mysql3')->hasColumn('seedling_stems', 'deleted_at'), fn ($query) => $query->whereNull('st.deleted_at'))
            ->when(Schema::connection('mysql3')->hasColumn('seedling_individuals', 'deleted_at'), fn ($query) => $query->whereNull('i.deleted_at'))
            ->orderBy('r.census')
            ->orderBy('i.trap')
            ->orderBy('i.plot')
            ->orderBy('st.tag')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->values();

        $rowKey = fn (array $row): string => (string) ($row['census'] ?? '') . "\x1F" . strtoupper((string) ($row['tag'] ?? ''));
        $normalize = fn ($value): string => $value === null ? '' : (string) $value;

        $rebuiltByKey = $rebuiltRows->keyBy($rowKey);
        $existingRows = DB::connection('mysql3')
            ->table('seedling')
            ->whereIn('tag', $tags)
            ->get($syncColumns)
            ->map(fn ($row) => (array) $row)
            ->values();
        $existingByKey = $existingRows->keyBy($rowKey);

        foreach ($existingRows as $existingRow) {
            if (!$rebuiltByKey->has($rowKey($existingRow))) {
                DB::connection('mysql3')
                    ->table('seedling')
                    ->where('census', $existingRow['census'] ?? null)
                    ->where('tag', $existingRow['tag'] ?? null)
                    ->delete();
            }
        }

        foreach ($rebuiltRows as $rebuiltRow) {
            $key = $rowKey($rebuiltRow);
            $existingRow = $existingByKey->get($key);

            if (!$existingRow) {
                DB::connection('mysql3')->table('seedling')->insert($rebuiltRow);
                continue;
            }

            $changes = [];
            foreach ($syncColumns as $column) {
                if ($normalize($existingRow[$column] ?? null) !== $normalize($rebuiltRow[$column] ?? null)) {
                    $changes[$column] = $rebuiltRow[$column] ?? null;
                }
            }

            if ($changes !== []) {
                DB::connection('mysql3')
                    ->table('seedling')
                    ->where('census', $rebuiltRow['census'] ?? null)
                    ->where('tag', $rebuiltRow['tag'] ?? null)
                    ->update($changes);
            }
        }
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
        $user = $this->actorAccount($request);

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

                $tablecov::where('id', $savecov[$i]['id'])->update(['cov' => $savecov[$i]['cov'], 'date' => $savecov[$i]['date'], 'canopy' => $savecov[$i]['canopy'], 'note' => $savecov[$i]['note'], 'updated_id' => $user]);
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

    //小苗後端資料修改
    public function saveupdate(Request $request)
    {
        abort_unless((int) ($request->user()?->is_admin ?? 0) === 1, 403);

        $payload = $request->all();
        $workRows = $payload['workRows'] ?? [];
        $identityRows = $payload['identityRows'] ?? [];
        $masterRows = $payload['masterRows'] ?? [];
        $user = (string) ($payload['user'] ?? $request->user()?->account ?? $request->user()?->name ?? '');
        $from = (string) ($payload['from'] ?? '');
        $currentTag = (string) ($payload['tag'] ?? '');

        if (!is_array($workRows) || !is_array($identityRows) || !is_array($masterRows)) {
            return [
                'result' => 'error',
                'datasavenote' => '資料格式錯誤。',
                'datasavenote_type' => 'error',
            ];
        }

        $savedTag = $currentTag;
        $updatedAt = now()->toDateTimeString();
        $duplicateNote = $this->seedlingUpdateDuplicateNumberNote($identityRows);
        if ($duplicateNote !== '') {
            return [
                'result' => 'error',
                'datasavenote' => $duplicateNote,
                'datasavenote_type' => 'error',
            ];
        }

        $analysisTags = $this->collectAffectedSeedlingTags($workRows, $identityRows, $masterRows);

        DB::connection('mysql3')->transaction(function () use ($workRows, $identityRows, $masterRows, $user, $updatedAt, &$savedTag) {
            foreach ($workRows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $workId = $row['work_id'] ?? $row['id'] ?? null;
                if (!$workId) {
                    continue;
                }

                $tag = strtoupper(trim((string) ($row['tag'] ?? '')));
                $mtag = trim((string) ($row['mtag'] ?? ''));
                $originalTag = strtoupper(trim((string) ($row['original_tag'] ?? '')));
                $originalMtag = trim((string) ($row['original_mtag'] ?? ''));
                if ($mtag !== '' && $mtag !== $originalMtag && ($tag === '' || $tag === $originalTag)) {
                    $tag = $originalMtag !== '' && str_starts_with($originalTag, $originalMtag)
                        ? $mtag . substr($originalTag, strlen($originalMtag))
                        : $mtag;
                }
                if ($tag !== '' && ($mtag === '' || ($tag !== $originalTag && $mtag === $originalMtag))) {
                    $mtag = explode('.', $tag)[0];
                }

                $update = $this->onlySeedlingUpdateFields($row, [
                    'census', 'year', 'month', 'date', 'trap', 'plot', 'tag', 'mtag', 'csp',
                    'ht', 'cotno', 'leafno', 'ind', 'note', 'recruit', 'status', 'sprout',
                    'x', 'y', 'alternote',
                ]);

                if ($tag !== '') {
                    $update['tag'] = $tag;
                    $savedTag = $tag;
                }
                if ($mtag !== '') {
                    $update['mtag'] = $mtag;
                }
                $update['updated_id'] = $user;

                DB::connection('mysql3')
                    ->table('slrecord1')
                    ->where('id', $workId)
                    ->update($update);
            }

            foreach ($identityRows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $tag = strtoupper(trim((string) ($row["tag"] ?? "")));
                $mtag = trim((string) ($row["mtag"] ?? ""));
                $originalTag = strtoupper(trim((string) ($row["original_tag"] ?? "")));
                $originalMtag = trim((string) ($row["original_mtag"] ?? ""));
                $isSprout = strtoupper(trim((string) ($row["sprout"] ?? ""))) === "TRUE";
                if ($mtag !== "" && $mtag !== $originalMtag && ($tag === "" || $tag === $originalTag)) {
                    $tag = $originalMtag !== "" && str_starts_with($originalTag, $originalMtag)
                        ? $mtag . substr($originalTag, strlen($originalMtag))
                        : $mtag;
                }
                if ($tag !== "" && ($mtag === "" || ($tag !== $originalTag && $mtag === $originalMtag))) {
                    $mtag = explode(".", $tag)[0];
                }
                if ($tag !== "") {
                    $savedTag = $tag;
                }

                $stemId = $row["stem_id"] ?? null;
                if ($stemId) {
                    $stemUpdate = $this->onlySeedlingUpdateFields($row, ["mtag", "tag", "sprout"]);
                    $stemUpdate["ind"] = 1;
                    if ($tag !== "") {
                        $stemUpdate["tag"] = $tag;
                        $stemUpdate = $this->withSeedlingStemBranch($stemUpdate, $tag);
                    }
                    if ($mtag !== "") {
                        $stemUpdate["mtag"] = $mtag;
                    }
                    $stemUpdate["updated_id"] = $user;
                    $stemUpdate["updated_at"] = $updatedAt;

                    if (($tag !== "" && $tag !== $originalTag) || ($mtag !== "" && $mtag !== $originalMtag)) {
                        $oldStem = DB::connection("mysql3")
                            ->table("seedling_stems")
                            ->where("id", $stemId)
                            ->first();

                        if ($oldStem) {
                            $newStem = (array) $oldStem;
                            unset($newStem["id"]);
                            $newStem = array_merge($newStem, $stemUpdate, [
                                "deleted_at" => null,
                                "updated_id" => $user,
                                "updated_at" => $updatedAt,
                            ]);

                            DB::connection("mysql3")
                                ->table("seedling_stems")
                                ->insert($newStem);

                            DB::connection("mysql3")
                                ->table("seedling_stems")
                                ->where("id", $stemId)
                                ->update([
                                    "deleted_at" => $updatedAt,
                                    "updated_id" => $user,
                                    "updated_at" => $updatedAt,
                                ]);
                        }
                    } else {
                        DB::connection("mysql3")
                            ->table("seedling_stems")
                            ->where("id", $stemId)
                            ->update($stemUpdate);
                    }
                }

                $individualId = $row["individual_id"] ?? null;
                if ($individualId && !$isSprout) {
                    $individualUpdate = $this->onlySeedlingUpdateFields($row, ["mtag", "trap", "plot", "x", "y", "csp"]);
                    if ($mtag !== "") {
                        $individualUpdate["mtag"] = $mtag;
                    }
                    $individualUpdate["updated_id"] = $user;
                    $individualUpdate["updated_at"] = $updatedAt;

                    if ($originalMtag !== "" && $mtag !== "" && $mtag !== $originalMtag) {
                        $oldIndividual = DB::connection("mysql3")
                            ->table("seedling_individuals")
                            ->where("id", $individualId)
                            ->first();

                        if ($oldIndividual) {
                            $newIndividual = (array) $oldIndividual;
                            unset($newIndividual["id"]);
                            $newIndividual = array_merge($newIndividual, $individualUpdate, [
                                "mtag" => $mtag,
                                "merge_to" => null,
                                "deleted_at" => null,
                                "updated_id" => $user,
                                "updated_at" => $updatedAt,
                            ]);

                            $existingNewIndividual = DB::connection("mysql3")
                                ->table("seedling_individuals")
                                ->where("mtag", $mtag)
                                ->whereNull("deleted_at")
                                ->first();

                            if ($existingNewIndividual) {
                                DB::connection("mysql3")
                                    ->table("seedling_individuals")
                                    ->where("id", $existingNewIndividual->id)
                                    ->update($newIndividual);
                            } else {
                                DB::connection("mysql3")
                                    ->table("seedling_individuals")
                                    ->insert($newIndividual);
                            }

                            DB::connection("mysql3")
                                ->table("seedling_individuals")
                                ->where("id", $individualId)
                                ->update([
                                    "deleted_at" => $updatedAt,
                                    "merge_to" => $mtag,
                                    "updated_id" => $user,
                                    "updated_at" => $updatedAt,
                                ]);
                        }
                    } else {
                        DB::connection("mysql3")
                            ->table("seedling_individuals")
                            ->where("id", $individualId)
                            ->update($individualUpdate);
                    }
                }

                $originalTag = strtoupper(trim((string) ($row["original_tag"] ?? "")));
                if ($tag !== "" && $originalTag !== "" && $tag !== $originalTag) {
                    DB::connection("mysql3")
                        ->table("seedling_records")
                        ->where("tag", $originalTag)
                        ->update([
                            "tag" => $tag,
                            "updated_id" => $user,
                            "updated_at" => $updatedAt,
                        ]);
                }
            }

            foreach ($masterRows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $recordId = $row["record_id"] ?? $row["id"] ?? null;
                if (!$recordId) {
                    continue;
                }

                $recordUpdate = $this->onlySeedlingUpdateFields($row, [
                    "census", "year", "month", "date", "ht", "cotno", "leafno",
                    "recruit", "status", "note",
                ]);
                $recordUpdate["updated_id"] = $user;
                $recordUpdate["updated_at"] = $updatedAt;

                DB::connection("mysql3")
                    ->table("seedling_records")
                    ->where("id", $recordId)
                    ->update($recordUpdate);
            }

        });
        $this->syncSeedlingAnalysisRows($analysisTags);

        return [
            'result' => 'ok',
            'tag' => $savedTag,
            'from' => $from,
            'datasavenote' => '資料已儲存',
            'datasavenote_type' => 'success',
        ];
    }

    public function deleteupdate(Request $request)
    {
        abort_unless((int) ($request->user()?->is_admin ?? 0) === 1, 403);

        $payload = $request->all();
        $tableType = (string) ($payload["tableType"] ?? "");
        $rows = $payload["rows"] ?? [];
        $user = (string) ($payload["user"] ?? $request->user()?->account ?? $request->user()?->name ?? "");
        $deletedAt = now()->toDateTimeString();

        if (!in_array($tableType, ["work", "identity", "records", "all"], true) || !is_array($rows)) {
            return [
                "result" => "error",
                "datasavenote" => "刪除資料格式錯誤。",
                "datasavenote_type" => "error",
            ];
        }

        $workRows = $tableType === "all" ? ($payload["workRows"] ?? []) : ($tableType === "work" ? $rows : []);
        $identityRows = $tableType === "all" ? ($payload["identityRows"] ?? []) : ($tableType === "identity" ? $rows : []);
        $recordRows = $tableType === "all" ? ($payload["masterRows"] ?? []) : ($tableType === "records" ? $rows : []);

        if (!is_array($workRows) || !is_array($identityRows) || !is_array($recordRows)) {
            return [
                "result" => "error",
                "datasavenote" => "刪除資料格式錯誤。",
                "datasavenote_type" => "error",
            ];
        }

        $analysisTags = $this->collectAffectedSeedlingTags($workRows, $identityRows, $recordRows);

        $deletedCount = 0;

        DB::connection("mysql3")->transaction(function () use ($workRows, $identityRows, $recordRows, $user, $deletedAt, &$deletedCount) {
            $workIds = collect($workRows)
                ->map(fn ($row) => $row["work_id"] ?? $row["id"] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($workIds)) {
                $deletedCount += DB::connection("mysql3")
                    ->table("slrecord1")
                    ->whereIn("id", $workIds)
                    ->delete();
            }

            $stemIds = collect($identityRows)
                ->map(fn ($row) => $row["stem_id"] ?? $row["id"] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();
            $individualIds = collect($identityRows)
                ->map(fn ($row) => $row["individual_id"] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($stemIds)) {
                $deletedCount += DB::connection("mysql3")
                    ->table("seedling_stems")
                    ->whereIn("id", $stemIds)
                    ->update([
                        "deleted_at" => $deletedAt,
                        "updated_id" => $user,
                        "updated_at" => $deletedAt,
                    ]);
            }

            if (!empty($individualIds)) {
                $deletedCount += DB::connection("mysql3")
                    ->table("seedling_individuals")
                    ->whereIn("id", $individualIds)
                    ->update([
                        "deleted_at" => $deletedAt,
                        "updated_id" => $user,
                        "updated_at" => $deletedAt,
                    ]);
            }

            $recordIds = collect($recordRows)
                ->map(fn ($row) => $row["record_id"] ?? $row["id"] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($recordIds)) {
                $deletedCount += DB::connection("mysql3")
                    ->table("seedling_records")
                    ->whereIn("id", $recordIds)
                    ->update([
                        "deleted_at" => $deletedAt,
                        "updated_id" => $user,
                        "updated_at" => $deletedAt,
                    ]);
            }
        });
        $this->syncSeedlingAnalysisRows($analysisTags);

        return [
            "result" => "ok",
            "tag" => $payload["tag"] ?? null,
            "from" => $payload["from"] ?? null,
            "datasavenote" => $deletedCount > 0 ? "已刪除此筆資料" : "沒有可刪除資料。",
            "datasavenote_type" => $deletedCount > 0 ? "success" : "error",
        ];
    }

    private function seedlingBranchFromTag(string $tag): int
    {
        $parts = explode(".", $tag);

        return isset($parts[1]) ? (int) $parts[1] : 0;
    }

    private function seedlingStemBranchColumn(): ?string
    {
        if (Schema::connection('mysql3')->hasColumn('seedling_stems', 'branch')) {
            return 'branch';
        }

        if (Schema::connection('mysql3')->hasColumn('seedling_stems', 'branch_no')) {
            return 'branch_no';
        }

        return null;
    }

    private function withSeedlingStemBranch(array $values, string $tag): array
    {
        $branchColumn = $this->seedlingStemBranchColumn();
        if ($branchColumn !== null) {
            $values[$branchColumn] = $this->seedlingBranchFromTag($tag);
        }

        return $values;
    }

    private function seedlingUpdateDuplicateNumberNote(array $identityRows): string
    {
        foreach ($identityRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $tag = strtoupper(trim((string) ($row["tag"] ?? "")));
            $mtag = trim((string) ($row["mtag"] ?? ""));
            $originalTag = strtoupper(trim((string) ($row["original_tag"] ?? "")));
            $originalMtag = trim((string) ($row["original_mtag"] ?? ""));
            $isSprout = strtoupper(trim((string) ($row["sprout"] ?? ""))) === "TRUE";

            if ($mtag !== "" && $mtag !== $originalMtag && ($tag === "" || $tag === $originalTag)) {
                $tag = $originalMtag !== "" && str_starts_with($originalTag, $originalMtag)
                    ? $mtag . substr($originalTag, strlen($originalMtag))
                    : $mtag;
            }
            if ($tag !== "" && ($mtag === "" || ($tag !== $originalTag && $mtag === $originalMtag))) {
                $mtag = explode(".", $tag)[0];
            }

            if (str_contains($tag, ".") && !$isSprout) {
                return "tag {$tag} 為分支編號，萌櫱需為 TRUE。";
            }

            if ($isSprout) {
                if ($mtag === "") {
                    return "萌櫱資料需填寫 mtag，請確認後再儲存。";
                }

                $hasParentMtag = DB::connection("mysql3")
                    ->table("seedling_individuals")
                    ->where("mtag", $mtag)
                    ->whereNull("deleted_at")
                    ->exists();

                if (!$hasParentMtag) {
                    return "萌櫱 mtag {$mtag} 不存在於 seedling_individuals，請先確認主幹編號。";
                }
            }

            $individualId = $row["individual_id"] ?? null;
            if (!$isSprout && $originalMtag !== "" && $mtag !== "" && $mtag !== $originalMtag) {
                $duplicateMtag = DB::connection("mysql3")
                    ->table("seedling_individuals")
                    ->where("mtag", $mtag)
                    ->whereNull("deleted_at")
                    ->when($individualId, fn ($query) => $query->where("id", "!=", $individualId))
                    ->exists();

                if ($duplicateMtag) {
                    return "mtag {$mtag} 已存在，請先確認資料檢視中沒有重號。";
                }
            }

            $stemId = $row["stem_id"] ?? null;
            if ($originalTag !== "" && $tag !== "" && $tag !== $originalTag) {
                $duplicateTag = DB::connection("mysql3")
                    ->table("seedling_stems")
                    ->where("tag", $tag)
                    ->whereNull("deleted_at")
                    ->when($stemId, fn ($query) => $query->where("id", "!=", $stemId))
                    ->exists();

                if ($duplicateTag) {
                    return "tag {$tag} 已存在，請先確認資料檢視中沒有重號。";
                }
            }
        }

        return "";
    }

    private function onlySeedlingUpdateFields(array $row, array $allowed): array
    {
        $update = [];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $row)) {
                continue;
            }

            $value = $row[$field];
            if (is_string($value)) {
                $value = trim($value);
            }
            if (in_array($field, ['note', 'alternote', 'recruit', 'status', 'sprout', 'csp', 'tag', 'mtag'], true) && $value === null) {
                $value = '';
            }
            if ($field === 'date' && ($value === '' || $value === null)) {
                $value = '0000-00-00';
            }

            $update[$field] = $value;
        }

        return $update;
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
                    $seedling = $this->previousSeedlingRows($recruit[$i]['tag']);
                    if ($seedling->isEmpty()) {
                        $datacheck['datasavenote'] = ($datacheck['datasavenote'] === '' ? '' : '<br>') . '第' . ($i + 1) . '筆 查無舊資料';
                        $datacheck['pass'] = "0";
                        $hasRecruitError = true;
                    } else {

                        if ($recruit[$i]['x'] == '') {
                            $base = DB::connection('mysql3')
                                ->table('seedling_individuals')
                                ->where('mtag', 'like', $recruit[$i]['mtag'])
                                ->first();
                            $base = $base ? (array) $base : null;

                            if ($base) {
                                $recruit[$i]['x'] = $base['x'];
                                $recruit[$i]['y'] = $base['y'];
                            }
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
        $user = $this->actorAccount($request);

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
                    $uplist['updated_id'] = $user;

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
                $insert2['updated_id'] = $user;

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
        $user = $authUser?->account
            ?? $authUser?->name
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
            'user' => $request->user()?->account ?? $request->user()?->name,
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
