<?php

use App\Services\Fushan\SeedlingExperimentEntrySaveService;

function validateGreenhouseExperimentRow(array $row): array
{
    $method = new ReflectionMethod(SeedlingExperimentEntrySaveService::class, 'validateRow');

    return $method->invoke(
        new SeedlingExperimentEntrySaveService,
        $row,
        (object) ['species' => '香楠', 'previous_cotyledon_count' => null],
        0,
        '2026-11',
        'greenhouse',
    );
}

it('uses the greenhouse fields without density or cotyledons', function (): void {
    $config = require dirname(__DIR__, 2).'/config/greenhouse-experiment-entry.php';
    $fields = collect($config['columns'])->pluck('data');

    expect($fields)
        ->toContain('date', 'plot', 'individual_no', 'species', 'length', 'leaf_count', 'status', 'note')
        ->not->toContain('density', 'cotyledon_count');
});

it('accepts valid greenhouse alive measurements and stores no cotyledon value', function (): void {
    [$normalized, $errors] = validateGreenhouseExperimentRow([
        'batch_item_id' => 1,
        'date' => '2026-11-19',
        'length' => 8.5,
        'leaf_count' => 4,
        'status' => 'A',
        'note' => '',
    ]);

    expect($errors)->toBeEmpty()
        ->and($normalized['cotyledon_count'])->toBeNull();
});

it('requires both greenhouse measurements to be minus four for dead or gone plants', function (): void {
    [, $errors] = validateGreenhouseExperimentRow([
        'batch_item_id' => 1,
        'date' => '2026-11-19',
        'length' => -4,
        'leaf_count' => 2,
        'status' => 'D',
        'note' => '',
    ]);

    expect(collect($errors)->pluck('code'))->toContain('dead_measurement');
});

it('mounts the shared entry component for the greenhouse page', function (): void {
    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/pages/fushan/seedling-experiment/workspace.blade.php');
    $layout = file_get_contents(dirname(__DIR__, 2).'/resources/views/layouts/seedling-experiment.blade.php');

    expect($view)->toContain("@livewire('fushan.seedling-experiment-entry', ['experimentCode' => 'greenhouse'])")
        ->and($layout)->toContain("in_array(\$page, ['groundcover-phase2', 'greenhouse'], true)");
});

it('offers G revival for greenhouse treatments', function (): void {
    $component = file_get_contents(dirname(__DIR__, 2).'/app/Http/Livewire/Fushan/SeedlingExperimentEntry.php');
    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/livewire/fushan/seedling-experiment-entry.blade.php');

    expect($component)
        ->not->toContain("if (\$this->experimentCode === 'greenhouse') {\n            \$this->gRevivalCandidates = [];")
        ->and($view)->toContain('<strong>G 復活</strong>')
        ->and($view)->toContain('@if ($gRevivalCandidates !== [])')
        ->and($view)->not->toContain('本組目前沒有可恢復追蹤的 G 個體。')
        ->and($view)->not->toContain('@unless($isGreenhouse)\n                    <div class="seedling-entry-revival">');
});
