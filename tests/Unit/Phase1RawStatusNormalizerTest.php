<?php

use App\Services\Fushan\Phase1RawStatusNormalizer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

it('corrects contradictory phase one statuses and can run repeatedly', function () {
    config()->set('database.connections.phase1_status_test', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    $schema = Schema::connection('phase1_status_test');
    $schema->create('groundcover_phase1_raw', function (Blueprint $table): void {
        $table->id();
        $table->string('date');
        $table->string('plot');
        $table->string('no');
        $table->string('species')->nullable();
        $table->string('length')->nullable();
        $table->string('leaf_count')->nullable();
        $table->string('status')->nullable();
        $table->text('note')->nullable();
        $table->timestamps();
    });

    $now = now();
    $records = [
        ['date' => '2025-01-01', 'plot' => 'P1', 'no' => 'E1', 'length' => '10', 'status' => 'A', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-02-01', 'plot' => 'P1', 'no' => 'E1', 'length' => '-4', 'status' => 'A', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-03-01', 'plot' => 'P1', 'no' => 'E1', 'length' => '-4', 'status' => null, 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-04-01', 'plot' => 'P1', 'no' => 'E1', 'length' => '-4', 'status' => 'G', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-05-01', 'plot' => 'P1', 'no' => 'E1', 'length' => '-4', 'status' => 'A', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-06-01', 'plot' => 'P1', 'no' => 'E1', 'length' => '12', 'status' => 'D', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-01-01', 'plot' => 'P1', 'no' => 'E2', 'length' => '4', 'status' => null, 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2024-07-14', 'plot' => 'GM005-C', 'no' => 'E240', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'G', 'note' => '補植為E284。', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-01-01', 'plot' => 'P2', 'no' => 'E3', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'G', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-02-01', 'plot' => 'P2', 'no' => 'E3', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'D', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-03-01', 'plot' => 'P2', 'no' => 'E3', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'G', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-01-01', 'plot' => 'P2', 'no' => 'E4', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'G', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-02-01', 'plot' => 'P2', 'no' => 'E4', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'G', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-03-01', 'plot' => 'P2', 'no' => 'E4', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'D', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-04-01', 'plot' => 'P2', 'no' => 'E4', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'D', 'created_at' => $now, 'updated_at' => $now],
        ['date' => '2025-05-01', 'plot' => 'P2', 'no' => 'E4', 'length' => '-4', 'leaf_count' => '-4', 'status' => 'G', 'created_at' => $now, 'updated_at' => $now],
    ];
    DB::connection('phase1_status_test')->table('groundcover_phase1_raw')->insert(
        array_map(fn (array $record): array => array_merge(['species' => null, 'leaf_count' => null, 'note' => null], $record), $records)
    );

    $normalizer = app(Phase1RawStatusNormalizer::class);
    $connection = DB::connection('phase1_status_test');

    expect($normalizer->normalize($connection))->toMatchArray([
        'known_row_corrections' => 1,
        'alive_with_minus_four' => 2,
        'blank_with_length' => 1,
        'non_alive_with_length' => 1,
        'blank_with_minus_four' => 1,
        'leaf_count_with_minus_four_length' => 4,
        'single_d_changed_to_g' => 1,
        'premature_g_changed_to_d' => 2,
        'total' => 10,
    ])->and(
        $connection->table('groundcover_phase1_raw')->orderBy('id')->pluck('status')->all()
    )->toBe(['A', 'D', 'D', 'G', 'G', 'A', 'A', 'A', 'G', 'G', 'G', 'D', 'D', 'D', 'D', 'G'])
        ->and($connection->table('groundcover_phase1_raw')->where('no', 'E240')->first())
        ->length->toBe('5')
        ->leaf_count->toBe('0')
        ->note->toBeNull()
        ->and($normalizer->normalize($connection)['total'])->toBe(0);

    DB::purge('phase1_status_test');
});
