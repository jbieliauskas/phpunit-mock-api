<?php

declare(strict_types=1);

namespace Justasb\MockApi\Constraint;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;

trait RequestConstraintTrait
{
    // TODO: provide assertArray* method equivalents

    private function requestJsonEqualsCanonicalizing(array $expected): RequestPropertyIs
    {
        return $this->requestJsonIs(
            new IsEqual($expected, 0, true)
        );
    }

    private function requestJsonIs(Constraint $constraint): RequestPropertyIs
    {
        return new RequestPropertyIs(
            $constraint,
            'json',
            fn (PsrRequestInterface $request) => json_decode((string) $request->getBody(), true)
        );
    }
}
