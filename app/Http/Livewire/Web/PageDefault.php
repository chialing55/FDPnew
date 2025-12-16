<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use App\Models\Web\Page;
use App\Models\Web\Subject;
use App\Models\Web\ContentBlock;
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
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        $slugGrpoup=explode('/', $slug);
        
        if ($slugGrpoup[0] === 'sites' ) {
            // 針對動態樣區頁面，加入預設的內容區塊
            $site = $slugGrpoup[1];
            $additionalBlocks = $this->siteContent($site);
            $blocks = $blocks->concat($additionalBlocks);
            // 
        }

        if ($slugGrpoup[0] === 'subjects' ) {
            // 針對動態主題頁面，加入預設的內容區塊
            // $subjectSlug = $slugGrpoup[1];
            $additionalBlocks = $this->subjectContent($slug);
            $blocks = $additionalBlocks->concat($blocks);
            // 
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

    public function siteContent($site): Collection
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
                    'currentSite' => $site,   // 傳給這個 block 的參數
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
                    'site' => $site,   // 傳給這個 block 的參數
                    
                ],
            ]),
            new ContentBlock([
                'id'          => null,
                'title_zh_tw' => '研究計畫',
                'title_en'    => 'Research projects',
                'body_zh_tw'  => 'Sample body content.',
                'body_en'     => 'Sample body content.',
                'view'        => '', // 可選，指定 Blade 視圖
            ]),
            new ContentBlock([
                'id'          => null,
                'title_zh_tw' => '文章發表',
                'title_en'    => 'Research publications',
                'body_zh_tw'  => 'Sample body content.',
                'body_en'     => 'Sample body content.',
                'view'        => '', // 可選，指定 Blade 視圖
            ]),
        ]);
    }

    public function subjectContent($slug): Collection
    {
        $blocks = Subject::whereHas('page', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->firstOrFail();

        if ($blocks) {
            return collect([
                new ContentBlock([
                    'id'          => null, // 或給個虛擬 id，如 'site-team'
                    'title_zh_tw' => '簡介',
                    'title_en'    => 'Introduction',
                    'body_zh_tw'  => $blocks->description_zh_tw,
                    'body_en'     => $blocks->description_en,
                    'view'        => '', // 可選，指定 Blade 視圖
                    'params'      => '',
                ]),
                new ContentBlock([
                    'id'          => null, // 或給個虛擬 id，如 'site-team'
                    'title_zh_tw' => '研究方法',
                    'title_en'    => 'Methods',
                    'body_zh_tw'  => $blocks->method_zh_tw,
                    'body_en'     => $blocks->method_en,
                    'view'        => '', // 可選，指定 Blade 視圖
                    'params'      => '',
                ]),
                new ContentBlock([
                    'id'          => null,
                    'title_zh_tw' => '基礎成果',
                    'title_en'    => 'Research Results',
                    'body_zh_tw'  => '',
                    'body_en'     => '',
                    'view'        => 'web.research-output-list', // 可選，指定 Blade 視圖
                    'params'      => [
                        'subject' => $blocks->id,   // 傳給這個 block 的參數
                        
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
