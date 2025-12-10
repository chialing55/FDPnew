<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use App\Models\Web\Page;
use App\Models\Web\Site;
use App\Models\Web\ContentBlock;
use Illuminate\Support\Str;

class Index extends Component
{
    public $plots=['fushan', 'nanjenshan', 'shoushan'];
    public $indexIntro;
    public $plotsContent=[];

    public function mount(): void
    {
        // 1. 先找到該頁
        $page = Page::where('slug', 'index')->firstOrFail();

        // 2. 載入該頁的內容區塊
        $this->indexIntro = $page->contentBlocks()->where('block_type','intro')->first();


        // 3. 載入各樣區的內容區塊
        foreach ($this->plots as $plot) {
            $slug='sites/'.$plot;
            // dd($slug);
            $plotBlock = Page::where('slug', $slug)->firstOrFail();
            $plotIntro = $plotBlock->site;
            
               
            $this->plotsContent[$plot]['title'] = $plotBlock->title;
            $this->plotsContent[$plot]['intro'] = $plotIntro->description ?? ''; 
            $this->plotsContent[$plot]['slug'] = $plotBlock->slug;
        }
        
        // dd($this->indexIntro);

    }



    public function render()
    {
        return view('livewire.web.index');
    }
}
