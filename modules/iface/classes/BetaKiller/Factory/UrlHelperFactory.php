<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\DI\ContainerInterface;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;

class UrlHelperFactory
{
    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private $container;

    /**
     * UrlHelperFactory constructor.
     *
     * @param \BetaKiller\DI\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(UrlContainerInterface $params = null, UrlElementStack $stack = null): UrlHelper
    {
        $params = $params ?? new ResolvingUrlContainer;
        $stack  = $stack ?? new UrlElementStack($params);

        return $this->container->make(UrlHelper::class, [
            'stack'  => $stack,
            'params' => $params,
        ]);
    }
}
