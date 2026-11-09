<?php

namespace App\Console\Commands;

use App\Services\Fushan\Phase1FormalDataImporter;
use Illuminate\Console\Command;

class ImportPhase1FormalData extends Command
{
    protected $signature = 'seedling-experiment:import-phase1';

    protected $description = '依調查日期逐批將第一期修改後資料匯入個體與調查紀錄表';

    public function handle(Phase1FormalDataImporter $importer): int
    {
        $result = $importer->importRemaining();

        foreach ($result['imported'] as $batch) {
            $this->info("{$batch['date']} 完成：新增個體 {$batch['createdIndividuals']}、調查紀錄 {$batch['createdRecords']}、略過 G 後重複紀錄 {$batch['skippedAfterGone']}。");
        }

        if ($result['stopped'] !== null) {
            $this->error("遇到不合資料，已停在 {$result['stopped']}；該批及後續批次尚未匯入。");
            foreach ($result['errors'] as $error) {
                $this->line("- {$error}");
            }

            return self::FAILURE;
        }

        $this->info('第一期所有剩餘調查批次均已匯入完成。');

        return self::SUCCESS;
    }
}
