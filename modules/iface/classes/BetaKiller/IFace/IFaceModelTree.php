<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;

class IFaceModelTree
{
    /**
     * @var string[]
     */
    private $entityLinkedCodenameCache;

    /**
     * @var \BetaKiller\IFace\IFaceModelInterface[]
     */
    private $items = [];

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     * @param bool|null                             $warnIfExists
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function add(IFaceModelInterface $model, ?bool $warnIfExists = null): void
    {
        $codename = $model->getCodename();

        if ($warnIfExists && isset($this->items[$codename])) {
            throw new IFaceException('IFace :codename already exists in the tree', [':codename' => $codename]);
        }

        $parentCodename = $model->getParentCodename();

        if ($parentCodename) {
            // Store parent for future usage
            $parent = $this->getByCodename($parentCodename);
            $model->setParent($parent);
        }

        $this->items[$codename] = $model;
    }

    /**
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function validate(): void
    {
        $this->validateBranch();
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface|null $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function validateBranch(IFaceModelInterface $parent = null): void
    {
        $children = $this->getChilds($parent);

        $this->validateLayer($children);

        foreach ($children as $child) {
            $this->validateModel($child);
            $this->validateBranch($child);
        }
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function validateLayer(array $models): void
    {
        $dynamicCounter = 0;

        foreach ($models as $model) {
            if ($model->hasDynamicUrl() || $model->hasTreeBehaviour()) {
                $dynamicCounter++;
            }
        }

        if ($dynamicCounter > 1) {
            throw new IFaceException('Layer must have only one IFace with dynamic dispatching');
        }
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function validateModel(IFaceModelInterface $model): void
    {
        $codename = $model->getCodename();

        if (!$model->getLabel()) {
            throw new IFaceException('Label is missing for IFace :codename', [':codename' => $codename]);
        }

        if (!$model->getZoneName()) {
            throw new IFaceException('IFace zone is missing for IFace :codename', [':codename' => $codename]);
        }
    }

    /**
     * Returns default iface model
     *
     * @return \BetaKiller\IFace\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDefault(): IFaceModelInterface
    {
        foreach ($this->items as $item) {
            if ($item->isDefault()) {
                return $item;
            }
        }

        throw new IFaceException('No default IFace found');
    }

    /**
     * Returns list of root elements
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRoot(): array
    {
        $root = $this->getChilds();

        if (!$root) {
            throw new IFaceException('No root IFaces found, define them first');
        }

        return $root;
    }

    /**
     * Returns list of child nodes
     *
     * @param \BetaKiller\IFace\IFaceModelInterface $parent
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getChildren(IFaceModelInterface $parent): array
    {
        return $this->getChilds($parent);
    }

    /**
     * Returns list of child nodes of $parentModel (or root nodes if none provided)
     *
     * @param \BetaKiller\IFace\IFaceModelInterface|null $parentModel
     *
     * @return IFaceModelInterface[]
     */
    private function getChilds(IFaceModelInterface $parentModel = null): array
    {
        $parentCodename = $parentModel ? $parentModel->getCodename() : null;

        $models = [];

        foreach ($this->items as $model) {
            if ($model->getParentCodename() !== $parentCodename) {
                continue;
            }

            $models[] = $model;
        }

        return $models;
    }

    /**
     * Returns parent iface model or null if none was found
     *
     * @param \BetaKiller\IFace\IFaceModelInterface $child
     *
     * @return \BetaKiller\IFace\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getParent(IFaceModelInterface $child): ?IFaceModelInterface
    {
        $parentCodename = $child->getParentCodename();

        return $parentCodename
            ? $this->getByCodename($parentCodename)
            : null;
    }

    /**
     * Returns iface model by codename or throws an exception if nothing was found
     *
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByCodename(string $codename): IFaceModelInterface
    {
        if (!isset($this->items[$codename])) {
            throw new IFaceException('No IFace found by codename :codename', [':codename' => $codename]);
        }

        return $this->items[$codename];
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getByActionAndZone(string $action, string $zone): array
    {
        $output = [];

        foreach ($this->items as $model) {
            if ($model->getEntityActionName() !== $action) {
                continue;
            }

            if ($model->getZoneName() !== $zone) {
                continue;
            }

            $output[] = $model;
        }

        return $output;
    }

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
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

        $model = $this->findByEntityActionAndZone($entity, $action, $zone);

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
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceModelInterface|null
     */
    private function findByEntityActionAndZone(
        DispatchableEntityInterface $entity,
        string $entityAction,
        string $zone
    ): ?IFaceModelInterface {
        foreach ($this->items as $model) {
            if ($model->getEntityModelName() !== $entity->getModelName()) {
                continue;
            }

            if ($model->getEntityActionName() !== $entityAction) {
                continue;
            }

            if ($model->getZoneName() !== $zone) {
                continue;
            }

            return $model;
        }

        return null;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \ArrayIterator|\BetaKiller\IFace\IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getReverseBreadcrumbsIterator(IFaceModelInterface $model): \ArrayIterator
    {
        $stack   = [];
        $current = $model;

        do {
            $stack[] = $current;

            $current = $this->getParent($current);
        } while ($current);

        return new \ArrayIterator($stack);
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return IFaceModelRecursiveIterator|IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursiveIterator(IFaceModelInterface $parent = null)
    {
        return new IFaceModelRecursiveIterator($parent, $this);
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursivePublicIterator(IFaceModelInterface $parent = null)
    {
        return $this->getRecursiveFilterIterator(function (IFaceModelInterface $model) {
            return $this->isPublicModel($model);
        }, $parent);
    }

    /**
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
