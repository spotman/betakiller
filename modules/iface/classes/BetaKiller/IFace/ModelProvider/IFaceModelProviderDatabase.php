<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Repository\IFaceRepository;

class IFaceModelProviderDatabase implements IFaceModelProviderInterface
{
    /**
     * @var \BetaKiller\Repository\IFaceRepository
     */
    private $repository;

    /**
     * IFaceModelProviderDatabase constructor.
     *
     * @param \BetaKiller\Repository\IFaceRepository $repository
     */
    public function __construct(IFaceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getAll(): array
    {
        $models = [];

        foreach ($this->repository->getFullTree() as $item) {
            $models[] = $item;
        }

        return $models;
    }
}
