<?php

namespace App\Services\Web;

use App\Models\Web\Publication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ZoteroPublicationSync
{
    public function sync(): array
    {
        $userId = config('publication_sync.zotero_user_id');
        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            throw new RuntimeException('請設定有效的 ZOTERO_USER_ID。');
        }

        $lock = Cache::lock('changyang-publication-sync', 7200);
        if (! $lock->get()) {
            throw new RuntimeException('文獻同步正在執行，請稍後再試。');
        }

        try {
            $created = 0;
            $matched = 0;
            $skipped = 0;
            $start = 0;

            do {
                $response = Http::acceptJson()->withHeaders(['Zotero-API-Version' => '3'])
                    ->connectTimeout(10)->timeout(30)->retry(3, 1000)
                    ->get("https://api.zotero.org/users/{$userId}/publications/items/top", [
                        'format' => 'json', 'limit' => 100, 'start' => $start,
                    ])->throw();
                $items = $response->json();
                if (! is_array($items)) {
                    throw new RuntimeException('Zotero 回傳格式異常，未完成同步。');
                }
                $total = (int) $response->header('Total-Results', count($items));

                foreach ($items as $item) {
                    $data = $this->metadata($item['data'] ?? []);
                    $key = $item['key'] ?? null;
                    if (! is_string($key) || $key === '' || blank($data['title'])) {
                        $skipped++;
                        Log::warning('Skipping Zotero publication without an item key or title.');

                        continue;
                    }

                    try {
                        $publication = Publication::query()->where('zotero_id', $key)->first()
                            ?? PublicationIdentity::existing($data);
                    } catch (ValidationException) {
                        $skipped++;
                        Log::warning('Zotero publication matched multiple existing records.', ['zotero_id' => $key]);

                        continue;
                    }

                    if ($publication !== null) {
                        $updates = ['is_changyang' => true];
                        if (blank($publication->zotero_id)) {
                            $updates['zotero_id'] = $key;
                        }
                        $publication->update($updates);
                        $matched++;

                        continue;
                    }

                    Publication::create(array_merge($data, [
                        'zotero_id' => $key,
                        'is_changyang' => true,
                        'is_active' => true,
                        'site_review_status' => 'pending',
                    ]));
                    $created++;
                }

                $start += count($items);
            } while ($start < $total && $items !== []);

            return compact('created', 'matched', 'skipped');
        } finally {
            $lock->release();
        }
    }

    private function metadata(array $item): array
    {
        $authors = collect($item['creators'] ?? [])
            ->filter(fn (array $creator): bool => ($creator['creatorType'] ?? '') === 'author')
            ->map(fn (array $creator): string => filled($creator['name'] ?? null) ? $creator['name'] : trim(($creator['firstName'] ?? '').' '.($creator['lastName'] ?? '')))
            ->filter()->implode('; ');
        preg_match('/\b(\d{4})\b/', (string) ($item['date'] ?? ''), $year);

        return PublicationTextNormalizer::metadata([
            'title' => trim(strip_tags((string) ($item['title'] ?? ''))),
            'authors' => $authors,
            'year' => $year[1] ?? null,
            'journal' => $item['publicationTitle'] ?? $item['bookTitle'] ?? null,
            'volume' => $item['volume'] ?? null,
            'issue' => $item['issue'] ?? null,
            'pages' => $item['pages'] ?? null,
            'doi' => PublicationIdentity::doi($item['DOI'] ?? null) ?: null,
            'url' => is_string($item['url'] ?? null) && preg_match('~^https?://~i', $item['url']) ? $item['url'] : null,
            'type' => match ($item['itemType'] ?? '') {
                'journalArticle' => 'journalArticle', 'book', 'bookSection' => 'book',
                'thesis' => 'thesis', 'dataset' => 'dataset', 'preprint' => 'preprint', default => 'paper',
            },
            'language' => str_starts_with(strtolower((string) ($item['language'] ?? '')), 'zh') ? 'zh' : 'en',
        ]);
    }
}
