<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class HtmlContentEditor extends Field
{
    protected string $view = 'forms.components.html-content-editor';

    protected bool|Closure $showsExamples = true;

    protected bool|Closure $showsPreview = true;

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

    public function showsExamples(): bool
    {
        return (bool) $this->evaluate($this->showsExamples);
    }

    public function showsPreview(): bool
    {
        return (bool) $this->evaluate($this->showsPreview);
    }
}
