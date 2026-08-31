<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use App\Models\Web\Page;
use App\Models\Web\ContentBlock;
use App\Models\Web\ContentBlockItem;
use App\Models\Web\ResearchOutput;
use App\Models\Web\Project;
use Illuminate\Support\Str;
use App\Support\Web\RelatedContentBlockFactory;
use App\Support\Web\RelatedTagStyle;

class PageDefault extends Component
{

    public string $slug;
    public $page;
    public $contentBlocks;

    public function mount($page, $slug)
    {
        $this->page = $page;
        // dd($slug);
        $blocks = $page->contentBlocks()
            ->with(['items' => fn ($query) => $query->where('is_public', true)->orderBy('sort_order')])
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        if ($page instanceof Project) {
            $blocks->prepend($this->projectInformationBlock($page));
        }

        // 成果頁面的自訂內容之後，固定顯示研究計畫與學術產出。
        if ($page instanceof ResearchOutput) {
            $blocks = $blocks->concat(RelatedContentBlockFactory::forResearchOutput(
                $page->sites()->value('sites.id'),
                $page->subjects()->value('subjects.id'),
            ));
        }

        $slugGroup = explode('/', $slug);
        
        if ($slugGroup[0] === 'sites') {
            // 樣區 slug 只在頁面入口解析一次；所有子元件一律接收 site ID。
            $siteId = $page instanceof Page
                ? $page->site()->value('id')
                : null;
            abort_unless($siteId, 404);
            $blocks->push($this->fixedViewBlock('參與團隊', 'Team Members', 'web.site-teams-block', [
                'currentSite' => (string) $siteId,
            ]));
            $blocks = $blocks->concat(RelatedContentBlockFactory::forSite((int) $siteId));
            // 
        }

        if ($slugGroup[0] === 'subjects') {
            // 研究主題的簡介與方法由 content_blocks 管理，動態成果區塊接在後方。
            $subjectId = $page instanceof Page
                ? $page->subject()->where('is_active', true)->value('id')
                : null;
            abort_unless($subjectId, 404);
            $blocks = $blocks->concat(RelatedContentBlockFactory::forSubject((int) $subjectId));
        }

        if (($slug ?? null) === 'publications') {
            $blocks->push($this->fixedViewBlock(null, null, 'web.publication-list'));
        }

        if (($slug ?? null) === 'projects') {
            $blocks->push($this->fixedViewBlock(null, null, 'web.project-list'));
        }

        if (($slug ?? null) === 'about/news') {
            $blocks->push($this->fixedViewBlock(null, null, 'web.news-list'));
        }

        if (($slug ?? null) === 'about/team') {
            $blocks = $blocks->concat([
                $this->fixedViewBlock('樣區負責人', 'Site Manager', 'web.site-teams-block', [
                    'currentRole' => 'plot_manager',
                ]),
                $this->fixedViewBlock('合作單位', 'Cooperative unit', 'web.site-teams-block', [
                    'currentRole' => 'team_partner',
                ]),
            ]);
        }

        if (($slug ?? null) === 'results') {
            $blocks->push($this->fixedViewBlock(
                null,
                null,
                'web.research-output-list'
            ));
        }

        // dd($blocks->toArray());

        foreach ($blocks as $block) {
            $block->anchorId = Str::slug(
                $block->title_en ?? ('section-' . ($block->id ?? uniqid()))
            );
        }

        // dd($blocks->toArray());
            
        $this->contentBlocks = $blocks;
    }

    private function fixedViewBlock(?string $titleZhTw, ?string $titleEn, string $view, array $params = []): ContentBlock
    {
        return ContentBlock::systemBlock([
            'title_zh_tw' => $titleZhTw,
            'title_en' => $titleEn,
            'view' => $view,
            'params' => $params,
            'is_public' => true,
        ]);
    }

    private function projectInformationBlock(Project $project): ContentBlock
    {
        $project->loadMissing(['sites.page', 'subjects.page']);

        $block = new ContentBlock([
            'title_zh_tw' => '計畫資訊',
            'title_en' => 'Project Information',
            'sort_order' => -1,
            'is_public' => true,
        ]);

        $block->setRelation('items', collect([
            new ContentBlockItem([
                'type' => 'text',
                'body_zh_tw' => $this->projectInformationHtml($project, false),
                'body_en' => $this->projectInformationHtml($project, true),
                'is_public' => true,
            ]),
        ]));

        return $block;
    }

    private function projectInformationHtml(Project $project, bool $english): string
    {
        $rows = [];
        $period = collect([$project->start_date, $project->end_date])->filter()->implode(' – ');

        if ($period !== '') {
            $rows[] = [$english ? 'Project period' : '執行期間', e($period)];
        }

        $pi = $english
            ? ($project->pi_en ?: $project->pi_zh_tw)
            : ($project->pi_zh_tw ?: $project->pi_en);
        if (filled($pi)) {
            $rows[] = [$english ? 'Principal investigator' : '主持人', e($pi)];
        }

        $agency = $english
            ? ($project->funding_agency_en ?: $project->funding_agency_zh_tw)
            : ($project->funding_agency_zh_tw ?: $project->funding_agency_en);
        if (filled($agency)) {
            $rows[] = [$english ? 'Funding agency' : '補助單位', e($agency)];
        }

        $sites = $project->sites
            ->sortBy(fn ($site) => [$site->page?->nav_order ?? PHP_INT_MAX, $site->id])
            ->map(fn ($site): string => $this->projectInformationTag(
                $site->page?->slug,
                $english ? ($site->name_en ?: $site->name_zh_tw) : ($site->name_zh_tw ?: $site->name_en),
                RelatedTagStyle::for('site', $site->id),
            ))->implode('');
        if ($sites !== '') {
            $rows[] = [$english ? 'Forest dynamics plots' : '動態樣區', '<div class="flex flex-wrap gap-2">'.$sites.'</div>'];
        }

        $subjects = $project->subjects
            ->sortBy(fn ($subject) => [$subject->page?->nav_order ?? PHP_INT_MAX, $subject->id])
            ->map(fn ($subject): string => $this->projectInformationTag(
                $subject->page?->slug,
                $english
                    ? ($subject->short_name_en ?: $subject->name_en ?: $subject->name_zh_tw)
                    : ($subject->short_name_zh_tw ?: $subject->name_zh_tw ?: $subject->name_en),
                RelatedTagStyle::for('subject', $subject->id),
            ))->implode('');
        if ($subjects !== '') {
            $rows[] = [$english ? 'Research subjects' : '研究主題', '<div class="flex flex-wrap gap-2">'.$subjects.'</div>'];
        }

        if (filled($project->website_url)) {
            $label = $english ? 'Project website' : '計畫網站';
            $url = e($project->website_url);
            $rows[] = [$label, '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>'];
        }

        if ($rows === []) {
            return '<p class="text-gray-500">' . ($english ? 'No project information available.' : '目前尚無計畫資訊。') . '</p>';
        }

        return '<dl class="grid gap-x-6 gap-y-3 sm:grid-cols-[8rem_minmax(0,1fr)]">'
            . collect($rows)->map(fn (array $row): string => '<dt class="font-semibold text-gray-700">' . e($row[0]) . '</dt><dd class="min-w-0 break-words">' . $row[1] . '</dd>')->implode('')
            . '</dl>';
    }

    private function projectInformationTag(?string $slug, ?string $label, string $style): string
    {
        $content = e((string) $label);
        $classes = RelatedTagStyle::classes();

        if (filled($slug)) {
            return '<a href="'.e(url('/'.$slug)).'" style="'.e($style).'" class="'.$classes.'">'.$content.'</a>';
        }

        return '<span style="'.e($style).'" class="'.$classes.'">'.$content.'</span>';
    }

    public function render()
    {
        return view('livewire.web.page-default');
    }
}
