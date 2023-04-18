<?php
namespace BetaKiller\View;

use BetaKiller\Twig\TwigView;
use Psr\Container\ContainerInterface;
use Twig\Environment;

class TwigViewFactory implements ViewFactoryInterface
{
    private ContainerInterface $container;

    /**
     * TwigViewFactory constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $file): ViewInterface
    {
        return new TwigView($this->getTwigEnv(), $file);
    }

    /**
     * Returns true if template exists
     *
     * @param string $file
     *
     * @return bool
     */
    public function exists(string $file): bool
    {
        return $this->getTwigEnv()->getLoader()->exists($file);
    }

    /**
     * No direct Environment injection coz of circular dependency TwigExtension => WidgetFacade => TwigExtension
     *
     * @return \Twig\Environment
     */
    private function getTwigEnv(): Environment
    {
        try {
            return $this->container->get(Environment::class);
        } catch (\Throwable $e) {
            throw new \LogicException($e->getMessage());
        }
    }
}
