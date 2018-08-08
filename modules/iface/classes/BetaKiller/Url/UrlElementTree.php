<?php
namespace BetaKiller\Url;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;

class UrlElementTree implements UrlElementTreeInterface
{
    /**
     * @var string[]
     */
    private $entityLinkedCodenameCache;

    /**
     * @var \BetaKiller\Url\UrlElementInterface[]
     */
    private $items = [];

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     * @param bool|null                           $warnIfExists
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function add(UrlElementInterface $model, ?bool $warnIfExists = null): void
    {
        $codename = $model->getCodename();

        if ($warnIfExists && isset($this->items[$codename])) {
            throw new IFaceException('IFace :codename already exists in the tree', [':codename' => $codename]);
        }

        $this->items[$codename] = $model;
    }

    /**
     * Returns true if Url element with provided codename exists
     *
     * @param string $codename
     *
     * @return bool
     */
    public function has(string $codename): bool
    {
        return isset($this->items[$codename]);
    }

    /**
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function validate(): void
    {
        $this->validateBranch();
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function validateBranch(UrlElementInterface $parent = null): void
    {
        $children = $this->getChilds($parent);

        $this->validateLayer($children);

        foreach ($children as $child) {
            $this->validateModel($child);
            $this->validateBranch($child);
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[] $models
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function validateLayer(array $models): void
    {
        $dynamicCounter = 0;
        $uris           = [];

        foreach ($models as $model) {
            if ($model instanceof IFaceModelInterface && ($model->hasDynamicUrl() || $model->hasTreeBehaviour())) {
                $dynamicCounter++;
            }

            $uri = $model->getUri();

            if (\in_array($uri, $uris, true)) {
                throw new IFaceException('Duplicate URIs per layer are not allowed, codename is :name', [
                    ':name' => $model->getCodename(),
                ]);
            }

            $uris[] = $uri;
        }

        if ($dynamicCounter > 1) {
            throw new IFaceException('Layer must have only one IFace with dynamic dispatching');
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function validateModel(UrlElementInterface $model): void
    {
        if ($model instanceof IFaceModelInterface) {
            $this->validateIFaceModel($model);
        }
    }

    private function validateIFaceModel(IFaceModelInterface $model): void
    {
        $codename = $model->getCodename();

        if (!$model->getLabel()) {
            throw new IFaceException('Label is missing for IFace :codename', [':codename' => $codename]);
        }

        if (!$model->getZoneName()) {
            throw new IFaceException('IFace zone is missing for UrlElement :codename', [':codename' => $codename]);
        }
    }

    /**
     * Returns default iface model
     *
     * @return \BetaKiller\Url\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDefault(): IFaceModelInterface
    {
        foreach ($this->items as $item) {
            if ($item instanceof IFaceModelInterface && $item->isDefault()) {
                return $item;
            }
        }

        throw new IFaceException('No default IFace found');
    }

    /**
     * Returns list of root elements
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
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
     * @param \BetaKiller\Url\UrlElementInterface $parent
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getChildren(UrlElementInterface $parent): array
    {
        return $this->getChilds($parent);
    }

    /**
     * Returns list of child nodes of $parentModel (or root nodes if none provided)
     *
     * @param \BetaKiller\Url\UrlElementInterface|null $parentModel
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    private function getChilds(UrlElementInterface $parentModel = null): array
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
     * @param \BetaKiller\Url\UrlElementInterface $child
     *
     * @return \BetaKiller\Url\UrlElementInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getParent(UrlElementInterface $child): ?UrlElementInterface
    {
        $parentCodename = $child->getParentCodename();

        return $parentCodename
            ? $this->getByCodename($parentCodename)
            : null;
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $child
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getParentIFaceModel(IFaceModelInterface $child): ?IFaceModelInterface
    {
        $parent = $this->getParent($child);

        if (!$parent) {
            return null;
        }

        if (!$parent instanceof IFaceModelInterface) {
            throw new IFaceException('Can not get parent IFace for :codename coz it is not of IFace type', [
                ':codename' => $child->getCodename(),
            ]);
        }

        return $parent;
    }

    /**
     * Returns iface model by codename or throws an exception if nothing was found
     *
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByCodename(string $codename): UrlElementInterface
    {
        if (!isset($this->items[$codename])) {
            throw new IFaceException('No UrlElement found by codename :codename', [':codename' => $codename]);
        }

        return $this->items[$codename];
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getIFacesByActionAndZone(string $action, string $zone): array
    {
        $output = [];

        foreach ($this->items as $model) {
            if (!$model instanceof IFaceModelInterface) {
                continue;
            }

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
     * Search for UrlElement linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string                                        $zone
     *
     * @return \BetaKiller\Url\IFaceModelInterface
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
            throw new IFaceException('No UrlElement found for :entity.:action entity in :zone zone', [
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
     * @return \BetaKiller\Url\UrlElementInterface|null
     */
    private function findByEntityActionAndZone(
        DispatchableEntityInterface $entity,
        string $entityAction,
        string $zone
    ): ?IFaceModelInterface {
        foreach ($this->items as $model) {
            if (!$model instanceof IFaceModelInterface) {
                continue;
            }

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
     * Returns array of WebHookModelInterface instances linked to provided service
     *
     * @param string $serviceName
     *
     * @return \BetaKiller\Url\WebHookModelInterface[]
     */
    public function getWebHooksByServiceName(string $serviceName): array
    {
        return \array_filter($this->items, function (UrlElementInterface $urlElement) use ($serviceName) {
            return $urlElement instanceof WebHookModelInterface && $urlElement->getServiceName() === $serviceName;
        });
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \ArrayIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getReverseBreadcrumbsIterator(UrlElementInterface $model): \ArrayIterator
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
     * @param \BetaKiller\Url\UrlElementInterface|null                     $parent
     *
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null $filter
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursiveIteratorIterator(
        UrlElementInterface $parent = null,
        UrlElementFilterInterface $filter = null
    ): \RecursiveIteratorIterator {
        return new \RecursiveIteratorIterator(
            $this->getRecursiveIterator($parent, $filter),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|null                     $parent
     *
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null $filter
     *
     * @return \RecursiveIterator
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getRecursiveIterator(
        UrlElementInterface $parent = null,
        UrlElementFilterInterface $filter = null
    ): \RecursiveIterator {
        return new UrlElementTreeRecursiveIterator($this, $parent, $filter);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getPublicIFaceIterator(UrlElementInterface $parent = null): \RecursiveIteratorIterator
    {
        return $this->getRecursiveFilterIterator(function (UrlElementInterface $model) {
            return $this->isIFace($model) && $this->isPublicModel($model);
        }, $parent);
    }

    /**
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursiveSitemapIterator(): \RecursiveIteratorIterator
    {
        return $this->getRecursiveFilterIterator(function (UrlElementInterface $model) {
            return $this->isIFace($model) && !$model->isHiddenInSiteMap() && $this->isPublicModel($model);
        });
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getAdminIFaceIterator(UrlElementInterface $parent = null): \RecursiveIteratorIterator
    {
        return $this->getRecursiveFilterIterator(function (UrlElementInterface $model) {
            return $this->isIFace($model) && $this->isAdminModel($model);
        }, $parent);
    }

    /**
     * @param callable                 $callback
     * @param UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    protected function getRecursiveFilterIterator(callable $callback, UrlElementInterface $parent = null)
    {
        $filter = new \RecursiveCallbackFilterIterator(
            $this->getRecursiveIterator($parent),
            $callback
        );

        return new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
    }

    private function isIFace(UrlElementInterface $urlElement): bool
    {
        return $urlElement instanceof IFaceModelInterface;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return bool
     */
    private function isAdminModel(UrlElementInterface $model): bool
    {
        if (!$model instanceof IFaceModelInterface) {
            return false;
        }

        return $model->getZoneName() === ZoneInterface::ADMIN;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return bool
     */
    private function isPublicModel(UrlElementInterface $model): bool
    {
        if (!$model instanceof IFaceModelInterface) {
            return false;
        }

        return $model->getZoneName() === ZoneInterface::PUBLIC;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getLinkedIFaceFromCache(string $key): ?IFaceModelInterface
    {
        if (!isset($this->entityLinkedCodenameCache[$key])) {
            return null;
        }

        $codename = $this->entityLinkedCodenameCache[$key];

        $instance = $this->getByCodename($codename);

        if (!$instance instanceof IFaceModelInterface) {
            throw new IFaceException('UrlElement :name must implement :must', [
                ':name' => $codename,
                ':must' => IFaceModelInterface::class,
            ]);
        }

        return $instance;
    }

    /**
     * @param string                              $key
     * @param \BetaKiller\Url\IFaceModelInterface $model
     */
    private function storeLinkedIFaceInCache(string $key, IFaceModelInterface $model): void
    {
        $this->entityLinkedCodenameCache[$key] = $model->getCodename();
    }
}
