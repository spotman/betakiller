<?php
namespace BetaKiller\DI;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception;
use BetaKiller\Helper\AppEnvInterface;
use DI\ContainerBuilder;
use DI\DependencyException;
use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;

class Container implements ContainerInterface
{
    /**
     * @var Container
     */
    protected static $instance;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @deprecated Bad practice, use DI in constructor instead
     */
    public static function getInstance(): self
    {
        if (!static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * You can`t create objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    protected function __construct()
    {
    }

    public function init(ConfigProviderInterface $configProvider, AppEnvInterface $appEnv): void
    {
        if ($this->container) {
            throw new Exception('Container is already initialized');
        }

        $builder = new ContainerBuilder();

        $config = (array)$configProvider->load(['php-di']);

        $definitions    = $config['definitions'];
        $useAutowiring  = $config['autowiring'] ?? true;
        $useAnnotations = $config['annotations'] ?? true;

        /** @url http://php-di.org/doc/performances.html */
        $compileTo        = $config['compile_to'];
        $cacheDefinitions = $config['cache_definitions'];

        if ($compileTo) {
            $builder->enableCompilation($compileTo);
        }

        if ($cacheDefinitions) {
            $builder->enableDefinitionCache();
        }

        $builder->addDefinitions($definitions);

        // Add core classes explicitly
        $builder->addDefinitions([
            ConfigProviderInterface::class => $configProvider,
            AppEnvInterface::class         => $appEnv,
        ]);

        $this->container = $builder
            ->useAutowiring($useAutowiring)
            ->useAnnotations($useAnnotations)
            ->build();
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws \Psr\Container\NotFoundExceptionInterface No entry was found for this identifier.
     * @throws \Psr\Container\ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id): bool
    {
        return $this->getContainer()->has($id);
    }

    /**
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @param string $name       Entry name or a class name.
     * @param array  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                           parameters to specific values. Parameters not defined in this array will
     *                           be automatically resolved.
     *
     * @throws \InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException       Error while resolving the entry.
     * @throws \DI\NotFoundException         No entry or class found for the given name.
     * @return mixed
     */
    public function make($name, array $parameters = null)
    {
        return $this->getContainer()->make($name, $parameters ?? []);
    }

    /**
     * Call the given function using the given parameters.
     *
     * @param callable $callable   Function to call.
     * @param array    $parameters Parameters to use.
     *
     * @return mixed Result of the function.
     *
     * @throws InvocationException Base exception class for all the sub-exceptions below.
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     */
    public function call($callable, array $parameters = null)
    {
        return $this->getContainer()->call($callable, $parameters ?: []);
    }

    /**
     * Inject all dependencies on an existing instance.
     *
     * @param mixed $instance Object to perform injection upon
     *
     * @throws \InvalidArgumentException
     * @throws \DI\DependencyException Error while injecting dependencies
     * @return mixed $instance Returns the same instance
     */
    public function injectOn($instance)
    {
        return $this->getContainer()->injectOn($instance);
    }

    /**
     * @return ContainerInterface|mixed
     */
    protected function getContainer()
    {
        if (!$this->container) {
            throw new Exception('Initialize DI container before using it');
        }

        return $this->container;
    }

    /**
     * Prevent cloning
     */
    final private function __clone() {}
}
