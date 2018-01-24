<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;

class IFaceModelTree
{
    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate
     */
    protected $modelProvider;

    /**
     * @var string[]
     */
    private $entityLinkedCodenameCache;

    public function __construct(IFaceModelProviderAggregate $modelProvider)
    {
        $this->modelProvider = $modelProvider;
    }

    /**
     * @return \BetaKiller\IFace\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDefault(): IFaceModelInterface
    {
        return $this->modelProvider->getDefault();
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
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByCodename(string $codename): IFaceModelInterface
    {
        return $this->modelProvider->getByCodename($codename);
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
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string                                        $zone
     *
     * @return \BetaKiller\IFace\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByEntityActionAndZone(
        DispatchableEntityInterface $entity,
        string $action,
        string $zone
    ): IFaceModelInterface {
        $key = implode('.', [$entity->getModelName(), $action, $zone]);

        $model = $this->getLinkedIFaceFromCache($key);

        if ($model) {
            return $model;
        }

        $model = $this->modelProvider->getByEntityActionAndZone($entity, $action, $zone);

        if (!$model) {
            throw new IFaceException('No IFace found for :entity.:action entity in :zone zone', [
                ':entity' => $entity->getModelName(),
                ':action' => $action,
                ':zone'   => $zone,
            ]);
        }

        $this->storeLinkedIFaceInCache($key, $model);

        return $model;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \ArrayIterator|\BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getReverseBreadcrumbsIterator(IFaceModelInterface $model): \ArrayIterator
    {
        $stack   = [];
        $current = $model;

        do {
            $stack[] = $current;

            $parent  = $this->getParent($current);
            $current = $parent;
        } while ($parent);

        return new \ArrayIterator($stack);
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

    /**
     * @param string $key
     *
     * @return \BetaKiller\IFace\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getLinkedIFaceFromCache(string $key): ?IFaceModelInterface
    {
        if (!isset($this->entityLinkedCodenameCache[$key])) {
            return null;
        }

        $codename = $this->entityLinkedCodenameCache[$key];

        return $this->getByCodename($codename);
    }

    /**
     * @param string                                $key
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     */
    private function storeLinkedIFaceInCache(string $key, IFaceModelInterface $model): void
    {
        $this->entityLinkedCodenameCache[$key] = $model->getCodename();
    }
}
