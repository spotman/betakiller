<?php
namespace BetaKiller\Status;

interface StatusWorkflowInterface
{
    /**
     * @param string $codename
     * @throws StatusException
     */
    public function do_transition($codename);

    /**
     * @param string $codename
     *
     * @return bool
     */
    public function is_transition_allowed($codename);
}
