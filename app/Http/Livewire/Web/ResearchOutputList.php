<?php

namespace App\Http\Livewire\Web;

use App\Http\Livewire\Web\Concerns\InteractsWithSiteSubjectFilters;
use App\Models\Web\ResearchOutput;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ResearchOutputList extends Component
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

        /**
         * 依 subjects 對應的 pages.nav_order（nav_group=subjects）
         * 取「最小 nav_order」作為該成果的排序值
         */
        $subjectSortSubquery = DB::connection('mysql_web')->table('subjects')
            ->join('research_output_subject', 'research_output_subject.subject_id', '=', 'subjects.id')
            ->join('pages', function ($join) {
                $join->on('pages.id', '=', 'subjects.page_id')
                    ->where('pages.nav_group', '=', 'subjects');
            })
            ->whereColumn('research_output_subject.research_output_id', 'research_outputs.id')
            ->selectRaw('MIN(pages.nav_order)');

        $siteSortSubquery = DB::connection('mysql_web')->table('sites')
            ->join('research_output_site', 'research_output_site.site_id', '=', 'sites.id')
            ->join('pages', function ($join) {
                $join->on('pages.id', '=', 'sites.page_id')
                    ->where('pages.nav_group', '=', 'sites');
            })
            ->whereColumn('research_output_site.research_output_id', 'research_outputs.id')
            ->selectRaw('MIN(pages.nav_order)');

        $outputs = ResearchOutput::query()
            ->where('is_active', 1)
            ->select('research_outputs.*')
            ->selectSub($siteSortSubquery, 'site_nav_order')
            ->selectSub($subjectSortSubquery, 'subject_nav_order')
            ->with([
                'subjects:id,short_name_zh_tw,short_name_en,name_zh_tw,name_en,page_id',
                'sites:id,name_zh_tw,name_en',
            ])
            ->when($subjectId, fn ($q) => $q->whereHas('subjects', fn ($qq) => $qq->where('subjects.id', $subjectId))
            )
            ->when($siteId, fn ($q) => $q->whereHas('sites', fn ($qq) => $qq->where('sites.id', $siteId))
            )
            ->orderByRaw('site_nav_order IS NULL')
            ->orderBy('site_nav_order')
            ->orderByRaw('subject_nav_order IS NULL') // 沒 subject 的排後面
            ->orderBy('subject_nav_order')
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        return view('livewire.web.research-output-list', [
            'outputs' => $outputs,
        ]);
    }
}
