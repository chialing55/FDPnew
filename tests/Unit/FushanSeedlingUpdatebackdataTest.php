<?php

use App\Http\Livewire\Fushan\SeedlingUpdatebackdata;

test('identity rows describe each soft-deleted source', function () {
    $component = new SeedlingUpdatebackdata;
    $method = new ReflectionMethod($component, 'normalizeIdentityRow');
    $method->setAccessible(true);

    $row = $method->invoke($component, [
        'tag' => '4284',
        'mtag' => '4284',
        'individual_deleted_at' => '2026-05-26 14:51:14',
        'stem_deleted_at' => '2026-05-26 14:51:14',
    ]);

    expect($row['deletion_note'])
        ->toBe('individual 已被軟刪除；stem 已被軟刪除');
});

test('record rows describe soft deletion', function () {
    $component = new SeedlingUpdatebackdata;
    $method = new ReflectionMethod($component, 'normalizeMasterRow');
    $method->setAccessible(true);

    $row = $method->invoke($component, [
        'tag' => '4284',
        'record_deleted_at' => '2026-05-26 14:51:14',
    ]);

    expect($row['deletion_note'])->toBe('record 已被軟刪除');
});
