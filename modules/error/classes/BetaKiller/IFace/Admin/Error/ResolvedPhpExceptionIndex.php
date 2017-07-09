<?php
namespace BetaKiller\IFace\Admin\Error;

class ResolvedPhpExceptionIndex extends AbstractPhpExceptionIndex
{
    /**
     * @return \BetaKiller\Model\PhpExceptionModelInterface[]
     */
    protected function getPhpExceptions(): array
    {
        return $this->repository->getResolvedPhpExceptions();
    }
}
