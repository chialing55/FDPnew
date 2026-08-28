<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use App\Models\Web\Page;
use App\Models\Web\Site;
use App\Models\Web\News;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    public array $plots = [];
    public $indexIntro;
    public $plotsContent=[];
    public $latestNews;
    public int $siteCount = 0;
    public int $speciesCount = 0;
    public int $treeCount = 0;

    public function mount(): void
    {
        // 1. 先找到該頁
        $page = Page::where('slug', 'index')->firstOrFail();

        // 2. 載入該頁的內容區塊
        $this->indexIntro = $page->contentBlocks()
            ->with(['items' => fn ($query) => $query->where('is_public', true)->orderBy('sort_order')])
            ->where('is_public', true)
            ->orderBy('sort_order')
            ->first();
        $this->latestNews = News::public()->latestFirst()->take(6)->get();


        // 3. 從資料庫動態載入所有顯示於前台的樣區。
        $sites = Site::query()
            ->with('page')
            ->where('is_active', true)
            ->whereHas('page', fn ($query) => $query->where('nav_group', 'sites'))
            ->get()
            ->sortBy(fn (Site $site): array => [$site->page?->nav_order ?? PHP_INT_MAX, $site->id]);

        $this->siteCount = $sites->count();
        $this->speciesCount = DB::connection('plant_catalog')
            ->table('site_species')
            ->whereNotNull('code')
            ->where('code', '<>', '')
            ->distinct()
            ->count('code');
        $this->treeCount = DB::connection('mysql1')
            ->table('base')
            ->where('deleted_at', '')
            ->distinct()
            ->count('tag')
            + DB::connection('mysql5')
                ->table('1ha_data_2024')
                ->where('deleted_at', '')
                ->distinct()
                ->count('tag');

        $fallbackImages = collect(Storage::disk('public')->files('plot-cards'))
            ->filter(fn (string $file): bool => in_array(
                strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                ['jpg', 'jpeg', 'png', 'webp', 'gif'],
                true,
            ))
            ->values()
            ->all();

        foreach ($sites as $site) {
            $plot = Str::afterLast($site->page->slug, '/');
            $this->plots[] = $plot;
            $this->plotsContent[$plot] = [
                'title' => $site->page->title,
                'intro' => $site->description ?? '',
                'slug' => $site->page->slug,
                'image' => $site->homepage_image
                    ? Storage::disk('public')->url($site->homepage_image)
                    : (! empty($fallbackImages)
                        ? Storage::disk('public')->url(Arr::random($fallbackImages))
                        : null),
                'image_position' => max(1, min(100, (int) ($site->homepage_image_position ?? 50))),
            ];
        }

    }



    public function render()
    {
        return view('livewire.web.index');
    }
}
