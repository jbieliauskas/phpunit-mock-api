<?php

declare(strict_types=1);

namespace Justasb\MockApi\Mock;

class MethodCallRecorder
{
    private array $callTree = [];

    public function __call(string $method, array $args): self
    {
        $newRecorder = new self();

        $this->callTree[] = [
            new MethodCall($method, $args),
            $newRecorder
        ];

        return $newRecorder;
    }

    public function ___playbackFor(object $object): void
    {
        /**
         * @var MethodCall $call
         * @var self $recorder
         */
        foreach ($this->callTree as [$call, $recorder]) {
            $method = $call->getName();
            $args = $call->getArgs();

            $result = call_user_func_array([$object, $method], $args);

            if ($result !== null) {
                $recorder->___playbackFor($result);
            }
        }
    }

    /**
     * @return MethodCall[]
     */
    public function ___queryAllCallsTo(string $method): array
    {
        $foundCalls = [];

        /**
         * @var MethodCall $call
         * @var self $recorder
         */
        foreach ($this->callTree as [$call, $recorder]) {
            if ($call->getName() === $method) {
                $foundCalls[] = clone $call;
            }

            $furtherFoundCalls = $recorder->___queryAllCallsTo($method);
            $foundCalls = array_merge($foundCalls, $furtherFoundCalls);
        }

        return $foundCalls;
    }
}
