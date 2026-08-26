<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ChangYangMediaRelocator
{
    public function relocate(array $data): array
    {
        $manifest = [];

        foreach ($data['pages'] as &$page) {
            if ($page['hero_image_path']) {
                $page['hero_image_path'] = $this->map($manifest, $page['hero_image_path'], 'changyang/heroes/'.$page['slug']);
            }

            foreach ($page['sections'] as &$section) {
                foreach ($section['blocks'] as &$block) {
                    foreach ($block['images'] as &$image) {
                        $image['image_path'] = $this->map($manifest, $image['image_path'], 'changyang/content/'.$page['slug']);
                    }
                    unset($image);

                    if ($block['content_html'] ?? null) {
                        $block['content_html'] = $this->relocateInlineMedia($block['content_html'], $page['slug'], $manifest);
                    }
                    if ($block['media_content_html'] ?? null) {
                        $block['media_content_html'] = $this->relocateInlineMedia($block['media_content_html'], $page['slug'], $manifest);
                    }
                }
                unset($block);
            }
            unset($section);
        }
        unset($page);

        foreach ($data['galleries'] as &$gallery) {
            $directory = 'changyang/galleries/'.Str::slug($gallery['title']);
            if ($gallery['cover_image_path']) {
                $gallery['cover_image_path'] = $this->map($manifest, $gallery['cover_image_path'], $directory.'/covers');
            }
            foreach ($gallery['items'] as &$item) {
                $item['image_path'] = $this->map($manifest, $item['image_path'], $directory.'/images');
                if ($item['thumbnail_path']) {
                    $item['thumbnail_path'] = $this->map($manifest, $item['thumbnail_path'], $directory.'/thumbnails');
                }
            }
            unset($item);
        }
        unset($gallery);

        $data['media_manifest'] = array_values($manifest);

        return $data;
    }

    public function publish(array $manifest): void
    {
        $disk = Storage::disk('public');
        foreach ($manifest as $entry) {
            $source = public_path($entry['source']);
            if (! is_file($source)) {
                throw new RuntimeException("Missing Changyang source media: {$entry['source']}");
            }

            $stream = fopen($source, 'rb');
            if ($stream === false) {
                throw new RuntimeException("Unable to read Changyang source media: {$entry['source']}");
            }
            try {
                if (! $disk->put($entry['target'], $stream)) {
                    throw new RuntimeException("Unable to publish Changyang media: {$entry['target']}");
                }
            } finally {
                fclose($stream);
            }
        }
    }

    private function relocateInlineMedia(string $html, string $pageSlug, array &$manifest): string
    {
        return preg_replace_callback('#/changyang-assets/([^"\'<>\s)]+)#i', function (array $match) use ($pageSlug, &$manifest): string {
            $source = 'changyang-assets/'.html_entity_decode($match[1]);
            $extension = strtolower(pathinfo(parse_url($source, PHP_URL_PATH) ?: $source, PATHINFO_EXTENSION));
            $directory = in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'], true)
                ? 'changyang/documents'
                : 'changyang/content/'.$pageSlug;

            return '/storage/'.$this->map($manifest, $source, $directory);
        }, $html) ?? $html;
    }

    private function map(array &$manifest, string $source, string $directory): string
    {
        if (str_starts_with($source, 'changyang/')) {
            return $source;
        }

        $source = ltrim($source, '/');
        $basename = basename(parse_url($source, PHP_URL_PATH) ?: $source);
        $target = trim($directory, '/').'/'.$basename;
        $key = $source.'|'.$target;
        $manifest[$key] = ['source' => $source, 'target' => $target];

        return $target;
    }
}
