<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Web\Project;
use App\Models\Web\Site;
use App\Models\Web\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProjectList extends Component
{
    use WithPagination;

    public ?string $subject = null; // id 或 slug
    public ?string $site = null;    // id 或 name_en

    public bool $showSiteTags = true;
    public bool $showSubjectTags = true;
    public bool $showFilters = false;

    public bool $showSiteFilter = true;
    public bool $showSubjectFilter = true;

    public array $siteOptions = [];
    public array $subjectOptions = [];

    public int $perPage = 50;

    public function mount(): void
    {
        $this->site = $this->blankToNull($this->site);
        $this->subject = $this->blankToNull($this->subject);

        // tag 顯示邏輯（你原本的是對的）
        $this->showSiteTags    = $this->site === null;
        $this->showSubjectTags = $this->subject === null;

        // ⭐ 篩選器顯示邏輯（新）
        $this->showSiteFilter    = $this->site === null;
        $this->showSubjectFilter = $this->subject === null;

        // 只要「至少有一個篩選器會顯示」，就需要 options
        if ($this->showSiteFilter || $this->showSubjectFilter) {
            if ($this->showSiteFilter) {
                $this->siteOptions = $this->getSiteOptions();
            }

            if ($this->showSubjectFilter) {
                $this->subjectOptions = $this->getSubjectOptions();
            }
        }
    }


    private function blankToNull($v): ?string
    {
        if ($v === null) return null;
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    private function resolveSubjectId(?string $subject): ?int
    {
        if (!$subject) return null;
        if (ctype_digit($subject)) return (int) $subject;
        return Subject::where('slug', $subject)->value('id');
    }

    private function resolveSiteId(?string $site): ?int
    {
        if (!$site) return null;
        if (ctype_digit($site)) return (int) $site;

        // 你之前用 name_en 找 fushan，我延用
        return Site::whereRaw('LOWER(name_en) = ?', [strtolower($site)])->value('id');
    }

    /**
     * 標籤顏色（同一個 id 永遠同色）
     */
    public function tagStyle(string $type, int $id): string
    {
        $colors = [
            'site' => ['#f1f5f9', '#eff6ff', '#eef2ff', '#ecfeff', '#f0fdfa', '#ecfdf5'],
            'subject' => [
                '#fffbeb', '#fff7ed', '#fff1f2', '#fdf4ff', '#f5f3ff',
                '#f7fee7', '#ecfeff', '#ecfdf5', '#f0f9ff', '#fef2f2',
            ],
        ];

        $list = $colors[$type] ?? ['#f3f4f6'];
        $bg = $list[$id % count($list)];
        return "background-color: {$bg};";
    }

    // 篩選器 options（照你 research-output-list 的寫法）
    private function getSiteOptions(): array
    {
        $locale = app()->getLocale();

        return DB::connection('mysql_web')->table('sites')
            ->join('pages', function ($join) {
                $join->on('pages.id', '=', 'sites.page_id')
                    ->where('pages.nav_group', '=', 'sites');
            })
            ->orderBy('pages.nav_order')
            ->select('sites.id', 'sites.name_zh_tw', 'sites.name_en')
            ->get()
            ->mapWithKeys(function ($r) use ($locale) {
                $label = $locale === 'en'
                    ? ($r->name_en ?: $r->name_zh_tw)
                    : ($r->name_zh_tw ?: $r->name_en);

                return [(string) $r->id => $label];
            })
            ->toArray();
    }

    private function getSubjectOptions(): array
    {
        $locale = app()->getLocale();

        return DB::connection('mysql_web')->table('subjects')
            ->join('pages', function ($join) {
                $join->on('pages.id', '=', 'subjects.page_id')
                    ->where('pages.nav_group', '=', 'subjects');
            })
            ->orderBy('pages.nav_order')
            ->select('subjects.id', 'subjects.short_name_zh_tw', 'subjects.short_name_en', 'subjects.name_zh_tw', 'subjects.name_en')
            ->get()
            ->mapWithKeys(function ($r) use ($locale) {
                $label = $locale === 'en'
                    ? ($r->short_name_en ?: $r->name_en ?: $r->short_name_zh_tw ?: $r->name_zh_tw)
                    : ($r->short_name_zh_tw ?: $r->name_zh_tw ?: $r->short_name_en ?: $r->name_en);

                return [(string) $r->id => $label];
            })
            ->toArray();
    }

    public function updatedSite(): void
    {
        $this->site = $this->blankToNull($this->site);
        $this->resetPage();
    }

    public function updatedSubject(): void
    {
        $this->subject = $this->blankToNull($this->subject);
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

    public function render()
    {
        $siteId = $this->site && ctype_digit($this->site) ? (int) $this->site : $this->resolveSiteId($this->site);
        $subjectId = $this->subject && ctype_digit($this->subject) ? (int) $this->subject : $this->resolveSubjectId($this->subject);

        /**
         * ✅ 依 sites / subjects 對應 pages.nav_order 來排序
         * 注意：這裡 pivot 我先假設是：
         * - project_site (project_id, site_id)
         * - project_subject (project_id, subject_id)
         * 如果你不是這個名字，改掉表名即可。
         */
        $subjectSortSubquery = DB::connection('mysql_web')->table('subjects')
            ->join('project_subject', 'project_subject.subject_id', '=', 'subjects.id')
            ->join('pages', function ($join) {
                $join->on('pages.id', '=', 'subjects.page_id')
                    ->where('pages.nav_group', '=', 'subjects');
            })
            ->whereColumn('project_subject.project_id', 'projects.id')
            ->selectRaw('MIN(pages.nav_order)');

        $siteSortSubquery = DB::connection('mysql_web')->table('sites')
            ->join('project_site', 'project_site.site_id', '=', 'sites.id')
            ->join('pages', function ($join) {
                $join->on('pages.id', '=', 'sites.page_id')
                    ->where('pages.nav_group', '=', 'sites');
            })
            ->whereColumn('project_site.project_id', 'projects.id')
            ->selectRaw('MIN(pages.nav_order)');

        $projects = Project::query()
            ->where('is_active', 1)
            ->select('projects.*')
            ->selectSub($siteSortSubquery, 'site_nav_order')
            ->selectSub($subjectSortSubquery, 'subject_nav_order')
            ->with([
                'subjects:id,short_name_zh_tw,short_name_en,name_zh_tw,name_en,page_id',
                'sites:id,name_zh_tw,name_en,page_id',
            ])
            ->when($subjectId, fn ($q) =>
                $q->whereHas('subjects', fn ($qq) => $qq->where('subjects.id', $subjectId))
            )
            ->when($siteId, fn ($q) =>
                $q->whereHas('sites', fn ($qq) => $qq->where('sites.id', $siteId))
            )
            ->orderByRaw('start_date IS NULL')
            ->orderBy('start_date', 'desc')
            // ->orderByRaw('site_nav_order IS NULL')
            // ->orderBy('site_nav_order')
            // ->orderByRaw('subject_nav_order IS NULL')
            // ->orderBy('subject_nav_order')
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        // ✅ 多語標題（跟你 output 那邊一致）
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
