<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class GameCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->decimal('progress', 6, 2)->default(0.00);
            $table->tinyInteger('is_active');
            $table->timestamps();

            $table->index('is_active', 'is_active');
        });

        Schema::create('campaign_games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id');
            $table->integer('game_id');
            $table->timestamps();

            $table->index('campaign_id', 'campaign_id');
        });

        DB::insert("
            INSERT INTO campaigns(id, name, description, is_active, created_at, updated_at)
            VALUES (1, 'Switch Highlights round-up', 'A round-up of unranked games from our Switch Highlights series.', 1, NOW(), NOW())
        ");

        $gamesList = [
            4488,4485,4232,4439,4397,4413,4512,4381,4486,4513,
            4496,4546,4461,4376,4364,4245,4378,4468,4524,4514,
            4556,4617,4583,4447,4582,4516,4411,4567,4560,4593,
            4630,4612,4520,4663,4276,4499,4542,4585,
            4602,4565,4530,4595,4613,4664,4670,4562,4564,
        ];

        $campaignId = 1;

        foreach ($gamesList as $gameItem) {

            $gameId = $gameItem;

            DB::insert("
                INSERT INTO campaign_games(campaign_id, game_id, created_at, updated_at) VALUES(?, ?, NOW(), NOW())
            ", [$campaignId, $gameId]);

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_games');
        Schema::dropIfExists('campaigns');
    }
}
