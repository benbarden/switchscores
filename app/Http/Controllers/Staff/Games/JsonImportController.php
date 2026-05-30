<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use App\Domain\GameImport\JsonImportService;
use App\Domain\GameImport\ImportResult;
use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

class JsonImportController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private JsonImportService $importService,
    ) {
    }

    /**
     * Show the upload form.
     */
    public function showUploadForm()
    {
        $pageTitle = 'Import games from JSON';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        return view('staff.games.json-import.upload', $bindings);
    }

    /**
     * Process the uploaded JSON and show preview.
     */
    public function preview(Request $request)
    {
        $pageTitle = 'Import games - Preview';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        // Validate file upload
        $request->validate([
            'json_file' => 'required|file|mimes:json,txt|max:5120', // 5MB max
        ]);

        $file = $request->file('json_file');
        $jsonContent = file_get_contents($file->getRealPath());

        // Parse and validate
        $result = $this->importService->parseAndValidate($jsonContent);

        // Store in session for confirm step
        $request->session()->put('import_result', $result->toArray());

        $bindings['ImportResult'] = $result;
        $bindings['HasValidGames'] = $result->hasValidGames();
        $bindings['HasErrors'] = $result->hasErrors();
        $bindings['HasNewPublishers'] = $result->hasNewPublishers();

        return view('staff.games.json-import.preview', $bindings);
    }

    /**
     * Confirm and execute the import.
     */
    public function confirm(Request $request)
    {
        $pageTitle = 'Import games - Complete';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        // Retrieve from session
        $resultData = $request->session()->get('import_result');
        if (!$resultData) {
            return redirect()->route('staff.games.json-import.upload')
                ->withErrors(['error' => 'No import data found. Please upload a file again.']);
        }

        $result = ImportResult::fromArray($resultData);

        if (!$result->hasValidGames()) {
            return redirect()->route('staff.games.json-import.upload')
                ->withErrors(['error' => 'No valid games to import.']);
        }

        // Execute the import
        $createdGames = $this->importService->executeImport($result);

        // Clear session
        $request->session()->forget('import_result');

        $bindings['CreatedGames'] = $createdGames;
        $bindings['NewPublishers'] = $result->newPublishers;
        $bindings['BatchDate'] = $result->batchDate;

        return view('staff.games.json-import.complete', $bindings);
    }
}
