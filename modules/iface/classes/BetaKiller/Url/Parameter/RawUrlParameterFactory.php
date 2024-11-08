<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

use BetaKiller\Config\AppConfigInterface;

/**
 * Class RawUrlParameterFactory
 *
 * @package BetaKiller\Url
 */
readonly class RawUrlParameterFactory
{
    /**
     * RawUrlParameterFactory constructor.
     */
    public function __construct(private AppConfigInterface $appConfig)
    {
    }

    /**
     * @param string $codename
     * @param string $uriValue
     *
     * @return \BetaKiller\Url\Parameter\RawUrlParameterInterface
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    public function create(string $codename, string $uriValue): RawUrlParameterInterface
    {
        $map = $this->appConfig->getRawUrlParameters();

        $className = $map[$codename] ?? null;

        if (!$className) {
            throw new UrlParameterException('Missing UrlParameter mapping for ":name"', [
                ':name' => $codename,
            ]);
        }

        if (!is_a($className, RawUrlParameterInterface::class, true)) {
            throw new UrlParameterException('Class :class must implement :must', [
                ':class' => $className,
                ':must'  => RawUrlParameterInterface::class,
            ]);
        }

        return call_user_func([$className, 'fromUriValue'], $uriValue);
    }
}
