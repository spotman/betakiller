<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Rule\DefinitionRuleInterface;

abstract class AbstractRuleTest extends AbstractDefenceTest
{
    /**
     * @param mixed $input
     *
     * @dataProvider validDataProvider
     */
    public function testValid($input): void
    {
        $filter = $this->makeInstance();

        $this->assertTrue($filter->check($input));
    }

    /**
     * @param mixed $input
     *
     * @dataProvider invalidDataProvider
     */
    public function testInvalid($input): void
    {
        $filter = $this->makeInstance();

        $this->assertFalse($filter->check($input));
    }

    /**
     * @param mixed $input
     *
     * @dataProvider invalidArgumentDataProvider
     */
    public function testInvalidArgument($input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $filter = $this->makeInstance();

        $filter->check($input);
    }

    final public function validDataProvider(): array
    {
        return $this->prepareDataProvider($this->validData());
    }

    /**
     * @return mixed[][]
     */
    final public function invalidDataProvider(): array
    {
        return $this->prepareDataProvider($this->invalidData());
    }

    /**
     * @return mixed[][]
     */
    final public function invalidArgumentDataProvider(): array
    {
        return $this->prepareDataProvider($this->invalidArgumentData());
    }

    private function prepareDataProvider(array $input): array
    {
        $output = [];

        foreach ($input as $value) {
            // Wrap for dataProvider
            $output[] = [$value];
        }

        return $output;
    }

    /**
     * Array of valid values
     *
     * @return mixed[]
     */
    abstract public function validData(): array;

    /**
     * Array of invalid values
     *
     * @return mixed[]
     */
    abstract public function invalidData(): array;

    /**
     * Array of invalid arguments
     *
     * @return mixed[]
     */
    abstract public function invalidArgumentData(): array;

    abstract protected function makeInstance(): DefinitionRuleInterface;
}
