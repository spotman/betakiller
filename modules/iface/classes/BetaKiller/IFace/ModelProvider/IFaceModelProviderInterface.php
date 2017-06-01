<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;

interface IFaceModelProviderInterface
{
    /**
     * Returns list of root elements
     *
     * @return IFaceModelInterface[]
     */
    public function getRoot();

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface
     */
    public function getDefault();

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     *
     * @return IFaceModelInterface|null
     */
    public function getByCodename($codename);

    /**
     * @param IFaceModelInterface|null $parentModel
     *
     * @return IFaceModelInterface[]
     */
    public function getLayer(IFaceModelInterface $parentModel = null);

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $parentModel
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getChildren(IFaceModelInterface $parentModel);

    /**
     * @param IFaceModelInterface $model
     *
     * @return IFaceModelInterface|NULL
     */
    public function getParent(IFaceModelInterface $model);

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceModelInterface|null
     */
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, $entityAction, $zone);

    /**
     * @param string $action
     * @param string $zone
     *
     * @return IFaceModelInterface[]
     */
    public function getByActionAndZone($action, $zone);
}
