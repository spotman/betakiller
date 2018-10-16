<?php
namespace BetaKiller\Url;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;

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
     * @return \BetaKiller\Url\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getDefault(): IFaceModelInterface;

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
     * @param \BetaKiller\Url\IFaceModelInterface $child
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getParentIFaceModel(IFaceModelInterface $child): ?IFaceModelInterface;

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
     * @return \BetaKiller\Url\IFaceModelInterface[]
     */
    public function getIFacesByActionAndZone(string $action, string $zone): array;

    /**
     * Search for UrlElement linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string                                        $zone
     *
     * @return \BetaKiller\Url\IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getByEntityActionAndZone(
        DispatchableEntityInterface $entity,
        string $action,
        string $zone
    ): IFaceModelInterface;

    /**
     * Returns array of WebHookModelInterface instances linked to provided service
     *
     * @param string $serviceName
     *
     * @return \BetaKiller\Url\WebHookModelInterface[]
     */
    public function getWebHooksByServiceName(string $serviceName): array;

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return \ArrayIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getReverseBreadcrumbsIterator(UrlElementInterface $model): \ArrayIterator;

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
    ): \RecursiveIteratorIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getPublicIFaceIterator(UrlElementInterface $parent = null): \RecursiveIteratorIterator;

    /**
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getRecursiveSitemapIterator(): \RecursiveIteratorIterator;

    /**
     * @param \BetaKiller\Url\UrlElementInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|\BetaKiller\Url\UrlElementInterface[]
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getAdminIFaceIterator(UrlElementInterface $parent = null): \RecursiveIteratorIterator;
}
