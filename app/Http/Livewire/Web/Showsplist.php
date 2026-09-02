<?php

namespace App\Http\Livewire\Web;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Support\Web\RelatedTagStyle;

//網頁物種清單
class Showsplist extends Component
{
    public string $search = '';

    public string $displayMode = 'site';

    public array $selectedSites = [];

    public array $selectedResearches = [];

    public string $filterMatch = 'intersection';

    public string $sortColumn = 'canonical_name';

    public string $sortDirection = 'asc';

    public int $filterVersion = 0;

    /** @var array<int, array<string, mixed>> */
    public array $species = [];

    /** @var array<string, array<string, bool>> */
    public array $siteResearchAvailability = [];

    public function mount(): void
    {
        $this->species = $this->loadSpecies();
        $this->siteResearchAvailability = $this->loadSiteResearchAvailability();
    }

    public function setDisplayMode(string $mode): void
    {
        if (in_array($mode, ['site', 'research'], true)) {
            $this->displayMode = $mode;
        }
    }

    public function toggleSite(string $site): void
    {
        $this->selectedSites = $this->toggleFilter($this->selectedSites, $site, ['fushan', 'nanjenshan', 'shoushan']);
        $this->filterVersion++;
    }

    public function selectAllSites(): void
    {
        $this->selectedSites = [];
        $this->filterVersion++;
    }

    public function toggleResearch(string $research): void
    {
        $this->selectedResearches = $this->toggleFilter($this->selectedResearches, $research, ['tree', 'seedling', 'seed']);
        $this->filterVersion++;
    }

    public function selectAllResearches(): void
    {
        $this->selectedResearches = [];
        $this->filterVersion++;
    }

    public function setFilterMatch(string $match): void
    {
        if (in_array($match, ['intersection', 'union'], true)) {
            $this->filterMatch = $match;
            $this->filterVersion++;
        }
    }

    public function sort(string $column): void
    {
        $columns = ['family', 'canonical_name', 'chname', 'fushan', 'nanjenshan', 'shoushan', 'tree', 'seedling', 'seed'];
        if (! in_array($column, $columns, true)) {
            return;
        }

        $this->sortDirection = $this->sortColumn === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortColumn = $column;
    }

    public function sortIndicator(string $column): string
    {
        if ($this->sortColumn !== $column) {
            return '';
        }

        return $this->sortDirection === 'asc' ? '↑' : '↓';
    }

    public function tagClasses(): string
    {
        return RelatedTagStyle::classes();
    }

    public function tagStyle(string $type, int $index): string
    {
        return RelatedTagStyle::for($type, $index);
    }

    /** @param array<string, mixed> $species */
    public function siteCellMatches(array $species, string $site): bool
    {
        if ($this->selectedResearches === []) {
            return (int) ($species[$site] ?? 0) === 1;
        }

        $matches = collect($this->selectedResearches)
            ->map(fn (string $research): bool => (int) ($species[$site.'_'.$research] ?? 0) === 1);

        return $this->filterMatch === 'union'
            ? $matches->contains(true)
            : $matches->every(fn (bool $match): bool => $match);
    }

    /** @param array<string, mixed> $species */
    public function researchCellMatches(array $species, string $research): bool
    {
        if ($this->selectedSites === []) {
            return (int) ($species[$research] ?? 0) === 1;
        }

        return collect($this->effectiveSelectedSites())
            ->contains(fn (string $site): bool => (int) ($species[$site.'_'.$research] ?? 0) === 1);
    }

    /** @return array<int, string> */
    private function effectiveSelectedSites(): array
    {
        if ($this->selectedSites === [] || $this->selectedResearches === []) {
            return $this->selectedSites;
        }

        return collect($this->selectedSites)->filter(function (string $site): bool {
            $availability = collect($this->selectedResearches)
                ->map(fn (string $research): bool => $this->siteResearchAvailability[$site][$research] ?? false);

            return $this->filterMatch === 'union'
                ? $availability->contains(true)
                : $availability->every(fn (bool $available): bool => $available);
        })->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function loadSpecies(): array
    {
        if (! Schema::connection('plant_catalog')->hasTable('site_species')
            || ! Schema::connection('plant_catalog')->hasTable('species_research_links')
            || ! Schema::connection('plant_catalog')->hasTable('taiwan_checklist')) {
            return [];
        }

        return DB::connection('plant_catalog')->table('site_species as species')
            ->join('taiwan_checklist as checklist', 'checklist.spcode', '=', 'species.code')
            ->leftJoin('species_research_links as research', function ($join): void {
                $join->on('research.site', '=', 'species.site')
                    ->on('research.spcode', '=', 'species.spcode');
            })
            ->whereIn('species.site', ['fushan', 'nanjenshan', 'shoushan'])
            ->whereNotNull('species.code')->where('species.code', '<>', '')
            ->whereNotNull('species.spcode')->where('species.spcode', '<>', '')
            ->where('species.spcode', 'not like', 'UNK%')
            ->where(function ($query): void {
                $query->whereNull('species.csp')
                    ->orWhere('species.csp', 'not like', 'UNK%');
            })
            ->select([
                'species.code', 'checklist.family', 'checklist.chfamily', 'checklist.genus',
                'checklist.canonical_name', 'checklist.chname',
            ])
            ->selectRaw("MAX(CASE WHEN species.site = 'fushan' THEN 1 ELSE 0 END) as fushan")
            ->selectRaw("MAX(CASE WHEN species.site = 'fushan' THEN species.spcode END) as fushan_spcode")
            ->selectRaw("MAX(CASE WHEN species.site = 'nanjenshan' THEN 1 ELSE 0 END) as nanjenshan")
            ->selectRaw("MAX(CASE WHEN species.site = 'shoushan' THEN 1 ELSE 0 END) as shoushan")
            ->selectRaw("MAX(CASE WHEN research.research_code = 'tree' THEN 1 ELSE 0 END) as tree")
            ->selectRaw("MAX(CASE WHEN research.research_code = 'seed' THEN 1 ELSE 0 END) as seed")
            ->selectRaw("MAX(CASE WHEN research.research_code = 'seedling' THEN 1 ELSE 0 END) as seedling")
            ->selectRaw("MAX(CASE WHEN species.site = 'fushan' AND research.research_code = 'tree' THEN 1 ELSE 0 END) as fushan_tree")
            ->selectRaw("MAX(CASE WHEN species.site = 'fushan' AND research.research_code = 'seed' THEN 1 ELSE 0 END) as fushan_seed")
            ->selectRaw("MAX(CASE WHEN species.site = 'fushan' AND research.research_code = 'seedling' THEN 1 ELSE 0 END) as fushan_seedling")
            ->selectRaw("MAX(CASE WHEN species.site = 'nanjenshan' AND research.research_code = 'tree' THEN 1 ELSE 0 END) as nanjenshan_tree")
            ->selectRaw("MAX(CASE WHEN species.site = 'nanjenshan' AND research.research_code = 'seed' THEN 1 ELSE 0 END) as nanjenshan_seed")
            ->selectRaw("MAX(CASE WHEN species.site = 'nanjenshan' AND research.research_code = 'seedling' THEN 1 ELSE 0 END) as nanjenshan_seedling")
            ->selectRaw("MAX(CASE WHEN species.site = 'shoushan' AND research.research_code = 'tree' THEN 1 ELSE 0 END) as shoushan_tree")
            ->selectRaw("MAX(CASE WHEN species.site = 'shoushan' AND research.research_code = 'seed' THEN 1 ELSE 0 END) as shoushan_seed")
            ->selectRaw("MAX(CASE WHEN species.site = 'shoushan' AND research.research_code = 'seedling' THEN 1 ELSE 0 END) as shoushan_seedling")
            ->groupBy([
                'species.code', 'checklist.family', 'checklist.chfamily', 'checklist.genus',
                'checklist.canonical_name', 'checklist.chname',
            ])
            ->orderBy('checklist.family')->orderBy('checklist.genus')->orderBy('checklist.canonical_name')
            ->get()->map(fn ($species): array => (array) $species)->all();
    }

    /** @return array<string, array<string, bool>> */
    private function loadSiteResearchAvailability(): array
    {
        $availability = collect(['fushan', 'nanjenshan', 'shoushan'])
            ->mapWithKeys(fn (string $site): array => [$site => [
                'tree' => false, 'seedling' => false, 'seed' => false,
            ]])->all();

        if (! Schema::connection('plant_catalog')->hasTable('species_research_links')) {
            return $availability;
        }

        DB::connection('plant_catalog')->table('species_research_links')
            ->whereIn('site', array_keys($availability))
            ->whereIn('research_code', ['tree', 'seedling', 'seed'])
            ->distinct()->get(['site', 'research_code'])
            ->each(function ($row) use (&$availability): void {
                $availability[$row->site][$row->research_code] = true;
            });

        return $availability;
    }

    /** @return array<int, array<string, mixed>> */
    public function filteredSpecies(): array
    {
        $search = trim(Str::lower($this->search));
        return collect($this->species)
            ->when($search !== '', fn ($species) => $species->filter(fn (array $row): bool => Str::contains(
                Str::lower(implode(' ', [
                    $row['family'] ?? '', $row['chfamily'] ?? '', $row['genus'] ?? '',
                    $row['canonical_name'] ?? '', $row['chname'] ?? '', $row['code'] ?? '',
                ])),
                $search
            )))
            ->filter(function (array $species): bool {
                $filters = [...$this->selectedSites, ...$this->selectedResearches];
                if ($filters === []) {
                    return true;
                }

                if ($this->filterMatch === 'union') {
                    if ($this->selectedSites === []) {
                        return collect($this->selectedResearches)
                            ->contains(fn (string $research): bool => (int) ($species[$research] ?? 0) === 1);
                    }

                    return collect($this->selectedSites)->contains(function (string $site) use ($species): bool {
                        if ((int) ($species[$site] ?? 0) !== 1) {
                            return false;
                        }

                        return $this->selectedResearches === []
                            || collect($this->selectedResearches)->contains(
                                fn (string $research): bool => (int) ($species[$site.'_'.$research] ?? 0) === 1
                            );
                    });
                }

                if ($this->selectedSites === []) {
                    return collect($this->selectedResearches)
                        ->every(fn (string $research): bool => (int) ($species[$research] ?? 0) === 1);
                }

                $eligibleSites = collect($this->selectedSites)->filter(function (string $site): bool {
                    return collect($this->selectedResearches)
                        ->every(fn (string $research): bool => $this->siteResearchAvailability[$site][$research] ?? false);
                });

                if ($eligibleSites->isEmpty()) {
                    return false;
                }

                return $eligibleSites->every(function (string $site) use ($species): bool {
                    if ((int) ($species[$site] ?? 0) !== 1) {
                        return false;
                    }

                    return collect($this->selectedResearches)
                        ->every(fn (string $research): bool => (int) ($species[$site.'_'.$research] ?? 0) === 1);
                });
            })
            ->sortBy($this->sortColumn, SORT_NATURAL | SORT_FLAG_CASE, $this->sortDirection === 'desc')
            ->values()
            ->all();
    }

    /** @param array<int, string> $selected @param array<int, string> $allowed */
    private function toggleFilter(array $selected, string $value, array $allowed): array
    {
        if (! in_array($value, $allowed, true)) {
            return $selected;
        }

        return collect($selected)->contains($value)
            ? array_values(array_diff($selected, [$value]))
            : [...$selected, $value];
    }

    public function render()
    {
        return view('livewire.web.showsplist', ['speciesList' => $this->filteredSpecies()]);
    }
}
