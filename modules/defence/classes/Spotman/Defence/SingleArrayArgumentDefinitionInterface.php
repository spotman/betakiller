<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface SingleArrayArgumentDefinitionInterface extends ArgumentDefinitionInterface, ArgumentWithRulesInterface
{
    /**
     * @return \Spotman\Defence\SingleArgumentDefinitionInterface
     */
    public function getNested(): SingleArgumentDefinitionInterface;
}
