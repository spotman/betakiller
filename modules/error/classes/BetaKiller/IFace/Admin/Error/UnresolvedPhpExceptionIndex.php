<?php
namespace BetaKiller\IFace\Admin\Error;

class UnresolvedPhpExceptionIndex extends AbstractPhpExceptionIndex
{
    /**
     * @return \BetaKiller\Model\PhpExceptionModelInterface[]
     */
    protected function getPhpExceptions(): array
    {
        return $this->repository->getUnresolvedPhpExceptions();
    }
}
