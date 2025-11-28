<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use App\Models\Web\Page;
use App\Models\Web\ContentBlock;

class Index extends Component
{
    public $plots=['fushan', 'nanjenshan', 'shoushan'];
    public $indexIntro;

    public function mount(): void
    {
        // 1. 先找到該頁
        $page = Page::where('slug', 'index')->firstOrFail();

        // 2. 載入該頁的內容區塊
        $this->indexIntro = $page->blocks()->where('block_type','intro')->first();
        
        // dd($this->indexIntro);

    }

    public function render()
    {
        return view('livewire.web.index');
    }
}
