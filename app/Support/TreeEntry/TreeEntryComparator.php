<?php

namespace App\Support\TreeEntry;

final class TreeEntryComparator
{
    public function compare(array $firstRows, array $secondRows, array $columns, array $lockedStemids = []): array
    {
        $first = $this->index($firstRows);
        $second = $this->index($secondRows);
        $locked = array_fill_keys(array_map('strval', $lockedStemids), true);
        $stemids = array_values(array_unique(array_merge(array_keys($first), array_keys($second))));
        usort($stemids, fn (string $a, string $b) => $this->sortKey($first[$a] ?? $second[$a]) <=> $this->sortKey($first[$b] ?? $second[$b]));

        $differences = [];
        $eligible = 0;
        foreach ($stemids as $stemid) {
            if (isset($locked[$stemid])) {
                continue;
            }
            $eligible++;
            $row1 = $first[$stemid] ?? null;
            $row2 = $second[$stemid] ?? null;
            $reference = $row1 ?? $row2;

            if ($row1 === null || !$this->hasDate($row1['date'] ?? null)) {
                $differences[] = $this->difference($reference, '第一次輸入缺資料');
                if ($row2 === null || !$this->hasDate($row2['date'] ?? null)) {
                    $differences[] = $this->difference($reference, '第二次輸入缺資料');
                }
                continue;
            }
            if ($row2 === null || !$this->hasDate($row2['date'] ?? null)) {
                $differences[] = $this->difference($reference, '第二次輸入缺資料');
                continue;
            }

            foreach ($columns as $field => $label) {
                $value1 = $this->normalize($row1[$field] ?? null);
                $value2 = $this->normalize($row2[$field] ?? null);
                if ($value1 !== $value2) {
                    $differences[] = $this->difference($reference, "{$label} 資料不合", $label, $value1, $value2);
                }
            }
        }

        return ['eligible' => $eligible, 'differences' => $differences];
    }

    private function index(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $stemid = trim((string) ($row['stemid'] ?? ''));
            if ($stemid !== '' && (int) ($row['show'] ?? 1) === 1) {
                $indexed[$stemid] = $row;
            }
        }
        return $indexed;
    }

    private function hasDate($value): bool
    {
        return !in_array(trim((string) $value), ['', '0000-00-00'], true);
    }

    private function normalize($value): string
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
        if ((str_starts_with($text, '{') || str_starts_with($text, '['))) {
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($this->sortRecursive($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
        return $text;
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

    private function difference(array $row, string $message, string $field = '', string $first = '', string $second = ''): array
    {
        return [
            'qx' => (string) ($row['qx'] ?? ''), 'qy' => (string) ($row['qy'] ?? ''),
            'sqx' => (string) ($row['sqx'] ?? ''), 'sqy' => (string) ($row['sqy'] ?? ''),
            'stemid' => (string) ($row['stemid'] ?? ''), 'field' => $field,
            'first' => $first, 'second' => $second, 'message' => $message,
        ];
    }

    private function sortKey(array $row): array
    {
        return [(int) ($row['qx'] ?? 0), (int) ($row['qy'] ?? 0), (int) ($row['sqx'] ?? 0), (int) ($row['sqy'] ?? 0), (string) ($row['stemid'] ?? '')];
    }
}
