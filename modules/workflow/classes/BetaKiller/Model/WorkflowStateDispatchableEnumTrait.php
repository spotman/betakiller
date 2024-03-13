<?php
declare(strict_types=1);

namespace BetaKiller\Model;

trait WorkflowStateDispatchableEnumTrait
{
    use EnumBasedDispatchableEntityTrait;

    public function getCodename(): string
    {
        return $this->getID();
    }
}
