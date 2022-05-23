<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Support\Facades\Validator;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

use App\Domain\GameSeries\Repository as GameSeriesRepository;

class GameSeriesController extends Controller
{
    use SwitchServices;
    use AuthUser;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    protected $repoGameSeries;

    public function __construct(
        GameSeriesRepository $repoGameSeries
    )
    {
        $this->repoGameSeries = $repoGameSeries;
    }

    public function showList()
    {
        $pageTitle = 'Game series';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameSeriesList'] = $this->repoGameSeries->getAll();

        return view('staff.categorisation.game-series.list', $bindings);
    }

    public function addSeries()
    {
        $pageTitle = 'Add series';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSeriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.game-series.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $existingRecord = $this->repoGameSeries->getByName($request->name);

            $validator->after(function ($validator) use ($existingRecord) {
                // Check for duplicates
                if ($existingRecord != null) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.game-series.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // All ok
            $this->repoGameSeries->create($request->name, $request->link_title);

            return redirect(route('staff.categorisation.game-series.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FormMode'] = 'add';

        return view('staff.categorisation.game-series.add', $bindings);
    }

    public function editSeries($seriesId)
    {
        $pageTitle = 'Edit series';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSeriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $seriesData = $this->repoGameSeries->find($seriesId);
        if (!$seriesData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->repoGameSeries->edit($seriesData, $request->name, $request->link_title);

            return redirect(route('staff.categorisation.game-series.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['SeriesData'] = $seriesData;
        $bindings['SeriesId'] = $seriesId;

        return view('staff.categorisation.game-series.edit', $bindings);
    }

    public function deleteSeries($seriesId)
    {
        $pageTitle = 'Delete series';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSeriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $seriesData = $this->repoGameSeries->find($seriesId);
        if (!$seriesData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            // Delete the image, if it exists
            if ($seriesData->landing_image) {
                $landingImagePath = public_path('img/gen/series/'.$seriesData->landing_image);
                if (file_exists($landingImagePath)) {
                    unlink($landingImagePath);
                }
            }

            // Delete the record
            $this->repoGameSeries->delete($seriesId);

            // Done

            return redirect(route('staff.categorisation.game-series.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['SeriesData'] = $seriesData;
        $bindings['SeriesId'] = $seriesId;

        return view('staff.categorisation.game-series.delete', $bindings);
    }
}