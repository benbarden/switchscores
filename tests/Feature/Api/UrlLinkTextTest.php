<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class UrlLinkTextTest extends TestCase
{
    const API_URL_LINK_TEXT = '/api/url/link-text';

    protected function setUp(): void
    {
        parent::setUp();
        // Staff-only since #152; anonymous access is covered by StaffToolingAuthTest
        $this->actingAs(new User(['display_name' => 'Api Test', 'is_staff' => 1]));
    }

    public function testBasicNoParams()
    {
        $response = $this->json('GET', self::API_URL_LINK_TEXT);

        $response->assertStatus(404);
    }

    public function testResponseStructure()
    {
        $params = ['title' => 'Super Mario Odyssey'];

        $response = $this->json('GET', self::API_URL_LINK_TEXT, $params);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'linkText'
        ]);
    }

    private function checkTestValues($response, $expected)
    {
        $actual = $response->decodeResponseJson();

        $this->assertEquals($expected, $actual['linkText']);
    }

    private function checkJsonResponse($response, $expected)
    {
        $response->assertJson([
            'linkText' => $expected,
        ]);
    }

    public function testSuperMarioOdyssey()
    {
        $inputValues = [
            'title' => 'Super Mario Odyssey'
        ];
        $expected = 'super-mario-odyssey';

        $response = $this->json('GET', self::API_URL_LINK_TEXT, $inputValues);
        $response->assertStatus(200);

        $this->checkTestValues($response, $expected);
        $this->checkJsonResponse($response, $expected);
    }

    public function testApostrophe()
    {
        $inputValues = [
            'title' => 'Johnny Turbo\'s Arcade: Bad Dudes'
        ];
        $expected = 'johnny-turbos-arcade-bad-dudes';

        $response = $this->json('GET', self::API_URL_LINK_TEXT, $inputValues);
        $response->assertStatus(200);

        $this->checkTestValues($response, $expected);
        $this->checkJsonResponse($response, $expected);
    }

}
