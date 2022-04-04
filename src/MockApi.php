<?php

declare(strict_types=1);

namespace Justasb\MockApi;

use PHPUnit\Framework\TestCase;
use Justasb\MockApi\Mock\MethodCall;
use PHPUnit\Framework\MockObject\MockObject;
use Justasb\MockApi\Mock\MethodCallRecorder;
use Justasb\MockApi\Mock\ApiInvocationMocker;
use Justasb\MockApi\Mock\RequestToFunctionConversion;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @method ApiInvocationMocker get(string $path)
 * @method ApiInvocationMocker post(string $path)
 * @method ApiInvocationMocker patch(string $path)
 * @method ApiInvocationMocker put(string $path)
 * @method ApiInvocationMocker delete(string $path)
 */
class MockApi
{
    private TestCase $test;
    private MethodCallRecorder $callRecorder;
    private RequestToFunctionConversion $requestToFunction;
    private ?MockObject $mock;

    public function __construct(TestCase $test)
    {
        $this->test = $test;
        $this->callRecorder = new MethodCallRecorder();
        $this->requestToFunction = new RequestToFunctionConversion();
        $this->mock = null;
    }

    public function call(PsrRequestInterface $request): PsrResponseInterface
    {
        if ($this->mock === null) {
            $this->mock = $this->createMock();
        }

        $function = $this->requestToFunction->convertRequest($request);

        return call_user_func([$this->mock, $function], $request);
    }

    public function expects(InvocationOrder $invocationRule): ApiInvocationMocker
    {
        /** @var MockObject $mock */
        $mock = $this->callRecorder;
        $mocker = $mock->expects($invocationRule);

        return new ApiInvocationMocker($mocker, $this->requestToFunction);
    }

    public function __call(string $method, array $args)
    {
        if (in_array($method, ['get', 'post', 'patch', 'put', 'delete'])) {
            $mocker = $this->expects(new AnyInvokedCount());

            return call_user_func([$mocker, $method], $args[0]);
        }

        return call_user_func_array([$this, $method], $args);
    }

    private function createMock(): MockObject
    {
        $methods = array_map(
            fn (MethodCall $call) => $call->getArgs()[0],
            $this->callRecorder->___queryAllCallsTo('method')
        );

        $mock = $this->test
            ->getMockBuilder(\stdClass::class)
            ->addMethods($methods)
            ->getMock()
        ;

        $this->callRecorder->___playbackFor($mock);

        return $mock;
    }
}
