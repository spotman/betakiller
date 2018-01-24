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
    public function __construct(
        IFaceModelProviderDatabase $databaseProvider,
        IFaceModelProviderXmlConfig $adminProvider
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->adminProvider    = $adminProvider;
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface
     * @throws IFaceException
     * @deprecated Use IFaceModelTree instead
     */
    public function getDefault(): IFaceModelInterface
    {
        $model = null;

        foreach ($this->getSources() as $source) {
            $model = $source->getDefault();

            if ($model) {
                $this->storeInCache($model);

                return $model;
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
     * @param string $codename
     *
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @deprecated Use IFaceModelTree instead
     */
    public function getByCodename(string $codename): IFaceModelInterface
    {
        $model     = $this->getFromCache($codename);
        $exception = null;

        if (!$model) {
            foreach ($this->getSources() as $source) {
                try {
                    $model = $source->getByCodename($codename);
                    break;
                } catch (IFaceException $e) {
                    $exception = $e;
                }
            }

            if (!$model) {
                throw new IFaceException('No IFace found by codename :codename', [':codename' => $codename], 0,
                    $exception);
            }

            $this->storeInCache($model);
        }

        return $model;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $parentModel
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     * @deprecated Use IFaceModelTree instead
     */
    public function getChildren(IFaceModelInterface $parentModel): array
    {
        $models = parent::getChildren($parentModel);

        $this->storeInCacheMultiple($models);

        return $models;
    }

    /**
     * @param IFaceModelInterface $model
     *
     * @return IFaceModelInterface|NULL
     * @deprecated Use IFaceModelTree instead
     */
    public function getParent(IFaceModelInterface $model): ?IFaceModelInterface
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @deprecated Use IFaceModelTree instead
     */
    public function getRoot(): array
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @deprecated Use IFaceModelTree instead
     */
    public function getByEntityActionAndZone(
        DispatchableEntityInterface $entity,
        string $entityAction,
        string $zone
    ): ?IFaceModelInterface {
        $model = null;

        foreach ($this->getSources() as $source) {
            $model = $source->getByEntityActionAndZone($entity, $entityAction, $zone);

            if ($model) {
                $this->storeInCache($model);

                return $model;
            }
        }

        return null;
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @deprecated Use IFaceModelTree instead
     */
    public function getByActionAndZone(string $action, string $zone): array
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
    protected function getSources($reverse = null): array
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
    protected function storeInCacheMultiple(array $models): void
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
    protected function getFromCache($codename): ?IFaceModelInterface
    {
        return $this->modelInstances[$codename] ?? null;
    }

    protected function storeInCache(IFaceModelInterface $model): void
    {
        $this->modelInstances[$model->getCodename()] = $model;
    }
}
