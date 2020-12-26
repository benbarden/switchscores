<?php


namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\DataSource;

class DataSourceIgnoreController extends Controller
{
    use SwitchServices;

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

        $dsParsedItem = $this->getServiceDataSourceParsed()->find($dsParsedItemId);
        if (!$dsParsedItem) {
            return response()->json(['error' => 'Cannot find dsParsedItem for id: '.$dsParsedItemId], 400);
        }

        switch ($sourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $linkId = $dsParsedItem->link_id;

                $dsIgnoredItem = $this->getServiceDataSourceIgnore()->getBySourceAndLinkId($sourceId, $linkId);
                if ($dsIgnoredItem->count() > 0) {
                    return response()->json(['error' => 'Item is already marked as ignored [Source: '.$sourceId.'; Link: '.$linkId.']'], 400);
                }

                $this->getServiceDataSourceIgnore()->addLinkId($sourceId, $linkId);

                $data = array(
                    'status' => 'OK'
                );
                return response()->json($data, 200);

                break;

            case DataSource::DSID_WIKIPEDIA:

                $title = $dsParsedItem->title;

                $dsIgnoredItem = $this->getServiceDataSourceIgnore()->getBySourceAndTitle($sourceId, $title);
                if ($dsIgnoredItem->count() > 0) {
                    return response()->json(['error' => 'Item is already marked as ignored [Source: '.$sourceId.'; Title: '.$title.']'], 400);
                }

                $this->getServiceDataSourceIgnore()->addTitle($sourceId, $title);

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

        $dsParsedItem = $this->getServiceDataSourceParsed()->find($dsParsedItemId);
        if (!$dsParsedItem) {
            return response()->json(['error' => 'Cannot find dsParsedItem for id: '.$dsParsedItemId], 400);
        }

        switch ($sourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $linkId = $dsParsedItem->link_id;

                $dsIgnoredItem = $this->getServiceDataSourceIgnore()->getBySourceAndLinkId($sourceId, $linkId);
                if (!$dsIgnoredItem) {
                    return response()->json(['error' => 'Item is not marked as ignored'], 400);
                }

                $this->getServiceDataSourceIgnore()->deleteByLinkId($sourceId, $linkId);

                $data = array(
                    'status' => 'OK'
                );
                return response()->json($data, 200);

                break;

            case DataSource::DSID_WIKIPEDIA:

                $title = $dsParsedItem->title;

                $dsIgnoredItem = $this->getServiceDataSourceIgnore()->getBySourceAndTitle($sourceId, $title);
                if (!$dsIgnoredItem) {
                    return response()->json(['error' => 'Item is not marked as ignored'], 400);
                }

                $this->getServiceDataSourceIgnore()->deleteByTitle($sourceId, $title);

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