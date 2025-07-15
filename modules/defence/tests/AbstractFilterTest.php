<?php

declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\Filter\FilterInterface;

abstract class AbstractFilterTest extends AbstractDefenceTest
{
    /**
     * @param mixed $input
     *
     * @dataProvider passDataProvider
     */
    public function testPass($input): void
    {
        $filter = $this->makeInstance();

        $filter->apply($input);

        self::assertTrue(true);
    }

    /**
     * @param mixed $input
     *
     * @dataProvider passDataUnchangedProvider
     */
    public function testPassUnchanged($input): void
    {
        $filter = $this->makeInstance();

        self::assertEquals($input, $filter->apply($input));
    }

    /**
     * @param mixed $input
     * @param mixed $output
     *
     * @dataProvider sanitizeDataProvider
     */
    public function testSanitize($input, $output): void
    {
        $filter = $this->makeInstance();

        $this->assertEquals($output, $filter->apply($input));
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

        $filter->apply($input);
    }

    final public function passDataProvider(): array
    {
        $output = [];

        foreach ($this->passData() as $value) {
            // Wrap for dataProvider
            $output[] = [$value];
        }

        return $output;
    }

    final public function passDataUnchangedProvider(): array
    {
        $output = [];

        foreach ($this->passDataUnchanged() as $value) {
            // Wrap for dataProvider
            $output[] = [$value];
        }

        return $output;
    }

    /**
     * @return mixed[][]
     */
    final public function sanitizeDataProvider(): array
    {
        $output = [];

        foreach ($this->sanitizeData() as $in => $out) {
            // Wrap for dataProvider
            $output[] = [$in, $out];
        }

        return $output;
    }

    /**
     * @return mixed[][]
     */
    final public function invalidArgumentDataProvider(): array
    {
        $output = [];

        foreach ($this->invalidData() as $value) {
            // Wrap for dataProvider
            $output[] = [$value];
        }

        return $output;
    }

    /**
     * @return mixed[]
     */
    abstract public function passData(): array;

    public function passDataUnchanged(): array
    {
        // Same data by default
        return $this->passData();
    }

    /**
     * Key => value pairs
     *
     * @return array
     */
    abstract public function sanitizeData(): array;

    /**
     * @return mixed[]
     */
    abstract public function invalidData(): array;

    abstract protected function makeInstance(): FilterInterface;
}
