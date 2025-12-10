<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use App\Models\Web\Page;
use App\Models\Web\ContentBlock;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class PageDefault extends Component
{

    public string $slug;
    public $page;
    public $contentBlocks;

    public function mount($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $blocks = $page->contentBlocks()
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->get();

        $slugGrpoup=explode('/', $slug);
        
        if ($slugGrpoup[0] === 'sites' ) {
            // 針對動態樣區頁面，加入預設的內容區塊
            $additionalBlocks = $this->siteContent($slug);
            $blocks = $blocks->concat($additionalBlocks);
            
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

    public function siteContent($slug): Collection
    {
        return collect([
            new ContentBlock([
                'id'          => null, // 或給個虛擬 id，如 'site-team'
                'title_zh_tw' => '參與團隊',
                'title_en'    => 'Team Members',
                'body_zh_tw'  => 'Sample body content.',
                'body_en'     => 'Sample body content.',
                'view'        => '', // 可選，指定 Blade 視圖
            ]),
            new ContentBlock([
                'id'          => null,
                'title_zh_tw' => '基礎成果',
                'title_en'    => 'Research Results',
                'body_zh_tw'  => 'Sample body content.',
                'body_en'     => 'Sample body content.',
                'view'        => '', // 可選，指定 Blade 視圖
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


    public function render()
    {
        return view('livewire.web.page-default');
    }
}
