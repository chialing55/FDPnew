<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use App\Models\Web\Subject;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubjects extends ListRecords
{
    protected static string $resource = SubjectResource::class;

    protected ?string $subheading = '先從列表選擇一筆研究主題；點擊整列或「編輯內容」即可進入新版編輯頁面。';

    public function reorderTable(array $order): void
    {
        parent::reorderTable($order);

        Subject::query()->with('page')->whereIn('id', $order)->get()->each(
            fn (Subject $subject) => $subject->page?->update(['nav_order' => $subject->sort_order])
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('新增研究主題'),
        ];
    }
}
