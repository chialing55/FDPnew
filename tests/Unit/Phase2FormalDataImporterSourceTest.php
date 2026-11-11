<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class Phase2FormalDataImporterSourceTest extends TestCase
{
    public function test_import_batch_reads_density_from_each_cleaned_row_before_creating_a_plot(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Services/Fushan/Phase2FormalDataImporter.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$density = (int) $row->density;', $source);
        $this->assertStringContainsString("'density' => (int) \$density,", $source);
    }
}
