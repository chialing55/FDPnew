<?php

namespace App\Console\Commands;

use App\Services\ChangYangArchiveConverter;
use App\Services\ChangYangMediaRelocator;
use Illuminate\Console\Command;
use RuntimeException;

class ExportChangYangArchive extends Command
{
    protected $signature = 'changyang:export-archive {--output=database/data/changyang-content.json}';

    protected $description = 'Convert the archived Weebly pages into deterministic Changyang import data';

    public function handle(ChangYangArchiveConverter $converter, ChangYangMediaRelocator $mediaRelocator): int
    {
        $output = base_path($this->option('output'));
        $directory = dirname($output);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create {$directory}");
        }

        $data = $converter->convert();
        $mediaRelocator->publish($data['media_manifest']);
        file_put_contents($output, json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ).PHP_EOL);

        $sections = collect($data['pages'])->sum(fn (array $page): int => count($page['sections']));
        $blocks = collect($data['pages'])->sum(fn (array $page): int => collect($page['sections'])->sum(fn (array $section): int => count($section['blocks'])));
        $blockImages = collect($data['pages'])->sum(fn (array $page): int => collect($page['sections'])->sum(
            fn (array $section): int => collect($section['blocks'])->sum(fn (array $block): int => count($block['images']))
        ));
        $galleryItems = collect($data['galleries'])->sum(fn (array $gallery): int => count($gallery['items']));

        $this->info("Wrote {$output}");
        $this->table(['Pages', 'Sections', 'Blocks', 'Block images', 'News', 'Galleries', 'Gallery images'], [[
            count($data['pages']), $sections, $blocks, $blockImages, count($data['news']), count($data['galleries']), $galleryItems,
        ]]);

        return self::SUCCESS;
    }
}
