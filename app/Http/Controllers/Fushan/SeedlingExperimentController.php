<?php

namespace App\Http\Controllers\Fushan;

use App\Http\Controllers\Controller;
use App\Services\Fushan\SeedlingExperimentRecordPaperExporter;
use App\Services\Fushan\SeedlingExperimentSpreadsheetImporter;
use App\Services\Fushan\SeedlingExperimentSurveyService;
use App\Services\Fushan\SeedlingExperimentTxtExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'greenhouseRecordPaper' => $surveyService->summary('greenhouse'),
        ]);
    }

    public function prepareGreenhouseRecordPaper(SeedlingExperimentSurveyService $surveyService): RedirectResponse
    {
        try {
            $survey = $surveyService->prepare(auth()->id(), 'greenhouse');
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['greenhouse_record_paper' => '記錄紙產生失敗：'.$exception->getMessage()]);
        }

        return redirect()
            ->route('admin.fushan.seedling-experiment.documents')
            ->with('greenhouse_status', sprintf(
                '已建立 %s 溫室實驗調查批次與固定名單，可下載記錄紙。',
                $survey->survey_period
            ));
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

    public function downloadGreenhouseRecordPaper(
        int $survey,
        SeedlingExperimentRecordPaperExporter $exporter
    ): BinaryFileResponse|RedirectResponse {
        try {
            $result = $exporter->make($survey, 'greenhouse');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.fushan.seedling-experiment.documents')
                ->withErrors(['greenhouse_record_paper' => '記錄紙下載失敗：'.$exception->getMessage()]);
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

    public function downloadExperimentTxt(
        string $experiment,
        SeedlingExperimentTxtExporter $exporter
    ): StreamedResponse {
        return $exporter->download($experiment);
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
