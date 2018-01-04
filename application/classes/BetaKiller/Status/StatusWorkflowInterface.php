<?php
namespace BetaKiller\Status;

interface StatusWorkflowInterface
{
    public const CLASS_NS     = 'Status';
    public const CLASS_SUFFIX = 'Workflow';

    /**
     * @param string $codename
     *
     * @throws StatusException
     */
    public function doTransition(string $codename): void;

    /**
     * @param string $codename
     *
     * @return bool
     */
    public function isTransitionAllowed(string $codename): bool;
}
