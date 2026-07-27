<?php

test('fushan seedling data viewer uses the analysis table and index-friendly tag prefixes', function () {
    $source = file_get_contents(
        dirname(__DIR__, 2) . '/app/Http/Livewire/Fushan/SeedlingDataviewer.php'
    );

    expect($source)
        ->toContain('->table("seedling as s")')
        ->toContain("->where('s.tag', 'like', \$tag . '%')")
        ->toContain("->where('s.mtag', 'like', \$mtag . '%')")
        ->not->toContain('$this->tagOptions =')
        ->not->toContain('$this->mtagOptions =');
});
