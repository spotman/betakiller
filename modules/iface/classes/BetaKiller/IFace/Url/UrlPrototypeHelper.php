<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;

class UrlPrototypeHelper
{
    const PROTOTYPE_PCRE = '(\{([A-Za-z_]+)\.([A-Za-z_]+)(\(\))*\})';

    /**
     * @var \BetaKiller\IFace\Url\UrlParametersInterface
     */
    private $urlParameters;

    /**
     * @var \BetaKiller\IFace\Url\UrlDataSourceFactory
     */
    private $dataSourceFactory;

    /**
     * UrlPrototypeHelper constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $urlParameters
     * @param \BetaKiller\IFace\Url\UrlDataSourceFactory   $factory
     */
    public function __construct(UrlParametersInterface $urlParameters, UrlDataSourceFactory $factory)
    {
        $this->urlParameters     = $urlParameters;
        $this->dataSourceFactory = $factory;
    }

    public function fromIFaceUri(IFaceInterface $iface)
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
    public function fromIFaceModelUri(IFaceModelInterface $ifaceModel)
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
    public function getDataSourceInstance(UrlPrototype $prototype)
    {
        $name = $prototype->getDataSourceName();

        if (!$name) {
            throw new UrlPrototypeException('Empty UrlPrototype model name');
        }

        return $this->dataSourceFactory->create($name);
    }

    public function replaceUrlParametersParts($sourceString, UrlParametersInterface $parameters = null)
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
     * @param                                                   $prototypeString
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $parameters
     * @param bool|null                                         $isTree
     *
     * @return string
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    public function getCompiledPrototypeValue($prototypeString, UrlParametersInterface $parameters = null, $isTree = null)
    {
        $prototype = $this->fromString($prototypeString);

        $model = $this->getModelFromUrlParameters($prototype, $parameters);

        if ((bool)$isTree && !($model instanceof TreeModelSingleParentInterface)) {
            throw new UrlPrototypeException('Model :model must be instance of :object for tree traversing', [
                ':model'  => get_class($model),
                ':object' => TreeModelSingleParentInterface::class,
            ]);
        }

        $parts = [];

        do {
            $parts[] = $this->calculateModelKeyValue($prototype, $model);
        } while ($isTree && ($model = $model->getParent()));

        return implode('/', array_reverse($parts));
    }

    /**
     * @param \BetaKiller\IFace\Url\UrlPrototype                $prototype
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|null $parameters
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    public function getModelFromUrlParameters(UrlPrototype $prototype, UrlParametersInterface $parameters = null)
    {
        $modelName = $prototype->getDataSourceName();

        $model = $parameters ? $parameters->getEntity($modelName) : null;

        // Inherit model from current request url parameters
        $model = $model ?: $this->urlParameters->getEntity($modelName);

        if (!$model) {
            throw new UrlPrototypeException('Can not find :name model in parameters', [':name' => $modelName]);
        }

        return $model;
    }

    protected function calculateModelKeyValue(UrlPrototype $prototype, DispatchableEntityInterface $entity)
    {
        $key = $prototype->getModelKey();

        if (!$prototype->isMethodCall()) {
            return $entity->getUrlKeyValue($key);
        }

        $method = $key;

        if (!method_exists($entity, $method)) {
            throw new UrlPrototypeException('Method :method does not exists in model :model', [
                ':method' => $method,
                ':model'  => get_class($entity),
            ]);
        }

        return $entity->$method();
    }

    /**
     * @param string $string
     *
     * @return \BetaKiller\IFace\Url\UrlPrototype
     */
    private function fromString($string)
    {
        $string = trim($string, '{}');

        if (!$string) {
            throw new UrlPrototypeException('Empty url prototype string');
        }

        $prototype = $this->createPrototype();

        list($name, $key) = explode('.', $string);

        if (strpos($key, '()') !== false) {
            $prototype->markAsMethodCall();
        }

        $key = str_replace('()', '', $key);

        return $prototype
            ->setModelName($name)
            ->setModelKey($key);
    }

    private function createPrototype()
    {
        return new UrlPrototype;
    }
}
