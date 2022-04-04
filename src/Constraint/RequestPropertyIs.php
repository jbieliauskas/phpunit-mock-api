<?php

declare(strict_types=1);

namespace Justasb\MockApi\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class RequestPropertyIs extends Constraint
{
    private Constraint $constraint;
    private string $property;
    private \Closure $extract;

    public function __construct(Constraint $constraint, string $property, \Closure $extract)
    {
        $this->constraint = $constraint;
        $this->property = $property;
        $this->extract = $extract;
    }

    /**
     * @inheritDoc
     *
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function evaluate($request, string $description = '', bool $returnResult = false): ?bool
    {
        return $this->constraint->evaluate(
            ($this->extract)($request),
            $description,
            $returnResult
        );
    }

    public function count(): int
    {
        return $this->constraint->count();
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return "\$request->{$this->property} " . $this->constraint->toString();
    }
}
