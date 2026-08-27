<?php

namespace App\Services\Web;

use App\Models\Web\Publication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use League\Csv\Reader;
use RuntimeException;

class PublicationCsvImporter
{
    private const IMPORTABLE_COLUMNS = [
        'external_id',
        'authors',
        'authors_zh_tw',
        'title',
        'title_zh_tw',
        'journal',
        'journal_zh_tw',
        'year',
        'type',
        'language',
        'institution',
        'institution_zh_tw',
        'thesis_type',
        'volume',
        'issue',
        'pages',
        'doi',
        'url',
        'pdf_path',
        'is_open_access',
        'is_active',
    ];

    /** @return array{created: int, updated: int, skipped: int, skipped_rows: array<int, string>} */
    public function import(string $path, ?int $siteId = null): array
    {
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $headers = array_map(
            fn (string $header): string => $this->normalizeHeader($header),
            $csv->getHeader(),
        );

        $this->validateHeaders($headers);

        $created = 0;
        $updated = 0;
        $nonBlankRows = 0;
        $skippedRows = [];

        DB::connection('mysql_web')->transaction(function () use ($csv, $headers, $siteId, &$created, &$updated, &$nonBlankRows, &$skippedRows): void {
            foreach ($csv->getRecords() as $offset => $record) {
                $line = $offset + 1;
                $rawRow = array_combine($headers, array_values($record));

                if ($this->isBlankRow($rawRow)) {
                    continue;
                }

                $nonBlankRows++;

                try {
                    $row = $this->normalizeRow($rawRow);
                    $publication = $this->resolvePublication($row);
                    $isNew = ! $publication->exists;

                    $this->validateRow($row, $line, $isNew);
                    $publication->fill($row);
                    $publication->save();

                    if ($siteId !== null) {
                        $publication->sites()->syncWithoutDetaching([$siteId]);
                    }

                    $isNew ? $created++ : $updated++;
                } catch (RuntimeException $exception) {
                    $skippedRows[] = "第 {$line} 列：{$exception->getMessage()}";
                }
            }
        });

        if ($nonBlankRows === 0) {
            throw new RuntimeException('CSV 沒有可匯入的資料列。');
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => count($skippedRows),
            'skipped_rows' => $skippedRows,
        ];
    }

    private function normalizeHeader(string $header): string
    {
        return trim(preg_replace('/^\xEF\xBB\xBF/', '', $header), " \t\n\r\0\x0B\"'");
    }

    private function validateHeaders(array $headers): void
    {
        if ($headers === []) {
            throw new RuntimeException('CSV 沒有表頭列。');
        }

        $importableHeaders = array_values(array_intersect($headers, self::IMPORTABLE_COLUMNS));

        if ($importableHeaders === []) {
            throw new RuntimeException('CSV 沒有可匯入的欄位。');
        }

        if (count($importableHeaders) !== count(array_unique($importableHeaders))) {
            throw new RuntimeException('CSV 表頭有重複的匯入欄位。');
        }
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $column => $value) {
            if (! in_array($column, self::IMPORTABLE_COLUMNS, true)) {
                continue;
            }

            $value = is_string($value) ? trim($value) : $value;
            $normalized[$column] = $value === '' ? null : $value;
        }

        foreach (['is_open_access', 'is_active'] as $column) {
            if (array_key_exists($column, $normalized) && $normalized[$column] !== null) {
                $normalized[$column] = $this->booleanValue($normalized[$column], $column);
            }
        }

        if (array_key_exists('language', $normalized) && $normalized['language'] === null) {
            unset($normalized['language']);
        } elseif (filled($normalized['language'] ?? null)) {
            $normalized['language'] = match (strtolower((string) $normalized['language'])) {
                'en', 'english' => 'en',
                'zh', 'zh-tw', 'zh_tw', 'chinese', '中文' => 'zh',
                default => $normalized['language'],
            };
        }

        if (filled($normalized['thesis_type'] ?? null)) {
            $normalized['thesis_type'] = match (strtolower((string) $normalized['thesis_type'])) {
                'master', "master's thesis", 'masters thesis', '碩士', '碩士論文' => 'master',
                'doctoral', 'doctoral dissertation', 'phd', 'ph.d.', '博士', '博士論文' => 'doctoral',
                default => $normalized['thesis_type'],
            };
        }

        if (filled($normalized['doi'] ?? null)) {
            $normalized['doi'] = preg_replace('#^https?://(?:dx\.)?doi\.org/#i', '', $normalized['doi']);
        }

        return $normalized;
    }

    private function booleanValue(mixed $value, string $column): bool
    {
        return match (strtolower((string) $value)) {
            '1', 'true', 'yes', 'y', '是' => true,
            '0', 'false', 'no', 'n', '否' => false,
            default => throw new RuntimeException("{$column} 僅接受 1/0、true/false、yes/no 或是/否。"),
        };
    }

    private function resolvePublication(array $row): Publication
    {
        if (filled($row['external_id'] ?? null)) {
            return Publication::firstOrNew(['external_id' => $row['external_id']]);
        }

        if (filled($row['doi'] ?? null)) {
            return Publication::firstOrNew(['doi' => $row['doi']]);
        }

        if (filled($row['year'] ?? null) && filled($row['title'] ?? null)) {
            $publication = $this->findUniqueByFields([
                'year' => $row['year'],
                'title' => $row['title'],
            ]);

            if ($publication !== null) {
                return $publication;
            }
        }

        if (($row['type'] ?? null) === 'thesis') {
            $authorField = filled($row['authors_zh_tw'] ?? null) ? 'authors_zh_tw' : 'authors';
            $institutionField = filled($row['institution_zh_tw'] ?? null) ? 'institution_zh_tw' : 'institution';

            if (
                filled($row['year'] ?? null)
                && filled($row[$authorField] ?? null)
                && filled($row[$institutionField] ?? null)
            ) {
                $publication = $this->findUniqueByFields([
                    'year' => $row['year'],
                    $authorField => $row[$authorField],
                    $institutionField => $row[$institutionField],
                ]);

                if ($publication !== null) {
                    return $publication;
                }
            }
        } elseif (
            filled($row['year'] ?? null)
            && filled($row['journal'] ?? null)
            && filled($row['volume'] ?? null)
            && filled($row['pages'] ?? null)
        ) {
            $publication = $this->findUniqueByFields([
                'year' => $row['year'],
                'journal' => $row['journal'],
                'volume' => $row['volume'],
                'pages' => $row['pages'],
            ]);

            if ($publication !== null) {
                return $publication;
            }
        }

        return new Publication;
    }

    private function findUniqueByFields(array $fields): ?Publication
    {
        $matches = Publication::query()
            ->where(function ($query) use ($fields): void {
                foreach ($fields as $field => $value) {
                    $query->where($field, $value);
                }
            })
            ->limit(2)
            ->get();

        if ($matches->count() > 1) {
            throw new RuntimeException(
                '複合欄位比對到多筆既有文獻（'.implode('、', array_keys($fields)).'），請補上 external_id 或 DOI。'
            );
        }

        return $matches->first();
    }

    private function validateRow(array $row, int $line, bool $isNew): void
    {
        $rules = [
            'external_id' => ['nullable', 'string', 'max:255'],
            'authors' => [$isNew ? 'required' : 'nullable', 'string'],
            'authors_zh_tw' => ['nullable', 'string'],
            'title' => [$isNew ? 'required' : 'nullable', 'string', 'max:500'],
            'title_zh_tw' => ['nullable', 'string', 'max:500'],
            'journal' => ['nullable', 'string', 'max:255'],
            'journal_zh_tw' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:1000,9999'],
            'type' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:10'],
            'institution' => ['nullable', 'string', 'max:255'],
            'institution_zh_tw' => ['nullable', 'string', 'max:255'],
            'thesis_type' => ['nullable', 'in:master,doctoral'],
            'volume' => ['nullable', 'string', 'max:50'],
            'issue' => ['nullable', 'string', 'max:50'],
            'pages' => ['nullable', 'string', 'max:100'],
            'doi' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
            'pdf_path' => ['nullable', 'string', 'max:255'],
            'is_open_access' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];

        try {
            Validator::make($row, array_intersect_key($rules, $row))->validate();
        } catch (ValidationException $exception) {
            throw new RuntimeException(
                "第 {$line} 列資料錯誤：".collect($exception->errors())->flatten()->implode(' '),
                previous: $exception,
            );
        }

        if ($isNew && (! array_key_exists('authors', $row) || ! array_key_exists('title', $row))) {
            throw new RuntimeException("第 {$line} 列為新資料，CSV 必須包含 authors 與 title 欄位。");
        }
    }

    private function isBlankRow(array $row): bool
    {
        return collect($row)->every(fn (mixed $value): bool => blank($value));
    }
}
