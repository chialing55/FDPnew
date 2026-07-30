<?php

use App\Models\Web\Publication;

uses(Tests\TestCase::class);

it('uses available Chinese publication fields and falls back field by field', function () {
    app()->setLocale('zh-TW');

    $publication = new Publication([
        'authors' => 'English Author',
        'title' => 'English title',
        'journal' => 'English Journal',
        'title_zh_tw' => '中文標題',
    ]);

    expect($publication->display_authors)->toBe('English Author')
        ->and($publication->display_title)->toBe('中文標題')
        ->and($publication->display_journal)->toBe('English Journal');
});

it('always uses original publication fields on the English site', function () {
    app()->setLocale('en');

    $publication = new Publication([
        'authors' => 'English Author',
        'authors_zh_tw' => '中文作者',
        'title' => 'English title',
        'title_zh_tw' => '中文標題',
        'journal' => 'English Journal',
        'journal_zh_tw' => '中文期刊',
    ]);

    expect($publication->display_authors)->toBe('English Author')
        ->and($publication->display_title)->toBe('English title')
        ->and($publication->display_journal)->toBe('English Journal');
});
