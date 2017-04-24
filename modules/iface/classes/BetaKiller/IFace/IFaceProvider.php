<?php
namespace BetaKiller\IFace;

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderAggregate;

class IFaceProvider
{
    /**
     * @var IFaceInterface[]
     */
    protected $ifaceInstances;

    /**
     * @var IFaceModelProviderAggregate
     */
    protected $modelProvider;

    /**
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    protected $factory;

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

    public function getByCodename($codename)
    {
        $iface = $this->getFromCache($codename);

        if (!$iface) {
            $model = $this->getModelProvider()->getByCodename($codename);
            $iface = $this->createIFace($model);
            $this->storeInCache($codename, $iface);
        }

        return $iface;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    protected function getFromCache($codename)
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
    protected function storeInCache($codename, IFaceInterface $iface)
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
        $model        = $iface->getModel();
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
        $iface    = $this->getFromCache($codename);

        if (!$iface) {
            $iface = $this->createIFace($model);
            $this->storeInCache($codename, $iface);
        }

        return $iface;
    }
}
