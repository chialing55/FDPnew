<?php

use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class);

it('imports exactly the eight public pages', function () {
    expect(DB::connection('mysql_web')->table('changyang_pages')->orderBy('navigation_order')->pluck('slug')->all())
        ->toBe(['home', 'news', 'people', 'research', 'publications', 'courses', 'gallery', 'resources']);
});

it('serves every database-driven Changyang page', function (string $page) {
    $uri = $page === 'home' ? '/changyang' : '/changyang/'.$page;

    $this->get($uri)
        ->assertOk()
        ->assertSee('Plant Ecology Lab at NSYSU')
        ->assertSee('Resources');
})->with(['home', 'news', 'people', 'research', 'publications', 'courses', 'gallery', 'resources']);

it('does not expose gallery source pages as regular pages', function (string $page) {
    $this->get('/changyang/'.$page)->assertNotFound();
})->with(['fushan', 'bci', 'blog']);

it('renders page content, news groups and gallery albums from the database', function () {
    $this->get('/changyang/people')
        ->assertOk()
        ->assertSee('Principle Investigator (PI)')
        ->assertSee('Research Assistants')
        ->assertSee('Chia-Hao Chang-Yang (張楊家豪)')
        ->assertDontSee('<table', false)
        ->assertDontSee('wsite-multicol', false);

    $this->get('/changyang/news')
        ->assertOk()
        ->assertSee('Nov. 2024');

    $this->get('/changyang/research')
        ->assertOk()
        ->assertSee('Effects of climatic variation on plant reproduction')
        ->assertDontSee('<table', false)
        ->assertDontSee('wsite-multicol', false);

    $this->get('/changyang/resources')
        ->assertOk()
        ->assertSee('Taiwan Forest Bureau')
        ->assertDontSee('class="paragraph"', false)
        ->assertDontSee('<ul style=', false);

    $this->get('/changyang/gallery')
        ->assertOk()
        ->assertSee('Fushan')
        ->assertSee('BCI')
        ->assertSee('data-gallery-index', false)
        ->assertSee('data-open-album', false)
        ->assertSee('data-gallery-album hidden', false);
});

it('redirects old html paths only for valid pages', function () {
    $this->get('/changyang/research.html')
        ->assertRedirect('/changyang/research')
        ->assertStatus(301);

    $this->get('/changyang/fushan.html')->assertNotFound();
});

it('only references Changyang public-storage assets that exist', function () {
    foreach (['home', 'news', 'people', 'research', 'publications', 'courses', 'gallery', 'resources'] as $page) {
        $uri = $page === 'home' ? '/changyang' : '/changyang/'.$page;
        $html = $this->get($uri)->getContent();
        expect($html)->not->toContain('/changyang-assets/');
        preg_match_all('#/storage/changyang/([^"\')?]+)#', $html, $matches);

        foreach (array_unique($matches[1]) as $asset) {
            $asset = rtrim(html_entity_decode($asset), "'\"");
            expect(storage_path('app/public/changyang/'.$asset))
                ->toBeFile("Missing asset referenced by {$uri}: {$asset}");
        }
    }
});
