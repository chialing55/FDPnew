<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
    public $segment1;
    public $segment2;

    public function mount($slug, $page): void
    {
        $this->page = $page;
        $this->slug = $slug;
        $this->title = $this->page->title;
        // $this->title = PageTitleHelper::getTranslatedTitle(); 
        // dd($this->title);

        $this->segment1  = request()->segment(1);  // 'background'
        $this->segment2 = request()->segment(2);  // 'background'


        if ($this->page->nav_group) {
            $pageNavLabel = __('web.nav_'.$this->page->nav_group.'');
            $pageNavUrl = '';
        } else {
            $pageNavLabel = __('web.nav_results');
            $pageNavUrl = '/results';
        }

        $breadcrumbs[] = ['label' => __('web.nav_home'), 'url' => '/'];
        $breadcrumbs[] = ['label' => $pageNavLabel, 'url' => $pageNavUrl];
        $breadcrumbs[] = ['label' => __(''.$this->page->title.''), 'url' => ''];

        $this->breadcrumbs = $breadcrumbs;

        if($this->page->hero_image){
            $this->hero = Storage::url($this->page->hero_image);
        } else {
            $this->hero = asset('images/hero/' . $this->pickRandomHero());
        }

        // $this->hero = $this->pickRandomHero();
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
