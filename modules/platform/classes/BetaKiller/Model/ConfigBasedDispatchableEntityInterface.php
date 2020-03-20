<?php
namespace BetaKiller\Model;

/**
 * Interface ConfigBasedDispatchableEntityInterface
 *
 * @package BetaKiller\Core
 */
interface ConfigBasedDispatchableEntityInterface extends DispatchableEntityInterface
{
    public const URL_KEY_CODENAME = 'codename';

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
    public function getConfigOptions(): ?array;

    /**
     * Returns config-based property or null
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getConfigOption(string $key, $default = null);
}
