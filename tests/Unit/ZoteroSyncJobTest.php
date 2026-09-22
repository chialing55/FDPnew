<?php

use App\Jobs\SyncZoteroPublications;
use App\Services\Web\ZoteroPublicationSync;
use Illuminate\Support\Facades\Cache;

uses(Tests\TestCase::class);

beforeEach(function () {
    config()->set('cache.default', 'array');
});

it('records progress and completion without contacting Zotero', function () {
    $sync = Mockery::mock(ZoteroPublicationSync::class);
    $sync->shouldReceive('sync')->once()->andReturnUsing(function () {
        expect(Cache::get('zotero-sync-status')['message'])->toBe('同步進行中');

        return ['created' => 2, 'matched' => 3, 'skipped' => 1];
    });

    (new SyncZoteroPublications)->handle($sync);

    expect(Cache::get('zotero-sync-status')['message'])
        ->toBe('同步完成：新增 2 筆、比對 3 筆、略過 1 筆。');
});

it('reports failure without exposing exception details', function () {
    (new SyncZoteroPublications)->failed(new RuntimeException('private details'));

    expect(Cache::get('zotero-sync-status')['message'])
        ->toContain('同步失敗或逾時')->not->toContain('private details');
});
