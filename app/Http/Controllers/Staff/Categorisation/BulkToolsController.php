<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\GameTag\Repository as GameTagRepository;

class BulkToolsController extends Controller
{
    public function __construct(
        private CategoryRepository $repoCategory,
        private TagRepository $repoTag,
        private GameTagRepository $repoGameTag,
    )
    {
    }

    public function moveCategoryToCategory()
    {
        $pageTitle = 'Bulk move from category A to category B';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();

        return view('staff.categorisation.bulk-tools.move-category-to-category', $bindings);
    }

    public function moveCategoryToCategoryPreview(Request $request)
    {
        $pageTitle = 'Bulk move from category A to category B - Preview';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $categoryFrom = $request->input('category_from');
        $categoryTo = $request->input('category_to');

        $games = $this->repoCategory->gamesByCategory($categoryFrom);

        $bindings['title'] = $pageTitle;
        $bindings['affectedCount'] = $games->count();
        $bindings['sample'] = $games->take(10);
        $bindings['runRoute'] = route('staff.categorisation.bulk-tools.move-category-to-category.run');
        $bindings['hiddenInputs'] = [
            'category_from' => $categoryFrom,
            'category_to' => $categoryTo,
        ];

        return view('staff.categorisation.bulk-tools.tool-preview', $bindings);
    }

    public function moveCategoryToCategoryRun(Request $request)
    {
        $pageTitle = 'Bulk move from category A to category B - Run';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $categoryFrom = $request->input('category_from');
        $categoryTo = $request->input('category_to');

        $games = $this->repoCategory->gamesByCategory($categoryFrom);
        foreach ($games as $game) {
            $game->category_id = $categoryTo;
            $game->taxonomy_needs_review = 1;
            $game->save();
        }

        $bindings['message'] = 'Moved games in category A to category B';
        $bindings['count'] = $games->count();
        $bindings['backUrl'] = route('staff.categorisation.dashboard');

        return view('staff.categorisation.bulk-tools.tool-completed', $bindings);
    }

    public function addTagToGamesInCategory()
    {
        $pageTitle = 'Bulk add tag to games in category';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();
        $bindings['TagList'] = $this->repoTag->getAllCategorised();

        return view('staff.categorisation.bulk-tools.add-tag-to-games-in-category', $bindings);
    }

    public function addTagToGamesInCategoryPreview(Request $request)
    {
        $pageTitle = 'Bulk add tag to games in category - Preview';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $categoryId = $request->input('category_id');
        $tagId = $request->input('tag_id');

        $games = $this->repoCategory->gamesByCategory($categoryId);

        $bindings['title'] = $pageTitle;
        $bindings['affectedCount'] = $games->count();
        $bindings['sample'] = $games->take(10);
        $bindings['runRoute'] = route('staff.categorisation.bulk-tools.add-tag-to-games-in-category.run');
        $bindings['hiddenInputs'] = [
            'category_id' => $categoryId,
            'tag_id' => $tagId,
        ];

        return view('staff.categorisation.bulk-tools.tool-preview', $bindings);
    }

    public function addTagToGamesInCategoryRun(Request $request)
    {
        $pageTitle = 'Bulk add tag to games in category - Run';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $categoryId = $request->input('category_id');
        $tagId = $request->input('tag_id');

        $games = $this->repoCategory->gamesByCategory($categoryId);
        $ignoredCount = 0;
        $updatedCount = 0;
        foreach ($games as $game) {
            $gameId = $game->id;
            if ($this->repoGameTag->gameHasTag($gameId, $tagId)) {
                $ignoredCount++;
            } else {
                $this->repoGameTag->create($gameId, $tagId);
                $game->taxonomy_needs_review = 1;
                $game->save();
                $updatedCount++;
            }
        }

        $bindings['message'] = 'Added tags to games in category';
        $bindings['count'] = $updatedCount;
        $bindings['ignoredCount'] = $ignoredCount;
        $bindings['backUrl'] = route('staff.categorisation.dashboard');

        return view('staff.categorisation.bulk-tools.tool-completed', $bindings);
    }

    public function moveGamesWithTagToCategory()
    {
        $pageTitle = 'Bulk move games with tag A to category B';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['TagList'] = $this->repoTag->getAllCategorised();
        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();

        return view('staff.categorisation.bulk-tools.move-games-with-tag-to-category', $bindings);
    }

    public function moveGamesWithTagToCategoryPreview(Request $request)
    {
        $pageTitle = 'Bulk move games with tag A to category B - Preview';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $tagId = $request->input('tag_id');
        $categoryId = $request->input('category_id');

        $games = $this->repoTag->gamesByTag($tagId);

        $bindings['title'] = $pageTitle;
        $bindings['affectedCount'] = $games->count();
        $bindings['sample'] = $games->take(10);
        $bindings['runRoute'] = route('staff.categorisation.bulk-tools.move-games-with-tag-to-category.run');
        $bindings['hiddenInputs'] = [
            'tag_id' => $tagId,
            'category_id' => $categoryId,
        ];

        return view('staff.categorisation.bulk-tools.tool-preview', $bindings);
    }

    public function moveGamesWithTagToCategoryRun(Request $request)
    {
        $pageTitle = 'Bulk move games with tag A to category B - Run';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $tagId = $request->input('tag_id');
        $categoryId = $request->input('category_id');

        $games = $this->repoTag->gamesByTag($tagId);
        $ignoredCount = 0;
        $updatedCount = 0;
        foreach ($games as $game) {
            //$gameId = $game->id;
            if ($game->category_id == $categoryId) {
                $ignoredCount++;
            } else {
                $game->category_id = $categoryId;
                $game->taxonomy_needs_review = 1;
                $game->save();
                $updatedCount++;
            }
        }

        $bindings['message'] = 'Moved games with tag to category';
        $bindings['count'] = $updatedCount;
        $bindings['ignoredCount'] = $ignoredCount;
        $bindings['backUrl'] = route('staff.categorisation.dashboard');

        return view('staff.categorisation.bulk-tools.tool-completed', $bindings);
    }

    public function untagGamesWithTag()
    {
        $pageTitle = 'Bulk untag games with tag';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['TagList'] = $this->repoTag->getAllCategorised();

        return view('staff.categorisation.bulk-tools.untag-games-with-tag', $bindings);
    }

    public function untagGamesWithTagPreview(Request $request)
    {
        $pageTitle = 'Bulk untag games with tag - Preview';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $tagId = $request->input('tag_id');

        $games = $this->repoTag->gamesByTag($tagId);

        $bindings['title'] = $pageTitle;
        $bindings['affectedCount'] = $games->count();
        $bindings['sample'] = $games->take(10);
        $bindings['runRoute'] = route('staff.categorisation.bulk-tools.untag-games-with-tag.run');
        $bindings['hiddenInputs'] = [
            'tag_id' => $tagId,
        ];

        return view('staff.categorisation.bulk-tools.tool-preview', $bindings);
    }

    public function untagGamesWithTagRun(Request $request)
    {
        $pageTitle = 'Bulk untag games with tag - Run';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $tagId = $request->input('tag_id');

        $games = $this->repoTag->gamesByTag($tagId);
        foreach ($games as $game) {
            $gameId = $game->id;
            $this->repoGameTag->deleteGameTag($gameId, $tagId);
        }

        $bindings['message'] = 'Untagged games with tag';
        $bindings['count'] = $games->count();
        $bindings['backUrl'] = route('staff.categorisation.dashboard');

        return view('staff.categorisation.bulk-tools.tool-completed', $bindings);
    }
}
