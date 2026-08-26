<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

class ChangYangArchiveConverter
{
    public function __construct(private readonly ChangYangMediaRelocator $mediaRelocator) {}

    private const PAGE_DEFINITIONS = [
        'home' => ['file' => 'index.html', 'title' => 'Plant Ecology Lab at NSYSU', 'nav' => 'Home', 'template' => 'home', 'order' => 1],
        'news' => ['file' => 'news.html', 'title' => 'News', 'nav' => 'News', 'template' => 'news', 'order' => 2],
        'people' => ['file' => 'people.html', 'title' => 'People', 'nav' => 'People', 'template' => 'people', 'order' => 3],
        'research' => ['file' => 'research.html', 'title' => 'Research', 'nav' => 'Research', 'template' => 'default', 'order' => 4],
        'publications' => ['file' => 'publications.html', 'title' => 'Publications', 'nav' => 'Publications', 'template' => 'publications', 'order' => 5],
        'courses' => ['file' => 'courses.html', 'title' => 'Courses', 'nav' => 'Courses', 'template' => 'default', 'order' => 6],
        'gallery' => ['file' => 'gallery.html', 'title' => 'Gallery', 'nav' => 'Gallery', 'template' => 'gallery', 'order' => 7],
        'resources' => ['file' => 'resources.html', 'title' => 'Resources', 'nav' => 'Resources', 'template' => 'default', 'order' => 8],
    ];

    private const MONTHS = [
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
        'jul' => 7, 'aug' => 8, 'sep' => 9, 'sept' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
    ];

    public function convert(): array
    {
        $pages = [];
        foreach (self::PAGE_DEFINITIONS as $slug => $definition) {
            $pages[] = $this->convertPage($slug, $definition);
        }

        return $this->mediaRelocator->relocate([
            'version' => 1,
            'source' => 'changyang.weebly.com archive',
            'pages' => $pages,
            'news' => $this->convertNews(),
            'galleries' => $this->convertGalleries(),
        ]);
    }

    private function convertPage(string $slug, array $definition): array
    {
        [$document, $xpath] = $this->loadDocument($definition['file']);
        $hero = $this->extractHero($document, $xpath, $slug);
        $skipContent = in_array($slug, ['news', 'gallery', 'publications'], true);

        return [
            'slug' => $slug,
            'title' => $definition['title'],
            'navigation_label' => $definition['nav'] ?? null,
            'template' => $definition['template'],
            'hero_image_path' => $hero['image_path'],
            'hero_image_alt' => $hero['image_alt'],
            'hero_title' => $hero['title'],
            'hero_subtitle' => $hero['subtitle'],
            'hero_settings' => $hero['settings'],
            'meta_description' => null,
            'show_in_navigation' => isset($definition['nav']),
            'navigation_order' => $definition['order'] ?? 0,
            'is_active' => true,
            'sections' => $skipContent
                ? []
                : ($slug === 'people'
                    ? $this->extractPeopleSections($document, $xpath)
                    : ($slug === 'research'
                        ? $this->extractResearchSections($document, $xpath)
                        : ($slug === 'resources'
                            ? $this->extractResourceSections($document, $xpath)
                            : $this->extractSections($document, $xpath, $slug === 'home')))),
        ];
    }

    private function extractResourceSections(DOMDocument $document, DOMXPath $xpath): array
    {
        $groups = $this->extractSectionGroups($xpath);

        return array_values(array_map(function (array $group, int $sectionIndex) use ($document): array {
            [, $fragmentXpath, $root] = $this->loadFragment($this->serializeNodes($document, $group['nodes']));
            $blocks = [];
            $list = $fragmentXpath->query('.//ul|.//ol', $root)?->item(0);
            if ($list instanceof DOMElement) {
                $content = $this->normalizeRichTextFragment($list->ownerDocument?->saveHTML($list) ?? '');
                $blocks[] = [
                    'type' => 'rich_text',
                    'layout' => 'text_only',
                    'heading' => null,
                    'media_content_html' => null,
                    'content_html' => $content,
                    'settings' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                    'images' => [],
                ];
            }

            return [
                'heading' => $group['heading'],
                'subheading' => null,
                'settings' => null,
                'sort_order' => $sectionIndex + 1,
                'is_active' => true,
                'blocks' => $blocks,
            ];
        }, $groups, array_keys($groups)));
    }

    private function extractResearchSections(DOMDocument $document, DOMXPath $xpath): array
    {
        $groups = $this->extractSectionGroups($xpath);

        return array_values(array_map(function (array $group, int $index) use ($document): array {
            [$fragmentDocument, $fragmentXpath, $root] = $this->loadFragment($this->serializeNodes($document, $group['nodes']));
            $table = $fragmentXpath->query('.//table[contains(concat(" ", normalize-space(@class), " "), " wsite-multicol-table ")]', $root)?->item(0);
            $blocks = [];

            if ($table instanceof DOMElement) {
                $cells = iterator_to_array($fragmentXpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " wsite-multicol-col ")]', $table) ?: []);
                $imageCellIndex = 0;
                foreach ($cells as $cellIndex => $cell) {
                    if ($cell instanceof DOMElement && $fragmentXpath->query('.//img', $cell)?->length > 0) {
                        $imageCellIndex = $cellIndex;
                        break;
                    }
                }
                $bodyCellIndex = $imageCellIndex === 0 ? 1 : 0;
                $images = $this->extractImages($fragmentDocument->saveHTML($table));
                if (isset($images[0])) {
                    $images[0]['display_settings'] = [
                        'frame_width' => '342px',
                        'object_fit' => 'cover',
                        'position_x' => '50%',
                        'position_y' => '50%',
                    ];
                }
                $content = isset($cells[$bodyCellIndex]) && $cells[$bodyCellIndex] instanceof DOMElement
                    ? $this->normalizePersonCell($cells[$bodyCellIndex])
                    : '';

                $blocks[] = [
                    'type' => 'image_text',
                    'layout' => $imageCellIndex === 0 ? 'image_left' : 'image_right',
                    'heading' => null,
                    'media_content_html' => null,
                    'content_html' => $content !== '' ? $content : null,
                    'settings' => null,
                    'sort_order' => 1,
                    'is_active' => true,
                    'images' => array_slice($images, 0, 1),
                ];
            }

            return [
                'heading' => $group['heading'],
                'subheading' => null,
                'settings' => null,
                'sort_order' => $index + 1,
                'is_active' => true,
                'blocks' => $blocks,
            ];
        }, $groups, array_keys($groups)));
    }

    private function extractPeopleSections(DOMDocument $document, DOMXPath $xpath): array
    {
        $sections = $this->extractSectionGroups($xpath);

        return array_values(array_map(function (array $group, int $sectionIndex) use ($document): array {
            $groupHtml = $this->serializeNodes($document, $group['nodes']);
            [$groupDocument, $groupXpath, $root] = $this->loadFragment($groupHtml);
            $blocks = [];

            $tables = iterator_to_array($groupXpath->query('.//table[contains(concat(" ", normalize-space(@class), " "), " wsite-multicol-table ")]', $root) ?: []);
            foreach ($tables as $table) {
                if (! $table instanceof DOMElement) {
                    continue;
                }

                $headingNode = $groupXpath->query('.//h2[1]', $table)?->item(0);
                $heading = $headingNode instanceof DOMElement ? $this->cleanText($headingNode->textContent) : null;
                $images = $this->extractImages($groupDocument->saveHTML($table));
                if (isset($images[0])) {
                    $images[0]['display_settings'] = $this->peopleImageDisplaySettings($images[0]['image_path']);
                }
                $cells = iterator_to_array($groupXpath->query('.//td[contains(concat(" ", normalize-space(@class), " "), " wsite-multicol-col ")]', $table) ?: []);
                $meta = isset($cells[0]) && $cells[0] instanceof DOMElement ? $this->normalizePersonCell($cells[0]) : '';
                $body = isset($cells[1]) && $cells[1] instanceof DOMElement ? $this->normalizePersonCell($cells[1]) : '';
                $content = trim($body);

                if ($heading === null && $content === '' && $images === []) {
                    continue;
                }

                $blocks[] = [
                    'type' => $images === [] ? 'rich_text' : 'person',
                    'layout' => $images === [] ? 'text_only' : 'image_left',
                    'heading' => $heading,
                    'media_content_html' => $meta !== '' ? $meta : null,
                    'content_html' => $content !== '' ? $content : null,
                    'settings' => null,
                    'sort_order' => count($blocks) + 1,
                    'is_active' => true,
                    'images' => array_slice($images, 0, 1),
                ];
            }

            foreach ($tables as $table) {
                $table->parentNode?->removeChild($table);
            }
            $remainingHtml = $this->normalizeRichTextFragment($this->innerHtml($root));
            if ($this->cleanText(strip_tags($remainingHtml)) !== '') {
                $blocks[] = [
                    'type' => 'rich_text',
                    'layout' => 'text_only',
                    'heading' => null,
                    'media_content_html' => null,
                    'content_html' => $remainingHtml,
                    'settings' => null,
                    'sort_order' => count($blocks) + 1,
                    'is_active' => true,
                    'images' => [],
                ];
            }

            return [
                'heading' => $group['heading'],
                'subheading' => null,
                'settings' => null,
                'sort_order' => $sectionIndex + 1,
                'is_active' => true,
                'blocks' => $blocks,
            ];
        }, $sections, array_keys($sections)));
    }

    private function extractSectionGroups(DOMXPath $xpath): array
    {
        $groups = [];
        $wrappers = $xpath->query("//*[@id='wsite-content']/div[contains(concat(' ', normalize-space(@class), ' '), ' wsite-section-wrap ')]");

        foreach ($wrappers ?: [] as $wrapper) {
            $container = $xpath->query(".//div[contains(concat(' ', normalize-space(@class), ' '), ' wsite-section-elements ')]", $wrapper)?->item(0);
            if (! $container instanceof DOMElement) {
                continue;
            }

            $current = ['heading' => null, 'nodes' => []];
            foreach ($container->childNodes as $node) {
                if ($node instanceof DOMElement && strtolower($node->tagName) === 'h2') {
                    if ($current['heading'] !== null || $this->nodesHaveContent($current['nodes'])) {
                        $groups[] = $current;
                    }
                    $current = ['heading' => $this->cleanHeading($node), 'nodes' => []];
                } else {
                    $current['nodes'][] = $node;
                }
            }
            if ($current['heading'] !== null || $this->nodesHaveContent($current['nodes'])) {
                $groups[] = $current;
            }
        }

        return array_values(array_filter($groups, fn (array $group): bool => $group['heading'] !== null));
    }

    private function normalizePersonCell(DOMElement $cell): string
    {
        [, $xpath, $root] = $this->loadFragment($this->innerHtml($cell));
        $remove = iterator_to_array($xpath->query('.//h2|.//*[contains(concat(" ", normalize-space(@class), " "), " wsite-image ")]|.//hr', $root) ?: []);
        foreach ($remove as $node) {
            $node->parentNode?->removeChild($node);
        }

        return $this->normalizeRichTextFragment($this->innerHtml($root));
    }

    private function normalizeRichTextFragment(string $html): string
    {
        [, $xpath, $root] = $this->loadFragment($html);
        $elements = iterator_to_array($xpath->query('.//*', $root) ?: []);
        foreach ($elements as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }
            foreach (['style', 'id', 'size', 'color'] as $attribute) {
                $element->removeAttribute($attribute);
            }
            if ($element->hasAttribute('class')) {
                $classes = preg_split('/\s+/', trim($element->getAttribute('class'))) ?: [];
                if (! in_array('paragraph', $classes, true)) {
                    $element->removeAttribute('class');
                } else {
                    $element->setAttribute('class', 'paragraph');
                }
            }
        }

        $html = $this->cleanHtml($this->innerHtml($root));
        $html = preg_replace('#<div class="paragraph">(.*?)</div>#is', '<p>$1</p>', $html) ?? $html;
        do {
            $previousHtml = $html;
            $html = preg_replace('#<(?:font|span)(?:\s[^>]*)?>(.*?)</(?:font|span)>#is', '$1', $html) ?? $html;
        } while ($html !== $previousHtml);
        $html = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $html) ?? $html;
        $html = preg_replace('#<(?:strong|p)>\s*(?:<br\s*/?>|&nbsp;)*\s*</(?:strong|p)>#is', '', $html) ?? $html;
        $html = preg_replace('#(?:<br\s*/?>\s*)+</p>#i', '</p>', $html) ?? $html;
        $html = preg_replace('#(?:<br\s*/?>\s*)+$#i', '', $html) ?? $html;
        $html = preg_replace('#<div>\s*(?:<br\s*/?>|&nbsp;|\x{200B})*\s*</div>#iu', '', $html) ?? $html;
        $html = preg_replace('#<a(?:\s[^>]*)?>\s*</a>#is', '', $html) ?? $html;

        return trim($html);
    }

    private function loadFragment(string $html): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="fragment-root">'.$html.'</div></body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($document);
        $root = $xpath->query('//*[@id="fragment-root"]')?->item(0);
        if (! $root instanceof DOMElement) {
            throw new RuntimeException('Unable to parse an archived content fragment.');
        }

        return [$document, $xpath, $root];
    }

    private function extractHero(DOMDocument $document, DOMXPath $xpath, string $slug): array
    {
        $hero = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' banner-wrap ')]//div[contains(concat(' ', normalize-space(@class), ' '), ' wsite-section ')]")?->item(0);

        if (! $hero instanceof DOMElement && $slug === 'home') {
            $hero = $xpath->query("//*[@id='wsite-content']/div[contains(concat(' ', normalize-space(@class), ' '), ' wsite-section-wrap ')][1]//div[contains(concat(' ', normalize-space(@class), ' '), ' wsite-section ')]")?->item(0);
        }

        if (! $hero instanceof DOMElement) {
            return ['image_path' => null, 'image_alt' => null, 'title' => null, 'subtitle' => null, 'settings' => null];
        }

        $style = $hero->getAttribute('style');
        preg_match('#(?:/)?uploads/[^"\') ;]+#i', html_entity_decode($style), $imageMatch);
        preg_match('/height:\s*([^;]+)/i', $style, $heightMatch);
        preg_match('/background-position:\s*([^;]+)/i', $style, $positionMatch);

        $headings = [];
        foreach ($xpath->query('.//h1|.//h2|.//div[contains(concat(" ", normalize-space(@class), " "), " paragraph ")]', $hero) ?: [] as $node) {
            $text = $this->cleanText($node->textContent);
            if ($text !== '' && ! in_array($text, $headings, true)) {
                $headings[] = $text;
            }
        }

        return [
            'image_path' => isset($imageMatch[0]) ? $this->assetPath($imageMatch[0]) : null,
            'image_alt' => $definitionTitle = self::PAGE_DEFINITIONS[$slug]['title'] ?? null,
            'title' => $headings[0] ?? $definitionTitle,
            'subtitle' => $headings[1] ?? null,
            'settings' => array_filter([
                'height' => isset($heightMatch[1]) ? trim($heightMatch[1]) : null,
                'position' => isset($positionMatch[1]) ? trim($positionMatch[1]) : null,
                'overlay_opacity' => $slug === 'home' ? 0.08 : 0.3,
            ], fn ($value): bool => $value !== null),
        ];
    }

    private function extractSections(DOMDocument $document, DOMXPath $xpath, bool $skipFirstWrapper): array
    {
        $wrappers = $xpath->query("//*[@id='wsite-content']/div[contains(concat(' ', normalize-space(@class), ' '), ' wsite-section-wrap ')]");
        $sections = [];
        $sectionOrder = 1;

        foreach ($wrappers ?: [] as $wrapperIndex => $wrapper) {
            if ($skipFirstWrapper && $wrapperIndex === 0) {
                continue;
            }

            $container = $xpath->query(".//div[contains(concat(' ', normalize-space(@class), ' '), ' wsite-section-elements ')]", $wrapper)?->item(0);
            if (! $container instanceof DOMElement) {
                continue;
            }

            $groups = [];
            $current = ['heading' => null, 'nodes' => []];
            foreach ($container->childNodes as $node) {
                if ($node instanceof DOMElement && strtolower($node->tagName) === 'h2') {
                    if ($current['heading'] !== null || $this->nodesHaveContent($current['nodes'])) {
                        $groups[] = $current;
                    }
                    $current = ['heading' => $this->cleanText($node->textContent), 'nodes' => []];
                    continue;
                }

                $current['nodes'][] = $node;
            }
            if ($current['heading'] !== null || $this->nodesHaveContent($current['nodes'])) {
                $groups[] = $current;
            }

            foreach ($groups as $group) {
                $html = $this->cleanHtml($this->serializeNodes($document, $group['nodes']));
                $images = $this->extractImages($html);
                $hasText = $this->cleanText(strip_tags($html)) !== '';

                $blocks = [];
                if ($html !== '') {
                    $blocks[] = [
                        'type' => $images === [] ? 'rich_text' : 'image_text',
                        'layout' => count($images) > 1 ? 'image_grid' : ($images === [] ? 'text_only' : 'image_left'),
                        'heading' => null,
                        'content_html' => $html,
                        'settings' => null,
                        'sort_order' => 1,
                        'is_active' => true,
                        'images' => $images,
                    ];
                }

                if ($group['heading'] !== null || $hasText || $images !== []) {
                    $sections[] = [
                        'heading' => $group['heading'],
                        'subheading' => null,
                        'settings' => null,
                        'sort_order' => $sectionOrder++,
                        'is_active' => true,
                        'blocks' => $blocks,
                    ];
                }
            }
        }

        return $sections;
    }

    private function convertNews(): array
    {
        [, $xpath] = $this->loadDocument('news.html');
        $rootList = $xpath->query("//*[@id='wsite-content']//div[contains(concat(' ', normalize-space(@class), ' '), ' paragraph ')]/ul")?->item(0);
        if (! $rootList instanceof DOMElement) {
            throw new RuntimeException('Unable to find the archived News list.');
        }

        $news = [];
        foreach ($this->elementChildren($rootList, 'li') as $category) {
            $nestedList = $this->firstElementChild($category, 'ul');
            if (! $nestedList instanceof DOMElement) {
                continue;
            }

            $heading = '';
            foreach ($category->childNodes as $node) {
                if ($node === $nestedList) {
                    break;
                }
                $heading .= ' '.$node->textContent;
            }

            if (! preg_match('/\b([A-Za-z]{3,4})\.?\s+(20\d{2})\b/u', $this->cleanText($heading), $match)) {
                continue;
            }

            $month = self::MONTHS[strtolower($match[1])] ?? null;
            if ($month === null) {
                continue;
            }

            foreach ($this->elementChildren($nestedList, 'li') as $index => $item) {
                $content = $this->cleanHtml($this->innerHtml($item));
                if ($this->cleanText(strip_tags($content)) === '') {
                    continue;
                }
                $news[] = [
                    'category_year' => (int) $match[2],
                    'category_month' => $month,
                    'content_html' => $content,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ];
            }
        }

        return $news;
    }

    private function convertGalleries(): array
    {
        $galleries = [];
        foreach ([['title' => 'Fushan', 'file' => 'fushan.html'], ['title' => 'BCI', 'file' => 'bci.html']] as $galleryOrder => $definition) {
            [, $xpath] = $this->loadDocument($definition['file']);
            $items = [];
            $seen = [];
            foreach ($xpath->query("//*[@id='wsite-content']//img[contains(concat(' ', normalize-space(@class), ' '), ' galleryImage ')]") ?: [] as $image) {
                if (! $image instanceof DOMElement) {
                    continue;
                }

                $link = $image->parentNode instanceof DOMElement && strtolower($image->parentNode->tagName) === 'a'
                    ? $image->parentNode->getAttribute('href')
                    : '';
                $path = $this->assetPath($link !== '' ? $link : $image->getAttribute('src'));
                $thumbnail = $this->assetPath($image->getAttribute('src'));
                if ($path === null || isset($seen[$path])) {
                    continue;
                }
                $seen[$path] = true;
                $items[] = [
                    'image_path' => $path,
                    'thumbnail_path' => $thumbnail !== $path ? $thumbnail : null,
                    'title' => null,
                    'caption' => null,
                    'alt_text' => $this->cleanText($image->getAttribute('alt')) ?: null,
                    'sort_order' => count($items) + 1,
                    'is_active' => true,
                ];
            }

            $galleries[] = [
                'title' => $definition['title'],
                'description' => null,
                'cover_image_path' => $items[0]['thumbnail_path'] ?? $items[0]['image_path'] ?? null,
                'sort_order' => $galleryOrder + 1,
                'is_active' => true,
                'items' => $items,
            ];
        }

        return $galleries;
    }

    private function cleanHtml(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace_callback('#(?:/)?uploads/9/7/1/8/97185006/[^"\'<> )]+#i', function (array $match): string {
            $path = $this->assetPath($match[0]);

            return $path === null ? $match[0] : '/'.$path;
        }, $html) ?? $html;
        $html = preg_replace_callback('/href=("|\')([^"\']+)\.html(#[^"\']*)?\1/i', function (array $match): string {
            $slug = basename($match[2]);
            $slug = $slug === 'index' ? 'home' : $slug;

            return isset(self::PAGE_DEFINITIONS[$slug])
                ? 'href='.$match[1].'/changyang'.($slug === 'home' ? '' : '/'.$slug).($match[3] ?? '').$match[1]
                : $match[0];
        }, $html) ?? $html;
        $html = preg_replace('/\s+id=("|\')[^"\']*\1/i', '', $html) ?? $html;

        return trim($html);
    }

    private function extractImages(string $html): array
    {
        if ($html === '') {
            return [];
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="root">'.$html.'</div></body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($document);
        $images = [];
        $seen = [];

        foreach ($xpath->query('//*[@id="root"]//img') ?: [] as $image) {
            if (! $image instanceof DOMElement) {
                continue;
            }
            $path = $this->assetPath($image->getAttribute('src'));
            if ($path === null || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;
            $images[] = [
                'image_path' => $path,
                'alt_text' => $this->cleanText($image->getAttribute('alt')) ?: null,
                'caption' => null,
                'link_url' => null,
                'display_settings' => null,
                'sort_order' => count($images) + 1,
            ];
        }

        return $images;
    }

    private function peopleImageDisplaySettings(string $path): array
    {
        return array_filter([
            'frame_width' => '342px',
            'frame_height' => str_ends_with($path, '/1738022702_orig.jpg') ? '307px' : null,
            'object_fit' => 'cover',
            'position_x' => '50%',
            'position_y' => '50%',
        ], fn ($value): bool => $value !== null);
    }

    private function assetPath(string $source): ?string
    {
        $source = html_entity_decode(trim($source));
        $source = preg_replace('/[?#].*$/', '', $source) ?? $source;
        if (! preg_match('#(?:/)?uploads/(9/7/1/8/97185006/.+)$#i', $source, $match)) {
            if (str_starts_with($source, '/changyang-assets/')) {
                return ltrim($source, '/');
            }

            return null;
        }

        $relative = $match[1];
        $relative = preg_replace('#^(9/7/1/8/97185006)/(?:editor|published)/#i', '$1/', $relative) ?? $relative;
        $target = public_path('changyang-assets/'.$relative);
        if (! is_file($target)) {
            $directory = dirname($target);
            $filename = basename($target);
            $stem = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $candidates = [
                $directory.'/'.$stem.'_orig'.($extension !== '' ? '.'.$extension : ''),
                ...(glob($directory.'/'.$stem.'_orig.*') ?: []),
            ];
            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    $relative = substr($candidate, strlen(public_path('changyang-assets/')));
                    break;
                }
            }
        }

        return 'changyang-assets/'.str_replace('\\', '/', $relative);
    }

    private function loadDocument(string $filename): array
    {
        $path = base_path('changyang/'.$filename);
        if (! is_file($path)) {
            throw new RuntimeException("Missing Changyang archive page: {$filename}");
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML((string) file_get_contents($path), LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return [$document, new DOMXPath($document)];
    }

    private function elementChildren(DOMElement $parent, string $tag): array
    {
        $children = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === strtolower($tag)) {
                $children[] = $child;
            }
        }

        return $children;
    }

    private function firstElementChild(DOMElement $parent, string $tag): ?DOMElement
    {
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === strtolower($tag)) {
                return $child;
            }
        }

        return null;
    }

    private function nodesHaveContent(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node instanceof DOMElement || $this->cleanText($node->textContent) !== '') {
                return true;
            }
        }

        return false;
    }

    private function serializeNodes(DOMDocument $document, array $nodes): string
    {
        return implode('', array_map(fn (DOMNode $node): string => $document->saveHTML($node), $nodes));
    }

    private function innerHtml(DOMElement $element): string
    {
        $html = '';
        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument?->saveHTML($child) ?? '';
        }

        return $html;
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private function cleanHeading(DOMElement $heading): string
    {
        $html = preg_replace('#<br\s*/?>#i', "\n", $this->innerHtml($heading)) ?? $this->innerHtml($heading);
        $lines = array_filter(array_map(
            fn (string $line): string => $this->cleanText(strip_tags($line)),
            explode("\n", $html)
        ), fn (string $line): bool => $line !== '');

        return implode('<br>', $lines);
    }
}
