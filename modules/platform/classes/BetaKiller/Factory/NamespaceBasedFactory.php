<?php
namespace BetaKiller\Factory;

use BetaKiller\DI\ContainerInterface;
use BetaKiller\Helper\LoggerHelper;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

final class NamespaceBasedFactory implements NamespaceBasedFactoryInterface
{
    public const CACHE_TTL = 86400; // 1 day

    /**
     * @var array
     */
    private static array $instances = [];

    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var string[]
     */
    private array $rootNamespaces = [];

    /**
     * @var string[]
     */
    private array $classNamespaces = [];

    /**
     * @var string|null
     */
    private ?string $classSuffix = null;

    /**
     * @var string|null
     */
    private ?string $expectedInterface = null;

    /**
     * @var bool
     */
    private bool $useInterface = false;

    /**
     * @var bool
     */
    private bool $legacyNaming = false;

    /**
     * @var bool
     */
    private bool $instanceCachingEnabled = false;

    /**
     * @var callable|null
     */
    private $prepareArgumentsCallback;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private CacheInterface $classNamesCache;

    /**
     * @var bool
     */
    private bool $rawInstance = false;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     *
     * @param \BetaKiller\DI\ContainerInterface $container
     * @param \Psr\SimpleCache\CacheInterface   $cache
     * @param \Psr\Log\LoggerInterface          $logger
     */
    public function __construct(ContainerInterface $container, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->container       = $container;
        $this->classNamesCache = $cache;
        $this->logger          = $logger;
    }

    public function setExpectedInterface($interfaceName): NamespaceBasedFactoryInterface
    {
        $this->expectedInterface = (string)$interfaceName;

        return $this;
    }

    /**
     * @param string ...$namespaces
     *
     * @return $this
     */
    public function setClassNamespaces(string ...$namespaces): NamespaceBasedFactoryInterface
    {
        $this->classNamespaces = $namespaces;

        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return $this
     */
    public function setClassSuffix(string $suffix): NamespaceBasedFactoryInterface
    {
        $this->classSuffix = $suffix;

        return $this;
    }

    /**
     * @param string $ns
     *
     * @return $this
     */
    public function addRootNamespace(string $ns): NamespaceBasedFactoryInterface
    {
        $this->rootNamespaces[] = $ns;

        return $this;
    }

    /**
     * @return $this
     */
    public function cacheInstances(): NamespaceBasedFactoryInterface
    {
        $this->instanceCachingEnabled = true;

        return $this;
    }

    /**
     * @param callable $func
     *
     * @return $this
     */
    public function prepareArgumentsWith(callable $func): NamespaceBasedFactoryInterface
    {
        $this->prepareArgumentsCallback = $func;

        return $this;
    }

    /**
     * @return $this
     */
    public function rawInstances(): NamespaceBasedFactoryInterface
    {
        $this->rawInstance = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function useInterface(): NamespaceBasedFactoryInterface
    {
        $this->useInterface = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function legacyNaming(): NamespaceBasedFactoryInterface
    {
        $this->legacyNaming = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function create(string $codename, array $arguments = null)
    {
        if (!$codename) {
            throw new FactoryException('Object codename is missing');
        }

        $instance = $this->getInstanceFromCache($codename);

        if (!$instance) {
            $className = $this->detectClassName($codename);

            if ($this->prepareArgumentsCallback) {
                $prepArgs = \call_user_func($this->prepareArgumentsCallback, $arguments, $className);

                if ($prepArgs === false) {
                    throw new FactoryException('Can not prepare arguments for ":class" with :data', [
                        ':class' => $className,
                        ':data'  => \json_encode($arguments, JSON_THROW_ON_ERROR),
                    ]);
                }

                $arguments = $prepArgs;
            }

            try {
                $instance = $this->createInstance($className, $arguments);
            } catch (\Throwable $e) {
                throw FactoryException::wrap($e);
            }

            if ($this->expectedInterface && !($instance instanceof $this->expectedInterface)) {
                throw new FactoryException('Class :class must be instance of :expected', [
                    ':class'    => \get_class($instance),
                    ':expected' => $this->expectedInterface,
                ]);
            }

            $this->storeInstanceInCache($codename, $instance);
        }

        return $instance;
    }

    /**
     * @param string $codename
     *
     * @return string
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function detectClassName(string $codename): string
    {
        $separator = '\\';

        // Replace legacy naming underscore with namespace separator
        $codename = \str_replace('_', $separator, $codename);

        $className = $this->getClassNameFromCache($codename);

        if ($className) {
            return $className;
        }

        // Explode naming by namespace separator
        $codenameArray = explode($separator, $codename);

        // Add class namespaces if needed
        if ($this->classNamespaces) {
            $codenameArray = array_merge($this->classNamespaces, $codenameArray);
        }

        $baseName = implode($separator, $codenameArray).$this->classSuffix;

        if ($this->useInterface) {
            $baseName .= 'Interface';
        }

        $candidates = [];

        // Search for class in namespaces
        foreach ($this->rootNamespaces as $ns) {
            // Add namespace prefix
            $candidates[] = $ns.$separator.$baseName;
        }

        if ($this->legacyNaming) {
            // Search for legacy naming (it is just codename with underscore separators)
            $candidates[] = implode('_', $codenameArray);
        }

        $tried = [];

        // Search for class in candidates
        foreach ($candidates as $className) {
            if ($this->useInterface ? interface_exists($className) : class_exists($className)) {
                $this->storeClassNameInCache($codename, $className);

                return $className;
            }

            $tried[] = $className;
        }

        throw new FactoryException('No class found for :name, tried to autoload :tried', [
            ':name'  => $baseName,
            ':tried' => implode(', ', $tried),
        ]);
    }

    /**
     * @param string $codename
     *
     * @return string|null
     */
    private function getClassNameFromCache(string $codename): ?string
    {
        try {
            $key = $this->getClassNameCacheKey($codename);

            return $this->classNamesCache->get($key);
        } catch (InvalidArgumentException $e) {
            throw FactoryException::wrap($e);
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            return null;
        }
    }

    /**
     * @param string $codename
     * @param string $className
     *
     * @return void
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function storeClassNameInCache(string $codename, string $className): void
    {
        try {
            $key = $this->getClassNameCacheKey($codename);

            $this->classNamesCache->set($key, $className, self::CACHE_TTL);
        } catch (InvalidArgumentException $e) {
            throw FactoryException::wrap($e);
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }

    /**
     * @param string $codename
     *
     * @return mixed|null
     */
    private function getInstanceFromCache(string $codename)
    {
        $key = $this->getInstanceCacheKey($codename);

        return ($this->instanceCachingEnabled && isset(self::$instances[$key]))
            ? self::$instances[$key]
            : null;
    }

    private function hasInstanceInCache(string $codename): bool
    {
        $key = $this->getInstanceCacheKey($codename);

        return isset(self::$instances[$key]);
    }

    /**
     * @param string     $className
     * @param array|null $arguments
     *
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \InvalidArgumentException
     */
    private function createInstance(string $className, array $arguments = null)
    {
        if ($this->rawInstance) {
            return $arguments
                ? new $className(...$arguments)
                : new $className;
        }

        return $arguments
            ? $this->container->make($className, $arguments)
            : $this->container->make($className);
    }

    /**
     * @param string $codename
     * @param mixed  $instance
     */
    private function storeInstanceInCache(string $codename, $instance): void
    {
        if (!$this->instanceCachingEnabled) {
            return;
        }

        if ($this->hasInstanceInCache($codename)) {
            LoggerHelper::logRawException(
                $this->logger,
                new FactoryException('Instance :codename is already cached', [':codename' => $codename])
            );
        }

        $key = $this->getInstanceCacheKey($codename);

        self::$instances[$key] = $instance;
    }

    private function getClassNameCacheKey(string $codename): string
    {
        return 'namespaceBasedFactory.classNameCache.'.$this->getInstanceCacheKey($codename);
    }

    private function getInstanceCacheKey(string $codename): string
    {
        return implode('-', $this->classNamespaces).'.'.\str_replace('\\', '_', $codename);
    }
}
