<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;

class UrlPrototypeHelper
{
    public const PROTOTYPE_PCRE = '(\{([A-Za-z_]+)\.([A-Za-z_]+)(\(\))*\})';

    /**
     * @var \BetaKiller\IFace\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var \BetaKiller\IFace\Url\UrlDataSourceFactory
     */
    private $dataSourceFactory;

    /**
     * UrlPrototypeHelper constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $urlParameters
     * @param \BetaKiller\IFace\Url\UrlDataSourceFactory  $factory
     */
    public function __construct(UrlContainerInterface $urlParameters, UrlDataSourceFactory $factory)
    {
        $this->urlParameters     = $urlParameters;
        $this->dataSourceFactory = $factory;
    }

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
     * @return \BetaKiller\IFace\Url\UrlPrototype
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
     * @param \BetaKiller\IFace\Url\UrlPrototype $prototype
     *
     * @return \BetaKiller\IFace\Url\UrlDataSourceInterface
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
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
     * @param \BetaKiller\IFace\Url\UrlPrototype           $prototype
     * @param \BetaKiller\IFace\Url\UrlDataSourceInterface $dataSource
     *
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    public function validatePrototypeModelKey(UrlPrototype $prototype, UrlDataSourceInterface $dataSource): void
    {
        if (!$prototype->hasIdKey() && $prototype->getModelKey() !== $dataSource->getUrlKeyName()) {
            throw new UrlPrototypeException('Url prototype model key does not match default url key in :prototype', [
                ':prototype' => $prototype->asString(),
            ]);
        }
    }

    public function replaceUrlParametersParts(string $sourceString, UrlContainerInterface $parameters = null): string
    {
        return preg_replace_callback(
            UrlPrototypeHelper::PROTOTYPE_PCRE,
            function ($matches) use ($parameters) {
                return $this->getCompiledPrototypeValue($matches[0], $parameters);
            },
            $sourceString
        );
    }

    /**
     * @param                                                   $proto
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null  $params
     * @param bool|null                                         $isTree
     *
     * @return string
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    public function getCompiledPrototypeValue(string $proto, UrlContainerInterface $params = null, ?bool $isTree = null): string
    {
        $isTree = $isTree ?? false;
        $prototype = $this->fromString($proto);

        $model = $this->getParamFromUrlContainer($prototype, $params);

        if ($isTree && !($model instanceof TreeModelSingleParentInterface)) {
            throw new UrlPrototypeException('Model :model must be instance of :object for tree traversing', [
                ':model'  => get_class($model),
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
     * @param \BetaKiller\IFace\Url\UrlPrototype               $prototype
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $parameters
     *
     * @return \BetaKiller\IFace\Url\UrlParameterInterface|null
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    private function getParamFromUrlContainer(UrlPrototype $prototype, UrlContainerInterface $parameters = null): ?UrlParameterInterface
    {
        $name = $prototype->getDataSourceName();

        $instance = $parameters ? $parameters->getParameter($name) : null;

        // Inherit model from current request url parameters
        $instance = $instance ?: $this->urlParameters->getParameter($name);

        if (!$instance) {
            throw new UrlPrototypeException('Can not find :name parameter', [':name' => $name]);
        }

        return $instance;
    }

    private function calculateParameterKeyValue(UrlPrototype $prototype, UrlParameterInterface $param): string
    {
        $key = $prototype->getModelKey();

        if ($prototype->isMethodCall()) {
            $method = $key;

            if (!method_exists($param, $method)) {
                throw new UrlPrototypeException('Method :method does not exists in model :model', [
                    ':method' => $method,
                    ':model'  => get_class($param),
                ]);
            }

            return $param->$method();
        }

        if ($prototype->hasIdKey()) {
            // Do not publish IDs of non-dispatchable entities
            if (!($param instanceof DispatchableEntityInterface)) {
                throw new UrlPrototypeException('Parameter :model must be an entity for using ID in url', [
                    ':model'  => get_class($param),
                ]);
            }

            return $param->getID();
        }

        return $param->getUrlKeyValue($key);
    }

    /**
     * @param string $string
     *
     * @return \BetaKiller\IFace\Url\UrlPrototype
     */
    private function fromString(string $string): UrlPrototype
    {
        return UrlPrototype::fromString($string);
    }
}
