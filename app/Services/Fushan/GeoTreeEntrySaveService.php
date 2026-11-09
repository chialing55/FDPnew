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
    ) {}

    public function save(
        string $entry,
        int $qx,
        int $qy,
        int $sqx,
        int $sqy,
        array $submittedRows,
        string $actor,
    ): array {
        if (! in_array($entry, ['1', '2'], true)) {
            return $this->failure('輸入次數不正確，請重新進入輸入頁。');
        }
        $storedRows = $this->query($entry)
            ->where(compact('qx', 'qy', 'sqx', 'sqy'))
            ->where('show', 1)
            ->orderBy('tag')->orderBy('branch')
            ->get()
            ->map(fn ($row) => $row->toArray())
            ->all();

        $lockData = $this->rowLocks->resolve($storedRows);
        $previousByStemid = $lockData['previousByStemid'];
        $lockedStemids = $lockData['lockedStemids'];
        $rules = $this->profiles->validationRules('fushan_geo_trees');
        $locked = array_fill_keys($lockedStemids, true);
        $storedByStemid = collect($storedRows)->keyBy(fn (array $row) => (string) $row['stemid']);
        $rows = array_values($submittedRows);
        $errors = [];
        $changed = 0;
        $skipped = 0;
        $seen = [];

        foreach ($rows as $index => $submittedRow) {
            if (in_array((string) ($submittedRow['date'] ?? ''), ['', '0000-00-00'], true)) {
                $skipped++;

                continue;
            }

            $stemid = (string) ($submittedRow['stemid'] ?? '');
            $identityErrors = $this->validateIdentity($submittedRow, $storedByStemid, $seen, $index);
            if ($identityErrors !== []) {
                array_push($errors, ...$identityErrors);

                continue;
            }
            $seen[$stemid] = true;

            if (isset($locked[$stemid])) {
                $skipped++;

                continue;
            }

            $result = $this->validator->validate(
                [$submittedRow],
                $previousByStemid,
                $rules,
                'existing',
                $lockedStemids,
                true,
            );
            if ($result->fails()) {
                foreach ($result->errors as $error) {
                    $error['row'] = $index;
                    $errors[] = $error;
                }
                $rows[$index] = $result->rows[0] ?? $submittedRow;

                continue;
            }

            $row = $result->rows[0];
            $rows[$index] = $row;

            try {
                $rowChanged = DB::connection('fs_geo_tree_survey')->transaction(function () use ($entry, $row, $storedByStemid, $actor, $stemid): int {
                    $updates = $this->updatesFor($row, $storedByStemid->get($stemid));
                    if ($updates === []) {
                        return 0;
                    }

                    $updates['updated_id'] = $actor;
                    $updates['updated_at'] = now()->format('Y-m-d H:i:s');
                    $this->query($entry)->where('stemid', $stemid)->update($updates);

                    return 1;
                });
                $changed += $rowChanged;
            } catch (\Throwable $exception) {
                report($exception);
                $errors[] = $this->error($index, 'date', 'save_failed', "{$stemid} 儲存失敗，資料已保留，請稍後再試。");
            }
        }

        return [
            'ok' => $errors === [],
            'status' => $errors === [] ? ($changed > 0 ? 'success' : 'noop') : ($changed > 0 ? 'partial' : 'error'),
            'errors' => $errors,
            'rows' => $rows,
            'changed' => $changed,
            'skipped' => $skipped,
            'failed' => collect($errors)->pluck('row')->unique()->count(),
            'counts' => [
                'saved' => $changed,
                'skipped' => $skipped,
                'failed' => collect($errors)->pluck('row')->unique()->count(),
            ],
        ];
    }

    private function validateIdentity(array $row, $storedByStemid, array $seen, int $index): array
    {
        $errors = [];
        $stemid = (string) ($row['stemid'] ?? '');
        if ($stemid === '' || ! $storedByStemid->has($stemid)) {
            return [$this->error($index, 'stemid', 'scope', '資料不屬於目前的小樣區，請重新載入。')];
        }
        if (isset($seen[$stemid])) {
            return [$this->error($index, 'stemid', 'duplicate_row', "{$stemid} 重複出現在送出的資料中。")];
        }

        $stored = $storedByStemid->get($stemid);
        foreach (self::IDENTITY_FIELDS as $field) {
            if ((string) ($row[$field] ?? '') !== (string) ($stored[$field] ?? '')) {
                $errors[] = $this->error($index, $field, 'identity_changed', "{$stemid} 的 {$field} 不可修改。");
            }
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
        return [
            'ok' => false,
            'status' => 'error',
            'errors' => [$this->error(0, 'stemid', 'request', $message)],
            'rows' => [],
            'changed' => 0,
            'skipped' => 0,
            'failed' => 1,
            'counts' => ['saved' => 0, 'skipped' => 0, 'failed' => 1],
        ];
    }

    private function error(int $row, string $field, string $code, string $message): array
    {
        return compact('row', 'field', 'code', 'message');
    }
}
