<?php
declare(strict_types=1);

namespace Spotman\Defence\Test;

use Spotman\Defence\ArgumentsFacade;
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
            ->composite('a')->endComposite()
            ->composite('b')->optional()->endComposite();

        $this->checkCompositeDefinition($def);
    }

    public function testCompositeArray(): void
    {
        $def = $this->definitionBuilder()
            ->compositeArray('a')->endComposite()
            ->compositeArray('b')->optional()->endComposite();

        $this->checkCompositeDefinition($def);
    }

    private function checkSingleDefinition(DefinitionBuilderInterface $def): void
    {
        list($required, $optional, $optionalDefault) = $def->getArguments();

        $this->assertEquals('a', $required->getName());
        $this->assertEquals('b', $optional->getName());
        $this->assertEquals('c', $optionalDefault->getName());

        $this->assertFalse($required->isOptional());
        $this->assertTrue($optional->isOptional());
        $this->assertTrue($optionalDefault->isOptional());

        $this->assertEquals(null, $required->getDefaultValue());
        $this->assertEquals(null, $optional->getDefaultValue());
        $this->assertNotEquals(null, $optionalDefault->getDefaultValue());
    }

    private function checkCompositeDefinition(DefinitionBuilderInterface $def): void
    {
        list($required, $optional) = $def->getArguments();

        $this->assertEquals('a', $required->getName());
        $this->assertEquals('b', $optional->getName());

        $this->assertFalse($required->isOptional());
        $this->assertTrue($optional->isOptional());

        $this->assertEquals([], $required->getDefaultValue());
        $this->assertEquals([], $optional->getDefaultValue());
    }

    private function definitionBuilder(): DefinitionBuilderInterface
    {
        return new DefinitionBuilder();
    }
}
