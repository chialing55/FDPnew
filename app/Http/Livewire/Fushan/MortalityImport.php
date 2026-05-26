<?php

namespace App\Http\Livewire\Fushan;

use App\Models\FsMortality\Census;
use App\Models\FsMortality\CensusRecord;
use App\Models\FsMortality\CensusRecordComment;
use App\Models\FsMortality\CommentOption;
use App\Models\FsMortality\Record1;
use App\Models\FsMortality\StemCorrection;
use App\Models\FsMortality\TreeIndividual;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class MortalityImport extends Component
{
    public $user;
    public $site;
    public $importNote = '';
    public $record1Census;
    public $record1Count = 0;
    public $existingRecordCount = 0;
    public $canImport = false;

    public function mount($user = null, $site = null): void
    {
        $this->user = $user;
        $this->site = $site;
        $this->refreshImportState();
    }

    public function importRecord1(): void
    {
        $this->refreshImportState();

        if (!$this->canImport || $this->record1Census === null) {
            return;
        }

        $census = (int) $this->record1Census;
        $censusConfig = Census::query()->where('census', $census)->first();

        if (!$censusConfig) {
            $this->importNote = "找不到 census {$census} 對應的 censuses 設定，未匯入。";
            $this->canImport = false;
            return;
        }

        $rows = Record1::query()
            ->where('census', $census)
            ->whereNotNull('stemid')
            ->where('stemid', '!=', '')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            $this->importNote = 'record1 目前沒有可匯入資料。';
            $this->canImport = false;
            return;
        }

        $userName = trim((string) $this->user);
        $commentOptionIds = CommentOption::query()->pluck('id', 'id')->all();
        $commentOptionIdsByCode = CommentOption::query()->pluck('id', 'code')->all();
        $treeIndividualStemidMap = array_fill_keys(TreeIndividual::query()->pluck('stemid')->all(), true);
        $createdCount = 0;
        $updatedCount = 0;
        $commentCount = 0;
        $stemCorrectionCount = 0;
        $skippedMissingTreeStemids = [];

        DB::connection('fs_mortality')->transaction(function () use (
            $rows,
            $census,
            $censusConfig,
            $userName,
            $commentOptionIds,
            $commentOptionIdsByCode,
            $treeIndividualStemidMap,
            &$createdCount,
            &$updatedCount,
            &$commentCount,
            &$stemCorrectionCount,
            &$skippedMissingTreeStemids
        ) {
            foreach ($rows as $row) {
                $normalizedStemid = trim((string) $row->stemid);

                if (!isset($treeIndividualStemidMap[$normalizedStemid])) {
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

                foreach ($this->asArray($row->comments_json) as $index => $item) {
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

                foreach ($this->asArray($row->stem_corrections_json) as $item) {
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

            if ((int) $censusConfig->has_dbh === 0) {
                $dbhCensus = $this->nullIfBlank($censusConfig->dbh_census);

                if ($dbhCensus !== null) {
                    $this->backfillCensusRecordDbh($census, $dbhCensus);
                }
            }
        });

        $backupTable = $this->backupRecord2AndClearWorkTables((string) $censusConfig->survey_year);

        $skippedMissingTreeStemids = array_keys($skippedMissingTreeStemids);
        $skippedNote = empty($skippedMissingTreeStemids)
            ? ''
            : '；略過 tree_individuals 不存在的 stemid ' . count($skippedMissingTreeStemids) . ' 筆：' . implode('、', array_slice($skippedMissingTreeStemids, 0, 20));

        $this->importNote = "已完成匯入 census {$census}：新增 {$createdCount} 筆、更新 {$updatedCount} 筆、備註 {$commentCount} 筆、修正紀錄 {$stemCorrectionCount} 筆；已備份 record2 為 {$backupTable}，並清空 record1 / record2{$skippedNote}。";
        $this->refreshImportState(false);
    }

    private function refreshImportState(bool $resetNote = true): void
    {
        $this->record1Count = Record1::query()->count();
        $this->record1Census = null;
        $this->existingRecordCount = 0;
        $this->canImport = false;

        if ($this->record1Count === 0) {
            if ($resetNote) {
                $this->importNote = 'record1 目前沒有可匯入資料。';
            }
            return;
        }

        $censusValues = Record1::query()
            ->select('census')
            ->distinct()
            ->orderBy('census')
            ->pluck('census')
            ->map(fn ($value) => (int) $value)
            ->all();

        if (count($censusValues) !== 1) {
            if ($resetNote) {
                $this->importNote = 'record1 內有多個 census：' . implode('、', $censusValues) . '，請先確認資料。';
            }
            return;
        }

        $this->record1Census = $censusValues[0];
        $this->existingRecordCount = CensusRecord::query()
            ->where('census', $this->record1Census)
            ->count();

        $pendingStatus = Record1::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '');
            })
            ->exists();

        if ($pendingStatus) {
            if ($resetNote) {
                $this->importNote = 'record1 尚有 status 空白資料，請確認第一次輸入完成後再匯入。';
            }
            return;
        }

        $missingTeam = Record1::query()
            ->where(function ($query) {
                $query->whereNull('team_id')
                    ->orWhere('team_id', '');
            })
            ->exists();

        if ($missingTeam) {
            if ($resetNote) {
                $this->importNote = 'record1 尚有未選擇調查團隊的資料，請確認後再匯入。';
            }
            return;
        }

        $censusConfig = Census::query()->where('census', $this->record1Census)->first();

        if (!$censusConfig) {
            if ($resetNote) {
                $this->importNote = "找不到 census {$this->record1Census} 對應的 censuses 設定。";
            }
            return;
        }

        $recordYears = Record1::query()
            ->whereNotNull('date')
            ->pluck('date')
            ->map(fn ($date) => $this->extractYear($date))
            ->filter(fn ($year) => $year !== null)
            ->unique()
            ->values()
            ->all();
        $expectedSurveyYear = trim((string) $censusConfig->survey_year);

        if (empty($recordYears)) {
            if ($resetNote) {
                $this->importNote = 'record1.date 沒有可比對的年份，未匯入。';
            }
            return;
        }

        if (count($recordYears) !== 1 || (string) $recordYears[0] !== $expectedSurveyYear) {
            if ($resetNote) {
                $this->importNote = 'record1.date 年份（' . implode('、', $recordYears) . "）與 census {$this->record1Census} 對應的 survey_year（{$expectedSurveyYear}）不符。";
            }
            return;
        }

        $this->canImport = true;

        if ($resetNote) {
            $existingNote = $this->existingRecordCount > 0
                ? "正式表已有 {$this->existingRecordCount} 筆 census {$this->record1Census} 資料，匯入時會更新既有資料並重建其備註與修正紀錄。"
                : "正式表尚無 census {$this->record1Census} 資料，匯入時會新增資料。";
            $this->importNote = "record1 共有 {$this->record1Count} 筆 census {$this->record1Census} 資料，可匯入大表。{$existingNote}";
        }
    }

    private function backupRecord2AndClearWorkTables(string $surveyYear): string
    {
        $year = preg_replace('/\D+/', '', $surveyYear);
        $year = $year !== '' ? $year : now()->format('Y');
        $backupTable = 'record_' . $year;

        if (!Schema::connection('fs_mortality')->hasTable('record2')) {
            return $backupTable;
        }

        if (!Schema::connection('fs_mortality')->hasTable($backupTable)) {
            DB::connection('fs_mortality')->statement('CREATE TABLE `' . $backupTable . '` LIKE `record2`');
        }

        $this->truncateTable($backupTable);
        $this->insertTableFromTable($backupTable, 'record2');

        foreach (['record1', 'record2'] as $table) {
            if (Schema::connection('fs_mortality')->hasTable($table)) {
                $this->truncateTable($table);
            }
        }

        return $backupTable;
    }

    private function truncateTable(string $table): void
    {
        DB::connection('fs_mortality')->statement('TRUNCATE TABLE `' . str_replace('`', '``', $table) . '`');
    }

    private function insertTableFromTable(string $targetTable, string $sourceTable): void
    {
        $targetColumns = Schema::connection('fs_mortality')->getColumnListing($targetTable);
        $sourceColumns = Schema::connection('fs_mortality')->getColumnListing($sourceTable);
        $sourceColumnMap = array_flip($sourceColumns);

        $insertColumns = [];
        $selectColumns = [];

        foreach ($targetColumns as $column) {
            $quotedColumn = '`' . str_replace('`', '``', $column) . '`';
            $insertColumns[] = $quotedColumn;
            $selectColumns[] = isset($sourceColumnMap[$column])
                ? '`' . str_replace('`', '``', $sourceTable) . '`.' . $quotedColumn
                : 'NULL';
        }

        DB::connection('fs_mortality')->statement(
            'INSERT INTO `' . str_replace('`', '``', $targetTable) . '` (' . implode(', ', $insertColumns) . ') ' .
            'SELECT ' . implode(', ', $selectColumns) . ' FROM `' . str_replace('`', '``', $sourceTable) . '`'
        );
    }

    private function asArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function extractYear($date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y');
        }

        $text = trim((string) $date);

        return preg_match('/(19|20)\d{2}/', $text, $matches) ? $matches[0] : null;
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

    private function resolveStemCorrectionOldValue($row, string $fieldName): ?string
    {
        return match ($fieldName) {
            'qx' => $this->nullIfBlank($row->qx),
            'qy' => $this->nullIfBlank($row->qy),
            'subqx' => $this->nullIfBlank($row->subqx),
            'subqy' => $this->nullIfBlank($row->subqy),
            'stemid' => $this->nullIfBlank($row->stemid),
            'csp' => $this->nullIfBlank($row->csp),
            default => null,
        };
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
            ->mapWithKeys(fn ($row) => [(string) $row->stemid => $this->toNullableDbh($row->dbh)])
            ->all();

        if (empty($dbhs)) {
            return;
        }

        foreach ($records as $record) {
            $stemid = (string) $record->stemid;

            if (!array_key_exists($stemid, $dbhs) || $dbhs[$stemid] === null) {
                continue;
            }

            if ((string) $record->dbh === (string) $dbhs[$stemid]) {
                continue;
            }

            $record->update(['dbh' => $dbhs[$stemid]]);
        }
    }

    public function render()
    {
        return view('livewire.fushan.mortality-import');
    }
}
