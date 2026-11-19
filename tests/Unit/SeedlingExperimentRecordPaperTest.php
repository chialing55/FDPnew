<?php

use App\Services\Fushan\SeedlingExperimentRecordPaperExporter;

function seedlingExperimentRecordPaperForeignKey(string $status): string
{
    $exporter = (new ReflectionClass(SeedlingExperimentRecordPaperExporter::class))
        ->newInstanceWithoutConstructor();
    $method = new ReflectionMethod($exporter, 'recordForeignKey');

    return $method->invoke($exporter, $status);
}

it('uses the previous record while the survey is not completed', function (string $status): void {
    expect(seedlingExperimentRecordPaperForeignKey($status))
        ->toBe('batch_items.previous_record_id');
})->with(['prepared', 'in_progress']);

it('uses the current record after the survey is completed', function (): void {
    expect(seedlingExperimentRecordPaperForeignKey('completed'))
        ->toBe('batch_items.record_id');
});

it('uses Treatment and the greenhouse title in greenhouse record paper', function (): void {
    $exporter = (new ReflectionClass(SeedlingExperimentRecordPaperExporter::class))
        ->newInstanceWithoutConstructor();
    $method = new ReflectionMethod($exporter, 'worksheetXml');
    $xml = $method->invoke($exporter, [(object) [
        'plot_code' => 'ECSL',
        'individual_no' => 'M10',
        'species_name' => '香楠',
        'length' => 7.5,
        'leaf_count' => 4,
        'status' => 'A',
        'note' => null,
    ]], '2026-11', 'greenhouse');

    expect($xml)
        ->toContain('Treatment')
        ->toContain('溫室實驗 2026-11')
        ->toContain('ECSL')
        ->not->toContain('地被小苗試驗 2026-11');
});
