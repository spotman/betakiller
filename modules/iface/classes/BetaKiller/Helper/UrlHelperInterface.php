<?php

declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\ZoneInterface;

interface UrlHelperInterface
{
    /**
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getUrlElementByCodename(string $codename): UrlElementInterface;

    /**
     * @return \BetaKiller\Url\UrlElementInterface
     */
    public function getDefaultUrlElement(): UrlElementInterface;

    public function createUrlContainer(bool $importCurrent = null): UrlContainerInterface;

    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \BetaKiller\Helper\UrlHelperInterface
     */
    public function withUrlContainer(UrlContainerInterface $params): UrlHelperInterface;

    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \BetaKiller\Helper\UrlHelperInterface
     */
    public function importUrlContainer(UrlContainerInterface $params): UrlHelperInterface;

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function getUrlContainer(): UrlContainerInterface;

    /**
     * @param string                                               $codename
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @param bool|null                                            $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function makeCodenameUrl(
        string $codename,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string;

    /**
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param bool|null                                            $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function makeUrl(
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string;

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param \BetaKiller\Url\ZoneInterface                 $zone
     *
     * @param bool|null                                     $removeCycling
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getEntityUrl(
        DispatchableEntityInterface $entity,
        string $action,
        ZoneInterface $zone,
        ?bool $removeCycling = null
    ): string;

    public function getEntityNameUrl(
        string $entityName,
        string $action,
        ZoneInterface $zone,
        ?UrlContainerInterface $params = null,
        ?bool $removeCycling = null
    ): string;

    /**
     * @param string                        $entityName
     * @param \BetaKiller\Url\ZoneInterface $zone
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getCreateEntityUrl(string $entityName, ZoneInterface $zone): string;

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param \BetaKiller\Url\ZoneInterface                 $zone
     *
     * @param bool|null                                     $removeCycling
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getReadEntityUrl(
        DispatchableEntityInterface $entity,
        ZoneInterface $zone,
        ?bool $removeCycling = null
    ): string;

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param \BetaKiller\Url\ZoneInterface                 $zone
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getUpdateEntityUrl(DispatchableEntityInterface $entity, ZoneInterface $zone): string;

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param \BetaKiller\Url\ZoneInterface                 $zone
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getDeleteEntityUrl(DispatchableEntityInterface $entity, ZoneInterface $zone): string;

    /**
     * @param string                                               $entityName
     * @param \BetaKiller\Url\ZoneInterface                        $zone
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    public function getListEntityUrl(string $entityName, ZoneInterface $zone, ?UrlContainerInterface $params = null): string;

    /**
     * @param string                                               $entityName
     * @param \BetaKiller\Url\ZoneInterface                        $zone
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    public function getSearchEntityUrl(string $entityName, ZoneInterface $zone, ?UrlContainerInterface $params = null): string;

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     *
     * @return null|string
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getPreviewEntityUrl(DispatchableEntityInterface $entity): ?string;

    /**
     * @param \BetaKiller\Url\DummyModelInterface $dummy
     *
     * @return \BetaKiller\Url\UrlElementInterface|null
     */
    public function detectDummyRedirectTarget(DummyModelInterface $dummy): ?UrlElementInterface;

    /**
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function detectDummyForwardTarget(DummyModelInterface $dummy): ?UrlElementInterface;
}
