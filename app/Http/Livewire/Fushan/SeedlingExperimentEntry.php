<?php

namespace App\Http\Livewire\Fushan;

use App\Services\Fushan\SeedlingExperimentEntrySaveService;
use App\Services\Fushan\SeedlingExperimentSurveyService;
use App\Support\DataEntry\SaveFeedback;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;

class SeedlingExperimentEntry extends Component
{
    private const CONNECTION = 'fs_seedling_exp';

    public ?int $surveyId = null;

    public string $surveyPeriod = '';

    public string $surveyStatus = '';

    public string $defaultSurveyDate = '';

    public string $userName = '';

    public string $inputDate = '';

    public array $plots = [];

    public int $currentPlotIndex = 0;

    public array $records = [];

    public array $gRevivalCandidates = [];

    public string $gRevivalIndividualId = '';

    public int $completedCount = 0;

    public int $totalCount = 0;

    public string $message = '';

    public bool $messageIsError = false;

    public array $gridErrors = [];

    public string $gridMessage = '';

    public bool $gridResultOk = true;

    public string $gridStatus = 'noop';

    public function mount(): void
    {
        $this->userName = (string) (auth()->user()?->account ?? auth()->user()?->name ?? '');
        $this->inputDate = now()->format('Y-m-d');
        $this->loadActiveSurvey();
    }

    public function startInput(): void
    {
        $this->resetMessage();
        $validated = $this->validate([
            'defaultSurveyDate' => ['required', 'date_format:Y-m-d'],
        ], [
            'defaultSurveyDate.required' => '請先選擇調查日期。',
            'defaultSurveyDate.date_format' => '調查日期格式必須為 YYYY-MM-DD。',
        ]);

        try {
            $survey = app(SeedlingExperimentSurveyService::class)->start(
                $this->surveyId,
                $validated['defaultSurveyDate'],
                auth()->id(),
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->setError($exception->getMessage());

            return;
        }

        $this->surveyStatus = $survey->status;
        $this->loadPlots();
        $this->loadFirstIncompletePlot();
        $this->message = '已開始本期資料輸入。';
    }

    public function selectPlot(int $index): void
    {
        if (! isset($this->plots[$index])) {
            return;
        }

        $this->currentPlotIndex = $index;
        $this->gRevivalIndividualId = '';
        $this->loadCurrentPlot();
        $this->resetMessage();
    }

    public function saveRows(array $rows): void
    {
        $this->resetMessage();
        $this->records = array_values($rows);
        $plotGroup = $this->currentPlot()['group'] ?? null;
        if ($this->surveyId === null || $plotGroup === null) {
            $this->dispatchSaveResult(false, [], '找不到目前調查批次或樣區組。');

            return;
        }

        try {
            $result = app(SeedlingExperimentEntrySaveService::class)
                ->saveGroup($this->surveyId, $plotGroup, $rows);
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchSaveResult(false, [], '儲存失敗，輸入內容仍保留在畫面上。');

            return;
        }

        $changed = (int) ($result['changed'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? 0);
        $failed = (int) ($result['failed'] ?? 0);
        $status = SaveFeedback::status($changed, $failed);
        $feedbackMessage = SaveFeedback::message($status, $changed, $skipped, $failed);
        $this->loadPlots();

        if (! $result['ok']) {
            $this->dispatchSaveResult(
                false,
                $result['errors'],
                $feedbackMessage,
                $status,
                $result['counts'] ?? [],
            );

            return;
        }

        $this->loadCurrentPlot();
        $this->dispatchSaveResult(
            true,
            [],
            $feedbackMessage,
            $status,
            $result['counts'] ?? [],
        );
    }

    public function reviveG(): void
    {
        $this->resetMessage();
        $group = $this->currentPlot()['group'] ?? null;
        $individualId = filter_var($this->gRevivalIndividualId, FILTER_VALIDATE_INT);
        if ($this->surveyId === null || $group === null || $individualId === false) {
            $this->setError('請先選擇要恢復追蹤的 G 個體。');

            return;
        }

        try {
            app(SeedlingExperimentSurveyService::class)
                ->addRecoveredIndividual($this->surveyId, $group, $individualId);
        } catch (Throwable $exception) {
            report($exception);
            $this->setError($exception->getMessage());

            return;
        }

        $this->gRevivalIndividualId = '';
        $this->loadPlots();
        $this->loadCurrentPlot();
        $this->message = '已將 G 個體加入本期，請輸入本次 A 的實際量測值後儲存。';
    }

    public function completeSurvey(): void
    {
        $this->resetMessage();

        try {
            $result = app(SeedlingExperimentSurveyService::class)
                ->complete($this->surveyId, auth()->id());
        } catch (Throwable $exception) {
            report($exception);
            $this->setError($exception->getMessage());

            return;
        }

        $completedPeriod = $result['survey']->survey_period;
        $nextPeriod = $result['next_survey']->survey_period;
        $this->loadActiveSurvey();
        $this->message = "已完成 {$completedPeriod} 調查，並建立 {$nextPeriod} 調查批次與記錄紙名單。";
    }

    public function getTitleProperty(): string
    {
        if ($this->surveyPeriod === '') {
            return '地被實驗第二期資料輸入';
        }

        [$year, $month] = explode('-', $this->surveyPeriod);

        return sprintf('%d 年 %d 月 地被實驗第二期資料輸入', (int) $year, (int) $month);
    }

    public function getCurrentPlotProperty(): ?array
    {
        return $this->currentPlot();
    }

    public function getCanCompleteProperty(): bool
    {
        return $this->surveyStatus === 'in_progress'
            && $this->totalCount > 0
            && $this->completedCount === $this->totalCount;
    }

    public function getIsLastPlotGroupProperty(): bool
    {
        return $this->plots !== [] && $this->currentPlotIndex === count($this->plots) - 1;
    }

    public function render()
    {
        return view('livewire.fushan.seedling-experiment-entry');
    }

    private function loadActiveSurvey(): void
    {
        $summary = app(SeedlingExperimentSurveyService::class)->summary();
        $survey = $summary['active_survey'] ?? null;

        if ($survey === null) {
            $this->surveyId = null;
            $this->surveyPeriod = '';
            $this->surveyStatus = '';
            $this->plots = [];
            $this->records = [];
            $this->setError($summary['message'] ?? '目前沒有可輸入的調查批次。');

            return;
        }

        $this->surveyId = (int) $survey->id;
        $this->surveyPeriod = $survey->survey_period;
        $this->surveyStatus = $survey->status;
        $this->defaultSurveyDate = (string) ($survey->default_survey_date ?? '');

        if ($this->surveyStatus === 'in_progress') {
            $this->loadPlots();
            $this->loadFirstIncompletePlot();
        } else {
            $this->plots = [];
            $this->records = [];
            $this->refreshProgress();
        }
    }

    private function loadPlots(): void
    {
        $rows = DB::connection(self::CONNECTION)
            ->table('survey_individuals as batch_items')
            ->join('individuals as individuals', 'individuals.id', '=', 'batch_items.individual_id')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->where('batch_items.survey_id', $this->surveyId)
            ->orderBy('batch_items.survey_order')
            ->get(['plots.plot_code', 'batch_items.record_id']);

        $this->plots = $rows
            ->groupBy(fn (object $row): string => explode('-', $row->plot_code, 2)[0])
            ->map(function ($groupRows, string $group): array {
                $completed = $groupRows->whereNotNull('record_id')->count();

                return [
                    'group' => $group,
                    'plots' => $groupRows->pluck('plot_code')->unique()->values()->all(),
                    'total' => $groupRows->count(),
                    'completed' => $completed,
                    'is_complete' => $completed === $groupRows->count(),
                ];
            })->values()->all();

        $this->refreshProgress();
    }

    private function loadFirstIncompletePlot(): void
    {
        $index = collect($this->plots)->search(fn (array $plot): bool => ! $plot['is_complete']);
        $this->currentPlotIndex = $index === false ? 0 : (int) $index;
        $this->loadCurrentPlot();
    }

    private function loadCurrentPlot(): void
    {
        $plot = $this->currentPlot();
        if ($plot === null) {
            $this->records = [];
            $this->gRevivalCandidates = [];

            return;
        }

        $rows = DB::connection(self::CONNECTION)
            ->table('survey_individuals as batch_items')
            ->join('individuals as individuals', 'individuals.id', '=', 'batch_items.individual_id')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->leftJoin('records as records', 'records.id', '=', 'batch_items.record_id')
            ->where('batch_items.survey_id', $this->surveyId)
            ->whereIn('plots.plot_code', $plot['plots'])
            ->orderBy('batch_items.survey_order')
            ->get([
                'batch_items.id as batch_item_id',
                'batch_items.individual_id',
                'plots.plot_code as plot',
                'plots.density',
                'individuals.individual_no',
                'individuals.species_name as species',
                'records.survey_date as date',
                'records.length',
                'records.cotyledon_count',
                'records.leaf_count',
                'records.status',
                'records.note',
            ]);

        $previousByIndividual = DB::connection(self::CONNECTION)
            ->table('records')
            ->whereIn('individual_id', $rows->pluck('individual_id'))
            ->where('survey_period', '<', $this->surveyPeriod)
            ->orderBy('individual_id')
            ->orderByDesc('survey_date')
            ->orderByDesc('id')
            ->get(['individual_id', 'status'])
            ->unique('individual_id')
            ->keyBy('individual_id');

        $this->records = $rows
            ->map(function (object $row) use ($previousByIndividual): array {
                $previousWasDead = $row->status === null
                    && $previousByIndividual->get($row->individual_id)?->status === 'D';

                return [
                    'batch_item_id' => (int) $row->batch_item_id,
                    'date' => $row->date ?? $this->defaultSurveyDate,
                    'plot' => $row->plot,
                    'density' => $this->formatNumber($row->density),
                    'individual_no' => $row->individual_no,
                    'species' => $row->species,
                    'length' => $row->length === null ? ($previousWasDead ? -4 : '') : $this->formatNumber($row->length),
                    'cotyledon_count' => $row->cotyledon_count ?? ($previousWasDead ? -4 : 0),
                    'leaf_count' => $row->leaf_count ?? ($previousWasDead ? -4 : ''),
                    'status' => $row->status ?? ($previousWasDead ? 'D' : 'A'),
                    'note' => $row->note ?? '',
                ];
            })
            ->all();

        $this->loadGRevivalCandidates();
    }

    private function loadGRevivalCandidates(): void
    {
        $plotGroup = $this->currentPlot();
        if ($plotGroup === null || $this->surveyId === null) {
            $this->gRevivalCandidates = [];

            return;
        }

        $this->gRevivalCandidates = DB::connection(self::CONNECTION)
            ->table('individuals as individuals')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->whereIn('plots.plot_code', $plotGroup['plots'])
            ->whereNotNull('individuals.end_date')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')->from('survey_individuals as existing')
                    ->whereColumn('existing.individual_id', 'individuals.id')
                    ->where('existing.survey_id', $this->surveyId);
            })
            ->orderBy('individuals.survey_order')
            ->get(['individuals.id', 'plots.plot_code', 'individuals.individual_no', 'individuals.species_name'])
            ->map(fn (object $row): array => [
                'id' => (int) $row->id,
                'label' => "{$row->plot_code} / {$row->individual_no} / {$row->species_name}",
            ])->all();
    }

    private function refreshProgress(): void
    {
        if ($this->surveyId === null) {
            $this->completedCount = 0;
            $this->totalCount = 0;

            return;
        }

        $connection = DB::connection(self::CONNECTION);
        $this->totalCount = $connection->table('survey_individuals')
            ->where('survey_id', $this->surveyId)
            ->count();
        $this->completedCount = $connection->table('survey_individuals')
            ->where('survey_id', $this->surveyId)
            ->whereNotNull('record_id')
            ->count();
    }

    private function currentPlot(): ?array
    {
        return $this->plots[$this->currentPlotIndex] ?? null;
    }

    private function dispatchGrid(): void
    {
        if ($this->surveyStatus !== 'in_progress' || $this->records === []) {
            return;
        }

        $this->dispatch(
            'seedling-experiment-entry-grid-data',
            containerId: 'seedling-experiment-entry-hot',
            records: $this->records,
            columns: config('seedling-experiment-entry.columns'),
            initialErrors: $this->gridErrors,
            initialMessage: $this->gridMessage,
            initialResultOk: $this->gridResultOk,
            initialStatus: $this->gridStatus,
            initialDirty: $this->gridErrors !== [],
        );
    }

    public function rendered(): void
    {
        $this->dispatchGrid();
    }

    private function dispatchSaveResult(bool $ok, array $errors, string $message, ?string $status = null, array $counts = []): void
    {
        $this->gridErrors = $errors;
        $this->gridMessage = $message;
        $this->gridResultOk = $ok;
        $this->gridStatus = $status ?? ($ok ? 'success' : 'error');
        $this->dispatch(
            'seedling-experiment-entry-save-result',
            containerId: 'seedling-experiment-entry-hot',
            ok: $ok,
            status: $status ?? ($ok ? 'success' : 'error'),
            counts: $counts,
            errors: $errors,
            message: $message,
        );
    }

    private function resetMessage(): void
    {
        $this->message = '';
        $this->messageIsError = false;
        $this->gridErrors = [];
        $this->gridMessage = '';
        $this->gridResultOk = true;
        $this->gridStatus = 'noop';
        $this->resetErrorBag();
    }

    private function setError(string $message): void
    {
        $this->message = $message;
        $this->messageIsError = true;
    }

    private function formatNumber(mixed $value): int|float|string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $number = (float) $value;

        return floor($number) === $number ? (int) $number : $number;
    }
}
