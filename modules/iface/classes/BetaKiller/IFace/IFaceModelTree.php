<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;
use BetaKiller\Model\IFaceZone;

class IFaceModelTree
{
    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate
     */
    protected $modelProvider;

    public function __construct(IFaceModelProviderAggregate $modelProvider)
    {
        $this->modelProvider = $modelProvider;
    }

    /**
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRoot(): array
    {
        return $this->modelProvider->getRoot();
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $parent
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getChildren(IFaceModelInterface $parent): array
    {
        return $this->modelProvider->getChildren($parent);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $child
     *
     * @return \BetaKiller\IFace\IFaceModelInterface|null
     */
    public function getParent(IFaceModelInterface $child): ?IFaceModelInterface
    {
        return $this->modelProvider->getParent($child);
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByActionAndZone(string $action, string $zone): array
    {
        return $this->modelProvider->getByActionAndZone($action, $zone);
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return IFaceModelRecursiveIterator|IFaceModelInterface[]
     */
    public function getRecursiveIterator(IFaceModelInterface $parent = null)
    {
        return new IFaceModelRecursiveIterator($parent, $this->modelProvider);
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    public function getRecursivePublicIterator(IFaceModelInterface $parent = null)
    {
        return $this->getRecursiveFilterIterator(function (IFaceModelInterface $model) {
            return $this->isPublicModel($model);
        }, $parent);
    }

    /**
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    public function getRecursiveSitemapIterator()
    {
        return $this->getRecursiveFilterIterator(function (IFaceModelInterface $model) {
            return !$model->hideInSiteMap() && $this->isPublicModel($model);
        });
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    public function getRecursiveAdminIterator(IFaceModelInterface $parent = null)
    {
        return $this->getRecursiveFilterIterator(function (IFaceModelInterface $model) {
            return $this->isAdminModel($model);
        }, $parent);
    }

    /**
     * @param callable                 $callback
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    protected function getRecursiveFilterIterator(callable $callback, IFaceModelInterface $parent = null)
    {
        $iterator = $this->getRecursiveIterator($parent);

        $filter = new \RecursiveCallbackFilterIterator($iterator, $callback);

        return new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return bool
     */
    private function isAdminModel(IFaceModelInterface $model): bool
    {
        return $model->getZoneName() === IFaceZone::ADMIN_ZONE;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return bool
     */
    private function isPublicModel(IFaceModelInterface $model): bool
    {
        return $model->getZoneName() === IFaceZone::PUBLIC_ZONE;
    }
}
