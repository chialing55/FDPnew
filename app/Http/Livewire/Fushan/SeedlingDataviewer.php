<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

//小苗檢視資料
class SeedlingDataviewer extends Component
{
    public $user;
    public $site;
    public array $data = [];
    public array $traps = [];
    public array $plots = [];
    public array $months = [];
    public array $speciesOptions = [];
    public array $mtagOptions = [];
    public array $tagOptions = [];
    public array $statusOptions = [];
    public array $recruitOptions = [];
    public array $sproutOptions = [];
    public array $operatorOptions = ['>', '≧', '=', '≦', '<'];
    public string $trap = 'all';
    public string $plot = 'all';
    public string $ym = 'all';
    public string $mtag = 'all';
    public string $tag = 'all';
    public string $species = 'all';
    public string $status = 'all';
    public string $recruit = 'all';
    public string $sprout = 'all';
    public string $heightOperator = '=';
    public string $heightValue = '';
    public int $page = 1;
    public int $perPage = 50;
    public int $total = 0;
    public int $totalPages = 1;
    public string $latestSurveyYm = "尚無資料";
    public string $sortField = 'census';
    public string $sortDirection = 'asc';

    public function mount($user = null, $site = null): void
    {
        $this->user = $user;
        $this->site = $site;

        $this->latestSurveyYm = $this->loadLatestSurveyYm();
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


    private function loadLatestSurveyYm(): string
    {
        $latest = DB::connection("mysql3")
            ->table("seedling")
            ->select("year", "month")
            ->orderByDesc("census")
            ->first();

        if (!$latest || !$latest->year || !$latest->month) {
            return "尚無資料";
        }

        return sprintf("%04d-%02d", (int) $latest->year, (int) $latest->month);
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
                'trap' => $row->trap,
                'plot' => $row->plot,
                'census' => $row->census,
                'ym' => $row->ym,
                'mtag' => $row->mtag,
                'tag' => $row->tag,
                'species' => $row->csp,
                'status' => $row->status,
                'height' => $this->formatNumber($row->ht),
                'leaf' => $this->formatLeafCount($row->cotno, $row->leafno),
                'recruit' => $row->recruit,
                'sprout' => $row->sprout,
                'note' => $row->note,
            ])
            ->toArray();
    }


    private function refreshFilterOptions(): void
    {
        $this->traps = $this->distinctOption('s.trap', 'trap');
        $this->plots = $this->distinctOption('s.plot', 'plot');
        $this->months = $this->distinctMonthOptions();
        $this->speciesOptions = $this->distinctOption('s.csp', 'species');
        $this->statusOptions = $this->distinctOption('s.status', 'status');
        $this->recruitOptions = $this->distinctOption('s.recruit', 'recruit');
        $this->sproutOptions = $this->distinctOption('s.sprout', 'sprout');
    }


    private function baseQuery()
    {
        $query = DB::connection("mysql3")
            ->table("seedling as s")
            ->select([
                "s.trap",
                "s.plot",
                "s.census",
                DB::raw("CONCAT(s.year, CHAR(45), LPAD(s.month, 2, 0)) as ym"),
                "s.mtag",
                "s.tag",
                "s.csp",
                "s.status",
                "s.ht",
                "s.cotno",
                "s.leafno",
                "s.recruit",
                "s.sprout",
                DB::raw("COALESCE(s.note, CHAR(32)) as note"),
            ]);

        return $this->applyFilters($query);
    }


    private function optionBaseQuery(?string $except = null)
    {
        $query = DB::connection("mysql3")->table("seedling as s");

        return $this->applyFilters($query, $except);
    }


    private function applyFilters($query, ?string $except = null)
    {
        $tag = trim($this->tag);
        $mtag = trim($this->mtag);
        $species = trim($this->species);

        return $query
            ->when($except !== 'trap' && $this->trap !== 'all', fn ($query) => $query->where('s.trap', $this->trap))
            ->when($except !== 'plot' && $this->plot !== 'all', fn ($query) => $query->where('s.plot', $this->plot))
            ->when($except !== 'ym' && $this->ym !== 'all', function ($query) {
                [$year, $month] = explode('-', $this->ym, 2);

                $query->where('s.year', $year)->where('s.month', (int) $month);
            })
            ->when($except !== 'mtag' && $mtag !== '' && $mtag !== 'all', fn ($query) => $query->where('s.mtag', $mtag))
            ->when($except !== 'tag' && $tag !== '' && $tag !== 'all', fn ($query) => $query->where('s.tag', $tag))
            ->when($except !== 'species' && $species !== '' && $species !== 'all', fn ($query) => $query->where('s.csp', $species))
            ->when($except !== 'status' && $this->status !== 'all', fn ($query) => $query->where('s.status', $this->status))
            ->when($except !== 'recruit' && $this->recruit !== 'all', fn ($query) => $query->where('s.recruit', $this->recruit))
            ->when($except !== 'sprout' && $this->sprout !== 'all', fn ($query) => $query->where('s.sprout', $this->sprout))
            ->when($this->hasNumericFilter($this->heightValue), fn ($query) => $this->applyNumericFilter($query, 's.ht', $this->heightOperator, $this->heightValue));
    }


    private function sortableColumns(): array
    {
        return [
            'census' => ['s.census'],
            'trap' => ['s.trap'],
            'mtag' => ['s.mtag'],
            'tag' => ['s.tag'],
            'species' => ['s.csp'],
        ];
    }


    private function applySorting($query)
    {
        $columns = $this->sortableColumns()[$this->sortField] ?? $this->sortableColumns()['census'];
        $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        foreach ($columns as $column) {
            $query->orderBy($column, $direction);
        }

        return $query
            ->orderBy('s.census')
            ->orderBy('s.trap')
            ->orderBy('s.plot')
            ->orderBy('s.tag');
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
            ->values()
            ->toArray();
    }


    private function distinctMonthOptions(): array
    {
        return $this->optionBaseQuery('ym')
            ->selectRaw("CONCAT(s.year, '-', LPAD(s.month, 2, '0')) as ym")
            ->distinct()
            ->orderBy('ym')
            ->pluck('ym')
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


    private function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = (string) $value;

        if (strpos($value, '.') !== false) {
            return rtrim(rtrim($value, '0'), '.');
        }

        return $value;
    }


    private function formatLeafCount($cotyledon, $leaf): string
    {
        if ($leaf === null || $leaf === '') {
            return '';
        }

        if ($cotyledon === null || $cotyledon === '' || (float) $cotyledon <= 0) {
            return $this->formatNumber($leaf);
        }

        return $this->formatNumber($cotyledon) . '+' . $this->formatNumber($leaf);
    }


    public function render()
    {
        return view('livewire.fushan.seedling-dataviewer');
    }

}
