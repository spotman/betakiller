<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Parameter\ParameterInterface;
use Spotman\Defence\Parameter\ParameterProviderInterface;

final class FakeStringParameterProvider implements ParameterProviderInterface
{
    public static function makeValue(string $value): string
    {
        return 'fake-'.$value.'-fake';
    }

    public function convertValue($value): ParameterInterface
    {
        if (!\is_string($value)) {
            throw new \LogicException('Test value must be "string"');
        }

        return new FakeStringParameter('fake-'.$value.'-fake');
    }
}
