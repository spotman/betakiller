<?php
declare(strict_types=1);

namespace BetaKiller\Twig;

use BetaKiller\Config\ConfigProviderInterface;
use Debug;
use Kohana_Exception;
use Psr\Container\ContainerInterface;
use Twig_Environment;
use Twig_ExtensionInterface;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Twig_SimpleTest;

final class TwigEnvironmentFactory
{
    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * TwigEnvironmentFactory constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \Psr\Container\ContainerInterface          $container
     */
    public function __construct(ConfigProviderInterface $config, ContainerInterface $container)
    {
        $this->config    = $config;
        $this->container = $container;
    }

    /**
     * Create a new Twig environment
     *
     * @return \Twig_Environment
     * @throws \BetaKiller\Twig\TwigException
     */
    public function create(): Twig_Environment
    {
        $config    = (array)$this->config->load(['twig']);
        $envConfig = $config['environment'];
        $path      = $envConfig['cache'];
        $chmod     = $envConfig['chmod'];

        if ($path !== false && !is_writable($path) && !$this->initCache($path, $chmod)) {
            throw new Kohana_Exception('Directory :dir must exist and be writable', [
                ':dir' => Debug::path($path),
            ]);
        }

        $loader = new TwigLoaderCfs($config['loader']);
        $env    = new Twig_Environment($loader, $envConfig);

        /** @var string[] $functions */
        $functions = $config['functions'];

        /** @var string[] $filters */
        $filters = $config['filters'];

        /** @var string[] $tests */
        $tests = $config['tests'];

        /** @var string[] $extensions */
        $extensions = $config['extensions'];

        foreach ($functions as $key => $value) {
            $function = new Twig_SimpleFunction($key, $value);
            $env->addFunction($function);
        }

        foreach ($filters as $key => $value) {
            $filter = new Twig_SimpleFilter($key, $value);
            $env->addFilter($filter);
        }

        foreach ($tests as $key => $value) {
            $test = new Twig_SimpleTest($key, $value);
            $env->addTest($test);
        }

        foreach ($extensions as $extensionClassName) {
            if (!\is_string($extensionClassName)) {
                throw new TwigException('Extension must be a class name but :real given', [
                    ':must' => Twig_ExtensionInterface::class,
                    ':real' => \gettype($extensionClassName),
                ]);
            }

            $extension = $this->container->get($extensionClassName);

            if (!($extension instanceof Twig_ExtensionInterface)) {
                throw new TwigException('Twig extension must be an instance of :must, but :real given', [
                    ':must' => Twig_ExtensionInterface::class,
                    ':real' => \get_class($extension),
                ]);
            }

            $env->addExtension($extension);
        }

        return $env;
    }

    /**
     * Initialize the cache directory
     *
     * @param   string $path Path to the cache directory
     *
     * @param int      $chmod
     *
     * @return  boolean
     */
    private function initCache(string $path, int $chmod): bool
    {
        if (!@mkdir($path, $chmod, true) && !is_dir($path)) {
            return false;
        }

        return chmod($path, $chmod);
    }
}
