<?php
namespace BetaKiller\DI;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception;
use BetaKiller\Env\AppEnvInterface;
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
    private static ?ContainerInterface $instance = null;

    /**
     * @var \DI\Container
     */
    private \DI\Container $container;

    public static function factory(
        AppEnvInterface         $appEnv,
        ConfigProviderInterface $configProvider
    ): ContainerInterface {
        if (self::$instance) {
            throw new \LogicException('DI container is already initialized');
        }

        return self::$instance = new self($appEnv, $configProvider);
    }

    /**
     * @deprecated Bad practice, use DI in constructor instead
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            throw new Exception('Initialize DI container before using it');
        }

        return self::$instance;
    }

    /**
     * You can`t create objects directly, use CLASS::instance() instead
     * Also you can define your own protected constructor in child class
     */
    private function __construct(
        AppEnvInterface         $appEnv,
        ConfigProviderInterface $configProvider
    ) {
        $builder = new ContainerBuilder();

        /** @see http://php-di.org/doc/performances.html */
        $enableOptimization = $appEnv->isAppRunning() && ($appEnv->inProductionMode() || $appEnv->inStagingMode());

        if ($enableOptimization) {
            $compileTo = $appEnv->getCachePath('php-di');
            $builder->enableCompilation($compileTo);

            if (function_exists('apcu_enabled') && apcu_enabled()) {
                $builder->enableDefinitionCache();
            }
        }

        $config = (array)$configProvider->load(['php-di']);

        $definitions = $config['definitions'] ?? [];

        $builder->addDefinitions($definitions);

        // Add core classes explicitly
        $builder->addDefinitions([
            AppEnvInterface::class         => $appEnv,
            ConfigProviderInterface::class => $configProvider,

            // Inject container into factories
            ContainerInterface::class      => $this,

            // Use Invoker for calling methods and lambda functions with dependencies
            InvokerInterface::class        => $this,
        ]);

        $this->container = $builder
            ->useAutowiring(true)
            ->useAttributes(true)
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
        return $this->container->get($id);
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
        return $this->container->has($id);
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
    public function make(string $name, array $parameters = null): mixed
    {
        return $this->container->make($name, $parameters ?? []);
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
        return $this->container->call($callable, $parameters ?: []);
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }
}
