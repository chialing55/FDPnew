<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class IndexHero extends Component
{

    public string $hero = '';
    public $plants = '';
    public $censuses = '';
    public string $title = '';

    public function mount(): void
    {

        // dd($this->title);
        $this->title = $this->title();
        $this->hero = $this->resolveHero();
    } 

    protected function title(){
        $segment1 = request()->segment(1);  // 'background'
        // 第二段：motivation / team / ...  可能是 null
        $segment2 = request()->segment(2);  // 'motivation'

        // 只處理 background 開頭的
        if ($segment2 != '') {
            $key = 'web.nav_'.$segment1."_" . $segment2;
            // 用語系檔撈標題，如果沒定義就退回一個預設值           
        } else {
            $key = 'web.nav_'.$segment1;
        }  
        return __($key); 
    }

    protected function resolveHero(): string
    {
        $files = collect(Storage::disk('home_hero')->files())
            ->filter(fn (string $file): bool => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp','gif'], true))
            ->map(fn (string $file): string => Storage::disk('home_hero')->url($file))
            ->values()->all();

        if (empty($files)) {
            return '';
        }

        // 隨機取一張
        return Arr::random($files);
    }

    public function render()
    {
        return view('livewire.web.index-hero');
    }
}
