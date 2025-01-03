<?php
declare(strict_types=1);

namespace Spotman\Defence\Parameter;

use Spotman\Defence\ParameterArgumentDefinitionInterface;

interface ParameterProviderInterface
{
    /**
     * @param string|int $value
     *
     * @return \Spotman\Defence\Parameter\ArgumentParameterInterface
     */
    public function convertValue(string|int $value): ArgumentParameterInterface;
}
