<?php

namespace App\Http\Livewire\Web\Concerns;

use App\Models\Web\Site;
use App\Models\Web\Subject;

trait InteractsWithSiteSubjectFilters
{
    public ?string $site = null;

    public ?string $subject = null;

    public bool $showSiteTags = true;

    public bool $showSubjectTags = true;

    public bool $showSiteFilter = true;

    public bool $showSubjectFilter = true;

    public array $siteOptions = [];

    public array $subjectOptions = [];

    protected function initializeSiteSubjectFilters(): void
    {
        $this->site = $this->normalizedFilterId($this->site);
        $this->subject = $this->normalizedFilterId($this->subject);
        $this->showSiteTags = $this->showSiteFilter = $this->site === null;
        $this->showSubjectTags = $this->showSubjectFilter = $this->subject === null;

        if ($this->showSiteFilter) {
            $this->siteOptions = $this->siteFilterOptions();
        }
        if ($this->showSubjectFilter) {
            $this->subjectOptions = $this->subjectFilterOptions();
        }
    }

    protected function selectedSiteId(): ?int
    {
        return $this->site !== null ? (int) $this->site : null;
    }

    protected function selectedSubjectId(): ?int
    {
        return $this->subject !== null ? (int) $this->subject : null;
    }

    public function updatedSite(): void
    {
        $this->site = $this->normalizedFilterId($this->site);
        $this->resetPage();
    }

    public function updatedSubject(): void
    {
        $this->subject = $this->normalizedFilterId($this->subject);
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        if ($this->showSiteFilter) {
            $this->site = null;
        }
        if ($this->showSubjectFilter) {
            $this->subject = null;
        }
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

    private function normalizedFilterId(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function siteFilterOptions(): array
    {
        return Site::query()->where('sites.is_active', true)
            ->join('pages', 'pages.id', '=', 'sites.page_id')
            ->where('pages.nav_group', 'sites')->orderBy('pages.nav_order')
            ->select('sites.*')->get()
            ->mapWithKeys(fn (Site $site): array => [(string) $site->id => $site->name])->all();
    }

    private function subjectFilterOptions(): array
    {
        return Subject::query()->where('subjects.is_active', true)
            ->join('pages', 'pages.id', '=', 'subjects.page_id')
            ->where('pages.nav_group', 'subjects')->orderBy('pages.nav_order')
            ->select('subjects.*')->get()
            ->mapWithKeys(fn (Subject $subject): array => [
                (string) $subject->id => $subject->short_name ?: $subject->name,
            ])->all();
    }
}
