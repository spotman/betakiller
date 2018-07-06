<?php
namespace BetaKiller\Url;

use BetaKiller\Model\DispatchableEntityInterface;

interface UrlElementTreeInterface
{
    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     * @param bool|null                           $warnIfExists
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function add(UrlElementInterface $model, ?bool $warnIfExists = null): void;

    /**
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function validate(): void;

    /**
     * Returns default iface model
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDefault(): UrlElementInterface;

    /**
     * Returns list of root elements
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getParent(UrlElementInterface $child): ?UrlElementInterface;

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $child
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getParentIFaceModel(IFaceModelInterface $child): ?IFaceModelInterface;

    /**
     * Returns iface model by codename or throws an exception if nothing was found
     *
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByCodename(string $codename): UrlElementInterface;

    /**
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    public function getByActionAndZone(string $action, string $zone): array;

    /**
     * Search for UrlElement linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string                                        $zone
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByEntityActionAndZone(
        DispatchableEntityInterface $entity,
        string $action,
        string $zone
    ): UrlElementInterface;

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \ArrayIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getReverseBreadcrumbsIterator(UrlElementInterface $model): \ArrayIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|null $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursiveIteratorIterator(UrlElementInterface $parent = null): \RecursiveIteratorIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursivePublicIterator(UrlElementInterface $parent = null): \RecursiveIteratorIterator;

    /**
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursiveSitemapIterator(): \RecursiveIteratorIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRecursiveAdminIterator(UrlElementInterface $parent = null): \RecursiveIteratorIterator;
}
