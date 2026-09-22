<?php

namespace App\Filament\ChangYang\Resources\ChangYangPageResource\Pages;

use App\Filament\ChangYang\Resources\ChangYangPageResource;
use App\Filament\Actions\SyncZoteroPublicationsAction;
use App\Filament\Actions\ViewPublicPageAction;
use App\Models\ChangYang\NewsItem;
use App\Models\ChangYang\PersonCategory;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditChangYangPage extends EditRecord
{
    protected static string $resource = ChangYangPageResource::class;

    public function getTitle(): string
    {
        return '編輯頁面：'.($this->record->navigation_label ?: $this->record->title);
    }

    protected function getHeaderActions(): array
    {
        return [
            SyncZoteroPublicationsAction::make()
                ->visible(fn (): bool => $this->record->slug === 'publications'),
            ViewPublicPageAction::make(fn (): string => $this->record->slug === 'home'
                    ? route('changyang.home')
                    : route('changyang.page', ['page' => $this->record->slug])),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->slug !== 'people') {
            return;
        }

        collect($this->form->getRawState()['person_categories'] ?? [])
            ->values()
            ->each(function (array $category, int $sortOrder): void {
                $attributes = [
                    'title' => $category['title'],
                    'is_active' => (bool) ($category['is_active'] ?? false),
                    'sort_order' => $sortOrder + 1,
                ];

                if (filled($category['id'] ?? null)) {
                    PersonCategory::query()->whereKey($category['id'])->update($attributes);

                    return;
                }

                PersonCategory::query()->create([
                    ...$attributes,
                    'slug' => 'person-category-'.Str::uuid(),
                ]);
            });
    }

    public function reorderPeople(int $categoryId, array $order): void
    {
        $this->authorizeAccess();
        abort_unless($this->record->slug === 'people', 403);
        $category = PersonCategory::query()->findOrFail($categoryId);
        $category->getConnection()->transaction(function () use ($category, $order): void {
            $roles = $category->roles()->where('is_current', true)->lockForUpdate()->get();
            $ids = collect($order)->map(fn ($id) => (string) $id);
            abort_unless($ids->count() === $roles->count()
                && $ids->unique()->count() === $ids->count()
                && $ids->diff($roles->modelKeys())->isEmpty(), 422);

            foreach ($order as $position => $roleId) {
                $category->roles()->whereKey($roleId)->update(['sort_order' => $position + 1]);
            }
        });
        \Filament\Notifications\Notification::make()->title('人員排序已儲存')->success()->send();
    }

    public function reorderNews(array $order): void
    {
        $this->authorizeAccess();
        abort_unless($this->record->slug === 'news', 403);

        NewsItem::query()->getConnection()->transaction(function () use ($order): void {
            $items = NewsItem::query()->lockForUpdate()->latestFirst()->get();
            $ids = collect($order)->map(fn ($id) => (string) $id);

            abort_unless($ids->count() === $items->count()
                && $ids->unique()->count() === $items->count()
                && $ids->diff($items->modelKeys())->isEmpty(), 422);

            foreach ($order as $position => $newsId) {
                NewsItem::query()->whereKey($newsId)->update(['sort_order' => $position + 1]);
            }
        });

        \Filament\Notifications\Notification::make()->title('消息排序已儲存')->success()->send();
    }
}
