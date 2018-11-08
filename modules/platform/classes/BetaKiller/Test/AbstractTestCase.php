<?php
namespace BetaKiller\Test;

use Prophecy\Prophecy\ObjectProphecy;

abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
{
    protected function revealOrReturn($object)
    {
        return ($object instanceof ObjectProphecy)
            ? $object->reveal()
            : $object;
    }

    protected function writeToStderr(string $value): void
    {
        fwrite(STDERR, $value.PHP_EOL);
    }
}
