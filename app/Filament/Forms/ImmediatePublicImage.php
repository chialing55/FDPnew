<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImmediatePublicImage
{
    public static function field(
        string $name,
        string $label,
        string $disk = 'public',
        string $directory = '',
        int $maxSize = 10240,
        bool $multiple = false,
    ): FileUpload {
        return FileUpload::make($name)
            ->label($label)
            ->disk($disk)
            ->directory($directory)
            ->visibility('public')
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->maxSize($maxSize)
            ->multiple($multiple)
            ->previewable(false)
            ->extraAttributes(['class' => 'cms-compact-upload']);
    }

    public static function upload(mixed $state): ?TemporaryUploadedFile
    {
        $value = is_array($state) ? (array_values($state)[0] ?? null) : $state;

        return $value instanceof TemporaryUploadedFile ? $value : null;
    }

    public static function path(mixed $state): ?string
    {
        $value = is_array($state) ? (array_values($state)[0] ?? null) : $state;

        return is_string($value) && filled($value) ? $value : null;
    }

    public static function replace(TemporaryUploadedFile $upload, string $directory, ?string $oldPath): string
    {
        $newPath = $upload->store($directory, 'public');

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $newPath;
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function preview(mixed $state, string $emptyText = '尚未上傳圖片', bool $circular = false): HtmlString
    {
        $path = static::path($state);

        if (! $path) {
            return new HtmlString('<div style="padding:24px;border:1px dashed #cbd5e1;border-radius:8px;color:#64748b;text-align:center">' . e($emptyText) . '</div>');
        }

        if ($circular) {
            return new HtmlString(
                '<div style="display:grid;width:120px;height:120px;place-items:center;overflow:hidden;border:1px solid #d1d5db;border-radius:9999px;background:#fff">'
                . '<img src="' . e(Storage::disk('public')->url($path)) . '" alt="" style="display:block;width:100%;height:100%;object-fit:contain">'
                . '</div>'
            );
        }

        return new HtmlString(
            '<div style="max-width:360px;overflow:hidden;border:1px solid #e5e7eb;border-radius:8px;background:#fff">'
            . '<img src="' . e(Storage::disk('public')->url($path)) . '" alt="" style="display:block;width:100%;max-height:220px;object-fit:contain">'
            . '</div>'
        );
    }
}
