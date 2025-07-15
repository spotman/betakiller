<?php
declare(strict_types=1);

namespace Spotman\Defence;

use Spotman\Defence\Filter\ArrayFilter;

class SingleArrayArgumentDefinition extends AbstractArgumentDefinition implements SingleArrayArgumentDefinitionInterface
{
    use ArgumentWithRulesTrait;
    use ArgumentWithFiltersTrait;

    /**
     * @var \Spotman\Defence\SingleArgumentDefinitionInterface
     */
    private SingleArgumentDefinitionInterface $nested;

    /**
     * SingleArrayArgumentDefinition constructor.
     *
     * @param string                                             $name
     * @param \Spotman\Defence\SingleArgumentDefinitionInterface $nested
     */
    public function __construct(string $name, SingleArgumentDefinitionInterface $nested)
    {
        $this->nested = $nested;

        parent::__construct($name, self::TYPE_SINGLE_ARRAY);

        $this->addFilter(new ArrayFilter());
    }

    public function getNested(): SingleArgumentDefinitionInterface
    {
        return $this->nested;
    }
}
