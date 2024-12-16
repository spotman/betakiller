<?php

namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Repository\PhpExceptionRepository;

readonly class UnresolvedPhpExceptionIndexIFace extends AbstractPhpExceptionIndex
{
    /**
     * @param \BetaKiller\Repository\PhpExceptionRepository $repo
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface[]
     * @throws \Kohana_Exception
     */
    protected function getPhpExceptions(PhpExceptionRepository $repo): array
    {
        return $repo->getUnresolvedPhpExceptions();
    }
}
