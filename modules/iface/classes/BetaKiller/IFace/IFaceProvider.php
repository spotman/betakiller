<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\FactoryException;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;
use BetaKiller\Model\DispatchableEntityInterface;

class IFaceProvider
{
    /**
     * @var \BetaKiller\IFace\IFaceFactory
     */
    protected $factory;

    /**
     * @var IFaceModelProviderAggregate
     */
    private $modelProvider;

    /**
     * @var string[]
     */
    private $entityLinkedCache;

    /**
     * IFaceProvider constructor
     *
     * @param IFaceModelProviderAggregate    $modelProvider
     * @param \BetaKiller\IFace\IFaceFactory $factory
     */
    public function __construct(IFaceModelProviderAggregate $modelProvider, IFaceFactory $factory)
    {
        $this->modelProvider = $modelProvider;
        $this->factory       = $factory;
    }

    /**
     * Creates IFace instance from it`s codename (automatic model detection)
     *
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function fromCodename(string $codename): IFaceInterface
    {
        $model = $this->modelProvider->getByCodename($codename);

        return $this->fromModel($model);
    }

    /**
     * Creates IFace instance from it`s model
     *
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function fromModel(IFaceModelInterface $model): IFaceInterface
    {
        try {
            return $this->factory->createFromModel($model);
        } catch (FactoryException $e) {
            throw IFaceException::wrap($e);
        }
    }

    /**
     * @param IFaceInterface $parentIFace
     *
     * @return IFaceModelInterface[]
     * @throws IFaceException
     */
    public function getModelsLayer(IFaceInterface $parentIFace = null): array
    {
        $parentIFaceModel = $parentIFace ? $parentIFace->getModel() : null;

        $layer = $this->modelProvider->getLayer($parentIFaceModel);

        if (!$layer) {
            throw new IFaceException('Empty layer for :codename IFace',
                [':codename' => $parentIFace->getCodename()]
            );
        }

        return $layer;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $parentIFace
     *
     * @return \BetaKiller\IFace\IFaceInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getChildren(IFaceInterface $parentIFace): array
    {
        $models = $this->getModelsLayer($parentIFace);

        $ifaces = [];

        foreach ($models as $model) {
            $ifaces[] = $this->fromModel($model);
        }

        return $ifaces;
    }

    /**
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDefault(): IFaceInterface
    {
        $defaultModel = $this->modelProvider->getDefault();

        if (!$defaultModel) {
            throw new IFaceException('Default iface is missing');
        }

        return $this->fromModel($defaultModel);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getParent(IFaceInterface $iface): ?IFaceInterface
    {
        $model       = $iface->getModel();
        $parentModel = $this->modelProvider->getParent($model);

        return $parentModel
            ? $this->fromModel($parentModel)
            : null;
    }

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByEntityActionAndZone(
        DispatchableEntityInterface $entity,
        string $entityAction,
        string $zone
    ): IFaceInterface {
        $key = implode('.', [$entity->getModelName(), $entityAction, $zone]);

        if (!($iface = $this->getLinkedIFaceFromCache($key))) {
            $model = $this->modelProvider->getByEntityActionAndZone($entity, $entityAction, $zone);

            if (!$model) {
                throw new IFaceException('No IFace found for :entity.:action entity in :zone zone', [
                    ':entity' => $entity->getModelName(),
                    ':action' => $entityAction,
                    ':zone'   => $zone,
                ]);
            }

            $iface = $this->fromModel($model);

            $this->storeLinkedIFaceInCache($key, $iface);
        }

        return $iface;
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return \BetaKiller\IFace\IFaceInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByActionAndZone(string $action, string $zone): array
    {
        $models = $this->modelProvider->getByActionAndZone($action, $zone);
        $ifaces = [];

        foreach ($models as $model) {
            $ifaces[] = $this->fromModel($model);
        }

        return $ifaces;
    }

    /**
     * @param string $key
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getLinkedIFaceFromCache(string $key): ?IFaceInterface
    {
        if (!isset($this->entityLinkedCache[$key])) {
            return null;
        }

        $codename = $this->entityLinkedCache[$key];

        return $this->fromCodename($codename);
    }

    /**
     * @param string                           $key
     * @param \BetaKiller\IFace\IFaceInterface $iface
     */
    private function storeLinkedIFaceInCache(string $key, IFaceInterface $iface): void
    {
        $this->entityLinkedCache[$key] = $iface->getCodename();
    }
}
