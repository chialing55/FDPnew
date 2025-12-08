<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

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
        $this->hero = $this->pickRandomHero();
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
        return view('livewire.web.index-hero');
    }
}
