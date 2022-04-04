<?php

declare(strict_types=1);

namespace Justasb\MockApi\Mock;

use Psr\Http\Message\RequestInterface as PsrRequestInterface;

class RequestToFunctionConversion
{
    public function convert(string $method, string $path): string
    {
        $pathPieces = preg_split('|[/_-]|', $path);

        return array_reduce(
            $pathPieces,
            function (string $function, string $piece) {
                return $function . ucfirst(strtolower($piece));
            },
            strtolower($method)
        );
    }

    public function convertRequest(PsrRequestInterface $request): string
    {
        return $this->convert(
            $request->getMethod(),
            $request->getUri()->getPath()
        );
    }
}
