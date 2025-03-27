<?php

declare(strict_types=1);

namespace Spotman\Defence;

use LogicException;
use Spotman\Defence\Filter\ParameterFilter;
use Spotman\Defence\Parameter\ArgumentParameterInterface;

class ParameterArgumentDefinition extends SingleArgumentDefinition implements ParameterArgumentDefinitionInterface
{
    /**
     * Parameter codename (required for provider factory selection)
     *
     * @var string
     */
    private string $codename;

    /**
     * @inheritDoc
     */
    public function __construct(string $name, string $fqcn)
    {
        parent::__construct($name, self::TYPE_PARAMETER);

        $this->addFilter(new ParameterFilter());

        if (!is_a($fqcn, ArgumentParameterInterface::class, true)) {
            throw new LogicException(
                sprintf(
                    'Argument parameter must implement "%s" but "%s" provided',
                    ArgumentParameterInterface::class,
                    $fqcn
                )
            );
        }

        $this->codename = $fqcn::getParameterName();
    }

    public function getCodename(): string
    {
        return $this->codename;
    }
}
