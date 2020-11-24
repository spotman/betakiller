<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Parameter\ParameterInterface;

final class FakeStringParameter implements ParameterInterface
{
    private string $value;

    /**
     * FakeStringParameter constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
