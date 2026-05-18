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

    public function mount($user = null, $site = null): void
    {
        $this->user = $user;
        $this->site = $site;

        $this->traps = DB::connection('mysql3')
            ->table('seedling_individuals')
            ->select('trap')
            ->distinct()
            ->orderBy('trap')
            ->pluck('trap')
            ->map(fn ($trap) => (string) $trap)
            ->toArray();

        $this->plots = DB::connection('mysql3')
            ->table('seedling_individuals')
            ->select('plot')
            ->distinct()
            ->orderBy('plot')
            ->pluck('plot')
            ->map(fn ($plot) => (string) $plot)
            ->toArray();

        $this->months = DB::connection('mysql3')
            ->table('seedling_records')
            ->selectRaw("CONCAT(year, '-', LPAD(month, 2, '0')) as ym")
            ->distinct()
            ->orderBy('ym')
            ->pluck('ym')
            ->toArray();

        $this->speciesOptions = DB::connection('mysql3')
            ->table('seedling_individuals')
            ->select('csp')
            ->whereNotNull('csp')
            ->distinct()
            ->orderBy('csp')
            ->pluck('csp')
            ->map(fn ($species) => trim((string) $species))
            ->filter(fn ($species) => $species !== '')
            ->unique()
            ->values()
            ->toArray();

        $this->mtagOptions = DB::connection('mysql3')
            ->table('seedling_individuals')
            ->select('mtag')
            ->distinct()
            ->orderBy('mtag')
            ->pluck('mtag')
            ->toArray();

        $this->tagOptions = DB::connection('mysql3')
            ->table('seedling_stems')
            ->select('tag')
            ->distinct()
            ->orderBy('tag')
            ->pluck('tag')
            ->toArray();

        $this->statusOptions = DB::connection('mysql3')
            ->table('seedling_records')
            ->select('status')
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->filter(fn ($status) => trim((string) $status) !== '')
            ->values()
            ->toArray();

        $this->recruitOptions = DB::connection('mysql3')
            ->table('seedling_records')
            ->select('recruit')
            ->whereNotNull('recruit')
            ->distinct()
            ->orderBy('recruit')
            ->pluck('recruit')
            ->filter(fn ($recruit) => trim((string) $recruit) !== '')
            ->values()
            ->toArray();

        $this->sproutOptions = DB::connection('mysql3')
            ->table('seedling_stems')
            ->select('sprout')
            ->whereNotNull('sprout')
            ->distinct()
            ->orderBy('sprout')
            ->pluck('sprout')
            ->filter(fn ($sprout) => trim((string) $sprout) !== '')
            ->values()
            ->toArray();

        $this->search();
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
            ->orderBy('i.trap')
            ->orderBy('i.plot')
            ->orderBy('r.census')
            ->orderBy('st.tag')
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
                'x' => $row->x,
                'y' => $row->y,
                'note' => $row->note,
            ])
            ->toArray();
    }

    private function baseQuery()
    {
        $tag = trim($this->tag);
        $mtag = trim($this->mtag);
        $species = trim($this->species);

        return DB::connection('mysql3')
            ->table('seedling_records as r')
            ->join('seedling_stems as st', 'r.tag', '=', 'st.tag')
            ->join('seedling_individuals as i', 'st.mtag', '=', 'i.mtag')
            ->select([
                'i.trap',
                'i.plot',
                'r.census',
                DB::raw("CONCAT(r.year, '-', LPAD(r.month, 2, '0')) as ym"),
                'st.mtag',
                'st.tag',
                'i.csp',
                'r.status',
                'r.ht',
                'r.cotno',
                'r.leafno',
                'r.recruit',
                'st.sprout',
                'i.x',
                'i.y',
                DB::raw("COALESCE(r.note, '') as note"),
            ])
            ->when($this->trap !== 'all', fn ($query) => $query->where('i.trap', $this->trap))
            ->when($this->plot !== 'all', fn ($query) => $query->where('i.plot', $this->plot))
            ->when($this->ym !== 'all', function ($query) {
                [$year, $month] = explode('-', $this->ym, 2);

                $query->where('r.year', $year)->where('r.month', (int) $month);
            })
            ->when($mtag !== '' && $mtag !== 'all', fn ($query) => $query->where('st.mtag', $mtag))
            ->when($tag !== '' && $tag !== 'all', fn ($query) => $query->where('st.tag', $tag))
            ->when($species !== '' && $species !== 'all', fn ($query) => $query->where('i.csp', $species))
            ->when($this->status !== 'all', fn ($query) => $query->where('r.status', $this->status))
            ->when($this->recruit !== 'all', fn ($query) => $query->where('r.recruit', $this->recruit))
            ->when($this->sprout !== 'all', fn ($query) => $query->where('st.sprout', $this->sprout))
            ->when($this->hasNumericFilter($this->heightValue), fn ($query) => $this->applyNumericFilter($query, 'r.ht', $this->heightOperator, $this->heightValue));
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

        return rtrim(rtrim((string) $value, '0'), '.');
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
