<?php
declare(strict_types=1);

namespace Spotman\Defence\Parameter;

use Spotman\Defence\ArgumentDefinitionInterface;

interface ParameterProviderFactoryInterface
{
    public function createFor(ArgumentDefinitionInterface $argDef): ParameterProviderInterface;
}
