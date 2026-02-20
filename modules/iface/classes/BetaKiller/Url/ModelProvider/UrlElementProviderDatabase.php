<?php

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Model\UrlElement;
use BetaKiller\Repository\UrlElementRepository;

/**
 * @deprecated
 */
readonly class UrlElementProviderDatabase implements UrlElementProviderInterface
{
    /**
     * UrlElementProviderDatabase constructor.
     *
     * @param \BetaKiller\Repository\UrlElementRepository $repository
     */
    public function __construct(private UrlElementRepository $repository)
    {
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAll(): array
    {
        $models = [];

        /** @var UrlElement $item */
        foreach ($this->repository->getFullTree() as $item) {
            $models[] = $item->getDedicatedObject();
        }

        return $models;
    }
}
