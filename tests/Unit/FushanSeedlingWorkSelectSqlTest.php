<?php

use App\Http\Controllers\Fushan\SeedlingController;

test('next seedling survey excludes soft-deleted normalized rows', function () {
    $controller = new SeedlingController;
    $method = new ReflectionMethod($controller, 'seedlingWorkSelectSql');
    $method->setAccessible(true);

    $sql = $method->invoke($controller);

    expect($sql)
        ->toContain('r.deleted_at IS NULL')
        ->toContain('st.deleted_at IS NULL')
        ->toContain('i.deleted_at IS NULL');
});
