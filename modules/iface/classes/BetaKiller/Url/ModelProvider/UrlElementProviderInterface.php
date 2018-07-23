<?php
namespace BetaKiller\Url\ModelProvider;

interface UrlElementProviderInterface
{
    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getAll(): array;
}
