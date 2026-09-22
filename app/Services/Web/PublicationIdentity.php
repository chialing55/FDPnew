<?php

namespace App\Services\Web;

use App\Models\Web\Publication;
use Illuminate\Validation\ValidationException;

class PublicationIdentity
{
    public static function doi(?string $value): string
    {
        return mb_strtolower(trim(preg_replace('~^(?:https?://(?:dx\.)?doi\.org/|doi:\s*)~i', '', trim($value ?? ''))));
    }

    public static function title(?string $value): string
    {
        return preg_replace('/[\p{Z}\p{P}\s]+/u', '', mb_strtolower(strip_tags($value ?? '')));
    }

    public static function fingerprint(array $data): string
    {
        $doi = static::doi($data['doi'] ?? null);

        return hash('sha256', $doi !== '' ? 'doi:'.$doi : 'title:'.static::title($data['title'] ?? null).':'.($data['year'] ?? ''));
    }

    public static function existing(array $data): ?Publication
    {
        $doi = static::doi($data['doi'] ?? null);
        $title = static::title($data['title'] ?? null);
        $records = Publication::query()->get();
        $matches = $doi === '' ? collect() : $records->filter(fn ($record) => static::doi($record->doi) === $doi);
        if ($matches->isEmpty() && $title !== '') {
            $matches = $records->filter(fn ($record) => static::title($record->title) === $title
                && (string) $record->year === (string) ($data['year'] ?? '')
                && ($doi === '' || static::doi($record->doi) === '' || static::doi($record->doi) === $doi));
        }
        if ($matches->count() > 1) {
            throw ValidationException::withMessages(['data.title' => '找到多筆相同文獻，請核對後手動指定要沿用的資料。']);
        }

        return $matches->first();
    }
}
