<?php

declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Url\ModelProvider\ActionUrlElement;
use BetaKiller\Url\ModelProvider\DummyUrlElement;
use BetaKiller\Url\ModelProvider\IFaceUrlElement;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInterface;

readonly class UrlElementFactory implements UrlElementFactoryInterface
{
    public function createFrom(string $tagName, array $config): UrlElementInterface
    {
        return match ($tagName) {
            IFaceUrlElement::getXmlTagName() => IFaceUrlElement::factory($config),
            ActionUrlElement::getXmlTagName() => ActionUrlElement::factory($config),
            DummyUrlElement::getXmlTagName() => DummyUrlElement::factory($config),
            default => throw new UrlElementException('Unknown XML tag <:tag> in URL elements config', [
                ':tag' => $tagName,
            ]),
        };
    }
}
