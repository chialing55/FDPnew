<?php

use App\Services\Fushan\SeedlingExperimentTxtExporter;

function seedlingTxtExporterMethod(string $method, array $arguments): mixed
{
    $reflection = new ReflectionMethod(SeedlingExperimentTxtExporter::class, $method);

    return $reflection->invoke(new SeedlingExperimentTxtExporter, ...$arguments);
}

it('exports the expected columns for each experiment', function (): void {
    expect(seedlingTxtExporterMethod('headers', ['groundcover_phase1']))
        ->toBe(['Date', 'Plot', 'No.', 'Species', '長度', '葉片數', 'Status', '備註'])
        ->and(seedlingTxtExporterMethod('headers', ['groundcover_phase2']))
        ->toContain('Density', '子葉數')
        ->and(seedlingTxtExporterMethod('headers', ['greenhouse']))
        ->toContain('Treatment', '遮蔭處理排序', '備註')
        ->not->toContain('附註')
        ->not->toContain('Density', '子葉數');
});

it('derives the greenhouse shade from the Treatment code', function (): void {
    $values = seedlingTxtExporterMethod('values', [(object) [
        'survey_order' => 1,
        'survey_date' => '2026-10-01',
        'plot_code' => 'ECSL',
        'individual_no' => 'M10',
        'species_name' => '香楠',
        'length' => '7.50',
        'leaf_count' => 4,
        'status' => 'A',
        'note' => null,
    ], 'greenhouse']);

    expect($values[1])->toBe('L')
        ->and($values[4])->toBe('ECSL')
        ->and($values[6])->toBe(7.5);
});

it('exports minus one and minus four measurements as NA', function (int $value): void {
    expect(seedlingTxtExporterMethod('formatNumber', [$value]))->toBe('NA');
})->with([-1, -4]);

it('filters records after the first D or G while retaining the terminal record', function (): void {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Services/Fushan/SeedlingExperimentTxtExporter.php');

    expect($source)
        ->toContain("->whereNotExists(function (Builder \$terminal)")
        ->toContain("->whereIn('terminal.status', ['D', 'G'])")
        ->toContain("'terminal.survey_date', '<', 'r.survey_date'")
        ->toContain("'terminal.id', '<', 'r.id'");
});

it('shows three direct downloads without metadata input forms', function (): void {
    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/pages/fushan/seedling-experiment/workspace.blade.php');
    $note = file_get_contents(dirname(__DIR__, 2).'/resources/views/components/seedling-experiment/download-note.blade.php');

    expect($view)
        ->toContain("'groundcover_phase1' => [")
        ->toContain("'groundcover_phase2' => [")
        ->toContain("'greenhouse' => [")
        ->toContain('<th>資料表</th>')
        ->toContain('<th>資料範圍</th>')
        ->toContain('<th>說明</th>')
        ->toContain('<th>下載</th>')
        ->toContain('<x-seedling-experiment.download-note />')
        ->and($note)->toContain('不需輸入日期、輸入者或其他下載條件。');
});

it('protects both the download page and txt endpoint with admin permission', function (): void {
    $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web_admin_legacy.php');
    $layout = file_get_contents(dirname(__DIR__, 2).'/resources/views/layouts/seedling-experiment.blade.php');

    expect($routes)
        ->toContain("Route::get('/download', 'download')->middleware('admin')->name('download');")
        ->toMatch("/Route::get\('\/download\/\{experiment\}', 'downloadExperimentTxt'\)\s*->middleware\('admin'\)/")
        ->and($layout)->toContain("auth()->user()?->is_admin");
});
