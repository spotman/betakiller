<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

/**
 * Interface IntegerUrlParameterInterface
 *
 * Marker interface for UrlParameters which contains integer value
 *
 * @package BetaKiller\Url\Parameter
 */
interface IntegerUrlParameterInterface extends RawUrlParameterInterface
{
    /**
     * Returns raw integer value
     *
     * @return int
     */
    public function getValue(): int;
}
