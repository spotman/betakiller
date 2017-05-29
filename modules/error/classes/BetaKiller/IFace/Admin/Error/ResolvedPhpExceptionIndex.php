<?php
namespace BetaKiller\IFace\Admin\Error;

class ResolvedPhpExceptionIndex extends AbstractPhpExceptionIndex
{
    /**
     * @return \BetaKiller\Error\PhpExceptionModelInterface[]
     */
    protected function getPhpExceptions()
    {
        return $this->phpExceptionStorage->getResolvedPhpExceptions();
    }
}
