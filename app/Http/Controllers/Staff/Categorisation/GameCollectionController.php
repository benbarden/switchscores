<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Traits\AuthUser;
use App\Traits\SwitchServices;
use App\Traits\StaffView;

use App\Domain\GameCollection\Repository as GameCollectionRepository;

class GameCollectionController extends Controller
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

    protected $repoGameCollection;

    public function __construct(
        GameCollectionRepository $repoGameCollection
    )
    {
        $this->repoGameCollection = $repoGameCollection;
    }

    public function showList()
    {
        $bindings = $this->getBindingsCategorisationSubpage('Collections');

        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        return view('staff.categorisation.game-collection.list', $bindings);
    }

    public function addCollection()
    {
        $bindings = $this->getBindingsCategorisationCategoriesSubpage('Add collection');

        $request = request();

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.game-collection.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $existingRecord = $this->repoGameCollection->getByName($request->name);

            $validator->after(function ($validator) use ($existingRecord) {
                // Check for duplicates
                if ($existingRecord != null) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.game-collection.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // All ok
            $this->repoGameCollection->create($request->name, $request->link_title);

            return redirect(route('staff.categorisation.game-collection.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FormMode'] = 'add';

        return view('staff.categorisation.game-collection.add', $bindings);
    }

    public function editCollection($collectionId)
    {
        $bindings = $this->getBindingsCategorisationCategoriesSubpage('Edit collection');

        $collectionData = $this->repoGameCollection->find($collectionId);
        if (!$collectionData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->repoGameCollection->edit($collectionData, $request->name, $request->link_title);

            return redirect(route('staff.categorisation.game-collection.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['CollectionData'] = $collectionData;
        $bindings['CollectionId'] = $collectionId;

        return view('staff.categorisation.game-collection.edit', $bindings);
    }

    public function deleteCollection($collectionId)
    {
        $bindings = $this->getBindingsCategorisationCategoriesSubpage('Delete collection');

        $collectionData = $this->repoGameCollection->find($collectionId);
        if (!$collectionData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->repoGameCollection->delete($collectionId);

            // Done

            return redirect(route('staff.categorisation.game-collection.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['CollectionData'] = $collectionData;
        $bindings['CollectionId'] = $collectionId;

        return view('staff.categorisation.game-collection.delete', $bindings);
    }
}