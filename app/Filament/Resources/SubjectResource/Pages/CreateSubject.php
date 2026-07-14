<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\SubjectResource;
use App\Models\Web\Page;
use App\Models\Web\Subject;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateSubject extends CreateRecord
{
    protected static string $resource = SubjectResource::class;

    public function getTitle(): string
    {
        return '新增研究主題';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $tail = ltrim($data['page_slug'], '/');
        $slug = str_starts_with($tail, 'subjects/') ? $tail : 'subjects/' . $tail;
        unset($data['page_slug']);

        return DB::connection('mysql_web')->transaction(function () use ($data, $slug): Subject {
            $navOrder = ((int) Page::query()->where('nav_group', 'subjects')->max('nav_order')) + 1;
            $sortOrder = ((int) Subject::query()->max('sort_order')) + 1;

            $page = Page::query()->create([
                'slug' => $slug,
                'title_zh_tw' => $data['short_name_zh_tw'],
                'title_en' => $data['short_name_en'],
                'nav_group' => 'subjects',
                'nav_order' => $navOrder,
            ]);

            return Subject::query()->create($data + [
                'page_id' => $page->getKey(),
                'sort_order' => $sortOrder,
            ]);
        });
    }

    protected function getRedirectUrl(): string
    {
        return PageResource::getUrl('edit', ['record' => $this->record->page_id]);
    }
}
