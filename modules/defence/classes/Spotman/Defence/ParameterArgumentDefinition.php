<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\StringFilter;

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
    public function __construct(string $name, string $codename)
    {
        parent::__construct($name, self::TYPE_PARAMETER);

        $this->addFilter(new StringFilter());

        $this->codename = $codename;
    }

    public function getCodename(): string
    {
        return $this->codename;
    }
}
