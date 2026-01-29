<?php
namespace App\Http\Livewire\Web;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Web\ResearchOutput;
use App\Models\Web\Site;
use App\Models\Web\Subject;
use Illuminate\Support\Facades\DB;

class ResearchOutputList extends Component
{
    use WithPagination;

    public array $params = [];

    public ?string $subject = null; // 可為 id 或 slug
    public ?string $site = null;    // 可為 id 或 name_en/slug

    public bool $showSiteTags = true;
    public bool $showSubjectTags = true;

    public bool $showSiteFilter = true;
    public bool $showSubjectFilter = true;

    public bool $showFilters = false;

    public array $siteOptions = [];     // [id => name]
    public array $subjectOptions = [];  // [id => name]


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

    private function boolParam($v, bool $default): bool
    {
        if ($v === null) return $default;
        if (is_bool($v)) return $v;
        $v = strtolower(trim((string) $v));
        if (in_array($v, ['1','true','yes','y','on'], true)) return true;
        if (in_array($v, ['0','false','no','n','off'], true)) return false;
        return $default;
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

        // 你之前用 name_en 找 fushan，我保留這個
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
                '#fffbeb', // amber-50
                '#fff7ed', // orange-50
                '#fff1f2', // rose-50
                '#fdf4ff', // fuchsia-50
                '#f5f3ff', // violet-50
                '#f7fee7', // lime-50
                '#ecfeff', // cyan-50
                '#ecfdf5', // emerald-50
                '#f0f9ff', // sky-50
                '#fef2f2', // red-50（很淡）
            ],
        ];

        $list = $colors[$type] ?? ['#f3f4f6'];
        $bg = $list[$id % count($list)];
        return "background-color: {$bg};";
    }
//篩選器


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
            ->select('subjects.id', 'subjects.name_zh_tw', 'subjects.name_en')
            ->get()
            ->mapWithKeys(function ($r) use ($locale) {
                $label = $locale === 'en'
                    ? ($r->name_en ?: $r->name_zh_tw)
                    : ($r->name_zh_tw ?: $r->name_en);

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
        $this->site = null;
        $this->subject = null;
        $this->resetPage();
    }



    public function render()
    {
        $siteId = $this->site && ctype_digit($this->site) ? (int) $this->site : $this->resolveSiteId($this->site);
        $subjectId = $this->subject && ctype_digit($this->subject) ? (int) $this->subject : $this->resolveSubjectId($this->subject);


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
            ->where('is_public', 1)
            ->select('research_outputs.*')
            ->selectSub($siteSortSubquery, 'site_nav_order')
            ->selectSub($subjectSortSubquery, 'subject_nav_order')
            ->with([
                'subjects:id,name_zh_tw,page_id,name_en',
                'sites:id,name_zh_tw,name_en',
            ])
            ->when($subjectId, fn ($q) =>
                $q->whereHas('subjects', fn ($qq) => $qq->where('subjects.id', $subjectId))
            )
            ->when($siteId, fn ($q) =>
                $q->whereHas('sites', fn ($qq) => $qq->where('sites.id', $siteId))
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
