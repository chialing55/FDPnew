<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Web\ResearchOutput;
use App\Models\Web\Page;

class ResearchOutputController extends Controller
{
    public function show(string $slug)
    {


        $output = ResearchOutput::where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();
        $fallbackHeroImage = Page::query()->where('slug', 'results')->value('hero_image');
        // dd($output->toArray());
        return view('pages.web.default', [
            'slug' => 'results/' . $slug, // ✅ 只拿來做顯示/麵包屑可，但不要拿去查 DB
            'page' => $output,            // ✅ 讓 view 用同一個 $page 變數
            'isOutput' => true,           // ✅ 建議加，讓 view 判斷用
            'outputSlug' => $slug,        // ✅ 真正的 output slug（不含 results/）
            'fallbackHeroImage' => $fallbackHeroImage,
        ]);
    }
}
