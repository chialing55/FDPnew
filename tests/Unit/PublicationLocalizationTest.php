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
        ->toBe('Su, S.-H., <strong>Chang-Yang, C.-H.</strong>, <strong>Chang-Yang, C.-H</strong>. <strong>Micro-topographic differentiation</strong>. <em>Taiwan Journal of Forest Science</em>. 25 (1) : 63–80.')
        ->not->toContain('..');
});

it('uses distinct book and thesis citation formats on the ChangYang site', function () {
    app()->setLocale('en');

    $book = new Publication([
        'authors' => 'Chang-Yang, Chia-Hao',
        'title' => '森林動態',
        'type' => 'book',
        'language' => 'zh-TW',
        'journal' => 'Forest Press',
    ]);
    $thesis = new Publication([
        'authors' => 'Chang-Yang, C.-H.',
        'title' => 'Seed Rain Dynamics',
        'type' => 'thesis',
        'thesis_type' => 'doctoral',
        'institution' => 'National Taiwan University',
    ]);

    expect($book->chang_yang_citation_html)
        ->toBe('<strong>Chang-Yang, C.-H</strong>. <strong><em>森林動態 (in Chinese)</em></strong>. Forest Press.')
        ->and($thesis->chang_yang_citation_html)
        ->toBe('<strong>Chang-Yang, C.-H</strong>. <strong>Seed Rain Dynamics</strong>. Doctoral dissertation, National Taiwan University.');
});

it('keeps ChangYang thesis labels in English and marks Chinese theses', function () {
    app()->setLocale('zh-TW');

    $thesis = new Publication([
        'authors' => 'Chang-Yang, Chia-Hao',
        'title' => '森林種子雨研究',
        'type' => 'thesis',
        'language' => 'zh',
        'thesis_type' => 'master',
        'institution' => 'National Taiwan University',
    ]);

    expect($thesis->chang_yang_citation_html)
        ->toBe('<strong>Chang-Yang, C.-H</strong>. <strong>森林種子雨研究 (in Chinese)</strong>. Master\'s thesis, National Taiwan University.');
});

it('normalizes given-name-first Zotero authors and compound family names', function () {
    $publication = new Publication([
        'authors' => 'Kanokporn Kaewsong; Ekaphan Kraichak; Chia-Hao Chang-Yang; Alexandre Adalardo de Oliveira',
        'title' => 'Coastal plant communities',
        'type' => 'journalArticle',
    ]);

    expect($publication->chang_yang_citation_html)
        ->toStartWith('Kaewsong, K., Kraichak, E., <strong>Chang-Yang, C.-H.</strong>, de Oliveira, A. A. <strong>Coastal plant communities</strong>.');
});

it('normalizes comma-separated initials even when surname commas are mixed in', function () {
    $publication = new Publication([
        'authors' => 'Lin Y., Chao K.-J., Song G.-Z. M., Chao W.-C., Chang-Yang C.-H., Hsieh, C.-F.',
        'title' => 'Seedling mortality',
        'type' => 'journalArticle',
    ]);

    expect($publication->chang_yang_citation_html)
        ->toStartWith('Lin, Y., Chao, K.-J., Song, G.-Z. M., Chao, W.-C., <strong>Chang-Yang, C.-H.</strong>, Hsieh, C.-F. <strong>Seedling mortality</strong>.');
});
