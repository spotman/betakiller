<?php
declare(strict_types=1);

namespace Spotman\Defence;

interface ArgumentsDefinitionProviderInterface
{
    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     */
    public function addArgumentsDefinition(DefinitionBuilderInterface $builder): void;
}
