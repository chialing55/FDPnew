<?php

namespace App\Filament\Support;

use Filament\Pages\BasePage;

class CmsFormActions
{
    public static function configure(): void
    {
        BasePage::alignFormActionsEnd();
    }
}
