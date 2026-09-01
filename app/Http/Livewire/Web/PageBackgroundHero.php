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
    public ?string $titleZhTw = null;
    public ?string $titleEn = null;
    public array $breadcrumbs = [];
    public string $slug;
    public $page;
    public $segment1;
    public $segment2;

    public function mount(
        $slug,
        $page,
        ?string $fallbackHeroImage = null,
        ?string $breadcrumbParentLabel = null,
        string $breadcrumbParentUrl = ''
    ): void
    {
        $this->page = $page;
        $this->slug = $slug;
        $subject = $this->page instanceof Page && $this->page->nav_group === 'subjects'
            ? $this->page->subject
            : null;
        $this->title = $subject?->short_name ?: $this->page->title;
        $this->titleZhTw = $subject?->name_zh_tw ?: $this->page->title_zh_tw;
        $this->titleEn = $subject?->name_en ?: $this->page->title_en;
        // $this->title = PageTitleHelper::getTranslatedTitle(); 
        // dd($this->title);

        $this->segment1  = request()->segment(1);  // 'background'
        $this->segment2 = request()->segment(2);  // 'background'


        if ($breadcrumbParentLabel !== null) {
            $pageNavLabel = $breadcrumbParentLabel;
            $pageNavUrl = $breadcrumbParentUrl;
        } elseif ($this->page instanceof Page && in_array($this->page->slug, ['results', 'projects', 'publications', 'plants'], true)) {
            $pageNavLabel = null;
            $pageNavUrl = '';
        } elseif ($this->page->nav_group) {
            $pageNavLabel = __('web.nav_'.$this->page->nav_group.'');
            $pageNavUrl = '';
        } else {
            $pageNavLabel = __('web.nav_results');
            $pageNavUrl = '/results';
        }

        $breadcrumbs[] = ['label' => __('web.nav_home'), 'url' => '/'];
        if ($pageNavLabel !== null) {
            $breadcrumbs[] = ['label' => $pageNavLabel, 'url' => $pageNavUrl];
        }
        $breadcrumbs[] = ['label' => $this->title, 'url' => ''];

        $this->breadcrumbs = $breadcrumbs;

        $heroImage = $this->page->hero_image ?: $fallbackHeroImage;

        if ($heroImage) {
            $this->hero = str_starts_with($heroImage, 'library:')
                ? Storage::disk('home_hero')->url(substr($heroImage, strlen('library:')))
                : Storage::disk('public')->url($heroImage);
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
