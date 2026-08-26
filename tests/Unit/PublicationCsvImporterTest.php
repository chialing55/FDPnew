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
        $table->text('authors')->nullable();
        $table->text('authors_zh_tw')->nullable();
        $table->string('title', 500)->nullable();
        $table->string('title_zh_tw', 500)->nullable();
        $table->string('journal')->nullable();
        $table->string('journal_zh_tw')->nullable();
        $table->unsignedSmallInteger('year')->nullable();
        $table->string('type', 50)->nullable();
        $table->string('language', 10)->default('en');
        $table->string('institution')->nullable();
        $table->string('institution_zh_tw')->nullable();
        $table->string('thesis_type', 20)->nullable();
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

    expect($result)->toBe(['created' => 1, 'updated' => 1, 'skipped' => 0, 'skipped_rows' => []])
        ->and(Publication::count())->toBe(2)
        ->and(Publication::where('external_id', 'PUB-1')->first()->title_zh_tw)->toBe('更新後中文標題')
        ->and(Publication::where('external_id', 'PUB-1')->first()->is_open_access)->toBeTruthy()
        ->and(Publication::where('external_id', 'PUB-2')->first()->title_zh_tw)->toBeNull();

    fclose($file);
});

it('ignores extra CSV columns when the required publication fields exist', function () {
    $file = tmpfile();
    fwrite($file, "source_system,authors,unused_note,title,year,legacy_code\n");
    fwrite($file, "archive,Test author,ignore me,Test title,2026,OLD-1\n");
    $path = stream_get_meta_data($file)['uri'];

    $result = app(PublicationCsvImporter::class)->import($path);

    expect($result)->toBe(['created' => 1, 'updated' => 0, 'skipped' => 0, 'skipped_rows' => []])
        ->and(Publication::count())->toBe(1)
        ->and(Publication::first()->authors)->toBe('Test author')
        ->and(Publication::first()->title)->toBe('Test title')
        ->and(Publication::first()->year)->toBe(2026)
        ->and(Publication::first()->getAttributes())->not->toHaveKeys([
            'source_system',
            'unused_note',
            'legacy_code',
        ]);

    fclose($file);
});

it('imports author lists longer than 1000 characters', function () {
    $authors = str_repeat('Long Author Name; ', 80);
    $file = tmpfile();
    fputcsv($file, ['authors', 'title']);
    fputcsv($file, [$authors, 'Publication with a long author list']);
    $path = stream_get_meta_data($file)['uri'];

    $result = app(PublicationCsvImporter::class)->import($path);

    expect($result)->toBe(['created' => 1, 'updated' => 0, 'skipped' => 0, 'skipped_rows' => []])
        ->and(strlen(Publication::first()->authors))->toBeGreaterThan(1000)
        ->and(Publication::first()->authors)->toBe(rtrim($authors));

    fclose($file);
});

it('imports optional thesis metadata without defaulting thesis type', function () {
    $file = tmpfile();
    fputcsv($file, ['authors', 'title', 'type', 'language', 'institution', 'institution_zh_tw', 'thesis_type']);
    fputcsv($file, ['Thesis Author', 'Chinese thesis', 'thesis', 'zh', 'National Taiwan University', '國立臺灣大學', '博士論文']);
    fputcsv($file, ['Article Author', 'Regular article', 'paper', '', '', '', '']);
    $path = stream_get_meta_data($file)['uri'];

    $result = app(PublicationCsvImporter::class)->import($path);

    expect($result)->toBe(['created' => 2, 'updated' => 0, 'skipped' => 0, 'skipped_rows' => []])
        ->and(Publication::where('title', 'Chinese thesis')->first()->thesis_type)->toBe('doctoral')
        ->and(Publication::where('title', 'Chinese thesis')->first()->institution)->toBe('National Taiwan University')
        ->and(Publication::where('title', 'Chinese thesis')->first()->institution_zh_tw)->toBe('國立臺灣大學')
        ->and(Publication::where('title', 'Regular article')->first()->thesis_type)->toBeNull();

    fclose($file);
});

it('updates a publication without DOI by matching year and title', function () {
    Publication::create([
        'authors' => 'Original Author',
        'title' => 'Publication without DOI',
        'year' => 2024,
        'journal' => 'Forest Journal',
    ]);

    $file = tmpfile();
    fputcsv($file, ['authors', 'title', 'year', 'journal', 'volume', 'pages']);
    fputcsv($file, ['Updated Author', 'Publication without DOI', '2024', 'Forest Journal', '12', '10-20']);
    $path = stream_get_meta_data($file)['uri'];

    $result = app(PublicationCsvImporter::class)->import($path);

    expect($result)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0, 'skipped_rows' => []])
        ->and(Publication::count())->toBe(1)
        ->and(Publication::first()->authors)->toBe('Updated Author')
        ->and(Publication::first()->volume)->toBe('12');

    fclose($file);
});

it('updates a thesis without DOI by matching Chinese author and institution', function () {
    Publication::create([
        'authors' => 'Original Author',
        'authors_zh_tw' => '王小明',
        'title' => 'Original thesis title',
        'year' => 2023,
        'type' => 'thesis',
        'institution' => 'National Taiwan University',
        'institution_zh_tw' => '國立臺灣大學',
    ]);

    $file = tmpfile();
    fputcsv($file, ['authors', 'authors_zh_tw', 'title', 'year', 'type', 'institution_zh_tw', 'thesis_type']);
    fputcsv($file, ['Updated Author', '王小明', 'Updated thesis title', '2023', 'thesis', '國立臺灣大學', '碩士論文']);
    $path = stream_get_meta_data($file)['uri'];

    $result = app(PublicationCsvImporter::class)->import($path);

    expect($result)->toBe(['created' => 0, 'updated' => 1, 'skipped' => 0, 'skipped_rows' => []])
        ->and(Publication::count())->toBe(1)
        ->and(Publication::first()->title)->toBe('Updated thesis title')
        ->and(Publication::first()->thesis_type)->toBe('master');

    fclose($file);
});

it('skips an ambiguous row and continues importing later rows', function () {
    foreach (['First Author', 'Second Author'] as $author) {
        Publication::create([
            'authors' => $author,
            'title' => 'Duplicated title',
            'year' => 2024,
        ]);
    }

    $file = tmpfile();
    fputcsv($file, ['authors', 'title', 'year']);
    fputcsv($file, ['Ambiguous Author', 'Duplicated title', '2024']);
    fputcsv($file, ['New Author', 'Unique title', '2025']);
    $path = stream_get_meta_data($file)['uri'];

    $result = app(PublicationCsvImporter::class)->import($path);

    expect($result['created'])->toBe(1)
        ->and($result['updated'])->toBe(0)
        ->and($result['skipped'])->toBe(1)
        ->and($result['skipped_rows'][0])->toContain('第 2 列')
        ->and(Publication::where('title', 'Unique title')->exists())->toBeTrue();

    fclose($file);
});
