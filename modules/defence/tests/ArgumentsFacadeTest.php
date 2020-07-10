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
     * @param                                             $input
     *
     * @dataProvider validDataProvider
     */
    public function testValid(DefinitionBuilderInterface $def, $input): void
    {
        $output = $this->getFacade()->prepareArguments($input, $def);

        $this->assertEquals($input, $output->getAll());
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
     * @param                                             $output
     *
     * @dataProvider optionalDataProvider
     */
    public function testOptional(DefinitionBuilderInterface $def, $output): void
    {
        $result = $this->getFacade()->prepareArguments([], $def);

        $this->assertEquals($output, $result->getAll());
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
            'b' => true,
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
            'bool' => [
                $this->def()->bool('a'),
                ['a' => true],
            ],

            'bool + nullable' => [
                $this->def()->bool('a')->nullable(),
                ['a' => null],
            ],

            // Int
            'int'           => [
                $this->def()->int('a'),
                ['a' => 12345],
            ],

            'int + nullable' => [
                $this->def()->int('a')->nullable(),
                ['a' => null],
            ],

            // Float
            'float'           => [
                $this->def()->float('a'),
                ['a' => 123.45],
            ],

            'float + nullable' => [
                $this->def()->float('a')->nullable(),
                ['a' => null],
            ],

            // String
            'string'       => [
                $this->def()->string('a'),
                ['a' => 'qwerty'],
            ],

            'string + nullable' => [
                $this->def()->string('a')->nullable(),
                ['a' => null],
            ],

            // String
            'text'       => [
                $this->def()->text('a'),
                ['a' => "qwerty\r\nasdfgh\nzxcvbn\rpoiuytre"],
            ],

            'text + nullable' => [
                $this->def()->text('a')->nullable(),
                ['a' => null],
            ],

            // Int array
            'int array'       => [
                $this->def()->intArray('a'),
                ['a' => [123, 456]],
            ],

            // String array
            'string array'    => [
                $this->def()->stringArray('a'),
                ['a' => ['asd', 'qwe']],
            ],

            // Composite
            'compositeStart'       => [
                $this->def()->compositeStart('a')->int('b')->string('c')->compositeEnd(),
                ['a' => ['b' => 123, 'c' => 'qwe']],
            ],

            'compositeStart + nullable' => [
                $this->def()->compositeStart('a')->nullable()->int('b')->compositeEnd(),
                ['a' => null],
            ],

            // Composite array
            'compositeStart array'    => [
                $this->def()->compositeArrayStart('a')->int('b')->string('c'),
                [
                    'a' => [
                        ['b' => 123, 'c' => 'qwe'],
                        ['b' => 456, 'c' => 'asd'],
                    ],
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
            'boolean' => [
                $this->def()->bool('a')->optional(),
                [],
            ],

            'boolean + default' => [
                $this->def()->bool('a')->optional()->default(true),
                ['a' => true],
            ],

            'integer' => [
                $this->def()->int('a')->optional(),
                [],
            ],

            'integer + default' => [
                $this->def()->int('a')->optional()->default(10),
                ['a' => 10],
            ],

            'float' => [
                $this->def()->float('a')->optional(),
                [],
            ],

            'float + default' => [
                $this->def()->float('a')->optional()->default(10.2),
                ['a' => 10.2],
            ],

            'string' => [
                $this->def()->string('a')->optional(),
                [],
            ],

            'string + default' => [
                $this->def()->string('a')->optional()->default('qwerty'),
                ['a' => 'qwerty'],
            ],

            'intArray' => [
                $this->def()->intArray('a')->optional(),
                [],
            ],

            'intArray + default' => [
                $this->def()->intArray('a')->optional()->default([1, 4]),
                ['a' => [1, 4]],
            ],

            'stringArray' => [
                $this->def()->stringArray('a')->optional(),
                [],
            ],

            'stringArray + default' => [
                $this->def()->stringArray('a')->optional()->default(['asd', 'qwe']),
                ['a' => ['asd', 'qwe']],
            ],

            'compositeStart' => [
                $this->def()->compositeStart('a')->optional()->int('b')->string('c'),
                [],
            ],

            'compositeArray' => [
                $this->def()->compositeArrayStart('a')->optional()->int('b')->string('c'),
                [],
            ],
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
            [$this->def()->intArray('a'), [1.2, -2.5]],
            [$this->def()->intArray('a'), [null]],
            // Float array
            [$this->def()->intArray('a'), ['asd', 'qwe']],
            [$this->def()->intArray('a'), [true, false]],
            [$this->def()->intArray('a'), [null]],
            // String array
            [$this->def()->stringArray('a'), [123, 456]],
            [$this->def()->stringArray('a'), [true, false, null]],
            [$this->def()->stringArray('a'), [null]],
            // Composite
            [$this->def()->compositeStart('a')->int('b')->compositeEnd(), [123, 456]],
            [$this->def()->compositeStart('a')->int('b')->compositeEnd(), [false, true]],
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
            [$this->def()->compositeStart('a')->int('b')->positive(), ['b' => -123]],
            // Int array
            [$this->def()->intArray('a')->lengthBetween(2, 5), [1]],
            [$this->def()->intArray('a')->lengthBetween(1, 3), [1, 2, 3, 4]],
            // Int array
            [$this->def()->floatArray('a')->lengthBetween(2, 5), [1.0]],
            [$this->def()->floatArray('a')->lengthBetween(1, 3), [1.2, 2.3, 3.4, 4.5]],
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
