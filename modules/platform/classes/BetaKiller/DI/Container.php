<?php
namespace BetaKiller\DI;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Log\LoggerInterface;
use DI\ContainerBuilder;
use DI\DependencyException;
use Invoker\Exception\InvocationException;
use Invoker\Exception\NotCallableException;
use Invoker\Exception\NotEnoughParametersException;
use Invoker\InvokerInterface;

final class Container implements ContainerInterface
{
    /**
     * @var Container|null
     */
    private static ?Container $instance = null;

    /**
     * @var \DI\Container|null
     */
    private ?\DI\Container $container = null;

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
    private function __construct()
    {
    }

    public function init(
        ConfigProviderInterface $configProvider,
        AppEnvInterface $appEnv,
        LoggerInterface $logger
    ): void {
        if ($this->container) {
            throw new Exception('Container is already initialized');
        }

        $builder = new ContainerBuilder();

        $config = (array)$configProvider->load(['php-di']);

        $definitions    = $config['definitions'];
        $useAutowiring  = $config['autowiring'] ?? true;
        $useAnnotations = $config['annotations'] ?? true;

        /** @url http://php-di.org/doc/performances.html */
        $compile          = $config['compile'];
        $cacheDefinitions = $config['cache_definitions'];

        if ($compile) {
            $compileTo = $appEnv->getCachePath('php-di');
            $builder->enableCompilation($compileTo);
        }

        if ($cacheDefinitions) {
            $builder->enableDefinitionCache();
        }

        $builder->addDefinitions($definitions);

        // Add core classes explicitly
        $builder->addDefinitions([
            AppEnvInterface::class          => $appEnv,
            ConfigProviderInterface::class  => $configProvider,

            // Inject PSR logger and BetaKiller logger
            LoggerInterface::class          => $logger,
            \Psr\Log\LoggerInterface::class => $logger,

            // Inject container into factories
            ContainerInterface::class       => $this,

            // Use Invoker for calling methods and lambda functions with dependencies
            InvokerInterface::class         => $this,
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
     * @return mixed Entry.
     * @throws \Psr\Container\ContainerExceptionInterface Error while retrieving the entry.
     *
     * @throws \Psr\Container\NotFoundExceptionInterface No entry was found for this identifier.
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
     * @return mixed
     * @throws DependencyException       Error while resolving the entry.
     * @throws \DI\NotFoundException         No entry or class found for the given name.
     * @throws \InvalidArgumentException The name parameter must be of type string.
     */
    public function make($name, array $parameters = null)
    {
        return $this->getContainer()->make($name, $parameters ?? []);
    }

    /**
     * Call the given function using the given parameters.
     *
     * @param callable|array|string $callable   Function to call.
     * @param array|null            $parameters Parameters to use.
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
     * @return \DI\Container
     */
    private function getContainer(): \DI\Container
    {
        if (!$this->container) {
            throw new Exception('Initialize DI container before using it');
        }

        return $this->container;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }
}
