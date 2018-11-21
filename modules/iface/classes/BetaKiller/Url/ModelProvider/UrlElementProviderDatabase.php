<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Model\UrlElement;
use BetaKiller\Repository\UrlElementRepository;
use BetaKiller\Url\UrlElementInterface;

class UrlElementProviderDatabase implements UrlElementProviderInterface
{
    /**
     * @var \BetaKiller\Repository\UrlElementRepository
     */
    private $repository;

    /**
     * UrlElementProviderDatabase constructor.
     *
     * @param \BetaKiller\Repository\UrlElementRepository $repository
     */
    public function __construct(UrlElementRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAll(): array
    {
        $models = [];

        foreach ($this->repository->getFullTree() as $item) {

            $models[] = $this->detectDedicatedObject($item);
        }

        return $models;
    }

    /**
     * @param \BetaKiller\Model\UrlElement $element
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function detectDedicatedObject(UrlElement $element): UrlElementInterface
    {
        return $element->getDedicatedObject();
    }
}
