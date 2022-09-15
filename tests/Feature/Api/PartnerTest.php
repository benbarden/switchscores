<?php

namespace Tests\Feature\Api;

use App\Models\Partner;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    const API_URL_REVIEW_SITE = '/api/review/site';

    private $siteSwitchPlayer = [
        'siteId' => 2,
        'siteName' => 'Switch Player'
    ];

    public function testBasicNoParams()
    {
        $response = $this->json('GET', self::API_URL_REVIEW_SITE);

        $response->assertStatus(404);
    }

    public function testResponseStructure()
    {
        $params = ['reviewUrl' => 'http://switchplayer.net/abc'];

        $response = $this->json('GET', self::API_URL_REVIEW_SITE, $params);
        //dd($response->getContent());
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'siteId', 'siteName'
        ]);
    }

    private function checkTestValues($response, $expected)
    {
        $actual = $response->decodeResponseJson();

        $this->assertEquals($expected['siteId'], $actual['siteId']);
        $this->assertEquals($expected['siteName'], $actual['siteName']);
    }

    private function checkJsonResponse($response, $expected)
    {
        $response->assertJson([
            'siteId' => $expected['siteId'],
            'siteName' => $expected['siteName'],
        ]);
    }

    public function testSwitchPlayerValidReviewLink()
    {
        $inputValues = [
            'reviewUrl' => 'http://switchplayer.net/2017/03/13/super-bomberman-r-review/'
        ];

        $response = $this->json('GET', self::API_URL_REVIEW_SITE, $inputValues);
        $response->assertStatus(200);

        $this->checkTestValues($response, $this->siteSwitchPlayer);
        $this->checkJsonResponse($response, $this->siteSwitchPlayer);
    }

    public function testSwitchPlayerInvalidReviewLink()
    {
        $inputValues = [
            'reviewUrl' => 'http://switchplayerz.net/blabla/abc/'
        ];

        $response = $this->json('GET', self::API_URL_REVIEW_SITE, $inputValues);
        $response->assertStatus(404);

        //$this->checkTestValues($response, $this->siteSwitchPlayer);
        //$this->checkJsonResponse($response, $this->siteSwitchPlayer);
    }
}
