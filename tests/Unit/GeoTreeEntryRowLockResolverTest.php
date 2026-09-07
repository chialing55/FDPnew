<?php

use App\Services\Fushan\GeoTreeEntryRowLockResolver;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;

uses(Tests\TestCase::class);

it('keeps branches 1 and 10 distinct when resolving mortality and previous measurements', function () {
    $originalResolver = Model::getConnectionResolver();
    $connection = Mockery::mock(MySqlConnection::class . '[select]', [null]);
    $connection->shouldReceive('select')->andReturnUsing(function ($query, $bindings) {
        $rows = str_contains($query, '`tree_individuals`')
            ? [['stemid' => '042381.10']]
            : [
                ['stemid' => '042381.1', 'dbh' => 10.9, 'pom' => 1.3],
                ['stemid' => '042381.10', 'dbh' => 29.8, 'pom' => 1.3],
            ];

        return array_values(array_map(
            fn ($row) => (object) $row,
            array_filter($rows, fn ($row) => in_array($row['stemid'], $bindings, true)),
        ));
    });
    $resolver = Mockery::mock(ConnectionResolverInterface::class);
    $resolver->shouldReceive('connection')->andReturn($connection);
    Model::setConnectionResolver($resolver);

    try {
        $result = (new GeoTreeEntryRowLockResolver())->resolve([
            ['stemid' => '042381.1', 'dbh' => 0],
            ['stemid' => '042381.10', 'dbh' => 0],
        ]);

        expect($result['lockedStemids'])->toBe(['042381.10'])
            ->and($result['records'][0])->not->toHaveKey('_entryLock')
            ->and($result['records'][1]['_entryLock'])->toBe([
                'type' => 'active_mortality', 'displayColumn' => 'dbh', 'display' => 'M',
            ])
            ->and($result['records'][1]['dbh'])->toBe(0)
            ->and($result['previousByStemid']['042381.10']['dbh'])->toBe(29.8);
    } finally {
        Model::setConnectionResolver($originalResolver);
    }
});
