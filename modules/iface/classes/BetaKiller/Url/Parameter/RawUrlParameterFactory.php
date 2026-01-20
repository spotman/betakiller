<?php

declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

use BetaKiller\Config\AppConfigInterface;
use Throwable;

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
        $map = $this->getMap();

        $className = $map[$codename] ?? null;

        if (!$className) {
            throw new UrlParameterException('Missing UrlParameter mapping for ":name"', [
                ':name' => $codename,
            ]);
        }

        try {
            return $className::fromUriValue($uriValue);
        } catch (Throwable $e) {
            throw new UrlParameterException('Can not create :name from ":value"', [
                ':name' => $codename,
                ':value' => $uriValue,
            ], 0, $e);
        }
    }

    /**
     * @return array<string, \BetaKiller\Url\Parameter\RawUrlParameterInterface>
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    private function getMap(): array
    {
        static $map;

        if (!$map) {
            foreach ($this->appConfig->getRawUrlParameters() as $fqcn) {
                if (!is_a($fqcn, RawUrlParameterInterface::class, true)) {
                    throw new UrlParameterException('Class :class must implement :must', [
                        ':class' => $fqcn,
                        ':must'  => RawUrlParameterInterface::class,
                    ]);
                }

                $map[$fqcn::getUrlContainerKey()] = $fqcn;
            }
        }

        return $map;
    }
}
