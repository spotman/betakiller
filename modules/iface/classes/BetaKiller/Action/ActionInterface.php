<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

interface ActionInterface
{
    public const NAMESPACE = 'Action';
    public const SUFFIX    = 'Action';

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Spotman\Defence\ArgumentsInterface      $arguments
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request, ArgumentsInterface $arguments): ResponseInterface;
}
