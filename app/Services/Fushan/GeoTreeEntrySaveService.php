<?php

namespace App\Services\Fushan;

use App\Models\FsGeoTreeSurvey\Record1;
use App\Models\FsGeoTreeSurvey\Record2;
use App\Support\TreeEntry\TreeEntryProfileResolver;
use App\Support\TreeEntry\TreeEntryValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class GeoTreeEntrySaveService
{
    private const EDITABLE_FIELDS = ['date', 'status', 'code', 'dbh', 'pom', 'note', 'confirm'];

    private const IDENTITY_FIELDS = ['qx', 'qy', 'sqx', 'sqy', 'tag', 'branch', 'csp', 'stemid'];

    public function __construct(
        private readonly TreeEntryProfileResolver $profiles,
        private readonly TreeEntryValidator $validator,
        private readonly GeoTreeEntryRowLockResolver $rowLocks,
    ) {
    }

    public function save(
        string $entry,
        int $qx,
        int $qy,
        int $sqx,
        int $sqy,
        array $submittedRows,
        string $actor,
    ): array {
        if (!in_array($entry, ['1', '2'], true)) {
            return $this->failure('輸入次數不正確，請重新進入輸入頁。');
        }
        $storedRows = $this->query($entry)
            ->where(compact('qx', 'qy', 'sqx', 'sqy'))
            ->where('show', 1)
            ->orderBy('tag')->orderBy('branch')
            ->get()
            ->map(fn ($row) => $row->toArray())
            ->all();

        $structureErrors = $this->validateStructure($submittedRows, $storedRows);
        if ($structureErrors !== []) {
            return ['ok' => false, 'errors' => $structureErrors, 'rows' => $submittedRows];
        }

        $lockData = $this->rowLocks->resolve($storedRows);
        $previousByStemid = $lockData['previousByStemid'];
        $lockedStemids = $lockData['lockedStemids'];
        $rules = $this->profiles->validationRules('fushan_geo_trees');
        $result = $this->validator->validate(
            $submittedRows,
            $previousByStemid,
            $rules,
            'existing',
            $lockedStemids,
            true,
        );

        if ($result->fails()) {
            return ['ok' => false, 'errors' => $result->errors, 'rows' => $result->rows];
        }

        $locked = array_fill_keys($lockedStemids, true);
        $storedByStemid = collect($storedRows)->keyBy(fn (array $row) => (string) $row['stemid']);
        $changed = 0;

        DB::connection('fs_geo_tree_survey')->transaction(function () use (
            $entry,
            $result,
            $locked,
            $storedByStemid,
            $actor,
            &$changed,
        ): void {
            foreach ($result->rows as $row) {
                $stemid = (string) $row['stemid'];
                if (isset($locked[$stemid])) {
                    continue;
                }
                if (in_array((string) ($row['date'] ?? ''), ['', '0000-00-00'], true)) {
                    continue;
                }

                $updates = $this->updatesFor($row, $storedByStemid->get($stemid));
                if ($updates === []) {
                    continue;
                }

                $updates['updated_id'] = $actor;
                $updates['updated_at'] = now()->format('Y-m-d H:i:s');
                $this->query($entry)->where('stemid', $stemid)->update($updates);
                $changed++;
            }
        });

        return ['ok' => true, 'errors' => [], 'rows' => $result->rows, 'changed' => $changed];
    }

    private function validateStructure(array $submittedRows, array $storedRows): array
    {
        $errors = [];
        $expected = collect($storedRows)->keyBy(fn (array $row) => (string) $row['stemid']);
        $seen = [];

        foreach (array_values($submittedRows) as $index => $row) {
            $stemid = (string) ($row['stemid'] ?? '');
            if ($stemid === '' || !$expected->has($stemid)) {
                $errors[] = $this->error($index, 'stemid', 'scope', '資料不屬於目前的小樣區，請重新載入。');
                continue;
            }
            if (isset($seen[$stemid])) {
                $errors[] = $this->error($index, 'stemid', 'duplicate_row', "{$stemid} 重複出現在送出的資料中。");
                continue;
            }
            $seen[$stemid] = true;

            $stored = $expected->get($stemid);
            foreach (self::IDENTITY_FIELDS as $field) {
                if ((string) ($row[$field] ?? '') !== (string) ($stored[$field] ?? '')) {
                    $errors[] = $this->error($index, $field, 'identity_changed', "{$stemid} 的 {$field} 不可修改。");
                }
            }
        }

        if (count($seen) !== $expected->count()) {
            $errors[] = $this->error(0, 'stemid', 'missing_rows', '送出的資料筆數不完整，請重新載入後再輸入。');
        }

        return $errors;
    }

    private function updatesFor(array $row, array $stored): array
    {
        $updates = [];
        foreach (self::EDITABLE_FIELDS as $field) {
            $value = $row[$field] ?? '';
            if ($field === 'code') {
                $value = strtoupper((string) $value);
            } elseif (in_array($field, ['status', 'note', 'confirm'], true)) {
                $value = (string) $value;
            } elseif (in_array($field, ['dbh', 'pom'], true)) {
                $value = (float) $value;
            }

            if ((string) ($stored[$field] ?? '') !== (string) $value) {
                $updates[$field] = $value;
            }
        }

        return $updates;
    }

    private function query(string $entry): Builder
    {
        return $entry === '1' ? Record1::query() : Record2::query();
    }

    private function failure(string $message): array
    {
        return ['ok' => false, 'errors' => [$this->error(0, 'stemid', 'request', $message)], 'rows' => []];
    }

    private function error(int $row, string $field, string $code, string $message): array
    {
        return compact('row', 'field', 'code', 'message');
    }
}
