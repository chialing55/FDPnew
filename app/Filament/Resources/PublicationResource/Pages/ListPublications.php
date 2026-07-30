<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Actions\ListPageSettingsAction;
use App\Filament\Resources\PublicationResource;
use App\Models\Web\SiteSetting;
use App\Services\Web\PublicationCsvImporter;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class ListPublications extends ListRecords
{
    protected static string $resource = PublicationResource::class;

    protected ?string $subheading = '引用排列方式為全站共用設定；修改後會直接套用到前台所有學術產出。';

    protected function getHeaderActions(): array
    {
        return [
            ListPageSettingsAction::make('publications', '學術產出頁'),
            Actions\Action::make('importCsv')
                ->label('匯入 CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('csv')
                        ->label('CSV 檔案')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                        ->maxSize(20480)
                        ->storeFiles(false)
                        ->required()
                        ->helperText('依表頭名稱匯入；external_id 優先、DOI 次之，用來判斷新增或更新。'),
                ])
                ->modalDescription('支援欄位：external_id、authors、authors_zh_tw、title、title_zh_tw、journal、journal_zh_tw、year、type、volume、issue、pages、doi、url、pdf_path、is_open_access、is_active。')
                ->action(function (array $data, PublicationCsvImporter $importer): void {
                    $file = $data['csv'] ?? null;

                    if (! $file instanceof TemporaryUploadedFile) {
                        Notification::make()
                            ->danger()
                            ->title('匯入失敗')
                            ->body('無法讀取上傳的 CSV 檔案。')
                            ->send();

                        return;
                    }

                    try {
                        $result = $importer->import($file->getRealPath());

                        Notification::make()
                            ->success()
                            ->title('CSV 匯入完成')
                            ->body("新增 {$result['created']} 筆，更新 {$result['updated']} 筆。")
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->danger()
                            ->title('CSV 匯入失敗')
                            ->body($exception->getMessage())
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\Action::make('citationSettings')
                ->label('引用格式設定')
                ->icon('heroicon-o-cog-6-tooth')
                ->fillForm(fn (): array => [
                    'citation_style' => SiteSetting::getValue('publication_citation_style', 'year_after_authors'),
                ])
                ->form([
                    Forms\Components\Radio::make('citation_style')
                        ->label('引用排列方式')
                        ->options([
                            'year_after_authors' => '格式一：作者、年份、標題、期刊、卷（期）、頁碼',
                            'year_at_end' => '格式二：作者、標題、期刊、卷（期）、頁碼、年份',
                        ])
                        ->descriptions([
                            'year_after_authors' => 'Authors. 2015. Title. Journal 19: 2512–2522。',
                            'year_at_end' => 'Authors. Title. Journal 19: 2512–2522 (2015)。',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    SiteSetting::setValue('publication_citation_style', $data['citation_style']);
                })
                ->successNotificationTitle('引用格式已更新，前台已套用新設定'),
            Actions\CreateAction::make()->label('新增學術產出'),
        ];
    }
}
