<?php
namespace App\Filament\Pages;
use App\Forms\Components\HtmlContentEditor;
use App\Models\Web\ContentBlock;
use App\Models\Web\Page as WebPage;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
class HomeIntroductionManager extends Page
{
    protected static ?string $navigationGroup = '首頁';
    protected static ?string $navigationLabel = '網站介紹';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = '網站介紹';
    protected static ?string $slug = 'home-introduction';
    protected static string $view = 'filament.pages.home-introduction-manager';
    public ContentBlock $introduction;
    public array $data = [];
    public function mount(): void
    {
        $homepage = WebPage::query()->where('slug', 'index')->firstOrFail();
        $this->introduction = $homepage->contentBlocks()->orderBy('sort_order')->first()
            ?? $homepage->contentBlocks()->create([
                'sort_order' => 0,
                'is_public' => true,
                'title_zh_tw' => '網站介紹',
                'title_en' => 'Introduction',
            ]);
        $this->form->fill($this->introduction->only(['body_zh_tw', 'body_en']));
    }
    public function form(Form $form): Form
    {
        return $form->model($this->introduction)->statePath('data')->schema([
            Tabs::make('網站介紹')->tabs([
                Tabs\Tab::make('中文')->schema([HtmlContentEditor::make('body_zh_tw')->label('網站介紹（中）')]),
                Tabs\Tab::make('English')->schema([HtmlContentEditor::make('body_en')->label('Website introduction (English)')]),
            ])->persistTabInQueryString(),
        ]);
    }
    public function save(): void
    {
        $this->introduction->update($this->form->getState());
        Notification::make()->title('網站介紹已儲存')->success()->send();
    }
}
