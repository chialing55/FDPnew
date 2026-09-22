<?php

namespace App\Jobs;

use App\Services\Web\ZoteroPublicationSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SyncZoteroPublications implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public int $uniqueFor = 7200;

    public bool $failOnTimeout = true;

    public function handle(ZoteroPublicationSync $sync): void
    {
        Cache::put('zotero-sync-status', ['message' => '同步進行中', 'at' => now()->toDateTimeString()], 86400);
        $result = $sync->sync();
        Cache::put('zotero-sync-status', [
            'message' => "同步完成：新增 {$result['created']} 筆、比對 {$result['matched']} 筆、略過 {$result['skipped']} 筆。",
            'at' => now()->toDateTimeString(),
        ], 86400 * 30);
    }

    public function failed(?Throwable $exception): void
    {
        Cache::put('zotero-sync-status', [
            'message' => '同步失敗或逾時，請稍後重試；已匯入資料會保留。',
            'at' => now()->toDateTimeString(),
        ], 86400 * 30);
    }
}
