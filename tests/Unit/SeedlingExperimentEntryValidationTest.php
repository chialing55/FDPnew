<?php

use App\Services\Fushan\SeedlingExperimentEntrySaveService;

function validateSeedlingExperimentRow(array $row, object $stored): array
{
    $method = new ReflectionMethod(SeedlingExperimentEntrySaveService::class, 'validateRow');

    return $method->invoke(new SeedlingExperimentEntrySaveService, $row, $stored, 0, '2026-11')[1];
}

function validSeedlingExperimentRow(array $overrides = []): array
{
    return array_replace([
        'batch_item_id' => 1,
        'date' => '2026-11-04',
        'length' => 10,
        'cotyledon_count' => 0,
        'leaf_count' => 3,
        'status' => 'A',
        'note' => '',
    ], $overrides);
}

it('does not allow cotyledons for Machilus zuihoensis', function (): void {
    $errors = validateSeedlingExperimentRow(
        validSeedlingExperimentRow(['cotyledon_count' => 1]),
        (object) ['species' => '香楠', 'previous_cotyledon_count' => 0],
    );

    expect(collect($errors)->pluck('code'))->toContain('xiangnan_cotyledon');
});

it('limits yellow-fruit cotyledons to two and prevents increases', function (): void {
    $errors = validateSeedlingExperimentRow(
        validSeedlingExperimentRow(['cotyledon_count' => 3]),
        (object) ['species' => '黃杞', 'previous_cotyledon_count' => 1],
    );

    expect(collect($errors)->pluck('code'))
        ->toContain('huangqi_cotyledon_max')
        ->toContain('huangqi_cotyledon_increase');
});

it('allows yellow-fruit cotyledons to stay the same or decrease', function (int $current): void {
    $errors = validateSeedlingExperimentRow(
        validSeedlingExperimentRow(['cotyledon_count' => $current]),
        (object) ['species' => '黃杞', 'previous_cotyledon_count' => 2],
    );

    expect($errors)->toBeEmpty();
})->with([2, 1, 0]);
