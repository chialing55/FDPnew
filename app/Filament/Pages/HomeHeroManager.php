<?php

namespace App\Filament\Pages;

use App\Filament\Forms\ImmediatePublicImage;
use App\Models\Web\Page as WebPage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HomeHeroManager extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = '首頁';
    protected static ?string $navigationLabel = 'Hero 圖片';
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = '首頁 Hero 圖片';
    protected static ?string $slug = 'home-hero';
    protected static string $view = 'filament.pages.home-hero-manager';

    public WebPage $homepage;
    public ?array $data = [];

    public function mount(): void
    {
        $this->homepage = WebPage::query()->where('slug', 'index')->firstOrFail();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ImmediatePublicImage::field(
                    'heroUploads',
                    '選擇圖片',
                    disk: 'home_hero',
                    maxSize: 12288,
                    multiple: true,
                )
                    ->helperText('支援 JPG、PNG、WEBP、GIF，可一次選擇多張，單檔最大 12MB。')
                    ->live()
                    ->afterStateUpdated(fn (FileUpload $component, mixed $state) => $this->uploadHeroImages($component, $state)),
            ])
            ->statePath('data');
    }

    public function uploadHeroImages(FileUpload $component, mixed $state): void
    {
        $uploads = collect(is_array($state) ? $state : [$state])
            ->filter(fn (mixed $file): bool => $file instanceof TemporaryUploadedFile);

        if ($uploads->isEmpty()) {
            return;
        }

        $uploads->each(function (TemporaryUploadedFile $file): void {
            $filename = basename($file->getClientOriginalName());

            if (Storage::disk('home_hero')->exists($filename)) {
                $filename = pathinfo($filename, PATHINFO_FILENAME)
                    . '-' . now()->format('YmdHis')
                    . '.' . $file->getClientOriginalExtension();
            }

            $file->storeAs('', $filename, 'home_hero');
        });

        $component->state([]);
        Notification::make()->title($uploads->count() . ' 張 Hero 圖片已上傳')->success()->send();
    }

    public function deleteHeroImage(string $filename): void
    {
        $filename = basename($filename);

        if (! in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return;
        }

        Storage::disk('home_hero')->delete($filename);
        Notification::make()->title('Hero 圖片已刪除')->success()->send();
    }

    public function getHeroImages(): Collection
    {
        return collect(Storage::disk('home_hero')->files())
            ->filter(fn (string $file): bool => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
            ->map(fn (string $file): array => [
                'name' => basename($file),
                'url' => Storage::disk('home_hero')->url($file),
            ])
            ->values();
    }
}
