<?php
namespace BetaKiller\Factory;

use BetaKiller\DI\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

final class NamespaceBasedFactory
{
    public const CACHE_TTL = 86400; // 1 day

    /**
     * @var mixed[]
     */
    private static $instances = [];

    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $rootNamespaces = [];

    /**
     * @var string[]
     */
    private $classNamespaces;

    /**
     * @var string
     */
    private $classSuffix;

    /**
     * @var string
     */
    private $expectedInterface;

    /**
     * @var bool
     */
    private $instanceCachingEnabled = false;

    /**
     * @var callable
     */
    private $prepareArgumentsCallback;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $classNamesCache;

    /**
     * @var bool
     */
    private $rawInstance;

    /**
     *
     * @param \BetaKiller\DI\ContainerInterface $container
     * @param \Psr\SimpleCache\CacheInterface   $cache
     */
    public function __construct(ContainerInterface $container, CacheInterface $cache)
    {
        $this->container       = $container;
        $this->classNamesCache = $cache;
    }

    public function setExpectedInterface($interfaceName): NamespaceBasedFactory
    {
        $this->expectedInterface = (string)$interfaceName;

        return $this;
    }

    /**
     * @param string ...$namespaces
     *
     * @return $this
     */
    public function setClassNamespaces(string ...$namespaces): NamespaceBasedFactory
    {
        $this->classNamespaces = $namespaces;

        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return $this
     */
    public function setClassSuffix(string $suffix): NamespaceBasedFactory
    {
        $this->classSuffix = $suffix;

        return $this;
    }

    /**
     * @param string $ns
     *
     * @return $this
     */
    public function addRootNamespace(string $ns): NamespaceBasedFactory
    {
        $this->rootNamespaces[] = $ns;

        return $this;
    }

    /**
     * @return $this
     */
    public function cacheInstances(): NamespaceBasedFactory
    {
        $this->instanceCachingEnabled = true;

        return $this;
    }

    /**
     * @param callable $func
     *
     * @return $this
     */
    public function prepareArgumentsWith(callable $func): NamespaceBasedFactory
    {
        $this->prepareArgumentsCallback = $func;

        return $this;
    }

    /**
     * @return $this
     */
    public function rawInstances(): NamespaceBasedFactory
    {
        $this->rawInstance = true;

        return $this;
    }

    /**
     * @param string $codename
     * @param array  $arguments
     *
     * @return mixed
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create($codename, array $arguments = null)
    {
        if (!$codename) {
            throw new FactoryException('Object codename is missing');
        }

        $instance = $this->getInstanceFromCache($codename);

        if (!$instance) {
            $className = $this->detectClassName($codename);

            if ($this->prepareArgumentsCallback) {
                $arguments = \call_user_func($this->prepareArgumentsCallback, $arguments, $className);
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
        $className = $this->getClassNameFromCache($codename);

        if ($className) {
            return $className;
        }

        // Explode legacy naming by underscore
        $codenameArray = explode('_', $codename);

        // Add class namespaces if needed
        if ($this->classNamespaces) {
            $codenameArray = array_merge($this->classNamespaces, $codenameArray);
        }

        $separator = '\\';
        $baseName  = implode($separator, $codenameArray).$this->classSuffix;

        $searchNamespaces = array_merge($this->rootNamespaces, ['BetaKiller']);

        $tried = [];

        // Search for class in namespaces
        foreach ($searchNamespaces as $ns) {
            // Add namespace prefix
            $className = $ns.$separator.$baseName;

            if (class_exists($className)) {
                $this->storeClassNameInCache($codename, $className);

                return $className;
            }

            $tried[] = $className;
        }

        // Search for legacy naming (it is just codename with underscore separators)
        $className = implode('_', $codenameArray);

        if (class_exists($className)) {
            $this->storeClassNameInCache($codename, $className);

            return $className;
        }

        $tried[] = $className;

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
        }
    }

    /**
     * @param string $codename
     * @param string $className
     *
     * @return bool
     */
    private function storeClassNameInCache(string $codename, string $className): bool
    {
        try {
            $key = $this->getClassNameCacheKey($codename);

            return $this->classNamesCache->set($key, $className, self::CACHE_TTL);
        } catch (InvalidArgumentException $e) {
            throw FactoryException::wrap($e);
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
     * @param mixed $instance
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function storeInstanceInCache(string $codename, $instance): void
    {
        if (!$this->instanceCachingEnabled) {
            return;
        }

        if ($this->hasInstanceInCache($codename)) {
            throw new FactoryException('Instance :codename is already cached', [':codename' => $codename]);
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
        return str_replace('\\', '_', $this->expectedInterface).'.'.$codename;
    }
}
