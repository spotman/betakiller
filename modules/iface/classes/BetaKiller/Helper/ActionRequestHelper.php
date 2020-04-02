<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;

class ActionRequestHelper
{
    private const GET_ATTRIBUTE = 'action_arguments_get';
    private const POST_ATTRIBUTE = 'action_arguments_post';

    public static function withGetArguments(
        ServerRequestInterface $request,
        ArgumentsInterface $arguments
    ): ServerRequestInterface {
        return $request->withAttribute(self::GET_ATTRIBUTE, $arguments);
    }

    public static function withPostArguments(
        ServerRequestInterface $request,
        ArgumentsInterface $arguments
    ): ServerRequestInterface {
        return $request->withAttribute(self::POST_ATTRIBUTE, $arguments);
    }

    public static function getArguments(ServerRequestInterface $request): ArgumentsInterface
    {
        return $request->getAttribute(self::GET_ATTRIBUTE);
    }

    public static function postArguments(ServerRequestInterface $request): ArgumentsInterface
    {
        return $request->getAttribute(self::POST_ATTRIBUTE);
    }
}
