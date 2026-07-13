<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
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
            $this->hero = str_starts_with($this->page->hero_image, 'library:')
                ? Storage::disk('home_hero')->url(substr($this->page->hero_image, strlen('library:')))
                : Storage::disk('public')->url($this->page->hero_image);
        } else {
            $this->hero = $this->pickRandomHero();
        }

        // $this->hero = $this->pickRandomHero();
    } 

    protected function pickRandomHero(): string
    {
        $files = collect(Storage::disk('home_hero')->files())
            ->filter(fn (string $file): bool => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp','gif'], true))
            ->values()
            ->all();

        if (empty($files)) {
            return '';
        }

        // 隨機取一張
        return Storage::disk('home_hero')->url(Arr::random($files));
    }

    public function render()
    {
        return view('livewire.web.page-background-hero');
    }
}
