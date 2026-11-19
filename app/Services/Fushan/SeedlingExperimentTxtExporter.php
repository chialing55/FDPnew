<?php

namespace App\Services\Fushan;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SeedlingExperimentTxtExporter
{
    private const CONNECTION = 'fs_seedling_exp';

    private const EXPERIMENTS = [
        'groundcover_phase1' => '地被實驗第一期',
        'groundcover_phase2' => '地被實驗第二期',
        'greenhouse' => '溫室實驗',
    ];

    public function download(string $experimentCode): StreamedResponse
    {
        if (! isset(self::EXPERIMENTS[$experimentCode])) {
            throw ValidationException::withMessages(['download' => '未知的實驗類型。']);
        }

        $headers = $this->headers($experimentCode);
        $filename = self::EXPERIMENTS[$experimentCode].'_'.now()->format('Ymd').'.txt';

        return response()->streamDownload(function () use ($experimentCode, $headers): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, "\t");

            $this->query($experimentCode)->chunk(1000, function ($rows) use ($handle, $experimentCode): void {
                foreach ($rows as $row) {
                    fputcsv($handle, $this->values($row, $experimentCode), "\t");
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function query(string $experimentCode): Builder
    {
        return DB::connection(self::CONNECTION)
            ->table('records as r')
            ->join('individuals as i', 'i.id', '=', 'r.individual_id')
            ->join('plots as p', 'p.id', '=', 'i.plot_id')
            ->join('experiments as e', 'e.id', '=', 'p.experiment_id')
            ->where('e.code', $experimentCode)
            ->whereNotExists(function (Builder $terminal): void {
                $terminal->selectRaw('1')
                    ->from('records as terminal')
                    ->whereColumn('terminal.individual_id', 'r.individual_id')
                    ->whereIn('terminal.status', ['D', 'G'])
                    ->where(function (Builder $earlier): void {
                        $earlier->whereColumn('terminal.survey_date', '<', 'r.survey_date')
                            ->orWhere(function (Builder $sameDate): void {
                                $sameDate->whereColumn('terminal.survey_date', 'r.survey_date')
                                    ->whereColumn('terminal.id', '<', 'r.id');
                            });
                    });
            })
            ->select([
                'r.id', 'r.survey_date', 'r.status', 'r.length', 'r.cotyledon_count',
                'r.leaf_count', 'r.note', 'p.plot_code', 'p.density',
                'i.individual_no', 'i.species_name', 'i.survey_order',
            ])
            ->orderBy('r.survey_date')
            ->orderBy('i.survey_order')
            ->orderBy('p.plot_code')
            ->orderBy('i.individual_no')
            ->orderBy('r.id');
    }

    private function headers(string $experimentCode): array
    {
        return match ($experimentCode) {
            'groundcover_phase1' => ['Date', 'Plot', 'No.', 'Species', '長度', '葉片數', 'Status', '備註'],
            'groundcover_phase2' => ['調查順序排序', 'Date', 'Plot', 'Density', 'No.', 'Species', '長度', '子葉數', '葉片數', 'Status', '備註'],
            'greenhouse' => ['調查(輸入)順序', '遮蔭處理排序', 'Date', 'Sp.', 'Treatment', 'No.', '長度', '葉片數', 'Status', '備註'],
        };
    }

    private function values(object $row, string $experimentCode): array
    {
        $common = [
            $row->survey_date,
            $row->plot_code,
            $row->individual_no,
            $row->species_name,
            $this->formatNumber($row->length),
        ];

        return match ($experimentCode) {
            'groundcover_phase1' => [...$common, $this->formatNumber($row->leaf_count), $row->status, $row->note],
            'groundcover_phase2' => [
                $row->survey_order, $row->survey_date, $row->plot_code, $this->formatNumber($row->density),
                $row->individual_no, $row->species_name, $this->formatNumber($row->length),
                $this->formatNumber($row->cotyledon_count), $this->formatNumber($row->leaf_count), $row->status, $row->note,
            ],
            'greenhouse' => [
                $row->survey_order, substr((string) $row->plot_code, -1), $row->survey_date,
                $row->species_name, $row->plot_code, $row->individual_no,
                $this->formatNumber($row->length), $this->formatNumber($row->leaf_count), $row->status, $row->note,
            ],
        };
    }

    private function formatNumber(mixed $value): int|float|string|null
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $number = (float) $value;
        if (in_array($number, [-1.0, -4.0], true)) {
            return 'NA';
        }

        return floor($number) === $number ? (int) $number : $number;
    }
}
