<?php

namespace Tests\Feature\PublicSite;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameSearch\SearchTerm;
use Tests\TestCase;

/**
 * Title searches drop 4-byte characters (emoji) before querying, as games.title is utf8mb3 and
 * MySQL fails the whole query on them (error 3988). Scanner-style terms must not cause an error.
 */
class SearchTermTest extends TestCase
{
    public function testCleanDropsFourByteCharactersAndKeepsTheRest()
    {
        $this->assertSame('Mario', SearchTerm::clean('Mario😀'));
        $this->assertSame('Pokémon Legends', SearchTerm::clean('  Pokémon   Legends 🎮 '));
        $this->assertSame("the%27%22\\'\"", SearchTerm::clean("the\u{1F600}%27%22\\'\""));
        $this->assertSame('2025', SearchTerm::clean(2025));
    }

    public function testCleanIgnoresArraysAndNull()
    {
        $this->assertSame('', SearchTerm::clean(['x']));
        $this->assertSame('', SearchTerm::clean(null));
    }

    public function testCleanCopesWithInvalidUtf8()
    {
        $clean = SearchTerm::clean("Zelda\xF0\x9F");

        $this->assertTrue(mb_check_encoding($clean, 'UTF-8'));
        $this->assertStringStartsWith('Zelda', $clean);
    }

    public function testPublicSearchWithAnEmojiDoesNotFail()
    {
        $this->get(route('games.search', ['search_keywords' => 'Mario😀']))
            ->assertStatus(200)
            ->assertSee('Mario');
    }

    public function testPublicSearchWithAScannerStyleQueryDoesNotFail()
    {
        $this->get(route('games.search', [
            'search_keywords' => "the\u{1F600}%2527%2522\\'\"",
            'search_year_released' => 2025,
            'search_category' => 50,
            'search_series' => 175,
            'search_collection' => 1,
        ]))->assertStatus(200);
    }

    public function testPublicSearchWithAnArrayTermDoesNotFail()
    {
        $this->get('/games/search?search_keywords[]=x')->assertStatus(200);
    }

    public function testRepositoryTitleSearchesDoNotFailOnAnEmoji()
    {
        $repo = app(GameRepository::class);

        $this->assertIsIterable($repo->searchByTitle('Zelda😀'));
        $this->assertIsIterable($repo->partialTitleSearch('Zelda😀'));
        $this->assertIsIterable($repo->findWithFilters(['keywords' => 'Zelda😀']));
    }
}
