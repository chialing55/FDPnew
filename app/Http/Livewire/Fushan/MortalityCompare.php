<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class MortalityCompare extends Component
{
    public $statusNote;
    public $comnote;

    private const IGNORED_COMPARE_COLUMNS = [
        'id',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public function mount(): void
    {
        $this->statusNote = $this->buildStatusNote();
    }

    public function compare(): void
    {
        if (!$this->recordTablesExist()) {
            $this->comnote = 'record1 或 record2 尚未建立，請先產生死亡率調查輸入表。';
            return;
        }

        if (!$this->recordTablesHaveData()) {
            $this->comnote = 'record1 或 record2 尚無資料，請先產生死亡率調查輸入表。';
            return;
        }

        $record1 = $this->loadRecordsByStemid('record1');
        $record2 = $this->loadRecordsByStemid('record2');
        $stemids = array_values(array_unique(array_merge(array_keys($record1), array_keys($record2))));
        sort($stemids, SORT_NATURAL);

        $compareColumns = $this->compareColumns();
        $notes = [];

        foreach ($stemids as $stemid) {
            if (!isset($record1[$stemid])) {
                $notes[] = $this->formatCompareNote($record2[$stemid], '第一次輸入缺資料');
                continue;
            }

            if (!isset($record2[$stemid])) {
                $notes[] = $this->formatCompareNote($record1[$stemid], '第二次輸入缺資料');
                continue;
            }

            foreach ($compareColumns as $column) {
                $value1 = $this->normalizeValue($record1[$stemid][$column] ?? null);
                $value2 = $this->normalizeValue($record2[$stemid][$column] ?? null);

                if ($value1 !== $value2) {
                    $notes[] = $this->formatCompareNote($record1[$stemid], $column . ' 資料不合');
                }
            }
        }

        $this->comnote = empty($notes)
            ? '資料皆相符，請聯絡資料管理員。'
            : implode('<br>', $notes) . '<br>';
    }

    private function buildStatusNote(): string
    {
        if (!$this->recordTablesExist()) {
            return 'record1 或 record2 尚未建立，請先產生死亡率調查輸入表。';
        }

        if (!$this->recordTablesHaveData()) {
            return 'record1 或 record2 尚無資料，請先產生死亡率調查輸入表。';
        }

        if ($this->hasPendingStatus('record1')) {
            return '第一次輸入尚未完成，請確認輸入完成後再進行比對。';
        }

        if ($this->hasPendingStatus('record2')) {
            return '第二次輸入尚未完成，請確認輸入完成後再進行比對。';
        }

        return '兩次輸入皆已完成，可進行比對。';
    }

    private function recordTablesExist(): bool
    {
        return Schema::connection('fs_mortality')->hasTable('record1')
            && Schema::connection('fs_mortality')->hasTable('record2');
    }

    private function recordTablesHaveData(): bool
    {
        return DB::connection('fs_mortality')->table('record1')->exists()
            && DB::connection('fs_mortality')->table('record2')->exists();
    }

    private function hasPendingStatus(string $table): bool
    {
        return DB::connection('fs_mortality')
            ->table($table)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '');
            })
            ->exists();
    }

    private function loadRecordsByStemid(string $table): array
    {
        $columns = Schema::connection('fs_mortality')->getColumnListing($table);
        $query = DB::connection('fs_mortality')->table($table);

        foreach (['map_sort', 'map', 'stemid'] as $column) {
            if (in_array($column, $columns, true)) {
                $query->orderBy($column);
            }
        }

        return $query
            ->get()
            ->map(fn ($row) => (array) $row)
            ->filter(fn ($row) => trim((string) ($row['stemid'] ?? '')) !== '')
            ->keyBy(fn ($row) => (string) $row['stemid'])
            ->all();
    }

    private function compareColumns(): array
    {
        $record1Columns = Schema::connection('fs_mortality')->getColumnListing('record1');
        $record2Columns = Schema::connection('fs_mortality')->getColumnListing('record2');
        $commonColumns = array_values(array_intersect($record1Columns, $record2Columns));

        return array_values(array_filter(
            $commonColumns,
            fn ($column) => !in_array($column, self::IGNORED_COMPARE_COLUMNS, true)
        ));
    }

    private function normalizeValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return rtrim(rtrim(sprintf('%.6F', (float) $value), '0'), '.');
        }

        $text = trim((string) $value);

        if ($this->looksLikeJson($text)) {
            $decoded = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($this->sortRecursive($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return $text;
    }

    private function looksLikeJson(string $value): bool
    {
        return str_starts_with($value, '{') || str_starts_with($value, '[');
    }

    private function sortRecursive($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->sortRecursive($item);
        }

        if (!array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    private function formatCompareNote(array $record, string $note): string
    {
        $map = trim((string) ($record['map'] ?? ''));
        $stemid = trim((string) ($record['stemid'] ?? ''));

        return '死亡率資料比對: map ' . e($map) . ' stemid ' . e($stemid) . ' ' . e($note);
    }

    public function render()
    {
        return view('livewire.fushan.mortality-compare');
    }
}
