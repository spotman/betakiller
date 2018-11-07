<?php
declare(strict_types=1);

namespace Spotman\Defence\Rule;

use Spotman\Defence\GuardInterface;

interface DefinitionRuleInterface extends GuardInterface
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool;
}
