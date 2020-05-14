<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\DI\ContainerInterface;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;

final class UrlHelperFactory
{
    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * UrlHelperFactory constructor.
     *
     * @param \BetaKiller\DI\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param \BetaKiller\Url\UrlElementStack|null                 $stack
     *
     * @return \BetaKiller\Helper\UrlHelperInterface
     */
    public function create(UrlContainerInterface $params = null, UrlElementStack $stack = null): UrlHelperInterface
    {
        $params = $params ?? ResolvingUrlContainer::create();
        $stack  = $stack ?? new UrlElementStack($params);

        return $this->container->make(UrlHelper::class, [
            'stack'  => $stack,
            'params' => $params,
        ]);
    }
}
