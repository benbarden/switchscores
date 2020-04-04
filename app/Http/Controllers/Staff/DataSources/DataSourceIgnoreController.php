<?php


namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DataSourceIgnoreController extends Controller
{
    use SwitchServices;

    public function addToIgnoreList()
    {
        $request = request();

        $sourceId = $request->sourceId;
        $dsParsedItemId = $request->dsParsedItemId;
        $title = $request->title;

        if (!$sourceId) {
            return response()->json(['error' => 'Missing data: sourceId'], 400);
        }

        if (!$dsParsedItemId && (!$title)) {
            return response()->json(['error' => 'dsParsedItemId and title cannot both be blank; pick one'], 400);
        }

        if ($dsParsedItemId) {

            $dsParsedItem = $this->getServiceDataSourceParsed()->find($dsParsedItemId);
            if (!$dsParsedItem) {
                return response()->json(['error' => 'Cannot find dsParsedItem for id: '.$dsParsedItemId], 400);
            }

            $linkId = $dsParsedItem->link_id;

            $dsIgnoredItem = $this->getServiceDataSourceIgnore()->getBySourceAndLinkId($sourceId, $linkId);
            if ($dsIgnoredItem->count() > 0) {
                return response()->json(['error' => 'Item is already marked as ignored [Source: '.$sourceId.'; Link: '.$linkId.']'], 400);
            }

            $this->getServiceDataSourceIgnore()->add($sourceId, $linkId);

            $data = array(
                'status' => 'OK'
            );
            return response()->json($data, 200);

        } elseif ($title) {

            return response()->json(['error' => 'NOT YET SUPPORTED'], 400);

        }
    }

    public function removeFromIgnoreList()
    {
        $request = request();

        $sourceId = $request->sourceId;
        $dsParsedItemId = $request->dsParsedItemId;
        $title = $request->title;

        if (!$sourceId) {
            return response()->json(['error' => 'Missing data: sourceId'], 400);
        }

        if (!$dsParsedItemId && (!$title)) {
            return response()->json(['error' => 'dsParsedItemId and title cannot both be blank; pick one'], 400);
        }

        if ($dsParsedItemId) {

            $dsParsedItem = $this->getServiceDataSourceParsed()->find($dsParsedItemId);
            if (!$dsParsedItem) {
                return response()->json(['error' => 'Cannot find dsParsedItem for id: '.$dsParsedItemId], 400);
            }

            $linkId = $dsParsedItem->link_id;

            $dsIgnoredItem = $this->getServiceDataSourceIgnore()->getBySourceAndLinkId($sourceId, $linkId);
            if (!$dsIgnoredItem) {
                return response()->json(['error' => 'Item is not marked as ignored'], 400);
            }

            $this->getServiceDataSourceIgnore()->delete($sourceId, $linkId);

            $data = array(
                'status' => 'OK'
            );
            return response()->json($data, 200);

        } elseif ($title) {

            return response()->json(['error' => 'NOT YET SUPPORTED'], 400);

        }
    }
}