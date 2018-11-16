<?php
namespace BetaKiller\View;

use BetaKiller\Twig\TwigView;

class TwigViewFactory implements ViewFactoryInterface
{
    /**
     * @var \Twig_Environment
     */
    private $env;

    /**
     * TwigViewFactory constructor.
     *
     * @param \Twig_Environment $env
     */
    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
    }

    public function create(string $file): ViewInterface
    {
        return new TwigView($this->env, $file);
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
        return $this->env->getLoader()->exists($file);
    }
}
