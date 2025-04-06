<?php


namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;

use App\Models\DataSource;

use App\Traits\SwitchServices;

class DataSourceIgnoreController extends Controller
{
    use SwitchServices;

    public function __construct(
        private DataSourceRepository $repoDataSource,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed
    ){
    }

    public function addToIgnoreList()
    {
        $request = request();

        $sourceId = $request->sourceId;
        $dsParsedItemId = $request->dsParsedItemId;

        if (!$sourceId) {
            return response()->json(['error' => 'Missing data: sourceId'], 400);
        }

        if (!$dsParsedItemId) {
            return response()->json(['error' => 'Missing data: dsParsedItemId'], 400);
        }

        $dsParsedItem = $this->repoDataSourceParsed->find($dsParsedItemId);
        if (!$dsParsedItem) {
            return response()->json(['error' => 'Cannot find dsParsedItem for id: '.$dsParsedItemId], 400);
        }

        switch ($sourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $linkId = $dsParsedItem->link_id;

                $dsIgnoredItem = $this->repoDataSourceIgnore->getBySourceAndLinkId($sourceId, $linkId);
                if ($dsIgnoredItem->count() > 0) {
                    return response()->json(['error' => 'Item is already marked as ignored [Source: '.$sourceId.'; Link: '.$linkId.']'], 400);
                }

                $this->repoDataSourceIgnore->addLinkId($sourceId, $linkId);

                $data = array(
                    'status' => 'OK'
                );
                return response()->json($data, 200);

                break;

            default:
                return response()->json(['error' => 'NOT YET SUPPORTED'], 400);
                break;

        }

    }

    public function removeFromIgnoreList()
    {
        $request = request();

        $sourceId = $request->sourceId;
        $dsParsedItemId = $request->dsParsedItemId;

        if (!$sourceId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }
        if (!$dsParsedItemId) {
            return response()->json(['error' => 'Missing data: dsParsedItemId'], 400);
        }

        $dsParsedItem = $this->repoDataSourceParsed->find($dsParsedItemId);
        if (!$dsParsedItem) {
            return response()->json(['error' => 'Cannot find dsParsedItem for id: '.$dsParsedItemId], 400);
        }

        switch ($sourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $linkId = $dsParsedItem->link_id;

                $dsIgnoredItem = $this->repoDataSourceIgnore->getBySourceAndLinkId($sourceId, $linkId);
                if (!$dsIgnoredItem) {
                    return response()->json(['error' => 'Item is not marked as ignored'], 400);
                }

                $this->repoDataSourceIgnore->deleteByLinkId($sourceId, $linkId);

                $data = array(
                    'status' => 'OK'
                );
                return response()->json($data, 200);

                break;

            default:
                return response()->json(['error' => 'NOT YET SUPPORTED'], 400);
                break;

        }

    }
}