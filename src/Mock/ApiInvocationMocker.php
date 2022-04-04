<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Justasb\MockApi\Mock;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalAnd;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Justasb\MockApi\Constraint\RequestPropertyIs;
use Justasb\MockApi\Constraint\RequestConstraintTrait;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @method self get(string $path)
 * @method self post(string $path)
 * @method self patch(string $path)
 * @method self put(string $path)
 * @method self delete(string $path)
 */
class ApiInvocationMocker
{
    use RequestConstraintTrait;

    /**
     * @var InvocationMocker
     */
    private $mocker;
    private RequestToFunctionConversion $requestToFunction;
    private string $method;

    public function __construct($mocker, RequestToFunctionConversion $requestToFunction)
    {
        $this->mocker = $mocker;
        $this->requestToFunction = $requestToFunction;
    }

    public function __call(string $method, array $args)
    {
        if (in_array($method, ['get', 'post', 'patch', 'put', 'delete'])) {
            return $this->request($method, $args[0]);
        }

        return call_user_func_array([$this, $method], $args);
    }

    public function with(...$args): self
    {
        $this->mocker->with(...array_map(
            fn ($value) => $this->toRequestConstraint($value),
            $args
        ));

        return $this;
    }

    public function willReturn(...$values): self
    {
        $this->mocker->willReturn(...array_map(
            fn ($value) => $this->toResponse($value),
            $values
        ));

        return $this;
    }

    private function request(string $method, string $path): self
    {
        $function = $this->requestToFunction->convert($method, $path);
        $this->mocker->method($function);

        $this->method = $method;

        return $this;
    }

    private function toRequestConstraint($value): Constraint
    {
        if (is_array($value)) {
            $value = $this->requestJsonEqualsCanonicalizing($value);
        }

        if (!$value instanceof Constraint) {
            $value = new IsEqual($value);
        }

        $correctMethod = new RequestPropertyIs(
            new IsEqual($this->method, 0, false, true),
            'method',
            fn (PsrRequestInterface $request) => $request->getMethod()
        );

        $and = new LogicalAnd();
        $and->setConstraints([$correctMethod, $value]);

        return $and;
    }

    private function toResponse($value): PsrResponseInterface
    {
        if (is_array($value)) {
            $value = new GuzzleResponse(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($value)
            );
        }

        return $value;
    }
}
