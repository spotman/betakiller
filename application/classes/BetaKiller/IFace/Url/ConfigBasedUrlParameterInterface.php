<?php
namespace BetaKiller\IFace\Url;

/**
 * Interface ConfigBasedUrlParameterInterface
 *
 * @package BetaKiller\Core
 */
interface ConfigBasedUrlParameterInterface extends UrlParameterInterface
{
    /**
     * Config-based url parameters needs codename to be defined
     *
     * @return string
     */
    public function getCodename(): string;

    /**
     * Config-based url parameters may define properties in config file
     *
     * @return array|null
     */
    public function getOptions(): ?array;

    /**
     * Returns config-based property or null
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getOption(string $key, $default = null);
}
