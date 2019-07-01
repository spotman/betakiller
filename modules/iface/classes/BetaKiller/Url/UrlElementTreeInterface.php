<?php
namespace BetaKiller\Url;

use ArrayIterator;
use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;
use RecursiveIteratorIterator;

interface UrlElementTreeInterface
{
    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     * @param bool|null                           $warnIfExists
     *
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function add(UrlElementInterface $model, ?bool $warnIfExists = null): void;

    /**
     * Returns true if Url element with provided codename exists
     *
     * @param string $codename
     *
     * @return bool
     */
    public function has(string $codename): bool;

    /**
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function validate(): void;

    /**
     * Returns default iface model
     *
     * @return \BetaKiller\Url\UrlElementInterface
     */
    public function getDefault(): UrlElementInterface;

    /**
     * Returns list of root elements
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getRoot(): array;

    /**
     * Returns list of child nodes
     *
     * @param \BetaKiller\Url\UrlElementInterface $parent
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getChildren(UrlElementInterface $parent): array;

    /**
     * Returns parent iface model or null if none was found
     *
     * @param \BetaKiller\Url\UrlElementInterface $child
     *
     * @return \BetaKiller\Url\UrlElementInterface|null
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getParent(UrlElementInterface $child): ?UrlElementInterface;

    /**
     * Returns iface model by codename or throws an exception if nothing was found
     *
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getByCodename(string $codename): UrlElementInterface;

    /**
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\Url\EntityLinkedUrlElementInterface[]
     */
    public function getByActionAndZone(string $action, string $zone): array;

    /**
     * Search for UrlElement linked to provided entity, entity action and zone
     *
     * @param string $entityName
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\Url\EntityLinkedUrlElementInterface
     */
    public function getByEntityActionAndZone(
        string $entityName,
        string $action,
        string $zone
    ): EntityLinkedUrlElementInterface;

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \ArrayIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getReverseBreadcrumbsIterator(UrlElementInterface $model): ArrayIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \ArrayIterator
     */
    public function getBranchIterator(UrlElementInterface $model): ArrayIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|null                     $parent
     * @param \BetaKiller\Url\ElementFilter\UrlElementFilterInterface|null $filter
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getRecursiveIteratorIterator(
        UrlElementInterface $parent = null,
        UrlElementFilterInterface $filter = null
    ): RecursiveIteratorIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getPublicIFaceIterator(UrlElementInterface $parent = null): RecursiveIteratorIterator;

    /**
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getRecursiveSitemapIterator(): RecursiveIteratorIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getAdminIFaceIterator(UrlElementInterface $parent = null): RecursiveIteratorIterator;
}
