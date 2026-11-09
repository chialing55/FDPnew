<?php

namespace App\Services\Fushan;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SeedlingExperimentSurveyService
{
    private const CONNECTION = 'fs_seedling_exp';

    private const EXPERIMENT_CODE = 'groundcover_phase2';

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $connection = DB::connection(self::CONNECTION);
        $experiment = $connection->table('experiments')
            ->where('code', self::EXPERIMENT_CODE)
            ->first();

        if ($experiment === null) {
            return [
                'available' => false,
                'message' => '找不到地被實驗第二期設定。',
            ];
        }

        $hasBatchTables = Schema::connection(self::CONNECTION)->hasTable('surveys')
            && Schema::connection(self::CONNECTION)->hasTable('survey_individuals');

        $activeSurvey = $hasBatchTables
            ? $connection->table('surveys')
                ->where('experiment_id', $experiment->id)
                ->whereIn('status', ['prepared', 'in_progress'])
                ->orderByDesc('survey_period')
                ->first()
            : null;

        $latestPeriod = $connection->table('records as records')
            ->join('individuals as individuals', 'individuals.id', '=', 'records.individual_id')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('plots.experiment_id', $experiment->id)
            ->max('records.survey_period');

        $nextPeriod = $latestPeriod === null
            ? null
            : CarbonImmutable::createFromFormat('!Y-m', $latestPeriod)->addMonth()->format('Y-m');

        $eligibleQuery = $connection->table('individuals as individuals')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('plots.experiment_id', $experiment->id)
            ->whereNull('individuals.end_date');

        $eligibleCount = (clone $eligibleQuery)->count();
        $missingOrderCount = (clone $eligibleQuery)->whereNull('individuals.survey_order')->count();

        return [
            'available' => $hasBatchTables,
            'message' => $hasBatchTables ? null : '記錄紙資料表尚未建立，請先執行 migration。',
            'experiment_id' => (int) $experiment->id,
            'latest_period' => $latestPeriod,
            'next_period' => $nextPeriod,
            'eligible_count' => $eligibleCount,
            'missing_order_count' => $missingOrderCount,
            'active_survey' => $activeSurvey,
            'active_survey_count' => $activeSurvey === null
                ? 0
                : $connection->table('survey_individuals')
                    ->where('survey_id', $activeSurvey->id)
                    ->count(),
        ];
    }

    public function prepare(int|string|null $userId): object
    {
        if (! Schema::connection(self::CONNECTION)->hasTable('surveys')) {
            throw new RuntimeException('記錄紙資料表尚未建立，請先執行 migration。');
        }

        return DB::connection(self::CONNECTION)->transaction(function () use ($userId): object {
            $connection = DB::connection(self::CONNECTION);
            $experiment = $connection->table('experiments')
                ->where('code', self::EXPERIMENT_CODE)
                ->lockForUpdate()
                ->first();

            if ($experiment === null) {
                throw new RuntimeException('找不到地被實驗第二期設定。');
            }

            $existing = $connection->table('surveys')
                ->where('experiment_id', $experiment->id)
                ->whereIn('status', ['prepared', 'in_progress'])
                ->orderByDesc('survey_period')
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $latestPeriod = $connection->table('records as records')
                ->join('individuals as individuals', 'individuals.id', '=', 'records.individual_id')
                ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
                ->where('plots.experiment_id', $experiment->id)
                ->max('records.survey_period');

            if ($latestPeriod === null) {
                throw new RuntimeException('尚無正式調查紀錄，無法判斷下一個調查月份。');
            }

            $surveyPeriod = CarbonImmutable::createFromFormat('!Y-m', $latestPeriod)
                ->addMonth()
                ->format('Y-m');

            $individuals = $connection->table('individuals as individuals')
                ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
                ->where('plots.experiment_id', $experiment->id)
                ->whereNull('individuals.end_date')
                ->select([
                    'individuals.id',
                    'individuals.survey_order',
                ])
                ->orderBy('individuals.survey_order')
                ->get();

            if ($individuals->isEmpty()) {
                throw new RuntimeException('目前沒有仍需追蹤的小苗。');
            }

            if ($individuals->contains(fn (object $individual): bool => $individual->survey_order === null)) {
                throw new RuntimeException('仍有小苗缺少調查順序，尚不能產生記錄紙。');
            }

            if ($individuals->pluck('survey_order')->duplicates()->isNotEmpty()) {
                throw new RuntimeException('調查順序有重複值，尚不能產生記錄紙。');
            }

            $previousRecords = $connection->table('records')
                ->whereIn('individual_id', $individuals->pluck('id'))
                ->orderBy('survey_date')
                ->orderBy('id')
                ->get(['id', 'individual_id'])
                ->keyBy('individual_id');

            $now = now();
            $surveyId = $connection->table('surveys')->insertGetId([
                'experiment_id' => $experiment->id,
                'survey_period' => $surveyPeriod,
                'status' => 'prepared',
                'prepared_by' => is_numeric($userId) ? (int) $userId : null,
                'prepared_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $rows = $individuals->map(fn (object $individual): array => [
                'survey_id' => $surveyId,
                'individual_id' => $individual->id,
                'previous_record_id' => $previousRecords->get($individual->id)?->id,
                'survey_order' => $individual->survey_order,
                'record_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            foreach (array_chunk($rows, 500) as $chunk) {
                $connection->table('survey_individuals')->insert($chunk);
            }

            return $connection->table('surveys')->find($surveyId);
        });
    }

    public function findForDownload(int $surveyId): object
    {
        $survey = DB::connection(self::CONNECTION)
            ->table('surveys as surveys')
            ->join('experiments as experiments', 'experiments.id', '=', 'surveys.experiment_id')
            ->where('surveys.id', $surveyId)
            ->where('experiments.code', self::EXPERIMENT_CODE)
            ->select('surveys.*')
            ->first();

        if ($survey === null) {
            throw new RuntimeException('找不到指定的地被實驗第二期調查批次。');
        }

        return $survey;
    }

    public function start(int $surveyId, string $surveyDate, int|string|null $userId): object
    {
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $surveyDate);

        return DB::connection(self::CONNECTION)->transaction(function () use ($surveyId, $surveyDate, $date, $userId): object {
            $connection = DB::connection(self::CONNECTION);
            $survey = $connection->table('surveys')
                ->where('id', $surveyId)
                ->lockForUpdate()
                ->first();

            if ($survey === null || ! in_array($survey->status, ['prepared', 'in_progress'], true)) {
                throw new RuntimeException('目前調查批次不存在或已完成。');
            }
            if ($date->format('Y-m') !== $survey->survey_period) {
                throw new RuntimeException("調查日期必須屬於 {$survey->survey_period}。");
            }

            $connection->table('surveys')
                ->where('id', $surveyId)
                ->update([
                    'default_survey_date' => $surveyDate,
                    'status' => 'in_progress',
                    'started_by' => $survey->started_by ?? (is_numeric($userId) ? (int) $userId : null),
                    'started_at' => $survey->started_at ?? now(),
                    'updated_at' => now(),
                ]);

            return $connection->table('surveys')->find($surveyId);
        });
    }

    public function addRecoveredIndividual(int $surveyId, string $plotGroup, int $individualId): void
    {
        DB::connection(self::CONNECTION)->transaction(function () use ($surveyId, $plotGroup, $individualId): void {
            $connection = DB::connection(self::CONNECTION);
            $survey = $connection->table('surveys')->where('id', $surveyId)->lockForUpdate()->first();
            if ($survey === null || $survey->status !== 'in_progress') {
                throw new RuntimeException('目前調查批次不存在或尚未開始。');
            }

            $individual = $connection->table('individuals as individuals')
                ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
                ->where('individuals.id', $individualId)
                ->where('plots.experiment_id', $survey->experiment_id)
                ->where('plots.plot_code', 'like', $plotGroup.'-%')
                ->whereNotNull('individuals.end_date')
                ->select('individuals.*', 'plots.plot_code')
                ->first();
            if ($individual === null) {
                throw new RuntimeException('所選個體不屬於目前樣區組，或不是已由 G 結束的個體。');
            }

            $latestRecord = $connection->table('records')
                ->where('individual_id', $individualId)
                ->orderByDesc('survey_date')->orderByDesc('id')->first();
            if ($latestRecord === null || $latestRecord->status !== 'G') {
                throw new RuntimeException('所選個體最後狀態不是 G，不能使用 G 復活加入。');
            }
            if ($connection->table('survey_individuals')->where('survey_id', $surveyId)->where('individual_id', $individualId)->exists()) {
                throw new RuntimeException('此個體已在本期輸入名單中。');
            }
            if ($individual->survey_order === null) {
                throw new RuntimeException('此個體缺少調查順序，不能加入本期。');
            }

            $connection->table('survey_individuals')->insert([
                'survey_id' => $surveyId,
                'individual_id' => $individualId,
                'previous_record_id' => $latestRecord->id,
                'survey_order' => $individual->survey_order,
                'record_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * @return array{survey:object, next_survey:object}
     */
    public function complete(int $surveyId, int|string|null $userId): array
    {
        $completedSurvey = DB::connection(self::CONNECTION)->transaction(function () use ($surveyId, $userId): object {
            $connection = DB::connection(self::CONNECTION);
            $survey = $connection->table('surveys')
                ->where('id', $surveyId)
                ->lockForUpdate()
                ->first();

            if ($survey === null || $survey->status !== 'in_progress') {
                throw new RuntimeException('目前調查批次不存在、尚未開始或已完成。');
            }

            $completionErrors = app(SeedlingExperimentEntrySaveService::class)->completionErrors($surveyId);
            if ($completionErrors !== []) {
                $shown = array_slice($completionErrors, 0, 10);
                $message = '完成前檢查失敗：'.implode('；', $shown);
                if (count($completionErrors) > count($shown)) {
                    $message .= '；另有 '.(count($completionErrors) - count($shown)).' 項錯誤。';
                }

                throw new RuntimeException($message);
            }

            $missing = $connection->table('survey_individuals')
                ->where('survey_id', $surveyId)
                ->whereNull('record_id')
                ->count();
            if ($missing > 0) {
                throw new RuntimeException("仍有 {$missing} 株尚未完成輸入。");
            }

            $invalid = $connection->table('survey_individuals as batch_items')
                ->leftJoin('records as records', 'records.id', '=', 'batch_items.record_id')
                ->where('batch_items.survey_id', $surveyId)
                ->where(function ($query) use ($survey): void {
                    $query->whereNull('records.id')
                        ->orWhere('records.survey_period', '!=', $survey->survey_period);
                })
                ->count();
            if ($invalid > 0) {
                throw new RuntimeException('部分正式紀錄與本調查批次不一致，請先檢查資料。');
            }

            $connection->table('surveys')
                ->where('id', $surveyId)
                ->update([
                    'status' => 'completed',
                    'completed_by' => is_numeric($userId) ? (int) $userId : null,
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

            return $connection->table('surveys')->find($surveyId);
        });

        $nextSurvey = $this->prepare($userId);

        return ['survey' => $completedSurvey, 'next_survey' => $nextSurvey];
    }
}
