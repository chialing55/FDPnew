<?php

use App\Models\Web\Publication;
use App\Services\Web\PublicationTextNormalizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $fields = [
            'authors', 'authors_zh_tw', 'title', 'title_zh_tw', 'journal', 'journal_zh_tw',
            'institution', 'institution_zh_tw', 'volume', 'issue', 'pages',
        ];

        Publication::query()->select(array_merge(['id'], $fields))->orderBy('id')->chunkById(100, function ($publications) use ($fields): void {
            foreach ($publications as $publication) {
                $updates = [];

                foreach ($fields as $field) {
                    $normalized = PublicationTextNormalizer::text($publication->{$field});
                    if ($normalized !== $publication->{$field}) {
                        $updates[$field] = $normalized;
                    }
                }

                if ($updates !== []) {
                    $publication->update($updates);
                }
            }
        });
    }

    public function down(): void
    {
        // Replacing Unicode hyphens is intentionally irreversible.
    }
};
