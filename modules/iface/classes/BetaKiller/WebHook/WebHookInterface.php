<?php
declare(strict_types=1);

namespace BetaKiller\WebHook;

use BetaKiller\Url\WebHookModelInterface;
use Psr\Http\Message\ServerRequestInterface;

interface WebHookInterface
{
    public function getModel(): WebHookModelInterface;

    public function setModel(WebHookModelInterface $model): WebHookInterface;

    public function process(ServerRequestInterface $request): void;

    public function getRequestDefinition(): RequestDefinitionInterface;
}
