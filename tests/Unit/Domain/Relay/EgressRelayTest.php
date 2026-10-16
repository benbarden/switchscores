<?php

namespace Tests\Unit\Domain\Relay;

use App\Domain\Relay\EgressRelay;

use Tests\TestCase;

class EgressRelayTest extends TestCase
{
    public function testItIsOnlyConfiguredWithBothUrlAndKey()
    {
        $this->assertFalse((new EgressRelay(null, null))->isConfigured());
        $this->assertFalse((new EgressRelay('https://relay.example/', null))->isConfigured());
        $this->assertFalse((new EgressRelay(null, 'secret'))->isConfigured());
        $this->assertTrue((new EgressRelay('https://relay.example/', 'secret'))->isConfigured());
    }

    public function testTheTargetIsEncodedIntoTheUrlParameter()
    {
        $relay = new EgressRelay('https://relay.example/', 'secret');

        $url = $relay->relayUrl('https://api.ec.nintendo.com/v1/price?country=GB&lang=en&ids=1,2');

        // The target's own query string must survive as part of the one url= value.
        $this->assertEquals(
            'https://relay.example/?url='.rawurlencode('https://api.ec.nintendo.com/v1/price?country=GB&lang=en&ids=1,2'),
            $url
        );
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $this->assertEquals('https://api.ec.nintendo.com/v1/price?country=GB&lang=en&ids=1,2', $params['url']);
    }

    public function testTheKeyIsSentAsAHeader()
    {
        $this->assertEquals(['X-Relay-Key' => 'secret'], (new EgressRelay('https://relay.example/', 'secret'))->headers());
    }

    /**
     * The Worker's refusals are short plain-text bodies; a 403 from the target itself is passed
     * through and must not be mistaken for the relay rejecting the key.
     */
    public function testRefusalsAreToldApartFromTheTargetsOwnErrors()
    {
        $relay = new EgressRelay('https://relay.example/', 'secret');

        $this->assertStringContainsString('rejected the key', $relay->refusal(403, 'Forbidden'));
        $this->assertStringContainsString('allowlist', $relay->refusal(400, 'Host not allowed'));
        $this->assertNull($relay->refusal(403, '<html><title>Visitor anti-robot validation</title></html>'));
        $this->assertNull($relay->refusal(200, 'Forbidden'));
    }
}
