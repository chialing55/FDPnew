<?php

namespace App\Services\Fushan;

use App\Models\FsGeoTreeSurvey\Record1;
use App\Models\FsGeoTreeSurvey\Record2;
use App\Models\PlantCatalog\SiteSpecies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class GeoTreeSpecialModificationService
{
    public function __construct(private readonly GeoTreeEntryRowLockResolver $rowLocks)
    {
    }

    public function save(
        string $entry,
        int $qx,
        int $qy,
        int $sqx,
        int $sqy,
        array $submitted,
        string $actor,
    ): array {
        if (!in_array($entry, ['1', '2'], true)) {
            return $this->failure('stemid', '輸入次數不正確，請重新進入輸入頁。');
        }

        $stemid = (string) ($submitted['stemid'] ?? '');
        $stored = $this->query($entry)
            ->where('stemid', $stemid)
            ->where(compact('qx', 'qy', 'sqx', 'sqy'))
            ->where('show', 1)
            ->first();
        if ($stored === null) {
            return $this->failure('stemid', '找不到目前小樣區的資料，請重新載入。');
        }
        if ($this->rowLocks->resolve([$stored->toArray()])['lockedStemids'] !== []) {
            return $this->failure('stemid', '此筆資料為 M 或 --，不可填寫特殊修改。');
        }

        $definitions = collect(config('tree-entry.surveys.fushan_geo_trees.specialModification.columns', []))
            ->keyBy('data');
        $values = [];
        foreach ($definitions as $field => $definition) {
            if ($field === 'stemid') {
                continue;
            }
            $value = $submitted[$field] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            $values[$field] = is_string($value) ? trim($value) : $value;
        }

        if ($values === []) {
            return $this->failure('stemid', '請至少填寫一個需要修改的欄位。');
        }

        $errors = $this->validateValues($values);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $effectiveTag = (string) ($values['tag'] ?? $stored->tag);
        $effectiveBranch = (string) ($values['b'] ?? $stored->branch);
        $newStemid = "{$effectiveTag}.{$effectiveBranch}";
        if ($newStemid !== $stemid && $this->query($entry)->where('stemid', $newStemid)->exists()) {
            return $this->failure('tag', "修改後的 stemid {$newStemid} 已存在，tag 與 b 不會儲存。");
        }

        $json = json_encode($values, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        DB::connection('fs_geo_tree_survey')->transaction(function () use ($entry, $stemid, $json, $actor): void {
            $this->query($entry)->where('stemid', $stemid)->update([
                'alternote' => $json,
                'updated_id' => $actor,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
        });

        return ['ok' => true, 'errors' => [], 'message' => "{$stemid} 特殊修改已儲存。"];
    }

    private function validateValues(array &$values): array
    {
        $errors = [];
        foreach (['qx' => [0, 24], 'qy' => [0, 24], 'sqx' => [1, 4], 'sqy' => [1, 4], 'b' => [0, null]] as $field => [$minimum, $maximum]) {
            if (!array_key_exists($field, $values)) {
                continue;
            }
            if (!is_numeric($values[$field]) || floor((float) $values[$field]) !== (float) $values[$field]) {
                $errors[] = $this->error($field, "{$field} 必須是整數。");
                continue;
            }
            $values[$field] = (int) $values[$field];
            if ($values[$field] < $minimum || ($maximum !== null && $values[$field] > $maximum)) {
                $range = $maximum === null ? "不得小於 {$minimum}" : "必須介於 {$minimum}–{$maximum}";
                $errors[] = $this->error($field, "{$field} {$range}。");
            }
        }

        if (array_key_exists('pom', $values)) {
            if (!is_numeric($values['pom']) || !is_finite((float) $values['pom'])) {
                $errors[] = $this->error('pom', '原 POM 必須是數值。');
            } else {
                $values['pom'] = (float) $values['pom'];
            }
        }
        if (isset($values['tag']) && mb_strlen((string) $values['tag']) > 20) {
            $errors[] = $this->error('tag', 'tag 長度不可超過 20 個字元。');
        }
        if (isset($values['csp']) && !SiteSpecies::query()->fushan()->where('csp', $values['csp'])->exists()) {
            $errors[] = $this->error('csp', 'csp 必須是 plant_catalog.site_species 的福山物種名稱。');
        }

        return $errors;
    }

    private function query(string $entry): Builder
    {
        return $entry === '1' ? Record1::query() : Record2::query();
    }

    private function failure(string $field, string $message): array
    {
        return ['ok' => false, 'errors' => [$this->error($field, $message)]];
    }

    private function error(string $field, string $message): array
    {
        return compact('field', 'message');
    }
}
