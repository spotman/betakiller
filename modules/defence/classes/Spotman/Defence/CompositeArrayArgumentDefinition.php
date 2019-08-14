<?php
declare(strict_types=1);

namespace Spotman\Defence;

class CompositeArrayArgumentDefinition extends AbstractArgumentDefinition implements
    CompositeArrayArgumentDefinitionInterface
{
    use ArgumentWithRulesTrait;

    /**
     * @var CompositeArgumentDefinitionInterface
     */
    private $composite;

    /**
     * CompositeArrayArgumentDefinition constructor.
     *
     * @param string                                                $name
     * @param \Spotman\Defence\CompositeArgumentDefinitionInterface $composite
     */
    public function __construct(string $name, CompositeArgumentDefinitionInterface $composite)
    {
        $this->composite = $composite;

        parent::__construct($name, self::TYPE_COMPOSITE_ARRAY);
    }

    public function getComposite(): CompositeArgumentDefinitionInterface
    {
        return $this->composite;
    }
}
