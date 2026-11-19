<?php

it('keeps greenhouse lifecycle handling aligned with applicable groundcover rules', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Services/Fushan/GreenhouseFormalDataImporter.php');

    expect($source)
        ->toContain('個體已於 {$individual->end_date} 記為 G')
        ->toContain("\$individual->end_date !== null && \$status === 'G'")
        ->toContain("\$status === 'D' && \$individual->death_date === null")
        ->toContain('$deathDate = $individual->death_date ?: $date;');
});
