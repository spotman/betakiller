<?php
declare(strict_types=1);

namespace Spotman\Defence;

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
    public function __construct(string $name, string $type, string $codename)
    {
        parent::__construct($name, $type);

        $this->codename = $codename;
    }

    public function getCodename(): string
    {
        return $this->codename;
    }
}
