<?php

namespace App\Filament\ChangYang\Resources;

use App\Models\ChangYang\Gallery;
use App\Filament\ChangYang\Support\ChangYangPageLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    protected static ?string $slug = 'gallery';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = '相簿';

    public static function listUrl(): string
    {
        return ChangYangPageLink::tab('gallery');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('相簿名稱')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->label('相簿說明')->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->label('相簿排序')->numeric()->integer()->minValue(0)->default(0),
            Forms\Components\Toggle::make('is_active')->label('公開相簿')->default(true),
            Forms\Components\Repeater::make('items')->label('相片')->relationship()->orderColumn('sort_order')->reorderable()->addActionLabel('新增相片')->grid(2)->columnSpanFull()->schema([
                Forms\Components\FileUpload::make('image_path')->label('照片')->disk('public')->directory('changyang/gallery')->image()->required()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('thumbnail_path', null)),
                Forms\Components\Hidden::make('thumbnail_path'),
                Forms\Components\Toggle::make('is_cover')->label('封面照片')->default(false)->live()
                    ->helperText('指定封面會同時公開此照片；隱藏照片會取消封面。未指定時使用排序最前的公開照片。')
                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set, $component): void {
                        if (! $state) return;
                        $set('is_active', true);
                        $current = explode('.', $component->getStatePath());
                        $currentKey = $current[count($current) - 2];
                        foreach ($get('../../items') ?? [] as $key => $item) {
                            if ((string) $key !== $currentKey) $set('../../items.'.$key.'.is_cover', false);
                        }
                    }),
                Forms\Components\TextInput::make('title')->label('照片標題')->maxLength(255),
                Forms\Components\Textarea::make('caption')->label('照片說明'),
                Forms\Components\Toggle::make('is_active')->label('公開照片')->default(true)->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set): void {
                        if (! $state) $set('is_cover', false);
                    }),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'create' => GalleryResource\Pages\CreateGallery::route('/create'),
            'edit' => GalleryResource\Pages\EditGallery::route('/{record}/edit'),
        ];
    }
}
