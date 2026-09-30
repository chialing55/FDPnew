<?php

namespace App\Http\Controllers;

use App\Models\ChangYang\Gallery;
use App\Models\ChangYang\NewsItem;
use App\Models\ChangYang\Page;
use App\Models\ChangYang\PersonCategory;
use App\Models\Web\Publication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChangYangController extends Controller
{
    public function show(string $page = 'home'): View
    {
        $currentPage = Page::active()
            ->where('slug', $page)
            ->with([
                'blocks' => fn ($query) => $query->active()->orderBy('sort_order'),
                'blocks.images' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->firstOrFail();

        $navigation = Page::active()
            ->where('show_in_navigation', true)
            ->orderBy('navigation_order')
            ->get(['slug', 'navigation_label', 'title']);

        $newsGroups = collect();
        if ($currentPage->template === 'news') {
            $newsGroups = NewsItem::active()->latestFirst()
                ->get()
                ->groupBy(fn (NewsItem $item): string => sprintf('%04d-%02d', $item->category_year, $item->category_month));
        }

        $galleries = collect();
        if ($currentPage->template === 'gallery') {
            $galleries = Gallery::active()
                ->with(['items' => fn ($query) => $query->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get();
        }

        $publications = $currentPage->template === 'publications'
            ? Publication::query()->where('is_changyang', true)->latestFirst()->orderBy('title')->get()->groupBy('year')
            : collect();

        $personCategories = $currentPage->template === 'people'
            ? PersonCategory::query()
                ->where('is_active', true)
                ->with(['roles' => fn ($query) => $query->where('is_current', true)->whereHas('person', fn ($personQuery) => $personQuery->where('is_active', true))->with('person')->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get()
            : collect();

        return view('changyang.page', compact('currentPage', 'navigation', 'newsGroups', 'galleries', 'publications', 'personCategories'));
    }

    public function legacy(string $page): RedirectResponse
    {
        $slug = $page === 'index' ? 'home' : $page;
        abort_unless(Page::active()->where('slug', $slug)->exists(), 404);

        return redirect()->route(
            $slug === 'home' ? 'changyang.home' : 'changyang.page',
            $slug === 'home' ? [] : ['page' => $slug],
            301
        );
    }
}
