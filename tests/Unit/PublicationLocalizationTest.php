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

it('formats ChangYang journal citations without duplicate punctuation and highlights his name variants', function () {
    $publication = new Publication([
        'authors' => 'Su S.-H.; Chang-Yang C.-H.; Chia-Hao Chang-Yang.',
        'title' => 'Micro-topographic differentiation.',
        'journal' => 'Taiwan Journal of Forest Science.',
        'volume' => '25',
        'issue' => '1',
        'pages' => '63–80.',
        'type' => 'journalArticle',
    ]);

    expect($publication->chang_yang_citation_html)
        ->toBe('Su S.-H.; <strong>Chang-Yang C.-H.</strong>; <strong>Chia-Hao Chang-Yang</strong>. <strong>Micro-topographic differentiation</strong>. <em>Taiwan Journal of Forest Science</em>. 25 (1) : 63–80.')
        ->not->toContain('..');
});

it('uses distinct book and thesis citation formats on the ChangYang site', function () {
    app()->setLocale('en');

    $book = new Publication([
        'authors' => 'Chang-Yang, Chia-Hao',
        'title' => 'Forest Dynamics',
        'type' => 'book',
        'institution' => 'Forest Press',
    ]);
    $thesis = new Publication([
        'authors' => 'Chang-Yang, C.-H.',
        'title' => 'Seed Rain Dynamics',
        'type' => 'thesis',
        'thesis_type' => 'doctoral',
        'institution' => 'National Taiwan University',
    ]);

    expect($book->chang_yang_citation_html)
        ->toBe('<strong>Chang-Yang, Chia-Hao</strong>. <strong><em>Forest Dynamics</em></strong>. Forest Press.')
        ->and($thesis->chang_yang_citation_html)
        ->toBe('<strong>Chang-Yang, C.-H</strong>. <strong>Seed Rain Dynamics</strong>. Doctoral dissertation, National Taiwan University.');
});
