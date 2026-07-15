<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Web\Project;
use App\Models\Web\Page;

class ProjectController extends Controller
{
    public function show(Project $project)
    {
        abort_unless($project->is_active, 404);
        $project->load([
            'sites',
            'subjects' => fn ($query) => $query->where('subjects.is_active', true),
        ]);

        $heroPage = Page::where('slug', 'projects')->firstOrFail();
        $breadcrumbParentLabel = $heroPage->title;
        $heroPage->title_zh_tw = $project->title_zh_tw;
        $heroPage->title_en = $project->title_en;

        return view('pages.web.project', compact('project', 'heroPage', 'breadcrumbParentLabel'));
    }
}
