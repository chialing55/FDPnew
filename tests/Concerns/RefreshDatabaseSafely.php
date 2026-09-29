<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestDatabaseSafety;

trait RefreshDatabaseSafely
{
    use RefreshDatabase {
        refreshDatabase as private refreshDatabaseUsingFramework;
    }

    public function refreshDatabase(): void
    {
        TestDatabaseSafety::assertAllConnectionsAreForTesting($this->app['config']);

        $this->refreshDatabaseUsingFramework();
    }
}
