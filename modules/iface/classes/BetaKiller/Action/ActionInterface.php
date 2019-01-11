<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use Psr\Http\Server\RequestHandlerInterface;
use Spotman\Defence\DefinitionBuilderInterface;

interface ActionInterface extends RequestHandlerInterface
{
    public const NAMESPACE = 'Action';
    public const SUFFIX    = 'Action';

    /**
     * Arguments definition for request` GET data
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface;

    /**
     * Arguments definition for request` POST data
     *
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function postArgumentsDefinition(): DefinitionBuilderInterface;
}
