<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
// use App\Models\FsBaseLogin;
use App\Http\Controllers\UpdateController;
use App\Models\PlantCatalog\SiteSpecies;
use App\Models\Web\Page;

//依據網址導向各個頁面


class WebIndexController extends Controller
{


    public function index(Request $request)
    {

        $user = $request->user()?->name;

        // if ($user=='no'){
        //     return view('login1', [
        //         'check' => 'no'
        //     ]);
        // } else {

        // Session::start();
        // $input = Request::all();
        $lasterUpdate = '';
        $ob_update = new updateController;
        $lasterUpdate = $ob_update->latestUpdates();

        $request->session()->put('latest_update', $lasterUpdate);


        return view('webindex');
        // }

    }

    public function species(Request $request, $spcode)
    {
        $catalogCode = $this->catalogCodeFor($spcode);

        if ($catalogCode !== null && $catalogCode !== $spcode) {
            return redirect()->route('front.species', ['spcode' => $catalogCode], 301);
        }

        $user = $request->user()?->name;

        return view('pages/web/species', [
            'spcode' => $spcode,
            'user' => $user
        ]);
    }

    public function legacySpecies($spcode)
    {
        return redirect()->route('front.species', [
            'spcode' => $this->catalogCodeFor($spcode) ?? $spcode,
        ], 301);
    }

    private function catalogCodeFor(string $spcode): ?string
    {
        if (SiteSpecies::query()->where('code', $spcode)->exists()) {
            return $spcode;
        }

        return SiteSpecies::query()
            ->where('spcode', $spcode)
            ->whereNotNull('code')
            ->where('code', '<>', '')
            ->orderByRaw("site = 'fushan' DESC")
            ->value('code');
    }


    public function splist(Request $request)
    {

        $user = $request->user()?->name;


        $heroPage = Page::query()->where('slug', 'plants')->first()
            ?? new Page([
                'slug' => 'plants',
                'title_zh_tw' => '監測植物',
                'title_en' => 'Plant Monitoring',
                'nav_group' => 'plants',
            ]);

        return view('pages/web/splist', compact('user', 'heroPage'));
    }

}
