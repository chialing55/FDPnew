<?php

namespace App\Http\Controllers\Fushan;

use App\Http\Controllers\Controller;
use App\Services\Fushan\GeoTreeSurveyRecordPaperService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeoTreeSurveyController extends Controller
{
    public function documents(Request $request, GeoTreeSurveyRecordPaperService $recordPaperService): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_doc', [
            'excludedQuadrats' => $recordPaperService->excludedQuadrats(),
        ]);
    }

    public function entry(Request $request): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_entry');
    }

    private function page(Request $request, string $view, array $data = []): View
    {
        $user = $request->user();

        return view($view, array_merge([
            'site' => $request->route('site'),
            'project' => 'GEO-TREES',
            'user' => $user->account ?? $user->name,
        ], $data));
    }
}
