<?php

namespace App\Http\Livewire\Web;

use App\Http\Livewire\Web\Concerns\InteractsWithSiteSubjectFilters;
use App\Models\Web\Project;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectList extends Component
{
    use InteractsWithSiteSubjectFilters;
    use WithPagination;

    public int $perPage = 50;

    public function mount(): void
    {
        $this->initializeSiteSubjectFilters();
    }

    public function render()
    {
        $siteId = $this->selectedSiteId();
        $subjectId = $this->selectedSubjectId();

        $projects = Project::query()
            ->where('is_active', 1)
            ->select('projects.*')
            ->with([
                'subjects:id,short_name_zh_tw,short_name_en,name_zh_tw,name_en,page_id',
                'sites:id,name_zh_tw,name_en,page_id',
            ])
            ->when($subjectId, fn ($q) => $q->whereHas('subjects', fn ($qq) => $qq->where('subjects.id', $subjectId))
            )
            ->when($siteId, fn ($q) => $q->whereHas('sites', fn ($qq) => $qq->where('sites.id', $siteId))
            )
            ->orderByRaw('start_date IS NULL')
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        $locale = app()->getLocale();
        $projects->getCollection()->transform(function ($p) use ($locale) {
            $p->title = $locale === 'en'
                ? ($p->title_en ?: $p->title_zh_tw)
                : ($p->title_zh_tw ?: $p->title_en);

            return $p;
        });

        return view('livewire.web.project-list', [
            'projects' => $projects,
        ]);
    }
}
