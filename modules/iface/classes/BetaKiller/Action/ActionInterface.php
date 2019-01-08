<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\DefinitionBuilderInterface;

interface ActionInterface
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

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
