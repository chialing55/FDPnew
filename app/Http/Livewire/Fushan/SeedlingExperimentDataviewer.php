<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SeedlingExperimentDataviewer extends Component
{
    private const EXPERIMENTS = [
        'groundcover_phase1' => '地被實驗第一期',
        'groundcover_phase2' => '地被實驗第二期',
        'greenhouse' => '溫室實驗',
    ];

    public string $experiment = 'groundcover_phase1';
    public array $data = [];
    public array $plots = [];
    public array $periods = [];
    public array $speciesOptions = ['黃杞', '香楠'];
    public array $statusOptions = [];
    public array $operatorOptions = ['>', '≧', '=', '≦', '<'];
    public string $plot = 'all';
    public string $surveyPeriod = 'all';
    public string $individualNo = '';
    public string $species = 'all';
    public string $status = 'all';
    public string $lengthOperator = '=';
    public string $lengthValue = '';
    public int $page = 1;
    public int $perPage = 50;
    public int $total = 0;
    public int $totalPages = 1;
    public string $latestSurveyPeriod = '尚無資料';
    public string $sortField = 'survey_date';
    public string $sortDirection = 'asc';

    public function mount(): void
    {
        $this->refresh();
    }

    public function selectExperiment(string $experiment): void
    {
        if (!array_key_exists($experiment, self::EXPERIMENTS)) {
            return;
        }

        $this->experiment = $experiment;
        $this->resetFilters();
        $this->refresh();
    }

    public function search(): void
    {
        $this->page = 1;
        $this->loadData();
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadData();
        }
    }

    public function nextPage(): void
    {
        if ($this->page < $this->totalPages) {
            $this->page++;
            $this->loadData();
        }
    }

    public function goToPage($page): void
    {
        $this->page = max(1, min((int) $page, $this->totalPages));
        $this->loadData();
    }

    public function sortBy(string $field): void
    {
        if (!array_key_exists($field, $this->sortableColumns())) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->page = 1;
        $this->loadData();
    }

    public function sortIndicator(string $field): string
    {
        if ($this->sortField !== $field) {
            return '';
        }

        return $this->sortDirection === 'asc' ? ' ▲' : ' ▼';
    }

    public function experimentOptions(): array
    {
        return self::EXPERIMENTS;
    }

    public function experimentName(): string
    {
        return self::EXPERIMENTS[$this->experiment] ?? self::EXPERIMENTS['groundcover_phase1'];
    }

    private function refresh(): void
    {
        $this->latestSurveyPeriod = $this->baseQuery(false)->max('r.survey_period') ?? '尚無資料';
        $this->refreshFilterOptions();
        $this->loadData();
    }

    private function resetFilters(): void
    {
        $this->plot = 'all';
        $this->surveyPeriod = 'all';
        $this->individualNo = '';
        $this->species = 'all';
        $this->status = 'all';
        $this->lengthOperator = '=';
        $this->lengthValue = '';
        $this->page = 1;
        $this->sortField = 'survey_date';
        $this->sortDirection = 'asc';
    }

    private function loadData(): void
    {
        $query = $this->baseQuery();
        $this->total = (clone $query)->count();
        $this->totalPages = max(1, (int) ceil($this->total / $this->perPage));
        $this->page = max(1, min($this->page, $this->totalPages));

        $this->data = $this->applySorting($query)
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'survey_period' => $row->survey_period,
                'survey_date' => $row->survey_date,
                'plot' => $row->plot,
                'density' => $this->formatNumber($row->density),
                'individual_no' => $row->individual_no,
                'species' => $row->species,
                'length' => $this->formatNumber($row->length),
                'cotyledon_count' => $row->cotyledon_count,
                'leaf_count' => $row->leaf_count,
                'status' => $row->status,
                'note' => $row->note,
            ])
            ->toArray();
    }

    private function baseQuery(bool $applyFilters = true): Builder
    {
        $query = DB::connection('fs_seedling_exp')
            ->table('records as r')
            ->join('individuals as i', 'i.id', '=', 'r.individual_id')
            ->join('plots as p', 'p.id', '=', 'i.plot_id')
            ->join('experiments as e', 'e.id', '=', 'p.experiment_id')
            ->where('e.code', $this->experiment)
            ->select([
                'r.id', 'r.survey_period', 'r.survey_date', 'p.plot_code as plot',
                'p.density', 'i.individual_no', 'i.species_name as species',
                'r.length', 'r.cotyledon_count', 'r.leaf_count', 'r.status',
                'r.note',
            ]);

        return $applyFilters ? $this->applyFilters($query) : $query;
    }

    private function applyFilters(Builder $query): Builder
    {
        $individualNo = trim($this->individualNo);
        $species = trim($this->species);

        return $query
            ->when($this->plot !== 'all', fn (Builder $query) => $query->where('p.plot_code', $this->plot))
            ->when($this->surveyPeriod !== 'all', fn (Builder $query) => $query->where('r.survey_period', $this->surveyPeriod))
            ->when($individualNo !== '' && $individualNo !== 'all', fn (Builder $query) => $query->where('i.individual_no', 'like', $individualNo.'%'))
            ->when($species !== '' && $species !== 'all', fn (Builder $query) => $query->where('i.species_name', $species))
            ->when($this->status !== 'all', fn (Builder $query) => $query->where('r.status', $this->status))
            ->when($this->hasNumericFilter($this->lengthValue), fn (Builder $query) => $this->applyNumericFilter($query));
    }

    private function refreshFilterOptions(): void
    {
        $query = $this->baseQuery(false);
        $this->plots = (clone $query)->select('p.plot_code')->distinct()->orderBy('p.plot_code')->pluck('p.plot_code')->toArray();
        $this->periods = (clone $query)->select('r.survey_period')->distinct()->orderBy('r.survey_period')->pluck('r.survey_period')->toArray();
        $this->statusOptions = (clone $query)->select('r.status')->distinct()->orderBy('r.status')->pluck('r.status')->toArray();
    }

    private function sortableColumns(): array
    {
        return [
            'survey_date' => 'r.survey_date',
            'plot' => 'p.plot_code',
            'individual_no' => 'i.individual_no',
            'species' => 'i.species_name',
        ];
    }

    private function applySorting(Builder $query): Builder
    {
        $column = $this->sortableColumns()[$this->sortField] ?? 'r.survey_date';
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($column, $direction)
            ->orderBy('r.survey_date')
            ->orderBy('p.plot_code')
            ->orderBy('i.individual_no');
    }

    private function hasNumericFilter(string $value): bool
    {
        return trim($value) !== '' && is_numeric($value);
    }

    private function applyNumericFilter(Builder $query): Builder
    {
        if (!in_array($this->lengthOperator, $this->operatorOptions, true)) {
            return $query;
        }

        $operator = match ($this->lengthOperator) {
            '≧' => '>=',
            '≦' => '<=',
            default => $this->lengthOperator,
        };

        return $query->where('r.length', $operator, (float) $this->lengthValue);
    }

    private function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = (string) $value;

        return str_contains($value, '.') ? rtrim(rtrim($value, '0'), '.') : $value;
    }

    public function render()
    {
        return view('livewire.fushan.seedling-experiment-dataviewer');
    }
}
