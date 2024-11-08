<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

/**
 * Interface StringUrlParameterInterface
 *
 * Marker interface for UrlParameters which contains string value
 *
 * @package BetaKiller\Url\Parameter
 */
interface StringUrlParameterInterface extends RawUrlParameterInterface
{
    /**
     * Returns raw string value
     *
     * @return string
     */
    public function getValue(): string;
}
