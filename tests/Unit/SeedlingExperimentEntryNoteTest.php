<?php

use App\Http\Livewire\Fushan\SeedlingExperimentEntry;

function displayedSeedlingExperimentNote(mixed $recordId, mixed $currentNote, mixed $previousNote): string
{
    $method = new ReflectionMethod(SeedlingExperimentEntry::class, 'displayedNote');

    return $method->invoke(new SeedlingExperimentEntry, $recordId, $currentNote, $previousNote);
}

it('prefills the previous note before the current record is saved', function (): void {
    expect(displayedSeedlingExperimentNote(null, null, '前次備註'))->toBe('前次備註');
});

it('shows the current note after the current record is saved', function (): void {
    expect(displayedSeedlingExperimentNote(123, '本期備註', '前次備註'))->toBe('本期備註');
});

it('keeps a cleared current note blank instead of restoring the previous note', function (): void {
    expect(displayedSeedlingExperimentNote(123, null, '前次備註'))->toBe('');
});

it('uses a blank note when there is no previous record note', function (): void {
    expect(displayedSeedlingExperimentNote(null, null, null))->toBe('');
});
