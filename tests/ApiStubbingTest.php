<?php

declare(strict_types=1);

namespace Justasb\MockApi\Test;

use Justasb\MockApi\MockApi;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleClient;
use Justasb\MockApi\GuzzleClientHandler;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\DuplicateMethodException;

class ApiStubbingTest extends TestCase
{
    // TODO: test $this->once() and $this->exactly() behavior

    public function testWillReturn(): void
    {
        $api = new MockApi($this);
        $api
            ->get('/test/will/return')
            ->willReturn(['message' => 'Success'])
        ;

        $client = $this->createHttpClient($api);
        $response = $client->get('/test/will/return');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message":"Success"}', (string) $response->getBody());
    }

    public function testIncorrectPath(): void
    {
        $api = new MockApi($this);
        $api
            ->get('/test/a')
            ->willReturn(['message' => 'Correct'])
        ;

        $client = $this->createHttpClient($api);

        $this->expectException(\Throwable::class);
        $client->get('/test/b');
    }

    public function testIncorrectMethod(): void
    {
        $api = new MockApi($this);
        $api
            ->get('/test')
            ->willReturn(['message' => 'Correct'])
        ;

        $client = $this->createHttpClient($api);

        $this->expectException(\Throwable::class);
        $client->post('/test');
    }

    public function testWith(): void
    {
        $api = new MockApi($this);
        $api
            ->post('/test/with')
            ->with(['data' => 123])
            ->willReturn(['message' => 'Success'])
        ;

        $client = $this->createHttpClient($api);
        $response = $client->post('/test/with', ['json' => ['data' => 123]]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"message":"Success"}', (string) $response->getBody());
    }

    public function testIncorrectPayload(): void
    {
        $api = new MockApi($this);
        $api
            ->post('/test')
            ->with(['data' => 123])
            ->willReturn(['message' => 'Correct'])
        ;

        $client = $this->createHttpClient($api);

        $this->expectException(ExpectationFailedException::class);
        $client->post('/test', ['json' => ['data' => 456]]);
    }

    public function testPostThenGetCalls(): void
    {
        $api = new MockApi($this);
        $api
            ->post('/test/resources')
            ->willReturn(['id' => 5])
        ;
        $api
            ->get('/test/resources/5')
            ->willReturn(['message' => 'Success'])
        ;

        $client = $this->createHttpClient($api);

        $postResponse = $client->post('/test/resources');
        $id = json_decode((string) $postResponse->getBody())->id;
        $getResponse = $client->get("/test/resources/$id");

        $this->assertEquals('{"message":"Success"}', (string) $getResponse->getBody());
    }

    public function testWrongMultipleGetCalls(): void
    {
        $api = new MockApi($this);
        $api
            ->get('/test')
            ->willReturn(['call' => 1])
        ;
        $api
            ->get('/test')
            ->willReturn(['call' => 2])
        ;

        $client = $this->createHttpClient($api);

        $this->expectException(DuplicateMethodException::class);
        $client->get('/test');
    }

    public function testCorrectMultipleGetCalls(): void
    {
        $api = new MockApi($this);
        $api
            ->get('/test')
            ->willReturn(['call' => 1], ['call' => 2])
        ;

        $client = $this->createHttpClient($api);

        $response1 = $client->get('/test');
        $response2 = $client->get('/test');

        $this->assertEquals('{"call":1}', (string) $response1->getBody());
        $this->assertEquals('{"call":2}', (string) $response2->getBody());
    }

    private function createHttpClient(MockApi $api): GuzzleClient
    {
        $handler = HandlerStack::create(new GuzzleClientHandler($api));

        return new GuzzleClient([
            'base_uri' => 'https://api.myservice.io',
            'handler' => $handler,
        ]);
    }
}
