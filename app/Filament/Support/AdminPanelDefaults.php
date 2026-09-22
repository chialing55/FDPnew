<?php

namespace App\Filament\Support;

use Filament\Panel;

/** Common interaction safeguards for both website-management panels. */
class AdminPanelDefaults
{
    public static function configure(Panel $panel): Panel
    {
        CmsFormActions::configure();

        return $panel
            ->sidebarCollapsibleOnDesktop()
            ->databaseTransactions()
            ->unsavedChangesAlerts();
    }
}
