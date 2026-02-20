<?php

declare(strict_types=1);

namespace BetaKiller\Url;

use ArrayIterator;
use BetaKiller\Dev\StartupProfiler;
use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;
use Psr\Container\ContainerInterface;
use RecursiveIteratorIterator;

readonly class UrlElementTreeLazyProxy implements UrlElementTreeInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function add(UrlElementInterface $model, ?bool $warnIfExists = null): void
    {
        $this->getTree()->add($model, $warnIfExists);
    }

    public function has(string $codename): bool
    {
        return $this->getTree()->has($codename);
    }

    public function getDefault(): UrlElementInterface
    {
        return $this->getTree()->getDefault();
    }

    public function getRoot(): array
    {
        return $this->getTree()->getRoot();
    }

    public function getChildren(UrlElementInterface $parent): array
    {
        return $this->getTree()->getChildren($parent);
    }

    public function getParent(UrlElementInterface $child): ?UrlElementInterface
    {
        return $this->getTree()->getParent($child);
    }

    public function getByCodename(string $codename): UrlElementInterface
    {
        return $this->getTree()->getByCodename($codename);
    }

    public function getByActionAndZone(string $action, ZoneInterface $zone): array
    {
        return
            $this->getTree()->getByActionAndZone($action, $zone);
    }

    public function getByEntityActionAndZone(string $entityName, string $action, ZoneInterface $zone): EntityLinkedUrlElementInterface
    {
        return $this->getTree()->getByEntityActionAndZone($entityName, $action, $zone);
    }

    public function getReverseBreadcrumbsIterator(UrlElementInterface $model): ArrayIterator
    {
        return $this->getTree()->getReverseBreadcrumbsIterator($model);
    }

    public function getBranchIterator(UrlElementInterface $model): ArrayIterator
    {
        return $this->getTree()->getBranchIterator($model);
    }

    public function getRecursiveIteratorIterator(
        UrlElementInterface $parent = null,
        UrlElementFilterInterface $filter = null
    ): RecursiveIteratorIterator {
        return $this->getTree()->getRecursiveIteratorIterator($parent, $filter);
    }

    public function getPublicIFaceIterator(UrlElementInterface $parent = null): RecursiveIteratorIterator
    {
        return $this->getTree()->getPublicIFaceIterator($parent);
    }

    public function getRecursiveSitemapIterator(): RecursiveIteratorIterator
    {
        return $this->getTree()->getRecursiveSitemapIterator();
    }

    public function getAdminIFaceIterator(UrlElementInterface $parent = null): RecursiveIteratorIterator
    {
        return $this->getTree()->getAdminIFaceIterator($parent);
    }

    private function getTree(): UrlElementTreeInterface
    {
        static $tree;

        return $tree ??= $this->getTreeFactory()();
    }

    private function getTreeFactory(): UrlElementTreeFactory
    {
        $p = StartupProfiler::begin('UrlElementTreeFactory init');

        $factory = $this->container->get(UrlElementTreeFactory::class);

        StartupProfiler::end($p);

        return $factory;
    }
}
