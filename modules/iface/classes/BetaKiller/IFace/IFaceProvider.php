<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;
use BetaKiller\Model\DispatchableEntityInterface;

class IFaceProvider
{
    /**
     * @var IFaceInterface[]
     */
    private $ifaceInstances;

    /**
     * @var IFaceModelProviderAggregate
     */
    private $modelProvider;

    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    private $factory;

    /**
     * @var string[]
     */
    private $entityLinkedCodenameCache;

    /**
     * IFaceProvider constructor
     *
     * @param IFaceModelProviderAggregate $modelProvider
     * @param NamespaceBasedFactory       $factory
     */
    public function __construct(IFaceModelProviderAggregate $modelProvider, NamespaceBasedFactory $factory)
    {
        $this->modelProvider = $modelProvider;
        $this->factory       = $factory;
    }

    public function fromCodename($codename)
    {
        $iface = $this->getInstanceFromCache($codename);

        if (!$iface) {
            $model = $this->getModelProvider()->getByCodename($codename);
            $iface = $this->createIFace($model);
            $this->storeInstanceInCache($codename, $iface);
        }

        return $iface;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    protected function getInstanceFromCache($codename)
    {
        return isset($this->ifaceInstances[$codename])
            ? $this->ifaceInstances[$codename]
            : null;
    }

    /**
     * @return IFaceModelProviderAggregate
     */
    protected function getModelProvider()
    {
        return $this->modelProvider;
    }

    // TODO Move to IFaceFactory
    protected function createIFace(IFaceModelInterface $model)
    {
        $codename = $model->getCodename();

        /** @var \BetaKiller\IFace\IFaceInterface $object */
        $object = $this->factory
            ->setClassPrefixes('IFace')
            ->setExpectedInterface(IFaceInterface::class)
            ->create($codename);

        $object->setModel($model);

        return $object;
    }

    /**
     * @param string                           $codename
     * @param \BetaKiller\IFace\IFaceInterface $iface
     */
    protected function storeInstanceInCache($codename, IFaceInterface $iface)
    {
        $this->ifaceInstances[$codename] = $iface;
    }

    /**
     * @param IFaceInterface $parentIFace
     *
     * @return IFaceModelInterface[]
     * @throws IFaceException
     */
    public function getModelsLayer(IFaceInterface $parentIFace = null)
    {
        $parentIFaceModel = $parentIFace ? $parentIFace->getModel() : null;

        $layer = $this->getModelProvider()->getLayer($parentIFaceModel);

        if (!$layer) {
            throw new IFaceException('Empty layer for :codename IFace',
                [':codename' => $parentIFace->getCodename()]
            );
        }

        return $layer;
    }

    public function getDefault()
    {
        $defaultModel = $this->getModelProvider()->getDefault();

        return $this->createIFace($defaultModel);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function getParent(IFaceInterface $iface)
    {
        $model       = $iface->getModel();
        $parentModel = $this->getModelProvider()->getParent($model);

        return $parentModel
            ? $this->fromModel($parentModel)
            : null;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function fromModel(IFaceModelInterface $model)
    {
        $codename = $model->getCodename();
        $iface    = $this->getInstanceFromCache($codename);

        if (!$iface) {
            $iface = $this->createIFace($model);
            $this->storeInstanceInCache($codename, $iface);
        }

        return $iface;
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
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, $entityAction, $zone)
    {
        $key = implode('.', [$entity->getModelName(), $entityAction, $zone]);

        if (!($iface = $this->getLinkedIFaceFromCache($key))) {
            $model = $this->getModelProvider()->getByEntityActionAndZone($entity, $entityAction, $zone);

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
     * @param string $key
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    private function getLinkedIFaceFromCache($key)
    {
        if (!isset($this->entityLinkedCodenameCache[$key])) {
            return null;
        }

        $codename = $this->entityLinkedCodenameCache[$key];

        return $this->fromCodename($codename);
    }

    private function storeLinkedIFaceInCache($key, IFaceInterface $iface)
    {
        $this->entityLinkedCodenameCache[$key] = $iface->getCodename();
    }
}
