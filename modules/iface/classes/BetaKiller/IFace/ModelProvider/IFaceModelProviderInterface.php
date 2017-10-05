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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRoot(): array;

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDefault(): ?IFaceModelInterface;

    /**
     * Returns iface model by codename or throws exception if none was found
     *
     * @param string $codename
     *
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByCodename(string $codename): IFaceModelInterface;

    /**
     * @param IFaceModelInterface|null $parentModel
     *
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getLayer(IFaceModelInterface $parentModel = null): array;

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $parentModel
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getChildren(IFaceModelInterface $parentModel): array;

    /**
     * @param IFaceModelInterface $model
     *
     * @return IFaceModelInterface|NULL
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getParent(IFaceModelInterface $model): ?IFaceModelInterface;

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, string $entityAction, string $zone): ?IFaceModelInterface;

    /**
     * @param string $action
     * @param string $zone
     *
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByActionAndZone(string $action, string $zone): array;
}
