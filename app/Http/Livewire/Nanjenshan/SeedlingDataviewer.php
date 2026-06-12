<?php

namespace App\Http\Livewire\Nanjenshan;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SeedlingDataviewer extends Component
{
    public $user;
    public $site;
    public array $data = [];
    public array $plots = [];
    public array $quadrats = [];
    public array $months = [];
    public array $speciesOptions = [];
    public array $tagOptions = [];
    public array $statusOptions = [];
    public array $operatorOptions = ['>', '≧', '=', '≦', '<'];
    public string $plot = 'all';
    public string $quadrat = 'all';
    public string $ym = 'all';
    public string $tag = 'all';
    public string $species = 'all';
    public string $status = 'all';
    public string $heightOperator = '=';
    public string $heightValue = '';
    public string $leafEatenOperator = '=';
    public string $leafEatenValue = '';
    public string $leafCoveredOperator = '=';
    public string $leafCoveredValue = '';
    public string $diseaseSpotOperator = '=';
    public string $diseaseSpotValue = '';
    public int $page = 1;
    public int $perPage = 50;
    public int $total = 0;
    public int $totalPages = 1;

    public function mount($user = null, $site = null): void
    {
        $this->user = $user;
        $this->site = $site;

        $this->refreshFilterOptions();
        $this->search();
    }

    public function search(): void
    {
        $this->page = 1;
        $this->refreshFilterOptions();
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
        $page = max(1, min((int) $page, $this->totalPages));
        $this->page = $page;
        $this->loadData();
    }

    private function loadData(): void
    {
        $query = $this->baseQuery();

        $this->total = (clone $query)->count();
        $this->totalPages = max(1, (int) ceil($this->total / $this->perPage));
        $this->page = max(1, min($this->page, $this->totalPages));

        $this->data = $query
            ->orderBy('quadrats.plot_name')
            ->orderBy('quadrats.quadrat')
            ->orderBy('censuses.census')
            ->orderBy('seedling_individuals.tag')
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage)
            ->get()
            ->map(fn ($row) => [
                'plot_name' => $row->plot_name,
                'quadrat' => $row->quadrat,
                'census' => $row->census,
                'ym' => $row->ym,
                'tag' => $row->tag,
                'species' => $this->formatSpecies($row->standard_species_name, $row->recorded_species_name),
                'status' => $row->status,
                'height' => $this->formatNumber($row->height),
                'cotyledon' => $row->cotyledon,
                'leaf' => $row->leaf,
                'leaf_eaten_percent' => $this->formatNumber($row->leaf_eaten_percent),
                'leaf_covered_percent' => $this->formatNumber($row->leaf_covered_percent),
                'disease_spot_percent' => $this->formatNumber($row->disease_spot_percent),
                'death_cause' => $row->death_cause,
                'remark' => $row->remark,
            ])
            ->toArray();
    }

    private function refreshFilterOptions(): void
    {
        $this->plots = $this->distinctOption('quadrats.plot_name', 'plot');
        $this->quadrats = $this->distinctOption('quadrats.quadrat', 'quadrat');
        $this->months = $this->distinctOption('censuses.ym', 'ym');
        $this->speciesOptions = $this->distinctOption('seedling_individuals.standard_species_name', 'species');
        $this->tagOptions = $this->distinctOption('seedling_individuals.tag', 'tag');
        $this->statusOptions = $this->distinctOption('seedling_records.status', 'status');
    }

    private function baseQuery()
    {
        $query = DB::connection('njs_seedling')
            ->table('seedling_records')
            ->join('seedling_individuals', 'seedling_records.tag', '=', 'seedling_individuals.tag')
            ->join('quadrats', 'seedling_individuals.quadrat', '=', 'quadrats.quadrat')
            ->join('censuses', 'seedling_records.census', '=', 'censuses.census')
            ->select([
                'quadrats.plot_name',
                'quadrats.quadrat',
                'censuses.census',
                'censuses.ym',
                'seedling_individuals.tag',
                'seedling_individuals.recorded_species_name',
                'seedling_individuals.standard_species_name',
                'seedling_records.status',
                'seedling_records.height',
                'seedling_records.cotyledon',
                'seedling_records.leaf',
                'seedling_records.leaf_eaten_percent',
                'seedling_records.leaf_covered_percent',
                'seedling_records.disease_spot_percent',
                'seedling_records.death_cause',
                'seedling_records.remark',
            ]);

        return $this->applyFilters($query);
    }

    private function optionBaseQuery(?string $except = null)
    {
        $query = DB::connection('njs_seedling')
            ->table('seedling_records')
            ->join('seedling_individuals', 'seedling_records.tag', '=', 'seedling_individuals.tag')
            ->join('quadrats', 'seedling_individuals.quadrat', '=', 'quadrats.quadrat')
            ->join('censuses', 'seedling_records.census', '=', 'censuses.census');

        return $this->applyFilters($query, $except);
    }

    private function applyFilters($query, ?string $except = null)
    {
        $tag = trim($this->tag);
        $species = trim($this->species);

        return $query
            ->when($except !== 'plot' && $this->plot !== 'all', fn ($query) => $query->where('quadrats.plot_name', $this->plot))
            ->when($except !== 'quadrat' && $this->quadrat !== 'all', fn ($query) => $query->where('quadrats.quadrat', $this->quadrat))
            ->when($except !== 'ym' && $this->ym !== 'all', fn ($query) => $query->where('censuses.ym', $this->ym))
            ->when($except !== 'tag' && $tag !== '' && $tag !== 'all', fn ($query) => $query->where('seedling_individuals.tag', 'like', '%' . $tag . '%'))
            ->when($except !== 'status' && $this->status !== 'all', fn ($query) => $query->where('seedling_records.status', $this->status))
            ->when($this->hasNumericFilter($this->heightValue), fn ($query) => $this->applyNumericFilter($query, 'seedling_records.height', $this->heightOperator, $this->heightValue))
            ->when($this->hasNumericFilter($this->leafEatenValue), fn ($query) => $this->applyNumericFilter($query, 'seedling_records.leaf_eaten_percent', $this->leafEatenOperator, $this->leafEatenValue))
            ->when($this->hasNumericFilter($this->leafCoveredValue), fn ($query) => $this->applyNumericFilter($query, 'seedling_records.leaf_covered_percent', $this->leafCoveredOperator, $this->leafCoveredValue))
            ->when($this->hasNumericFilter($this->diseaseSpotValue), fn ($query) => $this->applyNumericFilter($query, 'seedling_records.disease_spot_percent', $this->diseaseSpotOperator, $this->diseaseSpotValue))
            ->when($except !== 'species' && $species !== '' && $species !== 'all', function ($query) use ($species) {
                $query->where(function ($query) use ($species) {
                    $query->where('seedling_individuals.standard_species_name', 'like', '%' . $species . '%')
                        ->orWhere('seedling_individuals.recorded_species_name', 'like', '%' . $species . '%');
                });
            });
    }

    private function distinctOption(string $column, string $except): array
    {
        return $this->optionBaseQuery($except)
            ->selectRaw("{$column} as option_value")
            ->whereNotNull(DB::raw($column))
            ->distinct()
            ->orderBy('option_value')
            ->pluck('option_value')
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->toArray();
    }

    private function hasNumericFilter(string $value): bool
    {
        return trim($value) !== '' && is_numeric($value);
    }

    private function applyNumericFilter($query, string $column, string $operator, string $value)
    {
        if (!in_array($operator, $this->operatorOptions, true) || !$this->hasNumericFilter($value)) {
            return $query;
        }

        $sqlOperator = match ($operator) {
            '≧' => '>=',
            '≦' => '<=',
            default => $operator,
        };

        return $query->where($column, $sqlOperator, (float) $value);
    }

    private function formatSpecies(?string $standard, ?string $recorded): string
    {
        $standard = trim((string) $standard);
        $recorded = trim((string) $recorded);

        if ($standard !== '' && $recorded !== '' && $standard !== $recorded) {
            return "{$standard} ({$recorded})";
        }

        return $standard !== '' ? $standard : $recorded;
    }

    private function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return rtrim(rtrim((string) $value, '0'), '.');
    }

    public function render()
    {
        return view('livewire.nanjenshan.seedling-dataviewer');
    }
}
