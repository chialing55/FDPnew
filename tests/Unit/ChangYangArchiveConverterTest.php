<?php

use App\Services\ChangYangArchiveConverter;

uses(Tests\TestCase::class);

it('converts the archived Changyang content into the agreed database structure', function () {
    $data = app(ChangYangArchiveConverter::class)->convert();

    expect($data['pages'])->toHaveCount(8)
        ->and(collect($data['pages'])->pluck('slug')->all())->toBe([
            'home', 'news', 'people', 'research', 'publications', 'courses', 'gallery', 'resources',
        ])
        ->and($data['news'])->toHaveCount(58)
        ->and($data['galleries'])->toHaveCount(2)
        ->and(collect($data['galleries'])->pluck('title')->all())->toBe(['Fushan', 'BCI'])
        ->and(collect($data['galleries'])->sum(fn (array $gallery): int => count($gallery['items'])))->toBe(37);

    $people = collect($data['pages'])->firstWhere('slug', 'people');
    expect(collect($people['sections'])->pluck('heading')->all())->toBe([
        'Principle Investigator (PI)',
        'Research Assistants 研究助理',
        'Graduate students 研究生',
        'Undergraduate students 大學部專題生',
        'Lab alumni',
    ])
        ->and(collect($people['sections'])->sum(fn (array $section): int => count($section['blocks'])))->toBe(16)
        ->and(collect($people['sections'])->flatMap(fn (array $section): array => $section['blocks'])->pluck('heading')->filter()->values()->all())
        ->toContain('Chia-Hao Chang-Yang (張楊家豪)', '蔡佳秀', '鄧永淥', '蔣寶慧 Po-Hui Chiang')
        ->and(json_encode($people))->not->toContain('<table', 'wsite-multicol');

    $principalInvestigator = $people['sections'][0]['blocks'][0];
    expect($principalInvestigator['media_content_html'])
        ->toContain('Assistant Professor', 'changyang@mail.nsysu.edu.tw', '+886-7-5252000 ext 3610')
        ->and($principalInvestigator['content_html'])->not->toContain('Assistant Professor');

    $firstAssistantImage = $people['sections'][1]['blocks'][0]['images'][0];
    expect($firstAssistantImage['display_settings'])->toBe([
        'frame_width' => '342px',
        'frame_height' => '307px',
        'object_fit' => 'cover',
        'position_x' => '50%',
        'position_y' => '50%',
    ]);

    $publications = collect($data['pages'])->firstWhere('slug', 'publications');
    expect($publications['sections'])->toBe([]);

    $research = collect($data['pages'])->firstWhere('slug', 'research');
    expect($research['sections'])->toHaveCount(3)
        ->and(collect($research['sections'])->flatMap(fn (array $section): array => $section['blocks'])->pluck('layout')->all())
        ->toBe(['image_left', 'image_right', 'image_left'])
        ->and(collect($research['sections'])->sum(fn (array $section): int => count($section['blocks'][0]['images'])))->toBe(3)
        ->and(json_encode($research))->not->toContain('<table', 'wsite-multicol', 'style=');

    $resources = collect($data['pages'])->firstWhere('slug', 'resources');
    expect($resources['sections'])->toHaveCount(3)
        ->and(collect($resources['sections'])->map(fn (array $section): int => count($section['blocks']))->all())
        ->toBe([1, 1, 1]);
    foreach ($resources['sections'] as $section) {
        expect($section['blocks'][0]['content_html'])
            ->toStartWith('<ul>')
            ->not->toContain('class=', 'style=', 'wsite-');
    }
});

it('maps every referenced media file to the public storage disk', function () {
    $data = app(ChangYangArchiveConverter::class)->convert();
    expect($data['media_manifest'])->not->toBeEmpty();

    foreach ($data['media_manifest'] as $entry) {
        expect($entry['source'])->toStartWith('changyang-assets/')
            ->and(public_path($entry['source']))->toBeFile("Missing archived source asset: {$entry['source']}")
            ->and($entry['target'])->toStartWith('changyang/');
    }
});
