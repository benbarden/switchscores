<?php

namespace Tests\Page;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Enums\HttpStatus;

class GamesTest extends TestCase
{
    public function doPageTest($url, $status = HttpStatus::STATUS_OK)
    {
        $response = $this->get($url);
        $response->assertStatus($status->value);
    }

    public function testGamesDetailPage()
    {
        $this->doPageTest('/games/1/the-legend-of-zelda-breath-of-the-wild');
        $this->doPageTest("/games/1", HttpStatus::REDIR_PERM);

        $this->doPageTest("/games/2/1-2-switch");
        $this->doPageTest("/games/2", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/2/abc", HttpStatus::REDIR_PERM);
    }

    public function testGamesBrowseByDatePage()
    {
        $this->doPageTest("/games/by-date", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/by-date/2020-01", HttpStatus::REDIR_PERM);

        $this->doPageTest("/c/switch-1/2016", HttpStatus::NOT_FOUND);
        $this->doPageTest("/c/switch-1/2016/12", HttpStatus::NOT_FOUND);

        $this->doPageTest("/c/switch-2/2024", HttpStatus::NOT_FOUND);
        $this->doPageTest("/c/switch-2/2024/12", HttpStatus::NOT_FOUND);

        $this->doPageTest("/c/switch-1/date");
        $this->doPageTest("/c/switch-1/2025/01");
        $this->doPageTest("/c/switch-1/2020/01");
        $this->doPageTest("/c/switch-1/2019/12");
        $this->doPageTest("/c/switch-1/2018/05");
        $this->doPageTest("/c/switch-1/2018/01");
        $this->doPageTest("/c/switch-1/2017/03");

        $this->doPageTest("/c/switch-1/2017/02", HttpStatus::NOT_FOUND);
        $this->doPageTest("/c/switch-1/2016/01", HttpStatus::NOT_FOUND);

        $this->doPageTest("/c/switch-2/date");
        $this->doPageTest("/c/switch-2/2025/06");
        $this->doPageTest("/c/switch-2/2025/07");
    }

    public function testGamesGeneralPages()
    {
        $this->doPageTest("/games", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/search");
    }

    public function testGamesBrowsePages()
    {
        $this->doPageTest("/games/by-category", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/by-category/adventure", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/by-series/pokemon", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/by-tag", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/by-tag/board-game", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/by-collection", HttpStatus::REDIR_PERM);
        $this->doPageTest("/games/by-collection/lego", HttpStatus::REDIR_PERM);

        $this->doPageTest("/c/switch-1/category");
        $this->doPageTest("/c/switch-1/category/adventure");
        $this->doPageTest("/c/switch-1/series/pokemon");
        $this->doPageTest("/c/switch-1/tag");
        $this->doPageTest("/c/switch-1/tag/mahjong");
        $this->doPageTest("/c/switch-1/collection");
        $this->doPageTest("/c/switch-1/collection/lego");

        $this->doPageTest("/c/switch-2/category");
        $this->doPageTest("/c/switch-2/category/adventure");
        $this->doPageTest("/c/switch-2/series/pokemon");
        $this->doPageTest("/c/switch-2/tag");
        $this->doPageTest("/c/switch-2/tag/mahjong");
        $this->doPageTest("/c/switch-2/collection");
        $this->doPageTest("/c/switch-2/collection/lego");
    }
}
