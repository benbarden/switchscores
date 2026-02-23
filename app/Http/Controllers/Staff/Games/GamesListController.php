<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\GameCollection\Repository as CollectionRepository;
use App\Domain\Game\Repository\GameAffiliateRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;
use App\Domain\Game\Repository\GameCrawlRepository;

use App\Models\Category;
use App\Models\Game;
use App\Models\GameSeries;
use App\Models\Tag;
use App\Models\GameCollection;
use App\Models\GamesCompany;

use App\Enums\GameListType;

class GamesListController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameListsRepository $repoGameLists,
        private CategoryRepository $repoCategory,
        private GameSeriesRepository $repoGameSeries,
        private TagRepository $repoTag,
        private CollectionRepository $repoCollection,
        private GameAffiliateRepository $repoGameAffiliate,
        private GamesCompanyRepository $repoGamesCompany,
        private TagCategoryRepository $repoTagCategory,
        private GameCrawlRepository $repoGameCrawl,
    )
    {
    }

    private function listConfig(): array
    {
        return [
            'recently-added' => [
                'title' => 'Recently added',
                'fetch' => fn() => $this->repoGameLists->recentlyAdded(100)
            ],
            'recently-released' => [
                'title' => 'Recently released',
                'sort' => "[ 6, 'desc'], [ 1, 'asc']",
                'fetch' => fn() => $this->repoGameLists->recentlyReleasedAll(1, 100)
            ],
            'upcoming-games' => [
                'title' => 'Upcoming and unreleased',
                'sort' => "[ 6, 'desc'], [ 1, 'asc']",
                'fetch' => fn() => $this->repoGameLists->upcomingAll()
            ],
            'no-category-excluding-low-quality' => [
                'title' => 'No category (Excluding low quality)',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noCategoryExcludingLowQuality(),
            ],
            'no-category-all' => [
                'title' => 'No category (All)',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noCategoryAll(),
            ],
            'no-category-with-collection' => [
                'title' => 'No category with collection',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noCategoryWithCollection(),
            ],
            'no-category-with-reviews' => [
                'title' => 'No category with reviews',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noCategoryWithReviews(),
            ],
            'no-eu-release-date' => [
                'title' => 'No EU release date',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noEuReleaseDate(),
            ],
            'no-eshop-price' => [
                'title' => 'No eShop price',
                'sort'  => "[6, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noPrice(),
            ],
            'no-video-type' => [
                'title' => 'No video type',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noVideoType(),
                'limit' => 200,
            ],
            'no-nintendo-co-uk-link' => [
                'title' => 'No Nintendo.co.uk link, and no override URL',
                'sort'  => "[6, 'desc']",
                'fetch' => fn() => $this->repoGameLists->noNintendoCoUkLink(),
            ],
            'broken-nintendo-co-uk-link' => [
                'title' => 'Broken Nintendo.co.uk link',
                'sort'  => "[4, 'desc']",
                'fetch' => fn() => $this->repoGameLists->brokenNintendoCoUkLink(),
            ],
            'upcoming-eshop-crosscheck' => [
                'title' => 'Upcoming (eShop crosscheck)',
                'sort'  => "[ 6, 'asc'], [ 1, 'asc']",
                'fetch' => fn() => $this->repoGameLists->upcomingEshopCrosscheckNoDate(),
                'view'  => 'staff.games.list.upcoming-eshop-crosscheck', // custom view path
            ],
            'format-option' => [
                'title' => null,
                'fetch' => function ($format, $value = null) {
                    return $this->repoGameLists->formatOption($format, $value);
                },
                'dynamicTitle' => true,
            ],
            'by-category' => [
                'title' => null,
                'sort'  => "[1, 'asc']",
                'fetch' => function ($category) {
                    if (!$category instanceof Category) {
                        $category = $this->repoCategory->find($category);
                    }
                    return $this->repoCategory->gamesByCategory($category->id);
                },
                'dynamicTitle' => true,
            ],
            'by-series' => [
                'title' => null,
                'sort'  => "[1, 'asc']",
                'fetch' => function ($gameSeries) {
                    if (!$gameSeries instanceof GameSeries) {
                        $gameSeries = $this->repoGameSeries->find($gameSeries);
                    }
                    return $this->repoGameSeries->gamesBySeries(null, $gameSeries->id);
                },
                'dynamicTitle' => true,
            ],
            'by-tag' => [
                'title' => null,
                'sort'  => "[1, 'asc']",
                'fetch' => function ($tag) {
                    if (!$tag instanceof Tag) {
                        $tag = $this->repoTag->find($tag);
                    }
                    return $this->repoTag->gamesByTag($tag->id);
                },
                'dynamicTitle' => true,
            ],
            'by-collection' => [
                'title' => null,
                'sort'  => "[1, 'asc']",
                'fetch' => function ($collection) {
                    if (!$collection instanceof GameCollection) {
                        $collection = $this->repoCollection->find($collection);
                    }
                    if (!$collection) abort(404);
                    return $this->repoCollection->gamesByCollection($collection->id);
                },
                'dynamicTitle' => true,
            ],
            'by-company' => [
                'title' => null,
                'sort'  => "[1, 'asc']",
                'fetch' => function ($companyId) {
                    $company = $this->repoGamesCompany->find($companyId);
                    if (!$company) abort(404);
                    return Game::where(function ($query) use ($companyId) {
                        $query->whereHas('gameDevelopers', function ($q) use ($companyId) {
                            $q->where('developer_id', $companyId);
                        })->orWhereHas('gamePublishers', function ($q) use ($companyId) {
                            $q->where('publisher_id', $companyId);
                        });
                    })->orderBy('title', 'asc')->get();
                },
                'dynamicTitle' => true,
            ],
            'no-tag-category' => [
                'title' => null,
                'sort'  => "[1, 'asc']",
                'fetch' => function ($tagCategoryId) {
                    $tagCategory = $this->repoTagCategory->find($tagCategoryId);
                    if (!$tagCategory) abort(404);
                    // Get games that don't have any tag from this tag category
                    // Excludes low quality and de-listed games
                    return Game::whereDoesntHave('gameTags', function ($query) use ($tagCategoryId) {
                        $query->whereHas('tag', function ($q) use ($tagCategoryId) {
                            $q->where('tag_category_id', $tagCategoryId);
                        });
                    })
                    ->where('is_low_quality', 0)
                    ->active()
                    ->orderBy('title', 'asc')
                    ->limit(100)
                    ->get();
                },
                'dynamicTitle' => true,
                'limit' => 100,
            ],
            // Affiliates
            'amazon-us-unchecked' => [
                'title' => 'Amazon US: Unchecked',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getUnchecked('us'),
                'limit' => 1000,
            ],
            'amazon-us-linked' => [
                'title' => 'Amazon US: Linked',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getLinked('us'),
                'limit' => 1000,
            ],
            'amazon-us-noproduct' => [
                'title' => 'Amazon US: No product',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getNoProduct('us'),
                'limit' => 1000,
            ],
            'amazon-us-ignored' => [
                'title' => 'Amazon US: Ignored',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getIgnored('us'),
                'limit' => 1000,
            ],
            'amazon-uk-unchecked' => [
                'title' => 'Amazon UK: Unchecked',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getUnchecked('uk'),
                'limit' => 1000,
            ],
            'amazon-uk-linked' => [
                'title' => 'Amazon UK: Linked',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getLinked('uk'),
                'limit' => 1000,
            ],
            'amazon-uk-noproduct' => [
                'title' => 'Amazon UK: No product',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getNoProduct('uk'),
                'limit' => 1000,
            ],
            'amazon-uk-ignored' => [
                'title' => 'Amazon UK: Ignored',
                //'sort'  => "[6, 'asc']",
                'fetch' => fn() => $this->repoGameAffiliate->getIgnored('uk'),
                'limit' => 1000,
            ],
            // Crawl status
            'crawl-status-404' => [
                'title' => 'Crawl status: 404 Not Found',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameCrawl->getStatus404(),
            ],
            'crawl-status-410' => [
                'title' => 'Crawl status: 410 Gone',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameCrawl->getStatus410(),
            ],
            'crawl-status-redirect' => [
                'title' => 'Crawl status: 3xx Redirect',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameCrawl->getStatusRedirect(),
            ],
            'crawl-status-error' => [
                'title' => 'Crawl status: 5xx Server Error',
                'sort'  => "[0, 'desc']",
                'fetch' => fn() => $this->repoGameCrawl->getStatusServerError(),
            ],

        ];
    }

    private function getDynamicTitle(string $listType, array $args): string
    {
        switch ($listType) {
            case 'format-option':
                [$format, $value] = array_pad($args, 2, null);
                $valueDesc = $value ?? '(Not set)';
                return 'By format option: ' . $format . ' - ' . $valueDesc;

            case 'by-category':
                [$category] = $args + [null];
                if (!$category instanceof Category) {
                    $category = $this->repoCategory->find($category);
                }
                if (!$category) abort(404);
                return 'By category: ' . $category->name;

            case 'by-series':
                [$series] = $args + [null];
                if (!$series instanceof GameSeries) {
                    $series = $this->repoGameSeries->find($series);
                }
                if (!$series) abort(404);
                return 'By series: ' . $series->series;

            case 'by-tag':
                [$tag] = $args + [null];
                if (!$tag instanceof Tag) {
                    $tag = $this->repoTag->find($tag);
                }
                if (!$tag) abort(404);
                return 'By tag: ' . $tag->tag_name;

            case 'by-collection':
                [$collectionId] = array_pad($args, 1, null);
                $collectionName = $this->repoCollection->find($collectionId)?->name ?? '(Unknown collection)';
                return 'By collection: ' . $collectionName;

            case 'by-company':
                [$companyId] = array_pad($args, 1, null);
                $companyName = $this->repoGamesCompany->find($companyId)?->name ?? '(Unknown company)';
                return 'By company: ' . $companyName;

            case 'no-tag-category':
                [$tagCategoryId] = array_pad($args, 1, null);
                $tagCategoryName = $this->repoTagCategory->find($tagCategoryId)?->name ?? '(Unknown tag category)';
                return 'No tag from: ' . $tagCategoryName;

            default:
                return '(Untitled list)';
        }
    }

    public function showList(
        Request $request, $listType, ...$args,
    )
    {
        $config = $this->listConfig()[$listType] ?? abort(404);

        if (!empty($config['dynamicTitle'])) {
            $pageTitle = $this->getDynamicTitle($listType, $args);
        } else {
            $pageTitle = $config['title'];
        }

        $sort = $config['sort'] ?? null;

        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle), jsInitialSort: $sort)->bindings;

        $bindings['ListMode'] = $listType;

        $bindings['GameList'] = ($config['fetch'])(...$args);

        // Optional: List limit message
        if (array_key_exists('limit', $config)) {
            $bindings['ListLimit'] = (string) $config['limit'];
        }

        // Pick view: custom or default
        $viewName = $config['view'] ?? 'staff.games.list.standard-view';

        $bindings['Args'] = $args;

        return view($viewName, $bindings);
    }

    public function exportCsv(Request $request, $listType, ...$args)
    {
        $config = $this->listConfig()[$listType] ?? abort(404);

        // Reuse the existing fetch callback — may return a Builder or a Collection
        $result = ($config['fetch'])(...$args);

        // If it's a query builder, force a get()
        if ($result instanceof \Illuminate\Database\Eloquent\Builder ||
            $result instanceof \Illuminate\Database\Query\Builder) {
            $games = $result->get();
        } else {
            // It's already a collection
            $games = $result;
        }

        // Choose export filename
        $filename = "export-{$listType}.csv";
        if (!empty($args)) {
            $filename = "export-{$listType}-" . implode('-', $args) . ".csv";
        }

        // CSV headers
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
        ];

        // Stream output
        $callback = function () use ($games) {
            $out = fopen('php://output', 'w');

            // Decide what columns to export — can expand later
            fputcsv(
                $out,
                ['ID', 'Title', 'Category', 'Format Digital', 'Is low quality', 'Category verification'],
                "\t");

            foreach ($games as $g) {
                fputcsv($out, [
                    $g->id,
                    $g->title,
                    $g->category->name,
                    $g->format_digital,
                    $g->is_low_quality,
                    $g->category_verification,
                ], "\t");
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

}