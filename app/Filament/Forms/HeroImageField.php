<?php

namespace App\Filament\Forms;

use Filament\Forms;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class HeroImageField
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public static function make(string $name = 'hero_image'): Forms\Components\CheckboxList
    {
        return Forms\Components\CheckboxList::make($name)
            ->label('現有照片')
            ->options(fn (): array => static::options())
            ->allowHtml()
            ->columns(3)
            ->afterStateHydrated(function (Forms\Components\CheckboxList $component, mixed $state): void {
                $component->state(filled($state) ? [$state] : []);
            })
            ->afterStateUpdated(function (Forms\Components\CheckboxList $component, mixed $state): void {
                if (is_array($state) && count($state) > 1) {
                    $component->state([array_values($state)[count($state) - 1]]);
                }
            })
            ->dehydrateStateUsing(fn (mixed $state): ?string => is_array($state) ? (array_values($state)[0] ?? null) : $state)
            ->helperText('未選擇時，研究成果會沿用研究成果頁的 Hero。');
    }

    protected static function options(): array
    {
        return collect(Storage::disk('home_hero')->files())
            ->filter(fn (string $file): bool => in_array(
                strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                self::IMAGE_EXTENSIONS,
                true,
            ))
            ->mapWithKeys(fn (string $file): array => [
                'hero/' . $file => static::option($file),
            ])
            ->all();
    }

    protected static function option(string $file): HtmlString
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('home_hero');

        return new HtmlString(
            '<div style="padding:8px"><img src="' . e($disk->url($file)) . '" style="width:100%;height:110px;object-fit:cover;border-radius:8px" alt="">'
            . '<div style="margin-top:6px;word-break:break-all;font-size:12px">' . e(basename($file)) . '</div></div>'
        );
    }
}
