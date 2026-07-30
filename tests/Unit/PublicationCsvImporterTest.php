<?php

use App\Models\Web\Publication;
use App\Services\Web\PublicationCsvImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

beforeEach(function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite is required for the isolated importer test.');
    }

    config()->set('database.connections.mysql_web', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Schema::connection('mysql_web')->create('publications', function (Blueprint $table): void {
        $table->id();
        $table->string('external_id')->nullable();
        $table->string('authors', 1000)->nullable();
        $table->string('authors_zh_tw', 1000)->nullable();
        $table->string('title', 500)->nullable();
        $table->string('title_zh_tw', 500)->nullable();
        $table->string('journal')->nullable();
        $table->string('journal_zh_tw')->nullable();
        $table->unsignedSmallInteger('year')->nullable();
        $table->string('type', 50)->nullable();
        $table->string('volume', 50)->nullable();
        $table->string('issue', 50)->nullable();
        $table->string('pages', 100)->nullable();
        $table->string('doi')->nullable();
        $table->string('url', 2048)->nullable();
        $table->string('pdf_path')->nullable();
        $table->boolean('is_open_access')->default(false);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
});

it('imports CSV columns and updates records by external id', function () {
    Publication::create([
        'external_id' => 'PUB-1',
        'authors' => 'Original author',
        'title' => 'Original title',
    ]);

    $file = tmpfile();
    fwrite($file, "\xEF\xBB\xBFexternal_id,authors,title,title_zh_tw,year,is_open_access\n");
    fwrite($file, "PUB-1,Updated author,Updated title,更新後中文標題,2025,yes\n");
    fwrite($file, "PUB-2,New author,New title,,2026,0\n");
    $path = stream_get_meta_data($file)['uri'];

    $result = app(PublicationCsvImporter::class)->import($path);

    expect($result)->toBe(['created' => 1, 'updated' => 1])
        ->and(Publication::count())->toBe(2)
        ->and(Publication::where('external_id', 'PUB-1')->first()->title_zh_tw)->toBe('更新後中文標題')
        ->and(Publication::where('external_id', 'PUB-1')->first()->is_open_access)->toBeTruthy()
        ->and(Publication::where('external_id', 'PUB-2')->first()->title_zh_tw)->toBeNull();

    fclose($file);
});
