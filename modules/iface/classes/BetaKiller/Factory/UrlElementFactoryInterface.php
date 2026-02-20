<?php

declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Url\UrlElementInterface;

interface UrlElementFactoryInterface
{
    /**
     * @param string $tagName
     * @param array  $config
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function createFrom(string $tagName, array $config): UrlElementInterface;
}
