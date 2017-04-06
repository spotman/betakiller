<?php
namespace BetaKiller\Factory;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\DI\ContainerInterface;

class NamespaceBasedFactory
{
    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    protected $appConfig;

    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $rootNamespaces = [];

    /**
     * @var string[]
     */
    protected $classPrefixes;

    /**
     * @var string
     */
    protected $classSuffix;

    /**
     * @var string
     */
    protected $expectedInterface;

    protected $instanceCachingEnabled = false;

    /**
     * @var callable
     */
    protected $prepareArgumentsCallback;

    /**
     * @var mixed[]
     */
    protected static $instances = [];

    /**
     *
     * @param \BetaKiller\Config\AppConfigInterface $app_config
     * @param \BetaKiller\DI\ContainerInterface     $container
     */
    public function __construct(AppConfigInterface $app_config, ContainerInterface $container)
    {
        $this->appConfig = $app_config;
        $this->container = $container;
    }

    public function setExpectedInterface($interfaceName)
    {
        $this->expectedInterface = (string)$interfaceName;
        return $this;
    }

    /**
     * @param string[] ...$prefixes
     *
     * @return $this
     */
    public function setClassPrefixes(...$prefixes)
    {
        $this->classPrefixes = $prefixes;
        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return $this
     */
    public function setClassSuffix($suffix)
    {
        $this->classSuffix = $suffix;
        return $this;
    }

    /**
     * @param string $ns
     *
     * @return $this
     */
    public function addRootNamespace($ns)
    {
        $this->rootNamespaces[] = (string)$ns;
        return $this;
    }

    /**
     * @return $this
     */
    public function cacheInstances()
    {
        $this->instanceCachingEnabled = true;
        return $this;
    }

    /**
     * @param callable $func
     *
     * @return $this
     */
    public function prepareArguments(callable $func)
    {
        $this->prepareArgumentsCallback = $func;
        return $this;
    }

    /**
     * @param string $codename
     * @param array $arguments
     *
     * @return mixed
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function create($codename, array $arguments = null)
    {
        if (!$codename) {
            throw new FactoryException('Object codename is missing');
        }

        $className = $this->detectClassName($codename);

        if ($this->prepareArgumentsCallback) {
            $arguments = call_user_func($this->prepareArgumentsCallback, $arguments, $className);
        }

        $instance = $this->getInstanceFromCache($className);

        if (!$instance) {
            $instance = $this->createInstance($className, $arguments);
            $this->storeInstanceInCache($className, $instance);
        }

        return $instance;
    }

    protected function createInstance($className, array $arguments = null)
    {
        try {
            $instance = $arguments
                ? $this->container->make($className, $arguments)
                : $this->container->make($className);
        } catch (\Exception $e) {
            throw new FactoryException('Can not instantiate :class class, error is: :msg', [
                ':class'    =>  $className,
                ':msg'      =>  $e->getMessage(),
            ], null, $e);
        }

        if ($this->expectedInterface && !($instance instanceof $this->expectedInterface)) {
            throw new FactoryException('Class :class must be instance of :expected', [
                ':class' => get_class($instance),
                ':expected' => $this->expectedInterface,
            ]);
        }

        return $instance;
    }

    private function detectClassName($codename)
    {
        $appNamespace = $this->appConfig->get_namespace();

        // Explode legacy naming by underscore
        $codenameArray = explode('_', $codename);

        // Add class prefixes if needed
        if ($this->classPrefixes) {
            $codenameArray = array_merge($this->classPrefixes, $codenameArray);
        }

        $separator = '\\';
        $baseName = implode($separator, $codenameArray);

        $searchNamespaces = array_filter(array_merge([$appNamespace], $this->rootNamespaces, ['BetaKiller']));

        $tried = [];

        // Search for class in namespaces
        foreach ($searchNamespaces as $ns) {
            // Add namespace prefix
            $className = $ns.$separator.$baseName.$this->classSuffix;

            if (class_exists($className)) {
                return $className;
            }

            $tried[] = $className;
        }

        // Search for legacy naming (it is just codename with underscore separators)
        $className = implode('_', $codenameArray);

        if (class_exists($className)) {
            return $className;
        }

        $tried[] = $className;

        throw new FactoryException('No class found for :name, tried to autoload :tried', [
            ':name' => $baseName,
            ':tried' => implode(',', $tried),
        ]);
    }

    /**
     * @param string $className
     *
     * @return mixed|null
     */
    private function getInstanceFromCache($className)
    {
        return ($this->instanceCachingEnabled && $this->hasInstanceInCache($className))
            ? self::$instances[$className]
            : null;
    }

    private function storeInstanceInCache($className, $instance)
    {
        if (!$this->instanceCachingEnabled) {
            return;
        }

        if ($this->hasInstanceInCache($className)) {
            throw new FactoryException('Instance of :className is already cached', [':className' => $className]);
        }

        self::$instances[$className] = $instance;
    }

    private function hasInstanceInCache($className)
    {
        return isset(self::$instances[$className]);
    }
}
