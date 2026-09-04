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
        return $this->page($request, 'pages.fushan.geo_tree_survey_entry', [
            'entry' => (string) ($request->route('entry') ?? '1'),
        ]);
    }

    public function note(Request $request): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_note');
    }

    public function compare(Request $request): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_compare');
    }

    public function dataviewer(Request $request): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_dataviewer');
    }

    public function download(Request $request): View
    {
        return $this->page($request, 'pages.fushan.geo_tree_survey_download');
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
