<?php

namespace App\Http\Controllers\Fushan;

use App\Http\Controllers\Controller;
use App\Models\FsBaseSpinfo;
use App\Models\FsMortality\CensusRecord;
use App\Models\FsMortality\CensusRecordComment;
use App\Models\FsMortality\Census;
use App\Models\FsMortality\CommentOption;
use App\Models\FsMortality\ImportStage;
use App\Models\FsMortality\Person;
use App\Models\FsMortality\StemCorrection;
use App\Models\FsMortality\Team;
use App\Models\FsMortality\TeamMember;
use App\Models\FsMortality\TreeIndividual;
use App\Models\FsTreeCensus1;
use App\Models\FsTreeCensus2;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus5;
use App\Services\Fushan\MortalityRecordPaperExporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use League\Csv\Reader;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MortalityController extends Controller
{
    private function ensureProcessAdmin(Request $request): void
    {
        abort_unless($request->user()?->is_admin, 403);
    }

    public function mortality(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');
        $surveyContext = $this->nextMortalitySurveyContext();

        return view('pages/fushan/mortality_doc', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            ...$surveyContext,
            ...$this->recordPaperDownloadContext(),
        ]);
    }

    public function downloadRecordPaper(Request $request, MortalityRecordPaperExporter $exporter): BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $downloadContext = $this->recordPaperDownloadContext();

        if (!$downloadContext['canDownloadRecordPaper']) {
            return redirect()
                ->route('admin.fushan.mortality.doc')
                ->with('status', $downloadContext['recordPaperDownloadMessage']);
        }

        try {
            $file = $exporter->make();
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.fushan.mortality.doc')
                ->with('status', $exception->getMessage());
        }

        return response()
            ->download($file['path'], $file['filename'])
            ->deleteFileAfterSend(true);
    }

    public function note(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_note', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
        ]);
    }

    public function entry(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');
        $entry = (string) $request->route('entry', '1');
        $entryContext = $this->getMortalityEntryContext();

        return view('pages/fushan/mortality_entry', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            'entry' => $entry,
            ...$entryContext,
        ]);
    }

    public function censusPage(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');
        $censuses = Census::query()
            ->orderBy('census')
            ->get();

        return view('pages/fushan/mortality_census', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            'censuses' => $censuses,
        ]);
    }

    public function saveCensusPage(Request $request)
    {
        $rows = $request->input('rows', []);
        $savedCount = 0;
        $createdCount = 0;

        foreach ($rows as $row) {
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
            $censusValue = $this->nullIfBlank($row['census'] ?? null);
            $surveyYear = $this->nullIfBlank($row['survey_year'] ?? null);
            $dbhCensus = $this->nullIfBlank($row['dbh_census'] ?? null);
            $dataBatch = $this->nullIfBlank($row['data_batch'] ?? null);

            if ($censusValue === null && $surveyYear === null && $dbhCensus === null && $dataBatch === null) {
                continue;
            }

            if ($censusValue === null) {
                return response()->json([
                    'message' => '每一列至少要填入 census。',
                ], 422);
            }

            if (!is_numeric($censusValue)) {
                return response()->json([
                    'message' => 'census 必須是數字。',
                ], 422);
            }

            if ($surveyYear !== null && !is_numeric($surveyYear)) {
                return response()->json([
                    'message' => 'survey_year 必須是數字。',
                ], 422);
            }

            if ($dbhCensus !== null && !is_numeric($dbhCensus)) {
                return response()->json([
                    'message' => 'dbh_census 必須是數字。',
                ], 422);
            }

            $attributes = [
                'census' => (int) $censusValue,
                'survey_year' => $surveyYear !== null ? (int) $surveyYear : null,
                'has_dbh' => in_array($row['has_dbh'] ?? null, [true, 1, '1', 'true', 'yes', 'Yes', 'YES'], true),
                'dbh_census' => $dbhCensus !== null ? (int) $dbhCensus : null,
                'data_batch' => $dataBatch,
            ];

            if ($id) {
                $census = Census::query()->find($id);

                if (!$census) {
                    continue;
                }

                $census->fill($attributes);

                if ($census->isDirty()) {
                    $census->save();
                    $savedCount++;
                }

                continue;
            }

            $exists = Census::query()
                ->where('census', $attributes['census'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => "census {$attributes['census']} 已存在，請直接修改原列。",
                ], 422);
            }

            Census::query()->create($attributes);
            $createdCount++;
        }

        $censuses = Census::query()
            ->orderBy('census')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'census' => $item->census,
                    'survey_year' => $item->survey_year,
                    'has_dbh' => $item->has_dbh ? 'Yes' : 'No',
                    'dbh_census' => $item->dbh_census,
                    'data_batch' => $item->data_batch,
                ];
            })
            ->values();

        return response()->json([
            'message' => "已儲存 {$savedCount} 筆，新增 {$createdCount} 筆。",
            'rows' => $censuses,
        ]);
    }

    public function surveyImport(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');
        $surveyImportContext = $this->getSurveyImportContext();

        return view('pages/fushan/mortality_survey_import', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            ...$surveyImportContext,
        ]);
    }

    public function uploadSurveyImport(Request $request)
    {
        $validated = $request->validate([
            'survey_file' => ['required', 'file', 'mimes:csv,txt'],
        ], [
            'survey_file.required' => '請先選擇要匯入的檔案。',
            'survey_file.mimes' => '目前只支援 csv 或 txt 檔。',
        ]);

        $surveyImportContext = $this->getSurveyImportContext();
        $nextCensus = $surveyImportContext['nextCensus'];
        $nextCensusValue = $surveyImportContext['nextCensusValue'];

        if (!$nextCensus) {
            return redirect()
                ->route('admin.fushan.mortality.survey-import')
                ->withErrors(['survey_file' => "找不到 census {$nextCensusValue} 的設定，請先到調查年度頁建立資料。"]);
        }

        if (!$surveyImportContext['needsNewImport']) {
            return redirect()
                ->route('admin.fushan.mortality.survey-import')
                ->withErrors(['survey_file' => $surveyImportContext['decisionMessage']]);
        }

        $file = $validated['survey_file'];
        $csvContent = file_get_contents($file->getRealPath());

        if ($csvContent === false) {
            return redirect()
                ->route('admin.fushan.mortality.survey-import')
                ->withErrors(['survey_file' => '無法讀取上傳檔案。']);
        }

        $csvContent = $this->convertSurveyImportContentToUtf8($csvContent);
        $firstLine = Str::before($csvContent, "\n");

        $delimiter = ',';
        if (is_string($firstLine)) {
            $delimiterCounts = [
                ',' => substr_count($firstLine, ','),
                "\t" => substr_count($firstLine, "\t"),
                ';' => substr_count($firstLine, ';'),
            ];
            arsort($delimiterCounts);
            $delimiter = (string) array_key_first($delimiterCounts);
        }

        $csv = Reader::fromString($csvContent);
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        $records = iterator_to_array($csv->getRecords());
        $targetTable = 'data_batch_' . $nextCensus->data_batch;
        $createdTable = $this->ensureDataBatchTableExists($targetTable);

        $normalizedRows = [];
        $page = 1;
        $inSeparatorBlock = false;

        foreach ($records as $record) {
            if ($this->isSurveyImportSeparatorRow($record)) {
                if (!$inSeparatorBlock) {
                    $page++;
                    $inSeparatorBlock = true;
                }

                continue;
            }

            $inSeparatorBlock = false;
            $normalized = $this->mapSurveyImportRowToBatchRow($record, $page);

            if ($normalized === null) {
                continue;
            }

            $normalizedRows[] = $normalized;
        }

        if (empty($normalizedRows)) {
            return redirect()
                ->route('admin.fushan.mortality.survey-import')
                ->withErrors(['survey_file' => '檔案沒有可匯入的資料列，請確認第一列為欄名，後續列為資料。']);
        }

        DB::connection('fs_mortality')->transaction(function () use ($normalizedRows, $targetTable) {
            DB::connection('fs_mortality')->table($targetTable)->delete();

            foreach (array_chunk($normalizedRows, 500) as $chunk) {
                DB::connection('fs_mortality')->table($targetTable)->insert($chunk);
            }
        });

        return redirect()
            ->route('admin.fushan.mortality.survey-import')
            ->with('status', "已匯入 census {$nextCensusValue} 的調查資料，共 " . count($normalizedRows) . " 筆到 `{$targetTable}`。")
            ->with('survey_import_summary', [
                'target_census' => $nextCensusValue,
                'survey_year' => $nextCensus->survey_year,
                'data_batch' => $nextCensus->data_batch,
                'target_table' => $targetTable,
                'created_table' => $createdTable,
                'imported_count' => count($normalizedRows),
                'file_name' => $file->getClientOriginalName(),
            ]);
    }

    public function generateMortalityEntryTables(Request $request)
    {
        $entryContext = $this->getMortalityEntryContext();
        $targetTable = $entryContext['targetTable'];
        $targetCensus = $entryContext['nextCensus']?->census;

        if (!$entryContext['nextCensus']) {
            return redirect()
                ->route('admin.fushan.mortality.import')
                ->with('status', '目前找不到下一次要處理的 census，尚無法產生輸入表單。');
        }

        if (!$entryContext['targetTableHasData'] || $targetTable === null) {
            return redirect()
                ->route('admin.fushan.mortality.import')
                ->with('status', "請先匯入 `{$targetTable}` 的資料後，再產生輸入表單。");
        }

        if ($entryContext['recordTablesMatchTargetCensus']) {
            return redirect()
                ->route('admin.fushan.mortality.import')
                ->with('status', "record1 與 record2 已是 census {$targetCensus} 的資料，可以直接開始輸入。");
        }

        $connection = DB::connection('fs_mortality');

        $treeIndividualRows = $connection->table($targetTable)
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->orderBy('id')
            ->get(['stemid']);

        $stageIndividuals = $this->buildTreeIndividualsPayload($treeIndividualRows);
        $treeIndividualSyncSummary = $this->syncTreeIndividuals($stageIndividuals);

        $recordTables = ['record1', 'record2'];

        // CREATE TABLE causes an implicit commit in MySQL, so do DDL before the transaction.
        foreach ($recordTables as $recordTable) {
            $this->ensureMortalityRecordTableExists($recordTable, $targetTable);
        }

        $entryRows = $this->buildMortalityEntryRows($targetTable, (int) $targetCensus);

        $connection->transaction(function () use ($connection, $recordTables, $entryRows) {
            foreach ($recordTables as $recordTable) {
                $connection->table($recordTable)->delete();

                if (!empty($entryRows)) {
                    foreach (array_chunk($entryRows, 500) as $chunk) {
                        $connection->table($recordTable)->insert($chunk);
                    }
                }
            }
        });

        $year = $entryContext['nextCensus']->survey_year ?? '—';
        $census = $entryContext['nextCensus']->census ?? '—';
        $status = "已先同步 tree_individuals（新增 {$treeIndividualSyncSummary['created_count']}、更新 {$treeIndividualSyncSummary['updated_count']}、停用 {$treeIndividualSyncSummary['deactivated_count']}），再更新 census {$census}（{$year} 年）的輸入表單資料：record1 / record2。";

        return redirect()
            ->route('admin.fushan.mortality.import')
            ->with('status', $status)
            ->with('tree_individual_sync_summary', $treeIndividualSyncSummary);
    }


    private function buildMortalityEntryRows(string $targetTable, int $targetCensus): array
    {
        $sourceRows = DB::connection('fs_mortality')
            ->table($targetTable)
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->orderBy('map_sort')
            ->orderBy('map')
            ->orderBy('id')
            ->get(['map_sort', 'map', 'stemid']);

        if ($sourceRows->isEmpty()) {
            return [];
        }

        $stemids = $sourceRows
            ->pluck('stemid')
            ->map(fn ($stemid) => trim((string) $stemid))
            ->filter(fn ($stemid) => $stemid !== '')
            ->unique()
            ->values()
            ->all();
        $baseRows = $this->baseRowsForStemids($stemids);
        $speciesMap = $this->speciesMapForBaseRows($baseRows);
        $latestRecords = $this->latestCensusRecordsForStemids($stemids);
        $recordColumns = collect(Schema::connection('fs_mortality')->getColumnListing('record1'))
            ->reject(fn ($column) => $column === 'id')
            ->values()
            ->all();
        $now = now();
        $rows = [];

        foreach ($sourceRows as $sourceRow) {
            $stemid = trim((string) $sourceRow->stemid);

            if ($stemid === '') {
                continue;
            }

            $baseTag = $this->baseTagFromStemid($stemid);
            $base = $baseRows[$baseTag] ?? null;
            $latestRecord = $latestRecords[$stemid] ?? null;
            $latestStatus = strtoupper(trim((string) ($latestRecord->status ?? '')));
            $status = $latestStatus !== '' && $latestStatus !== 'A' ? $latestStatus : null;

            $candidate = [
                'census' => $targetCensus,
                'map_sort' => $this->toNullableInteger($sourceRow->map_sort ?? null) ?? 1,
                'map' => $this->nullIfBlank($sourceRow->map ?? null),
                'q20' => $this->formatQ20($base->qx ?? null, $base->qy ?? null),
                'q10' => $this->formatQ10($base->subqx ?? null, $base->subqy ?? null),
                'qx' => $this->toNullableInteger($base->qx ?? null),
                'qy' => $this->toNullableInteger($base->qy ?? null),
                'subqx' => $this->toNullableInteger($base->subqx ?? null),
                'subqy' => $this->toNullableInteger($base->subqy ?? null),
                'stemid' => $stemid,
                'csp' => $this->nullIfBlank($speciesMap[$base->spcode ?? ''] ?? ($base->spcode ?? null)),
                'x' => $this->toNullableDecimal($base->qudx ?? null),
                'y' => $this->toNullableDecimal($base->qudy ?? null),
                'dbh1' => $latestStatus === 'D' ? null : $this->toNullableDbh($latestRecord->dbh ?? null),
                'dbh2' => null,
                'status' => $status,
                'mode' => null,
                'living_length' => null,
                'branches' => null,
                'illumination' => null,
                'leaning' => null,
                'liana' => null,
                'fungi' => null,
                'wounded_stem' => null,
                'deformity' => null,
                'rotten' => null,
                'leaves' => null,
                'leaf_damage' => null,
                'comments_json' => null,
                'stem_corrections_json' => null,
                'date' => null,
                'team_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => null,
                'updated_by' => null,
            ];

            $rows[] = array_intersect_key($candidate, array_flip($recordColumns));
        }

        return $rows;
    }

    private function baseRowsForStemids(array $stemids): array
    {
        $baseTags = collect($stemids)
            ->map(fn ($stemid) => $this->baseTagFromStemid((string) $stemid))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($baseTags)) {
            return [];
        }

        return DB::connection('mysql1')
            ->table('base')
            ->whereIn('tag', $baseTags)
            ->get([
                'tag',
                'spcode',
                'qx',
                'qy',
                'subqx',
                'subqy',
                'qudx',
                'qudy',
            ])
            ->keyBy('tag')
            ->all();
    }


    private function speciesMapForBaseRows(array $baseRows): array
    {
        $spcodes = collect($baseRows)
            ->pluck('spcode')
            ->map(fn ($spcode) => trim((string) $spcode))
            ->filter(fn ($spcode) => $spcode !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($spcodes)) {
            return [];
        }

        return FsBaseSpinfo::query()
            ->whereIn('spcode', $spcodes)
            ->pluck('csp', 'spcode')
            ->all();
    }

    private function latestCensusRecordsForStemids(array $stemids): array
    {
        $stemids = array_values(array_filter(array_map(fn ($stemid) => trim((string) $stemid), $stemids)));

        if (empty($stemids)) {
            return [];
        }

        $latestCensuses = DB::connection('fs_mortality')
            ->table('census_records')
            ->select('stemid', DB::raw('MAX(census) as latest_census'))
            ->whereIn('stemid', $stemids)
            ->groupBy('stemid');

        $latestStatusRows = DB::connection('fs_mortality')
            ->table('census_records as cr')
            ->joinSub($latestCensuses, 'latest', function ($join) {
                $join->on('cr.stemid', '=', 'latest.stemid')
                    ->on('cr.census', '=', 'latest.latest_census');
            })
            ->get([
                'cr.stemid',
                'cr.status',
            ])
            ->keyBy('stemid');

        $latestDbhCensuses = DB::connection('fs_mortality')
            ->table('census_records')
            ->select('stemid', DB::raw('MAX(census) as latest_dbh_census'))
            ->whereIn('stemid', $stemids)
            ->whereNotNull('dbh')
            ->groupBy('stemid');

        $latestDbhRows = DB::connection('fs_mortality')
            ->table('census_records as cr')
            ->joinSub($latestDbhCensuses, 'latest_dbh', function ($join) {
                $join->on('cr.stemid', '=', 'latest_dbh.stemid')
                    ->on('cr.census', '=', 'latest_dbh.latest_dbh_census');
            })
            ->get([
                'cr.stemid',
                'cr.dbh',
            ])
            ->keyBy('stemid');

        return collect($stemids)
            ->mapWithKeys(function ($stemid) use ($latestStatusRows, $latestDbhRows) {
                $statusRow = $latestStatusRows->get($stemid);
                $dbhRow = $latestDbhRows->get($stemid);

                return [$stemid => (object) [
                    'stemid' => $stemid,
                    'status' => $statusRow->status ?? null,
                    'dbh' => $dbhRow->dbh ?? null,
                ]];
            })
            ->all();
    }

    private function baseTagFromStemid(string $stemid): string
    {
        $stemid = trim($stemid);
        $beforeDot = explode('.', $stemid, 2)[0];

        return substr($beforeDot, 0, 6);
    }

    private function getSurveyImportContext(): array
    {
        $latestImportedCensus = (int) (CensusRecord::query()->max('census') ?? 0);
        $nextCensusValue = $latestImportedCensus + 1;
        $latestCensus = Census::query()->where('census', $latestImportedCensus)->first();
        $nextCensus = Census::query()->where('census', $nextCensusValue)->first();
        $needsNewImport = false;
        $decisionMessage = '目前無法判斷是否需要匯入新資料。';
        $targetTable = $nextCensus ? 'data_batch_' . $nextCensus->data_batch : null;
        $targetTableExists = false;
        $targetTableHasData = false;

        if (!$nextCensus) {
            $decisionMessage = "找不到 census {$nextCensusValue} 的設定，請先到調查年度頁建立資料。";
        } elseif ($targetTable !== null && Schema::connection('fs_mortality')->hasTable($targetTable)) {
            $targetTableExists = true;
            $targetTableHasData = DB::connection('fs_mortality')->table($targetTable)->exists();

            if ($targetTableHasData) {
                $decisionMessage = "`{$targetTable}` 已經有資料，目前不需要再上傳。";
                return [
                    'latestImportedCensus' => $latestImportedCensus ?: null,
                    'latestCensus' => $latestCensus,
                    'nextCensus' => $nextCensus,
                    'nextCensusValue' => $nextCensusValue,
                    'needsNewImport' => false,
                    'decisionMessage' => $decisionMessage,
                    'targetTable' => $targetTable,
                    'targetTableExists' => $targetTableExists,
                    'targetTableHasData' => $targetTableHasData,
                ];
            }
        } elseif (!$latestCensus) {
            $needsNewImport = true;
            $decisionMessage = "目前 `census_records` 還沒有既有 census，請先匯入 census {$nextCensusValue} 的調查資料。";
        } else {
            $needsNewImport = (string) $nextCensus->data_batch !== (string) $latestCensus->data_batch;
            $decisionMessage = $needsNewImport
                ? "census {$nextCensusValue} 的 `data_batch` 與前一次不同，需要匯入新資料。"
                : "census {$nextCensusValue} 的 `data_batch` 與前一次相同，目前不需要匯入新資料。";
        }

        return [
            'latestImportedCensus' => $latestImportedCensus ?: null,
            'latestCensus' => $latestCensus,
            'nextCensus' => $nextCensus,
            'nextCensusValue' => $nextCensusValue,
            'needsNewImport' => $needsNewImport,
            'decisionMessage' => $decisionMessage,
            'targetTable' => $targetTable,
            'targetTableExists' => $targetTableExists,
            'targetTableHasData' => $targetTableHasData,
        ];
    }

    private function recordPaperDownloadContext(): array
    {
        $surveyImportContext = $this->getSurveyImportContext();
        $targetCensus = $surveyImportContext['nextCensus']?->census;
        $record1Exists = Schema::connection('fs_mortality')->hasTable('record1');
        $record1HasData = $record1Exists
            ? DB::connection('fs_mortality')->table('record1')->exists()
            : false;
        $record1HasDateData = false;
        $record1MatchesTargetCensus = false;

        if ($record1HasData && $targetCensus !== null) {
            $record1CensusValues = DB::connection('fs_mortality')
                ->table('record1')
                ->select('census')
                ->distinct()
                ->pluck('census')
                ->map(fn ($value) => (int) $value)
                ->all();
            $record1MatchesTargetCensus = count($record1CensusValues) === 1
                && $record1CensusValues[0] === (int) $targetCensus;
        }

        if ($record1HasData && Schema::connection('fs_mortality')->hasColumn('record1', 'date')) {
            $record1HasDateData = DB::connection('fs_mortality')
                ->table('record1')
                ->whereNotNull('date')
                ->where('date', '!=', '')
                ->exists();
        }

        if (!$record1HasData || !$record1MatchesTargetCensus) {
            return [
                'recordPaperHasRecord1Data' => $record1HasData,
                'recordPaperHasDateData' => $record1HasDateData,
                'canDownloadRecordPaper' => false,
                'recordPaperDownloadMessage' => '請先產生新一年度輸入表單。',
            ];
        }

        if ($record1HasDateData) {
            return [
                'recordPaperHasRecord1Data' => true,
                'recordPaperHasDateData' => true,
                'canDownloadRecordPaper' => false,
                'recordPaperDownloadMessage' => '已進行資料輸入，不能下載紀錄紙。',
            ];
        }

        return [
            'recordPaperHasRecord1Data' => true,
            'recordPaperHasDateData' => false,
            'canDownloadRecordPaper' => true,
            'recordPaperDownloadMessage' => null,
        ];
    }

    private function getMortalityEntryContext(): array
    {
        $surveyImportContext = $this->getSurveyImportContext();
        $targetCensus = $surveyImportContext['nextCensus']?->census;
        $record1Exists = Schema::connection('fs_mortality')->hasTable('record1');
        $record2Exists = Schema::connection('fs_mortality')->hasTable('record2');
        $record1HasData = $record1Exists
            ? DB::connection('fs_mortality')->table('record1')->exists()
            : false;
        $record2HasData = $record2Exists
            ? DB::connection('fs_mortality')->table('record2')->exists()
            : false;
        $recordTablesReady = $record1HasData && $record2HasData;
        $record1CensusValues = $record1HasData
            ? DB::connection('fs_mortality')->table('record1')->select('census')->distinct()->orderBy('census')->pluck('census')->map(fn($value) => (int) $value)->all()
            : [];
        $record2CensusValues = $record2HasData
            ? DB::connection('fs_mortality')->table('record2')->select('census')->distinct()->orderBy('census')->pluck('census')->map(fn($value) => (int) $value)->all()
            : [];
        $record1MatchesTargetCensus = $record1HasData
            && $targetCensus !== null
            && count($record1CensusValues) === 1
            && $record1CensusValues[0] === (int) $targetCensus;
        $record2MatchesTargetCensus = $record2HasData
            && $targetCensus !== null
            && count($record2CensusValues) === 1
            && $record2CensusValues[0] === (int) $targetCensus;
        $recordTablesMatchTargetCensus = $record1MatchesTargetCensus && $record2MatchesTargetCensus;

        $generateBlockedReason = null;
        $recordTablesNeedRefresh = false;
        $recordTablesStatusMessage = null;

        if (!$surveyImportContext['nextCensus']) {
            $generateBlockedReason = '找不到下一次要處理的 census，請先到調查年度確認設定。';
        } elseif (!$surveyImportContext['targetTableHasData']) {
            $generateBlockedReason = "請先在匯入調查資料頁準備 `{$surveyImportContext['targetTable']}` 的資料。";
        } elseif (!$record1HasData && !$record2HasData) {
            $recordTablesStatusMessage = '目前輸入表單尚未有資料，請先建立本次輸入表單。';
        } elseif ($recordTablesMatchTargetCensus) {
            $recordTablesStatusMessage = "目前輸入表單已是 census {$targetCensus} 的資料，可以直接開始輸入。";
        } else {
            $recordTablesNeedRefresh = true;
            $recordTablesStatusMessage = "偵測到輸入表單仍是上次調查的資料，請確認是否清除後更新為 census {$targetCensus} 的資料。";
        }

        return [
            ...$surveyImportContext,
            'record1Exists' => $record1Exists,
            'record2Exists' => $record2Exists,
            'record1HasData' => $record1HasData,
            'record2HasData' => $record2HasData,
            'record1CensusValues' => $record1CensusValues,
            'record2CensusValues' => $record2CensusValues,
            'recordTablesReady' => $recordTablesReady,
            'record1MatchesTargetCensus' => $record1MatchesTargetCensus,
            'record2MatchesTargetCensus' => $record2MatchesTargetCensus,
            'recordTablesMatchTargetCensus' => $recordTablesMatchTargetCensus,
            'recordTablesNeedRefresh' => $recordTablesNeedRefresh,
            'recordTablesStatusMessage' => $recordTablesStatusMessage,
            'canGenerateRecords' => !$recordTablesMatchTargetCensus && $generateBlockedReason === null,
            'generateBlockedReason' => $generateBlockedReason,
        ];
    }

    private function ensureDataBatchTableExists(string $tableName): bool
    {
        if (Schema::connection('fs_mortality')->hasTable($tableName)) {
            return false;
        }

        if (Schema::connection('fs_mortality')->hasTable('data_batch_3')) {
            DB::connection('fs_mortality')->statement("CREATE TABLE `{$tableName}` LIKE `data_batch_3`");
            return true;
        }

        Schema::connection('fs_mortality')->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('map_sort')->default(1);
            $table->string('map', 20)->nullable();
            $table->string('q20', 20)->nullable();
            $table->string('q10', 20)->nullable();
            $table->string('qx', 20)->nullable();
            $table->string('qy', 20)->nullable();
            $table->string('subqx', 20)->nullable();
            $table->string('subqy', 20)->nullable();
            $table->string('stemid', 20)->nullable()->index();
            $table->string('csp', 20)->nullable()->index();
            $table->string('x', 20)->nullable();
            $table->string('y', 20)->nullable();
            $table->string('dbh1', 20)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        return true;
    }

    private function ensureMortalityRecordTableExists(string $recordTable, string $targetTable): void
    {
        if (Schema::connection('fs_mortality')->hasTable($recordTable)) {
            return;
        }

        DB::connection('fs_mortality')->statement("CREATE TABLE `{$recordTable}` LIKE `{$targetTable}`");
    }

    private function isSurveyImportSeparatorRow(array $record): bool
    {
        $values = collect($record)
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values();

        if ($values->isEmpty()) {
            return true;
        }

        return $values->every(function ($value) {
            return in_array($value, ['-', '--', '—'], true);
        });
    }

    private function mapSurveyImportRowToBatchRow(array $record, int $mapSort): ?array
    {
        $headerMap = [
            'map' => 'map',
            'q20' => 'q20',
            'q10' => 'q10',
            'tag' => 'stemid',
            'sp' => 'csp',
            'species' => 'csp',
            'x' => 'x',
            'y' => 'y',
            'dbh(old)' => 'dbh1',
            'dbh_(old)' => 'dbh1',
            'dbh_old' => 'dbh1',
            'dbh_old)' => 'dbh1',
            'dbh' => 'dbh1',
        ];

        $raw = [];
        foreach ($record as $header => $value) {
            $normalizedHeader = strtolower(trim((string) $header));
            $normalizedHeader = str_replace([' ', '-', '/', "\n", "\r"], ['_', '_', '_', '', ''], $normalizedHeader);
            $normalizedHeader = str_replace(['（', '）'], ['(', ')'], $normalizedHeader);
            $normalizedHeader = preg_replace('/_+/', '_', $normalizedHeader);
            $normalizedHeader = trim((string) $normalizedHeader, '_');

            $column = $headerMap[$normalizedHeader] ?? null;
            if ($column === null) {
                continue;
            }

            $cellValue = trim((string) $value);
            $raw[$column] = $cellValue === '' ? null : $cellValue;
        }

        if (empty(array_filter($raw, function ($value) {
            return $value !== null && $value !== '' && $value !== '-';
        }))) {
            return null;
        }

        $q20 = $this->nullIfBlank($raw['q20'] ?? null);
        $map = $this->nullIfBlank($raw['map'] ?? null);
        if ($map === '=') {
            $map = $q20;
        }

        [$qx, $qy] = $this->splitSurveyImportQ20($q20);
        [$subqx, $subqy] = $this->splitSurveyImportQ10($this->nullIfBlank($raw['q10'] ?? null));
        $stemid = $this->nullIfBlank($raw['stemid'] ?? null);
        $stemid = $stemid !== null ? $this->normalizeBigTableStemid($stemid) : null;

        return [
            'map_sort' => $mapSort,
            'map' => $map,
            'q20' => $q20,
            'q10' => $this->nullIfBlank($raw['q10'] ?? null),
            'qx' => $qx,
            'qy' => $qy,
            'subqx' => $subqx,
            'subqy' => $subqy,
            'stemid' => $stemid,
            'csp' => $this->nullIfBlank($raw['csp'] ?? null),
            'x' => $this->nullIfBlank($raw['x'] ?? null),
            'y' => $this->nullIfBlank($raw['y'] ?? null),
            'dbh1' => $this->nullIfBlank($raw['dbh1'] ?? null),
            'created_at' => now(),
        ];
    }

    private function splitSurveyImportQ20(?string $q20): array
    {
        if ($q20 === null || !str_contains($q20, ',')) {
            return [null, null];
        }

        $parts = array_map('trim', explode(',', $q20, 2));

        return [
            $parts[0] !== '' ? $parts[0] : null,
            isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null,
        ];
    }

    private function splitSurveyImportQ10(?string $q10): array
    {
        $normalized = str_replace(' ', '', (string) $q10);

        return match ($normalized) {
            '<^' => ['1', '2'],
            '<v' => ['1', '1'],
            '>^' => ['2', '2'],
            '>v' => ['2', '1'],
            default => [null, null],
        };
    }

    private function convertSurveyImportContentToUtf8(string $content): string
    {
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            return $content;
        }

        $encoding = mb_detect_encoding($content, ['UTF-8', 'BIG-5', 'CP950', 'GBK', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding === false) {
            $encoding = 'CP950';
        }

        if ($encoding !== 'UTF-8') {
            $converted = @mb_convert_encoding($content, 'UTF-8', $encoding);
            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        return $content;
    }

    public function compare(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_compare', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
        ]);
    }

    public function import(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_import', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            ...$this->getMortalityEntryContext(),
        ]);
    }

    public function download(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');
        $latestCensus = $this->latestCensusRecordInfo();
        $latestCensusText = $latestCensus
            ? '第 ' . $latestCensus->census . ' 次（' . ($latestCensus->survey_year ?? '年份未設定') . ' 年）'
            : '尚無資料';

        return view('pages/fushan/mortality_download', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            'latestCensus' => $latestCensus,
            'latestCensusText' => $latestCensusText,
        ]);
    }

    public function downloadLatestCensusRecords(Request $request): StreamedResponse
    {
        $this->ensureProcessAdmin($request);

        $latestCensus = $this->latestCensusRecordInfo();
        abort_unless($latestCensus, 404);

        $census = (int) $latestCensus->census;
        $year = trim((string) ($latestCensus->survey_year ?? ''));
        $filenameYear = $year !== '' ? $year : 'unknown-year';
        $filename = 'mortality_census_' . $census . '_' . $filenameYear . '.txt';
        $headers = $this->mortalityDownloadHeaders();

        return $this->streamTxt($filename, $headers, function ($handle) use ($census) {
            $rows = DB::connection('fs_mortality')
                ->table('census_records as cr')
                ->leftJoin('tree_individuals as ti', 'cr.stemid', '=', 'ti.stemid')
                ->leftJoin(DB::raw($this->qualifiedBaseTable() . ' as b'), function ($join) {
                    $join->on('b.tag', '=', DB::raw("LEFT(SUBSTRING_INDEX(cr.stemid, '.', 1), 6)"));
                })
                ->where('cr.census', $census)
                ->select([
                    'cr.id',
                    'cr.map',
                    'cr.stemid',
                    'cr.date',
                    'cr.dbh',
                    'cr.status',
                    'cr.mode',
                    'cr.living_length',
                    'cr.branches',
                    'cr.illumination',
                    'cr.leaning',
                    'cr.liana',
                    'cr.fungi',
                    'cr.wounded_stem',
                    'cr.deformity',
                    'cr.rotten',
                    'cr.leaves',
                    'cr.leaf_damage',
                    'b.spcode',
                    'b.qx',
                    'b.qy',
                    'b.subqx',
                    'b.subqy',
                    'b.qudx',
                    'b.qudy',
                ])
                ->orderBy('cr.map')
                ->orderBy('b.qx')
                ->orderBy('b.qy')
                ->orderBy('b.subqx')
                ->orderBy('b.subqy')
                ->orderBy('cr.stemid')
                ->get();

            $comments = $this->downloadCommentsByRecordId($rows->pluck('id')->all());

            foreach ($rows as $row) {
                $stemid = (string) $row->stemid;

                fputcsv($handle, [
                    $row->map,
                    $this->formatQ20($row->qx, $row->qy),
                    $this->formatQ10($row->subqx, $row->subqy),
                    $row->qx,
                    $row->qy,
                    $row->subqx,
                    $row->subqy,
                    $stemid,
                    $row->spcode ?? '',
                    $this->formatDownloadValue($row->qudx),
                    $this->formatDownloadValue($row->qudy),
                    '',
                    $this->formatDownloadValue($row->dbh),
                    $row->status,
                    $row->mode,
                    $this->formatDownloadValue($row->living_length),
                    $row->branches,
                    $row->illumination,
                    $row->leaning,
                    $row->liana,
                    $this->formatDownloadValue($row->fungi),
                    $row->wounded_stem,
                    $row->deformity,
                    $row->rotten,
                    $row->leaves,
                    $this->formatDownloadValue($row->leaf_damage),
                    $comments[(int) $row->id] ?? '',
                    $this->formatDownloadDate($row->date),
                ], "\t");
            }
        });
    }

    private function latestCensusRecordInfo(): ?object
    {
        $latestCensus = DB::connection('fs_mortality')
            ->table('census_records')
            ->max('census');

        if ($latestCensus === null) {
            return null;
        }

        $censusInfo = DB::connection('fs_mortality')
            ->table('censuses')
            ->where('census', $latestCensus)
            ->first(['census', 'survey_year']);

        if ($censusInfo) {
            return $censusInfo;
        }

        return (object) [
            'census' => (int) $latestCensus,
            'survey_year' => null,
        ];
    }

    private function nextMortalitySurveyContext(): array
    {
        $latestCensus = $this->latestCensusRecordInfo();
        $latestSurveyYear = $latestCensus?->survey_year !== null
            ? (int) $latestCensus->survey_year
            : 2025;
        $nextSurveyYear = $latestSurveyYear + 1;

        return [
            'latestCensus' => $latestCensus,
            'latestSurveyYear' => $latestSurveyYear,
            'nextSurveyYear' => $nextSurveyYear,
            'nextCensus' => $latestCensus?->census !== null ? (int) $latestCensus->census + 1 : null,
        ];
    }

    private function mortalityDownloadHeaders(): array
    {
        return [
            'map',
            'q20',
            'q10',
            'qx',
            'qy',
            'subqx',
            'subqy',
            'stemid',
            'sp',
            'x',
            'y',
            'dbh1',
            'dbh2',
            'status',
            'mode',
            'living_length',
            'branches',
            'illumination',
            'leaning',
            'liana',
            'fungi',
            'wounded_stem',
            'deformity',
            'rotten',
            'leaves',
            'leaf_damage',
            'comments',
            'date',
        ];
    }

    private function qualifiedBaseTable(): string
    {
        $database = config('database.connections.mysql1.database');

        if (!$database) {
            return '`base`';
        }

        return '`' . str_replace('`', '``', $database) . '`.`base`';
    }
    private function downloadCommentsByRecordId(array $recordIds): array
    {
        $recordIds = array_values(array_filter(array_map('intval', $recordIds)));

        if (empty($recordIds)) {
            return [];
        }

        return DB::connection('fs_mortality')
            ->table('census_record_comments as crc')
            ->leftJoin('comment_options as co', 'crc.comment_option_id', '=', 'co.id')
            ->whereIn('crc.census_record_id', $recordIds)
            ->orderBy('crc.census_record_id')
            ->orderBy('crc.sort_order')
            ->orderBy('crc.id')
            ->get([
                'crc.census_record_id',
                'crc.comment_other',
                'co.comment_en',
                'co.comment_zh',
            ])
            ->groupBy('census_record_id')
            ->map(function ($rows) {
                return $rows
                    ->map(function ($row) {
                        $optionText = $this->nullIfBlank($row->comment_en)
                            ?? $this->nullIfBlank($row->comment_zh);
                        $otherText = $this->nullIfBlank($row->comment_other);

                        return $optionText ?? $otherText;
                    })
                    ->filter(fn ($text) => $text !== null && $text !== '')
                    ->implode(' | ');
            })
            ->all();
    }

    private function formatQ20($qx, $qy): string
    {
        if ($qx === null || $qy === null || $qx === '' || $qy === '') {
            return '';
        }

        return str_pad((string) (int) $qx, 2, '0', STR_PAD_LEFT)
            . ','
            . str_pad((string) (int) $qy, 2, '0', STR_PAD_LEFT);
    }

    private function formatQ10($subqx, $subqy): string
    {
        $subqx = (string) $subqx;
        $subqy = (string) $subqy;

        return match ($subqx . ',' . $subqy) {
            '1,2' => '<^',
            '1,1' => '<v',
            '2,2' => '>^',
            '2,1' => '>v',
            default => '',
        };
    }

    private function formatDownloadValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if (is_numeric($value)) {
            return rtrim(rtrim(sprintf('%.6F', (float) $value), '0'), '.');
        }

        return trim((string) $value);
    }

    private function formatDownloadDate($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }

    private function streamTxt(string $filename, array $headers, callable $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writeRows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, "\t");
            $writeRows($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function dataviewer(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_dataviewer', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
        ]);
    }

    public function process(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $user = $request->user();
        $site = $request->route('site');
        $basicProcessed = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('qx')
            ->where('qx', '!=', '')
            ->exists();
        $peopleProcessed = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('team_id')
            ->exists();
        $commentsRemaining = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('comments')
            ->where('comments', '!=', '')
            ->exists();
        $readyToImportRecords = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->whereNotNull('team_id')
            ->exists();
        $existingCensusImports = CensusRecord::query()
            ->select('census')
            ->distinct()
            ->pluck('census')
            ->map(fn($value) => (int) $value)
            ->all();
        $firstImportStageWithDate = ImportStage::query()
            ->whereNotNull('date')
            ->orderBy('id')
            ->first(['date']);
        $derivedImportYear = $this->extractImportStageYear($firstImportStageWithDate?->date);
        $derivedCensusConfig = $derivedImportYear !== null
            ? Census::query()->where('survey_year', $derivedImportYear)->first()
            : null;
        $derivedProcessCensus = $derivedCensusConfig?->census !== null
            ? (int) $derivedCensusConfig->census
            : null;
        $derivedProcessSurveyYear = $derivedCensusConfig?->survey_year !== null
            ? (string) $derivedCensusConfig->survey_year
            : $derivedImportYear;
        $processCensusStatus = null;

        if ($derivedImportYear === null) {
            $processCensusStatus = '目前無法從 `import_stage.date` 判斷年份，因此尚不能決定要處理哪一次 census。';
        } elseif ($derivedCensusConfig === null) {
            $processCensusStatus = "找不到 survey_year = {$derivedImportYear} 的 census 設定，請先補上 `censuses` 資料。";
        }
        $hasImportStageStemids = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->exists();
        $commentOtherRemainingCount = CensusRecordComment::query()
            ->whereNotNull('comment_other')
            ->where('comment_other', '!=', '')
            ->count();

        return view('pages/fushan/mortality_process', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            'basicProcessed' => $basicProcessed,
            'peopleProcessed' => $peopleProcessed,
            'commentsRemaining' => $commentsRemaining,
            'readyToImportRecords' => $readyToImportRecords,
            'existingCensusImports' => $existingCensusImports,
            'derivedProcessCensus' => $derivedProcessCensus,
            'derivedProcessSurveyYear' => $derivedProcessSurveyYear,
            'processCensusStatus' => $processCensusStatus,
            'commentOtherRemainingCount' => $commentOtherRemainingCount,
            'hasImportStageStemids' => $hasImportStageStemids,
        ]);
    }

    public function runBasicProcess(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $alreadyProcessed = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('qx')
            ->where('qx', '!=', '')
            ->exists();

        if ($alreadyProcessed) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', '基礎處理已完成，未重複執行。');
        }

        $invalidQ20Count = DB::connection('fs_mortality')
            ->table('import_stage')
            ->where(function ($query) {
                $query->whereNull('q20')
                    ->orWhere('q20', 'not like', '%,%');
            })
            ->count();

        $updatedMapCount = DB::connection('fs_mortality')
            ->table('import_stage')
            ->where('map', '=')
            ->update([
                'map' => DB::raw('q20'),
            ]);

        $updatedQ20SplitCount = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('q20')
            ->update([
                'qx' => DB::raw("SUBSTRING_INDEX(q20, ',', 1)"),
                'qy' => DB::raw("SUBSTRING_INDEX(q20, ',', -1)"),
            ]);

        $updatedQ10SplitCount = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('q10')
            ->update([
                'subqx' => DB::raw("
                    CASE
                        WHEN REPLACE(q10, ' ', '') = '<^' THEN '1'
                        WHEN REPLACE(q10, ' ', '') = '<v' THEN '1'
                        WHEN REPLACE(q10, ' ', '') = '>^' THEN '2'
                        WHEN REPLACE(q10, ' ', '') = '>v' THEN '2'
                        ELSE subqx
                    END
                "),
                'subqy' => DB::raw("
                    CASE
                        WHEN REPLACE(q10, ' ', '') = '<^' THEN '2'
                        WHEN REPLACE(q10, ' ', '') = '<v' THEN '1'
                        WHEN REPLACE(q10, ' ', '') = '>^' THEN '2'
                        WHEN REPLACE(q10, ' ', '') = '>v' THEN '1'
                        ELSE subqy
                    END
                "),
            ]);

        $stemRows = ImportStage::query()
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->get(['id', 'stemid']);

        $updatedStemidCount = 0;

        foreach ($stemRows as $row) {
            $normalizedStemid = $this->normalizeBigTableStemid((string) $row->stemid);

            if ($normalizedStemid === (string) $row->stemid) {
                continue;
            }

            $updatedStemidCount += ImportStage::query()
                ->whereKey($row->id)
                ->update([
                    'stemid' => $normalizedStemid,
                ]);
        }

        return redirect()
            ->route('admin.fushan.mortality.process')
            ->with('status', '基本處理已完成。')
            ->with('process_summary', [
                'invalid_q20_count' => $invalidQ20Count,
                'updated_map_count' => $updatedMapCount,
                'updated_q20_split_count' => $updatedQ20SplitCount,
                'updated_q10_split_count' => $updatedQ10SplitCount,
                'updated_stemid_count' => $updatedStemidCount,
            ]);
    }

    public function runPeopleProcess(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $validated = $request->validate([
            'census' => ['required', 'integer', 'min:1'],
        ], [
            'census.required' => '請輸入 census。',
            'census.integer' => 'census 必須是整數。',
            'census.min' => 'census 必須大於或等於 1。',
        ]);

        $census = (int) $validated['census'];

        $alreadyProcessed = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('team_id')
            ->exists();

        if ($alreadyProcessed) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', '調查者處理已完成，未重複執行。');
        }

        $rows = ImportStage::query()
            ->whereNotNull('people')
            ->where('people', '!=', '')
            ->get(['id', 'people']);

        $allNames = [];
        $rowSignatures = [];

        foreach ($rows as $row) {
            $names = $this->normalizePeopleNames($row->people);

            if (empty($names)) {
                continue;
            }

            $signature = implode('、', $names);
            $rowSignatures[$row->id] = [
                'names' => $names,
                'signature' => $signature,
            ];

            foreach ($names as $name) {
                $allNames[$name] = $name;
            }
        }

        $createdPeopleCount = 0;
        $createdTeamCount = 0;
        $updatedStageCount = 0;

        DB::transaction(function () use (
            $census,
            $allNames,
            $rowSignatures,
            &$createdPeopleCount,
            &$createdTeamCount,
            &$updatedStageCount
        ) {
            $existingPeople = Person::query()
                ->whereIn('name', array_values($allNames))
                ->pluck('id', 'name')
                ->all();

            foreach ($allNames as $name) {
                if (isset($existingPeople[$name])) {
                    continue;
                }

                $person = Person::create(['name' => $name]);
                $existingPeople[$name] = $person->id;
                $createdPeopleCount++;
            }

            $teamSignatures = [];

            Team::query()
                ->where('census', $census)
                ->with(['teamMembers' => function ($query) {
                    $query->orderBy('person_id');
                }])
                ->get()
                ->each(function ($team) use (&$teamSignatures) {
                    $personIds = $team->teamMembers->pluck('person_id')->map(fn($id) => (int) $id)->sort()->values()->all();
                    if (empty($personIds)) {
                        return;
                    }

                    $teamSignatures[implode('-', $personIds)] = $team->id;
                });

            $signatureToTeamId = [];

            foreach ($rowSignatures as $rowId => $info) {
                $personIds = collect($info['names'])
                    ->map(fn($name) => (int) $existingPeople[$name])
                    ->sort()
                    ->values()
                    ->all();

                $personSignature = implode('-', $personIds);

                if (!isset($teamSignatures[$personSignature])) {
                    $team = Team::create([
                        'census' => $census,
                    ]);

                    foreach ($personIds as $personId) {
                        TeamMember::create([
                            'team_id' => $team->id,
                            'person_id' => $personId,
                            'role' => null,
                        ]);
                    }

                    $teamSignatures[$personSignature] = $team->id;
                    $createdTeamCount++;
                }

                $signatureToTeamId[$rowId] = $teamSignatures[$personSignature];
            }

            foreach ($signatureToTeamId as $rowId => $teamId) {
                $updatedStageCount += ImportStage::query()
                    ->whereKey($rowId)
                    ->update([
                        'team_id' => $teamId,
                    ]);
            }
        });

        return redirect()
            ->route('admin.fushan.mortality.process')
            ->with('status', '調查者處理已完成。')
            ->with('people_process_summary', [
                'census' => $census,
                'created_people_count' => $createdPeopleCount,
                'created_team_count' => $createdTeamCount,
                'updated_stage_count' => $updatedStageCount,
            ]);
    }

    public function runTreeIndividualsProcess(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $basicProcessed = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('qx')
            ->where('qx', '!=', '')
            ->exists();

        if (!$basicProcessed) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', '請先完成基本處理，再同步 tree_individuals。');
        }

        $rows = ImportStage::query()
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->orderBy('id')
            ->get(['stemid']);
        $stageIndividuals = $this->buildTreeIndividualsPayload($rows);

        if (empty($stageIndividuals)) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', 'import_stage 目前沒有可同步的 stemid，未執行 tree_individuals 同步。');
        }

        $summary = $this->syncTreeIndividuals($stageIndividuals);

        return redirect()
            ->route('admin.fushan.mortality.process')
            ->with('status', 'tree_individuals 同步已完成。')
            ->with('tree_individual_process_summary', [
                'created_count' => $summary['created_count'],
                'updated_count' => $summary['updated_count'],
                'deactivated_count' => $summary['deactivated_count'],
            ]);
    }

    private function buildTreeIndividualsPayload(iterable $rows): array
    {
        $stageIndividuals = [];

        foreach ($rows as $row) {
            $stemid = trim((string) ($row->stemid ?? ''));

            if ($stemid === '' || isset($stageIndividuals[$stemid])) {
                continue;
            }

            $stageIndividuals[$stemid] = [
                'stemid' => $stemid,
                'is_active' => true,
            ];
        }

        return $stageIndividuals;
    }

    private function syncTreeIndividuals(array $stageIndividuals): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $deactivatedCount = 0;

        DB::transaction(function () use ($stageIndividuals, &$createdCount, &$updatedCount, &$deactivatedCount) {
            $existingIndividuals = TreeIndividual::query()
                ->whereIn('stemid', array_keys($stageIndividuals))
                ->get()
                ->keyBy('stemid');

            foreach ($stageIndividuals as $stemid => $attributes) {
                $existing = $existingIndividuals->get($stemid);

                if ($existing) {
                    $existing->fill($attributes);

                    if ($existing->isDirty()) {
                        $existing->save();
                        $updatedCount++;
                    }

                    continue;
                }

                TreeIndividual::query()->create($attributes);
                $createdCount++;
            }

            $activeStemids = TreeIndividual::query()
                ->where('is_active', 1)
                ->pluck('stemid')
                ->all();

            $stemidsToDeactivate = array_values(array_diff($activeStemids, array_keys($stageIndividuals)));

            if (!empty($stemidsToDeactivate)) {
                $deactivatedCount = TreeIndividual::query()
                    ->whereIn('stemid', $stemidsToDeactivate)
                    ->update([
                        'is_active' => 0,
                    ]);
            }
        });

        return [
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
            'deactivated_count' => $deactivatedCount,
        ];
    }

    public function runCommentProcess(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $redirectRoute = $request->input('return_to') === 'review'
            ? 'admin.fushan.mortality.process.comments.review'
            : 'admin.fushan.mortality.process';

        $commentsRemaining = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('comments')
            ->where('comments', '!=', '')
            ->exists();

        if (!$commentsRemaining) {
            return redirect()
                ->route($redirectRoute)
                ->with('status', '沒有待整理的 comments，未重複執行。');
        }

        $commentOptions = DB::connection('fs_mortality')
            ->table('comment_options')
            ->where('is_active', 1)
            ->pluck('id', 'comment_en')
            ->all();

        $rows = ImportStage::query()
            ->whereNotNull('comments')
            ->where('comments', '!=', '')
            ->get(['id', 'comments']);

        $updatedCount = 0;
        $skippedMultipleCount = 0;
        $skippedUnmappedCount = 0;

        foreach ($rows as $row) {
            $comments = $this->normalizeComments($row->comments);

            if (empty($comments)) {
                continue;
            }

            $entries = [];
            $hasUnmappedComment = false;

            foreach ($comments as $comment) {
                $commentId = $commentOptions[$comment] ?? null;

                if (!$commentId) {
                    $hasUnmappedComment = true;
                    break;
                }

                $entries[] = [
                    'kind' => 'option',
                    'comment_id' => (int) $commentId,
                ];
            }

            if ($hasUnmappedComment) {
                $skippedUnmappedCount++;
                continue;
            }

            if (count($comments) > 1) {
                $skippedMultipleCount++;
            }

            $updatedCount += ImportStage::query()
                ->whereKey($row->id)
                ->update([
                    'comments_json' => json_encode($entries, JSON_UNESCAPED_UNICODE),
                    'comments' => null,
                ]);
        }

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'Comments 整理已完成。')
            ->with('comment_process_summary', [
                'updated_count' => $updatedCount,
                'skipped_multiple_count' => $skippedMultipleCount,
                'skipped_unmapped_count' => $skippedUnmappedCount,
            ]);
    }

    public function runCensusRecordImport(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $validated = $request->validate([
            'census' => ['required', 'integer', 'min:1'],
        ], [
            'census.required' => '請輸入 census。',
            'census.integer' => 'census 必須是整數。',
            'census.min' => 'census 必須大於或等於 1。',
        ]);

        $census = (int) $validated['census'];
        $userName = (string) ($request->user()?->account ?? $request->user()?->name ?? '');

        $censusAlreadyImported = CensusRecord::query()
            ->where('census', $census)
            ->exists();

        if ($censusAlreadyImported) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', "census {$census} 的 `census_records` 已存在，未重複匯入。");
        }

        $commentsRemaining = DB::connection('fs_mortality')
            ->table('import_stage')
            ->whereNotNull('comments')
            ->where('comments', '!=', '')
            ->exists();

        if ($commentsRemaining) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', '請先完成 `comments` 整理並寫入 `comments_json` 後再匯入 `census_records`。');
        }

        $rows = ImportStage::query()
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->whereNotNull('team_id')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', 'import_stage 目前沒有可匯入 `census_records` 的資料。');
        }

        $censusConfig = Census::query()
            ->where('census', $census)
            ->first();

        if (!$censusConfig) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', "找不到 census {$census} 對應的 `censuses` 設定，未匯入。");
        }

        $expectedSurveyYear = trim((string) $censusConfig->survey_year);
        $importYears = $rows
            ->map(fn($row) => $this->extractImportStageYear($row->date))
            ->filter(fn($year) => $year !== null)
            ->unique()
            ->values()
            ->all();

        if (empty($importYears)) {
            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', 'import_stage.date 沒有可比對的年份，未匯入 `census_records`。');
        }

        if (count($importYears) !== 1 || (string) $importYears[0] !== $expectedSurveyYear) {
            $importYearText = implode('、', array_map('strval', $importYears));

            return redirect()
                ->route('admin.fushan.mortality.process')
                ->with('status', "import_stage.date 年份（{$importYearText}）與 census {$census} 對應的 survey_year（{$expectedSurveyYear}）不符，未匯入 `census_records`。");
        }

        $commentOptionIds = CommentOption::query()
            ->pluck('id', 'id')
            ->all();

        $commentOptionIdsByCode = CommentOption::query()
            ->pluck('id', 'code')
            ->all();
        $treeIndividualStemids = TreeIndividual::query()
            ->pluck('stemid')
            ->all();
        $treeIndividualStemidMap = array_fill_keys($treeIndividualStemids, true);
        $dbhBackfillSource = $censusConfig;

        $createdCount = 0;
        $updatedCount = 0;
        $commentCount = 0;
        $stemCorrectionCount = 0;
        $skippedMissingTreeCount = 0;
        $skippedMissingTreeStemids = [];

        DB::transaction(function () use (
            $rows,
            $census,
            $userName,
            $commentOptionIds,
            $commentOptionIdsByCode,
            $treeIndividualStemidMap,
            $dbhBackfillSource,
            &$createdCount,
            &$updatedCount,
            &$commentCount,
            &$stemCorrectionCount,
            &$skippedMissingTreeCount,
            &$skippedMissingTreeStemids
        ) {
            foreach ($rows as $row) {
                $normalizedStemid = trim((string) $row->stemid);

                if (!isset($treeIndividualStemidMap[$normalizedStemid])) {
                    $skippedMissingTreeCount++;
                    $skippedMissingTreeStemids[$normalizedStemid] = true;
                    continue;
                }

                $sourceStatus = strtoupper(trim((string) ($row->status ?? '')));
                $resolvedStatus = $sourceStatus === 'OK'
                    ? 'A'
                    : ($this->nullIfBlank($row->status) ?? 'NF');
                $resolvedMode = $sourceStatus === 'OK'
                    ? 'S'
                    : $this->nullIfBlank($row->mode);
                $resolvedBranches = $sourceStatus === 'OK'
                    ? 100
                    : $this->toNullableInteger($row->branches);

                $attributes = [
                    'map' => $this->nullIfBlank($row->map),
                    'date' => $row->date,
                    'dbh' => $this->toNullableDbh($row->dbh2),
                    'status' => $resolvedStatus,
                    'mode' => $resolvedMode,
                    'living_length' => $this->toNullableDecimal($row->living_length),
                    'branches' => $resolvedBranches,
                    'illumination' => $this->toNullableInteger($row->illumination),
                    'leaning' => $this->toNullableInteger($row->leaning),
                    'liana' => $this->nullIfBlank($row->liana),
                    'fungi' => $this->toNullableBoolean($row->fungi),
                    'wounded_stem' => $this->toNullableInteger($row->wounded_stem),
                    'deformity' => $this->toNullableInteger($row->deformity),
                    'rotten' => $this->toNullableInteger($row->rotten),
                    'leaves' => $this->toNullableInteger($row->leaves),
                    'leaf_damage' => $this->toNullableBoolean($row->leaf_damage),
                    'team_id' => $row->team_id,
                    'updated_by' => $userName !== '' ? $userName : null,
                ];

                $existing = CensusRecord::query()
                    ->where('stemid', $normalizedStemid)
                    ->where('census', $census)
                    ->first();

                if ($existing) {
                    $existing->fill($attributes);

                    if ($existing->isDirty()) {
                        $existing->save();
                        $updatedCount++;
                    }

                    $censusRecord = $existing;
                } else {
                    $censusRecord = CensusRecord::query()->create(array_merge($attributes, [
                        'stemid' => $normalizedStemid,
                        'census' => $census,
                        'created_by' => $userName !== '' ? $userName : null,
                    ]));
                    $createdCount++;
                }

                CensusRecordComment::query()
                    ->where('census_record_id', $censusRecord->id)
                    ->delete();

                foreach ((array) $row->comments_json as $index => $item) {
                    $kind = trim((string) ($item['kind'] ?? ''));
                    $commentId = (int) ($item['comment_id'] ?? 0);
                    $code = trim((string) ($item['code'] ?? ''));
                    $text = trim((string) ($item['text'] ?? ''));

                    if ($kind === 'option' && $commentId > 0 && isset($commentOptionIds[$commentId])) {
                        CensusRecordComment::query()->create([
                            'census_record_id' => $censusRecord->id,
                            'comment_option_id' => $commentId,
                            'comment_other' => $text !== '' ? $text : null,
                            'sort_order' => $index + 1,
                        ]);
                        $commentCount++;
                        continue;
                    }

                    if ($kind === 'option' && $code !== '' && isset($commentOptionIdsByCode[$code])) {
                        CensusRecordComment::query()->create([
                            'census_record_id' => $censusRecord->id,
                            'comment_option_id' => $commentOptionIdsByCode[$code],
                            'comment_other' => $text !== '' ? $text : null,
                            'sort_order' => $index + 1,
                        ]);
                        $commentCount++;
                        continue;
                    }

                    if ($text !== '') {
                        CensusRecordComment::query()->create([
                            'census_record_id' => $censusRecord->id,
                            'comment_option_id' => null,
                            'comment_other' => $text,
                            'sort_order' => $index + 1,
                        ]);
                        $commentCount++;
                    }
                }

                StemCorrection::query()
                    ->where('census_record_id', $censusRecord->id)
                    ->delete();

                foreach ((array) $row->stem_corrections_json as $item) {
                    $fieldName = trim((string) ($item['field'] ?? ''));
                    $newValue = trim((string) ($item['text'] ?? ''));

                    if ($fieldName === '' && $newValue === '') {
                        continue;
                    }

                    StemCorrection::query()->create([
                        'stemid' => $normalizedStemid,
                        'census_record_id' => $censusRecord->id,
                        'correction_type' => $this->determineStemCorrectionType($fieldName),
                        'field_name' => $fieldName !== '' ? $fieldName : 'other',
                        'old_value' => $this->resolveStemCorrectionOldValue($row, $fieldName),
                        'new_value' => $newValue !== '' ? $newValue : null,
                        'description' => null,
                        'status' => 'pending',
                        'created_by' => $userName !== '' ? $userName : null,
                        'updated_by' => $userName !== '' ? $userName : null,
                    ]);
                    $stemCorrectionCount++;
                }
            }

            if ($dbhBackfillSource && (int) $dbhBackfillSource->has_dbh === 0) {
                $dbhCensus = $this->nullIfBlank($dbhBackfillSource->dbh_census);

                if ($dbhCensus !== null) {
                    $this->backfillCensusRecordDbh($census, $dbhCensus);
                }
            }
        });

        return redirect()
            ->route('admin.fushan.mortality.process')
            ->with('status', '已完成匯入 `census_records`。')
            ->with('census_record_import_summary', [
                'census' => $census,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'comment_count' => $commentCount,
                'stem_correction_count' => $stemCorrectionCount,
                'skipped_missing_tree_count' => $skippedMissingTreeCount,
                'skipped_missing_tree_stemids' => array_keys($skippedMissingTreeStemids),
            ]);
    }

    public function commentReview(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $user = $request->user();
        $site = $request->route('site');

        $baseQuery = ImportStage::query()
            ->whereNotNull('comments')
            ->where('comments', '!=', '')
            ->orderBy('stemid');

        $remainingCount = (clone $baseQuery)->count();
        $records = (clone $baseQuery)
            ->limit(10)
            ->get();

        $commentOptions = CommentOption::query()
            ->where('is_active', 1)
            ->withCount('censusRecordComments')
            ->orderByDesc('census_record_comments_count')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $commentOptionIdsByCode = CommentOption::query()
            ->where('is_active', 1)
            ->pluck('id', 'code')
            ->all();
        $commentCategories = CommentOption::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->unique()
            ->values()
            ->all();

        $stemCorrectionOptions = [
            'qx' => '20x',
            'qy' => '20y',
            'subqx' => '10x',
            'subqy' => '10y',
            'stemid' => 'stemid',
            'csp' => 'csp',
            'other' => 'other',
        ];

        return view('pages/fushan/mortality_comment_review', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            'records' => $records,
            'remainingCount' => $remainingCount,
            'commentOptions' => $commentOptions,
            'commentCategories' => $commentCategories,
            'commentOptionIdsByCode' => $commentOptionIdsByCode,
            'stemCorrectionOptions' => $stemCorrectionOptions,
        ]);
    }

    public function storeCommentOption(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:fs_mortality.comment_options,code'],
            'comment_en' => ['required', 'string', 'max:255'],
            'comment_zh' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
        ], [
            'code.unique' => '這個 option code 已存在。',
            'comment_en.required' => '請輸入英文內容。',
            'comment_zh.required' => '請輸入中文內容。',
        ]);

        $maxSortOrder = (int) CommentOption::query()->max('sort_order');
        $normalizedCode = $this->nullIfBlank($validated['code'] ?? null);

        $commentOption = CommentOption::create([
            'code' => $normalizedCode,
            'comment_en' => $validated['comment_en'],
            'comment_zh' => $validated['comment_zh'],
            'category' => $validated['category'] ?? null,
            'is_active' => true,
            'sort_order' => $maxSortOrder + 1,
        ]);

        $option = [
            'id' => $commentOption->id,
            'code' => $normalizedCode,
            'comment_en' => $validated['comment_en'],
            'comment_zh' => $validated['comment_zh'],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => '已新增新的 comment option。',
                'option' => $option,
            ]);
        }

        return redirect()
            ->route('admin.fushan.mortality.process.comments.review')
            ->with('status', '已新增新的 comment option。');
    }

    public function saveCommentReviewPage(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $records = $request->input('records', []);
        $savedCount = 0;

        foreach ($records as $recordId => $payload) {
            $importStage = ImportStage::query()->find($recordId);

            if (!$importStage) {
                continue;
            }

            $commentItems = $payload['comment_items'] ?? [];
            $stemCorrectionItems = $payload['stem_correction_items'] ?? [];

            $commentsJson = $this->buildCommentReviewPayload($commentItems);
            $stemCorrectionsJson = $this->buildStemCorrectionPayload($stemCorrectionItems);

            if (empty($commentsJson) && empty($stemCorrectionsJson)) {
                continue;
            }

            $importStage->update([
                'comments_json' => $commentsJson ?: null,
                'stem_corrections_json' => $stemCorrectionsJson ?: null,
                'comments' => null,
            ]);

            $savedCount++;
        }

        return redirect()
            ->route('admin.fushan.mortality.process.comments.review')
            ->with('status', "已儲存本頁 {$savedCount} 筆整理結果。");
    }

    public function commentOtherReview(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $user = $request->user();
        $site = $request->route('site');

        $baseQuery = CensusRecordComment::query()
            ->with(['censusRecord'])
            ->whereNotNull('comment_other')
            ->where('comment_other', '!=', '')
            ->orderBy('id');

        $remainingCount = (clone $baseQuery)->count();
        $records = (clone $baseQuery)
            ->paginate(10)
            ->withQueryString();

        $commentOptions = CommentOption::query()
            ->where('is_active', 1)
            ->withCount('censusRecordComments')
            ->orderByDesc('census_record_comments_count')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $commentCategories = CommentOption::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category')
            ->unique()
            ->values()
            ->all();

        return view('pages/fushan/mortality_comment_other_review', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->account ?? $user->name,
            'records' => $records,
            'remainingCount' => $remainingCount,
            'commentOptions' => $commentOptions,
            'commentCategories' => $commentCategories,
        ]);
    }

    public function saveCommentOtherReviewPage(Request $request)
    {
        $this->ensureProcessAdmin($request);

        $records = $request->input('records', []);
        $savedCount = 0;

        foreach ($records as $recordId => $payload) {
            $commentRecord = CensusRecordComment::query()->find($recordId);

            if (!$commentRecord) {
                continue;
            }

            $commentOptionId = $payload['comment_option_id'] ?? null;
            $commentOptionId = $commentOptionId !== null && $commentOptionId !== ''
                ? (int) $commentOptionId
                : null;
            $commentOther = $this->nullIfBlank($payload['comment_other'] ?? null);

            $commentRecord->fill([
                'comment_option_id' => $commentOptionId,
                'comment_other' => $commentOther,
            ]);

            if (!$commentRecord->isDirty()) {
                continue;
            }

            $commentRecord->save();
            $savedCount++;
        }

        return redirect()
            ->route('admin.fushan.mortality.process.comment-other.review', ['page' => $request->input('page')])
            ->with('status', "已儲存本頁 {$savedCount} 筆 comment_other 整理結果。");
    }

    private function normalizePeopleNames(?string $people): array
    {
        if ($people === null) {
            return [];
        }

        $normalized = str_replace(['，', ','], '、', $people);
        $parts = explode('、', $normalized);

        return collect($parts)
            ->map(function ($name) {
                return trim(str_replace(["\u{3000}", ' '], '', $name));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function extractImportStageYear($date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y');
        }

        $value = trim((string) $date);

        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        return date('Y', $timestamp);
    }

    private function normalizeComments(?string $comments): array
    {
        if ($comments === null) {
            return [];
        }

        return collect(preg_split('/[;；,，]+/u', $comments) ?: [])
            ->map(fn($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function buildCommentReviewPayload(array $items): array
    {
        $payload = [];

        foreach ($items as $item) {
            $commentId = (int) ($item['comment_id'] ?? 0);
            $code = trim((string) ($item['code'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));

            if ($commentId === 0 && $code === '' && $text === '') {
                continue;
            }

            if ($commentId > 0) {
                $entry = [
                    'kind' => 'option',
                    'comment_id' => $commentId,
                ];

                if ($text !== '') {
                    $entry['text'] = $text;
                }

                $payload[] = $entry;
                continue;
            }

            if ($code !== '') {
                $entry = [
                    'kind' => 'option',
                    'code' => $code,
                ];

                if ($text !== '') {
                    $entry['text'] = $text;
                }

                $payload[] = $entry;
                continue;
            }

            $payload[] = [
                'kind' => 'other',
                'text' => $text,
            ];
        }

        return $payload;
    }

    private function buildStemCorrectionPayload(array $items): array
    {
        $payload = [];

        foreach ($items as $item) {
            $field = trim((string) ($item['field'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));

            if ($field === '' && $text === '') {
                continue;
            }

            $payload[] = [
                'field' => $field !== '' ? $field : 'other',
                'text' => $text,
            ];
        }

        return $payload;
    }

    private function nullIfBlank(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toNullableInteger(mixed $value): ?int
    {
        $normalized = $this->nullIfBlank($value);

        return $normalized === null ? null : (int) $normalized;
    }

    private function toNullableDecimal(mixed $value): ?float
    {
        $normalized = $this->nullIfBlank($value);

        return $normalized === null ? null : (float) $normalized;
    }

    private function toNullableDbh(mixed $value): ?float
    {
        $dbh = $this->toNullableDecimal($value);

        return ($dbh === null || $dbh == 0.0) ? null : $dbh;
    }

    private function toNullableBoolean(mixed $value): ?bool
    {
        $normalized = $this->nullIfBlank($value);

        if ($normalized === null) {
            return null;
        }

        if (in_array($normalized, ['1', 'true', 'TRUE', 'yes', 'Y'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'FALSE', 'no', 'N'], true)) {
            return false;
        }

        return (bool) ((int) $normalized);
    }

    private function determineStemCorrectionType(string $fieldName): string
    {
        return match ($fieldName) {
            'qx', 'qy', 'subqx', 'subqy' => 'location',
            'stemid' => 'stemid',
            'csp' => 'species',
            default => 'other',
        };
    }

    private function resolveStemCorrectionOldValue(ImportStage $row, string $fieldName): ?string
    {
        return match ($fieldName) {
            'qx' => $this->nullIfBlank($row->qx),
            'qy' => $this->nullIfBlank($row->qy),
            'subqx' => $this->nullIfBlank($row->subqx),
            'subqy' => $this->nullIfBlank($row->subqy),
            'stemid' => $this->nullIfBlank($row->stemid),
            'csp' => $this->nullIfBlank($row->sp),
            default => null,
        };
    }

    private function normalizeBigTableStemid(string $stemid): string
    {
        $stemid = trim($stemid);

        if ($stemid === '') {
            return $stemid;
        }

        if (str_contains($stemid, '-')) {
            return str_replace('-', '.', $stemid);
        }

        return $stemid . '.0';
    }

    private function backfillCensusRecordDbh(int $targetCensus, string $dbhCensus): void
    {
        $sourceCensusNumber = preg_replace('/\D+/', '', $dbhCensus);

        if ($sourceCensusNumber === '') {
            return;
        }

        $sourceModel = 'App\\Models\\FsTreeCensus' . $sourceCensusNumber;

        if (!class_exists($sourceModel)) {
            return;
        }

        $records = CensusRecord::query()
            ->where('census', $targetCensus)
            ->whereNotNull('stemid')
            ->get(['id', 'stemid', 'dbh']);

        if ($records->isEmpty()) {
            return;
        }

        $dbhs = $sourceModel::query()
            ->whereIn('stemid', $records->pluck('stemid')->all())
            ->get(['stemid', 'dbh'])
            ->mapWithKeys(function ($row) {
                return [(string) $row->stemid => $this->toNullableDbh($row->dbh)];
            })
            ->all();

        if (empty($dbhs)) {
            return;
        }

        foreach ($records as $record) {
            $stemid = (string) $record->stemid;

            if (!array_key_exists($stemid, $dbhs)) {
                continue;
            }

            $dbh = $dbhs[$stemid];

            if ($dbh === null) {
                continue;
            }

            if ((string) $record->dbh === (string) $dbh) {
                continue;
            }

            $record->update([
                'dbh' => $dbh,
            ]);
        }
    }
}
