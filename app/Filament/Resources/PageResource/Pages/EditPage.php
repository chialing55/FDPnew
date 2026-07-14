<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\SiteResource;
use App\Filament\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected array $siteTeamsData = [];

    protected ?bool $pageVisibility = null;

    protected array $subjectNames = [];

    public function getTitle(): string
    {
        $name = $this->record->site?->name_zh_tw ?: $this->record->title_zh_tw;

        if (in_array($this->record->slug, ['results', 'projects', 'about/news', 'about/team'], true)) {
            return '編輯' . $name . '頁';
        }

        return match ($this->record->nav_group) {
            'sites' => '編輯動態樣區：' . $name,
            'subjects' => '編輯研究主題：' . $name,
            default => '編輯頁面：' . $name,
        };
    }

    public function getBreadcrumb(): string
    {
        return $this->record->site?->name_zh_tw ?: $this->record->title_zh_tw;
    }

    public function getBreadcrumbs(): array
    {
        return match ($this->record->nav_group) {
            'sites' => [
                SiteResource::getUrl('index') => '動態樣區列表',
                $this->getBreadcrumb(),
            ],
            'subjects' => [
                SubjectResource::getUrl('index') => '研究主題列表',
                $this->getBreadcrumb(),
            ],
            default => [],
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('預覽前台')
                ->icon('heroicon-o-eye')
                ->url(fn () => url('/' . ltrim($this->record->slug, '/')))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->siteTeamsData = $data['site_teams'] ?? [];
        unset($data['site_teams']);

        $this->pageVisibility = isset($data['page_visibility']) ? (bool) $data['page_visibility'] : null;
        unset($data['page_visibility']);

        $this->subjectNames = [
            'short_name_zh_tw' => $data['subject_short_name_zh_tw'] ?? null,
            'short_name_en' => $data['subject_short_name_en'] ?? null,
            'name_zh_tw' => $data['subject_name_zh_tw'] ?? null,
            'name_en' => $data['subject_name_en'] ?? null,
        ];
        unset(
            $data['subject_short_name_zh_tw'],
            $data['subject_short_name_en'],
            $data['subject_name_zh_tw'],
            $data['subject_name_en'],
        );

        if ($this->record->subject && filled($this->subjectNames['short_name_zh_tw'])) {
            $data['title_zh_tw'] = $this->subjectNames['short_name_zh_tw'];
            $data['title_en'] = $this->subjectNames['short_name_en'];
        }

        $uploadedHero = $data['uploaded_hero_image'] ?? null;
        unset($data['uploaded_hero_image']);

        if (filled($uploadedHero)) {
            $data['hero_image'] = $uploadedHero;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->subject) {
            $this->record->subject->update($this->subjectNames);
        }

        if ($this->pageVisibility !== null) {
            $this->record->site?->update(['is_active' => $this->pageVisibility]);
            $this->record->subject?->update(['is_active' => $this->pageVisibility]);
        }

        $site = $this->record->site;

        if (! $site) {
            return;
        }

        $keptIds = collect($this->siteTeamsData)
            ->map(function (array $data) use ($site): int {
                $siteTeam = $site->siteTeams()->updateOrCreate(
                    ['id' => $data['id'] ?? null],
                    [
                        'team_id' => $data['team_id'],
                        'role' => $data['role'],
                        'sort_order' => $data['sort_order'] ?? 0,
                    ],
                );

                return (int) $siteTeam->getKey();
            });

        $site->siteTeams()
            ->when($keptIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($subject = $this->record->subject) {
            $data['subject_short_name_zh_tw'] = $subject->short_name_zh_tw;
            $data['subject_short_name_en'] = $subject->short_name_en;
            $data['subject_name_zh_tw'] = $subject->name_zh_tw;
            $data['subject_name_en'] = $subject->name_en;
        }

        return $data;
    }
}
