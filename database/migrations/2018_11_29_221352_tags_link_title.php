<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Services\UrlService;

class TagsLinkTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tags', function(Blueprint $table) {
            $table->string('link_title', 100);
            $table->index('link_title', 'link_title');
        });

        $serviceUrl = new UrlService();

        $tags = \DB::select("
            SELECT id, tag_name FROM tags ORDER BY id
        ");

        if ($tags) {

            foreach ($tags as $tag) {

                $tagId = $tag->id;
                $tagName = $tag->tag_name;

                $linkTitle = $serviceUrl->generateLinkText($tagName);

                \DB::update("
                    UPDATE tags SET link_title = ? WHERE id = ?
                ", [$linkTitle, $tagId]);

            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tags', function(Blueprint $table) {
            $table->dropColumn('link_title');
        });
    }
}
