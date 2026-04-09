<?php

namespace App\Http\Controllers\Fushan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MortalityController extends Controller
{
    public function mortality(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_doc', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->name,
        ]);
    }

    public function entry(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');
        $entry = (string) $request->route('entry', '1');

        return view('pages/fushan/mortality_entry', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->name,
            'entry' => $entry,
        ]);
    }

    public function record(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_record', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->name,
        ]);
    }

    public function compare(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_compare', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->name,
        ]);
    }

    public function import(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_import', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->name,
        ]);
    }

    public function dataviewer(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/mortality_dataviewer', [
            'site' => $site,
            'project' => '死亡率調查',
            'user' => $user->name,
        ]);
    }
}
