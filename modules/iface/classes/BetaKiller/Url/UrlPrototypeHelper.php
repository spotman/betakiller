<?php
namespace BetaKiller\Url;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;

class UrlPrototypeHelper
{
    public const PROTOTYPE_PCRE = '(\{([A-Za-z_]+)\.([A-Za-z_]+)(\(\))*\})';

    /**
     * @var \BetaKiller\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var \BetaKiller\Url\UrlDataSourceFactory
     */
    private $dataSourceFactory;

    /**
     * @var \BetaKiller\Url\RawUrlParameterFactory
     */
    private $rawParameterFactory;

    /**
     * UrlPrototypeHelper constructor.
     *
     * @param \BetaKiller\Url\UrlContainerInterface  $urlParameters
     * @param \BetaKiller\Url\UrlDataSourceFactory   $factory
     * @param \BetaKiller\Url\RawUrlParameterFactory $rawFactory
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
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return \BetaKiller\Url\UrlPrototype
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function fromIFaceUri(IFaceInterface $iface): UrlPrototype
    {
        $uri = $iface->getUri();

        if (!$uri) {
            throw new IFaceException('IFace :codename must have uri', [
                ':codename' => $iface->getCodename(),
            ]);
        }

        return $this->fromString($uri);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $ifaceModel
     *
     * @return \BetaKiller\Url\UrlPrototype
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function fromIFaceModelUri(IFaceModelInterface $ifaceModel): UrlPrototype
    {
        $uri = $ifaceModel->getUri();

        if (!$uri) {
            throw new IFaceException('IFace :codename must have uri', [
                ':codename' => $ifaceModel->getCodename(),
            ]);
        }

        return $this->fromString($ifaceModel->getUri());
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
     * @return \BetaKiller\Url\UrlParameterInterface
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
     * @param string                                     $sourceString
     * @param \BetaKiller\Url\UrlContainerInterface|null $parameters
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function replaceUrlParametersParts(string $sourceString, UrlContainerInterface $parameters = null): string
    {
        return preg_replace_callback(
            self::PROTOTYPE_PCRE,
            function ($matches) use ($parameters) {
                return $this->getCompiledPrototypeValue($matches[0], $parameters);
            },
            $sourceString
        );
    }

    /**
     * @param                                                   $proto
     * @param \BetaKiller\Url\UrlContainerInterface|null        $params
     * @param bool|null                                         $isTree
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getCompiledPrototypeValue(
        string $proto,
        UrlContainerInterface $params = null,
        ?bool $isTree = null
    ): string {
        $isTree    = $isTree ?? false;
        $prototype = $this->fromString($proto);

        $model = $this->getParamFromUrlContainer($prototype, $params);

        if ($isTree && !($model instanceof TreeModelSingleParentInterface)) {
            throw new UrlPrototypeException('Model :model must be instance of :object for tree traversing', [
                ':model'  => \get_class($model),
                ':object' => TreeModelSingleParentInterface::class,
            ]);
        }

        $parts = [];

        do {
            $parts[] = $this->calculateParameterKeyValue($prototype, $model);
        } while ($isTree && ($model = $model->getParent()));

        return implode('/', array_reverse($parts));
    }

    /**
     * @param \BetaKiller\Url\UrlPrototype               $prototype
     * @param \BetaKiller\Url\UrlContainerInterface|null $parameters
     *
     * @return \BetaKiller\Url\UrlParameterInterface
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function getParamFromUrlContainer(
        UrlPrototype $prototype,
        UrlContainerInterface $parameters = null
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
     * @param \BetaKiller\Url\UrlPrototype          $prototype
     * @param \BetaKiller\Url\UrlParameterInterface $param
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
     * @param string $string
     *
     * @return \BetaKiller\Url\UrlPrototype
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function fromString(string $string): UrlPrototype
    {
        return UrlPrototype::fromString($string);
    }
}
