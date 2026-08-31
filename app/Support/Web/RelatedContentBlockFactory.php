<?php

namespace App\Support\Web;

use App\Models\Web\ContentBlock;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class RelatedContentBlockFactory
{
    private const DEFINITIONS = [
        'research_outputs' => [
            'title_zh_tw' => '研究成果',
            'title_en' => 'Research Results',
            'view' => 'web.research-output-list',
        ],
        'projects' => [
            'title_zh_tw' => '研究計畫',
            'title_en' => 'Research Projects',
            'view' => 'web.project-list',
        ],
        'publications' => [
            'title_zh_tw' => '學術產出',
            'title_en' => 'Publications',
            'view' => 'web.publication-list',
        ],
    ];

    public static function forSite(int $siteId): Collection
    {
        $filters = ['site' => (string) $siteId];

        return collect([
            self::block('projects', $filters),
            self::block('publications', $filters),
        ]);
    }

    public static function forSubject(int $subjectId): Collection
    {
        $filters = ['subject' => (string) $subjectId];

        return collect([
            self::block('projects', $filters + ['includeAllTags' => true]),
            self::block('publications', $filters + ['includeAllTags' => true]),
        ]);
    }

    public static function forResearchOutput(?int $siteId, ?int $subjectId): Collection
    {
        $filters = array_filter([
            'site' => $siteId ? (string) $siteId : null,
            'subject' => $subjectId ? (string) $subjectId : null,
        ]);

        return collect([
            self::block('projects', $filters),
            self::block('publications', $filters),
        ]);
    }

    public static function block(
        string $type,
        array $filters = [],
        ?string $titleZhTw = null,
        ?string $titleEn = null,
    ): ContentBlock {
        $definition = self::DEFINITIONS[$type] ?? throw new InvalidArgumentException("Unknown related content type [{$type}].");

        return ContentBlock::systemBlock([
            'title_zh_tw' => $titleZhTw ?? $definition['title_zh_tw'],
            'title_en' => $titleEn ?? $definition['title_en'],
            'view' => $definition['view'],
            'params' => $filters,
            'is_public' => true,
        ]);
    }
}
