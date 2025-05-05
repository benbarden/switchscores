<?php


namespace App\Domain\Tag;

use Illuminate\Support\Facades\DB;

use App\Models\Tag;
use App\Models\Game;

class Repository
{
    public function create($tagName, $linkTitle, $tagCategoryId)
    {
        Tag::create([
            'tag_name' => $tagName,
            'link_title' => $linkTitle,
            'tag_category_id' => $tagCategoryId,
        ]);
    }

    public function edit(Tag $tagData, $tagName, $linkTitle, $tagCategoryId)
    {
        $values = [
            'tag_name' => $tagName,
            'link_title' => $linkTitle,
            'tag_category_id' => $tagCategoryId,
        ];

        $tagData->fill($values);
        $tagData->save();
    }

    public function delete($tagId)
    {
        Tag::where('id', $tagId)->delete();
    }

    public function find($tagId)
    {
        return Tag::find($tagId);
    }

    public function getByLinkTitle($linkTitle)
    {
        return Tag::where('link_title', $linkTitle)->first();
    }

    public function getAll()
    {
        return Tag::orderBy('tag_name', 'asc')->get();
    }

    public function gamesByTag($tagId)
    {
        return Game::whereHas('gameTags', function($query) use ($tagId) {
            $query->where('tag_id', $tagId);
        })->get();
    }

    public function rankedByTag($consoleId, $tagId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('games.console_id', $consoleId)
            ->where('game_tags.tag_id', $tagId)
            ->whereNotNull('games.game_rank')
            ->where('games.format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.game_rank', 'asc')
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function unrankedByTag($consoleId, $tagId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('games.console_id', $consoleId)
            ->where('game_tags.tag_id', $tagId)
            ->whereNull('games.game_rank')
            ->where('games.format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function delistedByTag($consoleId, $tagId, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'games.id AS game_id',
                'game_tags.id AS game_tag_id',
                'tags.tag_name')
            ->where('games.console_id', $consoleId)
            ->where('game_tags.tag_id', $tagId)
            ->whereNull('games.game_rank')
            ->where('format_digital', '=', Game::FORMAT_DELISTED)
            ->orderBy('games.title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }
}