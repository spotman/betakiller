<?php
namespace BetaKiller\IFace;

use BetaKiller\IFace\Url\DispatchableEntityInterface;

class IFaceFactory
{
    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $provider;

    /**
     * IFaceFactory constructor.
     *
     * @param \BetaKiller\IFace\IFaceProvider $provider
     */
    public function __construct(IFaceProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Creates instance of IFace from model
     *
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     */
    public function fromModel(IFaceModelInterface $model)
    {
        return $this->provider->fromModel($model);
    }

    /**
     * Creates IFace instance from it`s codename
     *
     * @param string $codename IFace codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function fromCodename($codename)
    {
        return $this->provider->fromCodename($codename);
    }

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\IFace\Url\DispatchableEntityInterface $entity
     * @param string                                            $entityAction
     * @param string                                            $zone
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, $entityAction, $zone)
    {
        return $this->provider->getByEntityActionAndZone($entity, $entityAction, $zone);
    }
}
