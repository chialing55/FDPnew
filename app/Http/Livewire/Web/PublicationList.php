<?php

namespace App\Http\Livewire\Web;

use App\Models\Web\Publication;
use App\Models\Web\Site;
use App\Models\Web\Subject;
use Livewire\Component;
use Livewire\WithPagination;

class PublicationList extends Component
{
    use WithPagination;

    public string $year = '';

    public string $site = '';

    public string $subject = '';

    public bool $showFilters = true;

    protected $queryString = [
        'year' => ['except' => ''],
        'site' => ['except' => ''],
        'subject' => ['except' => ''],
    ];

    public function mount(?string $site = null, ?string $subject = null, bool $showFilters = true): void
    {
        $this->site = $site ?? '';
        $this->subject = $subject ?? '';
        $this->showFilters = $showFilters;
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedSite(): void
    {
        $this->resetPage();
    }

    public function updatedSubject(): void
    {
        $this->resetPage();
    }

    public function tagStyle(string $type, int $id): string
    {
        $colors = [
            'site' => ['#f1f5f9', '#eff6ff', '#eef2ff', '#ecfeff', '#f0fdfa', '#ecfdf5'],
            'subject' => ['#fffbeb', '#fff7ed', '#fff1f2', '#fdf4ff', '#f5f3ff', '#f7fee7', '#ecfeff', '#ecfdf5', '#f0f9ff', '#fef2f2'],
        ];
        $list = $colors[$type] ?? ['#f3f4f6'];

        return 'background-color: '.$list[$id % count($list)].';';
    }

    public function render()
    {
        $query = Publication::active()
            ->with(['sites.page', 'subjects.page'])
            ->when($this->year !== '', fn ($query) => $query->where('year', $this->year))
            ->when($this->site !== '', fn ($query) => $query->whereHas(
                'sites', fn ($siteQuery) => $siteQuery->where('sites.id', (int) $this->site)
            ))
            ->when($this->subject !== '', fn ($query) => $query->whereHas(
                'subjects', fn ($subjectQuery) => $subjectQuery->where('subjects.id', $this->subject)
            ));

        return view('livewire.web.publication-list', [
            'publications' => $query->latestFirst()->paginate(30),
            'years' => Publication::active()->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'sites' => Site::query()->where('is_active', true)->with('page')->orderBy('sort_order')->get(),
            'subjects' => Subject::query()->where('is_active', true)->with('page')->orderBy('sort_order')->get(),
        ]);
    }
}
