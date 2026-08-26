<?php

namespace App\Http\Controllers;

use App\Models\ChangYang\Gallery;
use App\Models\ChangYang\NewsItem;
use App\Models\ChangYang\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChangYangController extends Controller
{
    public function show(string $page = 'home'): View
    {
        $currentPage = Page::active()
            ->where('slug', $page)
            ->with([
                'sections' => fn ($query) => $query->active()->orderBy('sort_order'),
                'sections.blocks' => fn ($query) => $query->active()->orderBy('sort_order'),
                'sections.blocks.images' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->firstOrFail();

        $navigation = Page::active()
            ->where('show_in_navigation', true)
            ->orderBy('navigation_order')
            ->get(['slug', 'navigation_label', 'title']);

        $newsGroups = collect();
        if ($currentPage->template === 'news') {
            $newsGroups = NewsItem::active()
                ->orderByDesc('category_year')
                ->orderByDesc('category_month')
                ->orderBy('sort_order')
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

        return view('changyang.page', compact('currentPage', 'navigation', 'newsGroups', 'galleries'));
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
