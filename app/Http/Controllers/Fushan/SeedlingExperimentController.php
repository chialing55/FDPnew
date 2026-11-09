<?php

namespace App\Http\Controllers\Fushan;

use App\Http\Controllers\Controller;
use App\Services\Fushan\SeedlingExperimentRecordPaperExporter;
use App\Services\Fushan\SeedlingExperimentSpreadsheetImporter;
use App\Services\Fushan\SeedlingExperimentSurveyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeedlingExperimentController extends Controller
{
    public function index(SeedlingExperimentSurveyService $surveyService): View
    {
        return $this->documents($surveyService);
    }

    public function documents(SeedlingExperimentSurveyService $surveyService): View
    {
        return $this->page('documents', [
            'recordPaper' => $surveyService->summary(),
        ]);
    }

    public function prepareRecordPaper(SeedlingExperimentSurveyService $surveyService): RedirectResponse
    {
        try {
            $survey = $surveyService->prepare(auth()->id());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['record_paper' => '記錄紙產生失敗：'.$exception->getMessage()]);
        }

        return redirect()
            ->route('admin.fushan.seedling-experiment.documents')
            ->with('status', sprintf(
                '已建立 %s 調查批次與固定名單，可下載記錄紙。',
                $survey->survey_period
            ));
    }

    public function downloadRecordPaper(
        int $survey,
        SeedlingExperimentRecordPaperExporter $exporter
    ): BinaryFileResponse|RedirectResponse {
        try {
            $result = $exporter->make($survey);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.fushan.seedling-experiment.documents')
                ->withErrors(['record_paper' => '記錄紙下載失敗：'.$exception->getMessage()]);
        }

        return response()
            ->download(
                $result['path'],
                $result['filename'],
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            )
            ->deleteFileAfterSend(true);
    }

    public function upload(): View
    {
        return $this->page('upload', [
            'uploadCounts' => SeedlingExperimentSpreadsheetImporter::counts(),
        ]);
    }

    public function storeUpload(
        Request $request,
        string $dataset,
        SeedlingExperimentSpreadsheetImporter $importer
    ): RedirectResponse {
        $validated = $request->validate([
            'spreadsheet' => ['required', 'file', 'mimes:xlsx,csv,ods', 'max:51200'],
        ]);

        try {
            $result = $importer->import($dataset, $validated['spreadsheet']);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['spreadsheet' => '檔案匯入失敗：'.$exception->getMessage()]);
        }

        return back()->with('status', $result);
    }

    public function entryGuide(): View
    {
        return $this->page('entry-guide');
    }

    public function downloadGreenhouseMerged(): StreamedResponse
    {
        $rows = DB::connection('fs_seedling_exp')->table('greenhouse_raw')
            ->orderByRaw("STR_TO_DATE(REPLACE(date, '-', ''), '%Y%m%d')")
            ->orderByRaw('CAST(survey_order AS UNSIGNED)')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['調查(輸入)順序', '遮蔭處理排序', 'Date', 'Sp.', 'Treatment', 'No.', '長度', '葉片數', 'Status', '附註']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->survey_order, $row->shade, $row->date, $row->species, $row->plot,
                    $row->no, $row->length, $row->leaf_count, $row->status, $row->note,
                ]);
            }
            fclose($handle);
        }, '溫室實驗完整資料_含調查順序.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function groundcoverPhase2(): View
    {
        return $this->page('groundcover-phase2');
    }

    public function greenhouse(): View
    {
        return $this->page('greenhouse');
    }

    public function data(): View
    {
        return $this->page('data');
    }

    public function download(): View
    {
        return $this->page('download');
    }

    private function page(string $page, array $data = []): View
    {
        return view('pages.fushan.seedling-experiment.workspace', array_merge([
            'page' => $page,
            'site' => 'fushan',
            'sitec' => '福山',
            'project' => '小苗實驗',
            'user' => auth()->user()?->name ?? '',
        ], $data));
    }
}
