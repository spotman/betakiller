<?php
namespace BetaKiller\IFace\ModelProvider;

interface UrlElementProviderInterface
{
    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getAll(): array;
}
