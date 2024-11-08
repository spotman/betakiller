<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

/**
 * Interface BooleanUrlParameterInterface
 *
 * Marker interface for UrlParameters which contains boolean value
 *
 * @package BetaKiller\Url\Parameter
 */
interface BooleanUrlParameterInterface extends RawUrlParameterInterface
{
    /**
     * Returns raw boolean value
     *
     * @return bool
     */
    public function getValue(): bool;
}
