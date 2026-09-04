<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\Staff\Games\SingleGameAddController;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * The single-game add tool (#135) reads a pasted store row before it touches the
 * network, and these are the branches that never do.
 *
 * A paste with several rows must not silently take the first one: on the store search
 * results it is easy to copy neighbouring games by accident, and the wrong one would be
 * imported with no sign anything was off.
 */
class SingleGameAddAnalyseTest extends TestCase
{
    private SingleGameAddController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->controller = $this->app->make(SingleGameAddController::class);
    }

    private function analyse(array $input)
    {
        return $this->controller->analyse(Request::create('/x', 'POST', $input));
    }

    private function sampleHtml(): string
    {
        return file_get_contents(__DIR__.'/../Domain/WeeklyBatch/fixtures/nintendo-sample.html');
    }

    public function testSeveralRowsPastedOffersAChoiceRatherThanGuessing()
    {
        $response = $this->analyse(['raw_html' => $this->sampleHtml()]);
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('choose', $data['status']);
        $this->assertGreaterThan(1, count($data['candidates']));

        // Enough to tell the games apart in the picker
        $first = $data['candidates'][0];
        $this->assertArrayHasKey('index', $first);
        $this->assertNotEmpty($first['title']);
        $this->assertNotEmpty($first['console']);
    }

    public function testEmptyInputAsksForSomethingToWorkWith()
    {
        $response = $this->analyse([]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('failed', json_decode($response->getContent(), true)['status']);
    }

    public function testHtmlWithNoGameRowsIsRejected()
    {
        $response = $this->analyse(['raw_html' => '<li data-nsuid="1"></li>']);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testTextThatIsNotAUrlIsRejectedBeforeAnyFetch()
    {
        $response = $this->analyse(['nintendo_url' => 'Some Ordinary Game']);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString(
            'URL',
            json_decode($response->getContent(), true)['message']
        );
    }

    public function testAnIndexOutsideThePasteIsRejected()
    {
        $response = $this->analyse([
            'raw_html'    => $this->sampleHtml(),
            'entry_index' => 99,
        ]);

        $this->assertEquals(422, $response->getStatusCode());
    }
}
