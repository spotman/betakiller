<?php

namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Repository\PhpExceptionRepository;

readonly class IgnoredPhpExceptionIndexIFace extends AbstractPhpExceptionIndex
{
    /**
     * @param \BetaKiller\Repository\PhpExceptionRepository $repo
     *
     * @return \BetaKiller\Model\PhpExceptionModelInterface[]
     */
    protected function getPhpExceptions(PhpExceptionRepository $repo): array
    {
        return $repo->getIgnoredPhpExceptions();
    }
}
