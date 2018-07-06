<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function detectDedicatedObject(UrlElement $element): UrlElementInterface
    {
        switch (true) {
            case $element->isTypeIFace():
                return $element->getIFaceModel();

            case $element->isTypeWebHook():
                return $element->getWebHookModel();

            default:
                throw new IFaceException('Unknown type of URL element for codename :codename', [
                    ':codename' => $element->getCodename(),
                ]);
        }
    }
}
