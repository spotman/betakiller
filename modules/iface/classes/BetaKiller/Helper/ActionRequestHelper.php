<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsInterface;

class ActionRequestHelper
{
    public const GET_ATTRIBUTE = 'action_arguments_get';
    public const POST_ATTRIBUTE = 'action_arguments_post';

    public static function getArguments(ServerRequestInterface $request): ArgumentsInterface
    {
        return $request->getAttribute(self::GET_ATTRIBUTE);
    }

    public static function postArguments(ServerRequestInterface $request): ArgumentsInterface
    {
        return $request->getAttribute(self::POST_ATTRIBUTE);
    }
}
