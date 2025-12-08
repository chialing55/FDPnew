<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use App\Helpers\PageTitleHelper;
use App\Models\Web\Page;

class PageBackgroundHero extends Component
{

    public string $hero = '';
    public $plants = '';
    public $censuses = '';
    public string $title = '';
    public array $breadcrumbs = [];
    public string $slug;
    public $page;

    public function mount($slug): void
    {
        $this->page = Page::where('slug', $slug)->firstOrFail();
        $this->title = $this->page->title;
        // $this->title = PageTitleHelper::getTranslatedTitle(); 
        // dd($this->title);

        $breadcrumbs[] = ['label' => __('web.nav_home'), 'url' => '/'];
        $breadcrumbs[] = ['label' => __('web.nav_'.$this->page->nav_group.''), 'url' => ''];
        $breadcrumbs[] = ['label' => __(''.$this->page->title.''), 'url' => ''];

        $this->breadcrumbs = $breadcrumbs;

        $this->hero = $this->pickRandomHero();
    } 

    protected function pickRandomHero(): string
    {
        $dir = public_path('images/hero');

        if (! is_dir($dir)) {
            return ''; // 目錄不存在就不顯示
        }

        // 只取常見圖片副檔名
        $files = collect(File::files($dir))
            ->filter(function ($f) {
                return in_array(strtolower($f->getExtension()), ['jpg','jpeg','png','webp','gif','JPG']);
            })
            ->map(fn ($f) => $f->getFilename())
            ->values()
            ->all();

        if (empty($files)) {
            return '';
        }

        // 隨機取一張
        return Arr::random($files);
    }

    public function render()
    {
        return view('livewire.web.page-background-hero');
    }
}
