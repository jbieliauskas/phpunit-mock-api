<?php

declare(strict_types=1);

namespace Justasb\MockApi;

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use GuzzleHttp\Promise\PromiseInterface as GuzzlePromiseInterface;

class GuzzleClientHandler
{
    private MockApi $mockApi;

    public function __construct(MockApi $mockApi)
    {
        $this->mockApi = $mockApi;
    }

    public function __invoke(PsrRequestInterface $request): GuzzlePromiseInterface
    {
        $response = $this->mockApi->call($request);

        return new FulfilledPromise($response);
    }
}
