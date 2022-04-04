<?php

declare(strict_types=1);

namespace Justasb\MockApi\Test;

use Justasb\MockApi\MockApi;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleClient;
use Justasb\MockApi\GuzzleClientHandler;
use Justasb\MockApi\Constraint\RequestConstraintTrait;

class BodyTest extends TestCase
{
    // TODO: provide recursive assertEqualsCanonicalizing()

    use RequestConstraintTrait;

    public function testJsonEqualsCanonicalizing(): void
    {
        $api = new MockApi($this);
        $api
            ->post('/test/json/canonicalizing')
            ->with([
                'name' => 'test item',
                'widget' => 'foo',
            ])
            ->willReturn(['message' => 'Success'])
        ;

        $client = $this->createHttpClient($api);

        $response = $client->post('/test/json/canonicalizing', [
            'json' => [
                'widget' => 'foo',
                'name' => 'test item',
            ],
        ]);

        $this->assertEquals('{"message":"Success"}', (string) $response->getBody());
    }

    public function testJsonHasKey(): void
    {
        $api = new MockApi($this);
        $api
            ->patch('/test/json/has-key')
            ->with(
                $this->requestJsonIs(
                    $this->arrayHasKey('timestamp')
                )
            )
            ->willReturn(['message' => 'Success'])
        ;

        $client = $this->createHttpClient($api);

        $response = $client->patch('/test/json/has-key', [
            'json' => [
                'widget' => 'foo',
                'timestamp' => strtotime('today'),
            ],
        ]);

        $this->assertEquals('{"message":"Success"}', (string) $response->getBody());
    }

    private function createHttpClient(MockApi $api): GuzzleClient
    {
        $handler = HandlerStack::create(new GuzzleClientHandler($api));

        return new GuzzleClient([
            'base_uri' => 'https://api.myservice.io',
            'handler' => $handler,
        ]);
    }

    // passing constraint also works
}
