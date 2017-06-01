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

    private function isAdminModel(IFaceModelInterface $model)
    {
        return $model->getZoneName() === IFaceZone::ADMIN_ZONE;
    }

    private function isPublicModel(IFaceModelInterface $model)
    {
        return $model->getZoneName() === IFaceZone::PUBLIC_ZONE;
    }
}
