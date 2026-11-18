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
