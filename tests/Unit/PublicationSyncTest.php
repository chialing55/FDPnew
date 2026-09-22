<?php

use App\Models\Web\Publication;
use App\Services\Web\ZoteroPublicationSync;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite is required for the isolated sync test.');
    }

    config()->set('database.connections.mysql_web', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
    config()->set('cache.default', 'array');
    config()->set('publication_sync.zotero_user_id', 7834744);
    Schema::connection('mysql_web')->create('publications', function (Blueprint $table): void {
        $table->id();
        $table->string('zotero_id')->nullable();
        $table->text('authors')->nullable();
        $table->string('title', 500)->nullable();
        $table->unsignedSmallInteger('year')->nullable();
        $table->string('journal')->nullable();
        $table->string('volume', 50)->nullable();
        $table->string('issue', 50)->nullable();
        $table->string('pages', 100)->nullable();
        $table->string('doi')->nullable();
        $table->string('url', 2048)->nullable();
        $table->string('type', 50)->nullable();
        $table->string('language', 10)->default('en');
        $table->string('pdf_path')->nullable();
        $table->boolean('is_active')->default(true);
        $table->boolean('is_changyang')->default(false);
        $table->string('site_review_status', 20)->default('reviewed');
        $table->timestamps();
    });
});

it('creates new Zotero records as teacher publications pending site review', function () {
    Http::fake(['api.zotero.org/*' => Http::response([[
        'key' => 'ZOTERO01',
        'data' => ['itemType' => 'journalArticle', 'title' => 'A Zotero ‐ publication', 'date' => '2026', 'DOI' => 'https://doi.org/10.1234/Forest'],
    ]], 200, ['Total-Results' => '1'])]);

    expect(app(ZoteroPublicationSync::class)->sync())->toBe(['created' => 1, 'matched' => 0, 'skipped' => 0]);
    expect(Publication::first()->only(['zotero_id', 'title', 'doi', 'is_changyang', 'is_active', 'site_review_status']))
        ->toBe(['zotero_id' => 'ZOTERO01', 'title' => 'A Zotero-publication', 'doi' => '10.1234/forest', 'is_changyang' => true, 'is_active' => true, 'site_review_status' => 'pending']);
});

it('matches DOI records without changing their approved site status or editorial data', function () {
    $existing = Publication::create([
        'authors' => 'Edited author', 'title' => 'Edited title', 'year' => 2020,
        'doi' => '10.1234/forest', 'pdf_path' => 'publications/edited.pdf',
        'is_active' => false, 'is_changyang' => false, 'site_review_status' => 'reviewed',
    ]);
    Http::fake(['api.zotero.org/*' => Http::response([[
        'key' => 'ZOTERO01',
        'data' => ['itemType' => 'journalArticle', 'title' => 'Source title', 'date' => '2026', 'DOI' => '10.1234/FOREST'],
    ]], 200, ['Total-Results' => '1'])]);

    expect(app(ZoteroPublicationSync::class)->sync())->toBe(['created' => 0, 'matched' => 1, 'skipped' => 0]);
    expect(Publication::count())->toBe(1)
        ->and($existing->fresh()->only(['zotero_id', 'title', 'pdf_path', 'is_active', 'is_changyang', 'site_review_status']))
        ->toBe(['zotero_id' => 'ZOTERO01', 'title' => 'Edited title', 'pdf_path' => 'publications/edited.pdf', 'is_active' => false, 'is_changyang' => true, 'site_review_status' => 'reviewed']);
});
