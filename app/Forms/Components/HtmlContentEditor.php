<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class HtmlContentEditor extends Field
{
    protected string $view = 'forms.components.html-content-editor';

    protected bool|Closure $showsExamples = true;

    protected bool|Closure $showsPreview = true;

    protected int|Closure $editorHeight = 420;

    protected int|Closure $editorMinHeight = 300;

    public function withoutExamples(): static
    {
        $this->showsExamples = false;

        return $this;
    }

    public function withoutPreview(): static
    {
        $this->showsPreview = false;

        return $this;
    }

    public function compact(): static
    {
        // Jodit 的高度包含兩列工具列；240px 可保留約 120px 的文字編輯區。
        $this->editorHeight = 240;
        $this->editorMinHeight = 240;

        return $this;
    }

    public function showsExamples(): bool
    {
        return (bool) $this->evaluate($this->showsExamples);
    }

    public function showsPreview(): bool
    {
        return (bool) $this->evaluate($this->showsPreview);
    }

    public function getEditorHeight(): int
    {
        return (int) $this->evaluate($this->editorHeight);
    }

    public function getEditorMinHeight(): int
    {
        return (int) $this->evaluate($this->editorMinHeight);
    }
}
