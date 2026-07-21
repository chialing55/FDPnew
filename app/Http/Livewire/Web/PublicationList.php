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

    public string $year = '';

    public string $type = '';

    public bool $showFilters = true;

    protected $queryString = [
        'year' => ['except' => ''],
        'type' => ['except' => ''],
        'site' => ['except' => ''],
        'subject' => ['except' => ''],
    ];

    public function mount(?string $site = null, ?string $subject = null, bool $showFilters = true): void
    {
        $this->site = $site;
        $this->subject = $subject;
        $this->showFilters = $showFilters;
        $this->initializeSiteSubjectFilters();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->year = '';
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
            ->when($this->year !== '', fn ($query) => $query->where('year', $this->year))
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->when($this->site !== null, fn ($query) => $query->whereHas(
                'sites', fn ($siteQuery) => $siteQuery->where('sites.id', $this->selectedSiteId())
            ))
            ->when($this->subject !== null, fn ($query) => $query->whereHas(
                'subjects', fn ($subjectQuery) => $subjectQuery->where('subjects.id', $this->selectedSubjectId())
            ));

        return view('livewire.web.publication-list', [
            'publications' => $query->latestFirst()->paginate(30),
            'years' => Publication::active()->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'types' => Publication::active()->whereNotNull('type')->where('type', '!=', '')
                ->distinct()->orderBy('type')->pluck('type')
                ->mapWithKeys(fn (string $type): array => [$type => Publication::typeLabels()[$type] ?? $type]),
        ]);
    }
}
