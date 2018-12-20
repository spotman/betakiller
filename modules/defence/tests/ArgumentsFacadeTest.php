<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\ArgumentsFacade;
use Spotman\Defence\DefinitionBuilder;
use Spotman\Defence\DefinitionBuilderInterface;

class ArgumentsFacadeTest extends AbstractDefenceTest
{
    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $def
     * @param                                             $value
     *
     * @dataProvider validDataProvider
     */
    public function testValid(DefinitionBuilderInterface $def, $value): void
    {
        $input = [
            'a' => $value,
        ];

        $output = $this->getFacade()->prepareArguments($input, $def);

        $this->assertEquals($output->getAll(), $input);
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $def
     * @param                                             $input
     * @param                                             $output
     *
     * @dataProvider filteredDataProvider
     */
    public function testFiltered(DefinitionBuilderInterface $def, $input, $output): void
    {
        $result = $this->getFacade()->prepareArguments(['a' => $input], $def);

        $this->assertEquals($result->getAll(), ['a' => $output]);
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $def
     * @param                                             $value
     *
     * @dataProvider optionalDataProvider
     */
    public function testOptional(DefinitionBuilderInterface $def, $value): void
    {
        $result = $this->getFacade()->prepareArguments([], $def);

        $this->assertEquals(['a' => $value], $result->getAll());
    }

    public function testUnknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $def = $this->def()->bool('a');

        $this->getFacade()->prepareArguments(['a' => true, 'b' => false], $def);
    }

    public function testIndexed(): void
    {
        $def = $this->def()
            ->string('s')
            ->int('i')
            ->bool('b');

        $expected = [
            's' => 'asd',
            'i' => 100,
            'b' => true
        ];

        $arguments = $this->getFacade()->prepareArguments(\array_values($expected), $def);

        $this->assertEquals($expected, $arguments->getAll());
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $def
     * @param                                             $value
     *
     * @dataProvider rulesDataProvider
     */
    public function testRules(DefinitionBuilderInterface $def, $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getFacade()->prepareArguments(['a' => $value], $def);
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $def
     * @param                                             $value
     *
     * @dataProvider invalidDataProvider
     */
    public function testInvalid(DefinitionBuilderInterface $def, $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getFacade()->prepareArguments(['a' => $value], $def);
    }

    public function validDataProvider(): array
    {
        return [
            // Bool
            [$this->def()->bool('a'), true],

            // Int
            [$this->def()->int('a'), 12345],

            // String
            [$this->def()->string('a'), 'qwerty'],

            // Int array
            [$this->def()->intArray('a'), [123, 456]],

            // String array
            [$this->def()->stringArray('a'), ['asd', 'qwe']],

            // Composite
            [
                $this->def()->composite('a')->int('b')->string('c'),
                ['b' => 123, 'c' => 'qwe'],
            ],

            // Composite array
            [
                $this->def()->compositeArray('a')->int('b')->string('c'),
                [
                    ['b' => 123, 'c' => 'qwe'],
                    ['b' => 456, 'c' => 'asd'],
                ],
            ],
        ];
    }

    public function filteredDataProvider(): array
    {
        return [
            // TODO Cases for XSS and SQL injections
            // String
            [$this->def()->string('a')->lowercase(), 'QWERTY', 'qwerty'],
            [$this->def()->string('a')->uppercase(), 'qwerty', 'QWERTY'],
        ];
    }

    public function optionalDataProvider(): array
    {
        return [
            // Bool
            [$this->def()->bool('a')->optional(), null],
            [$this->def()->bool('a')->optional()->default(true), true],
            // Int
            [$this->def()->int('a')->optional(), null],
            [$this->def()->int('a')->optional()->default(10), 10],
            // String
            [$this->def()->string('a')->optional(), null],
            [$this->def()->string('a')->optional()->default('qwerty'), 'qwerty'],
        ];
    }

    public function invalidDataProvider(): array
    {
        return [
            // Bool
            [$this->def()->bool('a'), 'asd'],
            [$this->def()->bool('a'), 12345],
            [$this->def()->bool('a'), new \stdClass],
            // Int
            [$this->def()->int('a'), 'asd'],
            [$this->def()->int('a'), false],
            [$this->def()->int('a'), new \stdClass],
            // String
            [$this->def()->string('a'), false],
            [$this->def()->string('a'), 12345],
            [$this->def()->string('a'), new \stdClass],
            // Int array
            [$this->def()->intArray('a'), ['asd', 'qwe']],
            [$this->def()->intArray('a'), [true, false]],
            // String array
            [$this->def()->stringArray('a'), [123, 456]],
            [$this->def()->stringArray('a'), [true, false]],
            // Composite
            [$this->def()->composite('a')->int('b'), [123, 456]],
            [$this->def()->composite('a')->int('b'), [false, true]],
        ];
    }

    public function rulesDataProvider(): array
    {
        return [
            // Int
            [$this->def()->int('a')->positive(), 0],
            [$this->def()->int('a')->positive(), -100],
            [$this->def()->int('a')->whitelist([100, 200, 300]), 500],
            // String
            [$this->def()->string('a')->whitelist(['asd', 'qwe']), 'zxc'],
            // Composite
            [$this->def()->composite('a')->int('b')->positive(), ['b' => -123]],
            // Int array
            [$this->def()->intArray('a')->lengthBetween(2, 5), [1]],
            [$this->def()->intArray('a')->lengthBetween(1, 3), [1, 2, 3, 4]],
            // String array
            [$this->def()->stringArray('a')->lengthBetween(2, 5), ['asd']],
            [$this->def()->stringArray('a')->lengthBetween(1, 3), ['asd', 'qwe', 'asd', 'qwe']],

        ];
    }

    private function def(): DefinitionBuilderInterface
    {
        return new DefinitionBuilder();
    }

    private function getFacade(): ArgumentsFacade
    {
        return new ArgumentsFacade();
    }
}
