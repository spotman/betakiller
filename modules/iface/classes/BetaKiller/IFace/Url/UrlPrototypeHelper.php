<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;

class UrlPrototypeHelper
{
    const PROTOTYPE_PCRE = '(\{([A-Za-z_]+)\.([A-Za-z_]+)(\(\))*\})';

    /**
     * @var \BetaKiller\IFace\Url\UrlParametersInterface
     */
    private $urlParameters;

    /**
     * UrlPrototypeHelper constructor.
     *
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $urlParameters
     */
    public function __construct(UrlParametersInterface $urlParameters)
    {
        $this->urlParameters = $urlParameters;
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
    public function getModelInstance(UrlPrototype $prototype)
    {
        $name = $prototype->getModelName();

        if (!$name) {
            throw new UrlPrototypeException('Empty UrlPrototype model name');
        }

        // TODO Introduce UrlDataSourceFactory and use it

        /** @var UrlDataSourceInterface $object */
        $object = \ORM::factory($name);

        if (!($object instanceof UrlDataSourceInterface)) {
            throw new UrlPrototypeException('The model :name must implement :proto', [
                ':name'  => $name,
                ':proto' => UrlDataSourceInterface::class,
            ]);
        }

        return $object;
    }

    public function getModelFromUrlParameters(UrlPrototype $prototype, UrlParametersInterface $params = null)
    {
        //TODO

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

    public function getCompiledPrototypeValue($prototypeString, UrlParametersInterface $parameters = null, $isTree = false)
    {
        $prototype = $this->fromString($prototypeString);

        $modelName = $prototype->getModelName();

        /** @var UrlDataSourceInterface $model */
        $model = $parameters ? $parameters->get($modelName) : null;

        // Inherit model from current request url parameters
        $model = $model ?: $this->urlParameters->get($modelName);

        if (!$model) {
            throw new UrlPrototypeException('Can not find :name model in parameters', [':name' => $modelName]);
        }

        if ($isTree && !($model instanceof TreeModelSingleParentInterface)) {
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

    protected function calculateModelKeyValue(UrlPrototype $prototype, UrlDataSourceInterface $model)
    {
        $key = $prototype->getModelKey();

        if (!$prototype->isMethodCall()) {
            return $model->getUrlKeyValue($key);
        }

        $method = $key;

        if (!method_exists($model, $method)) {
            throw new UrlPrototypeException('Method :method does not exists in model :model', [
                ':method' => $method,
                ':model'  => get_class($model),
            ]);
        }

        return $model->$method();
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

        $prototype = $this->cretePrototype();

        list($name, $key) = explode('.', $string);

        if (strpos($key, '()') !== false) {
            $prototype->markAsMethodCall();
        }

        $key = str_replace('()', '', $key);

        return $prototype
            ->setModelName($name)
            ->setModelKey($key);
    }

    protected function cretePrototype()
    {
        return new UrlPrototype;
    }
}
