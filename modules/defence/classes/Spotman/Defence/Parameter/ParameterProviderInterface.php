<?php
declare(strict_types=1);

namespace Spotman\Defence\Parameter;

interface ParameterProviderInterface
{
    public function convertValue($value): ParameterInterface;
}
