<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Traits\SwitchServices;

class MoveReleaseDatesToGamesTable extends Migration
{
    use SwitchServices;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('image_count');
            $table->dropColumn('overview');

            $table->date('eu_release_date')->nullable();
            $table->date('us_release_date')->nullable();
            $table->date('jp_release_date')->nullable();
            $table->tinyInteger('eu_is_released');
            $table->integer('release_year')->nullable();
        });

        $gamesList = DB::select('
            SELECT g.id, g.title FROM games g ORDER BY g.id
        ');

        foreach ($gamesList as $game) {

            $gameId = $game->id;

            $game = $this->getServiceGame()->find($gameId);

            if (!$game) continue;

            $euReleaseDate = $game->regionReleaseDate('eu');
            $usReleaseDate = $game->regionReleaseDate('us');
            $jpReleaseDate = $game->regionReleaseDate('jp');

            $game->eu_release_date = $euReleaseDate->release_date;
            $game->us_release_date = $usReleaseDate->release_date;
            $game->jp_release_date = $jpReleaseDate->release_date;
            $game->eu_is_released = $euReleaseDate->is_released;
            $game->release_year = $euReleaseDate->release_year;
            $game->save();

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
