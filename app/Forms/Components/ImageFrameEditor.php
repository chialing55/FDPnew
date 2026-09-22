<?php

namespace App\Forms\Components;

use App\Support\ChangYang\ImageFrame;
use Closure;
use Filament\Forms\Components\Field;

class ImageFrameEditor extends Field
{
    protected string $view = 'forms.components.image-frame-editor';

    protected string|Closure|null $imagePath = null;

    protected array|Closure $previewData = [];

    protected bool|Closure $showsTextPreview = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->default(ImageFrame::defaults());
        $this->afterStateHydrated(fn (self $component, $state) => $component->state(ImageFrame::normalize(is_array($state) ? $state : null)));
        $this->dehydrateStateUsing(fn ($state): array => ImageFrame::normalize(is_array($state) ? $state : null));
    }

    public function imagePath(string|Closure $path): static
    {
        $this->imagePath = $path;

        return $this;
    }

    public function getImagePath(): mixed
    {
        return $this->evaluate($this->imagePath);
    }

    public function previewData(array|Closure $data): static
    {
        $this->previewData = $data;

        return $this;
    }

    public function getPreviewData(): array
    {
        return $this->evaluate($this->previewData) ?? [];
    }

    public function withoutTextPreview(): static
    {
        $this->showsTextPreview = false;

        return $this;
    }

    public function showsTextPreview(): bool
    {
        return (bool) $this->evaluate($this->showsTextPreview);
    }
}
