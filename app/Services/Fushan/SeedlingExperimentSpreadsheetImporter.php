<?php

namespace App\Services\Fushan;

use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenSpout\Reader\Common\Creator\ReaderFactory;

class SeedlingExperimentSpreadsheetImporter
{
    private const CONNECTION = 'fs_seedling_exp';

    private const DATASETS = [
        'phase1_raw' => [
            'table' => 'groundcover_phase1_raw',
            'phase' => 1,
            'cleaned' => false,
            'required' => ['date', 'plot', 'no', 'species', 'length', 'leaf_count', 'status', 'note'],
        ],
        'phase1_cleaned' => [
            'table' => 'groundcover_phase1_cleaned',
            'raw_table' => 'groundcover_phase1_raw',
            'phase' => 1,
            'cleaned' => true,
            'required' => ['date', 'plot', 'no', 'species', 'length', 'leaf_count', 'status', 'note'],
        ],
        'phase2_raw' => [
            'table' => 'groundcover_phase2_raw',
            'phase' => 2,
            'cleaned' => false,
            'required' => ['survey_order', 'date', 'plot', 'density', 'no', 'species', 'length', 'cotyledon_count', 'leaf_count', 'status', 'note'],
        ],
        'phase2_cleaned' => [
            'table' => 'groundcover_phase2_cleaned',
            'raw_table' => 'groundcover_phase2_raw',
            'phase' => 2,
            'cleaned' => true,
            'required' => ['survey_order', 'date', 'plot', 'density', 'no', 'species', 'length', 'cotyledon_count', 'leaf_count', 'status', 'note'],
        ],
        'greenhouse_raw' => [
            'table' => 'greenhouse_raw',
            'kind' => 'greenhouse',
            'cleaned' => false,
            'required' => ['date', 'species', 'plot', 'no', 'length', 'leaf_count', 'status', 'note'],
        ],
        'greenhouse_order' => [
            'table' => 'greenhouse_survey_orders',
            'kind' => 'greenhouse_order',
            'cleaned' => false,
            'required' => ['survey_order', 'date', 'species', 'plot', 'no', 'length', 'leaf_count', 'status', 'note'],
        ],
    ];

    private const HEADERS = [
        '調查順序排序' => 'survey_order',
        '調查順序' => 'survey_order',
        '調查(輸入)順序' => 'survey_order',
        'survey_order' => 'survey_order',
        'date' => 'date',
        '日期' => 'date',
        'plot' => 'plot',
        '樣區' => 'plot',
        '處理' => 'plot',
        'treatment' => 'plot',
        '遮蔭處理排序' => 'shade',
        'density' => 'density',
        '密度' => 'density',
        'no.' => 'no',
        'no' => 'no',
        '編號' => 'no',
        'species' => 'species',
        'sp.' => 'species',
        '物種' => 'species',
        '長度' => 'length',
        '高度' => 'length',
        'length' => 'length',
        '子葉數' => 'cotyledon_count',
        'cotyledon_count' => 'cotyledon_count',
        '葉片數' => 'leaf_count',
        '葉片' => 'leaf_count',
        'leaf_count' => 'leaf_count',
        'status' => 'status',
        '狀態' => 'status',
        '備註' => 'note',
        '附註' => 'note',
        'note' => 'note',
    ];

    public static function counts(): array
    {
        return collect(self::DATASETS)->mapWithKeys(
            fn (array $config, string $key) => [$key => DB::connection(self::CONNECTION)->table($config['table'])->count()]
        )->all();
    }

    public function import(string $dataset, UploadedFile $file): string
    {
        $config = self::DATASETS[$dataset] ?? null;

        if (! $config) {
            throw ValidationException::withMessages(['spreadsheet' => '未知的資料類型。']);
        }

        if (in_array($dataset, ['phase1_cleaned', 'phase2_cleaned'], true)) {
            throw ValidationException::withMessages(['spreadsheet' => '修改後資料由系統內部產生，不需另外上傳。']);
        }

        $rows = $this->readRows($file, $config);

        if ($rows === []) {
            throw ValidationException::withMessages(['spreadsheet' => '檔案中沒有可匯入的資料列。']);
        }

        $result = $config['cleaned']
            ? $this->importCleaned($config, $rows, $file->getClientOriginalName())
            : $this->importRaw($config, $rows, $file->getClientOriginalName());

        $labels = [
            'phase1_raw' => '地被實驗第一期原始資料',
            'phase1_cleaned' => '地被實驗第一期修改後資料',
            'phase2_raw' => '地被實驗第二期原始資料',
            'phase2_cleaned' => '地被實驗第二期修改後資料',
            'greenhouse_raw' => '溫室實驗原始資料',
            'greenhouse_order' => '溫室實驗調查順序參考資料',
        ];

        if ($dataset === 'greenhouse_order') {
            return "溫室調查順序參考檔上傳完成：{$result['count']} 筆。請接著上傳完整歷史資料。";
        }

        if ($dataset === 'phase1_raw') {
            $cleanedCount = DB::connection(self::CONNECTION)->table('groundcover_phase1_cleaned')->count();
            $stats = $result['status_fixes'];
            $formal = app(Phase1FormalDataImporter::class)->importRemaining();

            if ($formal['stopped'] !== null) {
                throw ValidationException::withMessages([
                    'spreadsheet' => '原始資料上傳及修改後資料整理已完成，但正式資料匯入停在 '
                        .$formal['stopped'].'：'.implode('；', array_slice($formal['errors'], 0, 5)),
                ]);
            }

            $batchCount = count($formal['imported']);
            $recordCount = collect($formal['imported'])->sum('createdRecords');
            $individualCount = collect($formal['imported'])->sum('createdIndividuals');

            return "原始資料上傳完成：{$result['count']} 筆。修改後資料產生及整理完成：{$cleanedCount} 筆，共處理 {$stats['total']} 筆異動（移除重複 {$stats['duplicate_rows_deleted']} 筆）。正式資料匯入完成：{$batchCount} 批，新增個體 {$individualCount} 筆、調查紀錄 {$recordCount} 筆。";
        }

        if ($dataset === 'phase2_raw') {
            $cleanedCount = DB::connection(self::CONNECTION)->table('groundcover_phase2_cleaned')->count();
            $stats = $result['status_fixes'];
            $correctedCount = collect($stats)->except(['rows_changed'])->sum();
            $formal = app(Phase2FormalDataImporter::class)->importRemaining();

            if ($formal['stopped'] !== null) {
                throw ValidationException::withMessages([
                    'spreadsheet' => '原始資料上傳及修改後資料整理已完成，但正式資料匯入停在 '
                        .$formal['stopped'].'：'.implode('；', array_slice($formal['errors'], 0, 5)),
                ]);
            }

            $batchCount = count($formal['imported']);
            $plotCount = collect($formal['imported'])->sum('createdPlots');
            $recordCount = collect($formal['imported'])->sum('createdRecords');
            $individualCount = collect($formal['imported'])->sum('createdIndividuals');
            $backfilledCount = collect($formal['imported'])->sum('backfilledRecords');

            return "原始資料上傳完成：{$result['count']} 筆。修改後資料產生及整理完成：{$cleanedCount} 筆，共執行 {$correctedCount} 項修正（移除 {$stats['known_rows_deleted']} 筆、補登 {$stats['known_rows_inserted']} 筆）。正式資料匯入完成：{$batchCount} 批，新增樣區 {$plotCount} 個、個體 {$individualCount} 株、調查紀錄 {$recordCount} 筆（補登 {$backfilledCount} 筆）。";
        }

        if ($dataset === 'greenhouse_raw') {
            $cleanedCount = DB::connection(self::CONNECTION)->table('greenhouse_cleaned')->count();
            $formal = app(GreenhouseFormalDataImporter::class)->importRemaining();
            if ($formal['stopped'] !== null) {
                throw ValidationException::withMessages([
                    'spreadsheet' => '溫室原始資料上傳及整理完成，但正式資料匯入停在 '
                        .$formal['stopped'].'：'.implode('；', array_slice($formal['errors'], 0, 5)),
                ]);
            }

            $batchCount = count($formal['imported']);
            $plotCount = collect($formal['imported'])->sum('createdPlots');
            $individualCount = collect($formal['imported'])->sum('createdIndividuals');
            $recordCount = collect($formal['imported'])->sum('createdRecords');

            return "溫室原始資料上傳完成：{$result['count']} 筆。整理完成：{$cleanedCount} 筆。正式資料匯入完成：{$batchCount} 批，新增處理 {$plotCount} 個、個體 {$individualCount} 株、調查紀錄 {$recordCount} 筆。";
        }

        $count = $config['cleaned'] ? $result : $result['count'];

        return "{$labels[$dataset]}匯入完成，共 {$count} 筆。";
    }

    private function readRows(UploadedFile $file, array $config): array
    {
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        $readPath = $path.'.'.$extension;
        copy($path, $readPath);
        $this->normalizeCsvEncoding($readPath, $extension);
        $reader = ReaderFactory::createFromFile($readPath);
        $rows = [];

        try {
            $reader->open($readPath);

            foreach ($reader->getSheetIterator() as $sheet) {
                $headers = null;
                $rowNumber = 0;

                foreach ($sheet->getRowIterator() as $row) {
                    $rowNumber++;
                    $values = $row->toArray();

                    if ($headers === null) {
                        if ($this->rowIsEmpty($values)) {
                            continue;
                        }
                        $headers = $this->mapHeaders($values, $config['required'], $sheet->getName());

                        continue;
                    }

                    if ($this->rowIsEmpty($values)) {
                        continue;
                    }

                    $mapped = ['_sheet' => $sheet->getName(), '_row' => $rowNumber];
                    foreach ($headers as $index => $column) {
                        if ($column !== null) {
                            $mapped[$column] = $this->cellValue($values[$index] ?? null);
                        }
                    }
                    $rows[] = $mapped;
                }
            }
        } finally {
            $reader->close();
            @unlink($readPath);
        }

        return $rows;
    }

    private function normalizeCsvEncoding(string $path, string $extension): void
    {
        if ($extension !== 'csv') {
            return;
        }

        $contents = file_get_contents($path);
        if ($contents === false || $contents === '') {
            return;
        }

        $encoding = mb_detect_encoding($contents, ['UTF-8', 'CP950', 'BIG-5'], true);
        if ($encoding === false) {
            throw ValidationException::withMessages([
                'spreadsheet' => '無法辨識 CSV 文字編碼，請另存為 UTF-8、Big5 或 CP950 後再上傳。',
            ]);
        }

        if ($encoding !== 'UTF-8') {
            file_put_contents($path, mb_convert_encoding($contents, 'UTF-8', $encoding));
        }
    }

    private function mapHeaders(array $values, array $required, string $sheet): array
    {
        $mapped = [];
        foreach ($values as $value) {
            $header = mb_strtolower(trim((string) $value));
            $mapped[] = self::HEADERS[$header] ?? null;
        }

        $missing = array_values(array_diff($required, array_filter($mapped)));
        if ($missing !== []) {
            throw ValidationException::withMessages([
                'spreadsheet' => "工作表「{$sheet}」缺少必要欄位：".implode('、', $missing),
            ]);
        }

        return $mapped;
    }

    private function importRaw(array $config, array $rows, string $filename): array
    {
        $connection = DB::connection(self::CONNECTION);
        if (($config['kind'] ?? null) !== 'greenhouse_order'
            && $connection->table($config['table'])->where('source_file', $filename)->exists()) {
            throw ValidationException::withMessages(['spreadsheet' => "檔案 {$filename} 已上傳過；如需重傳，請先確認並移除舊資料。"]);
        }

        $now = now();
        $payload = array_map(function (array $row) use ($config, $filename, $now): array {
            if (($config['kind'] ?? null) === 'greenhouse') {
                return [
                    'survey_order' => $row['survey_order'] ?? null, 'shade' => $row['shade'] ?? null,
                    'date' => $row['date'], 'species' => $row['species'], 'plot' => $row['plot'],
                    'no' => $row['no'], 'length' => $row['length'], 'leaf_count' => $row['leaf_count'],
                    'status' => $row['status'], 'note' => $row['note'],
                    'source_file' => $filename, 'source_sheet' => $row['_sheet'], 'source_row' => $row['_row'],
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }

            if (($config['kind'] ?? null) === 'greenhouse_order') {
                $plot = strtoupper(trim((string) ($row['plot'] ?? '')));

                return [
                    'survey_order' => $row['survey_order'],
                    'shade' => strtoupper(trim((string) ($row['shade'] ?? substr($plot, -1)))),
                    'date' => $row['date'], 'species' => $row['species'], 'plot' => $plot,
                    'no' => strtoupper(trim((string) $row['no'])), 'length' => $row['length'],
                    'leaf_count' => $row['leaf_count'], 'status' => $row['status'], 'note' => $row['note'],
                    'source_file' => $filename, 'source_sheet' => $row['_sheet'], 'source_row' => $row['_row'],
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }

            $data = [
                'date' => $row['date'], 'plot' => $row['plot'], 'no' => $row['no'],
                'species' => $row['species'], 'length' => $row['length'],
                'leaf_count' => $row['leaf_count'], 'status' => $row['status'], 'note' => $row['note'],
                'source_file' => $filename, 'source_sheet' => $row['_sheet'], 'source_row' => $row['_row'],
                'created_at' => $now, 'updated_at' => $now,
            ];
            if ($config['phase'] === 2) {
                $data['survey_order'] = $row['survey_order'];
                $data['density'] = $row['density'];
                $data['cotyledon_count'] = $row['cotyledon_count'];
            }

            return $data;
        }, $rows);

        $statusFixes = $connection->transaction(function () use ($connection, $config, $payload): ?array {
            if (($config['kind'] ?? null) === 'greenhouse_order') {
                $this->validateGreenhouseOrderPayload($payload);
                $connection->table($config['table'])->delete();
            }

            foreach (array_chunk($payload, 500) as $chunk) {
                $connection->table($config['table'])->insert($chunk);
            }

            if ($config['table'] === 'groundcover_phase1_raw') {
                return $this->rebuildPhase1Cleaned($connection);
            }

            if ($config['table'] === 'groundcover_phase2_raw') {
                return $this->rebuildPhase2Cleaned($connection);
            }

            if ($config['table'] === 'greenhouse_raw') {
                $this->applyGreenhouseSurveyOrders($connection);

                return $this->rebuildGreenhouseCleaned($connection);
            }

            return null;
        });

        return ['count' => count($payload), 'status_fixes' => $statusFixes];
    }

    private function validateGreenhouseOrderPayload(array $payload): void
    {
        $orders = [];
        $individuals = [];
        $errors = [];

        foreach ($payload as $row) {
            $location = ($row['source_sheet'] ?: '工作表').'!'.$row['source_row'];
            $order = $this->integerValue($row['survey_order']);
            $key = $row['plot'].'|'.$row['no'];
            if ($order === null || $order <= 0) {
                $errors[] = "{$location}：調查順序必須是大於 0 的整數。";
            } elseif (isset($orders[$order])) {
                $errors[] = "{$location}：調查順序 {$order} 重複。";
            }
            if ($row['plot'] === '' || $row['no'] === '') {
                $errors[] = "{$location}：處理與編號不可空白。";
            } elseif (isset($individuals[$key])) {
                $errors[] = "{$location}：個體 {$key} 重複。";
            }
            if ($row['shade'] === '' || ! str_ends_with($row['plot'], $row['shade'])) {
                $errors[] = "{$location}：遮蔭處理與處理代碼最後一碼不一致。";
            }
            $orders[$order] = true;
            $individuals[$key] = true;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['spreadsheet' => implode('；', array_slice($errors, 0, 10))]);
        }
    }

    private function applyGreenhouseSurveyOrders(\Illuminate\Database\Connection $connection): void
    {
        $references = $connection->table('greenhouse_survey_orders')->get()
            ->keyBy(fn (object $row): string => strtoupper(trim((string) $row->plot)).'|'.strtoupper(trim((string) $row->no)));
        $assignedOrders = [];
        $individualOrders = [];
        $errors = [];

        $rows = $connection->table('greenhouse_raw')->orderBy('id')->get();
        foreach ($rows as $row) {
            $plot = strtoupper(trim((string) $row->plot));
            $number = strtoupper(trim((string) $row->no));
            $key = $plot.'|'.$number;
            $reference = $references->get($key);
            $order = $this->integerValue($row->survey_order) ?? ($reference ? (int) $reference->survey_order : null);
            $location = ($row->source_sheet ?: '工作表').'!'.$row->source_row;

            if ($plot === '' || $number === '') {
                $errors[] = "{$location}：處理與編號不可空白。";

                continue;
            }
            if ($order === null || $order <= 0) {
                $errors[] = "{$location}：{$plot} / {$number} 找不到調查順序。";

                continue;
            }
            if (isset($individualOrders[$key]) && $individualOrders[$key] !== $order) {
                $errors[] = "{$location}：{$plot} / {$number} 在不同資料列使用不同調查順序。";
            }
            if (isset($assignedOrders[$order]) && $assignedOrders[$order] !== $key) {
                $errors[] = "{$location}：調查順序 {$order} 同時對應 {$assignedOrders[$order]} 與 {$key}。";
            }
            $individualOrders[$key] = $order;
            $assignedOrders[$order] = $key;

            $connection->table('greenhouse_raw')->where('id', $row->id)->update([
                'survey_order' => $order,
                'shade' => substr($plot, -1),
                'plot' => $plot,
                'no' => $number,
                'updated_at' => now(),
            ]);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'spreadsheet' => '完整資料尚未全部配對調查順序：'.implode('；', array_slice($errors, 0, 10)),
            ]);
        }
    }

    private function rebuildPhase1Cleaned(\Illuminate\Database\Connection $connection): array
    {
        $target = 'groundcover_phase1_cleaned';
        $connection->table($target)->delete();

        $now = now();
        $connection->table('groundcover_phase1_raw')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($connection, $target, $now): void {
                $payload = $rows->map(function (object $row) use ($now): array {
                    return [
                        'raw_id' => $row->id,
                        'survey_date' => $this->dateValue($row->date, $row->source_sheet ?? '', $row->source_row ?? 0),
                        'plot' => $this->nullIfBlank($row->plot),
                        'individual_no' => $this->nullIfBlank($row->no),
                        'species' => $this->nullIfBlank($row->species),
                        'length' => $this->decimalValue($row->length),
                        'leaf_count' => $this->integerValue($row->leaf_count),
                        'status' => $this->nullIfBlank($row->status),
                        'note' => $this->nullIfBlank($row->note),
                        'clean_note' => null,
                        'source_file' => $row->source_file,
                        'source_sheet' => $row->source_sheet,
                        'source_row' => $row->source_row,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                $connection->table($target)->insert($payload);
            });

        return app(Phase1RawStatusNormalizer::class)->normalize(
            $connection,
            $target,
            'survey_date',
            'individual_no'
        );
    }

    private function rebuildPhase2Cleaned(\Illuminate\Database\Connection $connection): array
    {
        $target = 'groundcover_phase2_cleaned';
        $connection->table($target)->delete();

        $now = now();
        $connection->table('groundcover_phase2_raw')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($connection, $target, $now): void {
                $payload = $rows->map(function (object $row) use ($now): array {
                    return [
                        'raw_id' => $row->id,
                        'survey_order' => $row->survey_order,
                        'survey_date' => $this->dateValue($row->date, $row->source_sheet ?? '', $row->source_row ?? 0),
                        'plot' => $this->nullIfBlank($row->plot),
                        'density' => $this->decimalValue($row->density),
                        'individual_no' => $this->nullIfBlank($row->no),
                        'species' => $this->nullIfBlank($row->species),
                        'length' => $this->decimalValue($row->length),
                        'cotyledon_count' => $this->integerValue($row->cotyledon_count),
                        'leaf_count' => $this->integerValue($row->leaf_count),
                        'status' => $this->nullIfBlank($row->status),
                        'note' => $this->nullIfBlank($row->note),
                        'clean_note' => null,
                        'source_file' => $row->source_file,
                        'source_sheet' => $row->source_sheet,
                        'source_row' => $row->source_row,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                $connection->table($target)->insert($payload);
            });

        return app(Phase2RawStatusNormalizer::class)->normalize(
            $connection,
            $target,
            'survey_date',
            'individual_no'
        );
    }

    private function rebuildGreenhouseCleaned(\Illuminate\Database\Connection $connection): array
    {
        $target = 'greenhouse_cleaned';
        $connection->table($target)->delete();
        $now = now();

        $connection->table('greenhouse_raw')->orderBy('id')
            ->chunkById(500, function ($rows) use ($connection, $target, $now): void {
                $payload = $rows->map(fn (object $row): array => [
                    'raw_id' => $row->id,
                    'survey_order' => $this->integerValue($row->survey_order),
                    'shade' => strtoupper((string) $this->nullIfBlank($row->shade)),
                    'survey_date' => $this->dateValue($row->date, $row->source_sheet ?? '', $row->source_row ?? 0),
                    'plot' => strtoupper((string) $this->nullIfBlank($row->plot)),
                    'individual_no' => strtoupper((string) $this->nullIfBlank($row->no)),
                    'species' => $this->nullIfBlank($row->species),
                    'length' => $this->decimalValue($row->length),
                    'leaf_count' => $this->integerValue($row->leaf_count),
                    'status' => strtoupper((string) $this->nullIfBlank($row->status)),
                    'note' => $this->nullIfBlank($row->note),
                    'clean_note' => null,
                    'source_file' => $row->source_file,
                    'source_sheet' => $row->source_sheet,
                    'source_row' => $row->source_row,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();
                $connection->table($target)->insert($payload);
            });

        return ['rows_changed' => 0];
    }

    private function importCleaned(array $config, array $rows, string $filename): int
    {
        $connection = DB::connection(self::CONNECTION);
        if ($connection->table($config['table'])->where('source_file', $filename)->exists()) {
            throw ValidationException::withMessages(['spreadsheet' => "檔案 {$filename} 已上傳過；如需重傳，請先確認並移除舊資料。"]);
        }

        $now = now();
        $payload = [];

        foreach ($rows as $row) {
            $data = [
                'raw_id' => null,
                'survey_date' => $this->dateValue($row['date'], $row['_sheet'], $row['_row']),
                'plot' => $this->nullIfBlank($row['plot']),
                'individual_no' => $this->nullIfBlank($row['no']),
                'species' => $this->nullIfBlank($row['species']),
                'length' => $this->decimalValue($row['length']),
                'leaf_count' => $this->integerValue($row['leaf_count']),
                'status' => $this->nullIfBlank($row['status']),
                'note' => $this->nullIfBlank($row['note']),
                'clean_note' => null,
                'source_file' => $filename,
                'source_sheet' => $row['_sheet'],
                'source_row' => $row['_row'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($config['phase'] === 2) {
                $data['survey_order'] = $row['survey_order'] === null ? null : (int) $row['survey_order'];
                $data['density'] = $this->decimalValue($row['density']);
                $data['cotyledon_count'] = $this->integerValue($row['cotyledon_count']);
            }
            $payload[] = $data;
        }

        $connection->transaction(function () use ($connection, $config, $payload): void {
            foreach (array_chunk($payload, 500) as $chunk) {
                $connection->table($config['table'])->insert($chunk);
            }
        });

        return count($payload);
    }

    private function cellValue(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $this->nullIfBlank($value);
    }

    private function dateValue(?string $value, string $sheet, int $row): string
    {
        $timestamp = $value ? strtotime($value) : false;
        if ($timestamp === false) {
            throw ValidationException::withMessages(['spreadsheet' => "{$sheet}!{$row} 的日期「{$value}」無法辨識。"]);
        }

        return date('Y-m-d', $timestamp);
    }

    private function decimalValue(?string $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private function integerValue(?string $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function nullIfBlank(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function rowIsEmpty(array $values): bool
    {
        return collect($values)->every(fn ($value) => $this->nullIfBlank($value) === null);
    }
}
