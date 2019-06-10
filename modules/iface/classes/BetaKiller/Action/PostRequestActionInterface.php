<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use Spotman\Defence\DefinitionBuilderInterface;

interface PostRequestActionInterface
{
    /**
     * Arguments definition for request` POST data
     *
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     */
    public function definePostArguments(DefinitionBuilderInterface $builder): void;
}
