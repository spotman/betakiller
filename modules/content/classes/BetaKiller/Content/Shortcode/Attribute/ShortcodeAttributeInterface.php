<?php
namespace BetaKiller\Content\Shortcode\Attribute;


interface ShortcodeAttributeInterface
{
    public function getName(): string;

    public function isValueAvailable(string $value): bool;

    public function isOptional(): bool;
}
