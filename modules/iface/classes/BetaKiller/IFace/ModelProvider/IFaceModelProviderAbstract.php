<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\IFaceModelInterface;

abstract class IFaceModelProviderAbstract implements IFaceModelProviderInterface
{
    /**
     * @param IFaceModelInterface $model
     *
     * @return IFaceModelInterface|NULL
     */
    public function getParent(IFaceModelInterface $model): ?IFaceModelInterface
    {
        return $model->getParent();
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $parentModel
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getChildren(IFaceModelInterface $parentModel): array
    {
        return $parentModel->getChildren();
    }

    /**
     * @param IFaceModelInterface $parentModel
     *
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getLayer(IFaceModelInterface $parentModel = null): array
    {
        return $parentModel
            ? $this->getChildren($parentModel)
            : $this->getRoot();
    }
}
