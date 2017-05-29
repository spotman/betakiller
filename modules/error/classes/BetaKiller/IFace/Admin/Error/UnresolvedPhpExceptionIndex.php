<?php
namespace BetaKiller\IFace\Admin\Error;

class UnresolvedPhpExceptionIndex extends AbstractPhpExceptionIndex
{
    /**
     * @return \BetaKiller\Error\PhpExceptionModelInterface[]
     */
    protected function getPhpExceptions()
    {
        return $this->phpExceptionStorage->getUnresolvedPhpExceptions();
    }
}
