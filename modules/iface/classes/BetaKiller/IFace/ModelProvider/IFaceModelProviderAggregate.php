<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;

class IFaceModelProviderAggregate extends IFaceModelProviderAbstract
{
    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase
     */
    private $databaseProvider;

    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig
     */
    private $adminProvider;

    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderInterface[]
     */
    protected $sources;

    /**
     * @var IFaceModelInterface[]
     */
    protected $modelInstances = [];

    /**
     * IFaceModelProviderAggregate constructor.
     *
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase  $databaseProvider
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig $adminProvider
     */
    public function __construct(IFaceModelProviderDatabase $databaseProvider, IFaceModelProviderXmlConfig $adminProvider)
    {
        $this->databaseProvider = $databaseProvider;
        $this->adminProvider    = $adminProvider;
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface|null
     * @throws IFaceException
     */
    public function getDefault()
    {
        $model = null;

        foreach ($this->getSources() as $source) {
            if ($model = $source->getDefault()) {
                break;
            }
        }

        if (!$model) {
            throw new IFaceException('No default IFace found');
        }

        $this->storeInCache($model);

        return $model;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     *
     * @return IFaceModelInterface|null
     * @throws IFaceException
     */
    public function getByCodename($codename)
    {
        $model = $this->getFromCache($codename);

        if (!$model) {
            foreach ($this->getSources() as $source) {
                if ($model = $source->getByCodename($codename)) {
                    break;
                }
            }

            if (!$model) {
                throw new IFaceException('No IFace found by codename :codename', [':codename' => $codename]);
            }

            $this->storeInCache($model);
        }

        return $model;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $parentModel
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getChildren(IFaceModelInterface $parentModel)
    {
        $models = parent::getChildren($parentModel);

        $this->storeInCacheMultiple($models);

        return $models;
    }

    /**
     * @param IFaceModelInterface $model
     *
     * @return IFaceModelInterface|NULL
     */
    public function getParent(IFaceModelInterface $model)
    {
        $parent = parent::getParent($model);

        if ($parent) {
            $this->storeInCache($parent);
        }

        return $parent;
    }

    /**
     * Returns list of root elements
     *
     * @return IFaceModelInterface[]
     * @throws IFaceException
     */
    public function getRoot()
    {
        /** @var IFaceModelInterface[] $models */
        $models = [];

        foreach ($this->getSources(true) as $source) {
            $root = $source->getRoot();

            foreach ($root as $item) {
                $models[$item->getCodename()] = $item;
            }
        }

        $this->storeInCacheMultiple($models);

        return $models;
    }

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceModelInterface|null
     */
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, $entityAction, $zone)
    {
        $model = null;

        foreach ($this->getSources() as $source) {
            if ($model = $source->getByEntityActionAndZone($entity, $entityAction, $zone)) {
                break;
            }
        }

        if ($model) {
            $this->storeInCache($model);
        }

        return $model;
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return IFaceModelInterface[]
     */
    public function getByActionAndZone($action, $zone)
    {
        $models = [];

        foreach ($this->getSources() as $source) {
            $models[] = $source->getByActionAndZone($action, $zone);
        }

        return array_merge(...$models);
    }

    /**
     * @param bool $reverse
     *
     * @return \BetaKiller\IFace\ModelProvider\IFaceModelProviderInterface[]
     */
    protected function getSources($reverse = null)
    {
        if (!$this->sources) {
            $this->sources = [
                $this->databaseProvider,
                $this->adminProvider,
            ];
        }

        return (bool)$reverse ? array_reverse($this->sources) : $this->sources;
    }

    /**
     * @param IFaceModelInterface[] $models
     */
    protected function storeInCacheMultiple(array $models)
    {
        foreach ($models as $model) {
            $this->storeInCache($model);
        }
    }

    /**
     * @param string $codename
     *
     * @return IFaceModelInterface|NULL
     */
    protected function getFromCache($codename)
    {
        return isset($this->modelInstances[$codename])
            ? $this->modelInstances[$codename]
            : null;
    }

    protected function storeInCache(IFaceModelInterface $model)
    {
        $this->modelInstances[$model->getCodename()] = $model;
    }
}
