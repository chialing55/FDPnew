<?php

namespace App\Console\Commands;

use App\Services\Web\ZoteroPublicationSync;
use Illuminate\Console\Command;
use Throwable;

class SyncChangYangPublications extends Command
{
    protected $signature = 'changyang:sync-publications';

    protected $description = '同步老師的 Zotero My Publications 至正式文獻資料庫';

    public function handle(ZoteroPublicationSync $sync): int
    {
        try {
            $result = $sync->sync();
            $this->info("新增 {$result['created']} 筆文獻，已比對 {$result['matched']} 筆，略過 {$result['skipped']} 筆。新增文獻的樣區歸屬為待確認。");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('文獻同步未完成，已取得的正式文獻會保留。請查看 Laravel 日誌後重試。');

            return self::FAILURE;
        }
    }
}
