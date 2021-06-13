<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Support\Facades\Validator;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\StaffView;

use App\Domain\GameSeries\Repository as GameSeriesRepository;

class GameSeriesController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    protected $viewBreadcrumbs;
    protected $repoGameSeries;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        GameSeriesRepository $repoGameSeries
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoGameSeries = $repoGameSeries;
    }

    public function showList()
    {
        $bindings = $this->getBindings('Game series');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage('Game series');

        $bindings['GameSeriesList'] = $this->repoGameSeries->getAll();

        return view('staff.categorisation.game-series.list', $bindings);
    }

    public function addSeries()
    {
        $bindings = $this->getBindings('Add series');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSeriesSubpage('Add series');

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
        $bindings = $this->getBindings('Edit series');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSeriesSubpage('Edit series');

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
        $bindings = $this->getBindings('Delete series');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSeriesSubpage('Delete series');

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