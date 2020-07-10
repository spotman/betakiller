<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\DefinitionBuilder;
use Spotman\Defence\DefinitionBuilderInterface;

class DefinitionBuilderTest extends AbstractDefenceTest
{
    public function testBool(): void
    {
        $def = $this->definitionBuilder()
            ->bool('a')
            ->bool('b')->optional()
            ->bool('c')->optional()->default(true);

        $this->checkSingleDefinition($def);
    }

    public function testInteger(): void
    {
        $def = $this->definitionBuilder()
            ->int('a')
            ->int('b')->optional()
            ->int('c')->optional()->default(10);

        $this->checkSingleDefinition($def);
    }

    public function testFloat(): void
    {
        $def = $this->definitionBuilder()
            ->int('a')
            ->int('b')->optional()
            ->int('c')->optional()->default(10.5);

        $this->checkSingleDefinition($def);
    }

    public function testString(): void
    {
        $def = $this->definitionBuilder()
            // String
            ->string('a')
            ->string('b')->optional()
            ->string('c')->optional()->default('asd');

        $this->checkSingleDefinition($def);
    }

    public function testEmail(): void
    {
        $def = $this->definitionBuilder()
            // String
            ->email('a')
            ->email('b')->optional()
            ->email('c')->optional()->default('asd@mail.com');

        $this->checkSingleDefinition($def);
    }

    public function testText(): void
    {
        $def = $this->definitionBuilder()
            ->text('a')
            ->text('b')->optional()
            ->text('c')->optional()->default('qwerty');

        $this->checkSingleDefinition($def);
    }

    public function testHtml(): void
    {
        $def = $this->definitionBuilder()
            ->html('a')
            ->html('b')->optional()
            ->html('c')->optional()->default('qwerty<br />asd');

        $this->checkSingleDefinition($def);
    }

    public function testIntArray(): void
    {
        $def = $this->definitionBuilder()
            ->intArray('a')
            ->intArray('b')->optional()
            ->intArray('c')->optional()->default([1, 2]);

        $this->checkSingleDefinition($def);
    }

    public function testFloatArray(): void
    {
        $def = $this->definitionBuilder()
            ->floatArray('a')
            ->floatArray('b')->optional()
            ->floatArray('c')->optional()->default([1.2, 2.3]);

        $this->checkSingleDefinition($def);
    }

    public function testStringArray(): void
    {
        $def = $this->definitionBuilder()
            ->stringArray('a')
            ->stringArray('b')->optional()
            ->stringArray('c')->optional()->default(['asd', 'def']);

        $this->checkSingleDefinition($def);
    }

    public function testComposite(): void
    {
        $def = $this->definitionBuilder()
            ->compositeStart('a')->compositeEnd()
            ->compositeStart('b')->optional()->compositeEnd();

        $this->checkCompositeDefinition($def);
    }

    public function testCompositeArray(): void
    {
        $def = $this->definitionBuilder()
            ->compositeArrayStart('a')->compositeEnd()
            ->compositeArrayStart('b')->optional()->compositeEnd();

        $this->checkCompositeDefinition($def);
    }

    public function testBoolNullable(): void
    {
        $this->definitionBuilder()
            ->bool('a')->nullable();

        self::assertTrue(true);
    }

    public function testIntNullable(): void
    {
        $this->definitionBuilder()
            ->int('a')->nullable();

        self::assertTrue(true);
    }

    public function testFloatNullable(): void
    {
        $this->definitionBuilder()
            ->float('a')->nullable();

        self::assertTrue(true);
    }

    public function testStringNullable(): void
    {
        $this->definitionBuilder()
            ->string('a')->nullable();

        self::assertTrue(true);
    }

    public function testCompositeNullable(): void
    {
        $this->definitionBuilder()
            ->compositeStart('a')->nullable()
            ->int('b')
            ->compositeEnd();

        self::assertTrue(true);
    }

    public function testNonNullableIntArray(): void
    {
        $this->expectException(\DomainException::class);

        $this->definitionBuilder()
            ->intArray('a')->nullable();
    }

    public function testNonNullableFloatArray(): void
    {
        $this->expectException(\DomainException::class);

        $this->definitionBuilder()
            ->floatArray('a')->nullable();
    }

    public function testNonNullableStringArray(): void
    {
        $this->expectException(\DomainException::class);

        $this->definitionBuilder()
            ->stringArray('a')->nullable();
    }

    public function testNonNullableCompositeArray(): void
    {
        $this->expectException(\DomainException::class);

        $this->definitionBuilder()
            ->compositeArrayStart('a')->nullable()
            ->int('a')
            ->compositeEnd();
    }

    private function checkSingleDefinition(DefinitionBuilderInterface $def, $default = null): void
    {
        [$required, $optional, $optionalDefault] = $def->getArguments();

        $this->assertEquals('a', $required->getName());
        $this->assertEquals('b', $optional->getName());
        $this->assertEquals('c', $optionalDefault->getName());

        $this->assertFalse($required->isOptional());
        $this->assertTrue($optional->isOptional());
        $this->assertTrue($optionalDefault->isOptional());

        $this->assertEquals($default, $required->getDefaultValue());
        $this->assertEquals($default, $optional->getDefaultValue());
        $this->assertNotEquals($default, $optionalDefault->getDefaultValue());
    }

    private function checkCompositeDefinition(DefinitionBuilderInterface $def): void
    {
        [$required, $optional] = $def->getArguments();

        $this->assertEquals('a', $required->getName());
        $this->assertEquals('b', $optional->getName());

        $this->assertFalse($required->isOptional());
        $this->assertTrue($optional->isOptional());

        $this->assertEquals(false, $required->hasDefaultValue());
        $this->assertEquals(false, $optional->hasDefaultValue());

        $this->assertEquals(null, $required->getDefaultValue());
        $this->assertEquals(null, $optional->getDefaultValue());
    }

    private function definitionBuilder(): DefinitionBuilderInterface
    {
        return new DefinitionBuilder();
    }
}
