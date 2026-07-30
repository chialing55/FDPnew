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
        'volume',
        'issue',
        'pages',
        'doi',
        'url',
        'pdf_path',
        'is_open_access',
        'is_active',
    ];

    /** @return array{created: int, updated: int} */
    public function import(string $path): array
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

        DB::connection('mysql_web')->transaction(function () use ($csv, $headers, &$created, &$updated): void {
            foreach ($csv->getRecords() as $offset => $record) {
                $line = $offset + 1;
                $row = $this->normalizeRow(array_combine($headers, array_values($record)));

                if ($this->isBlankRow($row)) {
                    continue;
                }

                $publication = $this->resolvePublication($row);
                $isNew = ! $publication->exists;

                $this->validateRow($row, $line, $isNew);
                $publication->fill($row);
                $publication->save();

                $isNew ? $created++ : $updated++;
            }
        });

        if ($created + $updated === 0) {
            throw new RuntimeException('CSV 沒有可匯入的資料列。');
        }

        return compact('created', 'updated');
    }

    private function normalizeHeader(string $header): string
    {
        return trim(preg_replace('/^\xEF\xBB\xBF/', '', $header), " \t\n\r\0\x0B\"'");
    }

    private function validateHeaders(array $headers): void
    {
        if (count($headers) !== count(array_unique($headers))) {
            throw new RuntimeException('CSV 表頭有重複欄位。');
        }

        $unknown = array_values(array_diff($headers, self::IMPORTABLE_COLUMNS));

        if ($unknown !== []) {
            throw new RuntimeException('不支援的欄位：'.implode(', ', $unknown));
        }

        if ($headers === []) {
            throw new RuntimeException('CSV 沒有表頭列。');
        }
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $column => $value) {
            $value = is_string($value) ? trim($value) : $value;
            $normalized[$column] = $value === '' ? null : $value;
        }

        foreach (['is_open_access', 'is_active'] as $column) {
            if (array_key_exists($column, $normalized) && $normalized[$column] !== null) {
                $normalized[$column] = $this->booleanValue($normalized[$column], $column);
            }
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

        return new Publication;
    }

    private function validateRow(array $row, int $line, bool $isNew): void
    {
        $rules = [
            'external_id' => ['nullable', 'string', 'max:255'],
            'authors' => [$isNew ? 'required' : 'nullable', 'string', 'max:1000'],
            'authors_zh_tw' => ['nullable', 'string', 'max:1000'],
            'title' => [$isNew ? 'required' : 'nullable', 'string', 'max:500'],
            'title_zh_tw' => ['nullable', 'string', 'max:500'],
            'journal' => ['nullable', 'string', 'max:255'],
            'journal_zh_tw' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:1000,9999'],
            'type' => ['nullable', 'string', 'max:50'],
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
