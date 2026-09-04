<?php

namespace App\Support\TreeEntry;

final class TreeEntryValidationResult
{
    public function __construct(
        public readonly array $rows,
        public readonly array $errors,
    ) {
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }
}
