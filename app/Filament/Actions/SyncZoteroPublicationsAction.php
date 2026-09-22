<?php

namespace App\Filament\Actions;

use App\Jobs\SyncZoteroPublications;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SyncZoteroPublicationsAction
{
    public static function make(): Action
    {
        return Action::make('syncZoteroPublications')
            ->label('從 Zotero 更新資料')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->requiresConfirmation()
            ->modalContent(fn () => view('filament.changyang.forms.zotero-sync-status'))
            ->modalHeading('從 Zotero 更新學術產出')
            ->modalDescription('將同步 Zotero 的 My Publications；作業會在背景執行，每月 1 日仍會自動同步。')
            ->modalSubmitActionLabel('開始更新')
            ->action(function (): void {
                SyncZoteroPublications::dispatch()->onConnection('database');

                Notification::make()
                    ->success()
                    ->title('已開始從 Zotero 更新資料')
                    ->body('同步已排入背景作業。再次開啟此按鈕可查看進度及最後結果。')
                    ->send();
            });
    }
}
