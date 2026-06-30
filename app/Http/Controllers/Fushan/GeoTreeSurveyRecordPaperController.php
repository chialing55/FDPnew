<?php

namespace App\Http\Controllers\Fushan;

use App\Http\Controllers\Controller;
use App\Services\Fushan\GeoTreeSurveyRecordPaperService;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class GeoTreeSurveyRecordPaperController extends Controller
{
    public function __invoke(int $qx, GeoTreeSurveyRecordPaperService $service): Response
    {
        abort_unless(in_array($qx, $service->qxValues(), true), 404);

        ini_set('memory_limit', '256M');

        $pdf = Pdf::loadView(
            'pages.fushan.geo_tree_survey_record',
            $service->build($qx)
        )
            ->setPaper('A4')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->stream("geo-tree-survey-qx-{$qx}.pdf");
    }
}
