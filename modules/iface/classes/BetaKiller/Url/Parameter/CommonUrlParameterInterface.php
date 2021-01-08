<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

/**
 * Interface CommonUrlParameterInterface
 *
 * Marker interface for UrlParameters which are used across all the project (UTM markers, Facebook Client ID, etc)
 *
 * @package BetaKiller\Url\Parameter
 */
interface CommonUrlParameterInterface extends UrlParameterInterface
{
    public static function getQueryKey(): string;
}
