<?php

namespace App\Http\Controllers\Nanjenshan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeedlingController extends Controller
{
    public function doc(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/nanjenshan/seedling_doc', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->name,
        ]);
    }

    public function dataviewer(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/nanjenshan/seedling_dataviewer', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->name,
        ]);
    }

    public function download(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/nanjenshan/seedling_download', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->name,
        ]);
    }

    public function downloadAllData(): StreamedResponse
    {
        $filename = 'njs_seedling_all_data.txt';
        $headers = [
            'id',
            'plot_name',
            'quadrat',
            'census',
            'ym',
            'survey_day',
            'tag',
            'csp',
            'spcode',
            'status',
            'height',
            'cotyledon',
            'leaf',
            'leaf_eaten_percent',
            'leaf_covered_percent',
            'disease_spot_percent',
            'death_cause',
            'location_n',
            'remark',
        ];

        return $this->streamTxt($filename, $headers, function ($handle) use ($headers) {
            DB::connection('njs_seedling')
                ->table('seedling_records')
                ->join('seedling_individuals', 'seedling_records.tag', '=', 'seedling_individuals.tag')
                ->join('quadrats', 'seedling_individuals.quadrat', '=', 'quadrats.quadrat')
                ->join('censuses', 'seedling_records.census', '=', 'censuses.census')
                ->leftJoin('spinfo', 'seedling_individuals.standard_species_name', '=', 'spinfo.csp')
                ->select([
                    'seedling_records.id',
                    'quadrats.plot_name',
                    'quadrats.quadrat',
                    'censuses.census',
                    'censuses.ym',
                    'seedling_records.survey_date',
                    'seedling_individuals.tag',
                    'seedling_individuals.recorded_species_name',
                    'seedling_individuals.standard_species_name',
                    'spinfo.spcode',
                    'seedling_records.status',
                    'seedling_records.height',
                    'seedling_records.cotyledon',
                    'seedling_records.leaf',
                    'seedling_records.leaf_eaten_percent',
                    'seedling_records.leaf_covered_percent',
                    'seedling_records.disease_spot_percent',
                    'seedling_records.death_cause',
                    'seedling_records.location_n',
                    'seedling_records.remark',
                ])
                ->orderBy('quadrats.plot_name')
                ->orderBy('quadrats.quadrat')
                ->orderBy('censuses.census')
                ->orderBy('seedling_individuals.tag')
                ->chunk(1000, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            $row->plot_name,
                            $row->quadrat,
                            $row->census,
                            $row->ym,
                            $row->survey_date,
                            $row->tag,
                            $this->formatSpecies($row->standard_species_name, $row->recorded_species_name),
                            $row->spcode,
                            $row->status,
                            $this->formatNumber($row->height),
                            $row->cotyledon,
                            $row->leaf,
                            $this->formatNumber($row->leaf_eaten_percent),
                            $this->formatNumber($row->leaf_covered_percent),
                            $this->formatNumber($row->disease_spot_percent),
                            $row->death_cause,
                            $row->location_n,
                            $row->remark,
                        ], "\t");
                    }
                });
        });
    }

    public function downloadQuadrats(): StreamedResponse
    {
        $filename = 'njs_seedling_quadrats.txt';
        $headers = ['id', 'plot_name', 'quadrat', 'coord_x', 'coord_y', 'direction'];

        return $this->streamTxt($filename, $headers, function ($handle) use ($headers) {
            DB::connection('njs_seedling')
                ->table('quadrats')
                ->select($headers)
                ->orderBy('plot_name')
                ->orderBy('quadrat')
                ->chunk(1000, function ($rows) use ($handle, $headers) {
                    foreach ($rows as $row) {
                        fputcsv($handle, array_map(fn ($column) => $row->{$column}, $headers), "\t");
                    }
                });
        });
    }

    private function streamTxt(string $filename, array $headers, callable $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writeRows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, "\t");
            $writeRows($handle);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function formatSpecies(?string $standard, ?string $recorded): string
    {
        $standard = trim((string) $standard);
        $recorded = trim((string) $recorded);

        if ($standard !== '' && $recorded !== '' && $standard !== $recorded) {
            return "{$standard} ({$recorded})";
        }

        return $standard !== '' ? $standard : $recorded;
    }

    private function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return rtrim(rtrim((string) $value, '0'), '.');
    }
}
