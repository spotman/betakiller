<?php
namespace BetaKiller\Status;

interface StatusWorkflowInterface
{
    /**
     * @param string $codename
     * @throws StatusException
     */
    public function doTransition($codename);

    /**
     * @param string $codename
     *
     * @return bool
     */
    public function isTransitionAllowed($codename);
}
