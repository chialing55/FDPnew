<?php

use App\Services\Fushan\SeedlingExperimentSpreadsheetImporter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

function mapSeedlingExperimentHeaders(array $headers, array $required): array
{
    $method = new ReflectionMethod(SeedlingExperimentSpreadsheetImporter::class, 'mapHeaders');

    return $method->invoke(new SeedlingExperimentSpreadsheetImporter, $headers, $required, 'Sheet1');
}

it('accepts the plural Treatments header used by greenhouse history files', function () {
    $mapped = mapSeedlingExperimentHeaders(
        ['Date', 'Sp.', 'Treatments', 'No.', '長度', '葉片數', 'Status', '附註'],
        ['date', 'species', 'plot', 'no', 'length', 'leaf_count', 'status', 'note'],
    );

    expect($mapped)->toBe(['date', 'species', 'plot', 'no', 'length', 'leaf_count', 'status', 'note']);
});

it('requires survey order and shade in the greenhouse deployment file', function () {
    $datasets = new ReflectionClassConstant(SeedlingExperimentSpreadsheetImporter::class, 'DATASETS');
    $required = $datasets->getValue()['greenhouse_deployment']['required'];

    expect($required)->toContain('survey_order', 'shade', 'plot', 'no');
});

it('reports missing spreadsheet columns with user-facing labels', function () {
    try {
        mapSeedlingExperimentHeaders(
            ['Date', 'Sp.', 'No.', '長度', '葉片數', 'Status', '附註'],
            ['date', 'species', 'plot', 'no', 'length', 'leaf_count', 'status', 'note'],
        );

        $this->fail('Expected header validation to fail.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['spreadsheet'][0])
            ->toBe('工作表「Sheet1」缺少必要欄位：Treatment（處理）');
    }
});
