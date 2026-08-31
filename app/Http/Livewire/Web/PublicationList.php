<?php

namespace App\Http\Livewire\Web;

use App\Http\Livewire\Web\Concerns\InteractsWithSiteSubjectFilters;
use App\Models\Web\Publication;
use Livewire\Component;
use Livewire\WithPagination;

class PublicationList extends Component
{
    use InteractsWithSiteSubjectFilters;
    use WithPagination;

    public string $yearFrom = '';

    public string $yearTo = '';

    public string $type = '';

    public bool $showFilters = true;

    protected $queryString = [
        'yearFrom' => ['as' => 'year_from', 'except' => ''],
        'yearTo' => ['as' => 'year_to', 'except' => ''],
        'type' => ['except' => ''],
        'site' => ['except' => ''],
        'subject' => ['except' => ''],
    ];

    public function mount(
        ?string $site = null,
        ?string $subject = null,
        bool $showFilters = true,
        bool $includeAllTags = false,
    ): void
    {
        $this->site = $site;
        $this->subject = $subject;
        $this->showFilters = $showFilters;
        $this->yearTo = $this->yearTo !== '' ? $this->yearTo : (string) now()->year;
        $this->initializeSiteSubjectFilters();

        if ($includeAllTags) {
            $this->showSiteTags = true;
            $this->showSubjectTags = true;
        }
    }

    public function updatedYearFrom(): void
    {
        $this->resetPage();
    }

    public function updatedYearTo(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->yearFrom = '';
        $this->yearTo = (string) now()->year;
        $this->type = '';

        if ($this->showSiteFilter) {
            $this->site = null;
        }

        if ($this->showSubjectFilter) {
            $this->subject = null;
        }

        $this->resetPage();
    }

    public function render()
    {
        $query = Publication::active()
            ->with(['sites.page', 'subjects.page'])
            ->when($this->yearFrom !== '', fn ($query) => $query->where('year', '>=', $this->yearFrom))
            ->when($this->yearTo !== '', fn ($query) => $query->where('year', '<=', $this->yearTo))
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->when($this->site !== null, fn ($query) => $query->whereHas(
                'sites', fn ($siteQuery) => $siteQuery->where('sites.id', $this->selectedSiteId())
            ))
            ->when($this->subject !== null, fn ($query) => $query->whereHas(
                'subjects', fn ($subjectQuery) => $subjectQuery->where('subjects.id', $this->selectedSubjectId())
            ));

        return view('livewire.web.publication-list', [
            'publications' => $query->latestFirst()->paginate(30),
            'years' => Publication::active()->whereNotNull('year')->distinct()->pluck('year')
                ->push(now()->year)->unique()->sortDesc()->values(),
            'types' => Publication::active()->whereNotNull('type')->where('type', '!=', '')
                ->distinct()->orderBy('type')->pluck('type')
                ->mapWithKeys(fn (string $type): array => [$type => Publication::typeLabels()[$type] ?? $type]),
        ]);
    }
}
