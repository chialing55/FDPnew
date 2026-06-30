<?php

namespace App\Http\Controllers\Fushan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeoTreeSurveyController extends Controller
{
    public function documents(Request $request): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_doc');
    }

    public function entry(Request $request): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_entry');
    }

    private function page(Request $request, string $view): View
    {
        $user = $request->user();

        return view($view, [
            'site' => $request->route('site'),
            'project' => 'GEO-TREES',
            'user' => $user->account ?? $user->name,
        ]);
    }
}
