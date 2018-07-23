<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

interface RawUrlParameterInterface extends UrlParameterInterface
{
    public const CLASS_NS = ['Url', 'Parameter'];
    public const CLASS_SUFFIX = 'UrlParameter';

    /**
     * Process uri and set internal state
     *
     * @param string $uriValue
     */
    public function importUriValue(string $uriValue): void;

    /**
     * Returns composed uri for current state
     *
     * @return string
     */
    public function exportUriValue(): string;
}
