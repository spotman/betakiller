<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;

interface IFaceModelProviderInterface
{
    /**
     * @return IFaceModelInterface[]
     */
    public function getAll(): array;
}
