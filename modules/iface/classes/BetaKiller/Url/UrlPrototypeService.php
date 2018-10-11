<?php
namespace BetaKiller\Url;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\SingleParentTreeModelInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\RawUrlParameterFactory;
use BetaKiller\Url\Parameter\RawUrlParameterInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;

class UrlPrototypeService
{
    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var \BetaKiller\Url\UrlDataSourceFactory
     */
    private $dataSourceFactory;

    /**
     * @var \BetaKiller\Url\Parameter\RawUrlParameterFactory
     */
    private $rawParameterFactory;

    /**
     * UrlPrototypeService constructor.
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface  $urlParameters
     * @param \BetaKiller\Url\UrlDataSourceFactory             $factory
     * @param \BetaKiller\Url\Parameter\RawUrlParameterFactory $rawFactory
     */
    public function __construct(
        UrlContainerInterface $urlParameters,
        UrlDataSourceFactory $factory,
        RawUrlParameterFactory $rawFactory
    ) {
        $this->urlParameters       = $urlParameters;
        $this->dataSourceFactory   = $factory;
        $this->rawParameterFactory = $rawFactory;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return \BetaKiller\Url\UrlPrototype
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function createPrototypeFromUrlElement(UrlElementInterface $urlElement): UrlPrototype
    {
        $uri = $urlElement->getUri();

        if (!$uri) {
            throw new UrlElementException('IFace :codename must have uri', [
                ':codename' => $urlElement->getCodename(),
            ]);
        }

        return $this->createPrototypeFromString($uri);
    }

    /**
     * @param string $string
     *
     * @return \BetaKiller\Url\UrlPrototype
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function createPrototypeFromString(string $string): UrlPrototype
    {
        return UrlPrototype::fromString($string);
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype $prototype
     * @param string                       $uriValue
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function createParameterInstance(UrlPrototype $prototype, string $uriValue): UrlParameterInterface
    {
        // Search for model item
        if ($prototype->hasModelKey()) {
            $dataSource = $this->getDataSourceInstance($prototype);

            $this->validatePrototypeModelKey($prototype, $dataSource);

            return $prototype->hasIdKey()
                ? $dataSource->findById((int)$uriValue)
                : $dataSource->findItemByUrlKeyValue($uriValue, $this->urlParameters);
        }

        // Plain parameter - use factory instead
        return $this->getRawParameterInstance($prototype, $uriValue);
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype $prototype
     *
     * @return \BetaKiller\Url\UrlDataSourceInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getDataSourceInstance(UrlPrototype $prototype): UrlDataSourceInterface
    {
        $name = $prototype->getDataSourceName();

        if (!$name) {
            throw new UrlPrototypeException('Empty UrlDataSource name');
        }

        return $this->dataSourceFactory->create($name);
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype $prototype
     * @param string                       $uriValue
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getRawParameterInstance(UrlPrototype $prototype, string $uriValue): UrlParameterInterface
    {
        $name = $prototype->getDataSourceName();

        if (!$name) {
            throw new UrlPrototypeException('Empty UrlParameter name');
        }

        return $this->rawParameterFactory->create($name, $uriValue);
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype           $prototype
     * @param \BetaKiller\Url\UrlDataSourceInterface $dataSource
     *
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function validatePrototypeModelKey(UrlPrototype $prototype, UrlDataSourceInterface $dataSource): void
    {
        if (!$prototype->hasIdKey() && $prototype->getModelKey() !== $dataSource->getUrlKeyName()) {
            throw new UrlPrototypeException('Url prototype model key does not match default url key in :prototype', [
                ':prototype' => $prototype->asString(),
            ]);
        }
    }

    /**
     * @param string                                               $sourceString
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $parameters
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function replaceUrlParametersParts(string $sourceString, UrlContainerInterface $parameters = null): string
    {
        return preg_replace_callback(
            UrlPrototype::REGEX,
            function ($matches) use ($parameters) {
                return $this->getCompiledPrototypeValue($matches[0], $parameters);
            },
            $sourceString
        );
    }

    /**
     * @param string                                               $proto
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getCompiledPrototypeValue(string $proto, ?UrlContainerInterface $params = null): string
    {
        $prototype = $this->createPrototypeFromString($proto);

        $param = $this->getParamByPrototype($prototype, $params);

        return $this->calculateParameterKeyValue($prototype, $param);
    }

    /**
     * @param string                                               $proto
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getCompiledTreePrototypeValue(string $proto, ?UrlContainerInterface $params = null): string
    {
        $prototype = $this->createPrototypeFromString($proto);
        $parameter = $this->getParamByPrototype($prototype, $params);

        if (!($parameter instanceof SingleParentTreeModelInterface)) {
            throw new UrlPrototypeException('Model :name must be instance of :must for tree traversing', [
                ':name' => \get_class($parameter),
                ':must' => SingleParentTreeModelInterface::class,
            ]);
        }

        $parts = [];

        do {
            $parts[]   = $this->calculateParameterKeyValue($prototype, $parameter);
            $parameter = $parameter->getParent();
        } while ($parameter);

        return implode('/', array_reverse($parts));
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype                         $prototype
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $parameters
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function getParamByPrototype(
        UrlPrototype $prototype,
        ?UrlContainerInterface $parameters = null
    ): UrlParameterInterface {
        $name = $prototype->getDataSourceName();

        $instance = $parameters ? $parameters->getParameter($name) : null;

        // Inherit model from current request url parameters
        $instance = $instance ?: $this->urlParameters->getParameter($name);

        if (!$instance) {
            throw new UrlPrototypeException('Can not find :name parameter', [':name' => $name]);
        }

        return $instance;
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype                    $prototype
     * @param \BetaKiller\Url\Parameter\UrlParameterInterface $param
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function calculateParameterKeyValue(UrlPrototype $prototype, UrlParameterInterface $param): string
    {
        $key = $prototype->getModelKey();

        if ($prototype->isMethodCall()) {
            $method = $key;

            if (!method_exists($param, $method)) {
                throw new UrlPrototypeException('Method :method does not exists in model :model', [
                    ':method' => $method,
                    ':model'  => \get_class($param),
                ]);
            }

            return $param->$method();
        }

        if ($prototype->hasIdKey()) {
            // There is an ID key and its entity
            if ($param instanceof DispatchableEntityInterface) {
                return $param->getID();
            }

            // Do not publish IDs of non-dispatchable entities
            throw new UrlPrototypeException('Parameter :model must implement :must for using ID in url', [
                ':model' => \get_class($param),
                ':must'  => DispatchableEntityInterface::class,
            ]);
        }

        if ($prototype->hasModelKey()) {
            // Model key needs entity
            if ($param instanceof DispatchableEntityInterface) {
                return $param->getUrlKeyValue($key);
            }

            throw new UrlPrototypeException('UrlParameter :model must implement :must for using keys in url', [
                ':model' => \get_class($param),
                ':must'  => DispatchableEntityInterface::class,
            ]);
        }

        // No key, $param must be a simple UrlParameter
        if ($param instanceof RawUrlParameterInterface) {
            return $param->exportUriValue();
        }

        throw new UrlPrototypeException('UrlParameter :model must implement :must for raw usage in url', [
            ':model' => \get_class($param),
            ':must'  => RawUrlParameterInterface::class,
        ]);
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype                    $prototype
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \BetaKiller\Url\Parameter\UrlParameterInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getAvailableParameters(UrlPrototype $prototype, UrlContainerInterface $params): array
    {
        if ($prototype->isMethodCall()) {
            throw new UrlPrototypeException('Can not collect available params for method-based prototype :prototype', [
                ':prototype' => $prototype->asString(),
            ]);
        }

        if (!$prototype->hasModelKey()) {
            throw new NotImplementedHttpException('RawUrlParameter processing is missing');
        }

        // Prototype has model key and is related to a UrlDataSource
        $dataSource = $this->getDataSourceInstance($prototype);

        return $prototype->hasIdKey()
            ? $dataSource->getAll()
            : $dataSource->getItemsHavingUrlKey($params);
    }
}
