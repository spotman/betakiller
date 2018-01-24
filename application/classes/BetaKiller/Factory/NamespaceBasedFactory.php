<?php
namespace BetaKiller\Factory;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\DI\ContainerInterface;

final class NamespaceBasedFactory
{
    /**
     * @var mixed[]
     */
    private static $instances = [];

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

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
     * @var \BetaKiller\Factory\FactoryCacheInterface
     */
    private $classNamesCache;

    /**
     * @var bool
     */
    private $rawInstance;

    /**
     *
     * @param \BetaKiller\Config\AppConfigInterface     $appConfig
     * @param \BetaKiller\DI\ContainerInterface         $container
     * @param \BetaKiller\Factory\FactoryCacheInterface $cache
     */
    public function __construct(AppConfigInterface $appConfig, ContainerInterface $container, FactoryCacheInterface $cache)
    {
        $this->appConfig       = $appConfig;
        $this->container       = $container;
        $this->classNamesCache = $cache;
    }

    public function setExpectedInterface($interfaceName): NamespaceBasedFactory
    {
        $this->expectedInterface = (string)$interfaceName;

        return $this;
    }

    /**
     * @param string[] ...$namespaces
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
                throw new FactoryException('Can not instantiate :class class, error is: :msg', [
                    ':class' => $className,
                    ':msg'   => $e->getMessage(),
                ], $e->getCode(), $e);
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
        $appNamespace = $this->appConfig->getNamespace();

        // Explode legacy naming by underscore
        $codenameArray = explode('_', $codename);

        // Add class namespaces if needed
        if ($this->classNamespaces) {
            $codenameArray = array_merge($this->classNamespaces, $codenameArray);
        }

        $separator = '\\';
        $baseName  = implode($separator, $codenameArray).$this->classSuffix;

        $className = $this->getClassNameFromCache($baseName);

        if ($className) {
            return $className;
        }

        $searchNamespaces = array_filter(array_merge([$appNamespace], $this->rootNamespaces, ['BetaKiller']));

        $tried = [];

        // Search for class in namespaces
        foreach ($searchNamespaces as $ns) {
            // Add namespace prefix
            $className = $ns.$separator.$baseName;

            if (class_exists($className)) {
                $this->storeClassNameInCache($baseName, $className);
                return $className;
            }

            $tried[] = $className;
        }

        // Search for legacy naming (it is just codename with underscore separators)
        $className = implode('_', $codenameArray);

        if (class_exists($className)) {
            $this->storeClassNameInCache($baseName, $className);
            return $className;
        }

        $tried[] = $className;

        throw new FactoryException('No class found for :name, tried to autoload :tried', [
            ':name'  => $baseName,
            ':tried' => implode(',', $tried),
        ]);
    }

    /**
     * @param string $baseName
     *
     * @return string|null
     */
    private function getClassNameFromCache(string $baseName): ?string
    {
        if (!$this->classNamesCache->contains($baseName)) {
            return null;
        }

        return $this->classNamesCache->fetch($baseName);
    }

    private function storeClassNameInCache(string $baseName, string $className): bool
    {
        return $this->classNamesCache->save($baseName, $className);
    }

    /**
     * @param string $codename
     *
     * @return mixed|null
     */
    private function getInstanceFromCache(string $codename)
    {
        $key = $this->makeCacheKeyFromCodename($codename);

        return ($this->instanceCachingEnabled && isset(self::$instances[$key]))
            ? self::$instances[$key]
            : null;
    }

    private function hasInstanceInCache(string $codename): bool
    {
        $key = $this->makeCacheKeyFromCodename($codename);

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
     * @param        $instance
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

        $key = $this->makeCacheKeyFromCodename($codename);

        self::$instances[$key] = $instance;
    }

    private function makeCacheKeyFromCodename(string $codename): string
    {
        return $this->expectedInterface.'::'.$codename;
    }
}
