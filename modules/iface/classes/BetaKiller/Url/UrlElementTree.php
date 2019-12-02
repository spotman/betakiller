<?php
namespace BetaKiller\Url;

use ArrayIterator;
use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;
use RecursiveCallbackFilterIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;

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
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function add(UrlElementInterface $model, ?bool $warnIfExists = null): void
    {
        $codename = $model->getCodename();

        if ($warnIfExists && isset($this->items[$codename])) {
            throw new UrlElementException('IFace ":codename" already exists in the tree', [':codename' => $codename]);
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
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function validate(): void
    {
        $this->validateBranch();
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $parent
     *
     * @throws \BetaKiller\Url\UrlElementException
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
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateLayer(array $models): void
    {
        $dynamicCounter = 0;
        $uris           = [];

        foreach ($models as $model) {
            if ($model->hasDynamicUrl() || $model->hasTreeBehaviour()) {
                $dynamicCounter++;
            }

            $uri = $model->getUri();

            if (in_array($uri, $uris, true)) {
                throw new UrlElementException('Duplicate URIs per layer are not allowed, codename is ":name"', [
                    ':name' => $model->getCodename(),
                ]);
            }

            $uris[] = $uri;
        }

        if ($dynamicCounter > 1) {
            throw new UrlElementException('Layer must have only one IFace with dynamic dispatching');
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function validateModel(UrlElementInterface $model): void
    {
        $parentCodename = $model->getParentCodename();

        if ($parentCodename && !$this->has($parentCodename)) {
            throw new UrlElementException('Missing parent ":parent" for ":codename"', [
                ':parent'   => $parentCodename,
                ':codename' => $model->getCodename(),
            ]);
        }

        $this->validateZone($model);

        if ($model instanceof IFaceModelInterface) {
            $this->validateIFaceModel($model);
        }

        // Check label exists
        if ($model instanceof UrlElementWithLabelInterface) {
            $model->getLabel();
        }
    }

    private function validateIFaceModel(IFaceModelInterface $model): void
    {
        $codename = $model->getCodename();

        if (!$model->getLabel()) {
            throw new UrlElementException('Label is missing for IFace ":codename"', [
                ':codename' => $codename,
            ]);
        }
    }

    private function validateZone(UrlElementInterface $model): void
    {
        if (!$model->getZoneName()) {
            throw new UrlElementException('IFace zone is missing for UrlElement ":codename" with URI ":uri"', [
                ':codename' => $model->getCodename(),
                ':uri'      => $model->getUri(),
            ]);
        }
    }

    /**
     * Returns default iface model
     *
     * @return \BetaKiller\Url\IFaceModelInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getDefault(): UrlElementInterface
    {
        foreach ($this->items as $item) {
            if ($item->isDefault()) {
                return $item;
            }
        }

        throw new UrlElementException('No default UrlElement found');
    }

    /**
     * Returns list of root elements
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getRoot(): array
    {
        $root = $this->getChilds();

        if (!$root) {
            throw new UrlElementException('No root IFaces found, define them first');
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
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getParent(UrlElementInterface $child): ?UrlElementInterface
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
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getByCodename(string $codename): UrlElementInterface
    {
        if (!isset($this->items[$codename])) {
            throw new UrlElementException('No UrlElement found by codename ":codename"', [
                ':codename' => $codename,
            ]);
        }

        return $this->items[$codename];
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getByActionAndZone(string $action, string $zone): array
    {
        $output = [];

        foreach ($this->items as $model) {
            if (!$model instanceof EntityLinkedUrlElementInterface) {
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
     * @param string $entityName
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\Url\IFaceModelInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getByEntityActionAndZone(
        string $entityName,
        string $action,
        string $zone
    ): EntityLinkedUrlElementInterface {
        $key = implode('.', [$entityName, $action, $zone]);

        $model = $this->getLinkedElementFromCache($key);

        if ($model) {
            return $model;
        }

        $model = $this->findByEntityActionAndZone($entityName, $action, $zone);

        if (!$model) {
            throw new UrlElementException('No UrlElement found for ":entity.:action" entity action in ":zone" zone', [
                ':entity' => $entityName,
                ':action' => $action,
                ':zone'   => $zone,
            ]);
        }

        $this->storeLinkedElementInCache($key, $model);

        return $model;
    }

    /**
     * @param string $entityName
     * @param string $entityAction
     * @param string $zone
     *
     * @return \BetaKiller\Url\UrlElementInterface|null
     */
    private function findByEntityActionAndZone(
        string $entityName,
        string $entityAction,
        string $zone
    ): ?EntityLinkedUrlElementInterface {
        foreach ($this->items as $model) {
            if (!$model instanceof EntityLinkedUrlElementInterface) {
                continue;
            }

            if ($model->getEntityModelName() !== $entityName) {
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
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \ArrayIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getReverseBreadcrumbsIterator(UrlElementInterface $model): ArrayIterator
    {
        $stack   = [];
        $current = $model;

        do {
            $stack[] = $current;

            $current = $this->getParent($current);
        } while ($current);

        return new ArrayIterator($stack);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \ArrayIterator
     */
    public function getBranchIterator(UrlElementInterface $model): ArrayIterator
    {
        $stack   = [];
        $current = $model;

        do {
            $stack[] = $current;

            $current = $this->getParent($current);
        } while ($current);

        return new ArrayIterator(array_reverse($stack));
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|null                     $parent
     *
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null $filter
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getRecursiveIteratorIterator(
        UrlElementInterface $parent = null,
        UrlElementFilterInterface $filter = null
    ): RecursiveIteratorIterator {
        return new RecursiveIteratorIterator(
            $this->getRecursiveIterator($parent, $filter),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|null                     $parent
     *
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null $filter
     *
     * @return \RecursiveIterator
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function getRecursiveIterator(
        UrlElementInterface $parent = null,
        UrlElementFilterInterface $filter = null
    ): RecursiveIterator {
        return new UrlElementTreeRecursiveIterator($this, $parent, $filter);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\IFaceModelInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getPublicIFaceIterator(UrlElementInterface $parent = null): RecursiveIteratorIterator
    {
        return $this->getRecursiveFilterIterator(function (UrlElementInterface $model) {
            return $this->isIFace($model) && $this->isPublicModel($model);
        }, $parent);
    }

    /**
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\IFaceModelInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getRecursiveSitemapIterator(): RecursiveIteratorIterator
    {
        return $this->getRecursiveFilterIterator(function (UrlElementInterface $model) {
            return $this->isIFace($model) && !$model->isHiddenInSiteMap() && $this->isPublicModel($model);
        });
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\IFaceModelInterface[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getAdminIFaceIterator(UrlElementInterface $parent = null): RecursiveIteratorIterator
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
     * @throws \BetaKiller\Url\UrlElementException
     */
    protected function getRecursiveFilterIterator(callable $callback, UrlElementInterface $parent = null)
    {
        $filter = new RecursiveCallbackFilterIterator(
            $this->getRecursiveIterator($parent),
            $callback
        );

        return new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);
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
        return $model->getZoneName() === ZoneInterface::ADMIN;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return bool
     */
    private function isPublicModel(UrlElementInterface $model): bool
    {
        return $model->getZoneName() === ZoneInterface::PUBLIC;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function getLinkedElementFromCache(string $key): ?EntityLinkedUrlElementInterface
    {
        if (!isset($this->entityLinkedCodenameCache[$key])) {
            return null;
        }

        $codename = $this->entityLinkedCodenameCache[$key];

        $instance = $this->getByCodename($codename);

        if (!$instance instanceof EntityLinkedUrlElementInterface) {
            throw new UrlElementException('UrlElement :name must implement :must', [
                ':name' => $codename,
                ':must' => EntityLinkedUrlElementInterface::class,
            ]);
        }

        return $instance;
    }

    /**
     * @param string                                          $key
     * @param \BetaKiller\Url\EntityLinkedUrlElementInterface $model
     */
    private function storeLinkedElementInCache(string $key, EntityLinkedUrlElementInterface $model): void
    {
        $this->entityLinkedCodenameCache[$key] = $model->getCodename();
    }
}
