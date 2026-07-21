<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use App\Models\Web\Page;
use App\Models\Web\Subject;
use App\Models\Web\ContentBlock;
use App\Models\Web\ContentBlockItem;
use App\Models\Web\ResearchOutput;
use App\Models\Web\Project;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

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

        // 成果頁面的自訂內容之後，固定顯示研究計畫與文章發表。
        if ($page instanceof ResearchOutput) {
            $blocks = $blocks->concat($this->researchOutputContent($page));
        }

        $slugGrpoup=explode('/', $slug);
        
        if ($slugGrpoup[0] === 'sites' ) {
            // 樣區 slug 只在頁面入口解析一次；所有子元件一律接收 site ID。
            $siteId = $page instanceof Page
                ? $page->site()->value('id')
                : null;
            abort_unless($siteId, 404);
            $additionalBlocks = $this->siteContent((int) $siteId);
            $blocks = $blocks->concat($additionalBlocks);
            // 
        }

        if ($slugGrpoup[0] === 'subjects' ) {
            // 研究主題的簡介與方法由 content_blocks 管理，動態成果區塊接在後方。
            $additionalBlocks = $this->subjectContent($slug);
            $blocks = $blocks->concat($additionalBlocks);
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

    private function researchOutputContent(ResearchOutput $output): Collection
    {
        $site = $output->sites()->value('sites.id');
        $subject = $output->subjects()->value('subjects.id');

        return collect([
            new ContentBlock([
                'title_zh_tw' => '研究計畫',
                'title_en' => 'Research Projects',
                'body_zh_tw' => '',
                'body_en' => '',
                'view' => 'web.project-list',
                'params' => array_filter([
                    'site' => $site ? (string) $site : null,
                    'subject' => $subject ? (string) $subject : null,
                ]),
            ]),
            new ContentBlock([
                'title_zh_tw' => '文章發表',
                'title_en' => 'Research Publications',
                'body_zh_tw' => '',
                'body_en' => '',
                'view' => '',
            ]),
        ]);
    }

    public function siteContent(int $siteId): Collection
    {
        return collect([
            new ContentBlock([
                'id'          => null, // 或給個虛擬 id，如 'site-team'
                'title_zh_tw' => '參與團隊',
                'title_en'    => 'Team Members',
                'body_zh_tw'  => '',
                'body_en'     => '',
                'view'        => 'web.site-teams-block', // 可選，指定 Blade 視圖
                'params'      => [
                    'currentSite' => (string) $siteId,
                ],
            ]),
            new ContentBlock([
                'id'          => null,
                'title_zh_tw' => '基礎成果',
                'title_en'    => 'Research Results',
                'body_zh_tw'  => '',
                'body_en'     => '',
                'view'        => 'web.research-output-list', // 可選，指定 Blade 視圖
                'params'      => [
                    'site' => (string) $siteId,
                    
                ],
            ]),
            new ContentBlock([
                'id'          => null,
                'title_zh_tw' => '研究計畫',
                'title_en'    => 'Research projects',
                'body_zh_tw'  => '',
                'body_en'     => '',
                'view'        => 'web.project-list', // 可選，指定 Blade 視圖
                'params'      => [
                    'site' => (string) $siteId,
                    
                ],
            ]),
            new ContentBlock([
                'id'          => null,
                'title_zh_tw' => '文章發表',
                'title_en'    => 'Research publications',
                'body_zh_tw'  => '',
                'body_en'     => '',
                'view'        => 'web.publication-list',
                'params'      => [
                    'site' => (string) $siteId,
                    'showFilters' => false,
                ],
            ]),
        ]);
    }

    public function subjectContent($slug): Collection
    {
        $blocks = Subject::whereHas('page', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->where('is_active', true)
            ->firstOrFail();

        if ($blocks) {
            return collect([
                new ContentBlock([
                    'id'          => null,
                    'title_zh_tw' => '研究成果',
                    'title_en'    => 'Research Results',
                    'body_zh_tw'  => '',
                    'body_en'     => '',
                    'view'        => 'web.research-output-list', // 可選，指定 Blade 視圖
                    'params'      => [
                        'subject' => $blocks->id,   // 傳給這個 block 的參數
                    ],
                ]),
                new ContentBlock([
                    'id'          => null,
                    'title_zh_tw' => '研究計畫',
                    'title_en'    => 'Research Projects',
                    'body_zh_tw'  => '',
                    'body_en'     => '',
                    'view'        => 'web.project-list',
                    'params'      => [
                        'subject' => $blocks->id,
                    ],
                ]),
                new ContentBlock([
                    'id'          => null,
                    'title_zh_tw' => '文章發表',
                    'title_en'    => 'Research Publications',
                    'body_zh_tw'  => '',
                    'body_en'     => '',
                    'view'        => 'web.publication-list',
                    'params'      => [
                        'subject' => (string) $blocks->id,
                        'showFilters' => false,
                    ],
                ]),
            ]);
        } else {
            return collect([]);
        }




    }


    public function render()
    {
        return view('livewire.web.page-default');
    }
}
