<?php

use App\Models\Web\Publication;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class);

it('imports exactly the eight public pages', function () {
    expect(DB::connection('mysql_web')->table('changyang_pages')->orderBy('navigation_order')->pluck('slug')->all())
        ->toBe(['home', 'news', 'people', 'research', 'publications', 'courses', 'gallery', 'resources']);
});

it('serves every database-driven Changyang page', function (string $page) {
    $uri = $page === 'home' ? '/' : '/'.$page;

    $this->get('https://changyang.tw'.$uri)
        ->assertOk()
        ->assertSee('Plant Ecology Lab at NSYSU')
        ->assertSee('Resources');
})->with(['home', 'news', 'people', 'research', 'publications', 'courses', 'gallery', 'resources']);

it('publishes teacher publications independently of the plot website status', function () {
    $teacherPublication = Publication::create([
        'authors' => 'Visibility Test Author',
        'title' => 'Teacher website visibility regression test',
        'year' => 2099,
        'is_active' => false,
        'is_changyang' => true,
    ]);
    $plotOnlyPublication = Publication::create([
        'authors' => 'Visibility Test Author',
        'title' => 'Plot-only visibility regression test',
        'year' => 2099,
        'is_active' => true,
        'is_changyang' => false,
    ]);

    try {
        $this->get('https://changyang.tw/publications')
            ->assertOk()
            ->assertSee($teacherPublication->title)
            ->assertDontSee($plotOnlyPublication->title);
    } finally {
        $teacherPublication->delete();
        $plotOnlyPublication->delete();
    }
});

it('does not expose gallery source pages as regular pages', function (string $page) {
    $this->get('https://changyang.tw/'.$page)->assertNotFound();
})->with(['fushan', 'bci', 'blog']);

it('renders page content, news groups and gallery albums from the database', function () {
    $this->get('https://changyang.tw/people')
        ->assertOk()
        ->assertSee('Principle Investigator (PI)')
        ->assertSee('Research Assistants')
        ->assertSee('Chia-Hao Chang-Yang (張楊家豪)')
        ->assertDontSee('<table', false)
        ->assertDontSee('wsite-multicol', false);

    $this->get('https://changyang.tw/news')
        ->assertOk()
        ->assertSee('Nov. 2024');

    $this->get('https://changyang.tw/research')
        ->assertOk()
        ->assertSee('Effects of climatic variation on plant reproduction')
        ->assertDontSee('<table', false)
        ->assertDontSee('wsite-multicol', false);

    $this->get('https://changyang.tw/resources')
        ->assertOk()
        ->assertSee('Taiwan Forest Bureau')
        ->assertDontSee('class="paragraph"', false)
        ->assertDontSee('<ul style=', false);

    $this->get('https://changyang.tw/gallery')
        ->assertOk()
        ->assertSee('Fushan')
        ->assertSee('BCI')
        ->assertSee('data-gallery-index', false)
        ->assertSee('data-open-album', false)
        ->assertSee('data-gallery-album hidden', false);
});

it('redirects old html paths only for valid pages', function () {
    $this->get('https://changyang.tw/research.html')
        ->assertRedirect('https://changyang.tw/research')
        ->assertStatus(301);

    $this->get('https://changyang.tw/fushan.html')->assertNotFound();
});

it('only references Changyang public-storage assets that exist', function () {
    foreach (['home', 'news', 'people', 'research', 'publications', 'courses', 'gallery', 'resources'] as $page) {
        $uri = $page === 'home' ? '/' : '/'.$page;
        $html = $this->get('https://changyang.tw'.$uri)->getContent();
        expect($html)->not->toContain('/changyang-assets/');
        preg_match_all('#/storage/changyang/([^"\')?]+)#', $html, $matches);

        foreach (array_unique($matches[1]) as $asset) {
            $asset = rtrim(html_entity_decode($asset), "'\"");
            expect(storage_path('app/public/changyang/'.$asset))
                ->toBeFile("Missing asset referenced by {$uri}: {$asset}");
        }
    }
});
