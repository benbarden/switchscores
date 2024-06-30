<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 100);
            $table->integer('category_id');
            $table->string('url', 150);
            $table->mediumText('content_html');
            $table->integer('game_id')->nullable();
            $table->timestamps();

            $table->unique('url', 'url');
            $table->index('category_id', 'category_id');
            $table->index('game_id', 'game_id');
        });

        Schema::create('news_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('link_name', 100);
            $table->timestamps();

            $table->unique('link_name', 'link_name');
        });

        DB::insert("
            INSERT INTO news_categories(name, link_name, created_at, updated_at)
            VALUES('Site updates', 'site-updates', now(), now())
        ");
        DB::insert("
            INSERT INTO news_categories(name, link_name, created_at, updated_at)
            VALUES('Top Rated: New Releases', 'top-rated-new-releases', now(), now())
        ");

        $topRatedNewReleasesHtml = '        <p>
            Here are the top 10 rated Nintendo Switch games, based on recent releases only.
            This list is static - it won\'t change as future reviews come in.
            We\'ll aim to post more of these lists in the future.
        </p>
        <table class="table table-striped table-responsive">
            <thead>
            <tr>
                <th>Game</th>
                <th class="text-center">Rating</th>
                <th class="text-right">Reviews</th>
            </tr>
            </thead>
            <tbody>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/73/steamworld-dig-2">
                            SteamWorld Dig 2
                        </a>
                    </td>
                    <td class="text-center">
                        9.0
                    </td>
                    <td class="text-right">
                        11
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/98/stardew-valley">
                            Stardew Valley
                        </a>
                    </td>
                    <td class="text-center">
                        9.0
                    </td>
                    <td class="text-right">
                        3
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/330/lovers-in-a-dangerous-spacetime">
                            Lovers in a Dangerous Spacetime
                        </a>
                    </td>
                    <td class="text-center">
                        8.9
                    </td>
                    <td class="text-right">
                        4
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/335/oxenfree">
                            Oxenfree
                        </a>
                    </td>
                    <td class="text-center">
                        8.7
                    </td>
                    <td class="text-right">
                        3
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/142/axiom-verge">
                            Axiom Verge
                        </a>
                    </td>
                    <td class="text-center">
                        8.6
                    </td>
                    <td class="text-right">
                        4
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/138/golf-story">
                            Golf Story
                        </a>
                    </td>
                    <td class="text-center">
                        8.0
                    </td>
                    <td class="text-right">
                        6
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/144/pokken-tournament-dx">
                            Pokken Tournament DX
                        </a>
                    </td>
                    <td class="text-center">
                        8.0
                    </td>
                    <td class="text-right">
                        10
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/87/nba-2k18">
                            NBA 2K18
                        </a>
                    </td>
                    <td class="text-center">
                        7.9
                    </td>
                    <td class="text-right">
                        4
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/248/butcher">
                            Butcher
                        </a>
                    </td>
                    <td class="text-center">
                        7.9
                    </td>
                    <td class="text-right">
                        4
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/259/inversus-deluxe">
                            Inversus Deluxe
                        </a>
                    </td>
                    <td class="text-center">
                        7.7
                    </td>
                    <td class="text-right">
                        4
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/243/quest-of-dungeons">
                            Quest of Dungeons
                        </a>
                    </td>
                    <td class="text-center">
                        7.7
                    </td>
                    <td class="text-right">
                        4
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/271/kingdom-new-lands">
                            Kingdom: New Lands
                        </a>
                    </td>
                    <td class="text-center">
                        7.6
                    </td>
                    <td class="text-right">
                        5
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/329/pankapu">
                            Pankapu
                        </a>
                    </td>
                    <td class="text-center">
                        7.6
                    </td>
                    <td class="text-right">
                        4
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/103/dragon-ball-xenoverse-2">
                            Dragon Ball Xenoverse 2
                        </a>
                    </td>
                    <td class="text-center">
                        7.6
                    </td>
                    <td class="text-right">
                        6
                    </td>
                </tr>
                            <tr>
                    <td>
                        <a href="http://www.worldofswitch.com/games/252/semispheres">
                            Semispheres
                        </a>
                    </td>
                    <td class="text-center">
                        7.5
                    </td>
                    <td class="text-right">
                        5
                    </td>
                </tr>
                        </tbody>
        </table>
        <p>
            Note: As is our normal policy here, games with fewer than 3 reviews in our database are not
            included in the above list.
        </p>';

        $siteUpdateHtml = '        <p>
            We\'ve just added a new feature to World of Switch: <strong>Ranking</strong>. This will show up
            in the following places:
        </p>
        <ol>
            <li>
                <a href="http://www.worldofswitch.com/reviews/top-rated/all-time">Top Rated - All-time</a>: look for the
                number on the left-hand side.
            </li>
            <li>
                <a href="http://www.worldofswitch.com/games/53/arms">Game pages</a>: look for the
                new "Rank" box in the top-right corner.
            </li>
        </ol>
        <p>
            This makes it easy to understand where a game fits into the overall ranking of all Switch games
            listed at World of Switch.
        </p>
        <p>
            Games with fewer than 3 reviews are not ranked; they will be included if and when more reviews
            of these titles are available.
        </p>';

        $topRatedNewReleasesHtml = addslashes($topRatedNewReleasesHtml);

        DB::insert("
            INSERT INTO news(title, category_id, url, content_html, game_id, created_at, updated_at)
            VALUES('Top Rated: New Releases - 12th October, 2017', 2, '/news/171012/top-rated-new-releases-12th-october-2017',
            '$topRatedNewReleasesHtml', null, now(), now())
        ");

        $siteUpdateHtml = addslashes($siteUpdateHtml);

        DB::insert("
            INSERT INTO news(title, category_id, url, content_html, game_id, created_at, updated_at)
            VALUES('Site update - 15th October, 2017', 1, '/news/171015/site-update-15th-october-2017',
            '$siteUpdateHtml', null, now(), now())
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_categories');
        Schema::dropIfExists('news');
    }
}
