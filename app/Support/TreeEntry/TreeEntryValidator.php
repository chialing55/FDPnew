<?php

namespace App\Support\TreeEntry;

use DateTimeImmutable;

final class TreeEntryValidator
{
    public function validate(
        array $rows,
        array $previousByStemid,
        array $rules,
        string $mode = 'existing',
        array $lockedStemids = [],
        bool $skipRowsWithoutDate = false,
    ): TreeEntryValidationResult {
        $normalizedRows = [];
        $errors = [];
        $locked = array_fill_keys(array_map('strval', $lockedStemids), true);

        foreach (array_values($rows) as $index => $sourceRow) {
            $row = $this->normalize($sourceRow, $rules);
            $normalizedRows[] = $row;
            $stemid = (string) ($row['stemid'] ?? '');

            if (isset($locked[$stemid])) {
                continue;
            }
            if ($skipRowsWithoutDate && in_array((string) ($row['date'] ?? ''), ['', '0000-00-00'], true)) {
                continue;
            }

            $previous = $previousByStemid[$stemid] ?? null;
            $rowErrors = $this->validateRow($row, is_array($previous) ? $previous : null, $rules, $mode);
            foreach ($rowErrors as $error) {
                $errors[] = ['row' => $index] + $error;
            }
        }

        return new TreeEntryValidationResult($normalizedRows, $errors);
    }

    private function normalize(array $row, array $rules): array
    {
        foreach (['date', 'status', 'code', 'note', 'confirm'] as $field) {
            if (($row[$field] ?? null) === null) {
                $row[$field] = '';
            }
        }

        if ($rules['uppercaseCode'] ?? false) {
            $row['code'] = strtoupper((string) ($row['code'] ?? ''));
        }

        return $row;
    }

    private function validateRow(array $row, ?array $previous, array $rules, string $mode): array
    {
        $errors = [];
        $stemid = (string) ($row['stemid'] ?? '');
        $date = (string) ($row['date'] ?? '');
        $status = (string) ($row['status'] ?? '');
        $code = (string) ($row['code'] ?? '');
        $dbh = $row['dbh'] ?? null;
        $branch = $row['branch'] ?? $row['b'] ?? null;
        $confirm = $this->isChecked($row['confirm'] ?? '');

        $allowedStatuses = array_map('strval', $rules['allowedStatuses'] ?? []);
        if ($status !== '' && $allowedStatuses !== [] && !in_array($status, $allowedStatuses, true)) {
            $errors[] = $this->error('status', 'invalid_status', "{$stemid} status 含有不允許的值。");
        }

        if ($rules['requireDateAndDbh'] ?? false) {
            if ($date === '' || $date === '0000-00-00') {
                $errors[] = $this->error('date', 'required', "{$stemid} 需有日期資料。");
            } elseif (!$this->isValidDate($date)) {
                $errors[] = $this->error('date', 'date_format', "{$stemid} 日期格式必須為 YYYY-MM-DD。");
            }

            if ($dbh === '' || $dbh === null) {
                $errors[] = $this->error('dbh', 'required', "{$stemid} 需有 DBH 資料。");
            }
        }

        $hasNumericDbh = $dbh !== '' && $dbh !== null && is_numeric($dbh) && is_finite((float) $dbh);
        if ($dbh !== '' && $dbh !== null && !$hasNumericDbh) {
            $errors[] = $this->error('dbh', 'numeric', "{$stemid} DBH 必須是數值。");
        }

        if ($hasNumericDbh) {
            $dbhValue = (float) $dbh;
            if ($status !== '' && ($rules['statusRequiresZeroDbh'] ?? false) && $dbhValue !== 0.0) {
                $errors[] = $this->error('dbh', 'status_requires_zero', "{$stemid} status 有值時，DBH 必須為 0。");
            }
            if ($status === '' && ($rules['emptyStatusDisallowsZeroDbh'] ?? false) && $dbhValue === 0.0) {
                $errors[] = $this->error('dbh', 'active_disallows_zero', "{$stemid} status 空白時，DBH 不得為 0。");
            }
            if ($status === '' && isset($rules['minimumDbh']) && $dbhValue < (float) $rules['minimumDbh']) {
                $errors[] = $this->error('dbh', 'minimum', "{$stemid} DBH 必須大於或等於 {$rules['minimumDbh']}。");
            }
        }

        $statusExceptions = array_map('strval', $rules['statusCodeExceptions'] ?? []);
        if ($status !== '' && $code !== '' && ($rules['statusDisallowsCode'] ?? false)) {
            $codeCharacters = str_split($code);
            if (array_diff($codeCharacters, $statusExceptions) !== []) {
                $errors[] = $this->error('code', 'status_disallows_code', "{$stemid} status 有值時，code 不得有值。");
            }
        }

        $errors = array_merge($errors, $this->validateCode($row, $rules, $mode));

        if ($previous !== null) {
            $errors = array_merge($errors, $this->validatePom($row, $previous, $rules));
            if ($hasNumericDbh && $status === '' && ($rules['validateDbhShrink'] ?? false)) {
                $errors = array_merge($errors, $this->validateShrink($row, $previous, $rules, $confirm));
            }
        }

        if (array_key_exists('pom', $row) && ($row['pom'] === '' || $row['pom'] === null || !is_numeric($row['pom']))) {
            $errors[] = $this->error('pom', 'numeric', "{$stemid} POM 必須是數值。");
        }

        return $errors;
    }

    private function validateCode(array $row, array $rules, string $mode): array
    {
        $errors = [];
        $stemid = (string) ($row['stemid'] ?? '');
        $code = (string) ($row['code'] ?? '');
        if ($code === '') {
            return $errors;
        }

        if (($rules['disallowCodeWhitespace'] ?? false) && preg_match('/\s/u', $code)) {
            $errors[] = $this->error('code', 'whitespace', "{$stemid} code 中間不得留空格。");
        }

        $characters = preg_split('//u', $code, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!($rules['allowMultipleCodes'] ?? true) && count($characters) > 1) {
            $errors[] = $this->error('code', 'multiple_disallowed', "{$stemid} code 只能輸入一個代碼。");
        }
        $allowed = array_map('strval', $rules['allowedCodes'] ?? []);
        if ($allowed !== [] && array_diff($characters, $allowed) !== []) {
            $errors[] = $this->error('code', 'invalid_code', "{$stemid} code 含有不允許的代碼。");
        }
        if (($rules['disallowDuplicateCodes'] ?? false) && count($characters) !== count(array_unique($characters))) {
            $errors[] = $this->error('code', 'duplicate', "{$stemid} code 不得重複。");
        }
        if ($rules['sortMultipleCodes'] ?? false) {
            $sorted = $characters;
            sort($sorted, SORT_STRING);
            if ($characters !== $sorted) {
                $errors[] = $this->error('code', 'order', "{$stemid} 多個 code 必須依字母順序排列。");
            }
        }

        $rootOnlyCode = (string) ($rules['rootBranchOnlyCode'] ?? '');
        if ($rootOnlyCode !== '' && in_array($rootOnlyCode, $characters, true) && (int) ($row['branch'] ?? $row['b'] ?? 0) === 0) {
            $errors[] = $this->error('code', 'branch_only', "{$stemid} code {$rootOnlyCode} 只能記錄在分支。");
        }

        if ($mode === 'new') {
            $disallowed = array_map('strval', $rules['newRecordsDisallowCodes'] ?? []);
            if (array_intersect($characters, $disallowed) !== []) {
                $errors[] = $this->error('code', 'new_record_disallowed', "{$stemid} 新增資料不可使用 code C。");
            }
        }

        return $errors;
    }

    private function validatePom(array $row, array $previous, array $rules): array
    {
        $errors = [];
        $stemid = (string) ($row['stemid'] ?? '');
        $changeCode = (string) ($rules['changePomCode'] ?? '');
        if ($changeCode === '' || !array_key_exists('pom', $row) || !array_key_exists('pom', $previous)) {
            return $errors;
        }

        $hasChangeCode = str_contains((string) ($row['code'] ?? ''), $changeCode);
        $pomChanged = is_numeric($row['pom']) && is_numeric($previous['pom'])
            ? (float) $row['pom'] !== (float) $previous['pom']
            : (string) $row['pom'] !== (string) $previous['pom'];
        if ($hasChangeCode && !$pomChanged) {
            $errors[] = $this->error('pom', 'change_code_requires_change', "{$stemid} code 包含 C 時，POM 必須與前次不同。");
        }
        if (!$hasChangeCode && $pomChanged) {
            $errors[] = $this->error('code', 'pom_change_requires_code', "{$stemid} POM 與前次不同時，code 必須包含 C。");
        }
        if ($hasChangeCode && ($rules['changePomRequiresNote'] ?? false) && trim((string) ($row['note'] ?? '')) === '') {
            $errors[] = $this->error('note', 'change_code_requires_note', "{$stemid} code 包含 C 時，note 必須填寫說明。");
        }

        return $errors;
    }

    private function validateShrink(array $row, array $previous, array $rules, bool $confirm): array
    {
        if (!isset($previous['dbh']) || !is_numeric($previous['dbh'])) {
            return [];
        }

        $errors = [];
        $stemid = (string) ($row['stemid'] ?? '');
        $isSmaller = (float) $row['dbh'] < (float) $previous['dbh'];
        $changeCode = (string) ($rules['shrinkCanUseCode'] ?? '');
        $hasChangeCode = $changeCode !== '' && str_contains((string) ($row['code'] ?? ''), $changeCode);

        if ($isSmaller && !$confirm && !$hasChangeCode) {
            $errors[] = $this->error('confirm', 'shrink_confirmation_required', "{$stemid} DBH 小於前次調查，必須勾選縮水或使用 C。");
        }
        if (!$isSmaller && $confirm && ($rules['disallowShrinkWhenNotSmaller'] ?? false)) {
            $errors[] = $this->error('confirm', 'unexpected_shrink_confirmation', "{$stemid} DBH 未縮小，不應勾選縮水。");
        }
        if ($hasChangeCode && $confirm) {
            $errors[] = $this->error('confirm', 'change_code_disallows_confirmation', "{$stemid} 使用 C 時不應勾選縮水。");
        }

        return $errors;
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function isChecked(mixed $value): bool
    {
        return in_array($value, [1, '1', true, 'true'], true);
    }

    private function error(string $field, string $code, string $message): array
    {
        return compact('field', 'code', 'message');
    }
}
