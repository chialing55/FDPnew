<?php

namespace App\Services\Fushan;

use App\Models\FsGeoTreeSurvey\Census5Part;
use App\Models\FsMortality\TreeIndividual;

class GeoTreeSurveyRecordPaperService
{
    private const SUBQUADRAT_ORDER = [
        '11', '12', '22', '21',
        '13', '14', '24', '23',
        '33', '34', '44', '43',
        '31', '32', '42', '41',
    ];

    public function qxValues(): array
    {
        return range(0, 24);
    }

    public function build(int $selectedQx): array
    {
        if (!in_array($selectedQx, $this->qxValues(), true)) {
            throw new \InvalidArgumentException("qx {$selectedQx} 不在可輸出範圍內。");
        }

        $censusRows = Census5Part::query()
            ->where('qx', $selectedQx)
            ->orderBy('qx')
            ->orderBy('qy')
            ->orderBy('sqx')
            ->orderBy('sqy')
            ->orderBy('tag')
            ->orderBy('branch')
            ->get();

        $activeMortalityStemids = TreeIndividual::query()
            ->where('is_active', 1)
            ->whereIn('stemid', $censusRows->pluck('stemid'))
            ->pluck('stemid')
            ->mapWithKeys(fn ($stemid) => [(string) $stemid => true]);

        $rows = $censusRows
            ->map(fn (Census5Part $row) => $this->formatRow(
                $row->toArray(),
                $activeMortalityStemids->has((string) $row->stemid)
            ))
            ->groupBy(fn (array $row) => "{$row['qx']}:{$row['qy']}");

        $quadrats = [];
        foreach ($rows->keys()->map(fn (string $key) => (int) explode(':', $key)[1])->sort()->values() as $qy) {
                $quadratRows = collect($rows->get("{$selectedQx}:{$qy}", []));
                $subquadrats = [];

                foreach (self::SUBQUADRAT_ORDER as $subquadrat) {
                    $sqx = (int) $subquadrat[0];
                    $sqy = (int) $subquadrat[1];
                    $subquadrats[$subquadrat] = $quadratRows
                        ->filter(fn (array $row) => (int) $row['sqx'] === $sqx && (int) $row['sqy'] === $sqy)
                        ->values()
                        ->all();
                }

                $quadrats[] = [
                    'qx' => $selectedQx,
                    'qy' => $qy,
                    'subquadrats' => $subquadrats,
                ];
        }

        return [
            'title' => '2026 福山 GEO-TREES計畫 調查紀錄紙',
            'selectedQx' => $selectedQx,
            'quadrats' => $quadrats,
            'subquadratOrder' => self::SUBQUADRAT_ORDER,
        ];
    }

    private function formatRow(array $row, bool $isActiveMortality): array
    {
        $row['is_active_mortality'] = $isActiveMortality;
        $row['survey_dbh_mark'] = $isActiveMortality
            ? 'M'
            : ((float) ($row['dbh'] ?? 0) < 9.5 ? '---' : '');

        if (($row['status'] ?? '') === '-9') {
            $row['status'] = '';
        }

        if (($row['code'] ?? '') === 'C') {
            $row['code'] = '';
        }

        if ((float) ($row['pom'] ?? 1.3) !== 1.3) {
            $row['note'] = '[POM = ' . $row['pom'] . '] ' . ($row['note'] ?? '');
        }

        return $row;
    }
}
