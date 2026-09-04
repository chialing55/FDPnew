<?php

use App\Support\TreeEntry\TreeEntryComparator;

it('reports missing dates and ignores locked rows', function () {
    $rows1 = [
        ['stemid' => '1.0', 'qx' => 1, 'qy' => 2, 'sqx' => 1, 'sqy' => 1, 'show' => 1, 'date' => ''],
        ['stemid' => '2.0', 'qx' => 1, 'qy' => 2, 'sqx' => 1, 'sqy' => 1, 'show' => 1, 'date' => ''],
    ];
    $rows2 = [
        ['stemid' => '1.0', 'qx' => 1, 'qy' => 2, 'sqx' => 1, 'sqy' => 1, 'show' => 1, 'date' => '2026-09-04'],
        ['stemid' => '2.0', 'qx' => 1, 'qy' => 2, 'sqx' => 1, 'sqy' => 1, 'show' => 1, 'date' => ''],
    ];

    $result = (new TreeEntryComparator())->compare($rows1, $rows2, ['date' => '日期'], ['2.0']);

    expect($result['eligible'])->toBe(1)
        ->and($result['differences'])->toHaveCount(1)
        ->and($result['differences'][0]['message'])->toBe('第一次輸入缺資料');
});

it('normalizes numbers and json but reports actual field differences', function () {
    $base = ['stemid' => '1.0', 'qx' => 1, 'qy' => 2, 'sqx' => 1, 'sqy' => 1, 'show' => 1, 'date' => '2026-09-04'];
    $first = [$base + ['dbh' => '10.00', 'alternote' => '{"qx":2,"tag":"1"}', 'note' => '甲']];
    $second = [$base + ['dbh' => 10, 'alternote' => '{"tag":"1","qx":2}', 'note' => '乙']];

    $result = (new TreeEntryComparator())->compare($first, $second, [
        'dbh' => 'DBH', 'alternote' => '特殊修改', 'note' => 'note',
    ]);

    expect($result['differences'])->toHaveCount(1)
        ->and($result['differences'][0]['field'])->toBe('note')
        ->and($result['differences'][0]['first'])->toBe('甲')
        ->and($result['differences'][0]['second'])->toBe('乙');
});
